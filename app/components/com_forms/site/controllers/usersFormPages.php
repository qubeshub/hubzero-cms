<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Site\Controllers;

require_once "$componentPath/helpers/formsAuth.php";
require_once "$componentPath/helpers/pageBouncer.php";
require_once "$componentPath/helpers/params.php";
require_once "$componentPath/models/form.php";
require_once "$componentPath/models/formResponse.php";

use Components\Forms\Helpers\FormsAuth as AuthHelper;
use Components\Forms\Helpers\PageBouncer;
use Components\Forms\Helpers\Params;
use Components\Forms\Models\Form;
use Components\Forms\Models\FormResponse;
use Hubzero\Component\SiteController;

class UsersFormPages extends SiteController
{

	/**
	 * Parameter whitelist
	 *
	 * @var  array
	 */
	protected static $_paramWhitelist = [
		'form_id',
		'user_id'
	];

	/**
	 * Executes the requested task
	 *
	 * @return   void
	 */
	public function execute()
	{
		$this->_auth = new AuthHelper();
		$this->_bouncer = new PageBouncer(['component' => $this->_option]);
		$this->_params = new Params(['whitelist' => self::$_paramWhitelist]);

		parent::execute();
	}

	/**
	 * Renders list of form's pages pertaining to given user
	 *
	 * @return   void
	 */
	public function listTask()
	{
		$formId = $this->_params->getInt('form_id');
		$form = Form::oneOrFail($formId);
		$userId = $this->_params->getInt('user_id');
		$response = FormResponse::all()
			->whereEquals('form_id', $formId)
			->whereEquals('user_id', $userId)
			->row();

		$this->_bouncer->redirectUnlessCanViewResponse($response);
		$isComponentAdmin = $this->_auth->currentCanCreate();

		$this->view
			->set('form', $form)
			->set('response', $response)
			->set('userIsAdmin', $isComponentAdmin)
			->display();
	}

}
