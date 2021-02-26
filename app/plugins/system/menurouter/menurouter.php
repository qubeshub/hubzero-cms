<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

/**
 * Manipulate routing rules
 */
class plgSystemMenurouter extends \Hubzero\Plugin\Plugin
{
	/**
	 * Method to catch the onAfterRoute event.
	 *
	 * @return  boolean
	 */
	public function onBeforeRoute()
	{
		if (!App::isSite())
		{
			return false;
		}

		// Append a build rule
		// This is called whenever Route::url() is used
		$router = App::get('router');
		$router->rules('build')->append('menurouter', function ($uri)
		{
			$route = $uri->getPath();
			$route = trim($route, '/');
			$segments = explode('/', $route);

			$link = 'index.php?Itemid=';
			$active = false;
			// Community menu
			if (isset($segments[0]) && in_array($segments[0], array('groups', 'projects', 'members', 'partners', 'fmns')))
			{
				// The appropriate method here might be to look
				// up the menu item's parent menu item and use its
				// alias/path
				// $menu = App::get('menu');

				// foreach ($menu->getItems('menutype', 'default') as $m) {
					// echo '<pre>' . var_dump($menu->getItems('id', $m->parent_id)) . '</pre><br>';
					// echo '<pre>' . var_dump($m) . '</pre><br>';
				// }

				array_unshift($segments, 'community');

				// Again, here we would ideally use the parent
				// menu item's info
				$name = 'Community';

				$active = true;
			}

			if (isset($segments[0]) && ($segments[0] == 'publications') &&
			    isset($segments[1]) && ($segments[1] == 'submit')) {
				array_splice($segments, 1, 0, array('submitresource'));

				$name = 'Submit a Resource';

				$active = true;
			}

			// News menu
			if (isset($segments[0]) && in_array($segments[0], array('blog', 'newsletter', 'events')))
			{
				array_unshift($segments, 'news');
				$name = 'News & Activities';
				$active = true;
			}

			// About menu
			if (isset($segments[0]) && in_array($segments[0], array('citations', 'usage')))
			{
				array_unshift($segments, 'about');
				$name = 'About';
				$active = true;
			}

			if ($active)
			{
				$found = false;
				$items = App::get('pathway')->items();
				foreach ($items as $item)
				{
					if ($item->link == $link && $item->name == $name)
					{
						$found = true;
					}
				}
				// Currently injects an extra unnecessary Community in breadcrumbs.
				// I think this is because of redundancy with the System -> Subnav
				// plugin.  So, in other words, this is unnecessary!!!
				// if (!$found)
				// {
				// 	App::get('pathway')->prepend(
				// 		$name,
				// 		$link
				// 	);
				// }

				$result = implode('/', $segments);
				$route  = ($result != '') ? '/' . $result : '';

				$uri->setPath($route);
			}

			return $uri;
		});

		// Check the request for a URL missing
		// the desired prefix. Fix, and redirect.
		$request = App::get('request');

		// NOTE: We only want to do this if the
		// request method is GET.
		if ($request->method() != 'GET')
		{
			return false;
		}

		$segment = $request->segment(1);

		// Community menu
		if (in_array($segment, array('groups', 'projects', 'members', 'partners', 'fmns')))
		{
			$uri = str_replace('/' . $segment, '/community/' . $segment, $request->current(true));
			App::redirect($uri);
		}

		// Resource menu
		// Legacy redirect: Remove qubesresources from all URLs
		//   Components affected: publications, collections, software
		//   VERY important to keep this code around for a bit for DOIs.
		//   Eventually update DOI urls through the DOI service.
		if (in_array($segment, array('qubesresources')))
		{
			$uri = str_replace('/' . $segment, '', $request->current(true));
			App::redirect($uri);
		}
		// Inject "Submit a Resource" page if action is submit
		$segment_next = $request->segment(2);
		if ($segment == 'publications') {
			if ($segment_next == 'submit') {
				$uri = str_replace('/publications/submit', '/publications/submitresource/submit', $request->current(true));
				App::redirect($uri);
			}
			if ($segment_next == null) {
				$uri = str_replace('/publications', '/publications/browse', $request->current(true));
				App::redirect($uri);
			}
		}

		// News menu
		if (in_array($segment, array('blog', 'newsletter', 'events')))
		{
			$uri = str_replace('/' . $segment, '/news/' . $segment, $request->current(true));
			App::redirect($uri);
		}

		// About menu
		if (in_array($segment, array('citations', 'usage')))
		{
			$uri = str_replace('/' . $segment, '/about/' . $segment, $request->current(true));
			App::redirect($uri);
		}

		return true;
	}
}
