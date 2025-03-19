<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$responses = $this->responses->rows();
$formId = $this->formId;
$sortingAction = $this->sortingAction;
$sortingCriteria = $this->sortingCriteria;

if (count($responses) > 0):
	$this->view('_response_list', 'shared')
		->set('responses', $responses)
		->set('formId', $formId)
		->set('sortingAction', $sortingAction)
		->set('sortingCriteria', $sortingCriteria)
		->set('columns', ['id', 'user_id', 'completion_percentage', 'created', 'modified', 'submitted', 'accepted', 'reviewed_by'])
		->display();
else:
	$this->view('_response_list_none_notice')
		->display();
endif;
