<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('pageForm');

$action = $this->action;
$form = $this->form;
$formId = $form->get('id');
$formName = $form->get('name');
$page = $this->page;
$submitValue = Lang::txt('COM_FORMS_FIELDS_VALUES_CREATE_PAGE');

$breadcrumbs = [
	$formName => ['formsDisplayUrl', [$formId]],
	'New Page' => ['formsPagesNewUrl', [$formId]]
];
$this->view('_forms_breadcrumbs', 'shared')
	->set('breadcrumbs', $breadcrumbs)
	->set('page', 'New Page')
	->display();
?>

<section class="main section">
	<div class="grid">

		<?php
			$this->view('_page_form')
				->set('action', $action)
				->set('page', $page)
				->set('submitValue', $submitValue)
				->display();
		?>

	</div>
</section>
