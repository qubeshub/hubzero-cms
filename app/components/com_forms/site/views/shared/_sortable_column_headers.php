<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$columns = $this->columns;
$sortingCriteria = $this->sortingCriteria;

foreach ($columns as $field => $title):
	// Skip the column if it is not sortable
	if (in_array($field, ['action'])) {
		echo "<td>$title</td>";
	} else {
		$this->view('_sortable_column_header')
			->set('field', $field)
			->set('title', $title)
			->set('sortingCriteria', $sortingCriteria)
			->display();
	}
endforeach;
