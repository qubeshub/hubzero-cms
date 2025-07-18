<?php
/**
 * @package    framework
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Hubzero\Session\Storage;

use Hubzero\Session\Store;

/**
 * Memcached session storage handler
 *
 * Inspired by Joomla's JSessionStorageMemchached class
 */
class Memcached extends Store
{
	/**
	 * Key prefix
	 *
	 * @var  string
	 */
	private $prefix  = 'sess_';

	/**
	 * Resource for the current memcached connection.
	 *
	 * @var  object
	 */
	private $engine;

	/**
	 * Use compression?
	 *
	 * @var  int
	 */
	private $compress = false;

	/**
	 * List of servers
	 *
	 * @var  array
	 */
	private $servers = array();

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 * @return  void
	 */
	public function __construct($options = array())
	{
		if (!self::isAvailable())
		{
			throw new Exception(\Lang::txt('JLIB_SESSION_MEMCACHE_EXTENSION_NOT_AVAILABLE'));
		}

		if (isset($options['prefix']))
		{
			$this->prefix = $options['prefix'];
		}

		parent::__construct($options);

		if (isset($options['compress']) && $options['compress'])
		{
			$this->compress = \Memcached::OPT_COMPRESSION;
		}

		if (!isset($options['servers']) || empty($options['servers']))
		{
			$conf = new \Hubzero\Config\Repository('site');

			$options['servers'] = array(
				array(
					'host'   => $config->get('memcache_server_host', 'localhost'),
					'port'   => $config->get('memcache_server_port', 11211),
					'weight' => $config->get('memcache_persist', true)
				)
			);
		}

		$this->servers = $options['servers'];
	}

	/**
	 * Open the SessionHandler backend.
	 *
	 * @param   string   $save_path  The path to the session object.
	 * @param   string   $name       The name of the session.
	 * @return  boolean  True on success, false otherwise.
	 */
	#[\ReturnTypeWillChange]
	public function open($save_path, $name)
	{
		$this->engine = new \Memcached;

		// For each server in the array, we'll just extract the configuration and add
		// the server to the Memcached connection. Once we have added all of these
		// servers we'll verify the connection is successful and return it back.
		foreach ($this->servers as $server)
		{
			$this->engine->addServer(
				$server['host'], $server['port'], $server['weight']
			);
		}

		return true;
	}

	/**
	 * Close the SessionHandler backend.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	#[\ReturnTypeWillChange]
	public function close()
	{
		// $this->engine->close();
		return true;
	}

	/**
	 * Read the data for a particular session identifier from the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 * @return  string  The session data.
	 */
	#[\ReturnTypeWillChange]
	public function read($id)
	{
		$key = $this->key($id);

		$this->expiration($key);

		return $this->engine->get($key);
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string   $id    The session identifier.
	 * @param   string   $data  The session data.
	 * @return  boolean  True on success, false otherwise.
	 */
	#[\ReturnTypeWillChange]
	public function write($id, $data)
	{
		$key = $this->key($id);

		if ($this->engine->get($key . '_expire'))
		{
			$this->engine->replace($key . '_expire', time());
		}
		else
		{
			$this->engine->set($key . '_expire', time());
		}
		if ($this->engine->get($key))
		{
			$this->engine->replace($key, $data);
		}
		else
		{
			$this->engine->set($key, $data);
		}

		return;
	}

	/**
	 * Destroy the data for a particular session identifier in the SessionHandler backend.
	 *
	 * @param   string   $id  The session identifier.
	 * @return  boolean  True on success, false otherwise.
	 */
	#[\ReturnTypeWillChange]
	public function destroy($id)
	{
		$key = $this->key($id);

		$this->engine->delete($key . '_expire');

		return $this->engine->delete($key);
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * -- Not Applicable in memcached --
	 *
	 * @param   int  $maxlifetime  The maximum age of a session.
	 * @return  boolean  True on success, false otherwise.
	 */
	#[\ReturnTypeWillChange]
	public function gc($maxlifetime = null)
	{
		return true;
	}

	/**
	 * Get single session data as an object
	 *
	 * @param   int  $id  Session Id
	 * @return  object
	 */
	public function session($id)
	{
		$session = new Object;
		$session->session_id = $id;
		$session->data       = $this->read($id);

		return $session;
	}

	/**
	 * Get list of all sessions
	 *
	 * @param   array  $filters
	 * @return  array
	 */
	public function all($filters = array())
	{
		// load all session keys
		$data = $this->engine->getAllKeys();

		$sessions = array();

		// loop through all session keys and get data
		foreach ($data as $key => $value)
		{
			if (strpos($value->name, $this->prefix) === 0)
			{
				$session = new Object;
				$session->session_id = $value->name;
				$session->data       = $value;

				$sessions[] = $session;
			}
		}

		// return array of session objects
		return $sessions;
	}

	/**
	 * Build the storage key
	 *
	 * @param   string  $id  The session identifier.
	 * @return  string
	 */
	protected function key($id)
	{
		return $this->prefix . $id;
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public static function isAvailable()
	{
		return (extension_loaded('memcached') && class_exists('Memcached'));
	}

	/**
	 * Set expire time on each call since memcached sets it on cache creation.
	 *
	 * @param   string  $key  Cache key to expire.
	 * @return  void
	 */
	protected function expiration($key)
	{
		$lifetime = ini_get("session.gc_maxlifetime");

		$expire = $this->engine->get($key . '_expire');

		// Set prune period
		if ($expire + $lifetime < time())
		{
			$this->engine->delete($key);
			$this->engine->delete($key . '_expire');
		}
		else
		{
			$this->engine->replace($key . '_expire', time());
		}
	}
}
