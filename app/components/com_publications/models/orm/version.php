<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Models\Orm;

use Hubzero\Database\Relational;
use Hubzero\Utility\Str;
use Date;
use User;
use Lang;
use stdClass;
use Components\Publications\Models\PubCloud;

require_once __DIR__ . DS . 'attachment.php';
require_once __DIR__ . DS . 'author.php';
require_once __DIR__ . DS . 'license.php';
require_once \Component::path('com_publications') . DS . 'models' . DS . 'cloud.php';

/**
 * Model class for publication version
 */
class Version extends Relational implements \Hubzero\Search\Searchable
{
	/**
	 * State constants
	 *
	 * UNPUBLISHED = 0
	 * PUBLISHED   = 1
	 * DELETED     = 2
	 **/
	const STATE_DRAFT   = 3;
	const STATE_READY   = 4;
	const STATE_PENDING = 5;
	const STATE_WIP     = 7;

	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	public $namespace = 'publication';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'publication_id' => 'positive|nonzero'
	);

	/**
	 * Automatically fillable fields
	 *
	 * @var  array
	 */
	public $always = array(
		'modified',
		'modified_by'
	);

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	public $initiate = array(
		'created',
		'created_by'
	);

	/**
	 * Fields that have content that can/should be parsed
	 *
	 * @var  array
	 **/
	protected $parsed = array(
		'notes',
		'description'
	);

	public function transformStatusName()
	{
		$status = $this->get('state');
		$name = '';
		Lang::load('com_publications', \Component::path('com_publications') . '/admin');
		switch ($status)
		{
			case 0:
				$name = Lang::txt('COM_PUBLICATIONS_VERSION_UNPUBLISHED');
				break;

			case 1:
				$name = Lang::txt('COM_PUBLICATIONS_VERSION_PUBLISHED');
				break;

			case 3:
			default:
				$name = Lang::txt('COM_PUBLICATIONS_VERSION_DRAFT');
				break;

			case 4:
				$name = Lang::txt('COM_PUBLICATIONS_VERSION_READY');
				break;

			case 5:
				$name = Lang::txt('COM_PUBLICATIONS_VERSION_PENDING');
				break;

			case 7:
				$name = Lang::txt('COM_PUBLICATIONS_VERSION_WIP');
				break;
		}
		return $name;
	}

	/**
	 * Generates automatic modified field value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	public function automaticModified($data)
	{
		if (!isset($data['modified']) || !$data['modified'] || $data['modified'] == '0000-00-00 00:00:00')
		{
			$data['modified'] = Date::of('now')->toSql();
		}
		return $data['modified'];
	}

	/**
	 * Generates automatic modified by field value
	 *
	 * @param   array  $data  the data being saved
	 * @return  int
	 */
	public function automaticModifiedBy($data)
	{
		if (!isset($data['modified_by']) || !$data['modified_by'])
		{
			$data['modified_by'] = User::get('id');
		}
		return $data['modified_by'];
	}

	/**
	 * Establish relationship to project
	 *
	 * @return  object
	 */
	public function project()
	{
		return $this->publication->project;
	}

	/**
	 * Establish relationship to parent publication
	 *
	 * @return  object
	 */
	public function publication()
	{
		return $this->belongsToOne(__NAMESPACE__ . '\\Publication', 'publication_id');
	}

	/**
	 * Establish relationship to authors
	 *
	 * @return  object
	 */
	public function authors()
	{
		return $this->oneToMany(__NAMESPACE__ . '\\Author', 'publication_version_id');
	}

	/**
	 * Establish relationship to attachments
	 *
	 * @return  object
	 */
	public function attachments()
	{
		return $this->oneToMany(__NAMESPACE__ . '\\Attachment', 'publication_version_id');
	}

	/**
	 * Establish relationship to license
	 *
	 * @return  object
	 */
	public function license()
	{
		return $this->oneToOne(__NAMESPACE__ . '\\License', 'id', 'license_type');
	}

	/**
	 * Get the creator of this entry
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsToOne('Hubzero\User\User', 'created_by');
	}

	/**
	 * Get the modifier of this entry
	 *
	 * @return  object
	 */
	public function modifier()
	{
		return $this->belongsToOne('Hubzero\User\User', 'modified_by');
	}

	/**
	 * Establish relationship to curator
	 *
	 * @return  object
	 */
	public function curator()
	{
		return $this->belongsToOne('Hubzero\User\User', 'curator');
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function destroy()
	{
		// Remove authors
		foreach ($this->authors as $author)
		{
			if (!$author->destroy())
			{
				$this->addError($author->getError());
				return false;
			}
		}

		// Remove attachments
		foreach ($this->attachments as $attachment)
		{
			if (!$attachment->destroy())
			{
				$this->addError($attachment->getError());
				return false;
			}
		}

		// Attempt to delete the record
		return parent::destroy();
	}

	/**
	 * Check if the resource was deleted
	 *
	 * @return  bool
	 */
	public function isDeleted()
	{
		return ($this->get('state') == self::STATE_DELETED);
	}

	/**
	 * Check if the draft is ready
	 *
	 * @return  bool
	 */
	public function isReady()
	{
		return ($this->get('state') == self::STATE_READY);
	}

	/**
	 * Check if the resource is pending approval
	 *
	 * @return  bool
	 */
	public function isPending()
	{
		return ($this->get('state') == self::STATE_PENDING);
	}

	/**
	 * Check if the resource is pending author changes
	 *
	 * @return  bool
	 */
	public function isWorked()
	{
		return ($this->get('state') == self::STATE_WIP);
	}

	/**
	 * Is publication unpublished?
	 *
	 * @return  boolean
	 */
	public function isUnpublished()
	{
		return ($this->get('state') == self::STATE_UNPUBLISHED);
	}

	/**
	 * Check if the publication is published
	 *
	 * @return  bool
	 */
	public function isPublished()
	{
		if ($this->isNew())
		{
			return false;
		}

		if ($this->get('state') != self::STATE_PUBLISHED)
		{
			return false;
		}

		if ($this->get('published_up')
		 && $this->get('published_up') != '0000-00-00 00:00:00'
		 && $this->get('published_up') > Date::toSql())
		{
			return false;
		}

		if ($this->get('published_down')
		 && $this->get('published_down') != '0000-00-00 00:00:00'
		 && $this->get('published_down') < Date::toSql())
		{
			return false;
		}

		return true;
	}

	/**
	 * Is this main version
	 *
	 * @return  boolean
	 */
	public function isMain()
	{
		return ($this->get('main') == 1);
	}

	/**
	 * Is this dev version
	 *
	 * @return  boolean
	 */
	public function isDev()
	{
		return ($this->get('state') == self::STATE_DRAFT || $this->get('version_label') == 'dev');
	}

	/**
	 * Is this main published version?
	 *
	 * @return  boolean
	 */
	public function isCurrent()
	{
		return ($this->isMain() && $this->get('state') == self::STATE_PUBLISHED);
	}

	/**
	 * Does publication have future release date?
	 *
	 * @return  boolean
	 */
	public function isEmbargoed()
	{
		if (!$this->get('published_up') || $this->get('published_up') == '0000-00-00 00:00:00')
		{
			return false;
		}

		if (Date::of($this->get('published_up'))->toUnix() > Date::toUnix())
		{
			return true;
		}

		return false;
	}

	/**
	 * Return a formatted created timestamp
	 *
	 * @param   string  $as  Format (date, time, datetime, timeago, ...)
	 * @return  string
	 */
	public function created($as='')
	{
		return $this->_date('created', $as);
	}

	/**
	 * Return a formatted modified timestamp
	 *
	 * @param   string  $as  Format (date, time, datetime, timeago, ...)
	 * @return  string
	 */
	public function modified($as='')
	{
		return $this->_date('modified', $as);
	}

	/**
	 * Return a formatted published timestamp
	 *
	 * @param   string  $as  Format (date, time, datetime, timeago, ...)
	 * @return  string
	 */
	public function published($as='')
	{
		if ($this->get('accepted')
		 && $this->get('accepted') != '0000-00-00 00:00:00'
		 && $this->get('accepted') > $this->get('published_up'))
		{
			return $this->_date('accepted', $as);
		}
		return $this->_date('published_up', $as);
	}

	/**
	 * Return a formatted modified timestamp
	 *
	 * @param   string  $as  Format (date, time, datetime, timeago, ...)
	 * @return  string
	 */
	public function unpublished($as='')
	{
		return $this->_date('published_down', $as);
	}

	/**
	 * Return a formatted submitted timestamp
	 *
	 * @param   string  $as  Format (date, time, datetime, timeago, ...)
	 * @return  string
	 */
	public function submitted($as='')
	{
		return $this->_date('submitted', $as);
	}

	/**
	 * Return a formatted accepted timestamp
	 *
	 * @param   string  $as  Format (date, time, datetime, timeago, ...)
	 * @return  string
	 */
	public function accepted($as='')
	{
		return $this->_date('accepted', $as);
	}

	/**
	 * Return a formatted archived timestamp
	 *
	 * @param   string  $as  Format (date, time, datetime, timeago, ...)
	 * @return  string
	 */
	public function archived($as='')
	{
		return $this->_date('archived', $as);
	}

	/**
	 * Return a formatted released timestamp
	 *
	 * @param   string  $as  Format (date, time, datetime, timeago, ...)
	 * @return  string
	 */
	public function released($as='')
	{
		return $this->_date('released', $as);
	}

	/**
	 * Return a formatted timestamp
	 *
	 * @param   string  $key  Field to return
	 * @param   string  $as   What data to return
	 * @return  string
	 */
	protected function _date($key, $as='')
	{
		if (!$this->get($key) || $this->get($key) == '0000-00-00 00:00:00')
		{
			return '';
		}

		$dt = $this->get($key);

		switch (strtolower($as))
		{
			case 'date':
				$dt = Date::of($dt)->toLocal(Lang::txt('DATE_FORMAT_HZ1'));
				break;

			case 'time':
				$dt = Date::of($dt)->toLocal(Lang::txt('TIME_FORMAT_HZ1'));
				break;

			case 'datetime':
				$dt = $this->_date($key, 'date') . ' &#64; ' . $this->_date($key, 'time');
				break;

			case 'timeago':
				$dt = Date::of($dt)->relative();
				break;

			default:
				break;
		}

		return $dt;
	}

	/**
	 * Get the filespace path
	 *
	 * @return  string
	 */
	public function filespace()
	{
		$pid = Str::pad($this->get('publication_id'), 5);
		$vid = Str::pad($this->get('id'), 5);
		$sec = $this->get('secret');

		return PATH_APP . '/' . trim(\Component::params('com_publications')->get('webpath', '/site/publications'), '/') . '/' . $pid . '/' . $vid . '/' . $sec;
	}

	/**
	 * Check if this entry has an image
	 *
	 * @param   string   $type  The type of image
	 * @return  boolean
	 */
	public function hasImage($type = 'thumb')
	{
		// Build publication path
		$path = $this->filespace();

		if ($type == 'thumb')
		{
			$source = $path . DS . 'thumb.gif';

			// Check for default image
			if (!is_file($source))
			{
				$source = false;
			}
		}
		else
		{
			// Get master image
			$source = $path . DS . 'master.png';

			// Default image
			if (!is_file($source))
			{
				// Grab first bigger image in gallery
				if (is_dir($path . DS . 'gallery'))
				{
					$file_list = scandir($path . DS . 'gallery');

					foreach ($file_list as $file)
					{
						if ($file != '.' && $file != '..' && exif_imagetype($path . DS . 'gallery' . DS . $file))
						{
							list($width, $height, $type, $attr) = getimagesize($path . DS . 'gallery' . DS . $file);

							if ($width > 200)
							{
								$source = $path . DS . 'gallery' . DS . $file;
								break;
							}
						}
					}
				}

				if (!is_file($source))
				{
					$source = false;
				}
			}
		}

		return $source;
	}

	/**
	 * Split metadata into parts
	 *
	 * @return  array
	 */
	public function transformMetadata()
	{
		$data = array();

		preg_match_all("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", $this->get('metadata', ''), $matches, PREG_SET_ORDER);

		if (count($matches) > 0)
		{
			foreach ($matches as $match)
			{
				$data[$match[1]] = $match[2];
			}
		}

		return $data;
	}

	/**
	 * Generate link to current active version
	 *
	 * @param   string  $type
	 * @return  string
	 */
	public function link($type = '')
	{
		if (strpos($type, 'edit') !== false)
		{
			$base = $this->publication->project->isProvisioned()
				? 'index.php?option=com_publications&task=submit'
				: 'index.php?option=com_projects&alias=' . $this->publication->project->get('alias') . '&active=publications';
		} else {
			$id = $this->publication->get('alias') ? '&alias=' . $this->publication->get('alias') : '&id=' . $this->get('publication_id');
			$master_type = $this->publication->type;
			if ($master_type->ownergroup) {
				$group = \Hubzero\User\Group::getInstance($master_type->ownergroup);
				$base = 'index.php?option=com_groups&cn=' . $group->cn . '&active=publications';
			} else {
				$base = 'index.php?option=com_publications';
			}
			$base .= $id;
		}

		switch (strtolower($type))
		{
			case 'category':
				$link = 'index.php?option=com_publications&category=' . $this->publication->category->url_alias;
			break;

			case 'thumb':
				$src = Helpers\Html::getThumb($this->publication->get('id'), $this->get('id'), $this->publication->config());
				$link = with(new \Hubzero\Content\Moderator($src, 'public'))->getUrl();
				// $link = 'index.php?option=com_publications&id=' . $this->get('publication_id') . '&v=' . $this->get('id') . '&media=Image:thumb';
			break;

			case 'masterimage':
				$link = 'index.php?option=com_publications' . $id . '&v=' . $this->get('id') . '&media=Image:master';
			break;

			case 'serve':
				$link = $base . '&task=serve' . '&v=' . $this->get('version_number');
			break;

			case 'data':
				$link = $base . '&task=serve' . '&vid=' . $this->get('id');
			break;

			case 'citation':
				$link = $base . '&task=citation' . '&v=' . $this->get('version_number');
			break;

			case 'curate':
				$link = $base . '&controller=curation' . '&version=' . $this->get('version_number');
			break;

			case 'version':
				$link = $base . '&v=' . $this->get('version_number');
			break;

			case 'versionid':
				$link = $base . '&v=' . $this->get('id');
			break;

			case 'questions':
			case 'versions':
			case 'supportingdocs':
			case 'reviews':
			case 'wishlist':
			case 'citations':
				$link = $base . '&v=' . $this->get('version_number') . '&active=' . strtolower($type);
			break;

			case 'edit':
				$link = $this->get('publication_id') ? $base . '&pid=' . $this->get('publication_id') : $base;
			break;

			case 'editversion':
				$link = $base . '&pid=' . $this->get('publication_id') . '&version=' . $this->get('version_number');
			break;

			case 'editdev':
				$link = $base . '&pid=' . $this->get('publication_id') . '&version=dev';
			break;

			case 'editdefault':
				$link = $base . '&pid=' . $this->get('publication_id') . '&version=default';
			break;

			case 'editversionid':
				$link = $base . '&pid=' . $this->get('publication_id') . '&vid=' . $this->get('id');
			break;

			case 'editbase':
				$link = $base;
			break;

			case 'project':
				$link = $this->publication->project->isProvisioned()
					? 'index.php?option=com_publications&task=submit'
					: 'index.php?option=com_projects&alias=' . $this->publication->project->get('alias');
			break;

			case 'permalink':
			default:
				$link = $base;
			break;
		}

		return $link;
	}

	/**
	 * Get tag cloud
	 *
	 * @return  array
	 */
	public function tags($admin=null)
	{
		include_once \Component::path('com_tags') . '/models/cloud.php';

		$cloud = new \Components\Tags\Models\Cloud();

		$filters = array(
			'scope'    => 'publications',
			'scope_id' => $this->id
		);

		if (!is_null($admin)) {
			$filters['admin'] = $admin;
		}

		return $cloud->tags('list', $filters);
	}

	/**
	 * Get stats, such as number of views, downloads, comments, and adaptations
	 * 
	 * @return array
	 */
	public function stats()
	{
		$query = new \Hubzero\Database\Query;

		$stats = $query->select('* FROM (SELECT SUM(page_views) as views, SUM(primary_accesses + support_accesses) as downloads FROM #__publication_logs WHERE publication_version_id=' . $this->get('id') . ') AS views_downloads,
			(SELECT COUNT(*) as comments FROM #__item_comments WHERE item_type="publications" AND item_id=' . $this->get('id') . ') AS comments,
			(SELECT COUNT(*) as adaptations FROM `jos_publication_versions` WHERE forked_from=' . $this->get('id') . ' AND state=1) AS adaptations')
			->fetch();

		return $stats;
	}

	/**
	 * Namespace used for solr Search
	 *
	 * @return  string
	 */
	public static function searchNamespace()
	{
		return 'publication';
	}

	/**
	 * Generate solr search Id
	 *
	 * @return  string
	 */
	public function searchId()
	{
		return self::searchNamespace() . '-' . $this->publication->id;
	}

	/**
	 * Generate search document for Solr
	 *
	 * @return  array
	 */
	public function searchResult()
	{
		$activeVersion = $this->publication->getActiveVersion();
		if ($activeVersion->id != $this->id)
		{
			return false;
		}

		$obj = new stdClass;
		$obj->id = $this->searchId();
		$obj->hubtype = self::searchNamespace();
		$obj->title = $this->get('title');

		$description = $this->get('abstract') . ' ' . $this->get('description');
		$description = html_entity_decode($description);
		$description = \Hubzero\Utility\Sanitize::stripAll($description);

		$obj->description   = $description;
		$obj->url = rtrim(Request::root(), '/') . Route::urlForClient('site', $this->link('version'));
		$obj->doi = $this->get('doi');
		if ($this->get('published_up')) {
			$obj->publish_up = explode('+', Date::of($this->get('published_up'))->toISO8601())[0] . 'Z';
		}

		$obj->type = $this->publication->type->alias;

		$group = $this->publication->get('group_owner') ? $this->publication->group() : $activeVersion->project()->group();
		if ($group) {
			$obj->gid = $group->get('gidNumber');
			$obj->group = $group->get('description');
		}
 
		$tags = new PubCloud($this->id);
		$obj->keywords = $tags->render('string', array('type' => 'keywords'));

		// Focus areas (for faceting)
		$obj->tags = $tags->render('search', array('type' => 'focusareas'));

		// Focus areas (for general search)
		$obj->subject = $tags->render('string', array('type' => 'focusareas'));

		$authors = $this->authors;
		foreach ($authors as $author)
		{
			if ($author->role != 'submitter')
			{
				$obj->author[] = $author->name;
			}
		}

		// Stats
		$stats = $activeVersion->stats()[0];
		$obj->hits = $stats->views;
		$obj->hubid = $stats->downloads;

		$obj->owner_type = 'user';
		$obj->owner = $this->created_by;
		if ($this->statusName != 'published')
		{
			$obj->access_level = 'private';
		}
		elseif ($this->statusName == 'published')
		{
			if ($this->access == 0)
			{
				$obj->access_level = 'public';
			}
			elseif ($this->access == 1)
			{
				$obj->access_level = 'registered';
			}
			else
			{
				$obj->access_level = 'private';
			}
		}
		return $obj;
	}

	/**
	 * Get total number of records that will be indexed by Solr.
	 *
	 * @return  integer
	 */
	public static function searchTotal()
	{
		return self::all()->total();
	}

	/**
	 * Get records to be included in solr index
	 *
	 * @param   integer  $limit
	 * @param   integer  $offset
	 * @return  object   Hubzero\Database\Rows
	 */
	public static function searchResults($limit, $offset = 0)
	{
		return self::all()
			->start($offset)
			->limit($limit)
			->rows();
	}
}
