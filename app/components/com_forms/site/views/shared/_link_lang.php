<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$classes = isset($this->classes) ? $this->classes : '';
$confirm = isset($this->confirm) ? $this->confirm : false;
$text = Lang::txt($this->textKey);
$urlFunction = $this->urlFunction;
$urlFunctionArgs = $this->urlFunctionArgs;

$this->view('_link')
	->set('classes', $classes)
	->set('content', $text)
	->set('confirm', $confirm)
	->set('urlFunction', $urlFunction)
	->set('urlFunctionArgs', $urlFunctionArgs)
	->display();
