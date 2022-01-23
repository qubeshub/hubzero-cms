<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();
?>
<li class="post-mini">
	<div class="postdisp">
		<a class="<?php echo (is_null($post->special) ? 'comment' : '') ?>" href="<?php echo (is_null($post->special) ? $post->post_url : $post->special['link_url']) ?>">
			<div class="post-ids">
				<div class="post-img"><?php echo $post->img_html; ?></div>
				<div class="post-name truncate"><?php echo $post->title; ?></div>
			</div>
		</a>
		<div class="post-actions">
			<?php if ($post->special) { ?>
				<a href="<?php echo $post->special['link_url']; ?>">
					<span class="action-<?php echo $post->special['class']; ?>"><?php echo $post->special['link_name']; ?></span>
				</a>
			<?php } ?>
			<a class="comment" href="<?php echo $post->post_url; ?>">
				<span class="action-post">Post</span>
			</a>
		</div>
   </div>
</li>
