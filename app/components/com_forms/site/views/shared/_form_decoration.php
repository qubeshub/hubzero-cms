<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$decoration = $this->element;
$type = $this->type;

$decorationTypeMap = [
	'header' => '_form_decoration_header',
	'hidden' => '_form_decoration_hidden',
	'paragraph' => '_form_decoration_paragraph'
];

$partialName = $decorationTypeMap[$type];

$this->view($partialName)
	->set('decoration', $decoration)
	->display();
