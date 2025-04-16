<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$responses = $this->responses->rows();
$form = $this->form;
$sortingAction = $this->sortingAction;
$sortingCriteria = $this->sortingCriteria;
$columns = ['created', 'modified', 'submitted', 'completion_percentage', 'action']; // , 'id', 'accepted'];
if (!$form) {
	array_splice( $columns, 2, 0, array('form') );
}

if ($form) {
	echo '<h2>' . Lang::txt('COM_FORMS_HEADINGS_' . ($this->filter == 'shared' ? 'SHARED' : 'MY') . '_RESPONSES_FORM', $form->get('name')) . '</h2>';
} else {
	echo '<h2>' . Lang::txt('COM_FORMS_HEADINGS_' . ($this->filter == 'shared' ? 'SHARED' : 'MY') . '_RESPONSES_ALL') . '</h2>';
}
if (count($responses) > 0):
	$this->view('_response_list', 'shared')
		->set('responses', $responses)
		->set('formId', ($form ? $form->get('id') : 0))
		->set('sortingAction', $sortingAction)
		->set('sortingCriteria', $sortingCriteria)
        ->set('columns', $columns)
		->display();
else:
	$this->view('_response_list_none_notice')
		->display();
endif;
