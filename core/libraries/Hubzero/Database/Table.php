<?php
/**
 * @package    framework
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Hubzero\Database;

use Hubzero\Base\Obj;
use RuntimeException;
use Exception;
use Lang;
use App;
use Log;

/**
 * Abstract Table class
 *
 * Parent class to all tables.
 *
 * @deprecated  This is a temporary bandage for the removal
 *              Joomla-based code and should be expected to
 *              be removed in a future release.
 */
abstract class Table extends Obj
{
	/**
	 * Name of the database table to model.
	 *
	 * @var    string
	 * @since  2.1.12
	 */
	protected $_tbl = '';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var    string
	 * @since  2.1.12
	 */
	protected $_tbl_key = '';

	/**
	 * Database connector object.
	 *
	 * @var    object
	 * @since  2.1.12
	 */
	protected $_db;

	/**
	 * Should rows be tracked as ACL assets?
	 *
	 * @var    boolean
	 * @since  2.1.12
	 */
	protected $_trackAssets = false;

	/**
	 * The rules associated with this record.
	 *
	 * @var    object  A Access Rules object.
	 * @since  2.1.12
	 */
	protected $_rules;

	/**
	 * Indicator that the tables have been locked.
	 *
	 * @var    boolean
	 * @since  2.1.12
	 */
	protected $_locked = false;

	/**
	 * Cached table field descriptions
	 *
	 * @var array
	 */
	private $fieldCache = null;

	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param  string  $table  Name of the table to model.
	 * @param  string  $key    Name of the primary key field in the table.
	 * @param  object  &$db    Database connector object.
	 * @since  2.1.12
	 */
	public function __construct($table, $key, $db)
	{
		// Set internal variables.
		$this->_tbl = $table;
		$this->_tbl_key = $key;
		$this->_db = $db;

		// Initialise the table properties.
		if ($fields = $this->getFields())
		{
			foreach ($fields as $name => $v)
			{
				// Add the field if it is not already present.
				if (!property_exists($this, $name))
				{
					$this->$name = null;
				}
			}
		}

		// If we are tracking assets, make sure an access field exists and initially set the default.
		if (property_exists($this, 'asset_id'))
		{
			$this->_trackAssets = true;
		}

		// If the access property exists, set the default.
		if (property_exists($this, 'access'))
		{
			$this->access = (int) \Config::get('access');
		}
	}

	/**
	 * Get the columns from database table.
	 *
	 * @return  mixed   An array of the field names, or false if an error occurs.
	 * @since   2.1.12
	 */
	public function getFields()
	{
		if ($this->fieldCache === null)
		{
			// Lookup the fields for this table only once.
			$name = $this->_tbl;
			$fields = $this->_db->getTableColumns($name, false);

			if (empty($fields))
			{
				$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_COLUMNS_NOT_FOUND'));
				$this->setError($e);
				return false;
			}
			$this->fieldCache = $fields;
		}

		return $this->fieldCache;
	}

	/**
	 * Static method to get an instance of a Table class if it can be found in
	 * the table include paths.  To add include paths for searching for Table
	 * classes @see self::addIncludePath().
	 *
	 * @param   string  $type    The type (name) of the Table class to get an instance of.
	 * @param   string  $prefix  An optional prefix for the table class name.
	 * @param   array   $config  An optional array of configuration values for the Table object.
	 * @return  mixed   A Table object if found or boolean false if one could not be found.
	 * @since   2.1.12
	 */
	public static function getInstance($type, $prefix = 'Table', $config = array())
	{
		// Sanitize and prepare the table class name.
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix . ucfirst($type);

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($tableClass))
		{
			// Search for the class file in the Table include paths.
			$paths = self::addIncludePath();
			$pathIndex = 0;
			while (!class_exists($tableClass) && $pathIndex < count($paths))
			{
				if ($tryThis = \Filesystem::find($paths[$pathIndex++], strtolower($type) . '.php'))
				{
					// Import the class file.
					include_once $tryThis;
				}
			}
			if (!class_exists($tableClass))
			{
				// If we were unable to find the class file in the Table include paths, raise a warning and return false.
				return false;
			}
		}

		// If a database object was passed in the configuration array use it, otherwise get the global one from JFactory.
		$db = isset($config['dbo']) ? $config['dbo'] : App::get('db');

		// Instantiate a new table class and return it.
		return new $tableClass($db);
	}

	/**
	 * Add a filesystem path where Table should search for table class files.
	 * You may either pass a string or an array of paths.
	 *
	 * @param   mixed  $path  A filesystem path or array of filesystem paths to add.
	 * @return  array  An array of filesystem paths to find Table classes in.
	 * @since   2.1.12
	 */
	public static function addIncludePath($path = null)
	{
		// Declare the internal paths as a static variable.
		static $_paths;

		// If the internal paths have not been initialised, do so with the base table path.
		if (!isset($_paths))
		{
			$_paths = array(__DIR__ . '/table');
		}

		// Convert the passed path(s) to add to an array.
		settype($path, 'array');

		// If we have new paths to add, do so.
		if (!empty($path) && !in_array($path, $_paths))
		{
			// Check and add each individual new path.
			foreach ($path as $dir)
			{
				// Sanitize path.
				$dir = trim($dir);

				// Add to the front of the list so that custom paths are searched first.
				array_unshift($_paths, $dir);
			}
		}

		return $_paths;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 * @since   2.1.12
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return $this->_tbl . '.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.  In
	 * tracking the assets a title is kept for each asset so that there is some
	 * context available in a unified access manager.  Usually this would just
	 * return $this->title or $this->name or whatever is being used for the
	 * primary name of the row. If this method is not overridden, the asset name is used.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 * @since   2.1.12
	 */
	protected function _getAssetTitle()
	{
		return $this->_getAssetName();
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID,
	 * which will default to 1 if none exists.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   object   $table  A Table object for the asset parent.
	 * @param   integer  $id     Id to look up
	 * @return  integer
	 * @since   2.1.12
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		// For simple cases, parent to the asset root.
		$rootId = \Hubzero\Access\Asset::getRootId();
		if (!empty($rootId))
		{
			return $rootId;
		}

		return 1;
	}

	/**
	 * Method to get the database table name for the class.
	 *
	 * @return  string  The name of the database table being modeled.
	 */
	public function getTableName()
	{
		return $this->_tbl;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @return  string  The name of the primary key for the table.
	 * @since   2.1.12
	 */
	public function getKeyName()
	{
		return $this->_tbl_key;
	}

	/**
	 * Method to get the Database connector object.
	 *
	 * @return  object  The internal database connector object.
	 * @since   2.1.12
	 */
	public function getDbo()
	{
		return $this->_db;
	}

	/**
	 * Method to set the Database connector object.
	 *
	 * @param   object   &$db  A Database connector object to be used by the table object.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function setDBO(&$db)
	{
		// Make sure the new database object is a Database.
		if (!($db instanceof \Hubzero\Database\Driver))
		{
			return false;
		}

		$this->_db = &$db;

		return true;
	}

	/**
	 * Method to set rules for the record.
	 *
	 * @param   mixed  $input  A Hubzero\Access\Rules object, JSON string, or array.
	 * @return  void
	 * @since   2.1.12
	 */
	public function setRules($input)
	{
		if ($input instanceof \Hubzero\Access\Rules)
		{
			$this->_rules = $input;
		}
		else
		{
			$this->_rules = new \Hubzero\Access\Rules($input);
		}
	}

	/**
	 * Method to get the rules for the record.
	 *
	 * @return  object
	 * @since   2.1.12
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return  void
	 * @since   2.1.12
	 */
	public function reset()
	{
		// Get the default values for the class from the table.
		foreach ($this->getFields() as $k => $v)
		{
			// If the property is not the primary key or private, reset it.
			if ($k != $this->_tbl_key && (strpos($k, '_') !== 0))
			{
				$this->$k = $v->Default;
			}
		}
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed    $src     An associative array or object to bind to the Table instance.
	 * @param   mixed    $ignore  An optional array or space separated list of properties to ignore while binding.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function bind($src, $ignore = array())
	{
		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_BIND_FAILED_INVALID_SOURCE_ARGUMENT', get_class($this)));
			$this->setError($e);
			return false;
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		// Bind the source value, excluding the ignored fields.
		foreach ($this->getProperties() as $k => $v)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				if (isset($src[$k]))
				{
					$this->$k = $src[$k];
				}
			}
		}

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the Table instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
	 * @since   2.1.12
	 */
	public function load($keys = null, $reset = true)
	{
		if (empty($keys))
		{
			// If empty, use the value of the current key
			$keyName = $this->_tbl_key;
			$keyValue = $this->$keyName;

			// If empty primary key there's is no need to load anything
			if (empty($keyValue))
			{
				return true;
			}

			$keys = array($keyName => $keyValue);
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keys = array($this->_tbl_key => $keys);
		}

		if ($reset)
		{
			$this->reset();
		}

		// Initialise the query.
		$query = $this->_db->getQuery();
		$query->select('*');
		$query->from($this->_tbl);
		$fields = array_keys($this->getProperties());

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.
			if (!in_array($field, $fields))
			{
				$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_CLASS_IS_MISSING_FIELD', get_class($this), $field));
				$this->setError($e);
				return false;
			}
			// Add the search tuple to the query.
			$query->whereEquals($field, $value);
		}

		$this->_db->setQuery($query->toString());

		try
		{
			$row = $this->_db->loadAssoc();
		}
		catch (Exception $e)
		{
			$je = new Exception($e->getMessage());
			$this->setError($je);
			return false;
		}

		// Check that we have a result.
		if (empty($row))
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_EMPTY_ROW_RETURNED'));
			$this->setError($e);
			return false;
		}

		// Bind the object with the row and return.
		return $this->bind($row);
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 * @since   2.1.12
	 */
	public function check()
	{
		return true;
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function store($updateNulls = false)
	{
		$currentAssetId = null;

		// Initialise variables.
		$k = $this->_tbl_key;
		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		// The asset id field is managed privately by this class.
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($this->$k)
		{
			$stored = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else
		{
			$stored = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
			\Event::trigger($this->getTableName() . '_new', ['table' => $this]);
		}

		// If the store failed return false.
		if (!$stored)
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		\Event::trigger('system.onContentSave', array($this->getTableName(), $this));

		// If the table is not set to track assets return true.
		if (!$this->_trackAssets)
		{
			return true;
		}

		if ($this->_locked)
		{
			$this->_unlock();
		}

		//
		// Asset Tracking
		//

		$parentId = $this->_getAssetParentId();
		$name = $this->_getAssetName();
		$title = $this->_getAssetTitle();

		$asset = \Hubzero\Access\Asset::oneByName($name);

		// Re-inject the asset id.
		$this->asset_id = $asset->get('id');

		// Check for an error.
		if ($error = $asset->getError())
		{
			$this->setError($error);
			return false;
		}

		// Specify how a new or moved node asset is inserted into the tree.
		if (empty($this->asset_id) || $asset->get('parent_id') != $parentId)
		{
			$asset->setLocation($parentId, 'last-child');
		}

		// Prepare the asset to be stored.
		$asset->set('parent_id', $parentId);
		$asset->set('name', $name);
		$asset->set('title', $title);

		if ($this->_rules instanceof \Hubzero\Access\Rules)
		{
			$asset->set('rules', (string) $this->_rules);
		}

		if (!$asset->save())
		{
			$this->setError($asset->getError());
			return false;
		}

		// Create an asset_id or heal one that is corrupted.
		if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
		{
			// Update the asset_id field in this table.
			$this->asset_id = (int) $asset->get('id');

			$query = $this->_db->getQuery();
			$query->update($this->_tbl);
			$query->set(array(
				'asset_id' => (int) $this->asset_id
			));
			$query->whereEquals($k, (int) $this->$k);
			$this->_db->setQuery($query->toString());

			if (!$this->_db->execute())
			{
				$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_STORE_FAILED_UPDATE_ASSET_ID', $this->_db->getErrorMsg()));
				$this->setError($e);
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to provide a shortcut to binding, checking and storing a Table
	 * instance to the database table.  The method will check a row in once the
	 * data has been stored and if an ordering filter is present will attempt to
	 * reorder the table rows based on the filter.  The ordering filter is an instance
	 * property name.  The rows that will be reordered are those whose value matches
	 * the Table instance for the property specified.
	 *
	 * @param   mixed    $src             An associative array or object to bind to the Table instance.
	 * @param   string   $orderingFilter  Filter for the order updating
	 * @param   mixed    $ignore          An optional array or space separated list of properties to ignore while binding.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function save($src, $orderingFilter = '', $ignore = '')
	{
		// Attempt to bind the source to the instance.
		if (!$this->bind($src, $ignore))
		{
			return false;
		}

		// Run any sanity checks on the instance and verify that it is ready for storage.
		if (!$this->check())
		{
			return false;
		}

		// Attempt to store the properties to the database table.
		if (!$this->store())
		{
			return false;
		}

		// Attempt to check the row in, just in case it was checked out.
		if (!$this->checkin())
		{
			return false;
		}

		// If an ordering filter is set, attempt reorder the rows in the table based on the filter and value.
		if ($orderingFilter)
		{
			$filterValue = $this->$orderingFilter;
			$this->reorder($orderingFilter ? $this->_db->quoteName($orderingFilter) . ' = ' . $this->_db->Quote($filterValue) : '');
		}

		// Set the error to empty and return true.
		$this->setError('');

		return true;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed    $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function delete($pk = null)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_NULL_PRIMARY_KEY'));
			$this->setError($e);
			return false;
		}

		// If tracking assets, remove the asset first.
		if ($this->_trackAssets)
		{
			// Get and the asset name.
			$this->$k = $pk;
			$name = $this->_getAssetName();
			$asset = self::getInstance('Asset');

			if ($asset->loadByName($name))
			{
				if (!$asset->delete())
				{
					$this->setError($asset->getError());
					return false;
				}
			}
			else
			{
				$this->setError($asset->getError());
				// [!] Hubzero - Record doesn't exist. Since we're
				//     deleting entries, it shouldn't matter.
				//return false;
			}
		}

		// Delete the row by primary key.
		$query = $this->_db->getQuery();
		$query->delete($this->_tbl);
		$query->whereEquals($this->_tbl_key, $pk);
		$this->_db->setQuery($query->toString());

		// Check for a database error.
		if (!$this->_db->execute())
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		return true;
	}

	/**
	 * Method to check a row out if the necessary properties/fields exist.  To
	 * prevent race conditions while editing rows in a database, a row can be
	 * checked out if the fields 'checked_out' and 'checked_out_time' are available.
	 * While a row is checked out, any attempt to store the row by a user other
	 * than the one who checked the row out should be held until the row is checked
	 * in again.
	 *
	 * @param   integer  $userId  The Id of the user checking out the row.
	 * @param   mixed    $pk      An optional primary key value to check out.  If not set the instance property value is used.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function checkOut($userId, $pk = null)
	{
		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			return true;
		}

		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_NULL_PRIMARY_KEY'));
			$this->setError($e);
			return false;
		}

		// Get the current time in MySQL format.
		$time = \Date::of('now')->toSql();

		// Check the row out by primary key.
		$query = $this->_db->getQuery();
		$query->update($this->_tbl);
		$query->set(array(
			'checked_out' => (int) $userId,
			'checked_out_time' => $time
		));
		$query->whereEquals($this->_tbl_key, $pk);
		$this->_db->setQuery($query->toString());

		if (!$this->_db->execute())
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_CHECKOUT_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		// Set table values in the object.
		$this->checked_out = (int) $userId;
		$this->checked_out_time = $time;

		return true;
	}

	/**
	 * Method to check a row in if the necessary properties/fields exist.  Checking
	 * a row in will allow other users the ability to edit the row.
	 *
	 * @param   mixed    $pk  An optional primary key value to check out.  If not set the instance property value is used.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function checkIn($pk = null)
	{
		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			return true;
		}

		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_NULL_PRIMARY_KEY'));
			$this->setError($e);
			return false;
		}

		$columns = $this->getFields();

		$data = [];
		foreach ($columns as $name => $column)
		{
			// We want to get the default values from the
			// table's schema, rather than assuming
			if ($name == 'checked_out_time' || $name == 'checked_out')
			{
				$data[$name] = $column->Default;
			}
		}

		// Check the row in by primary key.
		$query = $this->_db->getQuery();
		$query->update($this->_tbl);
		$query->set(array(
			'checked_out' => $data['checked_out'],
			'checked_out_time' => $data['checked_out_time']
		));
		$query->whereEquals($this->_tbl_key, $pk);
		$this->_db->setQuery($query->toString());

		// Check for a database error.
		if (!$this->_db->execute())
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_CHECKIN_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		// Set table values in the object.
		$this->checked_out = $data['checked_out'];
		$this->checked_out_time = $data['checked_out_time'];

		return true;
	}

	/**
	 * Method to increment the hits for a row if the necessary property/field exists.
	 *
	 * @param   mixed    $pk  An optional primary key value to increment. If not set the instance property value is used.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function hit($pk = null)
	{
		// If there is no hits field, just return true.
		if (!property_exists($this, 'hits'))
		{
			return true;
		}

		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		// Check the row in by primary key.
		$query = $this->_db->getQuery();
		$query->update($this->_tbl);
		$query->set(array(
			'hits' => new \Hubzero\Database\Value\Raw('(hits + 1)')
		));
		$query->whereEquals($this->_tbl_key, $pk);
		$this->_db->setQuery($query->toString());

		// Check for a database error.
		if (!$this->_db->execute())
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_HIT_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		// Set table values in the object.
		$this->hits++;

		return true;
	}

	/**
	 * Method to determine if a row is checked out and therefore uneditable by
	 * a user. If the row is checked out by the same user, then it is considered
	 * not checked out -- as the user can still edit it.
	 *
	 * @param   integer  $with     The userid to preform the match with, if an item is checked out by this user the function will return false.
	 * @param   integer  $against  The userid to perform the match against when the function is used as a static function.
	 * @return  boolean  True if checked out.
	 * @since   2.1.12
	 * @todo    This either needs to be static or not.
	 */
	public function isCheckedOut($with = 0, $against = null)
	{
		// Handle the non-static case.
		if (isset($this) && ($this instanceof Table) && is_null($against))
		{
			$against = $this->get('checked_out');
		}

		// The item is not checked out or is checked out by the same user.
		if (!$against || ($against == $with))
		{
			return false;
		}

		$db = App::get('db');
		$db->setQuery('SELECT COUNT(userid)' . ' FROM ' . $db->quoteName('#__session') . ' WHERE ' . $db->quoteName('userid') . ' = ' . (int) $against);
		$checkedOut = (boolean) $db->loadResult();

		// If a session exists for the user then it is checked out.
		return $checkedOut;
	}

	/**
	 * Method to get the next ordering value for a group of rows defined by an SQL WHERE clause.
	 * This is useful for placing a new item last in a group of items in the table.
	 *
	 * @param   string  $where  WHERE clause to use for selecting the MAX(ordering) for the table.
	 * @return  mixed   Boolean false an failure or the next ordering value as an integer.
	 * @since   2.1.12
	 */
	public function getNextOrder($where = '')
	{
		// If there is no ordering field set an error and return false.
		if (!property_exists($this, 'ordering'))
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_ORDERING', get_class($this)));
			$this->setError($e);
			return false;
		}

		// Get the largest ordering value for a given where clause.
		$query = $this->_db->getQuery();
		$query->select('MAX(ordering)');
		$query->from($this->_tbl);

		if ($where)
		{
			$query->whereRaw($where);
		}

		$this->_db->setQuery($query->toString());
		$max = (int) $this->_db->loadResult();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_GET_NEXT_ORDER_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		// Return the largest ordering value + 1.
		return ($max + 1);
	}

	/**
	 * Method to compact the ordering values of rows in a group of rows
	 * defined by an SQL WHERE clause.
	 *
	 * @param   string  $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  mixed   Boolean true on success.
	 * @since   2.1.12
	 */
	public function reorder($where = '')
	{
		// If there is no ordering field set an error and return false.
		if (!property_exists($this, 'ordering'))
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_ORDERING', get_class($this)));
			$this->setError($e);
			return false;
		}

		// Initialise variables.
		$k = $this->_tbl_key;

		// Get the primary keys and ordering values for the selection.
		$query = $this->_db->getQuery();
		$query->select($this->_tbl_key);
		$query->select('ordering');
		$query->from($this->_tbl);
		$query->where('ordering', '>=', '0');
		$query->order('ordering', 'asc');

		// Setup the extra where and ordering clause data.
		if ($where)
		{
			$query->whereRaw($where);
		}

		$this->_db->setQuery($query->toString());
		$rows = $this->_db->loadObjectList();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_REORDER_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		// Compact the ordering values.
		foreach ($rows as $i => $row)
		{
			// Make sure the ordering is a positive integer.
			if ($row->ordering >= 0)
			{
				// Only update rows that are necessary.
				if ($row->ordering != $i + 1)
				{
					// Update the row ordering field.
					$query = $this->_db->getQuery();
					$query->update($this->_tbl);
					$query->set(array(
						'ordering' => ($i + 1)
					));
					$query->whereEquals($this->_tbl_key, $row->$k);
					$this->_db->setQuery($query->toString());

					// Check for a database error.
					if (!$this->_db->execute())
					{
						$e = new Exception(
							Lang::txt('JLIB_DATABASE_ERROR_REORDER_UPDATE_ROW_FAILED', get_class($this), $i, $this->_db->getErrorMsg())
						);
						$this->setError($e);

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  mixed    Boolean true on success.
	 * @since   2.1.12
	 */
	public function move($delta, $where = '')
	{
		// If there is no ordering field set an error and return false.
		if (!property_exists($this, 'ordering'))
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_ORDERING', get_class($this)));
			$this->setError($e);
			return false;
		}

		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		// Initialise variables.
		$k = $this->_tbl_key;
		$row = null;
		$query = $this->_db->getQuery();

		// Select the primary key and ordering values from the table.
		$query->select($this->_tbl_key);
		$query->select('ordering');
		$query->from($this->_tbl);

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where('ordering', '<', (int) $this->ordering);
			$query->order('ordering', 'DESC');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where('ordering', '>', (int) $this->ordering);
			$query->order('ordering', 'ASC');
		}

		// Add the custom WHERE clause if set.
		if ($where)
		{
			$query->whereRaw($where);
		}

		// Select the first row with the criteria.
		$this->_db->setQuery($query->toString(), 0, 1);
		$row = $this->_db->loadObject();

		// If a row is found, move the item.
		if (!empty($row))
		{
			// Update the ordering field for this instance to the row's ordering value.
			$query = $this->_db->getQuery();
			$query->update($this->_tbl);
			$query->set(array('ordering' => (int) $row->ordering));
			$query->whereEquals($this->_tbl_key, $this->$k);
			$this->_db->setQuery($query->toString());

			// Check for a database error.
			if (!$this->_db->execute())
			{
				$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}

			// Update the ordering field for the row to this instance's ordering value.
			$query = $this->_db->getQuery();
			$query->update($this->_tbl);
			$query->set(array('ordering' => (int) $this->ordering));
			$query->whereEquals($this->_tbl_key, $row->$k);
			$this->_db->setQuery($query->toString());

			// Check for a database error.
			if (!$this->_db->execute())
			{
				$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}

			// Update the instance value.
			$this->ordering = $row->ordering;
		}
		else
		{
			// Update the ordering field for this instance.
			$query = $this->_db->getQuery();
			$query->update($this->_tbl);
			$query->set(array('ordering' => (int) $this->ordering));
			$query->whereEquals($this->_tbl_key, $this->$k);
			$this->_db->setQuery($query->toString());

			// Check for a database error.
			if (!$this->_db->execute())
			{
				$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		\Hubzero\Utility\Arr::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				$this->setError($e);

				return false;
			}
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery();
		$query->update($this->_tbl);
		$query->set(array('published' => (int) $state));

		// Determine if there is checkin support for the table.
		if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'))
		{
			$query->whereEquals('checked_out', 0, 1)
				->orWhereEquals('checked_out', (int) $userId, 1)
				->resetDepth();
			$checkin = true;
		}
		else
		{
			$checkin = false;
		}

		// Build the WHERE clause for the primary keys.
		$query->whereRaw($k . ' = ' . implode(' OR ' . $k . ' = ', $pks));

		$this->_db->setQuery($query->toString());

		// Check for a database error.
		if (!$this->_db->execute())
		{
			$e = new Exception(Lang::txt('JLIB_DATABASE_ERROR_PUBLISH_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the Table instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->published = $state;
		}

		$this->setError('');
		return true;
	}

	/**
	 * Generic check for whether dependencies exist for this object in the database schema
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @param   mixed  $pk     An optional primary key value check the row for.  If not
	 *                         set the instance property value is used.
	 * @param   array  $joins  An optional array to compiles standard joins formatted like:
	 *                         [label => 'Label', name => 'table name' , idfield => 'field', joinfield => 'field']
	 * @return  boolean  True on success.
	 * @deprecated    2.1.12
	 * @since  2.1.12
	 */
	public function canDelete($pk = null, $joins = null)
	{
		// Deprecation warning.
		Log::debug('Hubzero\Database\Table::canDelete() is deprecated.');

		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		if (is_array($joins))
		{
			// Get a query object.
			$query = $this->_db->getQuery();

			// Setup the basic query.
			$query->select($this->_tbl_key);
			$query->from($this->_tbl);
			$query->whereEquals($this->_tbl_key, $this->$k);
			$query->group($this->_tbl_key);

			// For each join add the select and join clauses to the query object.
			foreach ($joins as $table)
			{
				$query->select('COUNT(DISTINCT ' . $table['idfield'] . ')', $table['idfield']);
				$query->join($table['name'], $table['joinfield'], $k, 'left');
			}

			// Get the row object from the query.
			$this->_db->setQuery((string) $query->toString(), 0, 1);
			$row = $this->_db->loadObject();

			// Check for a database error.
			if ($this->_db->getErrorNum())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}

			$msg = array();
			$i = 0;

			foreach ($joins as $table)
			{
				$k = $table['idfield'] . $i;

				if ($row->$k)
				{
					$msg[] = Lang::txt($table['label']);
				}

				$i++;
			}

			if (count($msg))
			{
				$this->setError("noDeleteRecord" . ": " . implode(', ', $msg));

				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * Method to export the Table instance properties to an XML string.
	 *
	 * @param   boolean  $mapKeysToText  True to map foreign keys to text values.
	 * @return  string   XML string representation of the instance.
	 * @deprecated  2.1.12
	 * @since   2.1.12
	 */
	public function toXML($mapKeysToText = false)
	{
		// Deprecation warning.
		Log::debug('Hubzero\Database\Table::toXML() is deprecated.');

		// Initialise variables.
		$xml = array();
		$map = $mapKeysToText ? ' mapkeystotext="true"' : '';

		// Open root node.
		$xml[] = '<record table="' . $this->_tbl . '"' . $map . '>';

		// Get the publicly accessible instance properties.
		foreach (get_object_vars($this) as $k => $v)
		{
			// If the value is null or non-scalar, or the field is internal ignore it.
			if (!is_scalar($v) || ($v === null) || ($k[0] == '_'))
			{
				continue;
			}

			$xml[] = '	<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
		}

		// Close root node.
		$xml[] = '</record>';

		// Return the XML array imploded over new lines.
		return implode("\n", $xml);
	}

	/**
	 * Method to lock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 * @throws  Exception
	 */
	protected function _lock()
	{
		$this->_db->lockTable($this->_tbl);
		$this->_locked = true;

		return true;
	}

	/**
	 * Method to unlock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 * @since   2.1.12
	 */
	protected function _unlock()
	{
		$this->_db->unlockTables();
		$this->_locked = false;

		return true;
	}
}
