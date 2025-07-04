<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Helpers;

use Components\Publications\Models\PubCloud;
use Components\Tags\Models\FocusArea;
use Route;
use Date;
use Lang;

require_once \Component::path('com_publications') . DS . 'models' . DS . 'cloud.php';

/**
 * Publication Tagging class
 */
class Tags extends \Hubzero\Base\Obj
{
	/**
	 * Database
	 *
	 * @var unknown
	 */
	public $_db  = null;

	/**
	 * Object type, used for linking objects (such as resources) to tags
	 *
	 * @var string
	 */
	public $_tbl = 'tags';

	/**
	 * The object to be tagged
	 *
	 * @var unknown
	 */
	public $_oid = null;

	/**
	 * The primary tag table
	 *
	 * @var string
	 */
	public $_tag_tbl = '#__tags';

	/**
	 * Tag/object mapping table
	 *
	 * @var string
	 */
	public $_obj_tbl = '#__tags_object';

	/**
	 * Constructor
	 *
	 * @param      object $db     Database
	 * @param      array  $config Optional configurations
	 * @return     void
	 */
	public function __construct($db, $config=array())
	{
		$this->_db  = $db;
		$this->_tbl = 'publications';
	}

	/**
	 * Get all the tags on an object
	 *
	 * @param      integer $object_id Object ID
	 * @param      integer $offset    Record offset
	 * @param      integer $limit     Record limit
	 * @param      integer $tagger_id Tagger ID (set this if you want to restrict tags only added by a specific user)
	 * @param      integer $strength  Tag strength (set this if you want to restrict tags by strength)
	 * @param      integer $admin     Has admin access?
	 * @return     array
	 */
	public function get_tags_on_object($object_id, $offset=0, $limit=10, $tagger_id=null, $strength=0, $admin=0, $label='')
	{
		$cloud = new PubCloud($object_id, $this->_tbl);
		return $cloud->tags('list', [
			'objectid' => $object_id,
			'tbl' => $this->_tbl
		]);
	}

	/**
	 * Add a tag to an object
	 * This will:
	 * 1) First, check if the tag already exists
	 *    a) if not, creates a database entry for the tag
	 * 2) Adds a reference linking tag with object
	 *
	 * @param      integer $tagger_id Tagger ID
	 * @param      integer $object_id Object ID
	 * @param      string  $tag       Tag
	 * @param      integer $strength  Tag strength
	 * @return     boolean True on success, false if errors
	 */
	public function safe_tag($tagger_id, $object_id, $tag, $strength=1, $label='')
	{
		if (!isset($tagger_id) || !isset($object_id) || !isset($tag))
		{
			$this->setError('safe_tag argument missing');
			return false;
		}

		$cloud = new PubCloud($object_id, $this->_tbl);
		return $cloud->add($tag, $tagger_id, 0, $strength, $label);
	}

	/**
	 * Get tags on a publication
	 *
	 * @param      integer $id        Resource ID
	 * @param      integer $tagger_id Tagger ID
	 * @param      integer $strength  Tag strength
	 * @param      integer $admin     Admin flag
	 * @return     array
	 */
	public function getTags($id, $tagger_id=0, $strength=0, $admin=0)
	{
		$sql = "SELECT DISTINCT t.* FROM $this->_tag_tbl AS t, $this->_obj_tbl AS rt WHERE ";

		$where = array();
		$where[] = "rt.objectid=$id";
		$where[] = "rt.tbl='$this->_tbl'";
		$where[] = "rt.tagid=t.id";
		if ($admin != 1)
		{
			$where[] = "t.admin=0";
		}
		if ($tagger_id != 0)
		{
			$where[] = "rt.taggerid=" . $this->_db->quote($tagger_id);
		}
		if ($strength)
		{
			$where[] = "rt.strength=" . $this->_db->quote($strength);
		}

		$sql .= implode(" AND ", $where) . " ORDER BY t.raw_tag";

		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}
	
	/**
	 * Get all user tags of a publication
	 *
	 * @param      integer 		$vid		Publication version ID
	 *
	 * @return     array or false
	 */
	public function getAllUserTags($vid)
	{
		$sql = "SELECT DISTINCT jt.* FROM $this->_tag_tbl AS jt LEFT JOIN $this->_obj_tbl AS jto ON jt.id = jto.tagid 
		WHERE jt.admin = 0 AND jto.tbl = 'publications' AND jto.objectid = $vid";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get all tags with a publication association
	 *
	 * @param      integer $id   Publication ID
	 * @param      integer $cat	 Publication category (optional)
	 * @param      string  $tag  Parameter description (if any) ...
	 * @return     array
	 */
	public function get_tags_with_objects($id=0, $category=0, $tag='')
	{
		$now = Date::toSql();

		$this->_db->setQuery("SELECT objectid FROM $this->_tag_tbl AS t,
			$this->_obj_tbl AS o WHERE o.tagid=t.id AND t.tag='$tag' AND o.tbl='$this->_tbl'");
		$objs = $this->_db->loadObjectList();

		$ids = '';
		if ($objs)
		{
			$s = array();
			foreach ($objs as $obj)
			{
				$s[] = $obj->objectid;
			}
			$ids = implode(',', $s);
		}

		$sql = "SELECT t.id, t.tag, t.raw_tag, r.id AS rid, 0 AS ucount, NULL AS rids
				FROM $this->_tag_tbl AS t, $this->_obj_tbl AS o, `#__publications` AS r
				JOIN `#__publication_versions` as V ON V.publication_id = r.id AND V.main = 1
				WHERE o.tbl='$this->_tbl'
				AND o.tagid=t.id
				AND t.admin=0
				AND o.objectid=V.id
				AND V.state=1
				AND (V.publish_up IS NULL OR V.publish_up = '0000-00-00 00:00:00' OR V.publish_up <= '$now')
				AND (V.publish_down IS NULL OR V.publish_down = '0000-00-00 00:00:00' OR V.publish_down >= '$now') ";
		if ($category)
		{
			$sql .= "AND r.category=" . $this->_db->quote($category) . " ";
		}

		if (!User::isGuest())
		{
			$sql .= "AND (V.access=0 OR V.access=1 OR V.access=2) ";
		}
		else
		{
			$sql .= "AND (V.access=0 OR V.access=2) ";
		}
		if ($ids)
		{
			$sql .= "AND o.objectid IN ($ids) ";
		}
		$sql .= "ORDER BY t.raw_tag ASC";

		$this->_db->setQuery($sql);
		$results = $this->_db->loadObjectList();

		$rows = array();
		if ($results)
		{
			foreach ($results as $result)
			{
				if (!isset($rows[$result->id]))
				{
					$rows[$result->id] = $result;
					$rows[$result->id]->ucount++;
					$rows[$result->id]->rids = array($result->rid);
				}
				else
				{
					if (!in_array($result->rid, $rows[$result->id]->rids))
					{
						$rows[$result->id]->ucount++;
						$rows[$result->id]->rids[] = $result->rid;
					}
				}
			}
		}
		return $rows;
	}

	/**
	 * Tag an object
	 * This will get a list of old tags on object and will
	 * 1) add any new tags not in the old list
	 * 2) remove any tags in the old list not found in the new list
	 *
	 * @param      integer $tagger_id  Tagger ID
	 * @param      integer $object_id  Object ID
	 * @param      string  $tag_string String of comma-separated tags
	 * @param      integer $strength   Tag strength
	 * @param      boolean $admin      Has admin access?
	 * @return     boolean True on success, false if errors
	 */
	public function tag_object($tagger_id, $object_id, $tag_string, $strength, $admin=false, $label='')
	{
		$cloud = new PubCloud($object_id, $this->_tbl);
		return $cloud->setTags($tag_string, $tagger_id, $admin, $strength, $label);
	}

	/**
	 * [TEMPORARY] Tag an existing object
	 * 	Only called when saving tags from admin interface
	 *
	 * @param      integer $object_id  Object ID
	 * @param      string  $tag_string String of comma-separated tags
	 */
	public function tag_existing_object($object_id, $tags)
	{
		// Going to manually do this like in Resources by deleting and then re-adding.
		// NOTE: This changes the time stamp!  Refactor:  Modify com_tags/models/cloud::setTags
		// Should be able to use $rt->setTags, giving comma separated string of tags (don't need label!)
		$rt = new \Components\Tags\Models\Cloud($object_id, 'publications');
		$this->_db->setQuery('DELETE FROM `#__tags_object` WHERE tbl = \'publications\' AND objectid = ' . $object_id);
		$this->_db->execute();
		foreach ($tags as $tag) {
			$rt->add($tag, User::get('id'), 0, 1); // Ignore label
		}
	}

	/**
	 * Remove a tag on an object
	 *
	 * @param      integer $tagger_id Tagger ID
	 * @param      integer $object_id Object ID
	 * @param      string  $tag       Tag to remove
	 * @param      integer $admin     Has admin access?
	 * @return     boolean True on success, false if errors
	 */
	public function remove_tag($tagger_id, $object_id, $tag, $admin)
	{
		$cloud = new PubCloud($object_id, $this->_tbl);
		return $cloud->remove($tag, $tagger_id);
	}

	/**
	 * Remove all tags on an object
	 *
	 * @param      integer $object_id Object ID
	 * @return     boolean True on success, false if errors
	 */
	public function remove_all_tags($object_id)
	{
		$cloud = new PubCloud($object_id, $this->_tbl);
		return $cloud->removeAll();
	}

	/**
	 * Normalize a tag
	 * Strips spaces, punctuation, makes lowercase, and allows only alpha-numeric chars
	 *
	 * @param      string $tag Raw tag
	 * @return     string Normalized tag
	 */
	public function normalize_tag($tag)
	{
		$t = \Components\Tags\Models\Tag::blank();
		return $t->normalize($tag);
	}

	/**
	 * Get the ID of a normalized tag
	 *
	 * @param      string $tag Normalized tag
	 * @return     mixed False if errors, integer on success
	 */
	public function get_tag_id($tag)
	{
		if (!isset($tag))
		{
			$this->setError('get_tag_id argument missing');
			return false;
		}

		$t = \Components\Tags\Models\Tag::oneByTag($tag);
		return $t->get('id');
	}

	/**
	 * Get the ID of a raw tag
	 *
	 * @param      string $tag Raw tag
	 * @return     mixed False if errors, integer on success
	 */
	public function get_raw_tag_id($tag)
	{
		if (!isset($tag))
		{
			$this->setError('get_raw_tag_id argument missing');
			return false;
		}
		return $this->get_tag_id($tag);
	}

	/**
	 * Get a count of tags
	 *
	 * @param      integer $admin     Show admin tags?
	 * @return     integer
	 */
	public function count_tags($admin=0)
	{
		$t = \Components\Tags\Models\Tag::all();
		if (!$admin)
		{
			$t->whereEquals('admin', 0);
		}
		return $t->total();
	}

	/**
	 * Get a tag cloud for an object
	 *
	 * @param      integer $showsizes Show tag size based on use?
	 * @param      integer $admin     Show admin tags?
	 * @param      integer $objectid  Object ID
	 * @return     mixed Return description (if any) ...
	 */
	public function get_tag_cloud($showsizes=0, $admin=0, $objectid=null)
	{
		$cloud = new PubCloud($objectid, $this->_tbl);
		return $cloud->render(); //TODO: Check with author as to why admin is aasumed not needed
		//return $cloud->render('html', array('admin' => $admin));
	}

	/**
	 * Return a list of tags for an object as a comma-separated string
	 *
	 * @param      integer $oid       Object ID
	 * @param	   array   $filters	  Array of filters
	 * @return     string
	 */
	public function get_tag_string($oid, $filters = array())
	{
		$cloud = new PubCloud($oid, $this->_tbl);
		return $cloud->render('string', $filters);
	}

	/**
	 * Return a list of tags for an object as a comma-separated string
	 *
	 * @param      integer $oid       Object ID
	 * @param	   integer $mtype	  Publication master type
	 * @return     array
	 */
	public function get_focus_areas($oid, $mtype)
	{
		$fas = FocusArea::fromObject($mtype);
		$cloud = new PubCloud($oid);
		$selected = $cloud->render('list', array('type' => 'focusareas'));
		return array('fas' => $fas, 'selected' => $selected);
	}

	/**
	 * Turn a comma-separated string of tags into an array of normalized tags
	 *
	 * @param      string  $tag_string Comma-separated string of tags
	 * @param      integer $keep       Use normalized tag as array key
	 * @return     array
	 */
	public function _parse_tags($tag_string, $keep=0)
	{
		$newwords = array();

		if (is_string($tag_string))
		{
			// If the tag string is empty, return the empty set.
			if ($tag_string == '')
			{
				return $newwords;
			}

			// Perform tag parsing
			$tag_string = trim($tag_string);
			$raw_tags = explode(',', $tag_string);
		}
		else if (is_array($tag_string))
		{
			$raw_tags = $tag_string;
		}
		else
		{
			throw new \InvalidArgumentException(Lang::txt('Tag lsit must be an array or string. Type of "%s" passed.', gettype($tag_string)));
		}

		foreach ($raw_tags as $raw_tag)
		{
			$raw_tag = trim($raw_tag);
			$nrm_tag = $this->normalize_tag($raw_tag);
			if ($keep != 0)
			{
				$newwords[$nrm_tag] = $raw_tag;
			}
			else
			{
				$newwords[] = $nrm_tag;
			}
		}
		return $newwords;
	}

	/**
	 * Push group alias into array for easier searching
	 *
	 * @param      array $groups User's gorups
	 * @return     array
	 */
	public function getUsersGroups($groups)
	{
		$arr = array();
		if (!empty($groups))
		{
			foreach ($groups as $group)
			{
				if ($group->regconfirmed)
				{
					$arr[] = $group->cn;
				}
			}
		}
		return $arr;
	}

	/**
	 * Get all publications associated with a tag
	 *
	 * @param      string  $tag      Tag to find data for
	 * @param      integer $type     Publication master type
	 * @param      string  $sortby   Sort data by
	 * @param      string  $tag2     Secondary tag
	 * @param      array   $filterby Extra, optional filters
	 * @return     array
	 */
	public function get_objects_on_tag($tag='', $type=0, $sortby='title', $tag2='', $filterby=array())
	{
		$now  = Date::toSql();

		$alias1 = is_numeric($tag) ? 'id' : 'tag';
		$alias2 = is_numeric($tag2) ? 'id' : 'tag';

		if ($tag || $tag2)
		{
			$query  = "SELECT V.id, V.title, COUNT(DISTINCT TA.tag) AS uniques ";
			$query .= "FROM #__publications as C ";
			$query .= "JOIN #__publication_versions as V ON C.id=V.publication_id ";
			$query .= "LEFT JOIN #__publication_audience AS TTA ON V.id=TTA.publication_version_id ";
			$query .= "INNER JOIN $this->_obj_tbl AS RTA ON RTA.objectid=V.id ";
			$query .= "INNER JOIN #__tags AS TA ON RTA.tagid=TA.id ";
		}
		else
		{
			$query  = "SELECT V.id, V.title ";
			$query .= "FROM #__publication_versions as V ";
			$query .= "LEFT JOIN #__publication_audience AS TTA ON V.id=TTA.rid ";
		}

		$query .= "WHERE V.state = 1 AND V.main = 1 ";
		if ($type) {
			$query .= "AND C.master_type=$type ";
		}
		$query .= "AND (V.published_up IS NULL OR V.published_up = '0000-00-00 00:00:00' OR V.published_up <= '" . $now . "') ";
		$query .= "AND (V.published_down IS NULL OR V.published_down = '0000-00-00 00:00:00' OR V.published_down >= '" . $now . "') AND ";

		$query .= (!User::isGuest()) ? "(V.access=0 OR V.access=1) " : "(V.access=0) ";
		$query .= "AND RTA.tbl='$this->_tbl' ";
		if ($tag || $tag2)
		{
			if ($tag && !$tag2)
			{
				$query .= "AND (TA.$alias1=$tag) ";
				$query .= "GROUP BY V.id HAVING uniques=1 ";
			}
			else if ($tag2 && !$tag)
			{
				$query .= "AND (TA.$alias2=$tag2) ";
				$query .= "GROUP BY V.id HAVING uniques=1 ";
			}
			else if ($tag && $tag2)
			{
				$query .= "AND (TA.$alias1=$tag) ";
				$query .= "AND (TA.$alias2=$tag2) ";
				$query .= "GROUP BY V.id HAVING uniques=2 ";
			}
		}
		switch ($sortby)
		{
			case 'ranking':
				$sort = "V.ranking DESC";
			break;
			case 'date':
				$sort = "V.published_up DESC";
			break;
			default:
			case 'title':
				$sort = "V.title ASC";
			break;
		}
		$query .= "ORDER BY " . $sort . ", V.published_up";

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Check if a tag is being used
	 *
	 * @param      string  $tag   Tag
	 * @param      integer $id    Publication ID
	 * @param      string  $alias Publication alias
	 * @return     mixed Return description (if any) ...
	 */
	public function checkTagUsage($tag, $id=0, $alias='')
	{
		if (!$id && !$alias)
		{
			return false;
		}
		if ($id)
		{
			$query = "SELECT COUNT(*)
						FROM $this->_obj_tbl AS ta, $this->_tag_tbl AS t
						WHERE ta.tagid=t.id
						AND t.tag='" . $tag . "'
						AND ta.tbl='publications'
						AND ta.objectid=" . $id;
		}
		if (!$id && $alias)
		{
			$query = "SELECT COUNT(*)
						FROM $this->_obj_tbl AS ta, $this->_tag_tbl AS t, #__publications AS r
						WHERE ta.tagid=t.id
						AND t.tag='" . $tag . "'
						AND ta.tbl='publications'
						AND ta.objectid=r.id
						AND r.alias='" . $alias . "'";
		}

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get a singular field, such as ID, for all items with a specific tag
	 *
	 * @param      string $tag  Tag to get data for
	 * @param      string $rtrn Field to return
	 * @return     array
	 */
	public function getTagUsage($tag, $rtrn='id')
	{
		if (!$tag)
		{
			return array();
		}

		$query = "SELECT r.$rtrn
					FROM $this->_obj_tbl AS ta, $this->_tag_tbl AS t, #__publications AS r
					WHERE ta.tagid=t.id
					AND t.tag='".$tag."'
					AND ta.tbl='publications'
					AND ta.objectid=r.id";

		$this->_db->setQuery($query);
		return $this->_db->loadColumn();
	}

	/**
	 * Get tag count
	 *
	 * @param      integer $id
	 *
	 * @return     string
	 */
	public function countTags($id)
	{
		$sql = "SELECT COUNT(*) FROM $this->_tag_tbl AS t,
			$this->_obj_tbl AS rt WHERE rt.objectid=$id
			AND rt.tbl='$this->_tbl' AND rt.tagid=t.id";

		$this->_db->setQuery( $sql );
		return $this->_db->loadResult();
	}

	/**
	 * Get picked tags
	 *
	 * @param      array $tags
	 *
	 * @return     object
	 */
	public function getPickedTags($tags = array())
	{
		if (empty($tags))
		{
			return $tags;
		}

		$picked = '';
		$k = 1;
		foreach ($tags as $att)
		{
			$picked  .= $att;
			$picked .= $k == count($tags) ? '' : ',';
			$k++;
		}

		$sql  = "SELECT DISTINCT t.* FROM $this->_tag_tbl AS t ";
		$sql .= "WHERE  t.id IN (".$picked.")";
		$sql .= " ORDER BY t.raw_tag";
		$this->_db->setQuery( $sql );
		return $this->_db->loadObjectList();
	}
	
	/**
	 * Get FOS (Field of Science and Technology) tag according to department tag id
	 *
	 * @param      int $vid	publication version id
	 *
	 * @return     object or false
	 */
	public function getFOSTag($vid)
	{
		// Get department tag id
		$deptQuery = "SELECT jt.id FROM $this->_tag_tbl AS jt LEFT JOIN $this->_obj_tbl AS jto ON jt.id = jto.tagid 
		WHERE jt.admin = 1 AND jto.tbl='publications' AND jt.raw_tag LIKE '%(Dept)%' AND jto.objectid = $vid";
		$this->_db->setQuery($deptQuery);
		$deptTagID = $this->_db->loadResult();
		
		if (!$deptTagID)
		{
			return false;
		}
		
		// Get FOS tag by department tag id
		$fosQuery = "SELECT jt.* FROM $this->_tag_tbl AS jt LEFT JOIN $this->_obj_tbl AS jto ON jt.id = jto.tagid 
		WHERE jto.tbl = 'tags' AND jto.label = 'parent' AND jto.objectid = $deptTagID";
		$this->_db->setQuery($fosQuery);
		return $this->_db->loadObject();
	}

	/**
	 * Get suggested tags
	 *
	 * @param      string 	$pubtitle
	 * @param      string 	$typealias
	 * @param      array 	$attached_tags
	 * @param      integer 	$limit
	 * @param      integer	$tcount
	 *
	 * @return     object
	 */
	public function getSuggestedTags($pubtitle = '', $typealias = '', $attached_tags = array(), $limit = 10, $tcount = 2)
	{
		$picked = '';
		$k = 1;
		if (!empty($attached_tags))
		{
			foreach ($attached_tags as $att)
			{
				$picked  .= $att->id;
				$picked .= $k == count($attached_tags) ? '' : ',';
				$k++;
			}
		}
		$keywords = array();
		if ($pubtitle)
		{
			$words = explode(' ', $pubtitle );
			foreach ($words as $word)
			{
				if ($word != '')
				{
					$keywords[] = trim($word);
				}
			}
		}

		$sql  = "SELECT DISTINCT t.*, tj.tagid, tj.objectid, COUNT(tj.tagid) AS tcount  ";
		$sql .= "FROM $this->_tag_tbl AS t ";
		$sql .= "JOIN $this->_obj_tbl AS tj ON t.id=tj.tagid ";
		$sql .= "WHERE ";
		$sql .= $picked ? " t.id NOT IN (".$this->_db->quote($picked).")" : "1=1";
		$sql .= " GROUP BY tagid ";
		$sql .= " HAVING tcount > ".$this->_db->quote($tcount);

		if (!empty($keywords))
		{
			$sql .= ' OR (';
			$w = 1;
			foreach ($keywords as $key)
			{
				$sql .= 't.raw_tag LIKE "%'.$this->_db->escape($key).'%"';
				$sql .= $w == count($keywords) ? '' : ' OR ';
				$w++;
			}
			$sql .= ')';
		}
		$sql .= " AND t.admin=0 ";
		$sql .= " ORDER BY  RAND()";
		$sql .= " LIMIT ".$limit;
		$this->_db->setQuery( $sql );
		return $this->_db->loadObjectList();
	}

	/**
	 * Get a tag cloud of top publication tags
	 *
	 * @param      integer $limit
	 * @param      string $tagstring
	 * @return     mixed Return description (if any) ...
	 */
	public function getTopTagCloud($limit, $tagstring='')
	{
		$tags = $this->getTopTags($limit);
		return $this->buildTopCloud($tags, 'alpha', 0, $tagstring);
	}

	/**
	 * Get top publication tags
	 *
	 * @param      integer $limit
	 * @return     mixed Return description (if any) ...
	 */
	public function getTopTags($limit)
	{
		$tj = \Components\Tags\Models\Objct::blank();

		$sql  = "SELECT t.tag, t.raw_tag, t.admin, tj.tagid, tj.objectid, COUNT(tj.tagid) AS tcount ";
		$sql .= "FROM #__tags AS t  ";
		$sql .= "JOIN " . $tj->getTableName() . " AS tj ON t.id=tj.tagid ";
		$sql .= "LEFT JOIN #__publication_versions AS V ON V.id=tj.objectid AND tj.tbl='publications' ";
		$sql .= "WHERE t.id=tj.tagid AND t.admin=0 ";
		$sql .= "AND tj.tbl=" . $this->_db->quote($this->_tbl) . " ";
		$sql .= "AND V.state=1 AND V.main=1 AND V.access!=4 ";
		$sql .= "AND V.published_up < " . $this->_db->quote(Date::toSql()) . " ";
		$sql .= "AND (V.published_down IS NULL OR V.published_down = '0000-00-00 00:00:00' OR V.published_down > " . $this->_db->quote(Date::toSql()) . ") ";
		$sql .= "GROUP BY tj.tagid ";
		$sql .= "ORDER BY tcount DESC ";
		$sql .= "LIMIT $limit";

		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	/**
	 * Turn a string of tags to an array
	 *
	 * @param      string $tag Tag string
	 * @return     mixed
	 */
	public function parseTopTags($tag, $remove='')
	{
		if (is_array($tag))
		{
			$bunch = $tag;
		}
		else
		{
			$bunch = $this->_parse_tags($tag);
		}

		$tags = array();
		if ($remove)
		{
			foreach ($bunch as $t)
			{
				if ($remove == $t)
				{
					continue;
				}
				$tags[] = $t;
			}
		}
		else
		{
			return $bunch;
		}

		return $tags;
	}

	/**
	 * Build a tag cloud
	 *
	 * @param      array   $tags      List of tags
	 * @param      string  $sort      How to sort tags?
	 * @param      integer $showsizes Show tag size based on use?
	 * @return     string HTML
	 */
	public function buildTopCloud($tags, $sort='alpha', $showsizes=0, $tagstring='')
	{
		$html = '';

		if ($tags && count($tags) > 0)
		{
			$lst = array();
			if (is_string($tagstring))
			{
				$lst = $this->_parse_tags($tagstring);
			}
			else
			{
				$lst = $tagstring;
			}

			$min_font_size = 1;
			$max_font_size = 1.8;

			if ($showsizes)
			{
				$retarr = array();
				foreach ($tags as $tag)
				{
					$retarr[$tag->raw_tag] = $tag->count;
				}
				ksort($retarr);

				$max_qty = max(array_values($retarr));  // Get the max qty of tagged objects in the set
				$min_qty = min(array_values($retarr));  // Get the min qty of tagged objects in the set

				// For ever additional tagged object from min to max, we add $step to the font size.
				$spread = $max_qty - $min_qty;
				if (0 == $spread)
				{ // Divide by zero
					$spread = 1;
				}
				$step = ($max_font_size - $min_font_size)/($spread);
			}

			// build HTML
			$html .= '<ol class="tags">' . "\n";
			$tll = array();
			foreach ($tags as $tag)
			{
				$class = '';
				switch ($tag->admin)
				{
					/*case 0:
						$class = ' class="restricted"';
					break;*/
					case 1:
						$class = ' class="admin"';
					break;
				}

				$lsst = $lst;

				if ($tagstring)
				{
					if (!in_array($tag->tag, $lst))
					{
						$lsst[] = $tag->tag;
					}
				}
				else
				{
					$lsst = array($tag->tag);
				}

				$tag->raw_tag = stripslashes($tag->raw_tag);
				$tag->raw_tag = str_replace('&amp;', '&', $tag->raw_tag);
				$tag->raw_tag = str_replace('&', '&amp;', $tag->raw_tag);
				if ($showsizes == 1)
				{
					$size = $min_font_size + ($tag->count - $min_qty) * $step;
					$tll[$tag->tag] = "\t".'<li' . $class . '><span data-size="' . round($size, 1) . 'em"><a class="tag' . ($tag->admin ? ' admin' : '') . '" href="' . Route::url('index.php?option=com_publications&task=browse&tag=' . implode(',', $lsst)) . '">' . stripslashes($tag->raw_tag) . '</a></li>' . "\n"; //' <span>' . $tag->count . '</span></a></span></li>' . "\n";
				}
				else
				{
					$tll[$tag->tag] = "\t".'<li' . $class . '><a class="tag' . ($tag->admin ? ' admin' : '') . '" href="' . urldecode(Route::url('index.php?option=com_publications&task=browse&tag=' . implode(',', $lsst))) . '">' . stripslashes($tag->raw_tag) . '</a></li>' . "\n"; //' <span>' . $tag->count . '</span></a></li>' . "\n";
				}
			}
			if ($sort == 'alpha')
			{
				ksort($tll);
				$html .= implode('', $tll);
			}
			$html .= '</ol>' . "\n";
		}

		return $html;
	}
}
