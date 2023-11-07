<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

// no direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for modifying forms table for groups form plugin
 **/
class Migration20231107000000PlgGroupsForms extends Base
{

	static $tableName = '#__forms_forms';
	static $gidColumn = 'gid';

	/**
	 * Up
	 **/
	public function up()
	{
		$tableName = self::$tableName;
		$gidColumn = self::$gidColumn;
		$needsGid = !$this->db->tableHasField($tableName, $gidColumn);

		$addGid = "ALTER TABLE $tableName ADD COLUMN `$gidColumn` int(11) unsigned DEFAULT NULL;";

		if ($this->db->tableExists($tableName) && $needsGid)
		{
			$this->db->setQuery($addGid);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tableName = self::$tableName;
		$gidColumn = self::$gidColumn;
		$hasGid = $this->db->tableHasField($tableName, $gidColumn);

		$dropGid = "ALTER TABLE $tableName DROP COLUMN $gidColumn;";

		if ($this->db->tableExists($tableName) && $hasGid)
		{
			$this->db->setQuery($dropGid);
			$this->db->query();
		}
	}

}
