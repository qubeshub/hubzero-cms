<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

foreach ($item_pubs as $pub_info)
{
	$pub = new Components\Publications\Models\Publication(null, 'default', $pub_info->version_id);

	echo '<div class="' . $item['class'] . ' resource' . ($item["featured"] ? ' featured' : '') . '">
';
	echo '  <a href="' . $pub->link('versionid') . '" aria-hidden="true" tabindex="-1">';
	echo '    <div class="resource-img">';
	echo '      <img src="' . Route::url($pub->link('masterimage')) . '" alt="">';
	echo '    </div>';
	echo '  </a>';
	if ($item['tag']) {
		if ($item['tag-target'])
		{
			echo '  <a href="' . $item['tag-target'] . '">';
		}
		echo '    <div class="resource-tag">';
		echo '      <span>' . $item['tag'] . '</span>';
		echo '    </div>';
		if ($item['tag-target'])
		{
			echo '  </a>';
		}
	}
	echo '  <div class="resource-title">';
	echo '    <a href="' . $pub->link('versionid') . '">';
	echo '      <span>' . $pub->get('title') . '</span>';
	echo '    </a>';
	echo '  </div>';
	echo '</div>';
}
?>
