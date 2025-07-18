<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$cls = isset($this->cls) ? $this->cls : 'odd';

$this->comment->set('option', $this->option);
$this->comment->set('item_id', $this->obj_id);
$this->comment->set('item_type', $this->obj_type);

if ($this->obj->get('created_by') == $this->comment->get('created_by'))
{
	$cls .= ' author';
}
if ($this->comment->get('anonymous'))
{
	$cls .= ' anonymous';
}

if ($mark = $this->params->get('onCommentMark'))
{
	if ($mark instanceof Closure)
	{
		$marked = (string) $mark($this->comment);
		$cls .= ($marked ? ' ' . $marked : '');
	}
}

$rtrn = $this->url ? $this->url : Request::getVar('REQUEST_URI', 'index.php?option=' . $this->option . '&id=' . $this->obj_id . '&active=comments', 'server');

$this->comment->set('url', $rtrn);

// Get replies
$replies = $this->comment->replies()
	->whereIn('state', array(
		Plugins\Publications\Comments\Models\Comment::STATE_PUBLISHED,
		Plugins\Publications\Comments\Models\Comment::STATE_FLAGGED,
		Plugins\Publications\Comments\Models\Comment::STATE_DELETED
	))
	->whereIn('access', User::getAuthorisedViewLevels());

if ($this->sortby == 'likes') {
	$replies = $replies->order('state', 'asc')
	                   ->order('positive', 'desc');
}
	
$replies = $replies->order('created', 'desc')
					->rows();

$deleted = ($this->comment->get('state') == Plugins\Publications\Comments\Models\Comment::STATE_DELETED);
$author_modified = ($this->comment->get('modified_by') == $this->comment->get('created_by'));
?>

<li class="comment <?php echo $cls; ?>" id="c<?php echo $this->comment->get('id'); ?>">
	<p class="comment-member-photo">
		<img src="<?php echo $this->comment->creator->picture($deleted || $this->comment->get('anonymous')); ?>" alt="" />
	</p>
	<div class="comment-content">
		<?php
		if (!$deleted && $this->params->get('comments_votable', 1))
		{
			$this->view('vote')
				->set('option', $this->option)
				->set('item', $this->comment)
				->set('params', $this->params)
				->set('url', $this->url)
				->display();
		}
		?>

		<?php $action = 'created'; ?>
		<p class="comment-title">
			<?php echo (!$deleted ? '<strong>' : '<em>'); ?>
				<?php if ($deleted) {
					echo ($author_modified ? Lang::txt('PLG_PUBLICATIONS_COMMENTS_DELETED_AUTHOR') : Lang::txt('PLG_PUBLICATIONS_COMMENTS_DELETED_ADMIN'));
					$action = 'modified';
				} elseif (!$this->comment->get('anonymous')) { ?>
					<?php if (in_array($this->comment->creator->get('access'), User::getAuthorisedViewLevels())) { ?>
						<a href="<?php echo Route::url($this->comment->creator->link()); ?>"><!--
							--><?php echo $this->escape(stripslashes($this->comment->creator->get('name'))); ?><!--
						--></a>
					<?php } else { ?>
						<?php echo $this->escape(stripslashes($this->comment->creator->get('name'))); ?>
					<?php } ?>
				<?php } else { ?>
					<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_ANONYMOUS'); ?>
				<?php } ?>
			<?php echo (!$deleted ? '</strong>' : '</em>'); ?>

			<span class="permalink">
				<span class="comment-date-at"><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_AT'); ?></span>
				<span class="time"><time datetime="<?php echo call_user_func(array($this->comment, $action)); ?>"><?php echo call_user_func_array(array($this->comment, $action), array('time')); ?></time></span>
				<span class="comment-date-on"><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_ON'); ?></span>
				<span class="date"><time datetime="<?php echo call_user_func(array($this->comment, $action)); ?>"><?php echo call_user_func_array(array($this->comment, $action), array('date')); ?></time></span>
				<?php if ($this->comment->modified > $this->comment->created): ?>
					<span>&nbsp;</span>
					<span>(Edited:</span>
					<?php $action = 'modified'; ?>
					<span class="comment-date-at"><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_AT'); ?></span>
					<span class="time"><time datetime="<?php echo call_user_func(array($this->comment, $action)); ?>"><?php echo call_user_func_array(array($this->comment, $action), array('time')); ?></time></span>
					<span class="comment-date-on"><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_ON'); ?></span>
					<span class="date"><time datetime="<?php echo call_user_func(array($this->comment, $action)); ?>"><?php echo call_user_func_array(array($this->comment, $action), array('date')); ?></time>)</span>
				<?php endif; ?>
			</span>
		</p>

		<?php if (!$deleted): ?>
			<div class="comment-body">
				<?php
				if ($this->comment->isReported())
				{
					echo '<p class="warning">' . Lang::txt('PLG_PUBLICATIONS_COMMENTS_REPORTED_AS_ABUSIVE') . '</p>';
				}
				else
				{
					echo $this->comment->content;
				}
				?>
			</div><!-- / .comment-body -->
		<?php endif; ?>

		<?php if (!$this->comment->isReported() && !$deleted) { ?>
			<div class="comment-attachments">
				<?php
				foreach ($this->comment->files()->rows() as $attachment)
				{
					if (!trim($attachment->get('description') ?: ''))
					{
						$attachment->set('description', $attachment->get('filename'));
					}

					if ($attachment->isImage())
					{
						if ($attachment->width() > 400)
						{
							$html = '<p><a href="' . Route::url($attachment->link()) . '" rel="lightbox"><img src="' . Route::url($attachment->link()) . '" alt="' . $attachment->get('description') . '" width="400" /></a></p>';
						}
						else
						{
							$html = '<p><img src="' . Route::url($attachment->link()) . '" rel="lightbox" alt="' . $attachment->get('description') . '" /></p>';
						}
					}
					else
					{
						$html = '<p class="attachment"><a href="' . Route::url($attachment->link()) . '" title="' . $attachment->get('description') . '">' . $attachment->get('description') . '</a></p>';
					}

					echo $html;
				}
				?>
			</div><!-- / .comment-attachments -->
		<?php } ?>

		<?php if (!$this->comment->isReported() && !$deleted) { ?>
			<p class="comment-options">
				<?php if (($this->params->get('access-delete-comment') && $this->comment->get('created_by') == User::get('id')) || $this->params->get('access-manage-comment')) { ?>
					<a class="icon-delete delete" href="<?php echo Route::url($this->comment->link('delete')); ?>" data-txt-confirm="<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_CONFIRM'); ?>"><!--
						--><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_DELETE'); ?><!--
					--></a>
				<?php } ?>
				<?php if (($this->params->get('access-edit-comment') && $this->comment->get('created_by') == User::get('id')) || $this->params->get('access-manage-comment')) { ?>
					<a class="icon-edit edit" data-txt-active="<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_CANCEL'); ?>" data-txt-inactive="<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_EDIT'); ?>" href="#" rel="edit-form<?php echo $this->comment->get('id'); ?>"><!--
						--><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_EDIT'); ?><!--
					--></a>
				<?php } ?>
				<?php if ($this->params->get('access-create-comment') && $this->depth < $this->params->get('comments_depth', 3)) { ?>
					<a class="icon-reply reply" data-txt-active="<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_CANCEL'); ?>" data-txt-inactive="<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_REPLY'); ?>" href="#" rel="reply-form<?php echo $this->comment->get('id'); ?>"><!--
						--><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_REPLY'); ?><!--
					--></a>
				<?php } ?>
					<a class="icon-copy copy">
						<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_COPY_LINK'); ?>
						<textarea class="js-copytextarea"><?php echo rtrim(Request::base(), '/') . Route::url($this->comment->link('permalink')); ?></textarea>
					</a>
					<a class="icon-abuse abuse" data-txt-flagged="<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_REPORTED_AS_ABUSIVE'); ?>" href="<?php echo Route::url($this->comment->link('report')); ?>"><!--
						--><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_REPORT_ABUSE'); ?><!--
					--></a>
			</p><!-- / .comment-options -->
		<?php } ?>
		<?php if ($this->depth < $this->params->get('comments_depth', 3)) {
			$this->view('commentform')
				 ->set('context', 'reply')
				 ->set('url', Route::url($this->comment->link('base')))
				 ->set('file', '')
				 ->set('params', $this->params)
				 ->set('comment', $this->comment)
				 ->display();
		}
		$files = $this->comment->files()->rows();
		$this->view('commentform')
			 ->set('context', 'edit')
			 ->set('url', Route::url($this->comment->link('base')))
			 ->set('file', ($files->count() ? $files->first()->get('filename') : ''))
			 ->set('params', $this->params)
			 ->set('comment', $this->comment)
			 ->display(); 
		?>
	</div><!-- / .comment-content -->

	<?php
	if (($this->depth < $this->params->get('comments_depth', 3)) && $replies->count())
	{	
		$this->view('list')
			->set('option', $this->option)
			->set('comments', $replies)
			->set('obj_type', $this->obj_type)
			->set('obj_id', $this->obj_id)
			->set('obj', $this->obj)
			->set('params', $this->params)
			->set('depth', $this->depth)
			->set('sortby', $this->sortby)
			->set('url', $this->url)
			->set('cls', $cls)
			->display();
	}
	?>
</li>