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

// $breadcrumbs = [
// 	 $formName => ['formsDisplayUrl', [$formId]],
// 	'Fill' => ['formsEditUrl', [$formId]]
// ];
// $this->view('_forms_breadcrumbs', 'shared')
// 	->set('breadcrumbs', $breadcrumbs)
// 	->set('page', Lang::txt('COM_FORMS_FIELDS_MANAGE', $formName))
// 	->display();
?>

<section class="main section">
	<div class="save-notify">
		<span></span>
	</div>

	<div class="grid">
		<div class="row">
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
