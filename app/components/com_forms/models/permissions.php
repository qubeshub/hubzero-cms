<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Models;

use Hubzero\Database\Relational;
use Hubzero\User\Group;

class Permissions extends Relational
{
    /**
	 * The table namespace
	 *
	 * @var string
	 */
	protected $namespace = 'forms';

    /**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
    protected $table = '#__forms_permissions';

    /**
	 * Map between SurveyJS properties and SQL 
	 *
	 * @var  string
	 **/
    private static $surveyjs_sql_map = [
        'anyone' => 1, 
        'qubes' => 2, 
        'readonly' => 3, 
        'hidden' => 0, 
        'restricted' => 0
    ];

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

    /**
     * Set general permissions for form
     * 
     * @param   mixed  $id  The primary key field value to use to retrieve one row
	 * @return  \Hubzero\Database\Relational|static
     */
    public static function setPermissions($obj, $json, $obj_type='form')
    {
        $default = [
            'access' => ($obj_type == 'form' ? 1 : 0), // fill default for form is anyone; response is restricted
            'visible' => 0,
            'editable' => 0
        ];
        $obj->set('access', (array_key_exists('accessibility', $json) ? self::$surveyjs_sql_map[$json['accessibility']] : $default['access']));

        if ($obj_type == 'form') {
		    $obj->set('visible', (array_key_exists('visibility', $json) ? self::$surveyjs_sql_map[$json['visibility']] : $default['visible']));
		    $obj->set('editable', (array_key_exists('editing', $json) ? self::$surveyjs_sql_map[$json['editing']] : $default['editable']));
        }
    }

    public static function setUsergroupPermissions($obj, $json, $obj_type='form')
    {
        $rows = self::all()
            ->whereEquals('object', $obj_type)
            ->whereEquals('object_id', $obj->get('id'))
            ->rows()
            ->toArray();

        $old = [];
        array_walk($rows, function($value) use (&$old) { 
            $old[$value['usergroup'] . '_' . $value['usergroup_id'] . '_' . $value['permission']] = $value;
        });
        
        $new = [];
        $properties = array("editors", "groupEditors", "userVisibility", "groupVisibility", "userAccessibility", "groupAccessibility");
        foreach ($properties as $property_string) {
            // For some reason form permissions are stored as JSON strings, while response permissions are stored as JSON objects
            $property = (array_key_exists($property_string, $json) ? ($obj_type == 'form' ? json_decode($json[$property_string], true) : $json[$property_string]) : []);
            array_walk($property, function($value) use (&$new, $obj, $obj_type, $property_string) {
                $usergroup = (array_key_exists('user', $value) ? 'user' : (is_numeric($value['role']) ? 'role' : $value['role']));
                $usergroup_id = (array_key_exists('user', $value) ? (int)$value['user'] : (is_numeric($value['role']) ? (int)$value['role'] : Group::getInstance($value['group'])->get('gidNumber')));
                switch($property_string) {
                    case 'userVisibility':
                    case 'groupVisibility':
                        $permission = 'visible';
                        break;
                    case 'userAccessibility':
                    case 'groupAccessibility':
                        $permission = $value['permission'];
                        break;
                    default:
                        $permission = 'edit';
                        break;
                }
                $row = array(
                    'object' => $obj_type,
                    'object_id' => (int)$obj->get('id'),
                    'usergroup' => $usergroup,
                    'usergroup_id' => (int)$usergroup_id,
                    'permission' => $permission
                );
                $new[$usergroup . '_' . $usergroup_id . '_' . $permission] = $row;
            });
        }

        $add = array_diff_key($new, $old);
        foreach ($add as $row) {
            $permissions = new Permissions();
            $permissions->set($row);
            if (!$permissions->save()) {
                return false;
            }
        }

        $delete = array_diff_key($old, $new);
        foreach ($delete as $row) {
            $permissions = Permissions::oneOrFail($row['id']);
            if (!$permissions->destroy()) {
                return false;
            }
        }

        return true;
    }

    public static function getUsergroupPermissions($obj, $obj_type='response')
    {
        $db = App::get('db');

        $rows = self::all()
            ->whereEquals('object', $obj_type)
            ->whereEquals('object_id', $obj->get('id'))
            ->rows()
            ->toArray();

        $permissions = new \stdClass();
        $permissions->userAccessibility = [];
        $permissions->groupAccessibility = [];
        array_walk($rows, function($permission) use (&$permissions, $db) {
            $permissionObj = new \stdClass();
            $permissionObj->permission = $permission['permission'];
            switch($permission['usergroup']) {
                case 'user':
                    $permissionObj->user = $permission['usergroup_id'];
                    $permissions->userAccessibility[] = $permissionObj;
                    break;
                case 'role':
    				$db->setQuery("SELECT r.gidNumber FROM `#__xgroups_roles` AS r WHERE r.id = " . $permission['usergroup_id']);
                    $permissionObj->group = \Hubzero\User\Group::getInstance($db->loadResult())->get('cn');
                    $permissionObj->role = $permission['usergroup_id'];
                    $permissions->groupAccessibility[] = $permissionObj;
                    break;
                default:
                    $permissionObj->group = \Hubzero\User\Group::getInstance($permission['usergroup_id'])->get('cn');
                    $permissionObj->role = $permission['usergroup'];
                    $permissions->groupAccessibility[] = $permissionObj;
                    break;
            }
        });

        // Add access, even though technically not usergroup permission - much cleaner code
        $reverse_access_map = self::$surveyjs_sql_map;
        unset($reverse_access_map['hidden']);
        $reverse_access_map = array_flip($reverse_access_map);
        $permissions->accessibility = $reverse_access_map[$obj->get('access')];

        return json_encode($permissions);
    }
}