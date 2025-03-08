<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2024 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for removing Geocode - Openstreetmap plugin
 **/
class Migration20250207171453PlgAuthfactorsAuthy extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->deletePluginEntry('authfactors', 'authy');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->addPluginEntry('authfactors', 'authy', 0);
	}
}

