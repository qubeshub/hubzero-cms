<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$componentPath = \Component::path('com_forms');

require_once "$componentPath/helpers/formsAuth.php";

use Components\Forms\Helpers\FormsAuth;

$formsAuth = new FormsAuth();

$authArgs = isset($this->authArgs) ? $this->authArgs : [];
$authMethod = $this->authMethod;
$classes = isset($this->classes) ? $this->classes : '';
$confirm = isset($this->confirm) ? $this->confirm : false;
$isAuthorized = $formsAuth->$authMethod(...$authArgs);
$textKey = $this->textKey;
$urlFunction = $this->urlFunction;
$urlFunctionArgs = isset($this->urlFunctionArgs) ? $this->urlFunctionArgs : [];

if ($isAuthorized):
	$this->view('_link_lang')
		->set('classes', $classes)
		->set('textKey', $textKey)
		->set('confirm', $confirm)
		->set('urlFunction', $urlFunction)
		->set('urlFunctionArgs', $urlFunctionArgs)
		->display();
endif;
