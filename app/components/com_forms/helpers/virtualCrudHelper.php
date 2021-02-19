<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Helpers;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/crudHelper.php";

use Components\Forms\Helpers\CrudHelper;

class VirtualCrudHelper extends CrudHelper
{

	/**
	 * Constructs VirtualCrudHelper instance
	 *
	 * @param    array   $args   Instantiation state
	 * @return   void
	 */
	public function __construct($args = [])
	{
		parent::__construct($args);
	}

	/**
	 * Handles failed creation of a record
	 *
	 * @return   void
	 */
	public function failedCreate($record, $url = '/')
	{
		parent::failedCreate($record);

		$this->_router->redirect($url);
	}

}
