<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;
use Components\Publications\Models\Publication;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script changing license from cc40-by-nc to cc40-by-nc-sa
 **/
class Migration20220224000000ComPublications extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
        $this->db = App::get('db');

        // Get master type id for CourseSource
        $this->db->setQuery("SELECT id FROM `#__publication_master_types` WHERE alias = 'coursesource'");
        if ($master_type_id = $this->db->loadResult()) {
            $this->log('Using CourseSource master type ' . $master_type_id);
        } else {
            $this->log('CourseSource master type not found!',  'error');
            return false;
        }

        // Get license type id for cc40-by-nc-sa
        $this->db->setQuery("SELECT id, text FROM `#__publication_licenses` WHERE name = 'cc40-by-nc-sa'");
        if ($license = $this->db->loadObject()) {
            $this->log('License id for cc40-by-nc-sa is ' . $license->id);
        } else {
            $this->log('License for cc40-by-nc-sa not found!', 'error');
            return false;
        }

        // Get all published versions of publications with CourseSource master type to (1) change license information and (2) repackage with updated license file
        $this->db->setQuery("SELECT V.id FROM `#__publication_versions` as V LEFT JOIN `#__publications` as P ON V.publication_id = P.id WHERE V.state = 1 AND P.master_type = " . $master_type_id . " ORDER BY id ASC");
        if ($cs_version_ids = $this->db->loadColumn()) {
            // Change their license_type value to license type id and repackage
            foreach ($cs_version_ids as $vid) {
                // Change license id and text
                $query = "UPDATE `#__publication_versions` SET license_type = $license->id, license_text = " . $this->db->quote($license->text) . " WHERE id = $vid";
                $this->db->setQuery($query);
                if ($this->db->query()) {
                    $this->log('Updated publication license for version with id ' . $vid, 'success');
                } else {
                    $this->log('Unable to update publication license for version with id ' . $vid, 'error');
                    break;
                }

                // Repackage bundle
                require_once \Component::path('publications') . DS . 'models' . DS . 'publication.php';
                $pub = new Publication(null, 'default', $vid);
                if (!$pub->exists()) {
                    $this->log('No CourseSource publication with id ' . $vid . ' exists!', 'error');
                    break;
                }
                $pub->setCuration(); // Set pub assoc and load curation
                if ($pub->_curationModel->package(true, 0)) { // Produce archival package (0 = no instructor bundle)
                    $this->log('Repackaged bundle for publication with id ' . $vid, 'success');
                } else {
                    $this->log('Unable to repackage bundle for publication with id ' . $vid, 'error');
                    break;
                }
            }
        } else {
            $this->log('No CourseSource publications found!', 'error');
            return false;
        }
	}

	/**
	 * Down
	 **/
	public function down()
	{
        // Empty since non-reversible
	}
}
