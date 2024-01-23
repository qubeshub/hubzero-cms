<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'Plugin' . DS . 'Plugin.php';
require_once \Component::path('com_publications') . DS . 'models' . DS . 'cloud.php';

use Components\Publications\Models\PubCloud;

/**
 * Publications Plugin class for alignments
 */
class plgPublicationsAlignments extends \Qubeshub\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Return the alias and name for this category of content
	 *
	 * @param   object   $publication  Current publication
	 * @param   string   $version      Version name
	 * @param   boolean  $extended     Whether or not to show panel
	 * @return  array
	 */
	public function &onPublicationAreas($publication, $version = 'default', $extended = true)
	{
		$areas = array();

		if ($publication->_category->_params->get('plg_alignments'))
		{
			$areas['alignments'] = Lang::txt('PLG_PUBLICATION_ALIGNMENTS');
		}

		return $areas;
	}

	/**
	 * Return data on a resource view (this will be some form of HTML)
	 *
	 * @param   object   $publication  Current publication
	 * @param   string   $option       Name of the component
	 * @param   array    $areas        Active area(s)
	 * @param   string   $rtrn         Data to be returned
	 * @param   string 	 $version      Version name
	 * @param   boolean  $extended     Whether or not to show panel
	 * @return  array
	 */
	public function onPublication($publication, $option, $areas, $rtrn='all', $version = 'default', $extended = true)
	{
		$arr = array(
			'name'    => 'alignments',
			'html'    => '',
			'metadata'=> ''
		);
		$rtrn = 'all';

		// Check if our area is in the array of areas we want to return results for
		if (is_array($areas))
		{
			if (!array_intersect($areas, $this->onPublicationAreas($publication))
			 && !array_intersect($areas, array_keys($this->onPublicationAreas($publication))))
			{
				$rtrn = 'metadata';
			}
		}

		if (!$publication->_category->_params->get('plg_alignments'))
		{
			return $arr;
		}

		$cloud = new PubCloud($publication->version->get('id'));

		// Are we returning HTML?
		if ($rtrn == 'all' || $rtrn == 'html')
		{
			// Instantiate a view
			$view = $this->view('default', 'browse')
				->set('option', $option)
				->set('publication', $publication)
				->set('cloud', $cloud->render('html', array('type' => 'focusareas')))
				->setErrors($this->getErrors());

			// Return the output
			$arr['html'] = $view->loadTemplate();
		}

		// Metadata
		$mtype = $publication->master_type;
		$tags = $cloud->tags('list', array("type" => "focusareas"), true);
		$fas = \Components\Tags\Models\FocusArea::fromTags($tags);
		$roots = $fas->parents(true)->orderByAlignment($mtype, 'P');
		$view = $this->view('default', 'metadata')
			->set('publication', $publication)
			->set('fas', $roots);

		$arr['metadata'] = $view->loadTemplate();

		return $arr;
	}
}
