<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Modules\Featuredresource;

use Components\Resources\Models\Entry;
use Hubzero\Module\Module;
use Component;
use User;

/**
 * Module class for displaying a random featured resource
 */
class Helper extends Module
{
	/**
	 * Container for properties
	 *
	 * @var  array
	 */
	public $id = 0;

	/**
	 * Generate module contents
	 *
	 * @return  void
	 */
	public function run()
	{
		include_once Component::path('com_resources') . DS . 'models' . DS . 'entry.php';

		$database = \App::get('db');

		//Get the admin configured settings
		$filters = array(
			'limit'      => 1,
			'start'      => 0,
			'type'       => $this->params->get('type', ''),
			'sortby'     => 'random',
			'minranking' => trim((string)$this->params->get('minranking', '6.0')),
			'tag'        => trim((string)$this->params->get('tag', '')),
			'access'     => 'public',
			'published'  => 1,
			'standalone' => 1,
			// Only published tools
			'toolState'  => 7
		);

		$rows = Entry::allWithFilters($filters)
			->limit(1000)
			->rows()
			->fieldsByKey('id');

		if (!empty($rows))
		{
			$id = array_rand($rows);

			$row = Entry::oneOrNew((isset($rows[$id]) ? $rows[$id] : 0));
		}

		$this->cls = trim($this->params->get('moduleclass_sfx',''));
		$this->txt_length = trim($this->params->get('txt_length',''));
		$this->thumb = '';

		// Did we get any results?
		if (isset($row) && $row->get('id'))
		{
			$config = Component::params('com_resources');

			// Resource
			$id = $row->id;

			$path = $row->filespace();

			if ($row->isTool())
			{
				include_once Component::path('com_tools') . DS . 'tables' . DS . 'version.php';

				$tv = new \Components\Tools\Tables\Version($database);

				$versionid = $tv->getVersionIdFromResource($id, 'current');

				$picture = $this->getToolImage($path, $versionid);
			}
			else
			{
				$picture = $this->getImage($path);
			}

			$thumb = $path . DS . $picture;

			if (!is_file(PATH_APP . $thumb))
			{
				$thumb = DS . trim($config->get('defaultpic',''));
			}

			if ($row->typetitle)
			{
				$row->typetitle = trim($row->typetitle);
				if (substr($row->typetitle, -1, 1) == 's' && substr($row->typetitle, -3, 3) != 'ies')
				{
					$row->typetitle = substr($row->typetitle, 0, strlen($row->typetitle) - 1);
				}
			}

			$this->id    = $id;
			$this->thumb = $thumb;
		}
		else
		{
			$row = '';
		}

		$this->row = $row;

		require $this->getLayoutPath();
	}

	/**
	 * Display module contents
	 *
	 * @return     void
	 */
	public function display()
	{
		if ($content = $this->getCacheContent())
		{
			echo $content;
			return;
		}

		$this->run();
	}

	/**
	 * Get a resource image
	 *
	 * @param   string  $path  Path to get resource image from
	 * @return  string
	 */
	private function getImage($path)
	{
		$d = (is_dir($path)) ? dir($path) : '';

		$images = array();

		if ($d)
		{
			while (false !== ($entry = $d->read()))
			{
				$img_file = $entry;
				if (is_file($path . DS . $img_file)
				 && substr($entry, 0, 1) != '.'
				 && strtolower($entry) !== 'index.html')
				{
					if (preg_match("#bmp|gif|jpg|png#i", $img_file))
					{
						$images[] = $img_file;
					}
				}
			}

			$d->close();
		}

		$b = 0;
		if ($images)
		{
			foreach ($images as $ima)
			{
				$bits = explode('.', $ima);
				$type = array_pop($bits);
				$img  = implode('.', $bits);

				if ($img == 'thumb')
				{
					return $ima;
				}
			}
		}
	}

	/**
	 * Get a screenshot of a tool
	 *
	 * @param   string   $path       Path to look for screenshots in
	 * @param   integer  $versionid  Tool version
	 * @return  string
	 */
	private function getToolImage($path, $versionid=0)
	{
		// Get contribtool parameters
		$tconfig = Component::params('com_tools');
		$allowversions = $tconfig->get('screenshot_edit');

		if ($versionid && $allowversions)
		{
			// Add version directory
			//$path .= DS.$versionid;
		}

		return $this->getImage($path);
	}
}
