<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formForm');

$formId = $this->form->get('id');
$formName = $this->form->get('name');
$responseId = $this->response->get('id');
$user = $this->response->getUser();
$userId = $user->get('id');
$userIsAdmin = $this->userIsAdmin;

$breadcrumbs = [
	 $formName => ['formsDisplayUrl', [$formId]],
	'Fill' => ['formsEditUrl', [$formId]]
];
$this->view('_forms_breadcrumbs', 'shared')
	->set('breadcrumbs', $breadcrumbs)
	->set('page', Lang::txt('COM_FORMS_FIELDS_MANAGE', $formName))
	->display();
?>

<section class="main section">
	<div class="save-notify">
		<span></span>
	</div>

	<div class="grid">
		<div class="row">
			<nav class="col span12 nav omega">
				<?php
					$this->view('_response_details_nav', 'shared')
						->set('current', 'Response')
						->set('formId', $formId)
						->set('responseId', $responseId)
						->set('userId', $userId)
						->set('userIsAdmin', $userIsAdmin)
						->display();
				?>
			</nav>

			<div class="col span12 omega">
				<?php
					$this->view('_surveyjs_library')
						->set('form', $this->form)
						->set('response', $this->response)
						->display();
				?>
			</div>
		</div>

	</div>
</section>
