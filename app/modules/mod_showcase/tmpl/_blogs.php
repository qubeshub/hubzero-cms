<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

foreach ($item_blogs as $blog)
{
	// https://stackoverflow.com/a/21947465
	preg_match("/\<img.+src\=(?:\"|\')(.+?)(?:\"|\')(?:.+?)\>/", $blog->get('content'), $matches);
	$img_link = (!empty($matches) ? $matches[1] : Route::url('app/modules/mod_showcase/assets/img/blog_placeholder.jpg'));

	echo '<div class="' . $item['class'] . ' blog' . ($item["featured"] ? ' featured' : '') . '">
';
  echo '  <a href="' . Route::url($blog->link()) . '" aria-hidden="true" tabindex="-1">';
  echo '    <div class="blog-img">';
	echo '      <img src="' . $img_link . '" alt="">';
	echo '    </div>';
	echo '  </a>';
	if ($item['tag']) {
		if ($item['tag-target'])
		{
			echo '  <a href="' . $item['tag-target'] . '">';
		}
		echo '    <div class="blog-tag">';
		echo '      <span>' . $item['tag'] . '</span>';
		echo '    </div>';
		if ($item['tag-target'])
		{
			echo '  </a>';
		}
	}
	echo '  <div class="blog-title">';
	echo '    <a href="' . Route::url($blog->link()) . '">';
	echo '      <span>' . $blog->get('title') . '</span>';
	echo '    </a>';
	echo '  </div>';
	echo '</div>';
}
?>
