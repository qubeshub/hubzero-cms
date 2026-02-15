<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding QUBES 2026 template
 **/
class Migration20260215000000TplQubes2026 extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
    // name, name, client (0=site, 1=admin), enabled, default, styles, protected (0=site)
		$this->addTemplateEntry('qubes2026', 'qubes2026', 0, 1, 1, null, 0);
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deleteTemplateEntry('qubes2026', 0);
	}
}
