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

use Components\Forms\Helpers\ComFormsPageBouncer as PageBouncer;
use Components\Forms\Helpers\FormPageElementDecorator as ElementDecorator;
use Components\Forms\Helpers\FormsAuth as AuthHelper;
use Components\Forms\Helpers\FormsRouter as RoutesHelper;
use Components\Forms\Helpers\Params;
use Components\Forms\Helpers\RelationalCrudHelper as RCrudHelper;
use Components\Forms\Helpers\FormResponseActivityHelper;
use Components\Forms\Helpers\VirtualCrudHelper as VCrudHelper;
use Components\Forms\Models\Form;
use Components\Forms\Models\FormResponse;
use Components\Forms\Models\FormResponseJson;
use Components\Forms\Models\ResponseFeedItem;
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
	 * Fill out form, and create response for given form & user if new
	 *
	 * @return   void
	 */
	public function fillTask()
	{
		$formId = $this->_params->getInt('form_id');
		$form = Form::oneOrFail($formId);
		$this->_pageBouncer->redirectIfFormDisabled($form);
		$this->_pageBouncer->redirectIfPrereqsNotAccepted($form);

		// In the future, allow multiple forms per user if form allows
		$response = FormResponse::all()
			->whereEquals('form_id', $formId)
			->whereEquals('user_id', User::get('id'));

		if ($response->count() == 0) {
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
				Notify::error($json->getError());
				return false;
			}

			$this->_responseActivity->logStart($response->get('id'));
		} else {
			$response = $response->row();
			$json = FormResponseJson::all()
				->whereEquals('response_id', $response->get('id'));
		}

		$this->view
			->set('form', $form)
			->set('response', $response)
			->set('json', $json)
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
	 * Attempts to handle the submission of a form response for review
	 *
	 * @return   void
	 */
	public function submitTask()
	{
		$currentUsersId = User::get('id');
		$formId = $this->_params->getInt('form_id');
		$form = Form::oneOrFail($formId);
		$response = $form->getResponse($currentUsersId);
		$responseId = $response->get('id');

		$this->_pageBouncer->redirectIfResponseSubmitted($response);
		$this->_pageBouncer->redirectIfFormNotOpen($form);
		$this->_pageBouncer->redirectIfPrereqsNotAccepted($form);

		$currentTime = Date::toSql();
		$response->set('modified', $currentTime);
		$response->set('submitted', $currentTime);

		if ($response->save())
		{
			$this->_responseActivity->logSubmit($response->get('id'));
			$forwardingUrl = $this->_routes->responseFeedUrl($responseId);
			$successMessage = Lang::txt('COM_FORMS_NOTICES_FORM_RESPONSE_SUBMIT_SUCCESS');
			$this->_rCrudHelper->successfulUpdate($forwardingUrl, $successMessage);
		}
		else
		{
			$errorSummary = Lang::txt('COM_FORMS_NOTICES_FORM_RESPONSE_SUBMIT_ERROR');
			$forwardingUrl = $this->_routes->formResponseReviewUrl($formId);
			$this->_rCrudHelper->failedBatchUpdate($forwardingUrl, $response, $errorSummary);
		}
	}

	/**
	 * Renders list of users responses
	 *
	 * @return   void
	 */
	public function listTask()
	{
		$currentUserId = User::get('id');
		$responses = FormResponse::all()
			->whereEquals('user_id', $currentUserId)
			->paginated('limitstart', 'limit')
			->rows();
		$responsesListUrl = $this->_routes->usersResponsesUrl();
		$feedItems = ResponseFeedItem::allForUser($currentUserId)
			->order('id', 'desc');

		$this->view
			->set('feedItems', $feedItems)
			->set('responses', $responses)
			->set('listUrl', $responsesListUrl)
			->display();
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

		$isComponentAdmin = $this->_auth->currentCanCreate();

		$this->view
			->set('comment', $comment)
			->set('createCommentUrl', $createCommentAction)
			->set('feedItems', $feedItems)
			->set('form', $form)
			->set('userIsAdmin', $isComponentAdmin)
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
		
		$response = Array(
			'status' => "Saved form json."
		);
		echo json_encode($response);
		exit();
	}
}
