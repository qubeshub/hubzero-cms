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

	public function isAdmin($userId)
	{
		return User::authorise('core.admin') || $this->isOwnedBy($userId);
	}

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
		$canDo = (($this->isAdmin($userId)) || // Owner or Site admin
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

	public function whereShared($formId = 0)
	{
		$currentUserId = User::get('id');
		$obj_type = (static::class == "Components\Forms\Models\Form" ? 'form' : 'response');
		$obj_table = ($obj_type == 'form') ? '#__forms_forms' : '#__forms_form_responses';
		$obj_uid = ($obj_type == 'form') ? 'created_by' : 'user_id';
		
		$items = $this->join('((SELECT FP.usergroup_id AS user_id, FP.object_id FROM #__forms_permissions FP
			WHERE FP.usergroup = "user" AND FP.object = "' . $obj_type . '") UNION
			(SELECT GM.uidNumber AS user_id, FP.object_id FROM #__xgroups_members GM
				INNER JOIN #__forms_permissions FP ON GM.gidNumber = FP.usergroup_id
				WHERE FP.usergroup = "members" AND FP.object = "' . $obj_type . '") UNION
			(SELECT GM.uidNumber AS user_id, FP.object_id FROM #__xgroups_managers GM
				INNER JOIN #__forms_permissions FP ON GM.gidNumber = FP.usergroup_id
				WHERE FP.usergroup = "managers" AND FP.object = "' . $obj_type . '") UNION
			(SELECT MR.uidNumber AS user_id, FP.object_id FROM #__xgroups_member_roles MR
				INNER JOIN #__forms_permissions FP ON MR.roleid = FP.usergroup_id
				WHERE FP.usergroup = "role" AND FP.object = "' . $obj_type . '")) AS p', $obj_table . '.id', 'p.object_id', 'left')
			->whereIn($obj_table . '.access', [0, 3])
			->whereEquals('p.user_id', $currentUserId)
			->where($obj_table . '.' . $obj_uid, '!=', $currentUserId); // Exclude own responses;
		if ($formId) { $items = $items->whereEquals('form_id', $formId);}
		$items = $items->orWhereEquals($obj_table . '.access', 1)
			->where($obj_table . '.' . $obj_uid, '!=', $currentUserId);
		if ($formId) { $items = $items->whereEquals('form_id', $formId);}
		$items = $items->orWhereEquals($obj_table . '.access', 2)
			->where($obj_table . '.' . $obj_uid, '!=', $currentUserId);
		if ($formId) { $items = $items->whereEquals('form_id', $formId);}

		return $items;
	}

	/**
	 * Returns all items for user w/ given ID
	 *
	 * @param    int      $userId   User record's ID
	 * @param    int      $formId   Form record's ID
	 * @param    string   $filter   Filter to apply (e.g. 'shared')
	 * @return   object
	 */
	public static function allForUser($userId, $formId = 0, $filter = '')
	{
		$obj_type = (static::class == "Components\Forms\Models\Form" ? 'form' : 'response');
		$userId_field = ($obj_type == 'form') ? 'created_by' : 'user_id';

		$items = self::all();
		
		if ($obj_type == 'response') {
			$items = $items->join('#__forms_forms AS F', 'form_id', 'F.id', 'left')
				->select('#__forms_form_responses.*, F.name AS form');
		}

		// Who (user or shared)
		if ($filter == 'shared') {
			$items = $items->whereShared($formId);
		} else { 
			$items = $items->whereEquals($userId_field, $userId);
			if ($formId) { $items = $items->whereEquals('form_id', $formId);}
		}

		$items = $items->paginated('limitstart', 'limit');

		return $items;
	}
}
