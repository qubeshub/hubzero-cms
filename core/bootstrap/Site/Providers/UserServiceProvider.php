<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Bootstrap\Site\Providers;

use Hubzero\Base\ServiceProvider;
use Hubzero\User\Picture\File;
use Hubzero\User\Picture\Namedfile;
use Hubzero\User\Manager;
use Hubzero\User\User;

/**
 * User service provider
 */
class UserServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return  void
	 */
	public function register()
	{
		$this->app['user'] = function($app)
		{
			return new Manager($app);
		};
	}

	/**
	 * Force SSL if site is configured to and
	 * the connection is not secure.
	 *
	 * @return  void
	 */
	public function boot()
	{
		// Set the base link to use for profiles
		User::$linkBase = 'index.php?option=com_members&id={ID}';

		// Set the picture resolver
		if ($this->app->has('component'))
		{
			$params = $this->app['component']->params('com_members');

			$config = [
				'path'          => PATH_APP . DS . 'site' . DS . 'members',
				'pictureName'   => 'profile.png',
				'thumbnailName' => 'thumb.png',
				'fallback'      => $params->get('defaultpic', '/core/components/com_members/site/assets/img/profile.gif')
			];

			// File 'profile.png'
			User::$pictureResolvers[] = new File($config);

			// Legacy picture 'mypicture.jpg'
			User::$pictureResolvers[] = new Namedfile($config);

			// Specified resolver
			if ($resolver = $params->get('picture', ''))
			{
				$cls = 'Hubzero\\User\\Picture\\' . ucfirst($resolver);

				if (class_exists($cls))
				{
					User::$pictureResolvers[] = new $cls($config);
				}
			}
		}
	}
}
