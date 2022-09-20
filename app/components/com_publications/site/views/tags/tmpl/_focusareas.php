<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

$fas = \Components\Tags\Models\FocusArea::fromTags($this->tags);
$roots = $fas->parents(true); // Get root FAs

$html = '';
foreach ($roots as $root) {
    $html .= '<h5 id="fa-' . $root->tag->tag . '">' . $root->label . '</h5>';
    $html .= $root->render('view', array('selected' => $fas));
}

echo $html;

return;