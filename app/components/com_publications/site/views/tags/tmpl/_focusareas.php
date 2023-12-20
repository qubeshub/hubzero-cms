<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

$mtype = (new \Components\Publications\Models\Publication(null, 'default', $this->scope_id))->master_type;
$fas = \Components\Tags\Models\FocusArea::fromTags($this->tags);
$roots = $fas->parents(true)->orderByAlignment($mtype, 'P');

$html = '';
foreach ($roots as $root) {
    $html .= '<h5 id="fa-' . $root->tag->tag . '">' . $root->label . '</h5>';
    $html .= $root->render('view', array('selected' => $fas));
}

echo $html;

return;