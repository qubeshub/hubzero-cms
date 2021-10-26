<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

echo '<pre>' . var_export($this->tags, true) . '</pre>';
die;

if (!count($this->tags))
{
    echo '';
    return;
}

// build HTML
$tll = array();
foreach ($this->tags as $tag => $tag_obj)
{
    $tll[$tag]  = '<li class="top-level' . (count($tag_obj['children']) ? ' sub-tags' : '') . '">';
    $tll[$tag] .= '<a class="tag' . $link_class . '" href="' . Route::url('index.php?option=com_tags&tag=' . $tag->get('tag')) . '">' . $this->escape(stripslashes($tag->get('raw_tag')));
    if ($this->config->get('show_tag_count', 0))
    {
        $tll[$tag->get('tag')] .= ' <span>' . $tag->get('count') . '</span>';
    }
    $tll[$tag->get('tag')] .= '</a>';
    if ($this->config->get('show_sizes') == 1)
    {
        $tll[$tag->get('tag')] .= '</span>';
    }
    $tll[$tag->get('tag')] .= '</li>';
}
if ($this->config->get('show_tags_sort', 'alpha') == 'alpha')
{
    ksort($tll);
}

$html  = '<ol class="tags top">' . "\n";
$html .= implode("\n", $tll);
$html .= '</ol>' . "\n";

echo $html;
