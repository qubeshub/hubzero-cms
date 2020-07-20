<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding access levels to publication_attachments table
 **/
class Migration20200720000000ComPublications extends Base
{
    /**
	 * List of tables and their datetime fields
	 *
	 * @var  array
	 **/
    public static $table = '#__publication_attachments';
    
	/**
	 * Up
	 **/
	public function up()
	{
        $table = self::$table;

        if ($this->db->tableExists($table) && !$this->db->tableHasField($table, 'access'))
        {
            $query = "ALTER TABLE `$table` ADD COLUMN `access` tinyint(2) NOT NULL DEFAULT '0'";
            $this->db->setQuery($query);
            $this->db->query();
        }

        $this->log(sprintf('Adding column `access` to table %s', $table));

        if (!$this->db->tableHasKey($table, 'idx_access'))
        {
            $query = "ALTER TABLE `$table` ADD INDEX `idx_access` (`access`)";
            $this->db->setQuery($query);
            $this->db->query();

            $this->log(sprintf('Adding index `idx_access` on column `access` to table %s', $table));
        }
    }

	/**
	 * Down
	 **/
	public function down()
	{
        $table = self::$table;

		if ($this->db->tableExists($table) && $this->db->tableHasField($table, 'access'))
		{
			if ($this->db->tableHasKey($table, 'idx_access'))
			{
				$query = "ALTER TABLE `$table` DROP KEY `idx_access`";
				$this->db->setQuery($query);
				$this->db->query();

				$this->log(sprintf('Dropping index `idx_access` on column `access` from table %s', $table));
			}

			$query = "ALTER TABLE `$table` DROP COLUMN `access`;";

			$this->db->setQuery($query);
			$this->db->query();

			$this->log(sprintf('Dropping column `access` from table %s', $table));
        }
    }
}
