<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Models\BlockElement;

use Components\Publications\Models\BlockElement as Base;
use Components\Publications\Models\PubCloud;
use Components\Tags\Models\FocusArea;

/**
 * Renders focus areas element
 */
class FocusAreas extends Base
{
	/**
  * Element name
  *
  * @var		string
  */
	protected	$_name = 'focusareas';

	/**
	 * Check completion status
	 *
	 * @return  object
	 */
	public function getStatus($manifest, $pub = null)
	{
		$status = new \Components\Publications\Models\Status();

		// Get requirements to check against
        $fas = FocusArea::fromObject($pub->master_type);
		$selected = (new PubCloud($pub->version->id))->render('search', array('type' => 'focusareas')); // Returns flattened paths

        $status->status = $fas->checkStatus($selected) ? 1 : 0;

		return $status;
	}

	/**
	 * Render
	 *
	 * @return  object
	 */
	public function render($elementid, $manifest, $pub = null, $viewname = 'edit',
		$status = null, $master = null, $order = 0)
	{
		$html   = '';

		switch ($viewname)
		{
			case 'edit':
			default:
				$html = $this->drawFormField( $elementid, $manifest, $pub,
					$status->elements->$elementid, $master);
			break;

			case 'freeze':
			case 'curator':
				$html = $this->drawItem( $elementid, $manifest, $pub, $status->elements->$elementid, $master, $viewname);
			break;
		}

		return $html;
	}

	/**
	 * Draw element with no editing capabilities
	 *
	 * @return  object
	 */
	public function drawItem( $elementId, $manifest, $pub = null,
		$status = null, $master = null, $viewname = 'freeze')
	{
		$view = new \Hubzero\Plugin\View(
			array(
				'folder'	=>'projects',
				'element'	=>'publications',
				'name'		=>'freeze',
				'layout'	=>'focusareas'
			)
		);

		$view->pub 			 = $pub;
		$view->manifest		 = $manifest;
		$view->status		 = $status;
		$view->elementId	 = $elementId;
		$view->name			 = $viewname;
		$view->master		 = $master;

		$view->fas = FocusArea::fromObject($pub->master_type);
		$cloud = new PubCloud($pub->version->get('id'));
		$view->fas_cloud = $cloud->render('html', array('type' => 'focusareas'));
		
		return $view->loadTemplate();
	}

	/**
	 * Draw element
	 *
	 * @return  object
	 */
	public function drawFormField( $elementId, $manifest, $pub = null,
		$status = null, $master = null)
	{
		$view = new \Hubzero\Plugin\View(
			array(
				'folder'	=>'projects',
				'element'	=>'publications',
				'name'		=>'blockelement',
				'layout'	=>'focusareas'
			)
		);

		$view->pub 			 = $pub;
		$view->manifest		 = $manifest;
		$view->status		 = $status;
        $view->master        = $master;
		$view->elementId	 = $elementId;

        $view->fas = FocusArea::fromObject($pub->master_type);
		$cloud = new PubCloud($pub->version->get('id'));
		$view->selected = $cloud->render('list', array('type' => 'focusareas'));

		return $view->loadTemplate();
	}
}
