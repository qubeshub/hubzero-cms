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
 * Migration script for for adding antispam plugins for Babaji
 **/
class Migration20150610140035PlgAntispamBabajispam extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addPluginEntry('antispam', 'babajispam', 0);
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deletePluginEntry('antispam', 'babajispam');
	}
}
