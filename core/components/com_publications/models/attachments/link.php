<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Models\Attachment;

use Components\Publications\Models\Attachment as Base;
use stdClass;
use Request;
use Route;
use Lang;
use User;
use Date;

/**
 * Handles a link attachment
 */
class Link extends Base
{
	/**
	 * Attachment type name
	 *
	 * @var	 string
	 */
	protected $_name = 'link';

	/**
	 * Unique attachment properties
	 *
	 * @var  array
	 */
	protected $_connector  = array('path');

	/**
	 * Get configs
	 *
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   array    $blockParams
	 * @return  boolean
	 */
	public function getConfigs($element, $elementId, $pub, $blockParams)
	{
		$configs= new stdClass;
		$typeParams = $element->typeParams;

		// Allow changes in non-draft version?
		$configs->freeze = isset($blockParams->published_editing)
						&& $blockParams->published_editing == 0
						&& ($pub->state == 1 || $pub->state == 5)
						? 1 : 0;

		// Log path
		$configs->logPath = \Components\Publications\Helpers\Html::buildPubPath($pub->id, $pub->version_id, '', 'logs', 0);

		// replace current attachments?
		$configs->replace  	= Request::getInt('replace_current', 0, 'post');

		// Verify file type against allowed before attaching?
		$configs->check = isset($blockParams->verify_types) ? $blockParams->verify_types : 0;

		// Get default title
		$title = isset($element->title) ? str_replace('{pubtitle}', $pub->title, $element->title) : null;
		$configs->title = str_replace('{pubversion}', $pub->version_label, $title);

		// Fancy launcher?
		$configs->fancyLauncher = isset($typeParams->fancyLauncher)
			? $typeParams->fancyLauncher : 0;

		return $configs;
	}

	/**
	 * Draw list
	 *
	 * @param   array    $attachments
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   array    $blockParams
	 * @param   boolean  $authorized
	 * @return  string   HTML
	 */
	public function drawList($attachments, $element, $elementId, $pub, $blockParams, $authorized)
	{
		// Get configs
		$configs = $this->getConfigs($element->params, $elementId, $pub, $blockParams);

		$html = '';

		$url =  Route::url('index.php?option=com_publications&task=serve&id=' . $pub->id . '&v=' . $pub->version_number . '&el=' . $elementId);
		$url = preg_replace('/\/administrator/', '', $url);

		if ($attachments)
		{
			// Serve individually
			foreach ($attachments as $attach)
			{
				if ($attach->type != $this->_name)
				{
					continue;
				}
				$itemUrl = $url . '&a=' . $attach->id;
				$title   = $attach->title ? $attach->title : $configs->title;
				$title   = $title ? $title : $attach->path;
				$pop     = Lang::txt('View link') . ' ' . $title;

				$html .= '<li>';
				$html .= $authorized === 'administrator' ? '[' . $this->_name . '] ' : '';
				$html .= '<a href="' . $itemUrl . '" title="' . $pop . '" rel="noreferrer" target="_blank" class="link-type">' . $title . '</a>';
				$html .='</li>';
			}
		}

		return $html;
	}

	/**
	 * Draw launcher
	 *
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   object   $blockParams
	 * @param   array    $elements
	 * @param   boolean  $authorized
	 * @return  string   HTML
	 */
	public function drawLauncher($element, $elementId, $pub, $blockParams, $elements, $authorized)
	{
		// Get configs
		$configs = $this->getConfigs($element->params, $elementId, $pub, $blockParams);

		$attachments = $pub->_attachments;
		$attachments = isset($attachments['elements'][$elementId])
					 ? $attachments['elements'][$elementId] : null;

		// Sort out attachments for this element
		$attachments = $this->_parent->getElementAttachments(
			$elementId,
			$attachments,
			$this->_name
		);

		$disabled = 0;
		$pop      = null;

		if ($pub->isUnpublished() || $pub->isDown())
		{
			$pop = Lang::txt('COM_PUBLICATIONS_STATE_UNPUBLISHED_POP');
			$disabled = 1;
		}
		elseif (!$authorized)
		{
			$pop = $pub->access == 1
				? Lang::txt('COM_PUBLICATIONS_STATE_REGISTERED_POP')
				: Lang::txt('COM_PUBLICATIONS_STATE_RESTRICTED_POP');
			$disabled = 1;
		}
		elseif (!$attachments)
		{
			$disabled = 1;
			$pop = Lang::txt('COM_PUBLICATIONS_ERROR_CONTENT_UNAVAILABLE');
		}

		$pop = $pop ? '<p class="warning">' . $pop . '</p>' : '';

		$html = '';

		// Which role?
		$role = $element->params->role;

		$url = Route::url('index.php?option=com_publications&task=serve&id=' . $pub->id . '&v=' . $pub->version_number) . '?el=' . $elementId;

		// Primary button
		if ($role == 1)
		{
			$attach = $attachments[0];
			if (count($attachments) > 1)
			{
				// TBD
			}

			// One launcher for all files
			$label  = Lang::txt('View publication');
			$class  = 'btn btn-primary active icon-next';
			$class .= $disabled ? ' link_disabled' : '';
			$title  = $configs->title ? $configs->title : Lang::txt('View publication');
			$html   = \Components\Publications\Helpers\Html::primaryButton($class, $url, $label, null, $title, 'rel="external"', $disabled, $pop);
		}
		elseif ($role == 2 && $attachments)
		{
			$html .= '<ul>';
			$html .= self::drawList($attachments, $element, $elementId, $pub, $blockParams, $authorized);
			$html .= '</ul>';
		}

		return $html;
	}

	/**
	 * Transfer files from one version to another
	 *
	 * @param   object   $elementparams
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   object   $blockParams
	 * @param   array    $attachments
	 * @param   object   $oldVersion
	 * @param   object   $newVersion
	 * @return  void
	 */
	public function transferData($elementparams, $elementId, $pub, $blockParams, $attachments, $oldVersion, $newVersion)
	{
		// Loop through attachments
		foreach ($attachments as $att)
		{
			// Make new attachment record
			$pAttach = new \Components\Publications\Tables\Attachment($this->_parent->_db);
			if (!$pAttach->copyAttachment($att, $newVersion->id, $elementId, User::get('id')))
			{
				continue;
			}
		}
	}

	/**
	 * Serve
	 *
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   object   $blockParams
	 * @param   integer  $itemId
	 * @return  boolean
	 */
	public function serve($element, $elementId, $pub, $blockParams, $itemId = 0)
	{
		// Get configs
		$configs = $this->getConfigs($element->params, $elementId, $pub, $blockParams);

		$attachments = $pub->_attachments;
		$attachments = isset($attachments['elements'][$elementId]) ? $attachments['elements'][$elementId] : null;

		// Sort out attachments for this element
		$attachments = $this->_parent->getElementAttachments($elementId, $attachments, $this->_name);

		$path = null;
		if ($itemId)
		{
			foreach ($attachments as $attach)
			{
				if ($attach->id == $itemId)
				{
					$path = $attach->path;
				}
			}
		}
		else
		{
			$attach = $attachments[0];
			$path   = $attach->path;
		}

		if (!$path)
		{
			$this->setError(Lang::txt('Oops! Something went wrong. Cannot redirect to content.'));
			return false;
		}

		$v = "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";

		// Absolute or relative link?
		$where = preg_match($v, $path) ? $path : DS . trim($path, DS);
		$this->_parent->set('redirect', $where);
		return true;
	}

	/**
	 * Save incoming
	 *
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   object   $blockParams
	 * @param   array    $toAttach
	 * @return  boolean
	 */
	public function save($element, $elementId, $pub, $blockParams, $toAttach = array())
	{
		$toAttach = $toAttach ? $toAttach : Request::getArray('url', '', 'post');
		$titles   = Request::getArray('title', '', 'post');
		$desc     = Request::getArray('desc', '', 'post');

		// Incoming selections
		if (!is_array($toAttach))
		{
			$toAttach = array($toAttach);
		}

		// Get configs
		$configs = $this->getConfigs($element, $elementId, $pub, $blockParams);

		// Cannot make changes
		if ($configs->freeze)
		{
			return false;
		}

		// Nothing to change
		if (empty($toAttach) && !$configs->replace)
		{
			return false;
		}

		// Get existing attachments for the elemnt
		$attachments = $pub->_attachments;
		$attachments = isset($attachments['elements'][$elementId]) ? $attachments['elements'][$elementId] : null;

		// Sort out attachments for this element
		$attachments = $this->_parent->getElementAttachments($elementId, $attachments, $this->_name);

		// Counters
		$i = 0;
		$a = 0;

		// Default title for publication
		$defaultTitle = $pub->_curationModel->_manifest->params->default_title;

		// Attach/refresh each selected item
		foreach ($toAttach as $identifier)
		{
			if (!trim($identifier))
			{
				continue;
			}

			$a++;
			$ordering = $i + 1;

			$title = isset($titles[$i]) ? $titles[$i] : null;
			$desc  = isset($desc[$i]) ? $desc[$i] : null;

			if ($this->addAttachment($identifier, $title, $pub, $configs, User::get('id'), $elementId, $element, $ordering))
			{
				// Do we also set draft title and metadata from the link?
				if ($i == 0 && $title && $element->role == 1
					&& stripos($pub->title, $defaultTitle) !== false)
				{
					// Load publication version
					$row = new \Components\Publications\Tables\Version($this->_parent->_db);
					if (!$row->load($pub->version_id))
					{
						$this->setError(Lang::txt('PLG_PROJECTS_PUBLICATIONS_PUBLICATION_VERSION_NOT_FOUND'));
						return false;
					}

					$row->title       = $title;
					$description      = \Hubzero\Utility\Sanitize::clean($desc);
					$row->description = $description;
					$row->abstract    = \Hubzero\Utility\Str::truncate($description, 255);
					$row->store();
				}

				$i++;
			}
		}

		// Success
		if ($i > 0 && $i == $a)
		{
			$message = $this->get('_message') ? $this->get('_message') : Lang::txt('Selection successfully saved');
			$this->set('_message', $message);
		}

		return true;
	}

	/**
	 * Add/edit file attachment
	 *
	 * @param   integer  $id
	 * @param   string   $title
	 * @param   object   $pub
	 * @param   object   $configs
	 * @param   integer  $uid
	 * @param   integer  $elementId
	 * @param   object   $element
	 * @param   integer  $ordering
	 * @return  boolean
	 */
	public function addAttachment($path, $title, $pub, $configs, $uid, $elementId, $element, $ordering = 1)
	{
		// Need to check against allowed types
		$accept = isset($element->typeParams->accept) ? $element->typeParams->accept : null;
		if ($configs->check)
		{
			if (!$this->checkAllowed(array($path), $accept))
			{
				return false;
			}
		}

		$objPA = new \Components\Publications\Tables\Attachment($this->_parent->_db);
		if ($objPA->loadElementAttachment($pub->version_id, array('path' => $path),
			$elementId, $this->_name, $element->role))
		{
			// Link already attached
			$this->setError(Lang::txt('The link is already attached'));
			return true;
		}
		else
		{
			$objPA->publication_id         = $pub->id;
			$objPA->publication_version_id = $pub->version_id;
			$objPA->path                   = $path;
			$objPA->type                   = $this->_name;
			$objPA->created_by             = $uid;
			$objPA->created                = Date::toSql();
			$objPA->role                   = $element->role;
			$objPA->title                  = $title;

			// Reflect the update in curation record
			$this->_parent->set('_update', 1);
		}

		$objPA->element_id = $elementId;
		$objPA->ordering   = $ordering;

		if (!$objPA->store())
		{
			$this->setError(Lang::txt('There was a problem attaching the link'));
			return false;
		}

		return true;
	}

	/**
	 * Remove attachment
	 *
	 * @param   object   $row
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   array    $blockParams
	 * @return  boolean
	 */
	public function removeAttachment($row, $element, $elementId, $pub, $blockParams)
	{
		// Get configs
		$configs = $this->getConfigs($element, $elementId, $pub, $blockParams);

		// Cannot make changes
		if ($configs->freeze)
		{
			return false;
		}

		// Remove link
		if (!$this->getError())
		{
			$row->delete();
			$this->set('_message', Lang::txt('Item removed'));

			// Reflect the update in curation record
			$this->_parent->set('_update', 1);

			return true;
		}

		return false;
	}

	/**
	 * Update attachment properties
	 *
	 * @param   object   $row
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   array    $blockParams
	 * @return  boolean
	 */
	public function updateAttachment($row, $element, $elementId, $pub, $blockParams)
	{
		// Incoming
		$title = Request::getString('title', '');
		$thumb = Request::getInt('makedefault', 0);

		// Get configs
		$configs = $this->getConfigs($element, $elementId, $pub, $blockParams);

		// Cannot make changes
		if ($configs->freeze)
		{
			return false;
		}

		// Update label
		$row->title       = $title;
		$row->modified_by = User::get('id');
		$row->modified    = Date::toSql();

		// Update record
		if (!$row->store())
		{
			$this->setError(Lang::txt('Error updating item record'));
		}

		$this->set('_message', Lang::txt('Update successful'));

		return true;
	}

	/**
	 * Check completion status
	 *
	 * @param   object  $element
	 * @param   array   $attachments
	 * @return  object
	 */
	public function getStatus($element, $attachments)
	{
		$status = new \Components\Publications\Models\Status();

		// Get requirements to check against
		$max      = $element->max;
		$min      = $element->min;
		$role     = $element->role;
		$params   = $element->typeParams;
		$required = $element->required;
		$counter  = count($attachments ? $attachments : []);
		$allowed  = isset($params->accept) ? $params->accept : null;

		if (!$required)
		{
			$status->status = $counter ? 1 : 2;
			return $status;
		}

		// Check for correct number of attachments
		if ($min > 0 && $counter < $min)
		{
			if ($counter)
			{
				$status->setError(Lang::txt('Need at least ' . $min . ' attachment'));
			}
			else
			{
				// No files
				$status->status = 0;
				return $status;
			}
		}
		elseif ($max > 0 && $counter > $max)
		{
			$status->setError(Lang::txt('Maximum ' . $max . ' attachment(s) allowed'));
		}
		// Check allowed formats
		elseif (!self::checkAllowed($attachments, $allowed))
		{
			if ($counter && !empty($accept))
			{
				$error = Lang::txt('Error: unacceptable URL. URL should start with: ');
				foreach ($params->allowed_ext as $ext)
				{
					$error .= ' ' . $ext .',';
				}
				$error = substr($error, 0, strlen($error) - 1);
				$status->setError($error);
			}
		}

		$status->status = $status->getError() ? 0 : 1;

		return $status;
	}

	/**
	 * Check for allowed formats
	 *
	 * @param   array    $attachments
	 * @param   array    $formats
	 * @return  boolean
	 */
	public function checkAllowed($attachments, $formats = array())
	{
		if (empty($formats))
		{
			return true;
		}

		foreach ($attachments as $attach)
		{
			$path = isset($attach->path) ? $attach->path : $attach;
			foreach ($formats as $f)
			{
				if (stripos($path, $f) !== false)
				{
					return true;
				}
			}

		}

		return false;
	}

	/**
	 * Draw attachment
	 *
	 * @param   object  $data
	 * @param   object  $params
	 * @return  string
	 */
	public function drawAttachment($data, $params)
	{
		// Output HTML
		$view = new \Hubzero\Plugin\View(
			array(
				'folder'  => 'projects',
				'element' => 'publications',
				'name'    => 'attachments',
				'layout'  => $this->_name
			)
		);
		$view->data   = $data;
		$view->params = $params;

		if ($this->getError())
		{
			$view->setError($this->getError());
		}
		return $view->loadTemplate();
	}

	/**
	 * Build Data object
	 *
	 * @param   object   $att
	 * @param   object   $view
	 * @param   integer  $i
	 * @return  object
	 */
	public function buildDataObject($att, $view, $i = 1)
	{
		$data = new stdClass;
		$data->row      = $att;
		$data->ordering = $i;
		$data->editUrl  = $view->pub->link('editversion');
		$data->id       = $att->id;
		$data->props    = $view->master->block . '-' . $view->master->blockId . '-' . $view->elementId;
		$data->viewer   = $view->viewer;
		$data->version  = $view->pub->version_number;
		return $data;
	}

	/**
	 * Add to zip bundle
	 *
	 * @param   object   $zip
	 * @param   array    $attachments
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   object   $blockParams
	 * @param   object   $readme
	 * @param   string   $bundleDir
	 * @return  boolean
	 */
	public function addToBundle($zip, $attachments, $element, $elementId, $pub, $blockParams, &$readme, $bundleDir)
	{
		return false;
	}

	/**
	 * Draw list
	 *
	 * @param   array    $attachments
	 * @param   object   $element
	 * @param   integer  $elementId
	 * @param   object   $pub
	 * @param   object   $blockParams
	 * @param   boolean  $authorized
	 * @return  boolean
	 */
	public function drawPackageList($attachments, $element, $elementId, $pub, $blockParams, $authorized)
	{
		return false;
	}
}
