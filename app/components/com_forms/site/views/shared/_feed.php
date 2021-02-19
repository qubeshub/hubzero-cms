<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$feedItems = $this->feedItems;
$itemView = $this->itemView;
$noticeView = $this->noticeView;
$subviewsSource = $this->subviewsSource;

foreach ($feedItems as $item):
	$this->view($itemView, $subviewsSource)
		->set('item', $item)
		->display();
endforeach;

if ($feedItems->count() == 0):
	$this->view($noticeView, $subviewsSource)
	->display();
endif;
