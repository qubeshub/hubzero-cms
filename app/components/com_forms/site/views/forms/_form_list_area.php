<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$forms = $this->forms->rows();
$sortingAction = $this->sortingAction;
$sortingCriteria = $this->sortingCriteria;
$columns = ['name', 'created', 'modified', 'opening_time', 'closing_time', 'action']; // , 'id', 'accepted'];

echo '<h2>' . Lang::txt('COM_FORMS_HEADINGS_' . ($this->filter == 'shared' ? 'SHARED' : 'MY') . '_FORMS_ALL') . '</h2>';

if (count($forms) > 0):
	$this->view('_form_list', 'shared')
		->set('forms', $forms)
		->set('sortingAction', $sortingAction)
		->set('sortingCriteria', $sortingCriteria)
        ->set('columns', $columns)
		->display();
else:
	$this->view('_form_list_none_notice')
		->display();
endif;
