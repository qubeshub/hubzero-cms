<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formDisplay');

$form = $this->form;
$formId = $form->get('id');
$formName = $form->get('name');
$response = $this->response;

$this->view('_forms_breadcrumbs', 'shared')
	->set('breadcrumbs', [$formName => ['formsDisplayUrl', [$formId]]])
	->set('page', $formName)
	->display();
?>

<section class="main section">
	<div class="landing-header">
	<?php
		$this->view('_protected_link', 'shared')
			->set('authArgs', [$form])
			->set('authMethod', 'canCurrentUserFillForm')
			->set('textKey', 'Start new response')
			->set('urlFunction', 'formResponseStartUrl')
			->set('urlFunctionArgs', [$formId])
			->set('classes', 'icon-edit btn')
			->display();

        $this->view('_link_lang', 'shared')
			->set('textKey', 'COM_FORMS_LINKS_MY_RESPONSES')
			->set('urlFunction', 'usersResponsesUrl')
			->set('urlFunctionArgs', [])
			->set('classes', 'icon-list btn')
			->display();
            
        $this->view('_protected_link', 'shared')
			->set('authArgs', [$form])
			->set('authMethod', 'canCurrentUserEditForm')
            ->set('textKey', 'COM_FORMS_FIELDS_VALUES_EDIT_FORM')
            ->set('urlFunction', 'formsEditUrl')
            ->set('urlFunctionArgs', [$formId])
            ->set('classes', 'icon-cog btn')
            ->display();

		$this->view('_protected_link', 'shared')
			->set('authArgs', [$form])
			->set('authMethod', 'canCurrentUserEditForm')
			->set('confirm', 'Are you sure you want to delete this form?')
            ->set('textKey', 'COM_FORMS_FIELDS_VALUES_DELETE_FORM')
            ->set('urlFunction', 'formsDeleteUrl')
            ->set('urlFunctionArgs', [$formId])
            ->set('classes', 'icon-delete btn')
            ->display();
    ?>
	</div>

	<div class="grid">
		<div class="row">

			<div class="col span7">
				<div>
					<?php
						$this->view('_form_overview')
							->set('form', $form)
							->set('response', $response)
							->display();
					?>
				</div>

				<div class="form-response-link">
					<?php
						$this->view('_form_response_link')
							->set('form', $form)
							->set('response', $response)
							->display();
					?>
				</div>
			</div>

		</div>
	</div>
</section>
