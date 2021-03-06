<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$classes = isset($this->classes) ? $this->classes : '';
$text = Lang::txt($this->textKey);
$urlFunction = $this->urlFunction;
$urlFunctionArgs = $this->urlFunctionArgs;

$this->view('_link')
	->set('classes', $classes)
	->set('content', $text)
	->set('urlFunction', $urlFunction)
	->set('urlFunctionArgs', $urlFunctionArgs)
	->display();
