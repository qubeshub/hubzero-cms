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
 * Migration script for adding com_fmns component
 **/
class Migration20180712000000ComFmns extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addComponentEntry('fmns');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deleteComponentEntry('fmns');
	}
}
