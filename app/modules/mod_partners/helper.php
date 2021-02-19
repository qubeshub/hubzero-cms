<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Modules\Partners;

use Hubzero\Module\Module;
use Components\Partners\Models\Partner;

include_once \Component::path('com_partners') . DS . 'models' . DS . 'partner.php';

/**
 * Module class for displaying a list of partners
 */
class Helper extends Module
{
	/**
	 * Get groups for a user
	 *
	 * @param   integer  $uid   User ID
	 * @param   string   $type  Membership type to return groups for
	 * @return  array
	 */
	private function _getPartners()
	{
		if ($this->featured) {
			$partners = Partner::all()->whereEquals('state', 1)->whereEquals('featured', $this->featured);
		} else {
			$partners = Partner::all()->whereEquals('state', 1);
		}
		return $partners->rows()->pickRandom($this->limit);
	}

	/**
	 * Display module contents
	 *
	 * @return  void
	 */
	public function display()
	{
		// Get the module parameters
		$this->moduleclass = $this->params->get('moduleclass');
		$this->limit = intval($this->params->get('limit', 100));
		$this->featured = $this->params->get('featured', 0);

		// Get the partners
		$this->partners = $this->_getPartners();

		require $this->getLayoutPath();
	}
}
