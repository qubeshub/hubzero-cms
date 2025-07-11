<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * Nominatim plugin for geocode
 *
 * The NominatimProvider is able to geocode and reverse geocode
 * street addresses. Access to a Nominatim server is required.
 * See the Nominatim Wiki Page for more information.
 */
class plgGeocodeNominatim extends \Hubzero\Plugin\Plugin
{
	/**
	 * Return a geocode provider
	 *
	 * @param  string  $context
	 * @param  object  $adapter
	 * @param  boolean $ip
	 * @return object
	 */
	public function onGeocodeProvider($context, $adapter, $ip=false)
	{
		if ($context != 'geocode.locate' && $context != 'geocode.address')
		{
			return;
		}

		if (!$this->params->get('rootUrl'))
		{
			return;
		}

		return new \Geocoder\Provider\Nominatim\Nominatim(
			$adapter,
			$this->params->get('rootUrl')
		);
	}
}
