<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

defined('_HZEXEC_') or die();

$this->css()
	 ->js();

$this->category->set('section_alias', $this->filters['section']);

$this->thread->set('section', $this->filters['section']);
$this->thread->set('category', $this->category->get('alias'));

$now = Date::of('now')->toSql();
?>
<header id="content-header">
	<h2><?php echo Lang::txt('COM_FORUM'); ?></h2>

	<div id="content-header-extra">
		<p>
			<a class="icon-comments comments btn" href="<?php echo Route::url($this->category->link()); ?>">
				<?php echo Lang::txt('COM_FORUM_ALL_DISCUSSIONS'); ?>
			</a>
		</p>
	</div>
</header>

<section class="main section">
	<div class="section-inner hz-layout-with-aside">
		<div class="subject">
			<h3 class="thread-title<?php echo ($this->thread->get('closed')) ? ' closed' : ''; ?>">
				<?php echo $this->escape(stripslashes($this->thread->get('title'))); ?>
			</h3>

			<?php
			$threading = $this->config->get('threading', 'list');

			$posts = $this->thread->thread()
				->whereIn('state', $this->filters['state'])
				->whereIn('access', $this->filters['access'])
				->order(($threading == 'tree' ? 'lft' : 'id'), 'asc')
				->paginated()
				->rows();

			$pageNav = $posts->pagination;

			if ($posts->count() > 0)
			{
				if ($threading == 'tree')
				{
					$posts = $this->thread->toTree($posts);
				}

				$this->view('_list')
					 ->set('option', $this->option)
					 ->set('controller', $this->controller)
					 ->set('comments', $posts)
					 ->set('thread', $this->thread)
					 ->set('likes', $this->likes)
					 ->set('parent', 0)
					 ->set('config', $this->config)
					 ->set('depth', 0)
					 ->set('cls', 'odd')
					 ->set('filters', $this->filters)
					 ->set('category', $this->category)
					 ->display();
			}
			else
			{
				?>
				<ol class="comments">
					<li>
						<p><?php echo Lang::txt('COM_FORUM_NO_REPLIES_FOUND'); ?></p>
					</li>
				</ol>
				<?php
			}
			?>
			<form action="<?php echo Route::url($this->thread->link()); ?>" method="get">
				<?php
				$pageNav->setAdditionalUrlParam('section', $this->filters['section']);
				$pageNav->setAdditionalUrlParam('category', $this->category->get('alias'));
				$pageNav->setAdditionalUrlParam('thread', $this->thread->get('id'));

				echo $pageNav;
				?>
			</form>

			<?php if (!$this->thread->get('closed')) { ?>
				<h3 class="post-comment-title">
					<?php echo Lang::txt('COM_FORUM_ADD_COMMENT'); ?>
				</h3>
				<form action="<?php echo Route::url($this->thread->link()); ?>" method="post" id="commentform" enctype="multipart/form-data">
					<p class="comment-member-photo">
						<?php
						$anon = (!User::isGuest() ? 0 : 1);
						?>
						<img src="<?php echo User::picture($anon); ?>" alt="<?php echo Lang::txt('COM_FORUM_USER_PHOTO'); ?>" />
					</p>

					<fieldset>
						<?php if (User::isGuest()) { ?>
							<p class="warning"><?php echo Lang::txt('COM_FORUM_LOGIN_COMMENT_NOTICE'); ?></p>
						<?php } else if ($this->config->get('access-create-post')) { ?>
							<p class="comment-title">
								<strong>
									<a href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id')); ?>"><?php echo $this->escape(stripslashes(User::get('name'))); ?></a>
								</strong>
								<span class="permalink">
									<span class="comment-date-at"><?php echo Lang::txt('COM_FORUM_AT'); ?></span>
									<span class="time"><time datetime="<?php echo $now; ?>"><?php echo Date::toLocal(Lang::txt('TIME_FORMAT_HZ1')); ?></time></span>
									<span class="comment-date-on"><?php echo Lang::txt('COM_FORUM_ON'); ?></span>
									<span class="date"><time datetime="<?php echo $now; ?>"><?php echo Date::toLocal(Lang::txt('DATE_FORMAT_HZ1')); ?></time></span>
								</span>
							</p>

							<label for="fieldcomment" id="addNewPostArea">
								<div>
									<?php echo Lang::txt('COM_FORUM_FIELD_COMMENTS'); ?>
									<span class="note" style='float:right'>Use an @ sign to mention users in the post</span>
								</div>
								<?php echo $this->editor('fields[comment]', '', 35, 15, 'fieldcomment',
										array(
											'class' => 'minimal no-footer',
											'mentions' => array(
												array(
													'minChars' => 0,
													'feed' =>  '/api/members/mentions/list?search={encodedQuery}',
													'itemTemplate' => '<li data-id="{id}"><img class="photo" src="{picture}" /><strong class="username">{username}</strong><span class="fullname">{name}</span></li>',
													'outputTemplate' => '<a href="/members/{id}" data-user-id="{id}" target="_blank">@{username}</a>&nbsp;&nbsp;',
												)
											)
										)); ?>
							</label>

							<label>
								<?php echo Lang::txt('COM_FORUM_FIELD_YOUR_TAGS'); ?>
								<?php
									echo $this->autocompleter('tags', 'tags', $this->escape($this->thread->tags('string')), 'actags');
								?>
							</label>

							<fieldset>
								<legend><?php echo Lang::txt('COM_FORUM_LEGEND_ATTACHMENTS'); ?></legend>
								<div class="grid">
									<div class="col span6">
										<label for="upload">
											<?php echo Lang::txt('COM_FORUM_FIELD_FILE'); ?>:
											<input type="file" name="upload" id="upload" />
										</label>
									</div>
									<div class="col span6 omega">
										<label for="field-description">
											<?php echo Lang::txt('COM_FORUM_FIELD_DESCRIPTION'); ?>:
											<input type="text" name="description" id="field-description" value="" />
										</label>
									</div>
								</div>
							</fieldset>

							<?php if ($this->config->get('allow_anonymous')) { ?>
								<label for="field-anonymous" id="comment-anonymous-label">
									<input class="option" type="checkbox" name="fields[anonymous]" id="field-anonymous" value="1" />
									<?php echo Lang::txt('COM_FORUM_FIELD_ANONYMOUS'); ?>
								</label>
							<?php } ?>

							<p class="submit">
								<input type="submit" value="<?php echo Lang::txt('JSUBMIT'); ?>" />
							</p>
						<?php } else { ?>
							<p class="warning"><?php echo Lang::txt('COM_FORUM_PERMISSION_DENIED'); ?></p>
						<?php } ?>

						<div class="sidenote">
							<p>
								<strong><?php echo Lang::txt('COM_FORUM_KEEP_POLITE'); ?></strong>
							</p>
						</div>
					</fieldset>
					<input type="hidden" name="fields[category_id]" value="<?php echo $this->thread->get('category_id'); ?>" />
					<input type="hidden" name="fields[parent]" value="<?php echo $this->thread->get('id'); ?>" />
					<input type="hidden" name="fields[state]" value="1" />
					<input type="hidden" name="fields[access]" value="<?php echo $this->thread->get('access', 0); ?>" />
					<input type="hidden" name="fields[id]" value="" />
					<input type="hidden" name="fields[scope]" value="site" />
					<input type="hidden" name="fields[scope_id]" value="0" />
					<input type="hidden" name="fields[thread]" value="<?php echo $this->thread->get('id'); ?>" />
					<input type="hidden" name="fields[scope_sub_id]" value="<?php echo $this->thread->get('scope_sub_id'); ?>" />
					<input type="hidden" name="fields[object_id]" value="<?php echo $this->thread->get('object_id'); ?>" />

					<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
					<input type="hidden" name="controller" value="threads" />
					<input type="hidden" name="task" value="save" />

					<?php echo Html::input('token'); ?>
				</form>
			<?php } ?>
		</div><!-- / .subject -->

		<aside class="aside">
			<div class="container">
				<h3><?php echo Lang::txt('COM_FORUM_ALL_TAGS'); ?></h3>
				<?php if ($this->thread->tags('cloud')) { ?>
					<?php echo $this->thread->tags('cloud'); ?>
				<?php } else { ?>
					<p><?php echo Lang::txt('COM_FORUM_NONE'); ?></p>
				<?php } ?>
			</div><!-- / .container -->

			<?php
			$participants = $this->thread->participants()
				->whereIn('state', $this->filters['state'])
				->whereIn('access', $this->filters['access'])
				->rows();

			if ($participants->count() > 0) { ?>
				<div class="container">
					<h3><?php echo Lang::txt('COM_FORUM_PARTICIPANTS'); ?></h3>
					<ul>
						<?php
						$anon = false;
						foreach ($participants as $participant)
						{
							if (!$participant->get('anonymous'))
							{
								?>
								<li>
									<a class="member" href="<?php echo Route::url('index.php?option=com_members&id=' . $participant->get('created_by')); ?>">
										<?php echo $this->escape(stripslashes($participant->get('name'))); ?>
									</a>
								</li>
								<?php
							}
							// Only display "anonymous" once
							elseif (!$anon)
							{
								$anon = true;
								?>
								<li>
									<span class="member">
										<?php echo Lang::txt('JANONYMOUS'); ?>
									</span>
								</li>
								<?php
							}
						}
						?>
					</ul>
				</div><!-- / .container -->
			<?php } ?>

			<?php
			$attachments = Components\Forum\Models\Attachment::all()
				->whereEquals('parent', $this->thread->get('thread'))
				->whereIn('state', $this->filters['state'])
				->rows();

			if ($attachments->count() > 0) { ?>
				<div class="container">
					<h3><?php echo Lang::txt('COM_FORUM_ATTACHMENTS'); ?></h3>
					<ul class="attachments">
						<?php
						foreach ($attachments as $attachment)
						{
							if ($attachment->get('status') != 2)
							{

								$cls = 'file';
								$title = trim($attachment->get('description', $attachment->get('filename')));
								$title = ($title ? $title : $attachment->get('filename'));

								// trims long titles
								$title = (strlen($title) > 25) ? substr($title, 0, 22) . '...' : $title;

								if ($attachment->isImage())
								{
									$cls = 'img';
								}
							?>
							<li>
								<a class="<?php echo $cls; ?> attachment" href="<?php echo Route::url($this->thread->link() . '&post=' . $attachment->get('post_id') . '&file=' . $attachment->get('filename')); ?>">
									<?php echo $this->escape(stripslashes($title)); ?>
								</a>
							</li>
							<?php
							} // end status check
						}
						?>
					</ul>
				</div><!-- / .container -->
			<?php } ?>
		</aside><!-- / .aside -->
	</div>
</section><!-- / .below section -->

