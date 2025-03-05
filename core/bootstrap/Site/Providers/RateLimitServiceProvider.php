<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Bootstrap\Site\Providers;

use Hubzero\Base\ServiceProvider;

/**
 * Rate Limit service provider
 */
class RateLimitServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return  void
	 */
	public function register()
	{
	}

	/**
	 * Run the ratelimit tests durint request boot
	 *
	 * @return  void
	 */
	public function boot()
	{
		$ip = $this->app['request']->ip();

		if (empty($ip))
		{
			return;
		}

		$db = $this->app['db'];
		
		$db->setQuery('SELECT * FROM #__ratelimit WHERE rule_id=0 AND ip=INET6_ATON(' . $db->Quote($ip) . ') LIMIT 1');
		$result = $db->loadObject();

		$timestamp = time();

		if (empty($result))
		{
			$db->setQuery('INSERT IGNORE INTO #__ratelimit (ip,count) VALUE (INET6_ATON(' . $db->Quote($ip) . '),1);');
			$db->execute();
		}
		else
		{
			$db->setQuery('UPDATE #__ratelimit SET count=count+1 WHERE rule_id=0 AND ip=INET6_ATON(' . $db->Quote($ip) . ');');
			$db->execute();
		}
	}
}
