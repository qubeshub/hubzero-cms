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

			// Add visible field
			if (!$this->db->tableHasField('#__forms_forms', 'visible')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` ADD COLUMN `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 0;");
				$this->db->query();
			}

			// Add access field
			if (!$this->db->tableHasField('#__forms_forms', 'access')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` ADD COLUMN `access` tinyint(1) UNSIGNED NOT NULL DEFAULT 1;");
				$this->db->query();
			}

			// Add editable field
			if (!$this->db->tableHasField('#__forms_forms', 'editable')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` ADD COLUMN `editable` tinyint(1) UNSIGNED NOT NULL DEFAULT 0;");
				$this->db->query();
			}

			// Add max_responses field
			if (!$this->db->tableHasField('#__forms_forms', 'max_responses')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` ADD COLUMN `max_responses` tinyint(1) UNSIGNED NOT NULL DEFAULT 0;"); // 0 means infinity
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

			// Remove visible field
			if ($this->db->tableHasField('#__forms_forms', 'visible')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` DROP COLUMN `visible`;");
				$this->db->query();
			}

			// Remove access field
			if ($this->db->tableHasField('#__forms_forms', 'access')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` DROP COLUMN `access`;");
				$this->db->query();
			}

			// Remove editable field
			if ($this->db->tableHasField('#__forms_forms', 'editable')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` DROP COLUMN `editable`;");
				$this->db->query();
			}

			// Remove max_responses field
			if ($this->db->tableHasField('#__forms_forms', 'max_responses')) {
				$this->db->setQuery("ALTER TABLE `#__forms_forms` DROP COLUMN `max_responses`;");
				$this->db->query();
			}
		}
	}
}