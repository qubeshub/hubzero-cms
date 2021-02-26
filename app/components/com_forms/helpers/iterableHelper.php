<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Helpers;

class IterableHelper
{

	/**
	 * Maps given objects by function return value
	 *
	 * @param    array    $objects        Objects to be mapped
	 * @param    string   $functionName   Name of function to invoke
	 * @param    array    $args           Argument(s) to be passed to function
	 * @return   array
	 */
	public function functionMap($objects, $functionName, $args = [])
	{
		$map = array_map(function($object) use ($functionName, $args) {
			return $object->$functionName(...$args);
		}, $objects);

		return $map;
	}

}
