<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Helpers;

class RelationalQueryHelper
{

	/**
	 * Creates a one dimensional array populated with requested data
	 * from given relational query without mutating relational query
	 *
	 * @param    object   $relationalQuery   Relational query
	 * @param    string   $attribute         Name of attribute to collect
	 * @return   array
	 */
	public function flatMap($relationalQuery, $attribute)
	{
		$flatMap = [];

		foreach ($relationalQuery as $record)
		{
			array_push($flatMap, $record->get($attribute));
		}

		return $flatMap;
	}

}
