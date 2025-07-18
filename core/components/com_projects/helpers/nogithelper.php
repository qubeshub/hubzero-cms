<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Projects\Helpers;

use Hubzero\Base\Obj;
use Hubzero\Filesystem\Manager;
use Component;
use User;
use Lang;

/**
 * Projects Git helper class
 */
class Nogit extends Obj
{
	/**
	 * User ID
	 *
	 * @var integer
	 */
	private $_uid = null;

	/**
	 * Full path to Git repo
	 *
	 * @var string
	 */
	private $_path = null;

	/**
	 * Constructor
	 *
	 * @param   string  $path
	 * @return  void
	 */
	public function __construct($path = null)
	{
		// Get component configs
		$configs = Component::params('com_projects');

		// Set repo path
		$this->_path = $path;
		$params = [];
		$params['path'] = $this->_path;
		$this->adapter = Manager::adapter('local', $params);

		// Set acting user
		$this->_uid = User::get('id');
	}

	/**
	 * Init Git repository
	 *
	 * @param   string  $path  Repo path
	 * @return  string
	 */
	public function iniGit($path = null)
	{
		if (!$path)
		{
			$path = $this->_path;
		}
		if (!$path || !is_dir($path))
		{
			return false;
		}
		if (!$this->_path)
		{
			$this->_path = $path;
		}
		return true;
	}

	/**
	 * Make a call
	 *
	 * @param   string  $call
	 * @return  array   to be parsed
	 */
	public function call($call = '')
	{
		if (!$this->_path || !is_dir($this->_path) || !$call)
		{
			return false;
		}

		// cd into repo
		chdir($this->_path);

		// exec call
		return $this->_exec($call);
	}

	/**
	 * Exec call
	 *
	 * @param   string  $call
	 * @return  array   to be parsed
	 */
	protected function _exec($call = '')
	{
		if (!$call)
		{
			return false;
		}

		$result = array();

		// exec call
		exec($call, $result);
		return $result;
	}

	/**
	 * Get file content
	 *
	 * @param   string  $file    file path
	 * @param   string  $target  Output content to path
	 * @return  bool
	 */
	public function getContent($file = '', $target = '')
	{
		if (!$file)
		{
			return false;
		}
		$this->call($call = 'cat  ' . escapeshellarg($file));

		return true;
	}

	/**
	 * Parse response
	 *
	 * @param   array  	$out     Array of data to parse
	 * @param   string  $return
	 * @return  mixed
	 */
	public function parseLog($out = array(), $return = 'date')
	{
		return '';
	}

	/**
	 * Lists files in the repository
	 *
	 * @param   string  $subdir  Local directory path
	 * @return  array
	 */
	public function getFiles($subdir = '', $recursive = false)
	{
		$files = $this->adapter->listContents($subdir, $recursive);
		$out = array();
		foreach ($files as $file)
		{
			$out[] = $file->path;
		}
		return $out;
	}

	/**
	 * Lists the directories in the repository
	 *
	 * @param   string  $subdir  Local directory path
	 * @return  array
	 */
	public function getDirectories($subdir = '', $recursive = false)
	{
		$files = $this->adapter->listContents($subdir, $recursive);
		$out = array();
		foreach ($files as $file)
		{
			if ($file->isDir())
			{
				$out[] = $file->path;
			}
		}
		return $out;
	}

	/**
	 * Get changes for sync
	 *
	 * @param   string  $localPath
	 * @param   string  $synced
	 * @param   string  $localDir
	 * @param   array   $localRenames
	 * @param   array   $connections
	 * @return  array
	 */
	public function getChanges($localPath, $synced, $localDir, &$localRenames, $connections)
	{
		//Stub for compatibility
		return array();
	}

	/**
	 * Make Git recognize empty folder
	 *
	 * @param   string  $dir
	 * @param   bool    $commit
	 * @param   string  $commitMsg
	 * @return  bool
	 */
	public function makeEmptyFolder($dir = '', $commit = true, $commitMsg = '')
	{
		// Check for repo
		if (!$this->_path || !is_dir($this->_path))
		{
			return false;
		}

		// cd into repo
		chdir($this->_path);

		// Git add
		$this->gitAdd($dir, $commitMsg);

		if ($commit == false)
		{
			return true;
		}

		// Commit change
		if ($this->gitCommit(Lang::txt('PLG_PROJECTS_FILES_CREATED_DIRECTORY') . '  ' . escapeshellarg($dir)))
		{
			return true;
		}

		return false;
	}

	/**
	 * Filter ASCII
	 *
	 * @param   array    $out
	 * @param   boolean  $diff   Format as diff?
	 * @param   boolean  $color  Color changes?
	 * @param   int      $max    Max number of lines
	 * @return  string
	 */
	public static function filterASCII($out = array(), $diff = false, $color = false, $max = 200)
	{
		$text = '';
		$o = 1;
		$found = 0;

		// Cut number of lines
		if (count($out) > $max)
		{
			$out = array_slice($out, 0, $max);
		}

		foreach ($out as $line)
		{
			$encoding = mb_detect_encoding($line);

			if ($encoding != "ASCII")
			{
				break;
			}
			else
			{
				if ($diff)
				{
					if (substr($line, 0, 2) == '@@')
					{
						$found = 1;
						continue;
					}

					if ($found == 0)
					{
						continue;
					}

					if ($color)
					{
						if (substr($line, 0, 1) == '+')
						{
							$line = trim($line, '+');
							$line = '<span class="rev-added">' . htmlentities($line) . '</span>';
						}
						if (substr($line, 0, 1) == '-')
						{
							$line = trim($line, '-');
							$line = '<span class="rev-removed">' . htmlentities($line) . '</span>';
						}
					}
					else
					{
						$line = htmlentities($line);
					}

					$line = trim($line, '+');
					$line = trim($line, '-');
				}
				else
				{
					$line = htmlentities($line);
				}

				$text.=  $line != '' ? $line . "\n" : "\n";
			}

			$o++;
		}

		$text = preg_replace("/\\\ No newline at end of file/", "", $text);

		return trim($text);
	}

	/**
	 * Determine if last change was a rename
	 *
	 * @param   string  $file   file path
	 * @param   string  $hash   Git hash
	 * @param   string  $since
	 * @return  array   to be parsed
	 */
	public function getRename($file = '', $hash = '', $since = '')
	{
		$renames = $this->gitLog($file, '', 'rename');
		$rename = '';

		$hashes = $this->getLocalFileHistory($file);
		$new    = $this->getLocalFileHistory($file, '', $since);
		$fetch  = 1;

		if (count($renames) > 0)
		{
			foreach ($hashes as $h)
			{
				// get commit message
				if ($since && in_array($h, $new))
				{
					$message = $this->gitLog($file, $h, 'message');

					if (!preg_match("/Moved/", $message))
					{
						$fetch = 0;
					}
				}

				$abbr = substr($h, 0, 7);

				if (isset($renames[$abbr]) && $renames[$abbr] != $file)
				{
					$rename = $renames[$abbr];

					return $fetch == 1 ? $rename : null;
				}
			}
		}
		else
		{
			return null;
		}
	}
	/**
	 * Show commit log detail
	 *
	 * @param   string  $file    file path
	 * @param   string  $hash    Git hash
	 * @param   string  $return
	 * @return  string
	 */
	public function gitLog($file = '', $hash = '', $return = 'date')
	{
		if (!$this->_path || !is_dir($this->_path))
		{
			return false;
		}

		$file_obj = new Models\File($file, $this->_path);
		$out = '';

		// Set exec command for retrieving different commit information
		switch ($return)
		{
			case 'combined':
				$out = 'combined';
				break;

			case 'num':
				$out = 'num';
				break;

			case 'author':
				$out = 'author';
				break;

			case 'email':
				$out = 'email';
				break;

			case 'message':
				$out = 'message';
				break;

			case 'size':
				$out = 'size';
				break;

			case 'diff':
				$out = 'diff';
				break;

			case 'content':
				$out = 'content';
				break;

			case 'rename':
				$out = 'rename';
				break;

			case 'namestatus':
				$out = 'namestatus';
				break;

			case 'blob':
				$out = 'blob';
				break;

			case 'hash':
			case 'timestamp':
			case 'date':
			default:
				$out = $file_obj->get('date')->toUnix();
				break;

		}

		// Parse returned array of data
		return $out;
	}
}
