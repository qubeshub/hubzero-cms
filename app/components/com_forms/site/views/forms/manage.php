<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formForm');

$form = $this->form;
$formId = $form->get('id');
$formName = $form->get('name');
$submitValue = Lang::txt('COM_FORMS_FIELDS_VALUES_UPDATE_FORM');

$breadcrumbs = [
	 $formName => ['formsDisplayUrl', [$formId]],
	'Manage' => ['formsEditUrl', [$formId]]
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
			<div class="col span12 omega">
				<?php
					$this->view('_form_edit_nav', 'shared')
						->set('current', 'Form')
						->set('formId', $formId)
						->display();
				?>
			</div>
		</div>

		<div class="row">
			<div class="col span12 omega">
				<?php
					$this->view('_form_form')
						->set('form', $form)
						->display();
				?>
			</div>
		</div>

	</div>
</section>
