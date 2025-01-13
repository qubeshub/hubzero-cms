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

use Components\Forms\Helpers\FormsRouter as RoutesHelper;
use Components\Forms\Helpers\MockProxy;
use Components\Forms\Helpers\PageBouncer;
use Components\Forms\Helpers\Params;
use Components\Forms\Helpers\Query;
use Components\Forms\Helpers\RelationalCrudHelper as CrudHelper;
use Components\Forms\Helpers\RelationalSearch as Search;
use Components\Forms\Models\Form;
use Components\Forms\Models\FormResponse;
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
		$this->bouncer->redirectUnlessAuthorized('core.create');

		$formId = $this->params->get('id');
		$form = $form ? $form : Form::oneOrNew($formId);
		
		// Route to updated page with new form id if new
		if ($formId == 0)
		{
			App::redirect($this->routes->formsEditUrl($form->get('id')));
		}

		$updateTaskUrl = $this->routes->formsUpdateUrl($formId);

		$this->view
			->set('formAction', $updateTaskUrl)
			->set('form', $form)
			->display();
	}

	/**
	 * Delete a form
	 */
	public function deleteTask()
	{
		$this->bouncer->redirectUnlessAuthorized('core.delete');

		$formId = $this->params->getInt('id');
		$form = Form::oneOrFail($formId);

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
	 * AJAX: Handles updating of given form using provided data
	 *
	 * @return   void
	 */
	public function updateTask()
	{
		// $this->bouncer->redirectUnlessAuthorized('core.create'); 

		$formId = $this->params->get('id');
		$formData = $this->params->getArray('form');
		$formData['modified'] = Date::toSql();
		$formData['modified_by'] = User::get('id');

		$form = Form::oneOrFail($formId);
		$form->set($formData);

		if ($form->save())
		{
			$response = Array(
				'status' => "Saved form settings."
			);
			echo json_encode($response);
			exit();
			// $forwardingUrl = $this->routes->formsEditUrl($formId);
			// $successMessage = Lang::txt('COM_FORMS_FORM_SAVE_SUCCESS');
			// $this->crudHelper->successfulUpdate($forwardingUrl, $successMessage);
		}
		else
		{
			// $this->crudHelper->failedUpdate($form);
		}
	}

	/**
	 * AJAX: Update json for form
	 */
	public function updatejsonTask()
	{
		$formId = $this->params->get('id');
		$form = Form::oneOrFail($formId);
		
		$formJson = $this->params->get('json');
		if (!$form->setJson($formJson)) {
			$response = Array(
				'status' => "Failed to save form json."
			);
			echo json_encode($response);
			exit();
		}

		// Grab title and description
		$json = json_decode($formJson, false);
		$form->set('name', $json->title); // Title should be required

		// Description is optional
		if (isset($json->description)) {
			$form->set('description', $json->description);
		}
		
		if (!$form->save()) {
			$response = Array(
				'status' => "Failed to save form title and description."
			);
			echo json_encode($response);
			exit();
		}

		$response = Array(
			'status' => "Saved form json.",
			'title' => $json->title // Sending this back to update breadcrumbs
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
