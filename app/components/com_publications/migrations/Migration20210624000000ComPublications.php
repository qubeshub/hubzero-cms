<?php

use Hubzero\Content\Migration\Base;

/**
 * Migration script for adding group ownership to master type
 **/
class Migration20210624000000ComPublications extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if ($this->db->tableExists('#__publication_master_types') && !$this->db->tableHasField('#__publication_master_types', 'ownergroup'))
		{
			$query = "ALTER TABLE `#__publication_master_types` ADD COLUMN `ownergroup` int(11) DEFAULT NULL";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__publication_master_types') && $this->db->tableHasField('#__publication_master_types', 'ownergroup'))
		{
			$query = "ALTER TABLE `#__publication_master_types` DROP COLUMN `ownergroup`";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}
