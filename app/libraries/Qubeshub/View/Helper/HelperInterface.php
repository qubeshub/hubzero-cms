<?php
/**
 * @package    framework
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Qubeshub\View\Helper;

require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'View' . DS . 'View.php';

use Qubeshub\View\View;

/**
 * Interface for view helpers
 */
interface HelperInterface
{
	/**
	 * Set the View object
	 *
	 * @param   object  $view
	 * @return  object
	 */
	public function setView(View $view);

	/**
	 * Get the View object
	 *
	 * @return  object
	 */
	public function getView();
}
