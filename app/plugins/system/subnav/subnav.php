<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * System plugin checking for missing/required registration fields
 */
class plgSystemSubnav extends \Hubzero\Plugin\Plugin
{
	/**
	 * Hook for after parsing route
	 *
	 * @return void
	 */
	public function onSubnavRequest()
	{
		// parse mappings
		$comMappings = $this->params->get('component_mappings');
		$urlMappings = $this->params->get('url_mappings');

		$comMappings = explode("\n", $comMappings);
		$urlMappings = explode("\n", $urlMappings);

		$componentsMap = array();
		foreach ($comMappings as $mapping)
		{
			$mapping = explode(',', $mapping);
			if (count($mapping) === 2) {
				$componentsMap[trim($mapping[0])] = trim($mapping[1]);
			}
		}

		$urlsMap = array();
		foreach ($urlMappings as $mapping)
		{
			$mapping = explode(',', $mapping);
			if (count($mapping) === 2) {
				$urlsMap[trim($mapping[0])] = trim($mapping[1]);
			}
		}

		$map = array('com' => $componentsMap, 'url' => $urlsMap);

		return $map;
	}
}
