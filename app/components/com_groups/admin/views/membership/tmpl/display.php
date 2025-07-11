<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$canDo = \Components\Groups\Helpers\Permissions::getActions('group');

Toolbar::title(Lang::txt('COM_GROUPS'), 'groups.png');

Toolbar::appendButton('Popup', 'new', 'COM_GROUPS_NEW', Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&tmpl=component&task=new&gid=' . $this->filters['gid']), 570, 170);

Toolbar::appendButton('Link', 'unblock', 'COM_GROUPS_ROLE_ASSIGN', 'index.php?option=' . $this->option . '&controller=roles&tmpl=component&task=assign&gid=' . $this->filters['gid'], 400, 400);

Toolbar::spacer();
switch ($this->filters['status'])
{
	case 'invitee':
		//if ($canDo->get('core.edit'))
		//{
			//Toolbar::custom('accept', 'publish', Lang::txt('Accept'), Lang::txt('Accept'), false, false);
		//}
		if ($canDo->get('core.delete'))
		{
			Toolbar::custom('uninvite', 'unpublish', 'COM_GROUPS_MEMBER_UNINVITE', 'COM_GROUPS_MEMBER_UNINVITE', false, false);
		}
	break;
	case 'applicant':
		if ($canDo->get('core.edit'))
		{
			Toolbar::custom('approve', 'publish', 'COM_GROUPS_MEMBER_APPROVE', 'COM_GROUPS_MEMBER_APPROVE', false, false);
		}
		if ($canDo->get('core.delete'))
		{
			Toolbar::custom('deny', 'unpublish', 'COM_GROUPS_MEMBER_DENY', 'COM_GROUPS_MEMBER_DENY', false, false);
		}
	break;
	default:
		if ($canDo->get('core.edit'))
		{
			Toolbar::custom('promote', 'promote', 'COM_GROUPS_MEMBER_PROMOTE', 'COM_GROUPS_MEMBER_PROMOTE', false, false);
			Toolbar::custom('demote', 'demote', 'COM_GROUPS_MEMBER_DEMOTE', 'COM_GROUPS_MEMBER_DEMOTE', false, false);
		}
		if ($canDo->get('core.delete'))
		{
			Toolbar::deleteList('COM_GROUPS_MEMBER_DELETE', 'delete');
		}
	break;
}
Toolbar::spacer();
Toolbar::help('membership');

$database = App::get('db');

Html::behavior('tooltip');

$this->css()
	->js();
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="grid">
			<div class="col span8">
				<label for="filter_search"><?php echo Lang::txt('COM_GROUPS_SEARCH'); ?>:</label>
				<input type="text" name="search" id="filter_search" ckass="filter" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo Lang::txt('COM_GROUPS_SEARCH'); ?>" />

				<label for="filter-status"><?php echo Lang::txt('COM_GROUPS_MEMBER_STATUS'); ?>:</label>
				<select name="status" id="filter-status" class="filter filter-submit">
					<option value=""<?php echo ($this->filters['status'] == '') ? ' selected="selected"' : ''; ?>><?php echo Lang::txt('COM_GROUPS_MEMBER_STATUS'); ?></option>
					<!-- <option value="member"<?php //echo ($this->filters['status'] == 'member') ? ' selected="selected"' : ''; ?>>Member</option> -->
					<option value="manager"<?php echo ($this->filters['status'] == 'manager') ? ' selected="selected"' : ''; ?>><?php echo Lang::txt('Manager'); ?></option>
					<option value="applicant"<?php echo ($this->filters['status'] == 'applicant') ? ' selected="selected"' : ''; ?>><?php echo Lang::txt('Applicant'); ?></option>
					<option value="invitee"<?php echo ($this->filters['status'] == 'invitee') ? ' selected="selected"' : ''; ?>><?php echo Lang::txt('Invitee'); ?></option>
				</select>

				<input type="submit" value="<?php echo Lang::txt('COM_GROUPS_GO'); ?>" />
			</div>
			<div class="col span4">
				<a class="button modal" href="<?php echo Route::url('index.php?option=com_groups&controller=roles&tmpl=component&gid=' . $this->filters['gid']); ?>" rel="{size: {width: 570, height: 170}, onClose: function() {}}">
					<span class="icon-32-new"><?php echo Lang::txt('Roles'); ?></span>
				</a>
			</div>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th colspan="8"><a href="<?php echo Route::url('index.php?option='.$this->option); ?>"><?php echo Lang::txt('COM_GROUPS'); ?></a> > (<?php echo $this->escape(stripslashes($this->group->get('cn'))); ?>) <?php echo $this->escape(stripslashes($this->group->get('description'))); ?></th>
			</tr>
			<tr>
				<th scope="col">
					<input type="checkbox" name="checkall-toggle" id="checkall-toggle" value="" class="checkbox-toggle toggle-all" />
					<label for="checkall-toggle" class="sr-only visually-hidden"><?php echo Lang::txt('JGLOBAL_CHECK_ALL'); ?></label>
				</th>
				<th scope="col" class="priority-4"><?php echo Html::grid('sort', 'COM_GROUPS_USERID', 'uidNumber', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo Html::grid('sort', 'COM_GROUPS_NAME', 'name', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-3"><?php echo Html::grid('sort', 'COM_GROUPS_USERNAME', 'username', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-5"><?php echo Html::grid('sort', 'COM_GROUPS_EMAIL', 'email', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo Lang::txt('COM_GROUPS_MEMBER_STATUS'); ?></th>
				<th scope="col" colspan="2"><?php echo Lang::txt('COM_GROUPS_MEMBER_ACTION'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8"><?php
				echo $this->pagination(
					$this->total,
					$this->filters['start'],
					$this->filters['limit']
				);
				?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		$i = 0;
		foreach ($this->rows as $row)
		{
			if (isset($row->username))
			{
				$reason = new \Components\Groups\Tables\Reason($database);
				$reason->loadReason($row->username, $this->filters['gidNumber']);
				$reasonforjoin = '';
				if ($reason)
				{
					$reasonforjoin = stripslashes($reason->reason == null ? '' : $reason->reason);
				}
			}

			$status = $row->role;
			if (in_array($row->uidNumber, $this->group->get('managers')))
			{
				$status = 'manager';
			}

			$roles = Components\Groups\Helpers\Permissions::getGroupMemberRoles($row->uidNumber, $this->group->get('gidNumber'));
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<input type="checkbox" name="id[]" id="cb<?php echo $i;?>" value="<?php echo (isset($row->uidNumber)) ? $row->uidNumber : $row->email; ?>" class="checkbox-toggle" />
					<label for="cb<?php echo $i;?>" class="sr-only visually-hidden"><?php echo (isset($row->uidNumber)) ? $row->uidNumber : $row->email; ?></label>
				</td>
				<td class="priority-4">
					<?php echo $this->escape($row->uidNumber); ?>
				</td>
				<td>
					<?php if ($canDo->get('core.edit') && isset($row->username)) : ?>
						<a href="<?php echo Route::url('index.php?option=com_members&controller=members&task=edit&id=' . $row->uidNumber); ?>">
							<?php echo $this->escape(stripslashes($row->name)); ?>
						</a>
					<?php else : ?>
						<span>
							<?php echo $this->escape(stripslashes($row->name)); ?>
						</span>
					<?php endif; ?>
					<?php if ($roles) : ?>
						<br />
						<span class="roles">
							<?php
							//echo Lang::txt('COM_GROUPS_ROLES') . ': ';
							$r = array();
							foreach ($roles as $role) :
								$r[] = '<span class="role">' . $role['name'] . ' <a href="' . Route::url('index.php?option=com_groups&controller=roles&task=unassign&gid=' . $this->filters['gid'] . '&id=' . $row->uidNumber . '&roleid=' . $role['id'] . '&return=' . $this->controller) . '" title="' . Lang::txt('COM_GROUPS_UNASSIGN_ROLE') . '">x</a></span>';
							endforeach;
							echo implode(', ', $r);
							?>
						</span>
					<?php endif; ?>
				</td>
				<td class="priority-3">
					<span>
						<?php echo $this->escape(stripslashes($row->username)); ?>
					</span>
				</td>
				<td class="priority-5">
					<span>
						<?php echo $this->escape(stripslashes($row->email)); ?>
					</span>
				</td>
				<td>
					<span class="status <?php echo $status; ?>">
						<?php echo $status; ?>
					</span>
				</td>
				<td>
		<?php if ($canDo->get('core.edit')) { ?>
			<?php
			switch ($status)
			{
				case 'invitee':
				case 'inviteemail':
					?>
						<a class="state unpublish" onclick="javascript:if (confirm('Cancel invitation?')){return true;}else{return false;}" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=uninvite&gid=' . $this->filters['gid'] . '&id=' . (isset($row->uidNumber) ? $row->uidNumber : $row->email) . '&' . Session::getFormToken() . '=1'); ?>">
							<span><?php echo Lang::txt('COM_GROUPS_MEMBER_UNINVITE'); ?></span>
						</a>
					</td>
					<td>
					<?php
				break;
				case 'applicant':
					?>
						<a class="state publish" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=approve&gid=' . $this->filters['gid'] . '&id=' . $row->uidNumber . '&' . Session::getFormToken() . '=1'); ?>">
							<span><?php echo Lang::txt('COM_GROUPS_MEMBER_APPROVE'); ?></span>
						</a>
					</td>
					<td>
						<a class="state unpublish" onclick="javascript:if (confirm('Deny membership?')){return true;}else{return false;}" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=deny&gid=' . $this->filters['gid'] . '&id=' . $row->uidNumber . '&' . Session::getFormToken() . '=1'); ?>">
							<span><?php echo Lang::txt('COM_GROUPS_MEMBER_DENY'); ?></span>
						</a>
					<?php
				break;
				case 'manager':
					?>
						<a class="state demote" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=demote&gid=' . $this->filters['gid'] . '&id=' . $row->uidNumber . '&' . Session::getFormToken() . '=1'); ?>">
							<span><?php echo Lang::txt('COM_GROUPS_MEMBER_DEMOTE'); ?></span>
						</a>
					</td>
					<td>
						&nbsp;
					<?php
				break;
				default:
				case 'member':
					?>
						<a class="state promote" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=promote&gid=' . $this->filters['gid'] . '&id=' . $row->uidNumber . '&' . Session::getFormToken() . '=1'); ?>">
							<span><?php echo Lang::txt('COM_GROUPS_MEMBER_PROMOTE'); ?></span>
						</a>
					</td>
					<td>
						<a class="state trash" onclick="javascript:if (confirm('Cancel membership?')){return true;}else{return false;}" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=delete&gid=' . $this->filters['gid'] . '&id=' . $row->uidNumber . '&' . Session::getFormToken() . '=1'); ?>">
							<span><?php echo Lang::txt('COM_GROUPS_MEMBER_REMOVE'); ?></span>
						</a>
					<?php
				break;
			}
			?>
		<?php } ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
			$i++;
		}
		?>
		</tbody>
	</table>

	<input type="hidden" name="gid" value="<?php echo $this->filters['gid']; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>">
	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->filters['sort']); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->filters['sort_Dir']); ?>" />
	<?php echo Html::input('token'); ?>
</form>
