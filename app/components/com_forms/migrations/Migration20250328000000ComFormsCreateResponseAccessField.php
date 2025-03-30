<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

// no direct access
defined('_HZEXEC_') or die();

class Migration20250328000000ComFormsCreateResponseAccessField extends Base
{

	public function up()
	{
		if ($this->db->tableExists('#__forms_form_responses')) {
			// Add access field
			if (!$this->db->tableHasField('#__forms_form_responses', 'access')) {
				$this->db->setQuery("ALTER TABLE `#__forms_form_responses` ADD COLUMN `access` tinyint(1) UNSIGNED NOT NULL DEFAULT 0;");
				$this->db->query();
			}
		}
	}

	public function down()
	{
		if ($this->db->tableExists('#__forms_form_responses')) {
			// Remove access field
			if ($this->db->tableHasField('#__forms_form_responses', 'access')) {
				$this->db->setQuery("ALTER TABLE `#__forms_form_responses` DROP COLUMN `access`;");
				$this->db->query();
			}
		}
	}
}