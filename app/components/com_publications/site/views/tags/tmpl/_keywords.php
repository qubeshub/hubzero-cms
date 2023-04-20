<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

if (!count($this->tags))
{
    echo '';
    return;
}

// build HTML
$html = '<ol class="tags top">';
foreach ($this->tags as $tag) {
    $html .= '<li class="top-level">';
    $html .= '<a class="tag" href="' . Route::url('/com_publications/browse?search=' . urlencode($tag->get('raw_tag'))) . '">' . $this->escape(stripslashes($tag->get('raw_tag'))) . '</a>';
    $html .= '</li>';
}
$html .= '</ol>';

echo $html;
