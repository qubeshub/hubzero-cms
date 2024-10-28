<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

// get the group
$group = \Hubzero\User\Group::getInstance($this->announcement->get('scope_id'));
$groupLink = rtrim(Request::base(), '/') . '/groups/' . $group->get('cn');

// define color
$bgcolor = '#FBF1BE';
$bdcolor = '#E9E1BC';

// if high priority
if ($this->announcement->priority == 1)
{
	$bgcolor = '#ffd3d4';
	$bdcolor = '#e9bcbc';
}
?>
	<!-- Start Header -->
	<table class="tbl-header" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td width="1%" align="left" valign="middle">
					<img src="https://qubeshub.org/app/site/media/images/emails/bullhorn-solid.png" width="24" height="24" style="border:none;" />
				</td>
				<td width="9%" align="right" valign="bottom" nowrap="nowrap" class="component left">
					Announcement
				</td>
				<td width="90%" align="right" valign="bottom" class="sitename group">
					<?php echo $group->get('description'); ?>
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
							<?php if ($group->get('logo')) { ?>
							<tr>
								<td valign="top" rowspan="3">
									<img style="max-height: 75px; max-width: 100px; width: auto; height: auto;" src="<?php echo rtrim(Request::root(), '/') . '/' . ltrim($group->getLogo(), '/'); ?>" alt="<?php echo $this->escape($group->get('description')); ?>" />
								</td>
							</tr>
							<?php } ?>
							<tr>
								<th style="text-align: right; padding: 0 0.5em 0 0.75em; font-weight: bold; white-space: nowrap;" align="right">Group:</th>
								<td style="text-align: left; padding: 0 0.5em;" align="left"><a href="<?php echo $groupLink; ?>"><?php echo $group->get('description'); ?></a></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em 0 0.75em; font-weight: bold; white-space: nowrap;" align="right">Priority:</th>
								<td style="text-align: left; padding: 0 0.5em;" align="left"><?php echo ($this->announcement->priority == 1 ? 'Elevated' : 'Normal'); ?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>

	<table id="announcement" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; line-height: 1.2em; margin: 1em 0 0 0">
		<tbody>
			<tr>
				<td>
					<table width="100%" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
						<tbody>
							<tr>
								<td style="text-align: left; padding: 0 1em 0 1em;" align="left">
									<?php echo $this->announcement->get('content'); ?>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
                <td align="center" style="padding-top:10px;">
					<a href="<?php echo $groupLink . '/announcements'; ?>" style="display: inline-block; padding: 12px 24px; color: white; background-color: #597F2F; text-decoration: none; border-radius: 5px; border: 1px solid #597F2F;">
						View announcement on <?php echo Config::get('sitename'); ?>
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
	<table class="tbl-footer group" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td align="center" valign="bottom">
					<span>You received this message because you are a member of the <a href="<?php echo $groupLink; ?>"><?php echo $group->get('description'); ?></a> group on <a href="<?php echo Request::base(); ?>"><?php echo Config::get('sitename'); ?></a>.</span><br />
					<?php if ($this->unsubscribeLink) { ?>
						<span>To <a href="<?php echo $this->unsubscribeLink; ?>">unsubscribe</a> from <a href="<?php echo $groupLink; ?>"><?php echo $group->get('description'); ?></a> announcements, you must <a href="<?php echo $this->unsubscribeLink; ?>">cancel your group membership</a>.
					<?php } ?>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End Footer -->
