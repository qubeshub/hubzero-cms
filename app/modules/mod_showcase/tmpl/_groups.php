<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

foreach ($item_groups as $grp)
{
	$group = Hubzero\User\Group::getInstance($grp->gidNumber);

	echo '<div class="' . $item['class'] . ' group' . ($item["featured"] ? ' featured' : '') . '">
';
	$path = PATH_APP . '/site/groups/' . $group->get('gidNumber') . '/uploads/' . $group->get('logo');

	if ($group->get('logo') && is_file($path)) {
		echo '  <a href="' . Route::url('index.php?option=com_groups&cn='. $group->get('cn')) . '" aria-hidden="true" tabindex="-1">';
		echo '    <div class="group-img">';
		echo '      <img src="' . with(new Hubzero\Content\Moderator($path))->getUrl() . '" alt="' . $this->escape(stripslashes($group->get('description'))) . '" />';
		echo '    </div>';
		echo '  </a>';
	}
	if ($item['tag']) {
		if ($item['tag-target'])
		{
			echo '  <a href="' . $item['tag-target'] . '">';
		}
		echo '    <div class="group-tag">';
		echo '      <span>' . $item['tag'] . '</span>';
		echo '    </div>';
		if ($item['tag-target'])
		{
			echo '  </a>';
		}
	}
	echo '  <div class="group-description">';
	echo '    <a href="' . Route::url('index.php?option=com_groups&cn='. $group->get('cn')) . '">';
	echo '      <span>' . $this->escape(stripslashes($group->get('description'))) . '</span>';
	echo '    </a>';
	echo '  </div>';

	// echo '    <a href="' . echo Route::url('index.php?option=' . $this->option . '&cn='. $group->get('cn')) . '">';
	// echo '    </a>';
	echo '</div>';
}
?>
