<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$form = $this->form;
$formId = $form->get('id');
// onclick="return confirm('Are you sure?')
$this->view('_protected_link', 'shared')
	->set('authArgs', [$form])
	->set('authMethod', 'canCurrentUserEditForm')
    ->set('confirm', 'Are you sure you want to delete this form?')
	->set('textKey', 'COM_FORMS_FIELDS_VALUES_DELETE_FORM')
	->set('urlFunction', 'formsDeleteUrl')
	->set('urlFunctionArgs', [$formId])
	->display();
