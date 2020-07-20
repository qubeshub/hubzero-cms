<?php

use Hubzero\Content\Migration\Base;
use Hubzero\Access\Group as Accessgroup;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding access levels to publication_attachments table
 **/
class Migration20200720000001ComPublications extends Base
{
    /**
	 * List of tables and their datetime fields
	 *
	 * @var  array
	 **/
    public static $table = '#__usergroups';
    
	/**
	 * Up
	 **/
	public function up()
	{
        $table = self::$table;

        if ($this->db->tableExists($table))
        {
            $row = Accessgroup::all()
                ->where('title', '=', 'Instructor')
                ->count();

            if (!$row) {
                $registered_id = Accessgroup::all()
                    ->where('title', '=', 'Registered')
                    ->row()
                    ->get('id');
                if ($registered_id) {
                    $fields = array(
                        'id' => 0,
                        'title' => 'Instructor',
                        'parent_id' => $registered_id
                    );
                    
                    if (!Accessgroup::oneOrNew($fields['id'])->set($fields)->save()) {
                        $this->log('Error in creating `Instructor` group.', 'error');
                        return false;
                    }
                } else {
                    $this->log('There is no `Registered` group.  Please create this group before continuing.', 'error');
                    return false;
                }
            } else {
                $this->log('You have an existing `Instructor` group.  Please remove this group before continuing.', 'error');
				return false;
            }
        }

        $this->log('Created `Instructor` access group.');
    }

	/**
	 * Down
	 **/
	public function down()
	{
        $table = self::$table;

        if ($this->db->tableExists($table))
        {
            $row = Accessgroup::all()
                ->where('title', '=', 'Instructor')
                ->row();

            if ($row) {
                if (!$row->destroy()) {
                    $this->log('Unknown error in attempt to delete `Instructor` access group.', 'error');
                    return false;
                }
            } else {
                $this->log('Error in deleting `Instructor` access group - group does not exist.', 'error');
                return false;
            }

            $this->log('Deleted `Instructor` access group.');
        }
    }
}
