<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$base = rtrim(Request::base(), '/');

$thread_link = $base . '/' . trim(Route::url($this->thread->link()), '/');
$comment_link = $base . '/' . trim(Route::url($this->thread->link('permalink', array('limit' => 1000)) . '#c' . $this->post->id), '/');
$category_link = $base . '/' . trim(Route::url($this->category->link()), '/');
$section_link = $base . '/' . trim(Route::url($this->section->link()), '/');
									
// get the group
$groupLink = rtrim(Request::base(), '/') . '/groups/' . $this->group->get('cn');

$bgcolor = '#f1f1f1';
$bdcolor = '#e1e1e1';

// Text for preheader
$preheader = (!$this->post->get('anonymous') ? $this->post->creator->get('name') : Lang::txt('JANONYMOUS')) . ' said "' . $this->post->comment . '" | ';
?>

<!-- Start Preheader -->
<span class="preheader"><?php echo $preheader; ?></span>
<!-- End Preheader -->

<?php if ($this->delimiter) { ?>
	<!-- Start Header Spacer -->
	<table class="tbl-delimiter" width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px dashed #b5c6b5;">
		<tbody>
			<tr>
				<td height="30" style="border-collapse: collapse; color: #9bac9b;">
					<div style="height: 0px; overflow: hidden; color: #fff; visibility: hidden;"><?php echo $this->delimiter; ?></div>
					<div style="text-align: center; font-size: 90%; display: block; padding: 1em;"><?php echo Lang::txt('PLG_GROUPS_FORUM_EMAIL_REPLY_ABOVE'); ?></div>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End Header Spacer -->

	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="30"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->
<?php } ?>
	<!-- Start Header -->
	<table class="tbl-header" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td align="left" valign="middle" style="width: 24px !important;">
					<img src="https://qubeshub.org/app/site/media/images/emails/comment-solid.png" width="24" height="24" style="border:none;" alt="Comment icon"/>
				</td>
				<td align="left" valign="bottom" nowrap="nowrap" class="component left" style="padding: 5px 10px 5px 10px !important;">
					Forum Post
				</td>
				<td width="90%" align="right" valign="bottom" class="sitename group">
					<?php echo $this->group->get('description'); ?>
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

	<table id="ticket-info" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; border: 1px solid <?php echo $bdcolor; ?>; background: <?php echo $bgcolor; ?>; font-size: 0.9em; line-height: 1.6em;">
		<tbody>
			<tr>
				<td width="100%" style="padding: 8px;">
					<table style="border-collapse: collapse; font-size: 1.1em;" cellpadding="0" cellspacing="0" border="0">
						<tbody>
							<?php if ($this->group->get('logo')) { ?>
							<tr>
								<td valign="top" rowspan="4">
									<img style="max-height: 75px; max-width: 100px; width: auto; height: auto;" src="<?php echo rtrim(Request::root(), '/') . '/' . ltrim($this->group->getLogo('path'), '/'); ?>" alt="<?php echo $this->escape($this->group->get('description')); ?>" />
								</td>
							</tr>
							<?php } ?>
							<tr>
								<th style="text-align: right; padding: 0 0.5em 0 0.75em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('PLG_GROUPS_FORUM_DETAILS_SECTION'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" align="left"><a href="<?php echo $section_link; ?>"><?php echo $this->escape($this->section->get('title')); ?></a></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em 0 0.75em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('PLG_GROUPS_FORUM_DETAILS_CATEGORY'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" align="left"><a href="<?php echo $category_link; ?>"><?php echo $this->escape($this->category->get('title')); ?></a></td>
							</tr>
							<tr>
								<th style="text-align: right; vertical-align: top; padding: 0 0.5em 0 0.75em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('PLG_GROUPS_FORUM_DETAILS_THREAD'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" align="left"><a href="<?php echo $thread_link; ?>"><?php echo $this->escape($this->thread->get('title')); ?></a></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>

	<table width="100%" id="ticket-comments" style="border-collapse: collapse; margin: 2em 0 0 0; padding: 0; line-height: 1.2em;" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td style="padding: 10px 0;">
					<div style="position: relative; border: 1px solid #CCCCCC; padding: 12px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px;">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<th width="50px" style="text-align: left;" align="left" valign="left">
									<img width="50" src="<?php echo Request::root() . '/members/' . $this->post->created_by . '/Image:thumb.png'; ?>" alt="Post author"/>
								</th>
								<th style="text-align: left; padding-left: 10px;" align="left" valign="top">
									<strong><a href="<?php echo Request::root() . 'members/' . $this->post->created_by; ?>"><?php echo (!$this->post->get('anonymous')) ? $this->post->creator->get('name') : Lang::txt('JANONYMOUS'); ?></a></strong>
								</th>
								<th style="text-align: right; color: #666; font-size: 0.9em;"" align="right" valign="top">
									<?php echo Lang::txt('PLG_GROUPS_FORUM_CREATED', $this->post->created('time'), $this->post->created('date')); ?>
								</th>
							</tr>
						</table>
						<div style="padding: 0 0.5em;">
							<?php echo $this->post->comment; ?>
							<?php
							$attachments = $this->post->attachments()
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
										echo '<p class="attachment" style="margin: 0.5em 0; padding: 0 0 0 0.5em; text-align: left;"><a class="' . ($attachment->isImage() ? 'img' : 'file') . '" data-filename="' . $attachment->get('filename') . '" href="' . $base . '/' . trim(Route::url($this->thread->link()), '/') . '/' . $attachment->get('post_id') . '/' . $attachment->get('filename') . '">' . $attachment->get('description') . '</a></p>';
									}
									?>
								</div>
							<?php } ?>
						</div>
					</div>
				</td>
			</tr>
			<tr>
                <td colspan="2" align="center" style="padding-top:10px;">
					<a href="<?php echo $comment_link; ?>" style="display: inline-block; padding: 12px 24px; color: white; background-color: #597F2F; text-decoration: none; border-radius: 5px; border: 1px solid #597F2F;">
						View post on <?php echo Config::get('sitename'); ?>
					</a>
                </td>
			</tr>
		</tbody>
	</table>

	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="30"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->

	<!-- Start Footer -->
	<?php if ($this->unsubscribe) { ?>
	<table class="tbl-footer group" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td align="center" valign="bottom">
					<span>You received this message because you subscribed to the <a href="<?php echo $groupLink; ?>"><?php echo $this->group->get('description'); ?></a> forum on <a href="<?php echo Request::base(); ?>"><?php echo Config::get('sitename'); ?></a>.</span><br />
					<span>You are currently receiving individual emails for each forum post.</span><br />
					<span>Change your forum notifications to a <a href="<?php echo $this->get('unsubscribe'); ?>&o=2">daily</a> / <a href="<?php echo $this->get('unsubscribe'); ?>&o=3">weekly</a> / <a href="<?php echo $this->get('unsubscribe'); ?>&o=4">monthly</a> digest.</span><br />
					<span><a href="<?php echo $this->get('unsubscribe'); ?>">Unsubscribe</a> from all <a href="<?php echo $groupLink; ?>"><?php echo $this->group->get('description'); ?></a> forum posts.
				</td>
			</tr>
		</tbody>
	</table>
	<?php } ?>
	<!-- End Footer -->