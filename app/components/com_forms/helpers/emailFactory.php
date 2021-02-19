<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Helpers;

use Hubzero\Utility\Arr;

class EmailFactory
{

	/**
	 * Instantiates an Email object
	 *
	 * @return   void
	 */
	public function __construct($args = [])
	{
		$this->_emailClass = Arr::getValue($args, 'class', 'Hubzero\Mail\Message');
	}

	/**
	 * Instantiates an email message
	 *
	 * @return   object
	 */
	public function one()
	{
		return new $this->_emailClass;
	}

}
