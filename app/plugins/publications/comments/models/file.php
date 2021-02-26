<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Plugins\Publications\Comments\Models;

use Hubzero\Item\Comment\File as ItemFile;
use Request;

/**
 * Model class for a forum post attachment
 */
class File extends ItemFile
{
	/**
	 * Generate and return various links to the entry
	 * Link will vary depending upon action desired, such as edit, delete, etc.
	 *
	 * @param   string  $type  The type of link to return
	 * @return  string
	 */
	public function link($type='')
	{
		static $path;

		if (!$path)
		{
			$path = $this->getUploadDir();
		}

		// If it doesn't exist or isn't published
		switch (strtolower($type))
		{
			case 'base':
				$link = $path . DS . $this->get('comment_id');
			break;

			case 'path':
			case 'filepath':
				$link = $path . DS . $this->get('comment_id') . DS . $this->get('filename');
			break;

			case 'permalink':
			default:
				$link = rtrim(Request::base(), '/') . substr($this->getUploadDir(), strlen(PATH_ROOT)) . '/' . $this->get('comment_id') . '/' . $this->get('filename');
			break;
		}

		return $link;
	}
}

