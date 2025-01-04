<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$base = rtrim(Request::root(), '/');

// Compute some counts for later use
$groups = count($this->posts);
$posts  = 0;

array_walk($this->posts, function($val, $idx) use (&$posts)
{
	$posts += $val->count();
});

// Text for preheader
$preheader = "You have " . $posts . " new post" . ($posts > 1 ? 's' : '') . " across " . $groups . " of your groups (";
$ct = 0;
foreach ($this->posts as $gid => $gposts) {
	$preheader .= ($ct++ > 0 ? '; ' : '') . $gposts->count() . ' new post' . ($gposts->count() > 1 ? 's' : '') . ' in ' . Hubzero\User\Group::getInstance($gid)->get('description');
}
$preheader .= ') | ';
?>

<!-- Start Preheader -->
<span class="preheader"><?php echo $preheader; ?></span>
<!-- End Preheader -->

<!-- Start Header -->
<table class="tbl-header" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr>
			<td align="left" valign="middle" style="width: 24px !important;">
				<img src="https://qubeshub.org/app/site/media/images/emails/comments-solid.png" width="24" height="24" style="border:none;" alt="Comment icon"/>
			</td>
			<td align="left" valign="bottom" nowrap="nowrap" class="component left" style="padding: 5px 10px 5px 10px !important;">
				Group Forum
			</td>
			<td width="90%" align="right" valign="bottom" nowrap="nowrap" class="sitename group">
				<?php echo ucfirst($this->interval); ?> Digest
			</td>
		</tr>
	</tbody>
</table>
<!-- End Header -->

<!-- Start Spacer -->
<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr>
			<td height="30"></td>
		</tr>
	</tbody>
</table>
<!-- End Spacer -->

<table id="digest-info" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; background-color: #F3F3F3; border: 1px solid #DDDDDD;">
	<tr>
		<td width="85" style="padding: 0 0 0 15px;">
			<img width="80" src="<?php echo Request::root() . '/core/components/com_forum/site/assets/img/group.png'; ?>" />
		</td>
		<td width="100%" style="padding: 14px;">
			<span style="font-weight: bold;">Your <?php echo $this->interval; ?> group discussion digest</span>
			<hr />
			<span>You have <?php echo $posts; ?> new post<?php if ($posts > 1) { echo 's'; } ?> across <?php echo $groups; ?> of your groups</span>
		</td>
	</tr>
</table>

<!-- Start Spacer -->
<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr>
			<td height="20"></td>
		</tr>
	</tbody>
</table>
<!-- End Spacer -->

<?php $ct = 0; ?>
<?php foreach ($this->posts as $gid => $posts) : ?>
	<?php if ($ct++ > 0): ?><hr /><?php endif; ?>
	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="10"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->
	<table class="group-discussions" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
		<tbody>
		<tr>
			<td>
				<?php $group = Hubzero\User\Group::getInstance($gid); ?>
				<table class="group-info" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; background-color: rgba(186, 186, 186, 0.4); border: 1px solid #DDDDDD;">
					<tr>
						<td width="100%" style="padding: 8px; border-collapse: collapse;">
							<table style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
								<tbody>
									<tr>
										<td valign="top" rowspan="3">			
											<img style="max-height: 75px; max-width: 100px; width: auto; height: auto;" src="<?php echo rtrim(Request::root(), '/') . '/' . ltrim($group->getLogo('path'), '/'); ?>" alt="<?php echo $this->escape($group->get('description')); ?>" />
										</td>
									</tr>
									<tr>
										<td style="text-align: left; padding: 0 0.5em 0.5em 0.75em; font-weight: bold;" align="right"><?php echo $group->get('description'); ?></th>
									</tr>
									<tr>
										<td style="text-align: left; padding: 0 0.5em 0 0.75em; white-space: nowrap;" align="right">You have <?php echo $posts->count(); ?> new post<?php if ($posts->count() > 1) { echo 's'; } ?></th>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<?php foreach ($posts as $post) : ?>
					<?php 
					$thread = Components\Forum\Models\Post::oneOrNew($post->get('thread'));
					$category = Components\Forum\Models\Category::oneOrFail($post->get('category_id'));
					$section = $category->section();

					$group_link = $base . '/groups/' . $group->get('cn');
					$section_link = $group_link . '/forum';
					$category_link = $section_link . '/' . $section->get('alias') . '/' . $category->get('alias');
					$thread_link = $category_link . '/' . $thread->get('id');
					$comment_link = $thread_link . '?limit=1000#c' . $post->id;
					?>
					<table width="100%" class="ticket-comments" style="border-collapse: separate !important; margin: 1em 0 0 0; line-height: 1.2em;" cellpadding="0" cellspacing="0" border="0">
						<thead>
							<tr>
								<th style="font-weight: normal; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; padding: 8px; text-align: left; -webkit-border-radius: 4px 4px 0 0; -moz-border-radius: 4px 4px 0 0; border-radius: 4px 4px 0 0; background-color: #F3F3F3;" align="left">
									<table style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
										<tbody>
											<tr>
												<th style="text-align: left; padding: 0 0.5em 0 0.75em; white-space: nowrap; vertical-align: top;" align="right"><?php echo Lang::txt('PLG_GROUPS_FORUM_DETAILS_SECTION'); ?>:</th>
												<td style="text-align: left; padding: 0 0.5em;" align="left"><a href="<?php echo $section_link; ?>"><?php echo $this->escape($section->get('title')); ?></a></td>
											</tr>
											<tr>
												<th style="text-align: left; padding: 0 0.5em 0 0.75em; white-space: nowrap; vertical-align: top;" align="right"><?php echo Lang::txt('PLG_GROUPS_FORUM_DETAILS_CATEGORY'); ?>:</th>
												<td style="text-align: left; padding: 0 0.5em;" align="left"><a href="<?php echo $category_link; ?>"><?php echo $this->escape($category->get('title')); ?></a></td>
											</tr>
											<tr>
												<th style="text-align: left; padding: 0 0.5em 0 0.75em; white-space: nowrap; vertical-align: top;" align="right"><?php echo Lang::txt('PLG_GROUPS_FORUM_DETAILS_THREAD'); ?>:</th>
												<td style="text-align: left; padding: 0 0.5em;" align="left"><a href="<?php echo $thread_link; ?>"><?php echo $this->escape($thread->get('title')); ?></a></td>
											</tr>
										</tbody>
									</table>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div style="position: relative; border: 1px solid #CCCCCC; padding: 12px; -webkit-border-radius: 0 0 4px 4px; -moz-border-radius: 0 0 4px 4px; border-radius: 0 0 4px 4px;">
										<table width="100%" cellpadding="0" cellspacing="0" border="0">
											<tr>
												<th width="50px" style="text-align: left;" align="left" valign="left">
													<img width="50" src="<?php echo Request::root() . '/members/' . $post->created_by . '/Image:thumb.png'; ?>" alt="Post author"/>
												</th>
												<th style="text-align: left; padding-left: 10px;" align="left" valign="top">
													<strong><a href="<?php echo Request::root() . 'members/' . $post->created_by; ?>"><?php echo (!$post->get('anonymous')) ? $post->creator->get('name') : Lang::txt('JANONYMOUS'); ?></a></strong>
												</th>
												<th style="text-align: right; color: #666; font-size: 0.9em;"" align="right" valign="top">
													<?php echo Lang::txt('PLG_GROUPS_FORUM_CREATED', $post->created('time'), $post->created('date')); ?>
												</th>
											</tr>
										</table>
										<div style="padding: 0 0.5em;">
											<?php echo $post->comment; ?>
											<?php
											$attachments = $post->attachments()
												->whereIn('state', array(Components\Forum\Models\Post::STATE_PUBLISHED))
												->rows();

											if ($attachments->count() > 0) { ?>
												<div class="comment-attachments" style="margin: 2em 0 0 0; padding: 0; text-align: left;">
													<span><strong>Attachments:</strong></span>
													<?php
													foreach ($attachments as $attachment)
													{
														if (!trim($attachment->get('description')))
														{
															$attachment->set('description', $attachment->get('filename'));
														}
														echo '<p class="attachment" style="margin: 0.5em 0; padding: 0 0 0 0.5em; text-align: left;"><a class="' . ($attachment->isImage() ? 'img' : 'file') . '" data-filename="' . $attachment->get('filename') . '" href="' . $thread_link . '/' . $attachment->get('post_id') . '/' . $attachment->get('filename') . '">' . $attachment->get('description') . '</a></p>';
													}
													?>
												</div>
											<?php } ?>
										</div>
										<table width="100%" cellpadding="0" cellspacing="0" border="0">
											<tr>
												<td style="text-align: right;">
													<a href="<?php echo $comment_link; ?>" style="display: inline-block; padding: 6px 12px; color: white; background-color: #597F2F; text-decoration: none; border-radius: 5px; border: 1px solid #597F2F;">
														View post on <?php echo Config::get('sitename'); ?>
													</a>
												</td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				<?php endforeach; ?>
			</td>
		</tr>
		</tbody>
	</table>
	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="10"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->
	<?php if ($this->encryptor):
	// add unsubscribe link
	$unsubscribeToken = $this->encryptor->buildEmailToken(1, 3, $this->user, $gid);
	$unsubscribeLink = $base . '/groups/' . $group->get('cn') . '/forum?action=unsubscribe&t=' . $unsubscribeToken;
	$interval = array('daily', 'weekly', 'monthly');
	$interval_id = array(2, 3, 4);
	?>
	<table class="tbl-footer group" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top: none; margin-bottom: 0;">
		<tbody>
			<tr>
				<td align="center" valign="bottom">
					<span>You received this digest because you subscribed to the <a href="<?php echo $group_link; ?>"><?php echo $group->get('description'); ?></a> forum on <a href="<?php echo $base; ?>"><?php echo Config::get('sitename'); ?></a>.</span><br />
					<span>You are currently receiving a <?php echo $this->interval; ?> digest.</span><br />
					<span>Change your forum notifications to receive <a href="<?php echo $unsubscribeLink . '&o=1'?>">individual emails for each post</a> or a
					<?php 
					echo implode(' / ', array_filter(array_map(function ($int, $id) use ($unsubscribeLink) {
						if ($int !== $this->interval) {
							return '<a href="' . $unsubscribeLink . '&o=' . $id . '">' . $int . '</a>';
						}
					}, $interval, $interval_id), function ($var) { return $var !== null; }));
					?> digest.</span><br />
					<span><a href="<?php echo $unsubscribeLink; ?>">Unsubscribe</a> from all <a href="<?php echo $group_link; ?>"><?php echo $group->get('description'); ?></a> forum posts.
				</td>
			</tr>
		</tbody>
	</table>
	<?php endif; ?>
		<!-- Start Spacer -->
		<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="10"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->
<?php endforeach; ?>