<?php

use Hubzero\Content\Migration\Base;

/**
 * Migration script to increase size of description and metadata fields of #__publication_versions table
 **/
class Migration20210626000000ComPublications extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
        // Increase size of description and metadata fields
		if ($this->db->tableExists('#__publication_versions') && 
            $this->db->tableHasField('#__publication_versions', 'description') &&
            $this->db->tableHasField('#__publication_versions', 'metadata'))
		{
			$query = "ALTER TABLE `#__publication_versions` 
                      MODIFY `description` MEDIUMTEXT NOT NULL,
                      MODIFY `metadata` MEDIUMTEXT NULL";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
        // Decrease size of description and metadata fields
		if ($this->db->tableExists('#__publication_versions') && 
            $this->db->tableHasField('#__publication_versions', 'description') &&
            $this->db->tableHasField('#__publication_versions', 'metadata'))
		{
			$query = "ALTER TABLE `#__publication_versions` 
                      MODIFY `description` TEXT NOT NULL,
                      MODIFY `metadata` TEXT NULL";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}
