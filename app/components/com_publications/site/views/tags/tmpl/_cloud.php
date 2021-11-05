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
$tll = array();
foreach ($this->tags as $tag) {
    $last_tag = end($tag);
    $first_tag = reset($tag);
    foreach($tag as $raw => $subtag) {
        $classes = trim(implode(" ", array($subtag === $first_tag ? 'top-level' : null, $subtag !== $last_tag ? 'sub-tags' : null)));
        // Opening...
        $tll[] = '<li' . ($classes ? ' class="' . $classes . '"' : '') . '>';
        $tll[] = '<a class="tag" href="' . Route::url('index.php?option=com_tags&tag=' . $last_tag) . '">' . $this->escape(stripslashes($raw)) . '</a>';
        if ($subtag !== $last_tag) {
            $tll[] = '<ol class="tags">';
        }
    }
    // ...Closing
    $tll[] = '</li>';
    for ($x = 2; $x <= count($tag); $x++) {
        $tll = array_merge($tll, array('</ol>', '</li>'));
    }
}

$html  = '<ol class="tags top">' . "\n";
$html .= implode("\n", $tll);
$html .= '</ol>' . "\n";

echo $html;
