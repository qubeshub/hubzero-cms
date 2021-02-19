<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

// no direct access
defined('_HZEXEC_') or die();

class Migration20181203113036ComFormsAddComponent extends Base
{

	public function up()
	{
		$this->addComponentEntry('forms');
	}

	public function down()
	{
		$this->deleteComponentEntry('forms');
	}

}
