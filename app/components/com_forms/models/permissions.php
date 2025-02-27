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
    public static function setPermissions($form, $json)
    {
		$form->set('visible', (array_key_exists('visibility', $json) ? self::$surveyjs_sql_map[$json['visibility']] : 0));
		$form->set('access', (array_key_exists('accessibility', $json) ? self::$surveyjs_sql_map[$json['accessibility']] : 1));
		$form->set('editable', (array_key_exists('editing', $json) ? self::$surveyjs_sql_map[$json['editing']] : 0));
    }

    public static function setUsergroupPermissions($form, $json)
    {
        $rows = self::all()
            ->whereEquals('object', 'form')
            ->whereEquals('object_id', $form->get('id'))
            ->rows()
            ->toArray();

        $old = [];
        array_walk($rows, function($value) use (&$old) { 
            $old[$value['usergroup'] . '_' . $value['usergroup_id'] . '_' . $value['permission']] = $value;
        });
        
        $new = [];
        $properties = array("editors", "groupEditors", "userVisibility", "groupVisibility", "userAccessibility", "groupAccessibility");
        foreach ($properties as $property_string) {
            $property = (array_key_exists($property_string, $json) ? json_decode($json[$property_string], true) : []);
            array_walk($property, function($value) use (&$new, $form, $property_string) {
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
                    'object' => 'form',
                    'object_id' => (int)$form->get('id'),
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
}