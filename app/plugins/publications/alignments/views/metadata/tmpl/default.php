<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
?>
<h4>Alignments</h4>
<div class="pub-content">
    <?php
    $html = ""; 
    if ($this->fas) {
        $html .= "<ol class='tags top'>";
        foreach ($this->fas as $fa) {
            $html .= "<li class='top-level'><a class='tag' href='" . Route::url('index.php?option=com_publications&id=' . $this->publication->id . '&active=alignments#fa-' . $fa->tag->tag) . "'>" . $fa->label . '</a></li>';
        }
        $html .= "</ol>";
        echo $html;
    } ?>
</div>