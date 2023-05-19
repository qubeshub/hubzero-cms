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
 * Migration script for installing group member profile tables
 **/
class Migration20230510000001PlgGroupMemberProfiles extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!$this->db->tableExists('#__xgroups_profiles'))
		{
			$query = "CREATE TABLE `#__xgroups_profiles` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `gid` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `profile_key` varchar(100) NOT NULL,
                `profile_value` text NOT NULL,
                `order` int(11) NOT NULL DEFAULT '0',
                `access` int(10) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `idx_user_id` (`user_id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__xgroups_profiles_fields'))
		{
			$query = "CREATE TABLE `#__xgroups_profile_fields` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `gid` int(11) NOT NULL,
                `type` varchar(255) NOT NULL,
                `name` varchar(255) NOT NULL DEFAULT '',
                `label` varchar(255) NOT NULL DEFAULT '',
                `placeholder` varchar(255) DEFAULT NULL,
                `description` mediumtext,
                `ordering` int(11) NOT NULL DEFAULT '0',
                `access` int(10) NOT NULL DEFAULT '0',
                `option_other` tinyint(2) NOT NULL DEFAULT '0',
                `option_blank` tinyint(2) NOT NULL DEFAULT '0',
                `action_create` tinyint(2) NOT NULL DEFAULT '1',
                `action_update` tinyint(2) NOT NULL DEFAULT '1',
                `action_edit` tinyint(2) NOT NULL DEFAULT '1',
                `action_browse` tinyint(2) NOT NULL DEFAULT '0',
                `min` int(11) NOT NULL DEFAULT '0',
                `max` int(11) NOT NULL DEFAULT '0',
                `default_value` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_gid` (`gid`),
                KEY `idx_type` (`type`),
                KEY `idx_access` (`access`)
                ) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__xgroups_profile_options'))
		{
			$query = "CREATE TABLE `#__xgroups_profile_options` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `field_id` int(11) NOT NULL DEFAULT '0',
                `value` varchar(255) NOT NULL DEFAULT '',
                `label` varchar(255) NOT NULL DEFAULT '',
                `ordering` int(11) NOT NULL DEFAULT '0',
                `checked` tinyint(2) NOT NULL DEFAULT '0',
                `dependents` tinytext,
                PRIMARY KEY (`id`),
                KEY `idx_field_id` (`field_id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}
	}

    /**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__xgroups_profiles'))
		{
			$query = "DROP TABLE IF EXISTS `#__xgroups_profiles`;";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__xgroups_profiles_fields'))
		{
			$query = "DROP TABLE IF EXISTS `#__xgroups_profiles_fields`;";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__xgroups_profile_options'))
		{
			$query = "DROP TABLE IF EXISTS `#__xgroups_profile_options`;";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}