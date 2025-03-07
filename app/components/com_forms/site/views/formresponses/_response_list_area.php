<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$responses = $this->responses->rows();
$sortingAction = $this->sortingAction;
$sortingCriteria = $this->sortingCriteria;

if (count($responses) > 0):
	$this->view('_response_list', 'shared')
		->set('responses', $responses)
		->set('sortingAction', $sortingAction)
		->set('sortingCriteria', $sortingCriteria)
        ->set('columns', ['id', 'form', 'completion_percentage', 'created', 'modified', 'submitted', 'accepted'])
		->display();
else:
	$this->view('_response_list_none_notice')
		->display();
endif;
