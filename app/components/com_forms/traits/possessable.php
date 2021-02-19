<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Traits;

trait possessable
{

	/**
	 * Does record belong to user with given ID
	 *
	 * @param    int    $userId   User ID
	 * @return   bool
	 */
	public function isOwnedBy($userId)
	{
		$ownerForeignKey = $this->_ownerForeignKey;

		return $userId === $this->$ownerForeignKey;
	}

}
