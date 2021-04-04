<?php
/**
 * @package    framework
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Qubeshub\View\Helper;

require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'View' . DS . 'View.php';
require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'View' . DS . 'Helper' . DS . 'HelperInterface.php';

use Qubeshub\View\View;

/**
 * Abstract view helper class
 */
abstract class AbstractHelper implements HelperInterface
{
	/**
	 * View object instance
	 *
	 * @var  object
	 */
	protected $_view = null;

	/**
	 * Set the View object
	 *
	 * @param   object  $view
	 * @return  object
	 */
	public function setView(View $view)
	{
		$this->_view = $view;

		return $this;
	}

	/**
	 * Get the view object
	 *
	 * @return  null|Renderer
	 */
	public function getView()
	{
		return $this->_view;
	}
}
