<?php
/**
 * @package    framework
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Qubeshub\Base;

use Hubzero\Container\Container;
use Hubzero\Error\Exception\NotAuthorizedException;
use Hubzero\Error\Exception\NotFoundException;
use Hubzero\Error\Exception\MethodNotAllowedException;
use Hubzero\Error\Exception\RuntimeException;
use Hubzero\Facades\Facade;
use Hubzero\Http\RedirectResponse;
use Hubzero\Http\Request;
use Hubzero\Http\Response;

/**
 * Application class
 *
 * Heavily influenced by Laravel's Application class
 * http://laravel.com
 */
class Application extends Container
{
	/**
	 * The framework version.
	 *
	 * @var  string
	 */
	const VERSION = '2.1.0-dev';

	/**
	 * Indicates if the application has "booted".
	 *
	 * @var  boolean
	 */
	protected $booted = false;

	/**
	 * Indicates if the application has "loaded".
	 *
	 * @var  boolean
	 */
	protected $loaded = false;

	/**
	 * All of the registered service providers.
	 *
	 * @var  array
	 */
	protected $serviceProviders = array();

	protected $startms = 0;

	protected $endms = 0;

	/**
	 * Create a new application instance.
	 *
	 * @param   object  $request   Request object
	 * @param   object  $response  Response object
	 * @return  void
	 */
	public function __construct($client = '', Request $request = null, Response $response = null)
	{
		$this->startms = microtime(true);

		// Work around for issues with SCRIPT_NAME and PHP_SELF set incorrectly by php-fpm
		// GH-12996 https://github.com/php/php-src/issues/12996 fixed in 8.2.16
		// GH-10869 https://github.com/php/php-src/issues/10869 fixed in 8.1.18
		// Don't overrite ORIG_SCRIPT_NAME if already set (e.g. between above versions)

		if (PHP_VERSION_ID < 80216 && isset($_SERVER['PATH_INFO']) && strpos($_SERVER['PATH_INFO'], '%') !== false)
		{
			if (!isset($_SERVER['ORIG_SCRIPT_NAME']))
			{
				$_SERVER['ORIG_SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];
			}

			$_SERVER['SCRIPT_NAME'] = str_replace(rawurldecode($_SERVER['PATH_INFO']), '', $_SERVER['SCRIPT_NAME']);
			$_SERVER['PHP_SELF'] = str_replace(rawurldecode($_SERVER['PATH_INFO']), '', $_SERVER['PHP_SELF']);
		}

		parent::__construct();

		$this['request']  = ($request  ?: Request::createFromGlobals());
		$this['response'] = ($response ?: new Response());
		$this['app'] = $this;
	}

	/**
	 * Dynamically access application services.
	 *
	 * @param   string  $key
	 * @return  mixed
	 */
	public function __get($key)
	{
		return $this[$key];
	}

	/**
	 * Dynamically set application services.
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this[$key] = $value;
	}

	/**
	 * Handle dynamic, static calls to the object.
	 *
	 * @param   string  $method
	 * @param   array   $args
	 * @return  mixed
	 */
	public function __call($method, $args)
	{
		$method = strtolower($method);
		if (substr($method, 0, 2) == 'is')
		{
			$client = substr($method, 2);

			$name = $this['client']->name;
			if (isset($this['client']->alias))
			{
				$name = $this['client']->alias;
			}
			return ($name == $client);
		}

		throw new RuntimeException(sprintf('Method [%s] not found.', $method));
	}

	/**
	 * Get the version number of the application.
	 *
	 * @return  string
	 */
	public function version()
	{
		if (!defined('HVERSION'))
		{
			return static::VERSION;
		}
		else
		{
			return HVERSION;
		}
	}

	/**
	 * Register facades with the autoloader
	 *
	 * @param   array  $aliases
	 * @return  void
	 */
	public function registerFacades($aliases = array())
	{
		// Set the application to resolve Facades
		Facade::setApplication($this);

		// Create aliaes for runtime
		Facade::createAliases((array) $aliases);
	}

	/**
	 * Register a service provider with the application.
	 *
	 * @param  mixed   $provider  \Hubzero\Base\ServiceProvider|string
	 * @param  array   $options
	 * @param  bool    $force
	 * @return object
	 */
	public function register($provider, $options = array()) //, $force = false)
	{
		/*if ($registered = $this->getRegistered($provider) && !$force)
		{
			return $registered;
		}*/

		// If the given "provider" is a string, we will resolve it, passing in the
		// application instance automatically for the developer. This is simply
		// a more convenient way of specifying your service provider classes.
		if (is_string($provider))
		{
			$provider = $this->resolveProviderClass($provider);
		}

		$provider->register();

		// Once we have registered the service we will iterate through the options
		// and set each of them on the application so they will be available on
		// the actual loading of the service objects and for developer usage.
		foreach ($options as $key => $value)
		{
			$this[$key] = $value;
		}

		// Since service providers can do more than just register callbacks,
		// we need to track the loaded providers for futher use later in the
		// application.
		$this->markAsRegistered($provider);

		// If the application has already booted, we will call this boot method on
		// the provider class so it has an opportunity to do its boot logic and
		// will be ready for any usage by the developer's application logics.
		if ($this->booted)
		{
			$this->bootProvider($provider);
		}

		return $this;
	}

	/**
	 * Get the registered service provider instance if it exists.
	 *
	 * @param   mixed  $provider  \Hubzero\Base\ServiceProvider|string
	 * @return  mixed  \Hubzero\Base\ServiceProvider|null
	 */
	/*public function getRegistered($provider)
	{
		$name = is_string($provider) ? $provider : get_class($provider);

		if (array_key_exists($name, $this->serviceProviders))
		{
			return $this->serviceProviders[$name];
		}

		return null;
	}*/

	/**
	 * Resolve a service provider instance from the class name.
	 *
	 * @param   string  $provider
	 * @return  object  \Hubzero\Base\ServiceProvider
	 */
	protected function resolveProviderClass($provider)
	{
		return new $provider($this);
	}

	/**
	 * Mark the given provider as registered.
	 *
	 * @param   object  \Hubzero\Base\ServiceProvider
	 * @return  void
	 */
	protected function markAsRegistered($provider)
	{
		$class = get_class($provider);

		$this->serviceProviders[$class] = $provider;
	}

	/**
	 * Detect the application's current environment.
	 *
	 * @param   array|string  $clients
	 * @return  string
	 */
	public function detectClient($clients)
	{
		$args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;

		return $this['client'] = with(new ClientDetector($this['request']))->detect($clients, $args);
	}

	/**
	 * Determine if we are running in the console.
	 *
	 * @return  bool
	 */
	public function runningInConsole()
	{
		return php_sapi_name() == 'cli';
	}

	/**
	 * Abort
	 *
	 * @param   integer  $code     Error code
	 * @param   string   $message  Error message
	 * @return  void
	 */
	public function abort($code, $message='')
	{
		switch ($code)
		{
			case 405:
				throw new MethodNotAllowedException($message, $code);
			break;

			case 404:
				throw new NotFoundException($message, $code);
			break;

			case 403:
				throw new NotAuthorizedException($message, $code);
			break;

			default:
				throw new RuntimeException($message, $code);
			break;
		}
	}

	/**
	 * Redirect current request to new request (sub requests)
	 *
	 * @param   string  $url      Url to redirect to
	 * @param   string  $message  Message to display on redirect.
	 * @param   array   $type     Message type.
	 * @return  void
	 */
	public function redirect($url, $message = null, $type = 'success')
	{
		$redirect = new RedirectResponse($url == null ? '' : $url);
		$redirect->setRequest($this['request']);

		if ($message && $this->has('notification'))
		{
			$this['notification']->message($message, $type);
		}

		$redirect->send();

		$this->close();
	}

	/**
	 * Terminate the application
	 *
	 * @return  void
	 */
	public function close()
	{
		exit();
	}

	/**
	 * Provides a secure hash based on a seed
	 *
	 * @param   string  $seed  Seed string.
	 * @return  string  A secure hash
	 */
	public function hash($seed)
	{
		return md5($this['config']->get('secret') . $seed);
	}

	public function load($client = null, $environments = null)
	{
		if ($this->loaded)
		{
			return;
		}

		if ($client == null)
		{
			if ($environments == null)
			{
				$environments = array(
					'administrator' => 'administrator',
					'api'           => 'api',
					'cli'           => 'cli',
					'install'       => 'install',
					'files'         => 'files',
				);
			}

			$this->detectClient($environments);
		}
		else
		{
			$this['client'] = ClientManager::client($client, true);
		}

		$client = $this['client']->name;

		$this['config'] = new \Hubzero\Config\Repository($client);

		$providers = PATH_CORE . '/bootstrap/' . $client . '/services.php';
		$services  = file_exists($providers) ? require $providers : array();

		$providers = PATH_CORE . '/bootstrap/' . ucfirst($client) . '/services.php';
		$services  = file_exists($providers) ? array_merge($services, require $providers) : $services;

		$providers = PATH_APP . '/bootstrap/' . $client . '/services.php';
		$services  = file_exists($providers) ? array_merge($services, require $providers) : $services;

		foreach ($services as $service)
		{
			$this->register($service);
		}

		$facades = PATH_CORE . '/bootstrap/' . $client . '/aliases.php';
		$aliases = file_exists($facades) ? require $facades : array();

		$facades = PATH_CORE . '/bootstrap/' . ucfirst($client) . '/aliases.php';
		$aliases = file_exists($facades) ? array_merge($aliases, require $facades) : $aliases;

		$facades = PATH_APP . '/bootstrap/' . $client . '/aliases.php';
		$aliases = file_exists($facades) ? array_merge($aliases, require $facades) : $aliases;

		$this->registerFacades($aliases);

		$this->loaded = true;
	}

	/**
	 * Boot the application's service providers.
	 *
	 * @return  void
	 */
	public function boot()
	{
		if ($this->booted)
		{
			return;
		}

		$this->load();

		array_walk($this->serviceProviders, function($p)
		{
			$this->bootProvider($p);
		});

		$this->booted = true;
	}

	/**
	 * Boot the given service provider.
	 *
	 * @param   object  $provider
	 * @return  void
	 */
	protected function bootProvider(ServiceProvider $provider)
	{
		if (method_exists($provider, 'boot'))
		{
			return $provider->boot();
		}
	}

	/**
	 * Get only runnable services
	 *
	 * @param   array  $layers  Unfiltered services
	 * @return  array  Filtered runnable services
	 */
	protected function middleware($services)
	{
		return array_filter($services, function($service)
		{
			return $service instanceof Middleware;
		});
	}

	/**
	 * Application layer is responsible for dispatching request
	 *
	 * @param   object  $request  Request object
	 * @return  object  Response object
	 */
	public function handle(Request $request)
	{
		return $this['response']->compress($this['config']->get('gzip', false));
	}

	/**
	 * Run the application and send the response.
	 *
	 * @return  void
	 */
	public function run()
	{
		// Boot the application
		//
		// This allows service providers to finish performing any
		// needed setup.
		$this->boot();

		// Start handling errors before doing anything else
		if ($this->has('error'))
		{
			array_walk($this->serviceProviders, function($p)
			{
				if (method_exists($p, 'startHandling'))
				{
					return $p->startHandling();
				}
			});
		}

		// Initialise
		if (!$this->runningInConsole() && $this->has('dispatcher'))
		{
			$this['dispatcher']->trigger('system.onAfterInitialise');

			if ($this->has('profiler') && $this->get('profiler'))
			{
				$this['profiler']->mark('afterInitialise');
			}
		}

		// Create a new stack and bind to application then
		$this['stack'] = new Stack($this);

		// Send request throught stack and finally send response
		$this['stack']
			->send($this['request'])
			->through($this->middleware($this->serviceProviders))
			->then(function($request, $response)
			{
				$response->prepare($request);
				$response->send();
			});
	}

	public function __destruct()
	{
		ignore_user_abort(true);

		session_write_close();

		while(ob_get_level())
			ob_end_flush();

		flush();

		fastcgi_finish_request();

		$this->endms = microtime(true);

		if ( function_exists("apcu_enabled") && apcu_enabled())
		{
			$time = (int) $this->startms;
			$ms = $this->endms - $this->startms;

			// The following is subject to race conditions, restarts, maybe garbage collection
			// and other vagaries of the APCu cache.  This data is intended as an
			// estimate so being exact really should not matter.

			$new_sec_count = (float) apcu_inc($time, 1, $success, 61);
			$old_sec_avg = (float) apcu_fetch('avg'.$time, $success);
			$new_sec_avg = ((($new_sec_count - 1) * $old_sec_avg) + $ms)/$new_sec_count;
			$sec_avg = (float) apcu_store('avg'.$time, $new_sec_avg, 61);

			$new_min_count = (float) apcu_inc(intdiv($time,60), 1, $success, 3601);
			$old_min_avg = (float) apcu_fetch('avg'.intdiv($time,60), $success);
			$new_min_avg = ((($new_min_count - 1) * $old_min_avg) + $ms)/$new_min_count;
			$min_avg = (float) apcu_store('avg'.intdiv($time,60), $new_min_avg, 3601);

			$new_hour_count = (float) apcu_inc(intdiv($time,3600), 1, $success, 86401);
			$old_hour_avg = (float) apcu_fetch('avg'.intdiv($time,3600), $success);
			$new_hour_avg = ((($new_hour_count - 1) * $old_hour_avg) + $ms)/$new_hour_count;
			$hour_avg = (float) apcu_store('avg'.intdiv($time,3600), $new_hour_avg, 86401);

			$new_day_count = (float) apcu_inc(intdiv($time,86400), 1, $success, 2678401);
			$old_day_avg = (float)  apcu_fetch('avg'.intdiv($time,86400), $success);
			$new_day_avg = ((($new_day_count - 1) * $old_day_avg) + $ms)/$new_day_count;
			$day_avg = (float) apcu_store('avg'.intdiv($time,86400), $new_day_avg, 2678401);
		}
	}

}
