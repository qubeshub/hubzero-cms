<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$name = $this->name;
$data = $this->data;
$operatorValue = $data->getOperator();
$dateValue = $data->getValue();

$this->view('_relative_date_fields', 'shared')
	->set('selectFieldName', "query[$name][operator]")
	->set('relativeOperatorValue', $operatorValue)
	->set('dateFieldName', "query[$name][value]")
	->set('dateValue', $dateValue)
	->display();
