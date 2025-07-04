<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Courses\Models;

use Hubzero\Base\Model;
use Hubzero\Utility\Str;
use Components\Courses\Tables;
use Date;
use Lang;

require_once dirname(__DIR__) . DS . 'tables' . DS . 'announcement.php';
require_once __DIR__ . DS . 'base.php';

/**
 * Announcement model class for a course
 */
class Announcement extends Base
{
	/**
	 * Table class name
	 *
	 * @var string
	 */
	protected $_tbl_name = '\\Components\\Courses\\Tables\\Announcement';

	/**
	 * Model context
	 *
	 * @var string
	 */
	protected $_context = 'com_courses.announcement.content';

	/**
	 * Object scope
	 *
	 * @var string
	 */
	protected $_scope = 'announcement';

	/**
	 * Returns a reference to this model
	 *
	 * This method must be invoked as:
	 *     $offering = \Components\Courses\Models\Announcement::getInstance($alias);
	 *
	 * @param   integer $oid ID (int)
	 * @return  object
	 */
	static function &getInstance($oid=0)
	{
		static $instances;

		if (!isset($instances))
		{
			$instances = array();
		}

		if (!isset($instances[$oid]))
		{
			$instances[$oid] = new self($oid);
		}

		return $instances[$oid];
	}

	/**
	 * Return a formatted timestamp
	 *
	 * @param   string $as What data to return
	 * @return  string
	 */
	public function published($as='')
	{
		$dt = ($this->get('publish_up') && $this->get('publish_up') != '0000-00-00 00:00:00')
			? $this->get('publish_up')
			: $this->get('created');
		switch (strtolower($as))
		{
			case 'date':
				return Date::of($dt)->toLocal(Lang::txt('DATE_FORMAT_HZ1'));
			break;

			case 'time':
				return Date::of($dt)->toLocal(Lang::txt('TIME_FORMAT_HZ1'));
			break;

			default:
				return $dt;
			break;
		}
	}

	/**
	 * Get the content of the entry in various formats
	 *
	 * @param   string  $as      Format to return state in [text, number]
	 * @param   integer $shorten Number of characters to shorten text to
	 * @return  string
	 */
	public function content($as='parsed', $shorten=0)
	{
		$as = strtolower($as);
		$options = array();

		switch ($as)
		{
			case 'parsed':
				$content = $this->get('content_parsed', null);
				if ($content === null)
				{
					$config = array(
						'option'   => 'com_courses',
						'scope'    => 'courses',
						'pagename' => $this->get('id'),
						'pageid'   => 0,
						'filepath' => '',
						'domain'   => ''
					);

					$content = $this->get('content');
					$this->importPlugin('content')->trigger('onContentPrepare', array(
						$this->_context,
						&$this,
						&$config
					));

					$this->set('content_parsed', $this->get('content'));
					$this->set('content', $content);

					return $this->content($as, $shorten);
				}
				$options['html'] = true;
			break;

			case 'clean':
				$content = strip_tags($this->content('parsed'));
			break;

			case 'raw':
			default:
				$content = $this->get('content');
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content == null ? '' : $content);
				$content = html_entity_decode($content);
			break;
		}

		if ($shorten)
		{
			$content = Str::truncate($content, $shorten, $options);
		}
		return $content;
	}
}
