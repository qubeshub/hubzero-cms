<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

foreach ($item_newsletters as $newsletter)
{
	// https://stackoverflow.com/a/21947465
	if ($newsletter->template_id === '-1') {
		$content = $newsletter->html_content;
	} else {
		// Loop through primary stories until find one that is not deleted
		foreach($newsletter->primary()->rows() as $primary) {
			if ($primary->deleted == 0) {
				$content = $primary->story;
				break;
			}
		}
	}
	preg_match("/\<img.+src\=(?:\"|\')(.+?)(?:\"|\')(?:.+?)\>/", $content, $matches);
	if (count($matches) > 1) {
		$img_link = $matches[1];
	} else {
		$img_link = '';
	}

	echo '<div class="' . $item['class'] . ' newsletter' . ($item["featured"] ? ' featured' : '') . '">
';
  echo '  <a href="' . Route::url('index.php?option=com_newsletter&id=' . $newsletter->id) . '" aria-hidden="true" tabindex="-1">';
  echo '    <div class="newsletter-img">';
	echo '      <img src="' . $img_link . '" alt="">';
	echo '    </div>';
	echo '  </a>';
	if ($item['tag']) {
		if ($item['tag-target'])
		{
			echo '  <a href="' . $item['tag-target'] . '">';
		}
		echo '    <div class="newsletter-tag">';
		echo '      <span>' . $item['tag'] . '</span>';
		echo '    </div>';
		if ($item['tag-target'])
		{
			echo '  </a>';
		}
	}
	echo '  <div class="newsletter-title">';
	echo '    <a href="' . Route::url('index.php?option=com_newsletter&id=' . $newsletter->id) . '">';
	echo '      <span>' . $newsletter->name . '</span>';
	echo '    </a>';
	echo '  </div>';
	echo '</div>';
}
?>
