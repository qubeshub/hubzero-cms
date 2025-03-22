<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Traits;

require_once "$componentPath/models/permissions.php";

use Components\Forms\Models\Permissions;

trait possessable
{

	/**
	 * Does record belong to user with given ID
	 *
	 * @param    int    $userId   User ID
	 * @return   bool
	 */
	public function isOwnedBy($userId)
	{
		$ownerForeignKey = $this->_ownerForeignKey;

		return $userId === $this->$ownerForeignKey;
	}

	public function isFillableBy($userId)
	{
		return $this->_checkPermissionFor($userId, 'fill', 'access');
	}

	public function isReadonlyBy($userId)
	{
		return $this->get('access') == 3 || $this->_checkUsergroupPermissionFor($userId, 'readonly');
	}

	private function _checkPermissionFor($userId, $permission, $global)
	{
		$canDo = ((User::authorise('core.admin')) || // Admin
				  ($this->isOwnedBy($userId)) || // Owner
				  ($this->get($global) == 1) || // Anyone
				  ($this->get($global) == 2) && !User::isGuest()); // QUBES Member

		return $canDo || $this->_checkUsergroupPermissionFor($userId, $permission);
	}

	private function _checkUsergroupPermissionFor($userId, $permission)
	{
		$db = App::get('db');
		$permission = $db->quote($permission);
		$object = (static::class == "Components\Forms\Models\Form" ? "'form'" : "'response'");

		// One query to rule them all
		$query = "SELECT COUNT(*) FROM
					((SELECT FP.usergroup_id FROM jos_forms_permissions FP
					WHERE FP.usergroup = 'user' AND FP.permission = " . $permission . " AND FP.object = " . $object . " AND FP.object_id = " . $this->get('id') . ")
					UNION
					(SELECT GM.uidNumber FROM jos_xgroups_members GM
					INNER JOIN jos_forms_permissions FP ON GM.gidNumber = FP.usergroup_id
					WHERE FP.usergroup = 'members' AND FP.permission = " . $permission . " AND FP.object = " . $object . " AND FP.object_id = " . $this->get('id') . ")
					UNION
					(SELECT GM.uidNumber FROM jos_xgroups_managers GM
					INNER JOIN jos_forms_permissions FP ON GM.gidNumber = FP.usergroup_id
					WHERE FP.usergroup = 'managers' AND FP.permission = " . $permission . " AND FP.object = " . $object . " AND FP.object_id = " . $this->get('id') . ")
					UNION
					(SELECT MR.uidNumber FROM jos_xgroups_member_roles MR
					INNER JOIN jos_forms_permissions FP ON MR.roleid = FP.usergroup_id
					WHERE FP.usergroup = 'role' AND FP.permission = " . $permission . " AND FP.object = " . $object . " AND FP.object_id = " . $this->get('id') . ")) P
					WHERE P.usergroup_id = " . $userId;
		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	 * Returns response permissions
	 *
	 * @return   string
	 */
	public function getUsergroupPermissions()
	{
		$obj_type = (static::class == "Components\Forms\Models\Form" ? 'form' : 'response');

		return Permissions::getUsergroupPermissions($this, $obj_type);
	}

}
