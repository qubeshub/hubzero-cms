<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

$html = '';
$multiple = !is_null($this->props['multiple_depth']) && ($this->props['multiple_depth'] <= $this->depth);
$tag = $this->child->tag->get('tag');
$html .= '<div class="fa' . ($this->depth === 1 ? ' top-level' : '') . '">';
$html .= '<input class="option" class="' . ($multiple ? 'checkbox' : 'radio') . '" type="' . ($multiple ? 'checkbox' : 'radio') . '" ' . (in_array($tag, $this->props['selected']) ? 'checked="checked" ' : '') . 'id="tagfa-' . $tag . '" name="tagfa-' . $this->parent . '[]" value="' . $tag . '"';
$html .= ' /><label style="display: inline;" for="tagfa-' . $tag . '"' . ($this->child->about ? ' title="' . htmlentities($this->child->about) . '" class="tooltips"' : '') . '>' . $this->child->label . '</label>';
$html .= $this->child->render('select', $this->props, ++$this->depth);
$html .= '</div>';

echo $html;

return;