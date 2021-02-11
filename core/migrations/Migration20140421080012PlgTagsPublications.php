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
 * Migration script for adding publications tags plugin
 **/
class Migration20140421080012PlgTagsPublications extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addPluginEntry('tags', 'publications');
	}

	/**
	 * Up
	 **/
	public function down()
	{
		$this->deletePluginEntry('tags', 'publications');
	}
}
