<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Site\Controllers;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/comFormsPageBouncer.php";
require_once "$componentPath/helpers/formPageElementDecorator.php";
require_once "$componentPath/helpers/formResponseActivityHelper.php";
require_once "$componentPath/helpers/formsAuth.php";
require_once "$componentPath/helpers/formsRouter.php";
require_once "$componentPath/helpers/params.php";
require_once "$componentPath/helpers/relationalCrudHelper.php";
require_once "$componentPath/helpers/virtualCrudHelper.php";
require_once "$componentPath/models/form.php";
require_once "$componentPath/models/formResponse.php";
require_once "$componentPath/models/formResponseJson.php";
require_once "$componentPath/models/responseFeedItem.php";
require_once "$componentPath/helpers/sortableResponses.php";
require_once "$componentPath/models/permissions.php";

use Components\Forms\Helpers\ComFormsPageBouncer as PageBouncer;
use Components\Forms\Helpers\FormPageElementDecorator as ElementDecorator;
use Components\Forms\Helpers\FormsAuth as AuthHelper;
use Components\Forms\Helpers\FormsRouter as RoutesHelper;
use Components\Forms\Helpers\Params;
use Components\Forms\Helpers\RelationalCrudHelper as RCrudHelper;
use Components\Forms\Helpers\FormResponseActivityHelper;
use Components\Forms\Helpers\VirtualCrudHelper as VCrudHelper;
use Components\Forms\Helpers\SortableResponses;
use Components\Forms\Models\Form;
use Components\Forms\Models\FormResponse;
use Components\Forms\Models\FormResponseJson;
use Components\Forms\Models\ResponseFeedItem;
use Components\Forms\Models\Permissions;
use Hubzero\Component\SiteController;
use Date;

class FormResponses extends SiteController
{

	/**
	 * Task mapping
	 *
	 * @var  array
	 */
	protected $_taskMap = [
		'__default' => 'list'
	];

	/**
	 * Parameter whitelist
	 *
	 * @var  array
	 */
	protected static $_paramWhitelist = [
		'form_id',
		'response_id',
		'tag_string'
	];

	/**
	 * Executes the requested task
	 *
	 * @return   void
	 */
	public function execute()
	{
    	$this->_auth = new AuthHelper();
		$this->_crudHelper = new VCrudHelper([
			'errorSummary' => Lang::txt('COM_FORMS_NOTICES_FAILED_START')
		]);
		$this->_rCrudHelper = new RCrudHelper([
			'controller' => $this
		]);
		$this->_decorator = new ElementDecorator();
		$this->_pageBouncer = new PageBouncer();
		$this->_params = new Params(
			['whitelist' => self::$_paramWhitelist]
		);
		$this->_responseActivity = new FormResponseActivityHelper();
		$this->_routes = new RoutesHelper();

		parent::execute();
	}

	/**
	 * Create response for given form
	 *
	 * @return   void
	 */
	public function startTask()
	{
		$formId = $this->_params->getInt('form_id');
		$form = Form::oneOrFail($formId);
		$this->_pageBouncer->redirectUnlessCanFillForm($form);
		$this->_pageBouncer->redirectIfPrereqsNotAccepted($form);

		$response = FormResponse::blank();
		$response->set([
			'form_id' => $formId,
			'user_id' => User::get('id'),
			'created' => Date::toSql()
		]);

		// Save to generate new id
		if (!$response->save()) {
			$formOverviewPage = $this->_routes->formsDisplayUrl($formId);
			$this->_crudHelper->failedCreate($response, $formOverviewPage);
			return false;
		}

		// Create JSON form
		$json = FormResponseJson::blank()
			->set('response_id', $response->get('id'));
		if (!$json->save()) {
			$formOverviewPage = $this->_routes->formsDisplayUrl($formId);
			$this->_crudHelper->failedCreate($response, $formOverviewPage);
			return false;
		}

		$this->_responseActivity->logStart($response->get('id'));

		$forwardingUrl = $this->_routes->formResponseFillUrl($response->get('id'));
		$successMessage = Lang::txt('COM_FORMS_FORM_SAVE_SUCCESS');
		$this->_crudHelper->successfulCreate($forwardingUrl, $successMessage);
	}

	/**
	 * Fill response
	 *
	 * @return   void
	 */
	public function fillTask()
	{
		$responseId = $this->_params->getInt('response_id', 0);
		$response = FormResponse::oneOrFail($responseId);
		$this->_pageBouncer->redirectUnlessCanFillResponse($response);
		$json = $response->getJson();
		$form = $response->getForm();
		
		$isFormAdmin = $this->_auth->canCurrentUserEditForm($form);

		$this->view
			->set('form', $form)
			->set('response', $response)
			->set('json', $json)
			->set('userIsAdmin', $isFormAdmin)
			->display();
	}

	/**
	 * Creates response record using given data
	 *
	 * @param    array    $responseData   Response instantiation data
	 * @return   object
	 */
	protected function _generateResponse($responseData = [])
	{
		$defaultData = [
			'form_id' => $this->_params->getInt('form_id'),
			'user_id' => User::get('id'),
			'created' => Date::toSql()
		];
		$combinedData = array_merge($defaultData, $responseData);

		$response = FormResponse::blank();
		$response->set($combinedData);

		return $response;
	}

	/**
	 * Renders response review page
	 *
	 * @return   void
	 */
	public function reviewTask()
	{
		$formId = $this->_params->getInt('form_id');
		$form = Form::oneOrFail($formId);

		$this->_pageBouncer->redirectIfFormNotOpen($form);
		$this->_pageBouncer->redirectIfPrereqsNotAccepted($form);

		$pageElements = $form->getFieldsOrdered();
		$responseSubmitUrl = $this->_routes->formResponseSubmitUrl();
		$userId = User::get('id');
		$decoratedPageElements = $this->_decorator->decorateForRendering($pageElements, $userId);

		foreach ($pageElements as $element)
		{
			$element->_returnDefault = false;
		}

		$this->view
			->set('form', $form)
			->set('pageElements', $decoratedPageElements)
			->set('responseSubmitUrl', $responseSubmitUrl)
			->display();
	}

	/**
	 * AJAX: Attempts to handle the submission of a form response for review
	 *
	 * @return   void
	 */
	public function submitTask()
	{
		$responseId = $this->_params->getInt('response_id');
		$response = FormResponse::oneOrFail($responseId);

		$currentTime = Date::toSql();
		$response->set('modified', $currentTime);
		$response->set('submitted', $currentTime);

		if ($response->save())
		{
			$this->_responseActivity->logSubmit($response->get('id'));
			$forwardingUrl = $this->_routes->responseFeedUrl($responseId);
			$successMessage = Lang::txt('COM_FORMS_NOTICES_FORM_RESPONSE_SUBMIT_SUCCESS');
			$ajax_response = Array(
				'status' => 'success',
				'message' => $successMessage,
				'redirect' => $forwardingUrl
			);
			// $this->_rCrudHelper->successfulUpdate($forwardingUrl, $successMessage);
		}
		else
		{
			$errorSummary = Lang::txt('COM_FORMS_NOTICES_FORM_RESPONSE_SUBMIT_ERROR');
			$forwardingUrl = $this->_routes->formResponseReviewUrl($formId);
			$ajax_response = Array(
				'status' => 'error',
				'message' => $errorSummary,
				'redirect' => $forwardingUrl
			);
			// $this->_rCrudHelper->failedBatchUpdate($forwardingUrl, $response, $errorSummary);
		}

		echo json_encode($ajax_response);
		exit();
	}

	/**
	 * Unsubmit responses
	 */

	public function unsubmitTask()
	{
		$formId = $this->_params->getInt('form_id');
		$form = Form::oneOrFail($formId);

		$responseIds = $this->_params->get('response_ids');	
		$returnUrl = $this->_params->get('return_url');

		$this->_pageBouncer->redirectUnlessCanEditForm($form, $returnUrl);

		foreach ($responseIds as $responseId)
		{
			$response = FormResponse::oneOrFail($responseId);
			$this->_responseActivity->logUnsubmit($responseId);
			$response->set('submitted', null);
			if (!$response->save()) {
				$errorSummary = Lang::txt('COM_FORMS_NOTICES_FAILED_RESPONSES_UNSUBMIT');
				$this->_rCrudHelper->failedBatchUpdate($returnUrl, $response, $errorSummary);
			}
		}

		$successMessage = Lang::txt('COM_FORMS_RESPONSE_UNSUBMIT_SUCCESS');
		$this->_crudHelper->successfulUpdate($returnUrl, $successMessage);
	}

	/**
	 * Renders list of users responses
	 *
	 * @return   void
	 */
	public function listTask()
	{
		$formId = $this->_params->getInt('form_id');
		$form = Form::one($formId);
		
		$currentUserId = User::get('id');
		$responses = FormResponse::all();
		if ($formId) {
			$responses = $responses->whereEquals('form_id', $formId);
		} else {
			$responses = $responses->join('#__forms_forms AS F', 'form_id', 'F.id', 'left')
								   ->select('#__forms_form_responses.*, F.name AS form');
		}
		$responses = $responses->whereEquals('user_id', $currentUserId)
			->paginated('limitstart', 'limit');
		$responses = $this->_sortResponses($responses);
		
		$responsesListUrl = $this->_routes->usersResponsesUrl($formId);
		$feedItems = ResponseFeedItem::allForUser($currentUserId, $formId);
		$feedItems = $feedItems->order('created', 'desc');

		$this->view
			->set('feedItems', $feedItems)
			->set('responses', $responses)
			->set('form', $form)
			->set('listUrl', $responsesListUrl)
			->display();
	}

	/**
	 * Sort responses using given field and direction
	 *
	 * @param    object   $responses   Form's responses
	 * @return   void
	 */
	protected function _sortResponses($responses)
	{
		$sortDirection = $this->_params->getString('sort_direction', 'asc');
		$sortField = $this->_params->getString('sort_field', 'id');
		$sortingCriteria = ['field' => $sortField, 'direction' => $sortDirection];

		$this->view->set('sortingCriteria', $sortingCriteria);
		$sortableResponses = new SortableResponses(['responses' => $responses]);
		$sortableResponses->order($sortField, $sortDirection);

		return $sortableResponses;
	}

	/**
	 * Renders response's feed
	 *
	 * @return   void
	 */
	public function feedTask()
	{
		$createCommentAction = $this->_routes->createResponseCommentUrl();
		$responseId = $this->_params->getInt('response_id');
		$response = FormResponse::oneOrFail($responseId);
		$form = $response->getForm();
		$tagUpdateUrl = $this->_routes->updateResponsesTagsUrl();

		$currentTagString = $response->getTagString();
		$receivedTagString = $this->_params->getString('tag_string');
		$tagString = $receivedTagString ? $receivedTagString : $currentTagString;
		$comment = $this->_params->getString('comment');
		$feedItems = ResponseFeedItem::allForResponse($responseId)
			->order('id', 'desc')
			->rows();

		$isFormAdmin = $this->_auth->canCurrentUserEditForm($form);

		$this->view
			->set('comment', $comment)
			->set('createCommentUrl', $createCommentAction)
			->set('feedItems', $feedItems)
			->set('form', $form)
			->set('userIsAdmin', $isFormAdmin)
			->set('response', $response)
			->set('tagString', $tagString)
			->set('tagUpdateUrl', $tagUpdateUrl)
			->display();
	}

	/**
	 * AJAX request to get form json
	 */
	public function getjsonTask()
	{
		$responseId = $this->_params->getInt('response_id');
		$response = FormResponse::oneOrFail($responseId);
		$responseJson = $response->getJson();

		echo $responseJson;
		exit();
	}

	/**
	 * AJAX: Update json for form
	 */
	public function updatejsonTask()
	{
		$responseId = $this->_params->getInt('response_id');
		$response = FormResponse::oneOrFail($responseId);
		
		$responseJson = $this->_params->get('json');
		if (!$response->setJson($responseJson)) {
			$response = Array(
				'status' => "Failed to save form json."
			);
			echo json_encode($response);
			exit();
		}
		
		$response->set('modified', Date::toSql());
		if (!$response->save()) {
			$response = Array(
				'status' => "Failed to save form modification date."
			);
			echo json_encode($response);
			exit();
		}

		$response = Array(
			'status' => "Saved form json."
		);
		echo json_encode($response);
		exit();
	}

	/**
	 * AJAX: Update json for form
	 */
	public function updatepermissionsTask()
	{
		$responseId = $this->_params->getInt('response_id');
		$response = FormResponse::oneOrFail($responseId);
		
		$permissions = $this->_params->get('surveyjs-popup-json-data');
		$json = json_decode($permissions, true);

		// General permissions
		Permissions::setPermissions($response, $json, 'response');

		if (!$response->save()) {
			$response = Array(
				'status' => "Failed to save response data."
			);
			echo json_encode($response);
			exit();
		}

		if (!Permissions::setUsergroupPermissions($response, $json, 'response')) {
			$response = Array(
				'status' => "Failed to save response permissions."
			);
			echo json_encode($response);
			exit();
		}

		$this->_responseActivity->logUpdatePermissions($response->get('id'), $permissions);
		$response = Array(
			'status' => "Saved form json."
		);
		echo json_encode($response);
		exit();
	}

	/**
	 * Ensure no invalid characters
	 *
	 * @param   array  $data
	 * @return  string
	 */
	private function _cleanFilename($filename, $uploadDir)
	{
		// Make sure filename is safe for the filesystem
		// Taken from core/libraries/Hubzero/Item/Comment/File.php::automaticFilename
		$filename = preg_replace("/[^A-Za-z0-9.]/i", '-', $filename);

		$ext = strrchr($filename, '.');
		$prefix = substr($filename, 0, -strlen($ext));

		if (strlen($prefix) > 240)
		{
			$prefix = substr($prefix, 0, 240);
			$filename = $prefix . $ext;
		}

		// Make sure filename is unique - if not, rename it
		// Taken from core/libraries/Hubzero/Item/Comment/File.php::uniqueFilename
		if (file_exists($uploadDir . DS . $filename))
		{
			$ext = strrchr($filename, '.');
			$prefix = substr($filename, 0, -strlen($ext));

			$i = 1;

			while (is_file($uploadDir . DS . $filename))
			{
				$filename = $prefix . ++$i . $ext;
			}
		}

		return $filename;
	}

	/**
	 * AJAX: Upload files
	 */
	public function uploadfilesTask()
	{
		$formId = $this->_params->getInt('form_id');
		$responseId = $this->_params->getInt('response_id');

		$form = Form::oneOrFail($formId);
		$uploadDir = PATH_APP . DS . 'site' . DS . 'forms' . DS . $formId . DS . $responseId;
		$uploads = $_FILES;
		
		// Create directory if it doesn't exist
		if (!is_dir($uploadDir))
		{
			if (!\Filesystem::makeDirectory($uploadDir))
			{
				$response = Array(
					'status' => "Failed to create upload directory."
				);
				echo json_encode($response);
				exit();
			}
		}

		// Loop over files
		$uploaded = [];
		foreach ($uploads as $file)
		{
			$filename = $this->_cleanFilename($file['name'], $uploadDir);
			$filepath = $uploadDir . DS . $filename;

			if (!\Filesystem::upload($file['tmp_name'], $filepath))
			{
				$response = Array(
					'status' => "Failed to move uploaded file."
				);
				echo json_encode($response);
				exit();
			}

			$uploaded[$file['name']] = Array(
				'url' => Request::root() . 'app' . DS . 'site' . DS . 'forms' . DS . $formId . DS . $responseId . DS . $filename,
				'name' => $filename
			);
		}

		echo json_encode($uploaded);
		exit();
	}

	/**
	 * Delete a form
	 */
	public function deleteTask()
	{
		$formId = $this->_params->getInt('form_id');
		$responseIds = $this->_params->get('response_ids');	
		$returnUrl = $this->_params->get('return_url');

		foreach ($responseIds as $responseId)
		{
			$response = FormResponse::oneOrFail($responseId);
			if ($this->_auth->canCurrentUserFillResponse($response)) {
				$this->_responseActivity->logDelete($responseId);
				if (!$response->destroy()) {
					$this->_crudHelper->failedDelete($returnUrl, $response);		
				}
			}
		}

		$successMessage = Lang::txt('COM_FORMS_RESPONSE_DELETE_SUCCESS');
		$this->_crudHelper->successfulDelete($returnUrl, $successMessage);
	}

	/*
	 * AJAX: Delete files
	 */
	public function deletefileTask() {
		$formId = $this->_params->getInt('form_id');
		$responseId = $this->_params->getInt('response_id');
		$filename = $this->_params->getString('filename');

		$uploadDir = PATH_APP . DS . 'site' . DS . 'forms' . DS . $formId . DS . $responseId;
		$filepath = $uploadDir . DS . $filename;

		if (!\Filesystem::delete($filepath))
		{
			$response = Array(
				'status' => "Failed to delete file."
			);
			echo json_encode($response);
			exit();
		}

		echo json_encode(Array(
			'status' => "Deleted file."
		));
		exit();
	}
}
