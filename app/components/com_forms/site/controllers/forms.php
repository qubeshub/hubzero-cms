<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Site\Controllers;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/formsRouter.php";
require_once "$componentPath/helpers/mockProxy.php";
require_once "$componentPath/helpers/pageBouncer.php";
require_once "$componentPath/helpers/params.php";
require_once "$componentPath/helpers/query.php";
require_once "$componentPath/helpers/relationalCrudHelper.php";
require_once "$componentPath/helpers/relationalSearch.php";
require_once "$componentPath/models/form.php";
require_once "$componentPath/models/formResponse.php";
require_once "$componentPath/models/permissions.php";

use Components\Forms\Helpers\FormsRouter as RoutesHelper;
use Components\Forms\Helpers\MockProxy;
use Components\Forms\Helpers\PageBouncer;
use Components\Forms\Helpers\Params;
use Components\Forms\Helpers\Query;
use Components\Forms\Helpers\RelationalCrudHelper as CrudHelper;
use Components\Forms\Helpers\RelationalSearch as Search;
use Components\Forms\Models\Form;
use Components\Forms\Models\FormResponse;
use Components\Forms\Models\Permissions;
use Hubzero\Component\SiteController;
use Hubzero\Filesystem\Util;
use \Date;
use \User;

class Forms extends SiteController
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
		'archived',
		'closing_time',
		'description',
		'disabled',
		'id',
		'name',
		'opening_time',
		'responses_locked',
	];

	/**
	 * Executes the requested task
	 *
	 * @return   void
	 */
	public function execute()
	{
		$this->bouncer = new PageBouncer([
			'component' => $this->_option
		]);
		$this->crudHelper = new CrudHelper([
			'controller' => $this,
			'errorSummary' => Lang::txt('COM_FORMS_FORM_SAVE_ERROR')
		]);
		$this->name = $this->_controller;
		$this->params = new Params(
			['whitelist' => self::$_paramWhitelist]
		);
		$this->routes = new RoutesHelper();
		$this->search = new Search([
			'class' => new MockProxy(['class' => 'Components\Forms\Models\Form'])
		]);

		parent::execute();
	}

	/**
	 * Renders searchable list of forms
	 *
	 * @return   void
	 */
	public function listTask()
	{
		$formListUrl = $this->routes->formListUrl();
		$searchFormAction = $this->routes->queryUpdateUrl();
		$query = Query::load();

		$forms = $this->search->findBy($query->toArray())
			->paginated('limitstart', 'limit')
			->order('name', 'asc');

		$this->view
			->set('formListUrl', $formListUrl)
			->set('forms', $forms)
			->set('query', $query)
			->set('searchFormAction', $searchFormAction)
			->display();
	}

	/**
	 * Renders new form page [DEPRECATED]
	 *
	 * @param    object   $form   Form to be created
	 * @return   void
	 */
	public function newTask($form = false)
	{
		$this->bouncer->redirectUnlessAuthorized('core.create');

		$createTaskUrl = $this->routes->formsCreateUrl();
		$form = $form ? $form : Form::blank();

		$this->view
			->set('formAction', $createTaskUrl)
			->set('form', $form)
			->display();
	}

	/**
	 * Attempts to create form record using submitted data [DEPRECATED]
	 *
	 * @return   void
	 */
	public function createTask()
	{
		$this->bouncer->redirectUnlessAuthorized('core.create');

		$formData = $this->params->getArray('form');
		$formData['created'] = Date::toSql();
		$formData['created_by'] = User::get('id');

		$form = Form::blank();
		$form->set($formData);

		if ($form->save())
		{
			$formId = $form->get('id');
			$forwardingUrl = $this->routes->formsEditUrl($formId);
			$successMessage = Lang::txt('COM_FORMS_FORM_SAVE_SUCCESS');
			$this->crudHelper->successfulCreate($forwardingUrl, $successMessage);
		}
		else
		{
			$this->crudHelper->failedCreate($form);
		}
	}

	/**
	 * AJAX request to get form json
	 */
	public function getjsonTask()
	{
		$formId = $this->params->get('id');
		$form = Form::oneOrFail($formId);
		$formJson = $form->getJson();

		echo $formJson;
		exit();
	}

	/**
	 * Renders form edit page
	 *
	 * @return   void
	 */
	public function manageTask($form = false)
	{
		$formId = $this->params->get('id');
		$form = $form ? $form : Form::oneOrNew($formId);
		$this->bouncer->redirectUnlessCanEditForm($form);
		
		// Route to updated page with new form id if new
		if ($formId == 0)
		{
			App::redirect($this->routes->formsEditUrl($form->get('id')));
		}

		$this->view
			->set('form', $form)
			->display();
	}

	/**
	 * Delete a form
	 */
	public function deleteTask()
	{
		$formId = $this->params->getInt('id');
		$form = Form::oneOrFail($formId);
		$this->bouncer->redirectUnlessCanEditForm($form);

		if ($form->destroy())
		{
			$forwardingUrl = $this->routes->formListUrl();
			$successMessage = Lang::txt('COM_FORMS_FORM_DELETE_SUCCESS');
			$this->crudHelper->successfulDelete($forwardingUrl, $successMessage);
		}
		else
		{
			$forwardingUrl = $this->routes->formsDisplayUrl($formId);
			$this->crudHelper->failedDelete($forwardingUrl, $form);
		}
	}

	/**
	 * AJAX: Update json for form
	 */
	public function updateTask()
	{
		$formId = $this->params->get('id');
		$form = Form::oneOrFail($formId);
		$form->set('modified', Date::toSql());
		$form->set('modified_by', User::get('id'));

		$formJson = $this->params->get('json');
		if (!$form->setJson($formJson)) {
			$response = Array(
				'status' => "Failed to save form json."
			);
			echo json_encode($response);
			exit();
		}

		// Grab form settings from json
		$json = json_decode($formJson, true);

		// Main form metadata
		$form->set('name', $json['title']);
		$form->set('description', (array_key_exists('description', $json) ? $json['description'] : ''));

		// Access rules
		$form->set('opening_time', (array_key_exists('openingTime', $json) ? $json['openingTime'] : '0000-00-00 00:00:00'));
		$form->set('closing_time', (array_key_exists('closingTime', $json) ? $json['closingTime'] : '0000-00-00 00:00:00'));
		$form->set('responses_locked', (array_key_exists('editable', $json) ? (int)(!$json['editable']) : 0)); // If specified, will be 0 (default is 1); note that database stores opposite
		$form->set('max_responses', (array_key_exists('limitResponses', $json) && $json['limitResponses'] ? (array_key_exists('limitResponseNumber', $json) ? $json['limitResponseNumber'] : 1) : 0));

		// General permissions
		Permissions::setPermissions($form, $json);
		
		if (!$form->save()) {
			$response = Array(
				'status' => "Failed to save form data."
			);
			echo json_encode($response);
			exit();
		}

		// Usergroup permissions
		if (!Permissions::setUsergroupPermissions($form, $json)) {
			$response = Array(
				'status' => "Failed to save form permissions."
			);
			echo json_encode($response);
			exit();
		}

		$response = Array(
			'status' => "Saved form data.",
			'title' => $json['title'] // Sending this back to update breadcrumbs
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
	private function _cleanFilename($filename)
	{
		$filename = preg_replace("/[^A-Za-z0-9.]/i", '-', $filename);

		$ext = strrchr($filename, '.');
		$prefix = substr($filename, 0, -strlen($ext));

		if (strlen($prefix) > 240)
		{
			$prefix = substr($prefix, 0, 240);
			$filename = $prefix . $ext;
		}

		return $filename;
	}

	/**
	 * AJAX: Upload files
	 */
	public function uploadfilesTask()
	{
		$formId = $this->params->get('id');
		$form = Form::oneOrFail($formId);
		$uploadDir = PATH_APP . DS . 'site' . DS . 'forms' . DS . $formId;
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
		$urls = [];
		foreach ($uploads as $file)
		{
			$filename = $this->_cleanFilename($file['name']);
			$filepath = $uploadDir . DS . $filename;

			if (!\Filesystem::upload($file['tmp_name'], $filepath))
			{
				$response = Array(
					'status' => "Failed to move uploaded file."
				);
				echo json_encode($response);
				exit();
			}

			$urls[$file['name']] = Request::root() . 'app' . DS . 'site' . DS . 'forms' . DS . $formId . DS . $filename;
		}

		echo json_encode($urls);
		exit();
	}

	/**
	 * Renders form display page
	 *
	 * @return   void
	 */
	public function displayTask()
	{
		$formId = Request::getInt('id');
		$form = Form::oneOrFail($formId);
		$response = FormResponse::oneWhere([
			'form_id' => $formId,
			'user_id' => User::get('id')
		]);

		$this->view
			->set('form', $form)
			->set('response', $response)
			->display();
	}

}
