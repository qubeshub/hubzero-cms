<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Courses\Tables;

use Hubzero\Database\Table;
use User;
use Date;
use Lang;

/**
 * Course section table class
 */
class Section extends Table
{
	/**
	 * Contructor method for Table class
	 *
	 * @param   object  &$db  database object
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__courses_offering_sections', 'id', $db);
	}

	/**
	 * Returns a reference to a section object
	 *
	 * @param   string  $pagename  The page to load
	 * @param   string  $scope     The page scope
	 * @return  object
	 */
	public static function getInstance($type, $prefix = 'Table', $config = array())
	{
		static $instances;

		$alias = $type;
		$offering_id = $prefix;

		if (!isset($instances))
		{
			$instances = array();
		}

		if (!isset($instances[$alias . '_' . $offering_id]))
		{
			$db = \App::get('db');
			$inst = new self($db);
			$inst->load($alias, $offering_id);

			$instances[$alias . '_' . $offering_id] = $inst;
		}

		return $instances[$alias . '_' . $offering_id];
	}

	/**
	 * Load a record and bind to $this
	 *
	 * @param   string   $oid  Record alias
	 * @return  boolean  True on success
	 */
	public function load($oid=null, $offering_id=null)
	{
		if ($oid === null)
		{
			return false;
		}
		if (is_numeric($oid))
		{
			return parent::load($oid);
		}

		if ($oid == '!!default!!')
		{
			$fields = array(
				'is_default'  => 1,
				'offering_id' => intval($offering_id)
			);
		}
		else
		{
			$fields = array(
				'alias'       => trim($oid),
				'offering_id' => intval($offering_id)
			);
		}

		return parent::load($fields);
	}

	/**
	 * Override the check function to do a little input cleanup
	 *
	 * @return  boolean
	 */
	public function check()
	{
		$this->offering_id = intval($this->offering_id);
		if (!$this->offering_id)
		{
			$this->setError(Lang::txt('Please provide an offering ID.'));
			return false;
		}

		$this->title = trim($this->title);
		if (!$this->title)
		{
			$this->setError(Lang::txt('Please provide a title.'));
			return false;
		}

		if (!$this->alias)
		{
			$this->alias = strtolower($this->title);
		}
		$this->alias = preg_replace("/[^a-zA-Z0-9\-_]/", '', $this->alias);

		$this->_db->setQuery("SELECT id FROM `#__courses_offering_sections` WHERE `offering_id`=" . $this->_db->quote($this->offering_id) . " AND `alias`=" . $this->_db->quote($this->alias));
		$id = $this->_db->loadResult();
		if ($id && $id != $this->id)
		{
			$this->setError(Lang::txt('A section with the alias "%s" already exists for the specified offering.', $this->alias));
			return false;
		}

		if (!$this->id)
		{
			$this->created    = Date::toSql();
			$this->created_by = User::get('id');
		}

		return true;
	}

	/**
	 * Build query method
	 *
	 * @param   array   $filters
	 * @return  string  Database query
	 */
	private function _buildQuery($filters=array())
	{
		$query  = " FROM $this->_tbl AS os";
		$query .= " INNER JOIN #__courses_offerings AS o ON o.id=os.offering_id";

		$where = array();

		if (isset($filters['offering_id']) && $filters['offering_id'])
		{
			$where[] = "os.offering_id=" . $this->_db->quote(intval($filters['offering_id']));
		}

		if (isset($filters['state']) && $filters['state'] >= 0)
		{
			$where[] = "os.state=" . $this->_db->quote(intval($filters['state']));
		}

		if (isset($filters['is_default']) && $filters['is_default'] >= 0)
		{
			$where[] = "os.is_default=" . $this->_db->quote(intval($filters['is_default']));
		}

		if (isset($filters['enrollment']))
		{
			$filters['enrollment'] = array_map('intval', $filters['enrollment']);
			if (is_array($filters['enrollment']))
			{
				$where[] = "os.enrollment IN (" . implode(',', $filters['enrollment']) . ")";
			}
			else
			{
				$where[] = "os.enrollment=" . $this->_db->quote(intval($filters['enrollment']));
			}
		}

		if (isset($filters['search']) && $filters['search'])
		{
			$where[] = "(LOWER(os.alias) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . "
					  OR LOWER(os.title) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . ")";
		}

		if (isset($filters['available']) && $filters['available'])
		{
			$now = Date::toSql();

			$where[] = "(os.publish_up IS NULL OR os.publish_up = '0000-00-00 00:00:00' OR os.publish_up <= " . $this->_db->quote($now) . ")";
			$where[] = "(os.publish_down IS NULL OR os.publish_down = '0000-00-00 00:00:00' OR os.publish_down >= " . $this->_db->quote($now) . ")";
		}

		if (isset($filters['started']))
		{
			$now = Date::toSql();

			if ($filters['started'] === true)
			{
				$where[] = "(os.start_date IS NULL OR os.start_date = '0000-00-00 00:00:00' OR os.start_date <= " . $this->_db->quote($now) . ")";
			}
			else if ($filters['started'] === false)
			{
				$where[] = "(os.start_date IS NOT NULL AND os.start_date != '0000-00-00 00:00:00' AND os.start_date > " . $this->_db->quote($now) . ")";
			}
		}

		if (isset($filters['ended']))
		{
			$now = Date::toSql();

			if ($filters['ended'] === true)
			{
				$where[] = "(os.end_date IS NOT NULL AND os.end_date != '0000-00-00 00:00:00' AND os.end_date < " . $this->_db->quote($now) . ")";
			}
			else if ($filters['ended'] === false)
			{
				$where[] = "(os.end_date IS NULL OR os.end_date = '0000-00-00 00:00:00' OR os.end_date >= " . $this->_db->quote($now) . ")";
			}
		}

		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}

		return $query;
	}

	/**
	 * Get a count of entries
	 *
	 * @param   array    $filters
	 * @return  integer
	 */
	public function count($filters=array())
	{
		$query  = "SELECT COUNT(os.id)";
		$query .= $this->_buildquery($filters);

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get a list of entries
	 *
	 * @param   array  $filters
	 * @return  array
	 */
	public function find($filters=array())
	{
		$query  = "SELECT os.*";
		$query .= $this->_buildquery($filters);

		if (!isset($filters['sort']) || $filters['sort'] == '')
		{
			$filters['sort'] = 'os.title';
		}
		if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC')))
		{
			$filters['sort_Dir'] = 'ASC';
		}

		$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];

		if (isset($filters['limit']) && $filters['limit'] != 0)
		{
			if (!isset($filters['start']))
			{
				$filters['start'] = 0;
			}
			$query .= " LIMIT " . (int) $filters['start'] . "," . (int) $filters['limit'];
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
