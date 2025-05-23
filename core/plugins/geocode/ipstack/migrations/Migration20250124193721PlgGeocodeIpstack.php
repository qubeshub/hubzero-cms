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
 * Migration script for adding Geocode - IpStack plugin
 **/
class Migration20250124193721PlgGeocodeIpstack extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addPluginEntry('geocode', 'ipstack');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deletePluginEntry('geocode', 'ipstack', 0);
	}
}

