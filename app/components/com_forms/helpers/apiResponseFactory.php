<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Helpers;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/apiBatchUpdateResponse.php";
require_once "$componentPath/helpers/apiNullResponse.php";
require_once "$componentPath/helpers/apiReadResponse.php";

use Components\Forms\Helpers\ApiBatchUpdateResponse;
use Components\Forms\Helpers\ApiNullResponse;
use Components\Forms\Helpers\ApiReadResponse;

class ApiResponseFactory
{

	/**
	 * Instantiates appropriate ApiResponse type
	 *
	 * @param    array    $args   CRUD operation and result
	 * @return   object
	 *
	 */
	public function one($args)
	{
		$operation = $args['operation'];

		switch($operation)
		{
			case 'batchUpdate':
				return new ApiBatchUpdateResponse($args);
			case 'read':
				return new ApiReadResponse($args);
			default:
				return new ApiNullResponse($args);
		}
	}

}
