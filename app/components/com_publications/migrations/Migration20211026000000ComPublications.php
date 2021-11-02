<?php

use Hubzero\Content\Migration\Base;

/**
 * Migration script for adding membership required to download to master type
 **/
class Migration20211026000000ComPublications extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if ($this->db->tableExists('#__publication_master_types') && !$this->db->tableHasField('#__publication_master_types', 'membership_required'))
		{
			$query = "ALTER TABLE `#__publication_master_types` ADD COLUMN `membership_required` int(2) DEFAULT 0";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__publication_master_types') && $this->db->tableHasField('#__publication_master_types', 'membership_required'))
		{
			$query = "ALTER TABLE `#__publication_master_types` DROP COLUMN `membership_required`";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}
