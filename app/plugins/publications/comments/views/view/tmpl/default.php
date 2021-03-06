<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css()
     ->js();
?>

<?php if ($this->params->get('access-view-comment')) { ?>
	<section class="below section">
		<div class="section-inner">
			<div class="thread">

				<?php if ($this->params->get('access-create-comment')) { ?>
					<?php
					$this->view('commentform')
						 ->set('context', 'new')
						 ->set('url', Route::url($this->url))
						 ->set('file', '')
						 ->set('params', $this->params)
						 ->set('comment', null)
						 ->display();
					?>					
				<?php } ?>

				<?php if ($this->params->get('comments_locked', 0) == 1) { ?>
					<p class="info"><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_LOCKED'); ?></p>
				<?php } ?>

				<div class="container" id="comments">
					<h3 class="post-comment-title">
						<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS'); ?>
					</h3>
					<nav class="entries-filters">
						<ul class="entries-menu order-options">
							<li><a<?php echo ($this->sortby == 'likes') ? ' class="active"' : ''; ?> data-url="<?php echo Route::url($this->obj->link('comments')) . '?sortby=likes'; ?>" title="<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_SORT_BY_LIKES'); ?>"><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_SORT_BY_LIKES'); ?></a></li>
							<li><a<?php echo ($this->sortby == 'created') ? ' class="active"' : ''; ?> data-url="<?php echo Route::url($this->obj->link('comments')) . '?sortby=created'; ?>"  title="<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_SORT_BY_DATE'); ?>"><?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_SORT_BY_DATE'); ?></a></li>
						</ul>
					</nav>
				</div>
				<?php if ($this->comments->count()) {
					$this->view('list')
						->set('option', $this->option)
						->set('comments', $this->comments)
						->set('obj_type', $this->obj_type)
						->set('obj_id', $this->obj_id)
						->set('obj', $this->obj)
						->set('params', $this->params)
						->set('depth', $this->depth)
						->set('sortby', $this->sortby)
						->set('url', $this->url)
						->set('cls', 'odd')
						->display();
				} elseif ($this->depth <= 1) { ?>
					<div class="results-none">
						<p class="no-comments">
							<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_NO_COMMENTS'); ?>
						</p>
					</div>
				<?php } ?>

			</div><!-- / .thread -->
		</div>
	</section><!-- / .below section -->
<?php } else { ?>
	<p class="warning">
		<?php echo Lang::txt('PLG_PUBLICATIONS_COMMENTS_MUST_BE_LOGGED_IN'); ?>
	</p>
<?php } ?>