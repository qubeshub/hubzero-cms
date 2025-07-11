<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Courses\Models\Assets;

use Components\Courses\Tables;
use Component;
use Request;

/**
 * Default file asset handler class
 */
class File extends Handler
{
	/**
	 * Class info
	 *
	 * Action message - what the user will see if presented with multiple handlers for this extension
	 * Responds to    - what extensions this handler responds to
	 *
	 * @var  array
	 **/
	protected static $info = array(
		'action_message' => 'Post notes or slides (i.e. a downloadable file)',
		'responds_to'    => array(
			'txt', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'ppt',
			'pptx', 'pps', 'ppsx', 'doc', 'docx', 'xls', 'xlsx',
			'gz', 'zip', 'tgz', 'tar', 'mp3', 'm', 'cpp', 'c', 'r', 'rmd',
			'wm2d', 'slx', 'srt', 'ipynb', 'aedt', 'csv', 'xyz', 'prm', 'avi'
		),
	);

	/**
	 * Create method for this handler
	 *
	 * @return  array  of assets created
	 **/
	public function create($localPath = null)
	{
		// Include needed files
		require_once dirname(dirname(__DIR__)) . DS . 'tables' . DS . 'asset.association.php';
		require_once dirname(dirname(__DIR__)) . DS . 'tables' . DS . 'asset.php';
		require_once dirname(__DIR__) . DS . 'asset.php';

		if (!$localPath)
		{
			// Get the file
			if (isset($_FILES['files']))
			{
				$file = $_FILES['files']['name'][0];
				$size = (int) $_FILES['files']['size'];

			}
			else
			{
				return array('error' => 'No files provided');
			}
		}
		else
		{
			$file = $localPath;
			$size = filesize($file);
		}
		// Get the file extension
		$pathinfo = pathinfo($file);
		$filename = $pathinfo['filename'];
		$ext      = $pathinfo['extension'];

		// @FIXME: should these come from the global settings, or should they be courses specific
		// Get config
		$config = Component::params('com_media');

		// Max upload size
		$sizeLimit = (int) $config->get('upload_maxsize');
		$sizeLimit = $sizeLimit * 1024 * 1024;

		// Check to make sure we have a file and its not too big
		if ($size == 0)
		{
			return array('error' => 'File is empty');
		}
		if ($size > $sizeLimit)
		{
			$max = preg_replace('/<abbr \w+=\\"\w+\\">(\w{1,3})<\\/abbr>/', '$1', \Hubzero\Utility\Number::formatBytes($sizeLimit));
			return array('error' => "File is too large. Max file upload size is $max");
		}

		// Create our asset table object
		$assetObj = new Tables\Asset($this->db);

		$this->asset['title']      = $filename;
		$this->asset['type']       = (!empty($this->asset['type'])) ? $this->asset['type'] : 'file';
		$this->asset['subtype']    = (!empty($this->asset['subtype'])) ? $this->asset['subtype'] : 'file';
		$this->asset['url']        = $file;
		$this->asset['created']    = Date::toSql();
		$this->asset['created_by'] = App::get('authn')['user_id'];
		$this->asset['course_id']  = Request::getInt('course_id', 0);

		// Save the asset
		if (!$assetObj->save($this->asset))
		{
			return array('error' => 'Asset save failed');
		}

		// Create asset assoc object
		$assocObj = new Tables\AssetAssociation($this->db);

		$this->assoc['asset_id'] = $assetObj->get('id');
		$this->assoc['scope']    = Request::getCmd('scope', 'asset_group');
		$this->assoc['scope_id'] = Request::getInt('scope_id', 0);

		// Save the asset association
		if (!$assocObj->save($this->assoc))
		{
			return array('error' => 'Asset association save failed');
		}

		// Get courses config
		$cconfig = Component::params('com_courses');

		// Build the upload path if it doesn't exist
		$uploadDirectory = PATH_APP . DS . trim($cconfig->get('uploadpath', '/site/courses'), DS) . DS . $this->asset['course_id'] . DS . $this->assoc['asset_id'] . DS;

		// Make sure upload directory exists and is writable
		if (!is_dir($uploadDirectory))
		{
			if (!Filesystem::makeDirectory($uploadDirectory, 0755, true))
			{
				return array('error' => 'Server error. Unable to create upload directory');
			}
		}
		if (!is_writable($uploadDirectory))
		{
			return array('error' => 'Server error. Upload directory isn\'t writable');
		}

		// Get the final file path
		$target_path = $uploadDirectory . $filename . '.' . $ext;

		// Move the file to the site folder
		set_time_limit(60);
		if (!$localPath)
		{
			// Scan for viruses
			if (!Filesystem::isSafe($_FILES['files']['tmp_name'][0]))
			{
				// Scan failed, delete asset and association and return an error
				$assetObj->delete();
				$assocObj->delete();
				Filesystem::deleteDirectory($uploadDirectory);
				return array('error' => 'File rejected because the anti-virus scan failed.');
			}
			if (!$move = move_uploaded_file($_FILES['files']['tmp_name'][0], $target_path))
			{
				// Move failed, delete asset and association and return an error
				$assetObj->delete();
				$assocObj->delete();
				Filesystem::deleteDirectory($uploadDirectory);
				return array('error' => 'Move file failed');
			}
		}
		else
		{
			Filesystem::copy($localPath, $target_path);
		}

		// Get the url to return to the page
		$course_id      = Request::getInt('course_id', 0);
		$offering_alias = Request::getCmd('offering', '');
		$course         = new \Components\Courses\Models\Course($course_id);

		$url = Route::url('index.php?option=com_courses&controller=offering&gid='.$course->get('alias').'&offering='.$offering_alias.'&asset='.$assetObj->get('id'));
		$url = rtrim(str_replace('/api', '', Request::root()), '/') . '/' . ltrim($url, '/');

		$return_info = array(
			'asset_id'       => $this->assoc['asset_id'],
			'asset_title'    => $this->asset['title'],
			'asset_type'     => $this->asset['type'],
			'asset_subtype'  => $this->asset['subtype'],
			'asset_url'      => $url,
			'course_id'      => $this->asset['course_id'],
			'offering_alias' => Request::getCmd('offering', ''),
			'scope_id'       => $this->assoc['scope_id'],
			'asset_ext'      => $ext,
			'upload_path'    => $uploadDirectory,
			'target_path'    => $target_path
		);

		// Return info
		return array('assets' => $return_info);
	}
}
