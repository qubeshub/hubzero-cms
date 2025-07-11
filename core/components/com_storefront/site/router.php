<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Storefront\Site;

use Hubzero\Component\Router\Base;

defined('_HZEXEC_') or die();

/**
 * Routing class for the component
 */
class Router extends Base
{

/**
 * Turn querystring parameters into an SEF route
 *
 * @param  array &$query Querystring
 */
function build(&$query)
{
	$segments = array();

	if (!empty($query['controller']))
	{
		unset($query['controller']);
	}

	return $segments;
}

/**
 * Parse a SEF route
 *
 * @param  array $segments Exploded route
 * @return array
 */
function parse(&$segments)
{
	$vars = array();

	$vars['controller'] = $segments[0];
	if (!empty($segments[1]))
	{
		$vars['task'] = $segments[1];

		if ($segments[0] == 'product')
		{
			$vars['task'] = 'display';
			$vars['product'] = $segments[1];
		}
	}

	foreach ($segments as $index => $value)
	{
		// skip first two segments -- these are controller and task
		if ($index < 2)
		{
			continue;
		}
		else
		{
			$vars['p' . ($index - 2)]	= $value;
		}
	}

	//print_r($vars);
	return $vars;
}

}
