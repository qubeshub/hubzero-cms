<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Hubzero\Content\Migration\Base;
use \Components\Publications\Models\Publication;
include_once(Component::path('com_publications') . '/models/publication.php');
// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for creating links in the SFTP directory
 **/
class Migration20180820101436ComPublications extends Base
{
	/**
	 * Number of database rows to process at a time
	 *
	 * @var  integer
	 */
	public $limit = 1000;

	/**
	 * Up
	 **/
	public function up()
	{
		$offset = 0;
		$versionQuery = "SELECT count(*) FROM `#__publication_versions` WHERE `state` = 1";
		$this->db->setQuery($versionQuery);
		$this->db->query();
		$versionCount = $this->db->loadResult();
		while ($offset < $versionCount)
		{
			$query = "SELECT `v`.`doi`, `v`.`id`, `v`.`version_number`, `v`.`publication_id`, `v`.`state`, `p`.`master_type`, `t`.`alias` FROM `#__publication_versions` v";
			$query .= " JOIN `#__publications` p ON `v`.`publication_id` = `p`.`id` JOIN `#__publication_master_types` t ON `p`.`master_type` = `t`.`id`";
			$query .= " WHERE `v`.`state` = 1 AND `v`.`published_up` < NOW() AND `t`.`alias` NOT IN ('series', 'databases') LIMIT {$offset}, {$this->limit};";

			$this->db->setQuery($query);
			$this->db->query();
			$publications = $this->db->loadAssocList();

			foreach ($publications as $publication)
			{
				$pubId = $publication['publication_id'];
				$versionId = $publication['id'];
				$doi = $publication['doi'];
				$versionNum = $publication['version_number'];
				$this->removeLink($pubId, $versionId, $versionNum, $doi);
				if (is_dir($this->_linkPath()))
				{
					$this->createLink($pubId, $versionId, $versionNum, $doi);
				}
			}
			$offset += $this->limit;
		}

	}

	/**
	 * Down
	 **/
	public function down()
	{
		if (is_dir($this->_linkPath()))
		{
			$versionQuery = "SELECT count(*) FROM `#__publication_versions` WHERE `state` = 1";
			$this->db->setQuery($versionQuery);
			$this->db->query();
			$versionCount = $this->db->loadResult();
			$offset = 0;
			while ($offset < $versionCount)
			{
				$query = "SELECT `doi`, `publication_id`, `id`, `version_number`, `state` FROM `#__publication_versions` WHERE `state` = 1 LIMIT {$offset}, {$this->limit};";

				$this->db->setQuery($query);
				$this->db->query();
				$publications = $this->db->loadAssocList();

				foreach ($publications as $publication)
				{
					$pubId = $publication['publication_id'];
					$versionId = $publication['id'];
					$doi = $publication['doi'];
					$versionNum = $publication['version_number'];
					$this->removeLink($pubId, $versionId, $versionNum, $doi);
				}
				$offset += $this->limit;
			}
		}

	}

	/**
	 * Generate link for publication package
	 *
	 * @return boolean
	 */
	protected function createLink($pubId, $versionId, $versionNum, $doi = '')
	{
		$bundleName = 'Resource' . '_' . $pubId;
		$bundleWithVersion = $bundleName . '_' . $versionNum;

		if ($doi != '')
		{
			$doi = str_replace('.', '_', $doi);
			$doi = str_replace('/', '_', $doi);
			$bundleName = $doi;
			// Set link to the same name as bundle if it is using DOI
			$bundleWithVersion = $doi;
		}

		$tarname = $bundleName . '.zip';
		$fileName = $bundleWithVersion . '.zip';
		$tarPath = '..' . '/' . str_pad($pubId, 5, "0", STR_PAD_LEFT) . '/' . str_pad($versionId, 5, "0", STR_PAD_LEFT) . '/' . $tarname;
		$linkPath = $this->_linkPath();
		if ($linkPath !== false)
		{
			chdir($linkPath);
		}
		if (!is_file($tarPath))
		{
			echo "Creating package for {$pubId}_{$versionNum}...." . PHP_EOL;
			$pubModel = new Publication($pubId, null, $versionId);
			$pubModel->setCuration();
			$pubModel->_curationModel->package();
			echo "Finished creating package for {$pubId}_{$versionNum}...." . PHP_EOL;
		}
		if (empty($pubId) || $linkPath == false || !is_file($tarPath))
		{
			return false;
		}
		$link = $linkPath . '/' . $fileName;
		if (!is_file($link))
		{
			if (!link($tarPath, $link))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Remove link for publication package
	 *
	 * @return boolean
	 */
	protected function removeLink($pubId, $versionId, $versionNum, $doi='')
	{
		$bundleName = 'Resource' . '_' . $pubId;
		$bundleWithVersion = $bundleName . '_' . $versionNum;
		if ($doi != '')
		{
			$doi = str_replace('.', '_', $doi);
			$doi = str_replace('/', '_', $doi);
			$bundleName = $doi;
			// Set link to the same name as bundle if it is using DOI
			$bundleWithVersion = $doi;
		}

		$tarname = $bundleWithVersion . '.zip';
		$linkPath = $this->_linkPath();
		$link = $linkPath . '/' . $tarname;
		if ($link == false)
		{
			return false;
		}
		if (file_exists($link))
		{
			unlink($link);
		}
		return true;
	}

	/**
	 * Get path to link used for downloading package via SFTP
	 *
	 * @return 	mixed 	string if sftp path provided, false if not
	 */
	private function _linkPath()
	{
		$sftpPath = PATH_APP . Component::params('com_publications')->get('sftppath');
		if (!is_dir($sftpPath))
		{
			return false;
		}
		return $sftpPath;
	}
}
