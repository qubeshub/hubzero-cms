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
$responsePermissions = $this->response->getUsergroupPermissions();
$user = $this->response->getUser();
$userId = $user->get('id');
$userName = $user->get('name');
$userIsAdmin = $this->userIsAdmin;

if ($userIsAdmin)
{
	$breadcrumbs = [
		$formName => ['formsDisplayUrl', [$formId]],
		'Manage' => ['formsEditUrl', [$formId]],
		'Responses' => ['formsResponseList', [$formId]],
		$userName => ['responseFeedUrl', [$responseId]]
	];
} else {
	$breadcrumbs = [
		$formName => ['formsDisplayUrl', [$formId]],
		'Responses' => ['usersResponsesUrl', [$formId]]
	];
}
if ($this->task == 'fill') {
	$breadcrumbs['Fill'] = ['formResponseFillUrl', [$responseId]];
} else {
	$breadcrumbs['View'] = ['formResponseViewUrl', [$responseId]];
}

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
						->set('responsePermissions', $responsePermissions)
						->set('userId', $userId)
						->set('userIsAdmin', $userIsAdmin)
						->display();
				?>
			</nav>

			<div class="col span12 omega">
				<?php
					$this->view('_surveyjs_library', 'shared')
						->set('form', $this->form)
						->set('response', $this->response)
						->set('action', $this->task)
						->display();
				?>
			</div>
		</div>

	</div>

	<input type="hidden" id="submitted" value="<?php echo $this->response->get('submitted'); ?>" />
</section>
