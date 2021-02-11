<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

/**
 * Migration script for adding mandrill mail plugin
 **/
class Migration20150518175926PlgMailMandrill extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addPluginEntry('mail', 'mandrill', 0);
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deletePluginEntry('mail', 'mandrill');
	}
}
