<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

// Get block properties
$complete = $this->pub->curation('blocks', $this->step, 'complete');
$props    = $this->pub->curation('blocks', $this->step, 'props');
$required = $this->pub->curation('blocks', $this->step, 'required');
?>

<!-- Load content selection browser //-->
<div id="tagsPick" class="blockelement<?php echo $required ? ' el-required' : ' el-optional';
echo $complete ? ' el-complete' : ' el-incomplete'; ?> freezeblock">
<?php  // Show tags
	echo $this->elHtml; 
	$keywords = $this->pub->getTagsForEditing(array('type' => 'keywords'));
	if ($keywords) {
		echo '<strong>Keywords</strong>: ' . $keywords;
	}
?>
</div>