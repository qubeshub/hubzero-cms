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
 * Migration script for removing Geocode - Oiorest plugin
 **/
class Migration20250125152250PlgGeocodeOiorest extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->deletePluginEntry('geocode', 'oiorest');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->addPluginEntry('geocode', 'oiorest', 0);
	}
}

