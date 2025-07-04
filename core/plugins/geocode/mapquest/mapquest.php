<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * MapQuest plugin for geocode
 *
 * The MapQuestProvider is able to geocode and reverse geocode
 * street addresses. A valid api key is required.
 */
class plgGeocodeMapquest extends \Hubzero\Plugin\Plugin
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

		if (!$this->params->get('apiKey') || $ip)
		{
			return;
		}

		return new \Geocoder\Provider\MapQuest\MapQuest(
			$adapter,
			$this->params->get('apiKey')
		);
	}
}
