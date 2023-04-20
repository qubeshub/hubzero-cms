<?php

use Hubzero\Content\Migration\Base;

/**
 * Migration script for adding admin column to jos_xgroups_roles
 **/
class Migration20230418000000ComGroups extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if ($this->db->tableExists('#__xgroups_roles') && !$this->db->tableHasField('#__xgroups_roles', 'admin'))
		{
            $query = "ALTER TABLE `#__xgroups_roles` ADD COLUMN `admin` tinyint(2) DEFAULT 0";
            $this->db->setQuery($query);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__xgroups_roles') && $this->db->tableHasField('#__xgroups_roles', 'admin'))
		{
            $query = "ALTER TABLE `#__xgroups_roles` DROP COLUMN `admin`";
            $this->db->setQuery($query);
            $this->db->query();
		}
	}
}
