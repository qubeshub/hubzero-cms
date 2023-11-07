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
 * Migration script for modifying form page fields table for groups form plugin
 **/
class Migration20231107000001PlgGroupsForms extends Base
{
	static $tableName = '#__forms_page_fields';
	static $paramsColumn = 'params';

	/**
	 * Up
	 **/
	public function up()
	{
		$tableName = self::$tableName;
		$paramsColumn = self::$paramsColumn;
		$needsParams = !$this->db->tableHasField($tableName, $paramsColumn);

		$addParams = "ALTER TABLE $tableName ADD COLUMN `$paramsColumn` text NULL DEFAULT NULL;";

		if ($this->db->tableExists($tableName) && $needsParams)
		{
			$this->db->setQuery($addParams);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$tableName = self::$tableName;
		$paramsColumn = self::$paramsColumn;
		$hasParams = $this->db->tableHasField($tableName, $paramsColumn);

		$dropParams = "ALTER TABLE $tableName DROP COLUMN $paramsColumn;";

		if ($this->db->tableExists($tableName) && $hasParams)
		{
			$this->db->setQuery($dropParams);
			$this->db->query();
		}
	}

}