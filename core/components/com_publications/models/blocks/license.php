<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Models\Block;

use Components\Publications\Models\Block as Base;
use stdClass;

/**
 * License block
 */
class License extends Base
{
	/**
	 * Block name
	 *
	 * @var		string
	 */
	protected $_name = 'license';

	/**
	 * Parent block name
	 *
	 * @var		string
	 */
	protected $_parentname = null;

	/**
	 * Default manifest
	 *
	 * @var		string
	 */
	protected $_manifest = null;

	/**
	 * Numeric block ID
	 *
	 * @var		integer
	 */
	protected $_blockId = 0;

	/**
	 * Display block content
	 *
	 * @return  string  HTML
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
					'name'		=> 'curation',
					'layout'	=> 'block'
				)
			);
		}
		else
		{
			$name = $viewname == 'freeze' ? 'freeze' : 'draft';

			// Output HTML
			$view = new \Hubzero\Plugin\View(
				array(
					'folder'	=> 'projects',
					'element'	=> 'publications',
					'name'		=> $name,
					'layout'	=> 'wrapper'
				)
			);
		}

		$view->manifest 	= $this->_manifest;
		$view->content 		= self::buildContent($pub, $viewname);
		$view->pub			= $pub;
		$view->active		= $this->_name;
		$view->step			= $blockId;
		$view->showControls = 4;

		if ($this->getError())
		{
			$view->setError($this->getError());
		}
		return $view->loadTemplate();
	}

	/**
	 * Build panel content
	 *
	 * @return  string  HTML
	 */
	public function buildContent($pub = null, $viewname = 'edit')
	{
		$name = $viewname == 'freeze' || $viewname == 'curator' ? 'freeze' : 'draft';

		// Get selector styles
		\Hubzero\Document\Assets::addPluginStylesheet('projects', 'publications', 'selector');

		// Output HTML
		$view = new \Hubzero\Plugin\View(
			array(
				'folder'	=> 'projects',
				'element'	=> 'publications',
				'name'		=> $name,
				'layout'	=> 'license'
			)
		);

		$view->pub		= $pub;
		$view->manifest = $this->_manifest;
		$view->step		= $this->_blockId;

		$objL = new \Components\Publications\Tables\License($this->_parent->_db);

		// Get selected license
		$view->license = $objL->getPubLicense($pub->version_id);

		$view->selections = $objL->getBlockLicenses($this->_manifest, $view->license);

		// Pre-select single available license
		if (!$view->license && count($view->selections) == 1)
		{
			$view->license = new \Components\Publications\Tables\License($this->_parent->_db);
			$view->license->load($view->selections[0]->id);
		}

		if ($this->getError())
		{
			$view->setError($this->getError());
		}
		return $view->loadTemplate();
	}

	/**
	 * Save block content
	 *
	 * @return  string  HTML
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
		$row = new \Components\Publications\Tables\Version($this->_parent->_db);

		if (!$row->load($pub->version_id))
		{
			$this->setError(Lang::txt('PLG_PROJECTS_PUBLICATIONS_PUBLICATION_VERSION_NOT_FOUND'));
			return false;
		}

		$originalType = $row->license_type;
		$originalText = $row->license_text;

		// Load license class
		$objL = new \Components\Publications\Tables\License($this->_parent->_db);

		// Incoming - license screen agreements
		$license = Request::getInt('license', 0, 'post');
		$text 	 = \Hubzero\Utility\Sanitize::clean(Request::getString('license_text', '', 'post'));
		$agree 	 = Request::getInt('agree', 0, 'post');
		$custom  = Request::getArray('substitute', array());

		if ($license)
		{
			if (!$objL->load($license))
			{
				$this->setError(Lang::txt('There was a problem saving license selection'));
				return false;
			}

			if ($objL->agreement == 1 && !$agree)
			{
				$this->setError(Lang::txt('PLG_PROJECTS_PUBLICATIONS_LICENSE_NEED_AGREEMENT'));
				return false;
			}
			elseif ($objL->customizable == 1 && !$text)
			{
				$this->setError(Lang::txt('PLG_PROJECTS_PUBLICATIONS_LICENSE_NEED_TEXT'));
				return false;
			}

			$row->license_type = $license;
			$text = preg_replace("/\r/", '', $text);
			$row->license_text = $text;

			// Pre-defined license text
			if ($objL->text && $objL->customizable == 0)
			{
				$row->license_text = $objL->text;

				// Do we have template items to replace?
				preg_match_all('/\[([^\]]*)\]/', $objL->text, $substitutes);
				if (count($substitutes) > 1)
				{
					foreach ($substitutes[1] as $sub)
					{
						if (!isset($custom[$sub]) || !$custom[$sub])
						{
							$this->setError(Lang::txt('PLG_PROJECTS_PUBLICATIONS_LICENSE_NEED_CUSTOM'));
							return false;
						}
						else
						{
							$row->license_text = preg_replace('/\[' . $sub . '\]/', trim($custom[$sub]), $row->license_text);
						}
					}
				}
			}

			$row->store();

			// Save agreement
			$row->saveParam($pub->version_id, 'licenseagreement', 1);

			// Save custom fields in version params
			foreach ($custom as $label => $value)
			{
				$row->saveParam($pub->version_id, 'licensecustom' . strtolower($label), trim($value));
			}

			if ($license != $originalType || $text != $originalText)
			{
				$this->_parent->set('_update', 1);
			}

			// Check agreements
			return true;
		}

		// Incoming - selector screen
		$selections = Request::getString('selecteditems', '');
		$toAttach = explode(',', $selections);

		// Allows to select nothing if optional
		if ($selections == '')
		{
			$row->saveParam($pub->version_id, 'licenseagreement', 0);
			$row->license_type = '';
			$row->store();
		}

		$i = 0;
		foreach ($toAttach as $license)
		{
			if (!trim($license))
			{
				continue;
			}

			// Make sure license exists
			if ($objL->load($license))
			{
				$row->license_type = $license;
				$i++;
				$row->store();

				// Clear agreement if license is changed
				if ($originalType != $license)
				{
					// Save agreement
					$row->saveParam($pub->version_id, 'licenseagreement', 0);
					$this->_parent->set('_update', 1);
				}

				// Only one choice
				break;
			}
		}

		if ($i)
		{
			$this->set('_message', Lang::txt('License selection saved'));
			return true;
		}
		else
		{
			$this->setError(Lang::txt('There was a problem saving license selection'));
			return false;
		}
	}

	/**
	 * Check completion status
	 *
	 * @return  object
	 */
	public function getStatus($pub = null, $manifest = null, $elementId = null)
	{
		$id = $pub->_curationModel->getBlockId('license');
		$blocks = $pub->_curationModel->getBlockSchema();
		$required = 1;

		foreach ($blocks as $block)
		{
			if ($block->name == 'license')
			{
				$required = $block->params->required;
			}
		}

		// Start status
		$status = new \Components\Publications\Models\Status();

		$status->status = 1;

		// Load license class
		$objL = new \Components\Publications\Tables\License($this->_parent->_db);

		if ($pub->license_type && $objL->load($pub->license_type) && $required == 1)
		{
			$agreement = $pub->params->get('licenseagreement');

			if (!$agreement)
			{
				// Get version params and extract agreement
				$versionParams = array_filter(explode(PHP_EOL, $pub->version->params ? $pub->version->params : ''));
				$versionParams = array_reduce($versionParams, function($carry, $item){
					$keyValueSplit = explode('=', $item);
					$key = trim(array_shift($keyValueSplit));
					$value = trim(array_shift($keyValueSplit));
					if ($key && $value)
					{
						$carry[$key] = $value;
						return $carry;
					}
				});
				$agreement = isset($versionParams['licenseagreement']) ? $versionParams['licenseagreement'] : 0;
			}

			// Missing agreement?
			if ($objL->agreement == 1 && !$agreement && $required)
			{
				$status->setError(Lang::txt('PLG_PROJECTS_PUBLICATIONS_LICENSE_NEED_AGREEMENT'));
				$status->status = 0;
			}

			if ($objL->customizable == 1
				&& $objL->text && !$pub->license_text)
			{
				$status->setError(Lang::txt('PLG_PROJECTS_PUBLICATIONS_LICENSE_NEED_TEXT'));
				$status->status = 0;
			}

			if ($pub->license_text)
			{
				preg_replace('/\[([^]]+)\]/', ' ', $pub->license_text, -1, $bingo);
				if ($bingo)
				{
					$status->setError(Lang::txt('Default values need to be substituted'));
					$status->status = 0;
				}
			}
		}
		elseif ($required == 0)
		{
			$status->status = 1;
		}
		else
		{
			$status->status = 0;
		}

		return $status;
	}

	/**
	 * Get default manifest for the block
	 *
	 * @return  void
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
				'name' 			=> 'license',
				'label' 		=> 'License',
				'title' 		=> 'Publication License',
				'draftHeading' 	=> 'Choose License',
				'draftTagline'	=> 'Define copyright and terms of use:',
				'about'			=> 'It is important that you provide a license for your publication stating your copyright and terms of use of your content.',
				'adminTips'		=> '',
				'elements' 		=> array(),
				'params'		=> array('required' => 1, 'published_editing' => 0, 'include' => array(), 'exclude' => array())
			);

			return json_decode(json_encode($manifest), false);
		}

		return $manifest;
	}
}
