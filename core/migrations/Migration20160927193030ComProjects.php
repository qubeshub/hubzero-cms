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
 * Migration script for allowing registered users to create and edit projects by default.
 **/
class Migration20160927193030ComProjects extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if ($this->db->tableExists('#__assets'))
		{
			$rules = '{"core.admin":{"7":1},"core.manage":{"6":1},"core.view":[],"core.create":{"2":1},"core.delete":[],"core.edit":{"2":1},"core.edit.state":[],"core.edit.own":[]}';

			$query = "SELECT `id` FROM `#__assets` WHERE `name` = 'com_projects' LIMIT 1";
			$this->db->setQuery($query);
			$id = $this->db->loadResult();

			if (!$id)
			{
				$parent = \Hubzero\Access\Asset::oneOrNew(\Hubzero\Access\Asset::getRootId());

				$tbl = \Hubzero\Access\Asset::blank();
				$tbl->set('level', 1);
				$tbl->set('parent', 1);
				$tbl->set('name', 'com_projects');
				$tbl->set('title', 'com_projects');
				$tbl->set('rules', $rules);
				$tbl->saveAsLastChildOf($parent);
			}
			else
			{
				// Set the first zone as default
				$query = "UPDATE `#__assets` SET `rules` = " . $this->db->quote($rules) . " WHERE `id` = " . $this->db->quote($id);
				$this->db->setQuery($query);
				$this->db->query();
			}
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__assets'))
		{
			$rules = '{"core.admin":{"7":1},"core.manage":{"6":1},"core.view":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}';

			$query = "SELECT `id` FROM `#__assets` WHERE `name` = 'com_projects' LIMIT 1";
			$this->db->setQuery($query);
			$id = $this->db->loadResult();

			if (!$id)
			{
				$parent = \Hubzero\Access\Asset::oneOrNew(\Hubzero\Access\Asset::getRootId());

				$tbl = \Hubzero\Access\Asset::blank();
				$tbl->set('level', 1);
				$tbl->set('parent', 1);
				$tbl->set('name', 'com_projects');
				$tbl->set('title', 'com_projects');
				$tbl->set('rules', $rules);
				$tbl->saveAsLastChildOf($parent);
			}
			else
			{
				// Set the first zone as default
				$query = "UPDATE `#__assets` SET `rules` = " . $this->db->quote($rules) . " WHERE `id` = " . $this->db->quote($id);
				$this->db->setQuery($query);
				$this->db->query();
			}
		}
	}
}
