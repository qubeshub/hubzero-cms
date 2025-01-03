<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

// no direct access
defined('_HZEXEC_') or die();

class Migration20241228000001ComFormsCreateFormResponsesJsonTable extends Base
{

	static $tableName = '#__forms_form_responses_json';

	public function up()
	{
		$tableName = self::$tableName;

		// Move to JSON field when update to MySQL >= 5.7
		$createTable = "CREATE TABLE $tableName (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`response_id` int(11) unsigned NOT NULL,
			`json` MEDIUMTEXT NULL DEFAULT NULL,
			PRIMARY KEY (`id`),
			INDEX `response_id_idx` (`response_id` ASC),
            FULLTEXT INDEX `json_idx` (`json` ASC)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

		if (!$this->db->tableExists($tableName))
		{
			$this->db->setQuery($createTable);
			$this->db->query();
		}
	}

	public function down()
	{
		$tableName = self::$tableName;

		$dropTable = "DROP TABLE $tableName";

		if ($this->db->tableExists($tableName))
		{
			$this->db->setQuery($dropTable);
			$this->db->query();
		}
	}
}