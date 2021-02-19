<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */
 
namespace Components\Fmns\Site;

use Hubzero\Component\Router\Base;

/**
 * Routing class for the component
 */
class Router extends Base
{
	/**
	 * Build SEF route
	 * 
	 * Incoming data is an associative array of querystring
	 * values to build a SEF URL from.
	 *
	 * Example:
	 *    controller=characters&task=view&id=123
	 *
	 * Gets turned into:
	 *    $segments = array(
	 *       'controller' => 'characters',
	 *       'task'=> 'view',
	 *       'id' => 123
	 *    );
	 *
	 * To generate:
	 *    url: /characters/view/123
	 * 
	 * @param   array  &$query
	 * @return  array 
	 */
	public function build(&$query)
	{
		$segments = array();

		if (!empty($query['controller'])) 
		{
			$segments[] = $query['controller'];
			unset($query['controller']);
		}
		if (!empty($query['task'])) 
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}
    if (!empty($query['page']))
    {
      $segments[] = $query['page'];
      unset($query['page']);
    }

		return $segments;
	}

	/**
	 * Parse SEF route
	 *
	 * Incoming data is an array of URL segments.
	 *
	 * Example:
	 *    url: /characters/view/123
	 *    $segments = array('characters', 'view', '123')
	 * 
	 * @param   array  $segments
	 * @return  array
	 */
	public function parse(&$segments)
	{
		$vars = array();

		if (empty($segments))
		{
			return $vars;
		}

    // Check first to see if requesting a page
		if (in_array($segments[0], array('ambassadors'))) 
		{
      $vars['task'] = 'page';
			$vars['page'] = $segments[0];
		} else {
      // Otherwise must be a controller
      $vars['task'] = $segments[0];
    }
    
		return $vars;
	}
}
