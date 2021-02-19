<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

foreach ($item_fmns as $fmn)
{
	// Get group associated with FMN for logo
	$group = Hubzero\User\Group::getInstance($fmn->group_cn);
	$path = PATH_APP . '/site/groups/' . $group->get('gidNumber') . '/uploads/' . $group->get('logo');
	$logo = ($group->get('logo') ? with(new Hubzero\Content\Moderator($path))->getUrl() : '');

	// Get status of fmn and set as class for element
	// Also set tag if autotagging and not overriden by user
	$tag = $item["tag"];
	$set_tag = (!$tag) && ($this->autotag);
	if ($this->_isFuture($fmn->start_date)) {
		$cls = ' upcoming';
		$tag = ($set_tag ? 'Upcoming FMN' : $tag);
		if ($fmn->reg_status) {
			$cls .= ' open';
			$tag = ($set_tag ? $tag . ' - Open for applications!' : $tag);
		}
	} elseif ($this->_isPast($fmn->start_date) &&
						$this->_isFuture($fmn->stop_date)) {
			$cls = ' current';
			$tag = ($set_tag ? 'Current FMN' : $tag);
	} else {
		$cls = '';
	}

	echo '<div class="' . $item['class'] . ' fmn' . $cls . '">
';
  echo '  <a href="' . Route::url('groups' . DS . $fmn->group_cn) . '" aria-hidden="true" tabindex="-1">';
  echo '    <div class="fmn-img">';
	if ($logo) {
		echo '      <img src="' . $logo . '" alt="' . $fmn->name . '" />';
	}
	echo '    </div>';
	echo '  </a>';

	if ($tag) {

		echo '    <div class="fmn-tag">';
		echo '      <span>' . $tag . '</span>';
		echo '    </div>';
	}

	echo '  <div class="fmn-title">';
	echo '    <a href="' . Route::url('groups' . DS . $fmn->group_cn) . '">';
	echo '      <span>' . $fmn->name . '</span>';
	echo '    </a>';
	echo '  </div>';
	echo '</div>';
}
?>
