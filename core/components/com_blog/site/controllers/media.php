<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Blog\Site\Controllers;

use Components\Blog\Models\Archive;
use Components\Blog\Models\Entry;
use Hubzero\Component\SiteController;
use Hubzero\Content\Server;
use InvalidArgumentException;
use RuntimeException;
use DirectoryIterator;
use Filesystem;
use Request;
use User;
use Lang;

/**
 * Blog controller class for media
 */
class Media extends SiteController
{
	/**
	 * Download a file
	 *
	 * @return  void
	 */
	public function downloadTask()
	{
		$archive = new Archive('site', 0);

		$entry = Entry::oneByScope(
			Request::getString('alias', ''),
			'site',
			0
		);

		if (!$entry->get('id') || !$entry->access('view'))
		{
			throw new \Exception(Lang::txt('Access denied.'), 403);
		}

		if (!($file = Request::getString('file', '')))
		{
			$parts = explode('/', $_SERVER['REQUEST_URI']);

			$filename = array_pop($parts);

			// Get the file name
			if (substr(strtolower($filename), 0, strlen('image:')) == 'image:')
			{
				$file = substr($filename, strlen('image:'));
			}
			elseif (substr(strtolower($filename), 0, strlen('file:')) == 'file:')
			{
				$file = substr($filename, strlen('file:'));
			}
		}

		// Decode file name
		$file = urldecode($file);

		// Build file path
		$file_path = $archive->filespace() . DS . $file;

		// Ensure the file exist
		if (!file_exists($file_path))
		{
			throw new InvalidArgumentException(Lang::txt('The requested file could not be found: %s', $file), 404);
		}

		// Serve up the image
		$server = new Server();
		$server->filename($file_path);
		$server->disposition('inline');
		$server->acceptranges(false); // @TODO fix byte range support

		// Serve up file
		if (!$server->serve())
		{
			// Should only get here on error
			throw new RuntimeException(Lang::txt('An error occurred while trying to output the file'), 500);
		}

		exit;
	}

	/**
	 * Upload a file or create a new folder
	 *
	 * @return  void
	 */
	public function uploadTask()
	{
		// Check if they're logged in
		if (User::isGuest())
		{
			return $this->displayTask();
		}

		// Incoming file
		$file = Request::getArray('upload', '', 'files');

		if (!$file['name'] || $file['size'] == 0)
		{
			$this->setError(Lang::txt('COM_BLOG_NO_FILE'));
			return $this->displayTask();
		}

		// Incoming
		$archive = new Archive(
			Request::getWord('scope', 'site'),
			Request::getInt('id', 0)
		);

		// Build the file path
		$path = $archive->filespace();

		if (!is_dir($path))
		{
			if (!Filesystem::makeDirectory($path))
			{
				$this->setError(Lang::txt('COM_BLOG_UNABLE_TO_CREATE_UPLOAD_PATH'));
				return $this->displayTask();
			}
		}

		// Make the filename safe
		$file['name'] = Filesystem::clean($file['name']);

		// Ensure file names fit.
		$ext = Filesystem::extension($file['name']);

		// Get media config
		$mediaConfig = Component::params('com_media');

		// Check that the file type is allowed
		$allowed = array_values(array_filter(explode(',', $mediaConfig->get('upload_extensions'))));

		if (!empty($allowed) && !in_array(strtolower($ext), $allowed))
		{
			$this->setError(Lang::txt('COM_BLOG_ERROR_UPLOADING_INVALID_FILE', implode(', ', $allowed)));

			return $this->displayTask();
		}

		// Size limit is in MB, so we need to turn it into just B
		$sizeLimit = $mediaConfig->get('upload_maxsize', 10);
		$sizeLimit = $sizeLimit * 1024 * 1024;

		if ($file['size'] > $sizeLimit)
		{
			$this->setError(Lang::txt('COM_BLOG_ERROR_UPLOADING_FILE_TOO_BIG', \Hubzero\Utility\Number::formatBytes($sizeLimit)));

			return $this->displayTask();
		}

		// Make sure the file name is unique
		$file['name'] = str_replace(' ', '_', $file['name']);
		if (strlen($file['name']) > 230)
		{
			$file['name']  = substr($file['name'], 0, 230);
			$file['name'] .= '.' . $ext;
		}

		// Perform the upload
		if (!Filesystem::upload($file['tmp_name'], $path . DS . $file['name']))
		{
			$this->setError(Lang::txt('COM_BLOG_ERROR_UPLOADING'));
		}

		// Virus scan
		if (!Filesystem::isSafe($path . DS . $file['name']))
		{
			Filesystem::delete($path . DS . $file['name']);

			$this->setError(Lang::txt('COM_BLOG_ERROR_UPLOADING'));
		}

		// Push through to the media view
		$this->displayTask();
	}

	/**
	 * Deletes a folder
	 *
	 * @return  void
	 */
	public function deletefolderTask()
	{
		// Check for request forgeries
		Request::checkToken('get');

		// Check if they're logged in
		if (User::isGuest())
		{
			return $this->displayTask();
		}

		// Incoming file
		$file = trim(Request::getString('folder', '', 'get'));
		if (!$file)
		{
			$this->setError(Lang::txt('COM_BLOG_NO_DIRECTORY'));
			return $this->displayTask();
		}

		// Incoming
		$archive = new Archive(
			Request::getWord('scope', 'site'),
			Request::getInt('id', 0)
		);

		// Build the file path
		$folder = $archive->filespace();

		// Delete the folder
		if (is_dir($folder))
		{
			// Attempt to delete the file
			if (!Filesystem::deleteDirectory($folder))
			{
				$this->setError(Lang::txt('COM_BLOG_UNABLE_TO_DELETE_DIRECTORY'));
			}
		}

		// Push through to the media view
		$this->displayTask();
	}

	/**
	 * Deletes a file
	 *
	 * @return  void
	 */
	public function deletefileTask()
	{
		// Check for request forgeries
		Request::checkToken('get');

		// Check if they're logged in
		if (User::isGuest())
		{
			return $this->displayTask();
		}

		// Incoming file
		$file = trim(Request::getString('file', '', 'get'));
		if (!$file)
		{
			$this->setError(Lang::txt('COM_BLOG_NO_FILE'));
			return $this->displayTask();
		}

		// Incoming
		$archive = new Archive(
			Request::getWord('scope', 'site'),
			Request::getInt('id', 0)
		);

		// Build the file path
		$path = $archive->filespace();

		if (!file_exists($path . DS . $file) or !$file)
		{
			$this->setError(Lang::txt('COM_BLOG_FILE_NOT_FOUND'));
			return $this->displayTask();
		}

		// Attempt to delete the file
		if (!Filesystem::delete($path . DS . $file))
		{
			$this->setError(Lang::txt('COM_BLOG_UNABLE_TO_DELETE_FILE'));
		}

		// Push through to the media view
		$this->displayTask();
	}

	/**
	 * Display an upload form and file listing
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Output HTML
		$archive = new Archive(
			Request::getWord('scope', 'site'),
			Request::getInt('id', 0)
		);

		$this->view
			->set('archive', $archive)
			->setLayout('display')
			->setErrors($this->getErrors())
			->display();
	}

	/**
	 * Lists all files and folders for a given directory
	 *
	 * @return  void
	 */
	public function listTask()
	{
		// Incoming
		$archive = new Archive(
			Request::getWord('scope', 'site'),
			Request::getInt('id', 0)
		);

		// Build the file path
		$path = $archive->filespace();

		$folders = array();
		$files   = array();

		if (!$this->getError() && is_dir($path))
		{
			$files   = Filesystem::files($path);
			$folders = Filesystem::directories($path);
		}

		$this->view
			->set('archive', $archive)
			->set('docs', $files)
			->set('folders', $folders)
			->setErrors($this->getErrors())
			->display();
	}
}
