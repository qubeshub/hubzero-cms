<?php


use Hubzero\Content\Migration\Base;


// No direct access
defined('_HZEXEC_') or die();


/**
 * Migration script for moving autosubscribe email settings from group component to plugin settings
 **/
class Migration20210208000000PlgGroupForum extends Base
{
    /**
     * List of tables
     *
     * @var  array
     **/
    public static $table = '#__xgroups';

    public static $pluginParams = '#__plugin_params';

    /**
     * Up
     **/
    public function up()
    {
        $this->log('Querying `#__xgroups` email settings', 'info');

        if ($this->db->tableExists('#__xgroups') && $this->db->tableHasField('#__xgroups', 'discussion_email_autosubscribe')) {
            // Get email settings from #__xgroups
            $query = "SELECT `g`.`gidNumber`, `g`.`discussion_email_autosubscribe`, `p`.`object_id`, `p`.`params`
            FROM `#__xgroups` g
            JOIN `#__plugin_params` p
            ON `g`.`gidNumber` = `p`.`object_id`
            WHERE `p`.`folder`='groups' AND `p`.`element` = 'forum'";
            $this->db->setQuery($query);
            $this->db->query();
            $result = $this->db->loadAssocList();

            $updatedArray = array();
            foreach ($result as $row) {
                $updateParams = json_decode($row['params']);
                $updateParams->discussion_email_autosubscribe = $row['discussion_email_autosubscribe'];
                $row['params'] = json_encode($updateParams);
                $updatedArray[] = $row;
            }

            $table = self::$pluginParams;
            // Update the plugins database
            foreach ($updatedArray as $param) {
                $params = json_encode($param["params"]);
                $updateQuery = "UPDATE `$table` SET `params`=$params WHERE `object_id`=$param[gidNumber] AND `folder`='groups' AND `element`='forum'";
                $this->db->setQuery($updateQuery);
                if ($this->db->query()) {
                    $this->log('Updating `#__plugin_params` successful', 'success');
                } else {
                    $this->log('Failed to update plugin table', 'error');
                }
            }

            // Remove email column from xgroups
            $tableQuery = "ALTER TABLE #__xgroups
            DROP COLUMN discussion_email_autosubscribe";
            $this->db->setQuery($tableQuery);
            $this->db->query();
        }
    }


    /**
     * Down
     **/
    public function down()
    {
        if ($this->db->tableExists('#__xgroups') && !$this->db->tableHasField('#__xgroups', 'discussion_email_autosubscribe')) {
            // Create email column in #__xgroups
            $query = "ALTER TABLE `#__xgroups`
            ADD `discussion_email_autosubscribe` tinyint(3) DEFAULT '0'";
            $this->db->setQuery($query);
            $this->db->query();

            // Get email settings from #__plugin_params
            $groupQuery = "SELECT `g`.`gidNumber`, `g`.`discussion_email_autosubscribe`, `p`.`object_id`, `p`.`params`
            FROM `#__xgroups`  g
            JOIN `#__plugin_params`  p
            ON `g`.`gidNumber` = `p`.`object_id`
            WHERE `p`.`folder` = 'groups' AND `p`.`element` = 'forum'";
            $this->db->setQuery($groupQuery);
            $this->db->query();
            $result = $this->db->loadAssocList();

            $newArray = array();
            foreach ($result as $row)
            {
                $emailParams = json_decode($row['params'], true);
                $row['discussion_email_autosubscribe'] = $emailParams['discussion_email_autosubscribe'];
                unset($emailParams['discussion_email_autosubscribe']);
                $row['params'] = json_encode($emailParams);
                $newArray[] = $row;
            }

            //Update the two tables
            foreach ($newArray as $update)
            {
                $update_group_query = "UPDATE `#__xgroups` SET `discussion_email_autosubscribe` = $update[discussion_email_autosubscribe] WHERE `gidNumber`=$update[object_id]";
                $this->db->setQuery($update_group_query);
                if ($this->db->query()) {
                    $this->log('Updating `#__xgroups` successful', 'success');
                } else {
                    $this->log('Failed to update groups table', 'error');
                }

                $params = json_encode($update["params"]);
                $update_plugin_query ="UPDATE `#__plugin_params` SET `params`= $params WHERE `object_id`=$update[gidNumber] AND `folder`='groups' AND `element`='forum'";
                $this->db->setQuery($update_plugin_query);
                if ($this->db->query()) {
                    $this->log('Updating `#__plugin_params` successful', 'success');
                } else {
                    $this->log('Failed to update plugin table', 'error');
                }
               
            }
        }
    }
}
