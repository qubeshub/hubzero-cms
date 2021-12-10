<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Plugins\Publications\Comments\Models;

use Hubzero\Item\Comment as ItemComment;
use Components\Publications\Models\Orm\Version as Version;

require_once __DIR__ . '/file.php';
require_once Component::path('com_publications') . DS . 'models' . DS . 'orm' . DS . 'version.php';
require_once Component::path('com_publications') . DS . 'models' . DS . 'orm' . DS . 'publication.php';

/**
 * Model for a comment
 */
class Comment extends ItemComment
{
	/**
	 * Flagged state
	 *
	 * @var  integer
	 */
	const STATE_FLAGGED = 3;

	/**
	 * URL for this entry
	 *
	 * @var  string
	 */
	private $_base = null;

	/**
	 * Get a list of files
	 *
	 * @return  object
	 */
	public function files()
	{
		return $this->oneToMany('Plugins\Publications\Comments\Models\File', 'comment_id');
	}

	/**
	 * Generate and return various links to the entry
	 * Link will vary depending upon action desired, such as edit, delete, etc.
	 *
	 * @param   string  $type  The type of link to return
	 * @return  string
	 */
	public function link($type='')
	{
		if (!isset($this->_base))
		{
			$this->_base = $this->get('url', 'index.php?option=com_' . $this->get('item_type') . '&id=' . $this->get('item_id') . '&active=comments');
		}
		$link = $this->_base;

		// check for page slug  (remove for now)
		$slug = '';
		if (strpos($link, '#') !== false)
		{
			list($link, $slug) = explode('#', $link);
			$slug = "#{$slug}";
		}

		$s = '&';
		if (strstr($link, '?') === false)
		{
			$s = '?';
		}

		// If it doesn't exist or isn't published
		switch (strtolower($type))
		{
			case 'base':
				$link .= $slug;
			break;

			case 'delete':
				$link .= $s . 'action=commentdelete&comment=' . $this->get('id') . $slug;
			break;

			case 'abuse':
			case 'report':
				$link = 'index.php?option=com_support&task=reportabuse&category=itemcomment&id=' . $this->get('id') . '&parent=' . $this->get('parent');
			break;

			case 'permalink':
				// Little complicated to allow for supergroup overrides
				$version = Version::one($this->get('item_id'));
				$link = $version->link('versionid');
				$link = $link . (!strpos($link, 'active=publications') ? '&active=comments' : '');
			default:
				$link .= '#c' . $this->get('id');
			break;
		}

		return $link;
	}

	/**
	 * Saves the current model to the database (override so don't save state to replies)
	 *
	 * @return  bool
	 */
	public function save()
	{
		// Need to skip save method from parent which saves state to replies
		return call_user_func(array(get_parent_class(get_parent_class($this)), 'save'));
	}
}
