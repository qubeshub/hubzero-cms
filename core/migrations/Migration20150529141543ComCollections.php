<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for fixing incorrect access values on colleciton items
 **/
class Migration20150529141543ComCollections extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if ($this->db->tableExists('#__collections_items'))
		{
			$query = "UPDATE `#__collections_items` SET `access`=0 WHERE `access`=1";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}
