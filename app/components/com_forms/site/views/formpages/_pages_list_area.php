<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$pages = $this->pages;

if (count($pages) > 0):
	$this->view('_pages_list')
		->set('pages', $pages)
		->display();
else:
	$this->view('_pages_none_notice')
		->display();
endif;
