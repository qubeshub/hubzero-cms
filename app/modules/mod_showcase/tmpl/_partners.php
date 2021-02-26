<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

foreach ($item_partners as $partner)
{
	echo '<div class="' . $item['class'] . ' resource' . ($item["featured"] ? ' featured' : '') . '">
';
  echo '  <a href="' . Route::url('groups' . DS . $partner->groups_cn) . '" aria-hidden="true" tabindex="-1">';
  echo '    <div class="partner-img">';
	echo '      <img src="app/site/media/images/partners/' . $partner->logo_img . '" alt="">';
	echo '    </div>';
	echo '  </a>';
	if ($item['tag']) {
		if ($item['tag-target'])
		{
			echo '  <a href="' . $item['tag-target'] . '">';
		}
		echo '    <div class="partner-tag">';
		echo '      <span>' . $item['tag'] . '</span>';
		echo '    </div>';
		if ($item['tag-target'])
		{
			echo '  </a>';
		}
	}
	echo '  <div class="partner-title">';
	echo '    <a href="' . Route::url('groups' . DS . $partner->groups_cn) . '">';
	echo '      <span>' . $partner->name . '</span>';
	echo '    </a>';
	echo '  </div>';
	echo '</div>';
}
?>
