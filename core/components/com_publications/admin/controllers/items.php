<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Publications\Tables;
use Components\Publications\Helpers;
use Components\Publications\Models;
use Exception;
use Request;
use Config;
use Route;
use Lang;
use User;
use App;

/**
 * Manage publications
 */
class Items extends AdminController
{
	/**
	 * Executes a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->_task = strtolower(Request::getCmd('task', ''));
		parent::execute();
	}

	/**
	 * Lists publications
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Incoming
		$this->view->filters = array();
		$this->view->filters['ignore_access'] = true;
		$this->view->filters['limit']    = Request::getState(
			$this->_option . '.publications.limit',
			'limit',
			Config::get('list_limit'),
			'int'
		);
		$this->view->filters['start']    = Request::getState(
			$this->_option . '.publications.limitstart',
			'limitstart',
			0,
			'int'
		);
		$this->view->filters['search']   = urldecode(trim(Request::getState(
			$this->_option . '.publications.search',
			'search',
			''
		)));
		$this->view->filters['sortby']     = trim(Request::getState(
			$this->_option . '.publications.sortby',
			'filter_order',
			'created'
			));
		$this->view->filters['sortdir'] = trim(Request::getState(
			$this->_option . '.publications.sortdir',
			'filter_order_Dir',
			'DESC'
		));
		$this->view->filters['status']   = trim(Request::getState(
			$this->_option . '.publications.status',
			'status',
			'all'
		));
		$this->view->filters['dev'] = 1;
		$this->view->filters['category']  = trim(Request::getState(
			$this->_option . '.publications.category',
			'category',
			''
		));

		// Instantiate a publication object
		$this->view->model = new Models\Publication($this->database);

		// Get record count
		$this->view->total = $this->view->model->entries('count', $this->view->filters, true);

		// Get publications
		$this->view->rows = $this->view->model->entries('list', $this->view->filters, true);

		$this->view->config = $this->config;

		// Get <select> of types
		// Get types
		$rt = new Tables\Category($this->database);
		$this->view->categories = $rt->getContribCategories();

		// Set any errors
		if ($this->getError())
		{
			$this->view->setError($this->getError());
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Edit form for a publication
	 *
	 * @param   integer  $isnew  Flag for editing (0) or creating new (1)
	 * @return  void
	 */
	public function editTask($isnew=0)
	{
		Request::setVar('hidemainmenu', 1);

		$this->view->isnew = $isnew;

		// Get the publications component config
		$this->view->config = $this->config;

		// Incoming publication ID
		$id = Request::getArray('id', array(0));
		if (is_array($id))
		{
			$id = $id[0];
		}

		// Is this a new publication? TBD
		if (!$id)
		{
			$this->view->isnew = 1;
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_PUBLICATIONS_ERROR_CREATE_FRONT_END'),
				'notice'
			);
			return;
		}

		// Incoming version
		$version = Request::getString('version', 'default');

		// Grab some filters for returning to place after editing
		$this->view->return = array();
		$this->view->return['category'] = Request::getString('category', '');
		$this->view->return['sortby']   = Request::getString('sortby', '');
		$this->view->return['status']   = Request::getString('status', '');

		// Instantiate a publication object
		$this->view->model = new Models\Publication($id, $version);

		// If publication not found, raise error
		if (!$this->view->model->exists())
		{
			throw new Exception(Lang::txt('COM_PUBLICATIONS_NOT_FOUND'), 404);
			return;
		}

		// Fail if checked out not by 'me'
		if ($this->view->model->get('checked_out')
		 && $this->view->model->get('checked_out') <> User::get('id'))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_PUBLICATIONS_ERROR_CHECKED_OUT'),
				'notice'
			);
			return;
		}

		// Editing existing
		$this->view->model->publication->checkout(User::get('id'));

		$this->view->model->setCuration();

		// Get licenses
		$this->view->licenses = $this->view->model->table('License')->getLicenses();

		// Get groups
		$filters = array(
			'authorized' => 'admin',
			'fields'     => array('cn', 'description', 'published', 'gidNumber', 'type'),
			'type'       => array(1, 3),
			'sortby'     => 'description'
		);
		$this->view->groups = \Hubzero\User\Group::find($filters);

		// Set any errors
		if ($this->getError())
		{
			$this->view->setError($this->getError());
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Edit content item
	 *
	 * @return  void
	 */
	public function editcontentTask()
	{
		// Incoming
		$id = Request::getInt('id', 0);
		$el = Request::getInt('el', 0);
		$v  = Request::getString('v', 'default');

		// Get publication information
		$this->view->pub = new Models\Publication($id, $v);

		// If publication not found, raise error
		if (!$this->view->pub->exists())
		{
			throw new Exception(Lang::txt('COM_PUBLICATIONS_NOT_FOUND'), 404);
			return;
		}

		$this->view->config = $this->config;
		$this->view->typeParams = $this->view->pub->masterType()->_params;

		// Get attachments
		$this->view->pub->attachments();

		// Set curation
		$this->view->pub->setCuration();

		if (!$el)
		{
			$this->setError('No Element Id');
		}
		else
		{
			$this->view->elementId = $el;
			$this->view->element   = $this->view->pub->_curationModel->getElementManifest($el, 'content');
		}

		// Set any errors
		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Save content item details
	 *
	 * @return  void
	 */
	public function savecontentTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		// Incoming
		$el      = Request::getInt('el', 0);
		$id      = Request::getInt('id', 0);
		$version = Request::getString('version', '');
		$params  = Request::getArray('params', array());
		$attachments = Request::getArray('attachments', array());

		// Load publication model
		$this->model  = new Models\Publication($id, $version);

		if (!$this->model->exists())
		{
			throw new Exception(Lang::txt('COM_PUBLICATIONS_NOT_FOUND'), 404);
			return;
		}

		// Set curation
		$this->model->setCuration();

		// Save attachments
		if (!empty($attachments))
		{
			foreach ($attachments as $attachId => $attach)
			{
				$pContent = new Tables\Attachment($this->database);
				if ($pContent->load($attachId))
				{
					$pContent->title = $attach['title'];
					$pContent->store();
				}
			}
		}

		// Set redirect URL
		$url = Route::url('index.php?option=' . $this->_option . '&controller='
			. $this->_controller . '&task=edit' . '&id[]=' . $id . '&version=' . $version, false);

		// Redirect back to publication
		App::redirect(
			$url,
			Lang::txt('COM_PUBLICATIONS_SUCCESS_SAVED_CONTENT')
		);
	}

	/**
	 * Add author form
	 *
	 * @return  void
	 */
	public function addauthorTask()
	{
		$this->editauthorTask();
	}

	/**
	 * Edit author name and details
	 *
	 * @return  void
	 */
	public function editauthorTask()
	{
		// Incoming
		$author = Request::getInt('author', 0);

		$this->view->setLayout('editauthor');

		$this->view->author = new Tables\Author($this->database);
		if ($this->_task == 'editauthor' && !$this->view->author->load($author))
		{
			throw new Exception(Lang::txt('COM_PUBLICATIONS_ERROR_NO_AUTHOR_RECORD'), 404);
			return;
		}
		
		if (!empty($this->view->author->user_id))
		{
			$user = \Components\Members\Models\Member::oneOrNew($this->view->author->user_id);
			$this->view->author->orcid = $user->get('orcid');
			$this->view->author->organization = $user->get('organization');
		}

		// Version ID
		$vid = Request::getInt('vid', $this->view->author->publication_version_id);

		$this->view->row = new Tables\Version($this->database);
		$this->view->pub = new Tables\Publication($this->database);

		// Load version
		if (!$this->view->row->load($vid))
		{
			throw new Exception(Lang::txt('COM_PUBLICATIONS_NOT_FOUND'), 404);
			return;
		}

		// Load publication
		$pid = Request::getInt('pid', $this->view->row->publication_id);
		if (!$this->view->pub->load($pid))
		{
			throw new Exception(Lang::txt('COM_PUBLICATIONS_NOT_FOUND'), 404);
			return;
		}

		// Instantiate project owner
		$objO = new \Components\Projects\Tables\Owner($this->database);
		$filters = array();
		$filters['limit']   = 0;
		$filters['start']   = 0;
		$filters['sortby']  = 'name';
		$filters['sortdir'] = 'ASC';
		$filters['status']  = 'active';

		// Get all active team members
		$this->view->team = $objO->getOwners($this->view->pub->project_id, $filters);

		// Set any errors
		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Delete author
	 *
	 * @return  void
	 */
	public function deleteauthorTask()
	{
		// Incoming
		$aid = Request::getInt('aid', 0);

		$pAuthor = new Tables\Author($this->database);
		if (!$pAuthor->load($aid))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_PUBLICATIONS_ERROR_LOAD_AUTHOR'),
				'error'
			);
			return;
		}

		$url = 'index.php?option=' . $this->_option . '&controller=' . $this->_controller;

		// Instantiate Version
		$row = new Tables\Version($this->database);
		if ($row->load($pAuthor->publication_version_id))
		{
			$url .= '&task=edit' . '&id[]=' . $row->publication_id . '&version=' . $row->version_number;
		}

		if (!$pAuthor->delete())
		{
			App::redirect(
				Route::url($url, false),
				Lang::txt('COM_PUBLICATIONS_ERROR_FAILED_TO_DELETE_AUTHOR'),
				'error'
			);
			return;
		}

		// Redirect back to publication
		App::redirect(
			Route::url($url, false),
			Lang::txt('COM_PUBLICATIONS_SUCCESS_DELETE_AUTHOR')
		);
		return;
	}

	/**
	 * Save author order
	 *
	 * @return  void
	 */
	public function saveauthororderTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		// Incoming
		$id       = Request::getInt('id', 0);
		$version  = Request::getString('version', '');
		$neworder = Request::getString('list', '');

		// Set redirect URL
		$url = Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=edit' . '&id[]=' . $id . '&version=' . $version, false);

		if (!$neworder)
		{
			// Nothing to save
			App::redirect(
				$url
			);
			return;
		}

		// Load publication model
		$model = new Models\Publication($id, $version);

		if (!$model->exists())
		{
			App::redirect(
				$url,
				Lang::txt('COM_PUBLICATIONS_NOT_FOUND'),
				'error'
			);
			return;
		}

		// Load publication project
		$model->project();

		// Get language file
		Lang::load('plg_projects_publications');

		// Save via block
		$blocksModel = new Models\Blocks($this->database);
		$block = $blocksModel->loadBlock('authors');

		$block->reorder(null, 0, $model, User::get('id'));
		if ($block->getError())
		{
			App::redirect(
				$url,
				$block->getError(),
				'error'
			);
			return;
		}
		else
		{
			// Update DOI in case changes
			if ($model->version->doi)
			{
				// Get DOI service
				$doiService = new Models\Doi($model);

				// Get updated authors
				$pAuthor = new Tables\Author($this->database);
				$authors = $pAuthor->getAuthors($model->version->id);
				$doiService->set('authors', $authors);

				// Update DOI
				if (preg_match("/" . $doiService->_configs->shoulder . "/", $model->version->doi))
				{
					$doiService->update($model->version->doi, true);

					if ($doiService->getError())
					{
						$this->setError($doiService->getError());
					}
				}
			}

			// Redirect back to publication
			App::redirect(
				$url,
				Lang::txt('COM_PUBLICATIONS_SUCCESS_SAVED_AUTHOR')
			);
			return;
		}
	}

	/**
	 * Save author name and details
	 *
	 * @return  void
	 */
	public function saveauthorTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		// Incoming
		$author  = Request::getInt('author', 0);
		$id      = Request::getInt('id', 0);
		$version = Request::getString('version', '');

		$firstName = Request::getString('firstName', '', 'post');
		$lastName  = Request::getString('lastName', '', 'post');
		$org       = Request::getString('organization', '', 'post');

		// Set redirect URL
		$url = Route::url('index.php?option=' . $this->_option . '&controller='
			. $this->_controller . '&task=edit' . '&id[]=' . $id . '&version=' . $version, false);

		// Load publication model
		$model  = new Models\Publication($id, $version);

		if (!$model->exists())
		{
			App::redirect(
				$url,
				Lang::txt('COM_PUBLICATIONS_NOT_FOUND'),
				'error'
			);
			return;
		}

		// Load publication project
		$model->project();

		// Get language file
		Lang::load('plg_projects_publications');

		// Save via block
		$blocksModel = new Models\Blocks($this->database);
		$block = $blocksModel->loadBlock('authors');

		if ($author)
		{
			$block->saveItem(null, 0, $model, User::get('id'), 0, $author);
		}
		else
		{
			$block->addItem(null, 0, $model, User::get('id'));
		}

		if ($block->getError())
		{
			App::redirect(
				$url,
				$block->getError(),
				'error'
			);
			return;
		}
		else
		{
			// Update DOI in case changes
			if ($model->version->doi)
			{
				// Get DOI service
				$doiService = new Models\Doi($model);

				// Get updated authors
				$authors = $model->table('Author')->getAuthors($model->version->id);
				$doiService->set('authors', $authors);

				// Update DOI
				if (preg_match("/" . $doiService->_configs->shoulder . "/", $model->version->get('doi')))
				{
					$doiService->update($model->version->get('doi'), true);

					if ($doiService->getError())
					{
						$this->setError($doiService->getError());
					}
				}
			}

			// Redirect back to publication
			App::redirect(
				$url,
				Lang::txt('COM_PUBLICATIONS_SUCCESS_SAVED_AUTHOR')
			);
			return;
		}
	}

	/**
	 * Save a publication and fall through to edit view
	 *
	 * @return  void
	 */
	public function applyTask()
	{
		$this->saveTask(true);
	}

	/**
	 * Saves a publication
	 * Redirects to main listing
	 *
	 * @param   boolean  $redirect
	 * @return  void
	 */
	public function saveTask($redirect = false)
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$id           = Request::getInt('id', 0);
		$action       = Request::getString('admin_action', '');
		$published_up = Request::getString('published_up', '');
		$version      = Request::getString('version', 'default');

		// Is this a new publication? Cannot create via back-end
		$isnew = $id ? 0 : 1;
		if (!$id)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_PUBLICATIONS_ERROR_LOAD_PUBLICATION'),
				'error'
			);
			return;
		}

		// Load publication model
		$this->model = new Models\Publication($id, $version);

		if (!$this->model->exists())
		{
			throw new Exception(Lang::txt('COM_PUBLICATIONS_NOT_FOUND'), 404);
		}

		// Checkin resource
		$this->model->publication->checkin();

		// Set redirect URL
		$url = Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=edit' . '&id[]=' . $id . '&version=' . $version, false);

		$authors = $this->model->authors();
		$project = $this->model->project();

		$this->model->setCuration();
		$requireDoi = isset($this->model->_curationModel->_manifest->params->require_doi)
					? $this->model->_curationModel->_manifest->params->require_doi : 0;

		// Incoming updates
		$title          = trim(Request::getString('title', '', 'post'));
		$title          = htmlspecialchars($title);
		$abstract       = trim(Request::getString('abstract', '', 'post'));
		$description    = trim(Request::getString('description', '', 'post'));
		$release_notes  = stripslashes(trim(Request::getString('release_notes', '', 'post')));
		$group_owner    = Request::getInt('group_owner', 0, 'post');
		$published_up   = trim(Request::getString('published_up', '', 'post'));
		$published_down = trim(Request::getString('published_down', '', 'post'));
		$state          = Request::getInt('state', 0);
		$featured       = Request::getInt('featured', 0);
		$metadata       = '';
		$activity       = '';

		// Save publication record
		$this->model->publication->alias    = trim(Request::getString('alias', '', 'post'));
		$this->model->publication->category = trim(Request::getInt('category', 0, 'post'));
		$this->model->publication->access   = Request::getInt('access', 0, 'post');
		$this->model->publication->group_owner = $group_owner;
		$this->model->publication->featured = $featured;
		/*if (!$project->get('owned_by_group'))
		{
			$this->model->publication->group_owner = $group_owner;
		}*/
		$this->model->publication->store();

		// Get metadata
		if (isset($_POST['nbtag']))
		{
			$category = $this->model->category();

			$fields = array();
			if (trim($category->customFields) != '')
			{
				$fs = explode("\n", trim($category->customFields));
				foreach ($fs as $f)
				{
					$fields[] = explode('=', $f);
				}
			}

			$nbtag = Request::getArray('nbtag', array());
			foreach ($nbtag as $tagname => $tagcontent)
			{
				$tagcontent = trim(stripslashes($tagcontent));
				if ($tagcontent != '')
				{
					$metadata .= "\n".'<nb:' . $tagname . '>' . $tagcontent . '</nb:' . $tagname . '>' . "\n";
				}
				else
				{
					foreach ($fields as $f)
					{
						if ($f[0] == $tagname && end($f) == 1)
						{
							Notify::error(Lang::txt('COM_PUBLICATIONS_REQUIRED_FIELD_CHECK', $f[1]));
							return $this->cancelTask();
						}
					}
				}
			}
		}

		$db = \App::get('db');
		$db->setQuery("select params
		               from #__extensions
		               where folder = 'projects' and element = 'publications'");
		$result = $db->loadRow();
		$params = isset($result[0]) ? json_decode($result[0]) : null;

		if (!!$params && isset($params->new_pubs) && !$params->new_pubs)
		{
			$abstract = htmlspecialchars(\Hubzero\Utility\Sanitize::clean($abstract));
		}

		// Save incoming
		$this->model->version->title        = $title;
		$this->model->version->abstract     = \Hubzero\Utility\Str::truncate($abstract, 64000, ["html" => true]);
		$this->model->version->description  = $description;
		$this->model->version->metadata     = $metadata;
		$this->model->version->release_notes= $release_notes;
		$this->model->version->license_text = trim(Request::getString('license_text', '', 'post'));
		$this->model->version->license_type = Request::getInt('license_type', 0, 'post');
		$this->model->version->access       = Request::getInt('access', 0, 'post');
		if ($version_label = Request::getString('version_label', null, 'post'))
		{
			$this->model->version->version_label = $version_label;
		}
		$this->model->version->downloadDisabled = Request::getBool('disabledownloadlink', false, 'post');

		// Get DOI service
		$doiService = new Models\Doi($this->model);

		// DOI manually entered?
		$doi = trim(Request::getString('doi', '', 'post'));
		if ($doi
		 && (!$this->model->version->doi || !preg_match("/" . $doiService->_configs->shoulder . "/", $this->model->version->doi)))
		{
			$this->model->version->doi = $doi;
		}

		$this->model->version->published_up   = $published_up
							? Date::of($published_up, Config::get('offset'))->toSql()
							: null;
		$this->model->version->published_down = $published_down && trim($published_down) != 'Never'
							? Date::of($published_down, Config::get('offset'))->toSql()
							: null;

		// Determine action (if status is flipped)
		if ($this->model->version->state != $state)
		{
			switch ($state)
			{
				case 1:
					$action = $this->model->version->state == 0 ? 'republish' : 'publish';
					break;
				case 0:
					$action = 'unpublish';
					break;
				case 3:
				case 4:
					$action = 'revert';
					break;
				case 7:
					$action = 'wip';
					break;
			}

			$this->model->version->state = $state;
		}
		else
		{
			foreach ($authors as $author)
			{
				$putCode = $author->orcid_work_put_code;
				
				if (!empty($putCode))
				{
					// Update the publication information in author's ORCID record
					if (!empty($author->user_id))
					{
						$profile = \Components\Members\Models\Member::oneOrFail($author->user_id);
					
						if ($profile)
						{
							$orcidID = $profile->get('orcid');
							$accessToken = $profile->get('access_token');
						}
					}
					else
					{
						$collaborator = $this->model->getCollaboratorByName($author->invited_name);
						
						if (!empty($collaborator))
						{
							$orcidID = $collaborator->orcid;
							$accessToken = $collaborator->access_token;
						}
					}
					
					if (!empty($orcidID) && !empty($accessToken))
					{
						$this->model->updateWorkInORCID($orcidID, $accessToken, $putCode);
					}
				}
				else
				{
					// Add publication to author's ORCID record
					if ($this->model->version->state == 1)
					{
						if (!empty($author->user_id))
						{
							$profile = \Components\Members\Models\Member::oneOrFail($author->user_id);
						
							if ($profile)
							{
								$orcidID = $profile->get('orcid');
								$accessToken = $profile->get('access_token');
							}
						}
						else
						{
							$collaborator = $this->model->getCollaboratorByName($author->invited_name);
							
							if (!empty($collaborator))
							{
								$orcidID = $collaborator->orcid;
								$accessToken = $collaborator->access_token;
							}
						}
						
						if (!empty($orcidID) && !empty($accessToken))
						{
							$putCode = $this->model->addPubToORCID($orcidID, $accessToken);
						
							if (!empty($putCode))
							{
								$authorTbl = new Tables\Author($this->database);
								$authorTbl->saveORCIDPutCode($author->id, $putCode);
							}
						}
					}
				}
			}
		}
		
		// Incoming tags
		$tags = Request::getString('tags', '', 'post');

		// Save the tags
		$rt = new Helpers\Tags($this->database);
		$rt->tag_object(User::get('id'), $this->model->version->id, $tags, 1, true);

		// Update DOI with latest information
		if ($this->model->version->doi && !$action)
		{
			if (preg_match("/" . $doiService->_configs->shoulder . "/", $this->model->version->doi))
			{
				$doiService->set('authors', $authors);
				
				$fosTag = $this->model->getFOSTag();
				if (!empty($fosTag))
				{
					$doiService->set('fosTag', $fosTag);
				}
				
				$doiService->update($this->model->version->doi, true);

				if ($doiService->getError())
				{
					$this->setError($doiService->getError());
				}
			}
		}

		// Email config
		$pubtitle = \Hubzero\Utility\Str::truncate($this->model->version->title, 100);
		$subject  = Lang::txt('Version') . ' ' . $this->model->version->version_label . ' '
					. Lang::txt('COM_PUBLICATIONS_OF') . ' '
					. strtolower(Lang::txt('COM_PUBLICATIONS_PUBLICATION'))
					. ' "' . $pubtitle . '" ';
		$sendmail = 0;
		$message  = rtrim(\Hubzero\Utility\Sanitize::clean(Request::getString('message', '')));
		$output   = Lang::txt('COM_PUBLICATIONS_SUCCESS_SAVED_ITEM');

		// Admin actions
		if ($action)
		{
			$output = '';

			switch ($action)
			{
				case 'publish':
				case 'republish':
					// Unset the published_down timestamp if publishing
					$this->model->version->published_down = null;

					$activity = $action == 'publish'
						? Lang::txt('COM_PUBLICATIONS_ACTIVITY_ADMIN_PUBLISHED')
						: Lang::txt('COM_PUBLICATIONS_ACTIVITY_ADMIN_REPUBLISHED');
					$subject .= $action == 'publish'
						? Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_PUBLISHED')
						: Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_REPUBLISHED');

					$this->model->version->state = 1;

					// Is service enabled? - Issue/update a DOI
					if ($doiService->on())
					{
						if ($this->model->version->doi
							&& preg_match("/" . $doiService->_configs->shoulder . "/", $this->model->version->doi))
						{
							$doiService->set('authors', $authors);
							$doiService->update($this->model->version->doi, true);

							if ($doiService->getError())
							{
								$this->setError($doiService->getError());
								break;
							}

							// Register URL and DOI name for DataCite DOI service
							$doiService->register(false, true, $this->model->version->doi);

							if ($doiService->getError())
							{
								$this->setError($doiService->getError());
							}
						}
						elseif ($requireDoi)
						{
							// Register metadata
							$doi = $doiService->register(true, false, null, true);

							if (!$doi)
							{
								App::redirect(
									$url, Lang::txt('COM_PUBLICATIONS_ERROR_DOI')
									. ' ' . $doiService->getError(), 'error');
								return;
							}
							else
							{
								$this->model->version->doi = $doi;
							}

							// Register the DOI name and URL to complete the DataCite DOI registration.
							$doiService->register(false, true, $doi);

							if ($doiService->getError())
							{
								$this->setError($doiService->getError());
							}
						}
					}

					// Save date accepted
					if ($action == 'publish')
					{
						$this->model->version->accepted = Date::toSql();

						$this->model->version->published_up  = $published_up
											? Date::of($published_up, Config::get('offset'))->toSql()
											: Date::toSql();

						// Get and save manifest and its version
						$versionNumber = $this->model->_curationModel->checkCurationVersion();
						$this->model->version->set('curation', json_encode($this->model->_curationModel->_manifest));
						$this->model->version->set('curation_version_id', $versionNumber);

						// Check if publication is within grace period (published status)
						$gracePeriod = $this->config->get('graceperiod', 0);
						$allowArchive = $gracePeriod ? false : true;
						if ($allowArchive && $this->model->version->accepted && $this->model->version->accepted != '0000-00-00 00:00:00')
						{
							$monthFrom = Date::of($this->model->version->accepted . '+1 month')->toSql();
							if (strtotime($monthFrom) < Date::toUnix())
							{
								$allowArchive = true;
							}
						}

						// Run mkAIP if no grace period set or passed
						if (!$this->getError()
						 && $this->model->version->doi
						 && $allowArchive == true
						 && (!$this->model->version->archived || $this->model->version->archived == '0000-00-00 00:00:00')
						 && Helpers\Utilities::mkAip($this->model->version))
						{
							$this->model->version->archived = Date::toSql();
						}
					}
					
					if ($action == 'republish')
					{
						$this->model->version->accepted = Date::toSql();
					}
					
					// Add publication to author's ORCID record
					foreach ($authors as $author)
					{
						if (!empty($author->user_id))
						{
							$profile = \Components\Members\Models\Member::oneOrFail($author->user_id);
							
							if ($profile)
							{
								$orcidID = $profile->get('orcid');
								$accessToken = $profile->get('access_token');
								
								if (!empty($orcidID) && !empty($accessToken))
								{
									$putCode = $this->model->addPubToORCID($orcidID, $accessToken);
									
									if ($putCode)
									{
										$tblAuthor = new Tables\Author($this->database);
										$tblAuthor->saveORCIDPutCode($author->id, $putCode);
									}
								}
							}
						}
						else
						{
							$collaborator = $this->model->getCollaboratorByName($author->invited_name);
							
							// Add pub to orcid record when both orcid and access_token are not null. Othewise, send email that includes ORCID management permission link
							if (!empty($collaborator))
							{
								$orcidID = $collaborator->orcid;
								$accessToken = $collaborator->access_token;
								
								if (!empty($orcidID) && !empty($accessToken))
								{
									$putCode = $this->model->addPubToORCID($orcidID, $accessToken);
									
									if ($putCode)
									{
										$authorTbl = new Tables\Author($this->database);
										$authorTbl->saveORCIDPutCode($author->id, $putCode);
									}
								}
							}
							else
							{
								if (!empty($author->invited_email))
								{
									$subjectForPermissionEmail = Lang::txt('COM_PUBLICATIONS_GRANT_ORCID_MANAGEMENT_PERMISSION');
									$messageForPermissionEmail = Lang::txt('COM_PUBLICATIONS_GRANT_ORCID_EMAIL_MESSAGE');
									
									$config = Component::params('com_members');
									$srv = $config->get('orcid_service', 'members');
									$clientID = $config->get('orcid_' . $srv . '_client_id', '');
									$redirectURI = $config->get('orcid_' . $srv . '_permission_uri', '');
									
									if (!empty($srv) && !empty($clientID) && !empty($redirectURI))
									{
										$permissionURL = "https://";
										
										if ($config->get('orcid_service', 'members') == 'sandbox')
										{
											$permissionURL .= 'sandbox.';
										}
										
										$permissionURL .= 'orcid.org/oauth/authorize?client_id=' . $clientID . htmlspecialchars('&') . "response_type=code" . htmlspecialchars('&') . "scope=/read-limited%20/activities/update%20/person/update&redirect_uri=" . urlencode($redirectURI);
										
										$from = [];
										$from['name']  = Config::get('sitename') . ' ' . Lang::txt('COM_PUBLICATIONS');
										$from['email'] = Config::get('mailfrom');
										
										$eview = new \Hubzero\Mail\View(array(
											'base_path' => dirname(__DIR__),
											'name'      => 'emails',
											'layout'    => 'admin_html'
										));
										
										$eview->model = $this->model;
										$eview->project = $this->model->project();
										$eview->subject = $subjectForPermissionEmail;
										$eview->message = $messageForPermissionEmail;
										$eview->permissionURL = $permissionURL;
										$eview->permissionTxt = Lang::txt('COM_PUBLICATIONS_GRANT_ORCID_MANAGEMENT_PERMISSION');

										$body = [];
										$body['multipart'] = $eview->loadTemplate();
										$body['multipart'] = str_replace("\n", "\r\n", $body['multipart']);
										
										$mail = new \Hubzero\Mail\Message();
										$mail->setSubject($subject)
											->addTo($author->invited_email, $author->invited_name)
											->addFrom($from['email'], $from['name'])
											->setPriority('normal')
											->addPart($body['multipart'], 'text/html');
										$mail->send();
									}
								}
							}
						}
					}

					if (!$this->getError())
					{
						$output .= ' ' . Lang::txt('COM_PUBLICATIONS_ITEM') . ' ';
						$output .= $action == 'publish'
							? Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_PUBLISHED')
							: Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_REPUBLISHED');
					}
					break;

				case 'revert':
					// What is this? This sets it to the state it's already in.
					//$this->model->version->state = $state ? $state : 4;
					$this->model->version->state = 3;
					$activity = Lang::txt('COM_PUBLICATIONS_ACTIVITY_ADMIN_REVERTED');
					$subject .= Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_REVERTED');
					$output .= ' ' . Lang::txt('COM_PUBLICATIONS_ITEM') . ' ';
					$output .= Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_REVERTED');
					
					// Remove the publication from author's ORCID record
					foreach ($authors as $author)
					{
						$putCode = $author->orcid_work_put_code;
						
						if (!empty($putCode))
						{
							if (!empty($author->user_id))
							{
								$profile = \Components\Members\Models\Member::oneOrFail($author->user_id);
								
								if ($profile)
								{
									$orcidID = $profile->get('orcid');
									$accessToken = $profile->get('access_token');
								}
							}
							else
							{
								$collaborator = $this->model->getCollaboratorByName($author->invited_name);
								
								if (!empty($collaborator))
								{
									$orcidID = $collaborator->orcid;
									$accessToken = $collaborator->access_token;
								}
							}
							
							if (!empty($orcidID) && !empty($accessToken))
							{
								$result = $this->model->deleteWorkInORCID($orcidID, $accessToken, $putCode);
								
								if ($result)
								{
									$tblAuthor = new Tables\Author($this->database);
									$tblAuthor->saveORCIDPutCode($author->id, '');
								}
							}
						}
					}
					
					break;

				case 'unpublish':
					$this->model->version->state = 0;
					$this->model->version->published_down = Date::toSql();
					$activity = Lang::txt('COM_PUBLICATIONS_ACTIVITY_ADMIN_UNPUBLISHED');
					$subject .= Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_UNPUBLISHED');

					$output .= ' ' . Lang::txt('COM_PUBLICATIONS_ITEM') . ' ';
					$output .= Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_UNPUBLISHED');
					
					// Save the unpublished reason
					$selectedReason = Request::getInt('unPubReasonDropdownList', 0);
					$verTblObj = new Tables\Version($db);
					
					if ($selectedReason == 0)
					{
						$otherReason = Request::getString('reason', '');
						$verTblObj->saveUnPubReason($id, $this->model->version->version_number, $selectedReason, $otherReason);
					}
					else
					{
						if ($selectedReason == 1)
						{
							$verTblObj->saveUnPubReason($id, $this->model->version->version_number, Lang::txt('COM_PUBLICATIONS_UNPUBLISHED_NOT_AVAILABLE'));
						}
						else if ($selectedReason == 2)
						{
							$verTblObj->saveUnPubReason($id, $this->model->version->version_number, Lang::txt('COM_PUBLICATIONS_UNPUBLISHED_ERROR'));
						}
					}
					
					// Update DOI metadata, and set resource URL to tombstone page url.
					if ($doiService->on())
					{
						if ($this->model->version->doi
							&& preg_match("/" . $doiService->_configs->shoulder . "/", $this->model->version->doi))
						{
							$doiService->set('authors', $authors);
							$doiService->update($this->model->version->doi, true);
							
							$resURL = $doiService->_configs->livesite . DS . 'index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=tombstone' . '&id=' . $id . '&v=' . $this->model->version->version_number;
							$result = $doiService->registerURL($doi, $resURL);
							
							if (!$result)
							{
								$doiService->setError(Lang::txt('COM_PUBLICATIONS_ERROR_REGISTER_NAME_URL'));
							}
							
							if ($doiService->getError())
							{
								$this->setError($doiService->getError());
							}
						}
					}
					
					// Remove the publication from author's ORCID record
					foreach ($authors as $author)
					{
						$putCode = $author->orcid_work_put_code;
						
						if (!empty($putCode))
						{
							if (!empty($author->user_id))
							{
								$profile = \Components\Members\Models\Member::oneOrFail($author->user_id);
								
								if ($profile)
								{
									$orcidID = $profile->get('orcid');
									$accessToken = $profile->get('access_token');
								}
							}
							else
							{
								$collaborator = $this->model->getCollaboratorByName($author->invited_name);
								
								if (!empty($collaborator))
								{
									$orcidID = $collaborator->orcid;
									$accessToken = $collaborator->access_token;
								}
							}
							
							if (!empty($orcidID) && !empty($accessToken))
							{
								$result = $this->model->deleteWorkInORCID($orcidID, $accessToken, $putCode);
								
								if ($result)
								{
									$tblAuthor = new Tables\Author($this->database);
									$tblAuthor->saveORCIDPutCode($author->id, '');
								}
							}
						}
					}
					break;

				case 'wip':
					$activity = Lang::txt('COM_PUBLICATIONS_ACTIVITY_ADMIN_REQUESTED_CHANGES');
					$subject .= Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_REQUESTED_CHANGES');

					$output .= ' ' . Lang::txt('COM_PUBLICATIONS_ITEM') . ' ';
					$output .= Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_REQUESTED_CHANGES');
					break;
			}
		}

		// Updating entry if anything changed
		if (!$this->getError())
		{
			$this->model->version->modified    = Date::toSql();
			$this->model->version->modified_by = User::get('id');

			// Store content
			if (!$this->model->version->store())
			{
				App::redirect(
					$url, $this->model->version->getError(), 'error'
				);
				return;
			}
			elseif ($action)
			{
				if ($this->model->version->state != 1)
				{
					$this->model->_curationModel->removeLink();
				}
				elseif (($action == 'republish' || $action == 'publish') && !$this->model->isEmbargoed())
				{
					$this->model->_curationModel->createLink();
				}

				// Add activity
				$activity .= ' ' . strtolower(Lang::txt('version'))
						  . ' ' . $this->model->version->version_label .' '
						  . Lang::txt('COM_PUBLICATIONS_OF') . ' '
						  . strtolower(Lang::txt('publication')) . ' "'
						  . $pubtitle . '" ';

				// Build return url
				$link = '/projects/' . $project->get('alias') . '/publications/' . $id . '/?version=' . $this->model->version->version_number;

				if ($action != 'message' && !$this->getError())
				{
					$aid = $project->recordActivity(
						$activity, $id, $pubtitle, $link, 'publication', 0, $admin = 1
					);
					$sendmail = $this->config->get('email') ? 1 : 0;

					// Append comment to activity
					if ($message && $aid)
					{
						require_once \Component::path('com_projects') . DS . 'tables' . DS . 'comment.php';
						$objC = new \Components\Projects\Tables\Comment($this->database);

						$comment = \Hubzero\Utility\Str::truncate($message, 250);
						$comment = \Hubzero\Utility\Sanitize::stripAll($comment);

						$objC->itemid           = $aid;
						$objC->tbl              = 'activity';
						$objC->parent_activity  = $aid;
						$objC->comment          = $comment;
						$objC->admin            = 1;
						$objC->created          = Date::toSql();
						$objC->created_by       = User::get('id');
						$objC->store();

						// Get new entry ID
						if (!$objC->id)
						{
							$objC->checkin();
						}

						if ($objC->id)
						{
							$what = Lang::txt('COM_PROJECTS_AN_ACTIVITY');
							$curl = Route::url($project->link('feed')) . '#tr_' . $aid; // same-page link
							$caid = $project->recordActivity(
								Lang::txt('COM_PROJECTS_COMMENTED') . ' ' . Lang::txt('COM_PROJECTS_ON') . ' ' . $what,
								$objC->id,
								$what,
								$curl,
								'quote',
								0,
								1
							);

							// Store activity ID
							if ($caid)
							{
								$objC->activityid = $aid;
								$objC->store();
							}
						}
					}
				}
			}
		}

		// Save parameters
		$params = Request::getArray('params', '', 'post');
		if (is_array($params))
		{
			foreach ($params as $k => $v)
			{
				$this->model->version->saveParam($this->model->version->id, $k, $v);
			}
		}

		// Do we have a message to send?
		if ($message)
		{
			$subject .= ' - '.Lang::txt('COM_PUBLICATIONS_MSG_ADMIN_NEW_MESSAGE');
			$sendmail = 1;
			$output  .= ' ' . Lang::txt('COM_PUBLICATIONS_MESSAGE_SENT');
		}

		// Send email
		if ($sendmail && !$this->getError())
		{
			// Get ids of publication authors with accounts
			$notify   = $this->model->table('Author')->getAuthors($this->model->version->id, 1, 1, 1, true);
			$notify[] = $this->model->version->created_by;
			$notify   = array_unique($notify);

			$this->_emailContributors($subject, $message, $notify, $action);
		}

		// Append any errors
		if ($this->getError())
		{
			Notify::error($this->getError());
		}

		if ($output)
		{
			Notify::success($output);
		}

		// Redirect to edit view?
		if ($redirect)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=edit' . '&id[]=' . $id . '&version=' . $this->model->get('version_number'), false)
			);
		}
		else
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false)
			);
		}
	}

	/**
	 * Sends a message to authors (or creator) of a publication
	 *
	 * @param   string  $subject
	 * @param   string  $message
	 * @param   array   $authors
	 * @param   string  $action
	 * @return  void
	 */
	private function _emailContributors($subject = '', $message = '', $authors = array(), $action = 'publish')
	{
		if (!$this->model->exists() || !$this->model->project()->exists())
		{
			return false;
		}

		// Get pub authors' ids
		if (empty($authors))
		{
			$authors = $this->model->table('Author')->getAuthors($this->model->version->id, 1, 1, 1);
		}

		// No authors – send to publication creator
		if (count($authors) == 0)
		{
			$authors = array($this->model->version->created_by);
		}

		// Make sure there are no duplicates
		$authors = array_unique($authors);

		if ($authors && count($authors) > 0)
		{
			// Email all the contributors
			$from = array();
			$from['email'] = Config::get('mailfrom');
			$from['name']  = Config::get('sitename') . ' ' . Lang::txt('COM_PUBLICATIONS');

			$subject = $subject
				? $subject : Lang::txt('COM_PUBLICATIONS_STATUS_UPDATE');

			// Get message body
			$eview = new \Hubzero\Mail\View(array(
				'name'      => 'emails',
				'layout'    => 'admin_plain'
			));
			$eview->option  = $this->_option;
			$eview->subject = $subject;
			$eview->action  = $action;
			$eview->model   = $this->model;
			$eview->message = $message;
			$eview->project = $this->model->project();

			$body = array();
			$body['plaintext'] = $eview->loadTemplate(false);
			$body['plaintext'] = str_replace("\n", "\r\n", $body['plaintext']);

			// HTML email
			$eview->setLayout('admin_html');
			$body['multipart'] = $eview->loadTemplate();
			$body['multipart'] = str_replace("\n", "\r\n", $body['multipart']);

			// Send message
			if (!Event::trigger('xmessage.onSendMessage', array(
				'publication_status_changed',
				$subject,
				$body,
				$from,
				$authors,
				$this->_option)
			))
			{
				$this->setError(Lang::txt('COM_PUBLICATIONS_ERROR_FAILED_MESSAGE_AUTHORS'));
			}
		}
	}

	/**
	 * Displays versions of a publication
	 *
	 * @return  void
	 */
	public function versionsTask()
	{
		// Get the publications component config
		$this->view->config = $this->config;

		// Incoming publication ID
		$id = Request::getInt('id', 0);

		// Need ID
		if (!$id)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_PUBLICATIONS_ERROR_LOAD_PUBLICATION'),
				'notice'
			);
			return;
		}

		// Grab some filters for returning to place after editing
		$this->view->return = array();
		$this->view->return['cat']    = Request::getString('cat', '');
		$this->view->return['sortby'] = Request::getString('sortby', '');
		$this->view->return['status'] = Request::getString('status', '');

		// Load publication model
		$this->view->pub = new Models\Publication($id, 'default');

		if (!$this->view->pub)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_PUBLICATIONS_ERROR_LOAD_PUBLICATION'),
				'notice'
			);
			return;
		}

		// Get versions
		$this->view->versions = $this->view->pub->version->getVersions($id, $filters = array('withdev' => 1));

		// Set any errors
		if ($this->getError())
		{
			$this->view->setError($this->getError());
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Removes a publication
	 * Redirects to main listing
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$ids = Request::getArray('id', array(0));
		$erase = Request::getInt('erase', 1);

		// Ensure we have some IDs to work with
		if (count($ids) < 1)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_PUBLICATIONS_ERROR_LOAD_PUBLICATION'),
				'notice'
			);
			return;
		}

		$version = count($ids) == 1 ? Request::getString('version', 'all') : 'all';

		require_once \Component::path('com_projects') . DS . 'tables' . DS . 'activity.php';

		foreach ($ids as $id)
		{
			// Load publication
			$objP = new Tables\Publication($this->database);
			if (!$objP->load($id))
			{
				throw new Exception(Lang::txt('COM_PUBLICATIONS_NOT_FOUND'), 404);
			}

			$projectId = $objP->project_id;

			$row = new Tables\Version($this->database);

			// Get versions
			$versions = $row->getVersions($id, $filters = array('withdev' => 1));

			if ($version != 'all' && count($versions) > 1)
			{
				// Check that version exists
				$version = $row->checkVersion($id, $version) ? $version : 'dev';

				// Load version
				if (!$row->loadVersion($id, $version))
				{
					throw new Exception(Lang::txt('COM_PUBLICATIONS_VERSION_NOT_FOUND'), 404);
				}

				// Cannot delete main version if other versions exist
				if ($row->main)
				{
					throw new Exception(Lang::txt('COM_PUBLICATIONS_VERSION_MAIN_ERROR_DELETE'), 404);
				}
				if ($erase == 1)
				{
					// Delete the version
					if ($row->delete())
					{
						// Delete associations to the version
						$this->deleteVersionExistence($row->id, $id);
					}
				}
				else
				{
					$row->state = 2;
					$row->store();
				}
			}
			else
			{
				// Delete all versions
				$i = 0;
				foreach ($versions as $v)
				{
					$objV = new Tables\Version($this->database);
					if ($objV->loadVersion($id, $v->version_number))
					{
						if ($erase == 1)
						{
							// Delete the version
							if ($objV->delete())
							{
								// Delete associations to the version
								$this->deleteVersionExistence($v->id, $id);
								$i++;
							}
						}
						else
						{
							$objV->state = 2;
							$objV->store();
						}
					}
				}

				// All versions deleted?
				if ($i == count($versions))
				{
					// Delete pub record and all associations
					$objP->delete($id);
					$objP->deleteExistence($id);

					// Delete related publishing activity from feed
					$activities = \Hubzero\Activity\Log::all()
						->whereEquals('scope', 'publication')
						->whereEquals('scope_id', $id)
						->whereEquals('state', 1)
						->rows()
						->toArray();

					$logs = array();
					foreach ($activities as $activity)
					{
						$logs[] = $activity['id'];
					}

					$past = \Hubzero\Activity\Recipient::all()
						->whereIn('log_id', $logs)
						->whereEquals('state', 1)
						->rows();

					foreach ($past as $p)
					{
						$p->set('state', 0);
						$p->save();
					}

					// Build publication path
					$path =  PATH_APP . DS . trim($this->config->get('webpath'), DS) . DS . \Hubzero\Utility\Str::pad($id);

					// Delete all files
					if (is_dir($path))
					{
						Filesystem::deleteDirectory($path);
					}
				}
			}
		}

		// Redirect
		$output = ($version != 'all')
			? Lang::txt('COM_PUBLICATIONS_SUCCESS_VERSION_DELETED')
			: Lang::txt('COM_PUBLICATIONS_SUCCESS_RECORDS_DELETED') . ' (' . count($ids) . ')';
		App::redirect(
			$this->buildRedirectURL(),
			$output
		);

		return;
	}

	/**
	 * Deletes assoc with pub version
	 *
	 * @param   integer  $vid
	 * @param   integer  $pid
	 * @return  void
	 */
	public function deleteVersionExistence($vid, $pid)
	{
		// Delete authors
		$pa = new Tables\Author($this->database);
		$authors = $pa->deleteAssociations($vid);

		// Delete attachments
		$pContent = new Tables\Attachment($this->database);
		$pContent->deleteAttachments($vid);

		// Delete screenshots
		$pScreenshot = new Tables\Screenshot($this->database);
		$pScreenshot->deleteScreenshots($vid);

		// Delete access accosiations
		$pAccess = new Tables\Access($this->database);
		$pAccess->deleteGroups($vid);

		// Delete audience
		$pAudience = new Tables\Audience($this->database);
		$pAudience->deleteAudience($vid);

		// Build publication path
		$path = Helpers\Html::buildPubPath($pid, $vid, $this->config->get('webpath'), '', 1);

		// Delete all files
		if (is_dir($path))
		{
			Filesystem::deleteDirectory($path);
		}

		return true;
	}

	/**
	 * Checks in a checked-out publication and redirects
	 *
	 * @return  void
	 */
	public function cancelTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$id = Request::getInt('id', 0);

		// Checkin the resource
		$row = new Tables\Publication($this->database);
		$row->load($id);
		$row->checkin();

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false)
		);

		return;
	}

	/**
	 * Resets the rating of a resource
	 * Redirects to edit task for the resource
	 *
	 * @return  void
	 */
	public function resetratingTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$id = Request::getInt('id', 0);

		if ($id)
		{
			// Load the object, reset the ratings, save, checkin
			$row = new Tables\Publication($this->database);
			$row->load($id);
			$row->rating = '0.0';
			$row->times_rated = '0';
			$row->store();
			$row->checkin();

			$this->_message = Lang::txt('COM_PUBLICATIONS_SUCCESS_RATING_RESET');
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=edit&id[]=' . $id, false),
			$this->_message
		);
	}

	/**
	 * Resets the ranking of a resource
	 * Redirects to edit task for the resource
	 *
	 * @return  void
	 */
	public function resetrankingTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$id  = Request::getInt('id', 0);

		if ($id)
		{
			// Load the object, reset the ratings, save, checkin
			$row = new Tables\Publication($this->database);
			$row->load($id);
			$row->ranking = '0';
			$row->store();
			$row->checkin();

			$this->_message = Lang::txt('COM_PUBLICATIONS_SUCCESS_RANKING_RESET');
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=edit&id[]=' . $id, false),
			$this->_message
		);
	}

	/**
	 * Produces archival package for publication
	 * Redirects to edit task for the resource
	 *
	 * @return  void
	 */
	public function archiveTask()
	{
		// Incoming
		$pid     = Request::getInt('pid', 0);
		$vid     = Request::getInt('vid', 0);
		$version = Request::getString('version', 'default');

		// Load publication
		$pub = new Models\Publication($pid, $version, $vid);

		if (!$pub->exists())
		{
			throw new Exception(Lang::txt('COM_PUBLICATIONS_NOT_FOUND'), 404);
		}

		$url = Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=edit' . '&id[]=' . $pid . '&version=' . $version, false);

		// Get attachments
		$pub->attachments();

		// Get authors
		$pub->authors();

		// Set pub assoc and load curation
		$pub->setCuration();

		// Produce archival package
		if (!$pub->_curationModel->package(true))
		{
			// Create link
			$pub->_curationModel->createLink();

			// Checkin the resource
			$pub->publication->checkin();

			// Redirect
			App::redirect($url, Lang::txt('COM_PUBLICATIONS_ERROR_ARCHIVAL'), 'error');
			return;
		}

		// Checkin the resource
		$pub->publication->checkin();

		// Redirect
		App::redirect($url, Lang::txt('COM_PUBLICATIONS_SUCCESS_ARCHIVAL'));
	}

	/**
	 * Checks-in one or more resources
	 * Redirects to the main listing
	 *
	 * @return  void
	 */
	public function checkinTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$id  = Request::getInt('id', 0);

		if ($id)
		{
			// Load the object and checkin
			$row = new Tables\Publication($this->database);
			$row->load($id);
			$row->checkin();
		}

		// Redirect
		App::redirect(Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false));
	}

	/**
	 * Builds the appropriate URL for redirction
	 *
	 * @return  string
	 */
	private function buildRedirectURL()
	{
		$url  = Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false);
		return $url;
	}

	/**
	 * Gets the full name of a user from their ID #
	 *
	 * @return  string
	 */
	public function authorTask()
	{
		$u = Request::getInt('u', 0);

		// Get the member's info
		$profile = User::getInstance($u);

		if (!$profile->get('name'))
		{
			$name  = $profile->get('givenName') . ' ';
			$name .= ($profile->get('middleName')) ? $profile->get('middleName') . ' ' : '';
			$name .= $profile->get('surname');
		}
		else
		{
			$name  = $profile->get('name');
		}

		echo $name . ' (' . $profile->get('id') . ')';
	}
}
