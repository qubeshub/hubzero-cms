<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Models\Block;

use Components\Publications\Models\Block as Base;
use Components\Publications\Models\PubCloud;
use Components\Tags\Models\FocusArea;
use stdClass;

require_once dirname(dirname(__DIR__)) . DS . 'helpers' . DS . 'tags.php';
require_once dirname(dirname(__DIR__)) . DS . 'models' . DS . 'cloud.php';
require_once \Component::path('com_tags') . DS . 'models' . DS . 'focusarea.php';

/**
 * Tags block
 */
class Tags extends Base
{
	/**
	 * Block name
	 *
	 * @var  string
	 */
	protected $_name = 'tags';

	/**
	 * Parent block name
	 *
	 * @var  string
	 */
	protected $_parentname = 'tags';

	/**
	 * Default manifest
	 *
	 * @var  string
	 */
	protected $_manifest = null;

	/**
	 * Numeric block ID
	 *
	 * @var  integer
	 */
	protected $_blockId = 0;

	/**
	 * Display block content
	 *
	 * @param   object   $pub
	 * @param   object   $manifest
	 * @param   string   $viewname
	 * @param   integer  $blockId
	 * @return  string   HTML
	 */
	public function display($pub = null, $manifest = null, $viewname = 'edit', $blockId = 0)
	{
		// Set block manifest
		if ($this->_manifest === null)
		{
			$this->_manifest = $manifest ? $manifest : self::getManifest();
		}

		// Register blockId
		$this->_blockId	= $blockId;

		if ($viewname == 'curator')
		{
			// Output HTML
			$view = new \Hubzero\Component\View(
				array(
					'name'   => 'curation',
					'layout' => 'block'
				)
			);
		}
		else
		{
			$name = $viewname == 'freeze' ? 'freeze' : 'draft';

			// Output HTML
			$view = new \Hubzero\Plugin\View(
				array(
					'folder'  => 'projects',
					'element' => 'publications',
					'name'    => $name,
					'layout'  => 'wrapper'
				)
			);
		}

		// Get block status
		$status = $pub->curation('blocks', $blockId, 'status');
		$status->review = $pub->curation('blocks', $blockId, 'review');

		// Get block element model
		$elModel = new \Components\Publications\Models\BlockElements($this->_parent->_db);

		// Properties object
		$master 			= new stdClass;
		$master->block 		= $this->_name;
		$master->blockId 	= $this->_blockId;
		$master->params		= $this->_manifest->params;
		// $master->props		= $elModel->getActiveElement($status->elements, $status->review);

		$view->manifest     = $this->_manifest;
		$view->content      = self::buildContent($pub, $viewname, $status, $master);
		$view->pub          = $pub;
		$view->active       = $this->_name;
		$view->step         = $blockId;
		$view->showControls = 4;
		$view->status		= $status;
		$view->master		= $master;

		if ($this->getError())
		{
			$view->setError($this->getError());
		}
		return $view->loadTemplate();
	}

	/**
	 * Build panel content
	 *
	 * @param   object  $pub
	 * @param   string  $viewname
	 * @return  string  HTML
	 */
	public function buildContent($pub = null, $viewname = 'edit', $status, $master)
	{
		$name = $viewname == 'freeze' || $viewname == 'curator' ? 'freeze' : 'draft';

		// Output HTML
		$view = new \Hubzero\Plugin\View(
			array(
				'folder'  => 'projects',
				'element' => 'publications',
				'name'    => $name,
				'layout'  => 'tags'
			)
		);

		// Get block element model
		$elModel = new \Components\Publications\Models\BlockElements($this->_parent->_db);

		// Build each element
		$o = 1;
		$html = '';
		foreach ($this->_manifest->elements as $elementId => $element)
		{
			$html  .= $elModel->drawElement(
						$element->name, $elementId, $element, $master,
						$pub, $status, $viewname, $o
			);
			$o++;
		}
		$view->elHtml = $html;

		$view->pub      = $pub;
		$view->manifest = $this->_manifest;
		$view->step     = $this->_blockId;

		// Get categories
		$view->categories = $pub->category()->getContribCategories();

		// Get focus areas and tags
		$cloud = new PubCloud($pub->version->get('id'));
		$view->keywords = $cloud->render('string', array('type' => 'keywords', 'for' => 'ckeditor'));
		if ($this->getError())
		{
			$view->setError($this->getError());
		}
		return $view->loadTemplate();
	}

	/**
	 * Transfer data from one version to another
	 *
	 * @param   object   $manifest
	 * @param   object   $pub
	 * @param   object   $oldVersion
	 * @param   object   $newVersion
	 * @return  boolean
	 */
	public function transferData($manifest, $pub, $oldVersion, $newVersion)
	{
		$tagsHelper = new \Components\Publications\Helpers\Tags($this->_parent->_db);		
		$tags = $tagsHelper->get_tag_string($oldVersion->id);
		$tagsHelper->tag_object(User::get('id'), $newVersion->id, $tags, 1);
		
		return true;
	}

	/**
	 * Save block content
	 *
	 * @param   object   $manifest
	 * @param   integer  $blockId
	 * @param   object   $pub
	 * @param   integer  $actor
	 * @param   integer  $elementId
	 * @return  string   HTML
	 */
	public function save($manifest = null, $blockId = 0, $pub = null, $actor = 0, $elementId = 0)
	{
		// Set block manifest
		if ($this->_manifest === null)
		{
			$this->_manifest = $manifest ? $manifest : self::getManifest();
		}

		// Make sure changes are allowed
		if ($this->_parent->checkFreeze($this->_manifest->params, $pub))
		{
			return false;
		}

		// Load publication version
		$objP = new \Components\Publications\Tables\Publication($this->_parent->_db);

		if (!$objP->load($pub->id))
		{
			$this->setError(Lang::txt('PLG_PROJECTS_PUBLICATIONS_PUBLICATION_NOT_FOUND'));
			return false;
		}

		// Get keywords and focus area tags
		$keywords = preg_split('/,\s*/', $_POST['tags']);
		$fa_tags = FocusArea::fromObject($pub->master_type)->processTags();
		$tags = array_merge($keywords, $fa_tags);

		// Going to manually do this like in Resources by deleting and then re-adding.
		// NOTE: This changes the time stamp!  Refactor:  Modify com_tags/models/cloud::setTags
		// Should be able to use $rt->setTags, giving comma separated string of tags (don't need label!)
		$rt = new \Components\Tags\Models\Cloud($pub->version->id, 'publications');
		$this->_parent->_db->setQuery('DELETE FROM `#__tags_object` WHERE tbl = \'publications\' AND objectid = ' . $pub->version->id);
		$this->_parent->_db->execute();
		foreach ($tags as $tag) {
			$rt->add($tag, User::get('id'), 0, 1); // Ignore label
		}

		// Reflect the update in curation record
		$this->_parent->set('_update', 1);

		// Save category
		$cat = Request::getInt('pubtype', 0);
		if ($cat && $pub->_category->id != $cat)
		{
			$objP->category = $cat;
			$objP->store();
		}

		return true;
	}

	/**
	 * Check element status
	 *
	 * @return  object
	 */
	public function getElementStatus($element, $pub = null)
	{
		// Get block element model
		$elModel = new \Components\Publications\Models\BlockElements($this->_parent->_db);

		$status = $elModel->getStatus($element->type, $element, $pub);
		return $status;
	}

	/**
	 * Check completion status
	 *
	 * @param   object   $pub
	 * @param   object   $manifest
	 * @param   integer  $elementId
	 * @return  object
	 */
	public function getStatus($pub = null, $manifest = null, $elementId = null)
	{
		// Set block manifest
		if ($this->_manifest === null)
		{
			$this->_manifest = $manifest ? $manifest : self::getManifest();
		}

		// Start status
		$status = new \Components\Publications\Models\Status();

		// Return element status
		if ($elementId !== null && isset($this->_manifest->elements->$elementId))
		{
			return self::getElementStatus($this->_manifest->elements->$elementId, $pub);
		}

		// Check against manifested requirements
		if ($this->_manifest && $this->_manifest->elements)
		{
			// Check if requirements are satisfied for each attachment element
			$i          = 0;
			$success    = 0;
			$incomplete = 0;

			foreach ($this->_manifest->elements as $elementId => $element)
			{
				if (!isset($status->elements))
				{
					$status->elements = new stdClass();
				}
				$status->elements->$elementId = self::getElementStatus($element, $pub);
				if ($status->elements->$elementId->status >= 1)
				{
					$success++;
				}
				if ($status->elements->$elementId->status == 2)
				{
					$incomplete++;
				}
				$i++;
			}

			$success = $success == $i ? 1 : 0;
			$status->status = $success == 1 && $incomplete ? 2 : $success;
		}

		$cloud = new PubCloud($pub->version->get('id'));
		$count = count($cloud->render('list', array('type' => 'keywords')));
		$status->status = $manifest->params->required && $count == 0 ? 0 : $status->status;

		return $status;
	}

	/**
	 * Get default manifest for the block element
	 *
	 * @return  void
	 */
	public function getElementManifest()
	{
		$manifest = array(
			'name' 		=> 'focusareas',
			'type' 		=> 'tags',
			'label'		=> 'Categories',
			'about'		=> 'Choose from these recommended tags:',
			'adminTips'	=> '',
			'params' 	=> array(
				'type'	=> 'alignment',
				'typeParams' => array(
					'view' => 'alignment'
				)
			)
		);
		return json_decode(json_encode($manifest), false);
	}

	/**
	 * Get default manifest for the block
	 *
	 * @param   bool  $new
	 * @return  object
	 */
	public function getManifest($new = false)
	{
		// Load config from db
		$obj = new \Components\Publications\Tables\Block($this->_parent->_db);
		$manifest = $obj->getManifest($this->_name);

		// Fall back
		if (!$manifest)
		{
			$manifest = array(
				'name' 					=> 'tags',
				'label' 				=> 'Tags',
				'title' 				=> Lang::txt('COM_PUBLICATIONS_BLOCKS_TAGS_TITLE'),
				'draftHeading' 	=> Lang::txt('COM_PUBLICATIONS_BLOCKS_TAGS_DRAFT_HEADING'),
				'draftTagline'	=> Lang::txt('COM_PUBLICATIONS_BLOCKS_TAGS_DRAFT_TAGLINE'),
				'about'					=> Lang::txt('COM_PUBLICATIONS_BLOCKS_TAGS_ABOUT'),
				'adminTips'			=> '',
				'elements' 			=> array(
					1 => array(
						'name' 		=> 'focusareas',
						'type' 		=> 'tags',
						'label'		=> 'Categories',
						'about'		=> 'Choose from these recommended tags:',
						'adminTips'	=> '',
						'params' 	=> array(
							'type'	=> 'alignment',
							'typeParams' => array(
								'view' => 'alignment'
							)
						)
					)
				),
				'params'				=> array(
					'required' => 0, 
					'published_editing' => 0
				)
			);

			return json_decode(json_encode($manifest), false);
		}

		return $manifest;
	}
}
