<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

$html = '';

// Grandchildren?
$ptag = $this->parent->tag->get('tag');
$gchildren = false;
foreach ($this->children as $child)
{
    if ($child->children()->count() > 0)
    {
        $gchildren = true;
        break;
    }
}

// If grandchildren, do dropdown
$active = in_array($ptag, $this->props['filters']);
if ($gchildren)
{
    $display = ($this->depth > 1  && !$active ? ' style="display:none;"' : '');
    $html .= '<div class="fad" for="tagfa-' . $ptag . '"' . $display . '>';
    $html .= '<select name="tagfa-' . $this->props['root'] . '[' . $ptag . '][]" id="tagfa-' . $ptag . '" class="option">';
    $html .= '<option value="">-None selected-</option>';
    foreach ($this->children as $child)
    {
        $ctag = $child->tag->get('tag');
        $selected = in_array($ctag, $this->props['filters']);
        $count = $this->props['facets'][$ptag . '.' . $ctag];
        $html .= '<option value="' . $ptag . '.' . $ctag . '"' . ($count == 0 ? ' hidden' : '') . ($selected ? ' selected' : '') . '>' . $child->label . ' (' . $count . ')</option>';
    }
    $html .= '</select>';
} else {
    // Do checkboxes
    $html .= '<div class="fac" for="tagfa-' . $ptag  . '"' . ($this->depth > 1 && !$active ? ' style="display:none;"' : '') . '>';
    $html .= '<ul>';
    foreach ($this->children as $child)
    {
        $ctag = $child->tag->get('tag');
        $checked = in_array($ctag, $this->props['filters']);
        $count = $this->props['facets'][$ptag . '.' . $ctag];
        $html .= '<li class="filter-option"' . ($count == 0 ? ' style="display:none;"' : '') . '>';
        $html .= '<input class="option" type="checkbox" id="tagfa-' . $ctag . '" name="tagfa-' . $this->props['root'] . '[' . $ptag . '][]" value="' . $ptag . '.' . $ctag . '"' . ($checked ? ' checked' : '');
        $html .= ' /><label style="display: inline;" for="tagfa-' . $ctag . '"' . ($child->about ? ' title="' . htmlentities($child->about) . '" class="tooltips"' : '') . '>' . $child->label . ' (' . $count . ')</label>';
        $html .= '</li>';
    }
    $html .= '</ul>';
}

echo $html;

return;