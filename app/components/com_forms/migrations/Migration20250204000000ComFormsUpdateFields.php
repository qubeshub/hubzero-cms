<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;

// no direct access
defined('_HZEXEC_') or die();

class Migration20250204000000ComFormsUpdateFields extends Base
{

	public function up()
	{
		if ($this->db->tableExists('#__forms_forms')) {
			// Remove disabled field
			if ($this->db->tableHasField('#__forms_forms', 'disabled')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` DROP COLUMN `disabled`;");
				$this->db->query();
			}

			// Remove archived field
			if ($this->db->tableHasField('#__forms_forms', 'archived')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` DROP COLUMN `archived`;");
				$this->db->query();
			}

			// Add discoverability field
			if (!$this->db->tableHasField('#__forms_forms', 'discoverable')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` ADD COLUMN `discoverable` tinyint(1) NULL DEFAULT 0;");
				$this->db->query();
			}
		}
	}

	public function down()
	{
		if ($this->db->tableExists('#__forms_forms')) {
			// Add disabled field
			if (!$this->db->tableHasField('#__forms_forms', 'disabled')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` ADD COLUMN `disabled` tinyint(1) NULL DEFAULT 0;");
				$this->db->query();
			}

			// Add archived field
			if (!$this->db->tableHasField('#__forms_forms', 'archived')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` ADD COLUMN `archived` tinyint(1) NULL DEFAULT 0;");
				$this->db->query();
			}

			// Remove discoverability field
			if ($this->db->tableHasField('#__forms_forms', 'discoverable')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` DROP COLUMN `discoverable`;");
				$this->db->query();
			}
		}
	}
}