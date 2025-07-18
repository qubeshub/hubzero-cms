<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use Components\Forum\Models\Manager;
use Components\Forum\Models\Section;
use Components\Forum\Models\Category;
use Components\Forum\Models\Post;
use Components\Forum\Models\Attachment;

require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'Mail' . DS . 'Message.php';

// No direct access
defined('_HZEXEC_') or die();

require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'Plugin' . DS . 'Plugin.php';
require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'Plugin' . DS . 'Params.php';
require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'Mail' . DS . 'View.php';

/**
 * Groups Plugin class for forum entries
 */
class plgGroupsForum extends \Qubeshub\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Loads the plugin language file
	 *
	 * @param   string   $extension  The extension for which a language file should be loaded
	 * @param   string   $basePath   The basepath to use
	 * @return  boolean  True, if the file has successfully loaded.
	 */
	public function loadLanguage($extension = '', $basePath = PATH_APP)
	{
		if (empty($extension))
		{
			$extension = 'plg_' . $this->_type . '_' . $this->_name;
		}

		$group = \Hubzero\User\Group::getInstance(Request::getCmd('cn'));
		if ($group && $group->isSuperGroup())
		{
			$basePath = PATH_APP . DS . 'site' . DS . 'groups' . DS . $group->get('gidNumber');
		}

		$lang = \App::get('language');
		return $lang->load(strtolower($extension), $basePath, null, false, true)
			|| $lang->load(strtolower($extension), PATH_APP . DS . 'plugins' . DS . $this->_type . DS . $this->_name, null, false, true)
			|| $lang->load(strtolower($extension), PATH_APP . DS . 'plugins' . DS . $this->_type . DS . $this->_name, null, false, true)
			|| $lang->load(strtolower($extension), PATH_CORE . DS . 'plugins' . DS . $this->_type . DS . $this->_name, null, false, true);
	}

	/**
	 * Return the alias and name for this category of content
	 *
	 * @return  array
	 */
	public function &onGroupAreas()
	{
		$area = array(
			'name'             => $this->_name,
			'title'            => Lang::txt('PLG_GROUPS_FORUM'),
			'default_access'   => $this->params->get('plugin_access', 'members'),
			'display_menu_tab' => $this->params->get('display_tab', 1),
			'icon'             => 'f086'
		);
		return $area;
	}

	/**
	 * Return data on a group view (this will be some form of HTML)
	 *
	 * @param   object   $group       Current group
	 * @param   string   $option      Name of the component
	 * @param   string   $authorized  User's authorization level
	 * @param   integer  $limit       Number of records to pull
	 * @param   integer  $limitstart  Start of records to pull
	 * @param   string   $action      Action to perform
	 * @param   array    $access      What can be accessed
	 * @param   array    $areas       Active area(s)
	 * @return  array
	 */
	public function onGroup($group, $option, $authorized, $limit, $limitstart, $action, $access, $areas=null)
	{
		$return = 'html';
		$active = $this->_name;
		$active_real = 'discussion';

		// The output array we're returning
		$arr = array(
			'html' => '',
			'metadata' => array(),
			'name' => $active
		);

		//get this area details
		$this_area = $this->onGroupAreas();

		// Check if our area is in the array of areas we want to return results for
		if (is_array($areas) && $limit)
		{
			if (!in_array($this_area['name'], $areas))
			{
				$return = 'metadata';
			}
		}

		$this->group    = $group;
		$this->database = App::get('db');

		require_once Component::path('com_forum') . DS . 'models' . DS . 'manager.php';

		$this->forum = new Manager('group', $group->get('gidNumber'));

		// Determine if we need to return any HTML (meaning this is the active plugin)
		if ($return == 'html')
		{
			//set group members plugin access level
			$group_plugin_acl = $access[$active];

			//get the group members
			$this->members = $group->get('members');
			$this->managers = $group->get('managers'); // REFACTOR: $this->group->get('managers') can be replaced by this

			//if set to nobody make sure cant access
			if ($group_plugin_acl == 'nobody')
			{
				$arr['html'] = '<p class="info">' . Lang::txt('GROUPS_PLUGIN_OFF', ucfirst($active_real)) . '</p>';
				return $arr;
			}

			//check if guest and force login if plugin access is registered or members
			if (User::isGuest()
			 && ($group_plugin_acl == 'registered' || $group_plugin_acl == 'members' || $group_plugin_acl == 'managers'))
			{
				$return = base64_encode(Request::getString('REQUEST_URI', Route::url('index.php?option=com_groups&cn=' . $group->get('cn') . '&active=' . $active, false, true), 'server'));
				App::redirect(
					Route::url('index.php?option=com_users&view=login&return=' . $return, false),
					Lang::txt('GROUPS_PLUGIN_REGISTERED', ucfirst($active_real)),
					'warning'
				);
				return;
			}

			//check to see if user is member and plugin access requires members
			if (!in_array(User::get('id'), $this->members)
			 && $group_plugin_acl == 'members'
			 && $authorized != 'admin')
			{
				$arr['html'] = '<p class="warning">' . Lang::txt('GROUPS_PLUGIN_REQUIRES_MEMBER', ucfirst($active_real)) . '</p>';
				return $arr;
			}

			// Check to see if user is manager and plugin access requires managers
			if (!in_array(User::get('id'), $this->managers)
			&& $group_plugin_acl == 'managers'
			&& $authorized != 'admin')
			{
				$arr['html'] = '<p class="info">' . Lang::txt('GROUPS_PLUGIN_REQUIRES_MANAGER', ucfirst($active_real)) . '</p>';
				return $arr;
			}
		   
			//user vars
			$this->group_plugin_acl = $group_plugin_acl;
			$this->authorized = $authorized;

			// Get the plugins params
			$this->params = \Qubeshub\Plugin\Params::getParams($this->group->get('gidNumber'), 'groups', $this->_name);
			$this->params->def('allow_anonymous', 1);
			$this->params->def('threading', 'list');
			$this->params->def('threading_depth', 3);
			$this->params->set('access-plugin', $group_plugin_acl);

			//option and paging vars
			$this->option = $option;
			$this->limitstart = $limitstart;
			$this->limit = $limit;
			$this->base = 'index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=' . $this->_name;

			$path = Request::path();
			if (strstr($path, '/'))
			{
				$bits = $this->_parseUrl();
				// Section name
				if (isset($bits[0]) && trim($bits[0]))
				{
					if ($bits[0] == 'new')
					{
						$action = 'newsection';
					}
					else if ($bits[0] == 'settings' || $bits[0] == 'savesettings')
					{
						$action = $bits[0];
					}
					else if ($bits[0] == 'unsubscribe')
					{
						$action = 'unsubscribe';
					}
					else
					{
						Request::setVar('section', $bits[0]);
					}
				}
				// Categry name
				if (isset($bits[1]) && trim($bits[1]))
				{
					if ($bits[1] == 'edit')
					{
						$action = 'editsection';
					}
					else if ($bits[1] == 'delete')
					{
						$action = 'deletesection';
					}
					else if ($bits[1] == 'new')
					{
						$action = 'editcategory';
					}
					else
					{
						Request::setVar('category', $bits[1]);
						$action = 'categories';
					}
				}
				// Thread name
				if (isset($bits[2]) && trim($bits[2]))
				{
					if ($bits[2] == 'edit')
					{
						$action = 'editcategory';
					}
					else if ($bits[2] == 'delete')
					{
						$action = 'deletecategory';
					}
					else if ($bits[2] == 'new')
					{
						$action = 'editthread';
					}
					else
					{
						Request::setVar('thread', $bits[2]);
						$action = 'threads';
					}
				}
				// Thread action
				if (isset($bits[3]) && trim($bits[3]))
				{
					if ($bits[3] == 'edit')
					{
						$action = 'editthread';
					}
					else if ($bits[3] == 'delete')
					{
						$action = 'deletethread';
					}
					else
					{
						Request::setVar('post', $bits[3]);
					}
				}
				// Thread attachment download
				if (isset($bits[4]) && trim($bits[4]))
				{
					Request::setVar('file', $bits[4]);
					$action = 'download';
				}
			}
			$action = Request::getCmd('action', $action, 'post');

			switch ($action)
			{
				// Settings
				case 'savesettings':
					$arr['html'] .= $this->savesettings();
					break;
				case 'settings':
					$arr['html'] .= $this->settings();
					break;

				case 'sections':
					$arr['html'] .= $this->sections();
					break;
				case 'savesection':
					$arr['html'] .= $this->savesection();
					break;
				case 'deletesection':
					$arr['html'] .= $this->deletesection();
					break;

				case 'categories':
					$arr['html'] .= $this->categories();
					break;
				case 'savecategory':
					$arr['html'] .= $this->savecategory();
					break;
				case 'newcategory':
					$arr['html'] .= $this->editcategory();
					break;
				case 'editcategory':
					$arr['html'] .= $this->editcategory();
					break;
				case 'deletecategory':
					$arr['html'] .= $this->deletecategory();
					break;

				case 'threads':
					$arr['html'] .= $this->threads();
					break;
				case 'savethread':
					$arr['html'] .= $this->savethread();
					break;
				case 'editthread':
					$arr['html'] .= $this->editthread();
					break;
				case 'deletethread':
					$arr['html'] .= $this->deletethread();
					break;

				case 'orderup':
					$arr['html'] .= $this->orderup();
					break;
				case 'orderdown':
					$arr['html'] .= $this->orderdown();
					break;

				case 'download':
					$arr['html'] .= $this->download();
					break;
				case 'search':
					$arr['html'] .= $this->search();
					break;

				case 'unsubscribe':
					$arr['html'] .= $this->unsubscribe();
					break;

				default:
					$arr['html'] .= $this->sections();
					break;
			}
		}

		$arr['metadata']['count'] = $this->forum->count('threads', array('state' => 1));

		// Return the output
		return $arr;
	}

	/**
	 * Parse an SEF URL into its component bits
	 * stripping out the path leading up to the blog plugin
	 *
	 * @return  string
	 */
	private function _parseUrl()
	{
		static $path;

		if (!$path)
		{
			$path = Request::path();

			$path = str_replace(Request::base(true), '', $path);
			$path = str_replace('index.php', '', $path);
			$path = '/' . trim($path, '/');

			$blog = '/groups/' . $this->group->get('cn') . '/forum';

			if ($path == $blog)
			{
				$path = array();
				return $path;
			}

			$path = ltrim($path, DS);
			$path = explode('/', $path);

			$paths = array();
			$start = false;
			foreach ($path as $bit)
			{
				if ($bit == 'groups' && !$start)
				{
					$start = true;
					continue;
				}
				if ($start)
				{
					$paths[] = $bit;
				}
			}
			if (count($paths) >= 2)
			{
				array_shift($paths); // Remove group cn
				array_shift($paths); // Remove 'blog'
			}
			$path = $paths;
		}

		return $path;
	}

	/**
	 * Set permissions
	 *
	 * @param   string   $assetType  Type of asset to set permissions for (component, section, category, thread, post)
	 * @param   integer  $assetId    Specific object to check permissions for
	 * @return  void
	 */
	protected function _authorize($assetType='component', $assetId=null)
	{
		$this->params->set('access-view', true);

		if (!User::isGuest() && $this->group->published != 0)
		{
			$this->params->set('access-view-' . $assetType, false);
			$this->params->set('access-create-' . $assetType, false);
			$this->params->set('access-delete-' . $assetType, false);
			$this->params->set('access-edit-' . $assetType, false);

			if (in_array(User::get('id'), $this->members))
			{
				$this->params->set('access-view-' . $assetType, true);
			}
			if (isset($this->thread) && is_object($this->thread))
			{
				if (!$this->thread->get('state'))
				{
					$this->params->set('access-view-' . $assetType, false);
				}
			}
			if (!in_array(User::get('id'), $this->members))
			{
				return;
			}

			switch ($assetType)
			{
				case 'thread':
					$this->params->set('access-create-' . $assetType, true);
					if ($this->authorized == 'admin' || $this->authorized == 'manager')
					{
						$this->params->set('access-manage-' . $assetType, true);
						$this->params->set('access-delete-' . $assetType, true);
						$this->params->set('access-edit-' . $assetType, true);
						$this->params->set('access-view-' . $assetType, true);
					}
				break;
				case 'category':
					if ($this->authorized == 'admin' || $this->authorized == 'manager')
					{
						$this->params->set('access-manage-' . $assetType, true);
						$this->params->set('access-create-' . $assetType, true);
						$this->params->set('access-delete-' . $assetType, true);
						$this->params->set('access-edit-' . $assetType, true);
						$this->params->set('access-view-' . $assetType, true);
					}
				break;
				case 'section':
					if ($this->authorized == 'admin' || $this->authorized == 'manager')
					{
						$this->params->set('access-manage-' . $assetType, true);
						$this->params->set('access-create-' . $assetType, true);
						$this->params->set('access-delete-' . $assetType, true);
						$this->params->set('access-edit-' . $assetType, true);
						$this->params->set('access-view-' . $assetType, true);
					}
				break;
				case 'component':
				default:
					if ($this->authorized == 'admin' || $this->authorized == 'manager')
					{
						$this->params->set('access-manage-' . $assetType, true);
						$this->params->set('access-create-' . $assetType, true);
						$this->params->set('access-delete-' . $assetType, true);
						$this->params->set('access-edit-' . $assetType, true);
						$this->params->set('access-view-' . $assetType, true);
					}
				break;
			}
		}
	}

	/**
	 * Show sections in this forum
	 *
	 * @return  string
	 */
	public function sections()
	{
		// Incoming
		$filters = array(
			'scope'      => $this->forum->get('scope'),
			'scope_id'   => $this->forum->get('scope_id'),
			'search'     => Request::getString('q', ''),
			'state'      => Section::STATE_PUBLISHED,
			'access'     => array(1),
			'sort'       => 'ordering',
			'sort_Dir'   => 'ASC'
		);

		if (!User::isGuest())
		{
			$filters['access'][] = 2; // Registered
			$filters['access'][] = 4; // Protected
		}
		if (in_array(User::get('id'), $this->members))
		{
			$filters['access'][] = 5; // Private
		}

		$edit = Request::getString('section', '');

		$sections = $this->forum->sections($filters)
			->ordered()
			->rows();

		$categories = $this->forum->categories()->rows();

		//get authorization
		$this->_authorize('section');
		$this->_authorize('category');

		if (!$sections->count()
		 && $this->params->get('access-create-section')
		 && Request::getWord('action') == 'populate')
		{
			switch ($this->group_plugin_acl)
			{
				case 'members':
					$access = 5;
					break;
				case 'registered':
					$access = 2;
					break;
				case 'anyone':
				default:
					$access = 1;
					break;
			}
			if (!$this->forum->setup($access))
			{
				$this->setError($this->forum->getError());
			}
			$sections = $this->forum->sections($filters)
				->purgeCache() // Previous query cached 'no results'
				->ordered()
				->rows();
		}

		// Instantiate a vew
		$this->view = $this->view('display', 'sections');

		// Email settings data
		$recvEmailOptionID = 0;
		$recvEmailOptionValue = 0;
		if (file_exists(PATH_APP . DS . 'plugins' . DS . 'groups' . DS . 'memberoptions' . DS . 'models' . DS . 'memberoption.php'))
		{
			include_once PATH_APP . DS . 'plugins' . DS . 'groups' . DS . 'memberoptions' . DS . 'models' . DS . 'memberoption.php';

			$recvEmailOption = Plugins\Groups\Memberoptions\Models\Memberoption::oneByUserAndOption(
				$this->group->get('gidNumber'),
				User::get('id'),
				'receive-forum-email'
			);

			$recvEmailOptionID = $recvEmailOption->get('id', 0);
			$recvEmailOptionValue = $recvEmailOption->get('optionvalue', 0);
		}

		return $this->view
			->set('recvEmailOptionID', $recvEmailOptionID)
			->set('recvEmailOptionValue', $recvEmailOptionValue)
			->set('option', $this->option)
			->set('group', $this->group)
			->set('filters', $filters)
			->set('config', $this->params)
			->set('forum', $this->forum)
			->set('sections', $sections)
			->set('categories', $categories)
			->set('edit', $edit)
			->loadTemplate();
	}

	/**
	 * Saves a section and redirects to main page afterward
	 *
	 * @return  void
	 */
	public function savesection()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming posted data
		$fields = Request::getArray('fields', array(), 'post');
		$fields = array_map('trim', $fields);
		$fields['state'] = 1;

		// Instantiate a new table row and bind the incoming data
		$section = \Components\Forum\Models\Section::oneOrNew($fields['id'])->set($fields);

		if (in_array($section->get('alias'), array('new', 'settings', 'savesettings')))
		{
			App::redirect(
				Route::url($this->base),
				Lang::txt('PLG_GROUPS_FORUM_SECTION_TITLE_RESERVED', $section->get('alias')),
				'error'
			);
		}

		// Check for alias duplicates
		if (!$section->isUnique())
		{
			App::redirect(
				Route::url($this->base),
				Lang::txt('PLG_GROUPS_FORUM_ERROR_SECTION_ALREADY_EXISTS'),
				'error'
			);
		}

		// Store new content
		if (!$section->save())
		{
			Notify::error($section->getError());
		}
		else
		{
			// Log activity
			Event::trigger('system.logActivity', [
				'activity' => [
					'action'      => ($fields['id'] ? 'updated' : 'created'),
					'scope'       => 'forum.section',
					'scope_id'    => $section->get('id'),
					'description' => Lang::txt('PLG_GROUPS_FORUM_ACTIVITY_SECTION_' . ($fields['id'] ? 'UPDATED' : 'CREATED'), '<a href="' . Route::url($this->base) . '">' . $section->get('title') . '</a>'),
					'details'     => array(
						'title' => $section->get('title'),
						'url'   => Route::url($this->base)
					)
				],
				'recipients' => array(
					['group', $this->group->get('gidNumber')],
					['forum.' . $this->forum->get('scope'), $this->forum->get('scope_id')],
					['forum.section', $section->get('id')],
					['user', $section->get('created_by')]
				)
			]);
		}

		// Set the redirect
		App::redirect(
			Route::url($this->base)
		);
	}

	/**
	 * Deletes a section and redirects to main page afterwards
	 *
	 * @return  void
	 */
	public function deletesection()
	{
		// Is the user logged in?
		// Login check is handled in the onGroup() method
		/*if (User::isGuest())
		{
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode(Route::url($this->base))),
				Lang::txt('PLG_GROUPS_FORUM_LOGIN_NOTICE'),
				'warning'
			);
			return;
		}*/

		// Incoming
		$alias = Request::getString('section', '');

		// Load the section
		$section = Section::all()
			->whereEquals('alias', Request::getString('section'))
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Section::STATE_DELETED)
			->row();

		if (!$section->get('id'))
		{
			App::abort(404, Lang::txt('LG_GROUPS_FORUM_SECTION_NOT_FOUND'));
		}

		// Check if user is authorized to delete entries
		$this->_authorize('section', $section->get('id'));

		if (!$this->params->get('access-delete-section'))
		{
			App::redirect(
				Route::url($this->base),
				Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'),
				'warning'
			);
		}

		// Set the section to "deleted"
		$section->set('state', $section::STATE_DELETED);

		if (!$section->save())
		{
			Notify::error($section->getError());
		}
		else
		{
			Notify::success(Lang::txt('PLG_GROUPS_FORUM_SECTION_DELETED'));

			// Log activity
			Event::trigger('system.logActivity', [
				'activity' => [
					'action'      => 'deleted',
					'scope'       => 'forum.section',
					'scope_id'    => $section->get('id'),
					'description' => Lang::txt('PLG_GROUPS_FORUM_ACTIVITY_SECTION_DELETED', '<a href="' . Route::url($this->base) . '">' . $section->get('title') . '</a>'),
					'details'     => array(
						'title' => $section->get('title'),
						'url'   => Route::url($this->base)
					)
				],
				'recipients' => array(
					['group', $this->group->get('gidNumber')],
					['forum.' . $this->forum->get('scope'), $this->forum->get('scope_id')],
					['forum.section', $section->get('id')],
					['user', $section->get('created_by')]
				)
			]);
		}

		// Redirect to main listing
		App::redirect(
			Route::url($this->base)
		);
	}

	/**
	 * Display content for a category
	 *
	 * @return  string
	 */
	public function categories()
	{
		// Incoming
		$filters = array(
			'limit'      => Request::getInt('limit', 25),
			'start'      => Request::getInt('limitstart', 0),
			'section'    => Request::getString('section', ''),
			'category'   => Request::getCmd('category', ''),
			'search'     => Request::getString('q', ''),
			'scope'      => $this->forum->get('scope'),
			'scope_id'   => $this->forum->get('scope_id'),
			'state'      => 1,
			'parent'     => 0,
			'access'     => array(1)
		);
		if (!User::isGuest())
		{
			$filters['access'][] = 2;
			$filters['access'][] = 4;
		}
		if (in_array(User::get('id'), $this->members))
		{
			$filters['access'][] = 5;
		}

		$filters['sortby'] = Request::getWord('sortby', $this->params->get('sorting', 'activity'));
		switch ($filters['sortby'])
		{
			case 'title':
				$filters['sort'] = 'sticky` DESC, `title';
				$filters['sort_Dir'] = strtoupper(Request::getString('sortdir', 'ASC'));
			break;

			case 'replies':
				$filters['sort'] = 'sticky` DESC, `rgt';
				$filters['sort_Dir'] = strtoupper(Request::getString('sortdir', 'DESC'));
			break;

			case 'created':
				$filters['sort'] = 'sticky` DESC, `created';
				$filters['sort_Dir'] = strtoupper(Request::getString('sortdir', 'DESC'));
			break;

			case 'activity':
			default:
				$filters['sort'] = 'sticky` DESC, `activity';
				$filters['sort_Dir'] = strtoupper(Request::getString('sortdir', 'DESC'));
			break;
		}

		if (!in_array($filters['sort_Dir'], array('ASC', 'DESC')))
		{
			$filters['sort_Dir'] = 'DESC';
		}

		$section = Section::all()
			->whereEquals('alias', $filters['section'])
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Section::STATE_DELETED)
			->row();
		if (!$section->get('id'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_ERROR_SECTION_NOT_FOUND'));
		}

		$category = Category::all()
			->whereEquals('alias', $filters['category'])
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->whereEquals('section_id', $section->get('id'))
			->where('state', '!=', Category::STATE_DELETED)
			->row();
		if (!$category->get('id'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_ERROR_CATEGORY_NOT_FOUND'));
		}

		//get authorization
		$this->_authorize('category');
		$this->_authorize('thread');

		if ($category->get('access') == 5 && !$this->params->get('access-view-category'))
		{
			App::abort(403, Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'));
		}

		$threads = $category->threads()
			->select("*, (CASE WHEN last_activity IS NOT NULL THEN last_activity ELSE created END)", 'activity')
			->whereEquals('state', $filters['state'])
			->whereIn('access', $filters['access'])
			->order($filters['sort'], $filters['sort_Dir'])
			->paginated()
			->rows();

		// Output view
		return $this->view('display', 'categories')
			->set('option', $this->option)
			->set('group', $this->group)
			->set('config', $this->params)
			->set('forum', $this->forum)
			->set('section', $section)
			->set('category', $category)
			->set('threads', $threads)
			->set('filters', $filters)
			->setErrors($this->getErrors())
			->loadTemplate();
	}

	/**
	 * Search forum entries and display results
	 *
	 * @return  string
	 */
	public function search()
	{
		// Incoming
		$filters = array(
			'limit'      => Request::getInt('limit', 25),
			'start'      => Request::getInt('limitstart', 0),
			'search'     => Request::getString('q', ''),
			'orderBy'    => Request::getString('orderBy', 'created'),
			'orderDir'   => Request::getString('orderDir', 'DESC'),
			'scope'      => $this->forum->get('scope'),
			'scope_id'   => $this->forum->get('scope_id'),
			'state'      => 1,
			'access'     => array(1)
		);
		if (!$filters['search'])
		{
			return $this->sections();
		}

		if (!in_array($filters['orderDir'], array('ASC', 'DESC')))
		{
			$filters['orderDir'] = 'DESC';
		}

		if (!User::isGuest())
		{
			$filters['access'][] = 2;
			$filters['access'][] = 4;
		}
		if (in_array(User::get('id'), $this->members))
		{
			$filters['access'][] = 5;
		}

		$section = Section::blank();
		$section->set('scope', $this->forum->get('scope'));
		$section->set('title', Lang::txt('PLG_GROUPS_FORUM_POSTS'));
		$section->set('alias', str_replace(' ', '-', $section->get('title')));
		$section->set('alias', preg_replace("/[^a-zA-Z0-9\-]/", '', strtolower($section->get('title'))));

		// Get all sections
		$sections = array();
		foreach ($this->forum->sections($filters)->rows() as $section)
		{
			$sections[$section->get('id')] = $section;
		}

		$category = Category::blank();
		$category->set('scope', $this->forum->get('scope'));
		$category->set('scope_id', $this->forum->get('scope_id'));
		$category->set('title', Lang::txt('PLG_GROUPS_FORUM_SEARCH'));
		$category->set('alias', str_replace(' ', '-', $category->get('title')));
		$category->set('alias', preg_replace("/[^a-zA-Z0-9\-]/", '', strtolower($category->get('title'))));

		$categories = array();
		foreach ($this->forum->categories($filters)->rows() as $category)
		{
			$categories[$category->get('id')] = $category;
		}

		$filters['search'] = Request::getString('q', '');

		//get authorization
		$this->_authorize('category');
		$this->_authorize('thread');

		return $this->view('search', 'categories')
			->set('option', $this->option)
			->set('group', $this->group)
			->set('config', $this->params)
			->set('forum', $this->forum)
			->set('sections', $sections)
			->set('categories', $categories)
			->set('filters', $filters)
			->setErrors($this->getErrors())
			->loadTemplate();
	}

	/**
	 * Show a form for editing a category
	 *
	 * @param   object  $category
	 * @return  string
	 */
	public function editcategory($category=null)
	{
		// Login check is handled in the onGroup() method
		/*if (User::isGuest())
		{
			$return = Route::url($this->base);
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($return))
			);
			return;
		}*/

		// Get the section
		$section = Section::all()
			->whereEquals('alias', Request::getString('section', ''))
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Section::STATE_DELETED)
			->row();
		if (!$section->get('id'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_ERROR_SECTION_NOT_FOUND'));
		}

		// Incoming
		if (!is_object($category))
		{
			$category = Category::all()
				->whereEquals('alias', Request::getString('category', ''))
				->whereEquals('scope', $this->forum->get('scope'))
				->whereEquals('scope_id', $this->forum->get('scope_id'))
				->whereEquals('section_id', $section->get('id'))
				->where('state', '!=', Category::STATE_DELETED)
				->row();
		}

		$this->_authorize('category', $category->get('id'));

		if ($category->isNew())
		{
			$category->set('created_by', User::get('id'));
			$category->set('section_id', $section->get('id'));
		}
		elseif ($category->get('created_by') != User::get('id') && !$this->params->get('access-create-category'))
		{
			App::redirect(
				Route::url($this->base)
			);
		}

		return $this->view('edit', 'categories')
			->set('option', $this->option)
			->set('group', $this->group)
			->set('config', $this->params)
			->set('forum', $this->forum)
			->set('section', $section)
			->set('category', $category)
			->setErrors($this->getErrors())
			->loadTemplate();
	}

	/**
	 * Save a category
	 *
	 * @return  void
	 */
	public function savecategory()
	{
		// Check for request forgeries
		Request::checkToken();

		$fields = Request::getArray('fields', array(), 'post');
		$fields = array_map('trim', $fields);

		// Instantiate a category
		$category = Category::oneOrNew($fields['id'])->set($fields);

		// Double-check that the user is authorized
		$this->_authorize('category', $category->get('id'));

		if (!$this->params->get('access-edit-category'))
		{
			// Set the redirect
			App::redirect(
				Route::url($this->base)
			);
		}

		$category->set('closed', (isset($fields['closed']) && $fields['closed']) ? 1 : 0);

		// Forge an alias from the title
		if ($category->get('alias') == '')
		{
			$alias = $category->automaticAlias(array('title' => $category->get('title')));
			$category->set('alias', $alias);
		}

		// Check for alias duplicates within section?
		if (!$category->isUnique())
		{
			$category->set('alias', ''); //reset alias
			$category->set('section_id', (int) $category->get('section_id'));
			Request::setVar('section_id', $category->get('section_id'));

			Notify::error(Lang::txt('PLG_GROUPS_FORUM_CATEGORY_ALREADY_EXISTS'));
			return $this->editcategory($category);
		}

		// Store new content
		if (!$category->save())
		{
			Notify::error($category->getError());
			return $this->editcategory($category);
		}

		// Log activity
		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => ($fields['id'] ? 'updated' : 'created'),
				'scope'       => 'forum.category',
				'scope_id'    => $category->get('id'),
				'description' => Lang::txt('PLG_GROUPS_FORUM_ACTIVITY_CATEGORY_' . ($fields['id'] ? 'UPDATED' : 'CREATED'), '<a href="' . Route::url($this->base) . '">' . $category->get('title') . '</a>'),
				'details'     => array(
					'title' => $category->get('title'),
					'url'   => Route::url($this->base)
				)
			],
			'recipients' => array(
				['group', $this->group->get('gidNumber')],
				['forum.' . $this->forum->get('scope'), $this->forum->get('scope_id')],
				['forum.section', $category->get('section_id')],
				['user', $category->get('created_by')]
			)
		]);

		// Set the redirect
		App::redirect(
			Route::url($this->base)
		);
	}

	/**
	 * Delete a category
	 *
	 * @return  void
	 */
	public function deletecategory()
	{
		// Is the user logged in?
		// Login check is handled in the onGroup() method
		/*if (User::isGuest())
		{
			App::redirect(
				Route::url($this->base),
				Lang::txt('PLG_GROUPS_FORUM_LOGIN_NOTICE'),
				'warning'
			);
			return;
		}*/

		// Load the category
		$category = Category::all()
			->whereEquals('alias', Request::getString('category', ''))
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Category::STATE_DELETED)
			->row();

		// Incoming
		if (!$category->get('id'))
		{
			App::redirect(
				Route::url($this->base),
				Lang::txt('PLG_GROUPS_FORUM_MISSING_ID'),
				'error'
			);
		}

		// Check if user is authorized to delete entries
		$this->_authorize('category', $category->get('id'));

		if (!$this->params->get('access-delete-category'))
		{
			App::redirect(
				Route::url($this->base),
				Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'),
				'warning'
			);
		}

		// Set the category to "deleted"
		$category->set('state', $category::STATE_DELETED);

		if (!$category->save())
		{
			App::redirect(
				Route::url($this->base),
				$category->getError(),
				'error'
			);
		}

		// Log activity
		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => 'deleted',
				'scope'       => 'forum.category',
				'scope_id'    => $category->get('id'),
				'description' => Lang::txt('PLG_GROUPS_FORUM_ACTIVITY_CATEGORY_DELETED', '<a href="' . Route::url($this->base) . '">' . $category->get('title') . '</a>'),
				'details'     => array(
					'title' => $category->get('title'),
					'url'   => Route::url($this->base)
				)
			],
			'recipients' => array(
				['group', $this->group->get('gidNumber')],
				['forum.' . $this->forum->get('scope'), $this->forum->get('scope_id')],
				['forum.section', $category->get('section_id')],
				['user', $category->get('created_by')]
			)
		]);

		// Redirect to main listing
		App::redirect(
			Route::url($this->base),
			Lang::txt('PLG_GROUPS_FORUM_CATEGORY_DELETED'),
			'passed'
		);
	}

	/**
	 * Show a thread
	 *
	 * @return  string
	 */
	public function threads()
	{
		// Incoming
		$filters = array(
			'limit'    => (int)Request::getState('groups.forum.thread', 'limit', Config::get('list_limit'), 'int'),
			'start'    => (int)Request::getState('groups.forum.thread.' . Request::getInt('thread', 0), 'limitstart', 0, 'int'),
			'section'  => Request::getString('section', ''),
			'category' => Request::getCmd('category', ''),
			'thread'   => Request::getInt('thread', 0),
			'scope'    => $this->forum->get('scope'),
			'scope_id' => $this->forum->get('scope_id'),
			'state'    => Post::STATE_PUBLISHED,
			'access'   => array(1)
		);
		if (!User::isGuest())
		{
			$filters['access'][] = 2; // Registered
			$filters['access'][] = 4; // Protected
		}
		if (in_array(User::get('id'), $this->members))
		{
			$filters['access'][] = 5; // Private
		}

		// Section
		$section = Section::all()
			->whereEquals('alias', $filters['section'])
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Section::STATE_DELETED)
			->row();
		if (!$section->get('id'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_ERROR_SECTION_NOT_FOUND'));
		}

		$category = Category::all()
			->whereEquals('alias', $filters['category'])
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Category::STATE_DELETED)
			->row();
		if (!$category->get('id'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_ERROR_CATEGORY_NOT_FOUND'));
		}

		$filters['category_id'] = $category->get('id');

		// Load the topic
		$thread = Post::oneOrFail($filters['thread']);

		// Make sure thread belongs to this group
		if ($thread->get('scope_id') != $this->forum->get('scope_id')
		 || $thread->get('scope') != $this->forum->get('scope'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_ERROR_THREAD_NOT_FOUND'));
		}

		// Redirect if the thread is soft-deleted
		if ($thread->get('state') == $thread::STATE_DELETED)
		{
			App::redirect(
				Route::url($this->base . '&scope=' . $this->filters['section'] . '/' . $this->filters['category']),
				Lang::txt('PLG_GROUPS_FORUM_ERROR_THREAD_NOT_FOUND'),
				'error'
			);
		}

		$filters['state'] = array(1, 3);

		// Get authorization
		$this->_authorize('category', $category->get('id'));
		$this->_authorize('thread', $thread->get('id'));
		$this->_authorize('post');

		// Get all the likes of this thread
		$db = \App::get('db');
		$queryLikes = "SELECT LIKES.threadId as 'threadId', LIKES.postId as 'postId', 
		  LIKES.userId as 'userId', USERS.name as 'userName', USERS.email as 'userEmail' 
		  FROM jos_forum_posts_like as LIKES, jos_users AS USERS
		  WHERE LIKES.userId = USERS.id AND LIKES.threadId = " . $thread->get('id');
		$db->setQuery($queryLikes);
		$initialLikesList = $db->loadObjectList();

		// If the access is anything beyond public,
		// make sure they're logged in.
		if (User::isGuest() && !in_array($thread->get('access'), User::getAuthorisedViewLevels()))
		{
			$return = Route::url($this->base, false, true);
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($return))
			);
		}

		// If the access is private, make sure they're a member
		if ($thread->get('access') == 5 && !$this->params->get('access-view-thread'))
		{
			App::abort(403, Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'));
		}

		// If the access is protected,
		// disable editing and posting capabilities
		if ($thread->get('access') == 4 && !$this->params->get('access-view-thread'))
		{
			$this->params->get('access-create-thread', false);
			$this->params->get('access-edit-thread', false);
			$this->params->get('access-delete-thread', false);
			$this->params->get('access-manage-thread', false);
		}

		return $this->view('display', 'threads')
			->set('option', $this->option)
			->set('group', $this->group)
			->set('config', $this->params)
			->set('forum', $this->forum)
			->set('section', $section)
			->set('category', $category)
			->set('thread', $thread)
			->set('likes', $initialLikesList)
			->set('filters', $filters)
			->setErrors($this->getErrors())
			->loadTemplate();
	}

	/**
	 * Show a form for editing a post
	 *
	 * @param   object  $post
	 * @return  string
	 */
	public function editthread($post=null)
	{
		$id       = Request::getInt('thread', 0);
		$category = Request::getString('category', '');
		$section  = Request::getString('section', '');

		// Login check is handled in the onGroup() method
		/*if (User::isGuest())
		{
			$return = Route::url($this->base . '&scope=' . $sectionAlias . '/' . $category . '/new', false, true);
			if ($id)
			{
				$return = Route::url($this->base . '&scope=' . $sectionAlias . '/' . $category . '/' . $id . '/edit', false, true);
			}
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($return))
			);
			return;
		}*/

		// Section
		$section = Section::all()
			->whereEquals('alias', $section)
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->row();
		if (!$section->get('id'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_ERROR_SECTION_NOT_FOUND'));
		}

		// Get the category
		$category = Category::all()
			->whereEquals('alias', $category)
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->row();
		if (!$category->get('id'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_ERROR_CATEGORY_NOT_FOUND'));
		}

		// Incoming
		if (!is_object($post))
		{
			$post = Post::oneOrNew($id);
		}

		// Get authorization
		$this->_authorize('thread', $id);

		if ($post->isNew())
		{
			$post->set('scope', $this->forum->get('scope'));
			$post->set('created_by', User::get('id'));
		}
		elseif ($post->get('created_by') != User::get('id') && !$this->params->get('access-edit-thread'))
		{
			App::redirect(Route::url($this->base . '&scope=' . $section . '/' . $category));
		}

		return $this->view('edit', 'threads')
			->set('option', $this->option)
			->set('group', $this->group)
			->set('config', $this->params)
			->set('forum', $this->forum)
			->set('section', $section)
			->set('category', $category)
			->set('post', $post)
			->setErrors($this->getErrors())
			->loadTemplate();
	}

	/**
	 * Saves posted data for a new/edited forum thread post
	 *
	 * @return  void
	 */
	public function savethread()
	{
		// Login check is handled in the onGroup() method
		/*if (User::isGuest())
		{
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode(Route::url($this->base, false, true)))
			);
			return;
		}*/

		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$section = Request::getString('section', '');
		$fields  = Request::getArray('fields', array(), 'post');
		$fields  = array_map('trim', $fields);

		$fields['sticky']    = (isset($fields['sticky']))    ? $fields['sticky']    : 0;
		$fields['closed']    = (isset($fields['closed']))    ? $fields['closed']    : 0;
		$fields['anonymous'] = (isset($fields['anonymous'])) ? $fields['anonymous'] : 0;

		// Instantiate a Post record
		$post = Post::oneOrNew($fields['id']);

		$this->_authorize('thread', intval($fields['id']));
		$asset = 'thread';
		if ($fields['parent'])
		{
			//$asset = 'post';
		}

		$moving = false;

		// Already present
		if ($fields['id'])
		{
			if ($post->get('created_by') == User::get('id'))
			{
				$this->params->set('access-edit-' . $asset, true);
			}

			// Determine if we are moving the category for email suppression
			if ($post->get('category_id') != $fields['category_id'])
			{
				$moving = true;
			}

			$fields['modified'] = \Date::toSql();
			$fields['modified_by'] = User::get('id');
		}

		// Extracting emails from the new post submitted
		$domComment = new \DOMDocument();
		$domComment->loadHTML($fields['comment']);
		$mentionEmailList = array();
		foreach ($domComment->getElementsByTagName('a') as $item) {
			$userId = $item->getAttribute('data-user-id');
            $user = User::getInstance($userId);
            $email = $user->get('email');

            $mentionEmailList[] = $email;
		}

		if (!$this->params->get('access-edit-thread')
		 && !$this->params->get('access-create-thread'))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=forum'),
				Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'),
				'warning'
			);
		}

		// Bind data
		$post->set($fields);

		// Make sure the thread exists and is accepting new posts
		if ($post->get('parent') && isset($fields['thread']))
		{
			$thread = Post::oneOrFail($fields['thread']);

			if (!$thread->get('id') || $thread->get('closed'))
			{
				Notify::error(Lang::txt('PLG_GROUPS_FORUM_ERROR_THREAD_CLOSED'));
				return $this->editthread($post);
			}
		}

		if (!$post->get('category_id'))
		{
			Notify::error(Lang::txt('PLG_GROUPS_FORUM_ERROR_MISSING_CATEGORY'));
			return $this->editthread($post);
		}

		// Make sure the category exists and is accepting new posts
		$category = Category::oneOrFail($post->get('category_id'));

		if ($category->get('closed'))
		{
			Notify::error(Lang::txt('PLG_GROUPS_ERROR_CATEGORY_CLOSED'));
			return $this->editthread($post);
		}

		// Store new content
		if (!$post->save())
		{
			Notify::error($post->getError());
			return $this->editthread($post);
		}

		// Upload
		if (!$this->upload($post->get('thread', $post->get('id')), $post->get('id')))
		{
			Notify::error($this->getError());
			return $this->editthread($post);
		}

		// Save tags
		$post->tag(Request::getString('tags', '', 'post'), User::get('id'));

		// Set message
		if (!$fields['id'])
		{
			$message = Lang::txt('PLG_GROUPS_FORUM_POST_ADDED');

			if (!$fields['parent'])
			{
				$message = Lang::txt('PLG_GROUPS_FORUM_THREAD_STARTED');
			}
		}
		else
		{
			$message = ($post->get('modified_by')) ? Lang::txt('PLG_GROUPS_FORUM_POST_EDITED') : Lang::txt('PLG_GROUPS_FORUM_POST_ADDED');
		}

		$section = $category->section();
		$thread  = Post::oneOrNew($post->get('thread'));

		// Disable notifications
		if ($fields['id'] && !Request::getInt('notify', 0))
		{
			$moving = true;
		}

		// Email the group and insert email tokens to allow them to respond to group posts via email
		$params = Component::params('com_groups');
		if ($params->get('email_forum_comments') && (isset($moving) && $moving == false))
		{
			$thread->set('section', $section->get('alias'));
			$thread->set('category', $category->get('alias'));

			$post->set('section', $section->get('alias'));
			$post->set('category', $category->get('alias'));

			// Figure out who should be notified about this comment (all group members for now)
			$userIDsToEmail = $this->_getEmailRecipientIds($category);

			$allowEmailResponses = true;

			try
			{
				$encryptor = new \Hubzero\Mail\Token();
			}
			catch (Exception $e)
			{
				$allowEmailResponses = false;
			}

			$from = array(
				'name'  => (!$post->get('anonymous') ? $post->creator->get('name', Lang::txt('PLG_GROUPS_FORUM_UNKNOWN')) : Lang::txt('JANONYMOUS')) . ' @ ' . Config::get('sitename'),
				'email' => Config::get('mailfrom'),
				'replytoname'  => Config::get('sitename'),
				'replytoemail' => Config::get('mailfrom')
			);

			// Email each group member separately, each needs a user specific token
			foreach ($userIDsToEmail as $userID)
			{
				$unsubscribeLink = '';
				$delimiter = '';

				if ($allowEmailResponses)
				{
					$delimiter = '~!~!~!~!~!~!~!~!~!~!';

					// Construct User specific Email ThreadToken
					// Version, type, userid, xforumid
					$token = $encryptor->buildEmailToken(1, 2, $userID, $post->get('thread', $post->get('id')));

					// add unsubscribe link
					$unsubscribeToken = $encryptor->buildEmailToken(1, 3, $userID, $this->group->get('gidNumber'));
					$unsubscribeLink  = rtrim(Request::base(), '/') . '/' . ltrim(Route::url('index.php?option=com_groups&cn=' . $this->group->get('cn') .'&active=forum&action=unsubscribe&t=' . $unsubscribeToken), DS);


					if(Component::params('com_groups')->get('email_comment_processing'))
					{
						$from['replytoname']  = Lang::txt('PLG_GROUPS_FORUM_REPLYTO') . ' @ ' . Config::get('sitename');
						$from['replytoemail'] = 'hgm-' . $token . '@' . $_SERVER['HTTP_HOST'];
					}
					else
					{
						$from['replytoname']  = 'noreply';
						$from['replytoemail'] = 'noreply@' . $_SERVER['HTTP_HOST'];
					}
				}

				$msg = array();

				// create view object
				$eview = new Qubeshub\Mail\View(array(
					'base_path' => __DIR__,
					'name'      => 'email',
					'layout'    => 'comment_plain'
				));

				// plain text
				$eview
					->set('delimiter', $delimiter)
					->set('unsubscribe', $unsubscribeLink)
					->set('group', $this->group)
					->set('section', $section)
					->set('category', $category)
					->set('thread', $thread)
					->set('post', $post);

				$plain = $eview->loadTemplate(false);
				$msg['plaintext'] = str_replace("\n", "\r\n", $plain);

				// HTML
				$eview->setLayout('comment_html');
				$html = $eview->loadTemplate();
				$msg['multipart'] = str_replace("\n", "\r\n", $html);

				$subject = Lang::txt('PLG_GROUPS_FORUM') . ': ' . $this->group->get('description') . ' - ' . $thread->get('title');

				if (!Event::trigger('xmessage.onSendMessage', array('group_message', $subject, $msg, $from, array($userID), $this->option, null, '', $this->group->get('gidNumber'), false, $post->get('anonymous', 0))))
				{
					$this->setError(Lang::txt('GROUPS_ERROR_EMAIL_MEMBERS_FAILED'));
				}
			}
		}

		$url = $this->base . '&scope=' . $section->get('alias') . '/' . $category->get('alias') . '/' . $thread->get('id');

		// Record the activity
		$recipients = array(
			['group', $this->group->get('gidNumber')],
			['forum.' . $this->forum->get('scope'), $this->forum->get('scope_id')],
			['user', $post->get('created_by')]
		);
		foreach ($this->group->get('managers') as $recipient)
		{
			$recipients[] = ['user', $recipient];
		}
		$type = 'thread';
		$desc = Lang::txt(
			'PLG_GROUPS_FORUM_ACTIVITY_' . strtoupper($type) . '_' . ($fields['id'] ? 'UPDATED' : 'CREATED'),
			'<a href="' . Route::url($url) . '">' . $post->get('title') . '</a>'
		);
		// If this is a post in a thread and not the thread starter...
		if ($post->get('parent'))
		{
			$thread = isset($thread) ? $thread : Post::oneOrFail($post->get('thread'));
			$thread->set('last_activity', ($fields['id'] ? $post->get('modified') : $post->get('created')));
			$thread->save();

			$type = 'post';
			$desc = Lang::txt(
				'PLG_GROUPS_FORUM_ACTIVITY_' . strtoupper($type) . '_' . ($fields['id'] ? 'UPDATED' : 'CREATED'),
				$post->get('id'),
				'<a href="' . Route::url($url) . '">' . $thread->get('title') . '</a>'
			);

			// If the parent post is not the same as the
			// thread starter (i.e., this is a reply)
			if ($post->get('parent') != $post->get('thread'))
			{
				$parent = Post::oneOrFail($post->get('parent'));
				$recipients[] = ['user', $parent->get('created_by')];
			}
		}

		// Email to Users
		if ($mentionEmailList) 
		{
			$comment = $fields['comment'];
			$createdByUserId = $post->get('created_by');
			$createdByUser = User::getInstance($createdByUserId);
			$createdUserName = $createdByUser->get('name');
			$groupAlias = $this->group->get('cn');
			$groupTitle = $this->group->get('description');

			$urlExt = 'groups/' . $this->group->get('cn') . '/forum/' . $section->get('alias') . '/' . $category->get('alias') . '/' . $post->get('thread') . '#c' . $post->get('id');
			$host = $_SERVER['HTTP_HOST'];
			$externalUrl = 'https://' . $host . '/' . $urlExt;

			$this->emailToAllMentionedUsersInGroup($mentionEmailList, $comment, $externalUrl, $createdUserName, $groupAlias, $groupTitle);
		}

		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => ($fields['id'] ? 'updated' : 'created'),
				'scope'       => 'forum.' . $type,
				'scope_id'    => $post->get('id'),
				'anonymous'   => $post->get('anonymous', 0),
				'description' => $desc,
				'details'     => array(
					'thread' => $post->get('thread'),
					'url'    => Route::url($url)
				)
			],
			'recipients' => $recipients
		]);

		// Set the redirect
		App::redirect(
			Route::url($url), // . '#c' . $model->id),
			$message,
			'passed'
		);
	}

	/**
	 * Email the mentioned users with a PHP html template
	 *
	 * @return  void
	 */
	public function emailToAllMentionedUsersInGroup($emails, $comment, $url, $postAuthor, $groupAlias, $groupTitle) 
	{
		$from = array();
		$from['name']  = Config::get('sitename') . ' ' . Lang::txt(strtoupper($this->_name));
		$from['email'] = Config::get('mailfrom');

		$subject = $postAuthor . " mentioned you on a group thread from " . $groupTitle;

		// BUILDING THE EMAIL TEMPLATE
		$eView = new \Hubzero\Mail\View(array(
			'base_path'	=> __DIR__,
			'name'		=> 'email',
			'layout'	=> 'mentions_html'
		));

		$eView->comment = $comment;
		$eView->commentNoTags = strip_tags($comment);
		$eView->postLink = $url;
		$eView->postAuthor = $postAuthor;
		$eView->groupTitle = $groupTitle;
		$eView->groupAlias = $groupAlias;

		$html = $eView->loadTemplate(false);
		$html = str_replace("\n", "\r\n", $html);

		// Create NEW message object and send
		$message = new Qubeshub\Mail\Message();
		$message->setSubject($subject)
			->addFrom($from['email'], $from['name'])
			->setTo($from['email'])
			->setBcc($emails)
			->addPart($html, 'text/html')
			->send();

		return true;
	}

	/**
	 * Get email recipient IDs
	 *
	 * @param   object  $category
	 * @return  array
	 */
	protected function _getEmailRecipientIds($category)
	{
		$userIDsToEmail = array();
		$memberoptions = $this->_loadMemberOptions();
		$categorySubscriptionsEnabled = Component::params('com_groups')->get('enable_forum_email_categories', 0);
		$users = $this->_loadExistingUsers($this->members);

		foreach ($users as $user)
		{
			$userId = $user->get('id');

			$sendEmail = $this->_shouldUserReceiveEmail($userId, $memberoptions, $categorySubscriptionsEnabled, $category);

			if ($sendEmail == 1)
			{
				$userIDsToEmail[] = $userId;
			}
		}

		return $userIDsToEmail;
	}

	/**
	 * Load required model
	 *
	 * @return  bool
	 */
	protected function _loadMemberOptions()
	{
		$memberoptions = false;

		if (file_exists(PATH_APP . DS . 'plugins' . DS . 'groups' . DS . 'memberoptions' . DS . 'models' . DS . 'memberoption.php'))
		{
			include_once PATH_APP . DS . 'plugins' . DS . 'groups' . DS . 'memberoptions' . DS . 'models' . DS . 'memberoption.php';
			$memberoptions = true;
		}

		return $memberoptions;
	}

	/**
	 * Get a list of User objects from group membership
	 *
	 * Note: filters out blocked and unapproved accounts
	 *
	 * @param   array  $userIds
	 * @return  array
	 */
	protected function _loadExistingUsers($userIds)
	{
		$users = array_map(function($userId)
		{
			return User::getInstance($userId);
		}, $userIds);

		$existingUsers = array_filter($users, function($user)
		{
			return ($user->get('id') && !$user->get('block') && $user->get('approved') > 0);
		});

		return $existingUsers;
	}

	/**
	 * Get a list of User objects from group membership
	 *
	 * Note: filters out blocked and unapproved accounts
	 *
	 * @param   integer  $userId
	 * @param   object   $memberoptions
	 * @param   bool     $categorySubscriptionsEnabled
	 * @param   object   $category
	 * @return  array
	 */
	protected function _shouldUserReceiveEmail($userId, $memberoptions, $categorySubscriptionsEnabled, $category)
	{
		$categoryId = $category->get('id');

		if ($categorySubscriptionsEnabled)
		{
			$usersCategory = $category->usersCategories()
				->whereEquals('category_id', $categoryId)
				->whereEquals('user_id', $userId)
				->row();

			$sendEmail = $usersCategory->isNew() ? 0 : 1;
		}
		else if ($memberoptions)
		{
			$groupId = $this->group->get('gidNumber');
			$usersGroupSettings = Plugins\Groups\Memberoptions\Models\Memberoption::oneByUserAndOption(
				$groupId,
				$userId,
				'receive-forum-email'
			);

			$sendEmail = $usersGroupSettings->get('optionvalue', 0);
		}

		return $sendEmail;
	}

	/**
	 * Remove a thread
	 *
	 * @return  void
	 */
	public function deletethread()
	{
		$section  = Request::getString('section', '');
		$category = Request::getString('category', '');

		$redirect = Route::url($this->base . '&scope=' . $section . '/' . $category)  . '?' . $_SERVER['QUERY_STRING'];

		// Is the user logged in?
		// Login check is handled in the onGroup() method
		/*if (User::isGuest())
		{
			App::redirect(
				$redirect,
				Lang::txt('PLG_GROUPS_FORUM_LOGIN_NOTICE'),
				'warning'
			);
		}*/

		// Incoming
		$id = Request::getInt('thread', 0);

		// Load the post
		$post = Post::oneOrFail($id);

		// Make the sure the category exist
		if (!$post->get('id'))
		{
			App::redirect(
				$redirect,
				Lang::txt('PLG_GROUPS_FORUM_MISSING_ID'),
				'error'
			);
		}

		// Check if user is authorized to delete entries
		$this->_authorize('thread', $id);

		if (!$this->params->get('access-delete-thread') && $post->get('created_by') != User::get('id'))
		{
			App::redirect(
				$redirect,
				Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'),
				'warning'
			);
		}

		// Trash the post
		// Note: this will carry through to all replies
		//       and attachments
		$post->set('state', $post::STATE_DELETED);

		if (!$post->save())
		{
			Notify::error($post->getError());
		}
		else
		{
			// Record the activity
			$recipients = array(
				['group', $this->group->get('gidNumber')],
				['forum.' . $this->forum->get('scope'), $this->forum->get('scope_id')]
			);
			foreach ($this->group->get('managers') as $recipient)
			{
				$recipients[] = ['user', $recipient];
			}

			$url = $post->link();

			Event::trigger('system.logActivity', [
				'activity' => [
					'action'      => 'deleted',
					'scope'       => 'forum.thread',
					'scope_id'    => $post->get('thread'),
					'description' => Lang::txt('PLG_GROUPS_FORUM_THREAD_DELETED', '<a href="' . Route::url($url) . '">' . $post->get('title') . '</a>'),
					'details'     => array(
						'thread' => $post->get('thread'),
						'url'    => Route::url($url)
					)
				],
				'recipients' => $recipients
			]);
		}

		// Redirect to main listing
		App::redirect(
			$redirect,
			Lang::txt('PLG_GROUPS_FORUM_THREAD_DELETED'),
			'passed'
		);
	}

	/**
	 * Uploads a file to a given directory and returns an attachment string
	 * that is appended to report/comment bodies
	 *
	 * @param   integer  $thread_id  Directory to upload files to
	 * @param   integer  $post_id    Post ID
	 * @return  boolean
	 */
	public function upload($thread_id, $post_id)
	{
		// Check if they are logged in
		if (User::isGuest())
		{
			return false;
		}

		if (!$thread_id)
		{
			$this->setError(Lang::txt('PLG_GROUPS_FORUM_NO_UPLOAD_DIRECTORY'));
			return false;
		}

		// Instantiate an attachment record
		$attachment = Attachment::oneOrNew(Request::getInt('attachment', 0));
		$attachment->set('description', trim(Request::getString('description', '')));
		$attachment->set('parent', $thread_id);
		$attachment->set('post_id', $post_id);
		if ($attachment->isNew())
		{
			$attachment->set('state', Attachment::STATE_PUBLISHED);
		}

		// Incoming file
		$file = Request::getArray('upload', array(), 'files');
		if (!$file || !isset($file['name']) || !$file['name'])
		{
			if ($attachment->get('id'))
			{
				// Only updating the description
				if (!$attachment->save())
				{
					$this->setError($attachment->getError());
					return false;
				}
			}
			return true;
		}

		// Get media config
		$mediaConfig = \Component::params('com_media');

		// Size limit is in MB, so we need to turn it into just B
		$sizeLimit = $mediaConfig->get('upload_maxsize', 10);
		$sizeLimit = $sizeLimit * 1024 * 1024;

		if ($file['size'] > $sizeLimit)
		{
			$max = preg_replace('/<abbr \w+=\\"\w+\\">(\w{1,3})<\\/abbr>/', '$1', \Hubzero\Utility\Number::formatBytes($sizeLimit));

			$this->setError(Lang::txt('PLG_GROUPS_FORUM_ERROR_UPLOADING_FILE_TOO_BIG', $max));
			return false;
		}

		// Ensure file names fit.
		$ext = Filesystem::extension($file['name']);

		// Check that the file type is allowed
		$allowed = array_values(array_filter(explode(',', $mediaConfig->get('upload_extensions'))));

		if (!empty($allowed) && !in_array(strtolower($ext), $allowed))
		{
			$this->setError(Lang::txt('PLG_GROUPS_ERROR_UPLOADING_INVALID_FILE', implode(', ', $allowed)));
			return false;
		}

		// Upload file
		if (!$attachment->upload($file['name'], $file['tmp_name']))
		{
			$this->setError($attachment->getError());
		}

		// Save entry
		if (!$attachment->save())
		{
			$this->setError($attachment->getError());
		}

		return true;
	}

	/**
	 * Serves up files only after passing access checks
	 *
	 * @return  void
	 */
	public function download()
	{
		// Incoming
		$section  = Request::getString('section', '');
		$category = Request::getString('category', '');
		$thread   = Request::getInt('thread', 0);
		$post     = Request::getInt('post', 0);
		$file     = Request::getString('file', '');

		// Check logged in status
		// Login check is handled in the onGroup() method
		/*if (User::isGuest())
		{
			$return = Route::url('index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=forum&scope=' . $section . '/' . $category . '/' . $thread . '/' . $post . '/' . $file);
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($return))
			);
			return;
		}*/

		// Instantiate an attachment object
		if (!$post)
		{
			$attach = Attachment::oneByThread($thread, $file);
		}
		else
		{
			$attach = Attachment::oneByPost($post);
		}

		if (!$attach->get('filename'))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_FILE_NOT_FOUND'));
		}

		// Get the parent ticket the file is attached to
		$post = $attach->post();

		if (!$post->get('id') || $post->get('state') == $post::STATE_DELETED)
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_POST_NOT_FOUND'));
		}

		// Load ACL
		$this->_authorize('thread', $post->get('thread'));

		// Ensure the user is authorized to view this file
		if (!$this->params->get('access-view-thread'))
		{
			$thread = Post::oneOrFail($post->get('thread'));
			if (!in_array($thread->get('access'), User::getAuthorisedViewLevels()))
			{
				App::abort(403, Lang::txt('PLG_GROUPS_FORUM_NOT_AUTH_FILE'));
			}
		}

		// Get the configured upload path
		$filename = $attach->path();

		// Ensure the file exist
		if (!file_exists($filename))
		{
			App::abort(404, Lang::txt('PLG_GROUPS_FORUM_FILE_NOT_FOUND') . ' ' . substr($filename, strlen(PATH_ROOT)));
		}

		// Initiate a new content server and serve up the file
		$server = new \Hubzero\Content\Server();
		$server->filename($filename);
		$server->disposition('inline');
		$server->acceptranges(false); // @TODO fix byte range support

		if (!$server->serve())
		{
			// Should only get here on error
			App::abort(500, Lang::txt('PLG_GROUPS_FORUM_SERVER_ERROR'));
		}

		exit;
	}

	/**
	 * Remove all items associated with the gorup being deleted
	 *
	 * @param   object  $group  Group being deleted
	 * @return  string  Log of items removed
	 */
	public function onGroupDelete($group)
	{
		$log = Lang::txt('PLG_GROUPS_FORUM') . ': ';

		require_once Component::path('com_forum') . DS . 'models' . DS . 'manager.php';

		$sections = Section::all()
			->whereEquals('scope', 'group')
			->whereEquals('scope_id', $group->get('gidNumber'))
			->rows();

		// Do we have any IDs?
		if ($sections->count() > 0)
		{
			// Loop through each ID
			foreach ($sections as $section)
			{
				// Get the categories in this section
				$categories = $section->categories()->rows();

				if ($categories->count())
				{
					// Build a list of category IDs
					foreach ($categories as $category)
					{
						$log .= 'forum.section.' . $section->get('id') . '.category.' . $category->get('id') . '.post' . "\n";
						$log .= 'forum.section.' . $section->get('id') . '.category.' . $category->get('id') . "\n";
					}
				}

				$log .= 'forum.section.' . $section->get('id') . ' ' . "\n";

				// Set the section to "deleted"
				// Set all the categories to "deleted"
				// Set all the threads/posts in all the categories to "deleted"
				$section->set('state', $section::STATE_DELETED);

				if (!$section->save())
				{
					$this->setError($sModel->getError());
					return '';
				}
			}
		}
		else
		{
			$log .= Lang::txt('PLG_GROUPS_FORUM_NO_RESULTS')."\n";
		}

		return $log;
	}

	/**
	 * Display settings
	 *
	 * @return  string
	 */
	private function settings()
	{
		if (User::isGuest())
		{
			$this->setError(Lang::txt('GROUPS_LOGIN_NOTICE'));
			return;
		}

		if ($this->authorized != 'manager' && $this->authorized != 'admin')
		{
			$this->setError(Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'));
			return $this->sections();
		}

		// Output HTML
		$view = $this->view('default', 'settings')
			->set('option', $this->option)
			->set('group', $this->group)
			->set('model', $this->forum)
			->set('config', $this->params)
			->set('authorized', $this->authorized)
			->setErrors($this->getErrors());

		return $view->loadTemplate();
	}

	/**
	 * Save settings
	 *
	 * @return  void
	 */
	private function savesettings()
	{
		if (User::isGuest())
		{
			$this->setError(Lang::txt('GROUPS_LOGIN_NOTICE'));
			return;
		}

		if ($this->authorized != 'manager' && $this->authorized != 'admin')
		{
			$this->setError(Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'));
			return $this->sections();
		}

		// Check for request forgeries
		Request::checkToken();

		$row = \Qubeshub\Plugin\Params::oneByPluginOrNew($this->group->get('gidNumber'), $this->_type, $this->_name);

		// Get parameters
		$params = new \Hubzero\Config\Registry(Request::getArray('params', array(), 'post'));
		$row->set('params', $params->toString());

		// Store new content
		if (!$row->save())
		{
			$this->setError($row->getError());
			return $this->settings();
		}

		// Record the activity
		$recipients = array(
			['group', $this->group->get('gidNumber')],
			['forum.' . $this->forum->get('scope'), $this->forum->get('scope_id')]
		);
		foreach ($this->group->get('managers') as $recipient)
		{
			$recipients[] = ['user', $recipient];
		}

		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => 'updated',
				'scope'       => 'forum.settings',
				'scope_id'    => $row->get('id'),
				'description' => Lang::txt('PLG_GROUPS_FORUM_ACTIVITY_SETTINGS_UPDATED')
			],
			'recipients' => $recipients
		]);

		// Redirect to setting spage
		App::redirect(
			Route::url('index.php?option=com_groups&cn=' . $this->group->get('cn') . '&active=' . $this->_name . '&action=settings'),
			Lang::txt('PLG_GROUPS_FORUM_SETTINGS_SAVED')
		);
	}

	/**
	 * Unsubscribe user from forum emails
	 *
	 * @return void
	 */
	public function unsubscribe()
	{
		$rtrn = 'index.php?option=com_groups&cn=' . $this->group->get('cn') . '&active=' . $this->_name;

		// get the token
		$token = Request::getCmd('t', '');

		// Get the option, if it exists
		$option = Request::getInt('o', 0);

		//token is required
		if ($token == '')
		{
			App::redirect(
				Route::url($rtrn),
				Lang::txt('PLG_GROUPS_FORUM_UNSUBSCRIBE_MISSING_TOKEN'),
				'error'
			);
		}

		// Check if guest and force login
		if (User::isGuest())
	    {
		   $url = Route::url($rtrn . '&action=unsubscribe&t=' . $token . '&o=' . $option, false, true);

		   App::redirect(
			   Route::url('index.php?option=com_users&view=login&return=' . base64_encode($url)),
			   Lang::txt('PLG_GROUPS_FORUM_UNSUBSCRIBE_LOGIN'),
			   'warning'
		   );
		   return;
	   	}

		$encryptor = new \Hubzero\Mail\Token();
		$tokenDetails = $encryptor->decryptEmailToken($token);

		// make sure token details are good (user id and group id are correct)
		if (empty($tokenDetails) || 
	   		!isset($tokenDetails[0]) || User::get('id') != $tokenDetails[0] ||
		    !isset($tokenDetails[1]) || $this->group->get('gidNumber') != $tokenDetails[1])
		{
			App::redirect(
				Route::url($rtrn),
				Lang::txt('PLG_GROUPS_FORUM_UNSUBSCRIBE_INVALID_TOKEN'),
				'error'
			);
		}

		$members = $this->group->get('members');
		// member of group?
		if (!in_array(User::get('id'), $members))
		{
			App::redirect(
				Route::url($rtrn),
				Lang::txt('PLG_GROUPS_FORUM_UNSUBSCRIBE_NOT_MEMBER'),
				'error'
			);
		}

		// need member option lib
		include_once PATH_APP . DS . 'plugins' . DS . 'groups' . DS . 'memberoptions' . DS . 'models' . DS . 'memberoption.php';

		// Find the user's group settings, do they want to get email (0 or 1)?
		$groupMemberOption = Plugins\Groups\Memberoptions\Models\Memberoption::oneByUserAndOption(
			$this->group->get('gidNumber'),
			$tokenDetails[0],
			'receive-forum-email'
		);

		// mark that they dont want to be received anymore.
		$groupMemberOption->set('gidNumber', $this->group->get('gidNumber'));
		$groupMemberOption->set('userid', $tokenDetails[0]);
		$groupMemberOption->set('optionname', 'receive-forum-email');
		$groupMemberOption->set('optionvalue', $option);

		// attempt to update
		if (!$groupMemberOption->save())
		{
			App::redirect(
				Route::url($rtrn),
				($option == 0 ? Lang::txt('PLG_GROUPS_FORUM_UNSUBSCRIBE_UNABLE_TO_UNSUBSCRIBE') : Lang::txt('PLG_GROUPS_FORUM_UNSUBSCRIBE_UNABLE_TO_CHANGE')),
				'error'
			);
		}

		// success
		App::redirect(
			Route::url($rtrn),
			($option == 0 ? Lang::txt('PLG_GROUPS_FORUM_UNSUBSCRIBE_SUCCESSFULLY_UNSUBSCRIBED') : Lang::txt('PLG_GROUPS_FORUM_UNSUBSCRIBE_SUCCESSFULLY_CHANGED'))
		);
	}

	/**
	 * Reorder a record up
	 *
	 * @return  void
	 */
	public function orderup()
	{
		return $this->reorder(-1);
	}

	/**
	 * Reorder a record up
	 *
	 * @return  void
	 */
	public function orderdown()
	{
		return $this->reorder(1);
	}

	/**
	 * Reorder a section
	 *
	 * @param   integer  $dir  Direction
	 * @return  void
	 */
	public function reorder($dir=1)
	{
		if (User::isGuest())
		{
			$this->setError(Lang::txt('GROUPS_LOGIN_NOTICE'));
			return;
		}

		if ($this->authorized != 'manager' && $this->authorized != 'admin')
		{
			$this->setError(Lang::txt('PLG_GROUPS_FORUM_NOT_AUTHORIZED'));
			return $this->sections();
		}

		// Get the section
		$section = Section::all()
			->whereEquals('alias', Request::getString('section', ''))
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->row();

		// Move the section
		if (!$section->move($dir))
		{
			Notify::error($section->getError());
		}
		else
		{
			// Record the activity
			$recipients = array(
				['group', $this->group->get('gidNumber')],
				['forum.' . $this->forum->get('scope'), $this->forum->get('scope_id')],
				['forum.section', $section->get('id')]
			);
			foreach ($this->group->get('managers') as $recipient)
			{
				$recipients[] = ['user', $recipient];
			}

			Event::trigger('system.logActivity', [
				'activity' => [
					'action'      => 'reordered',
					'scope'       => 'forum.section',
					'scope_id'    => $section->get('id'),
					'description' => Lang::txt('PLG_GROUPS_FORUM_ACTIVITY_SECTION_REORDERED', '<a href="' . Route::url($this->base) . '">' . $section->get('title') . '</a>'),
					'details'     => array(
						'title' => $section->get('title'),
						'url'   => Route::url($this->base)
					)
				],
				'recipients' => $recipients
			]);
		}

		// Redirect to main lsiting
		App::redirect(
			Route::url($this->base)
		);
	}
}
