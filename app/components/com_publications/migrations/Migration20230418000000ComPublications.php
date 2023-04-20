<?php

use Hubzero\Content\Migration\Base;

/**
 * Migration script to create jos_publication_curation_member_roles table
 * 
 * This table is used to store the roles of the members of a curation team.
 * Roles are taken from the jos_xgroups_roles table.
 * Columns include:
 *  - vid (version id)
 *  - uid (user id)
 *  - first_name
 *  - last_name
 *  - email
 *  - organization
 *  - roleid (see jos_xgroups_roles - article id and people id associated to group id associated to jos_xgroups_roles)
 *  - status (pending, active/accepted, completed, declined, no response, excluded, etc.)?
 *  - reason (another table?)
 *  - comment
 *  - last_contacted
 **/
class Migration20230418000000ComPublications extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!$this->db->tableExists('#__publication_curation_member_roles')) {
            $query = "CREATE TABLE IF NOT EXISTS `#__publication_curation_member_roles` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `vid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
				`uid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
				`rid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
				`first_name` VARCHAR(100) NULL DEFAULT NULL,
				`last_name` VARCHAR(100) NULL DEFAULT NULL,
                `email` VARCHAR(100) NULL DEFAULT NULL,
                `organization` VARCHAR(100) NULL DEFAULT NULL,
                `status` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                `reason` TEXT NULL DEFAULT NULL,
				`comment` TEXT NULL DEFAULT NULL,
                `last_contacted` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_vid` (`vid`),
				KEY `idx_vid_rid` (`vid`, `rid`),
				KEY `idx_uid_rid` (`uid`, `rid`),
				KEY `idx_vid_status` (`vid`, `status`)
              ENGINE = MyISAM DEFAULT CHARSET=utf8;";
            
            $this->db->setQuery($query);
			if ($this->db->query()) {
                $this->log("Successfully created `jos_publication_curation_member_roles` table.", "success");
            } else {
                $this->log("Unable to create `jos_publication_curation_member_roles` table.", "error");
            }
        }
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__publication_curation_member_roles')) {
			$query = "DROP TABLE `#__publication_curation_member_roles`";
			$this->db->setQuery($query);
			
			if ($this->db->query()) {
				$this->log("Successfully removed `#__publication_curation_member_roles` table.", "success");
			} else {
				$this->log("Unable to remove `#__publication_curation_member_roles` table.", "error");
			}
		}
	}
}
