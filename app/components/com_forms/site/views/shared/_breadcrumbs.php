<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$breadcrumbs = $this->breadcrumbs;
$page = $this->page;

foreach ($breadcrumbs as $text => $url)
{
	Pathway::append($text, $url);
}

Document::setTitle($page);

