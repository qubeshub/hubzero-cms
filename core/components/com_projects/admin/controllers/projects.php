<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Projects\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Projects\Tables;
use Components\Projects\Models;
use Components\Projects\Models\Orm\Description\Field;
use Components\Projects\Models\Orm\Description\Option;
use Components\Projects\Helpers;
use Component;
use Request;
use Notify;
use Plugin;
use Route;
use Lang;
use User;
use App;

include_once dirname(dirname(__DIR__)) . DS . 'models' . DS . 'orm' . DS . 'description' . DS . 'field.php';
require_once dirname(dirname(dirname(__DIR__))) . DS . 'com_members' . DS . 'helpers' . DS . 'utility.php';

/**
 * Manage projects
 */
class Projects extends AdminController
{
	/**
	 * Executes a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->registerTask('applyDescription', 'saveDescription');
		$this->registerTask('applydescription', 'savedescription');
		$this->registerTask('accesspublic', 'access');
		$this->registerTask('accessprivate', 'access');
		$this->registerTask('feature', 'featured');
		$this->registerTask('unfeature', 'featured');

		// Publishing enabled?
		$this->_publishing = Plugin::isEnabled('projects', 'publications') ? 1 : 0;

		// Include scripts
		$this->_includeScripts();
		
		$this->registerTask('querygrantagency', 'getGrantAgency');

		parent::execute();
	}

	/**
	 * Include necessary scripts
	 *
	 * @return  void
	 */
	protected function _includeScripts()
	{
		// Enable publication management
		if ($this->_publishing)
		{
			require_once Component::path('com_publications') . DS . 'models' . DS . 'publication.php';
		}
	}

	/**
	 * Lists projects
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		$this->view->config = $this->config;

		// Get quotas
		$this->view->defaultQuota = Helpers\Html::convertSize(floatval($this->config->get('defaultQuota', 1)), 'GB', 'b');
		$this->view->premiumQuota = Helpers\Html::convertSize(floatval($this->config->get('premiumQuota', 30)), 'GB', 'b');

		// Get filters
		$this->view->filters = array(
			'limit' => Request::getState(
				$this->_option . '.projects.limit',
				'limit',
				Config::get('list_limit'),
				'int'
			),
			'start' => Request::getState(
				$this->_option . '.projects.limitstart',
				'limitstart',
				0,
				'int'
			),
			'search' => urldecode(Request::getState(
				$this->_option . '.projects.search',
				'search',
				''
			)),
			'filterby' => Request::getState(
				$this->_option . '.projects.filterby',
				'filterby',
				''
			),
			'access' => Request::getState(
				$this->_option . '.projects.access',
				'access',
				0,
				'int'
			),
			/*'private' => Request::getState(
				$this->_option . '.projects.private',
				'private',
				-1,
				'int'
			),*/
			'sortby' => Request::getState(
				$this->_option . '.projects.sort',
				'filter_order',
				'id'
			),
			'sortdir' => Request::getState(
				$this->_option . '.projects.sortdir',
				'filter_order_Dir',
				'DESC'
			),
			'authorized' => true,
			'getowner'   => 1,
			'activity'   => 1,
			'quota'      => Request::getString('quota', 'all', 'post')
		);

		if (!in_array($this->view->filters['filterby'], array('active', 'archived')))
		{
			$this->view->filters['filterby'] = '';
		}

		$this->view->limit = $this->view->filters['limit'];
		$this->view->start = $this->view->filters['start'];

		// Retrieve all records when filtering by quota (no paging)
		if ($this->view->filters['quota'] != 'all')
		{
			$this->view->filters['limit'] = 'all';
			$this->view->filters['start'] = 0;
		}

		$obj = new Tables\Project($this->database);

		// Get records
		$this->view->rows = $obj->getRecords($this->view->filters, 'admin', 0, 1);

		// Get a record count
		$this->view->total = $obj->getCount($this->view->filters, 'admin', 0, 1);

		// Filtering by quota
		if ($this->view->filters['quota'] != 'all' && $this->view->rows)
		{
			$counter = $this->view->total;
			$rows = $this->view->rows;

			for ($i=0, $n=count($rows); $i < $n; $i++)
			{
				$params = new \Hubzero\Config\Registry($rows[$i]->params);
				$quota = $params->get('quota', 0);
				if (($this->view->filters['quota'] == 'premium' && $quota < $this->view->premiumQuota)
				 || ($this->view->filters['quota'] == 'regular' && $quota > $this->view->defaultQuota))
				{
					$counter--;
					unset($rows[$i]);
				}
			}

			$rows = array_values($rows);
			$this->view->total = $counter > 0 ? $counter : 0;

			// Fix up paging after filter
			if (count($rows) > $this->view->limit)
			{
				$k = 0;

				for ($i=0, $n=count($rows); $i < $n; $i++)
				{
					if ($k < $this->view->start || $k >= ($this->view->limit + $this->view->start))
					{
						unset($rows[$i]);
					}

					$k++;
				}
			}

			$this->view->rows = array_values($rows);
		}

		// Set any errors
		if ($this->getError())
		{
			$this->view->setError($this->getError());
		}

		// Check that master path is there
		if ($this->config->get('offroot') && !is_dir($this->config->get('webpath')))
		{
			$this->view->setError(Lang::txt('Master directory does not exist. Administrator must fix this! ') . $this->config->get('webpath'));
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Edit project info
	 *
	 * @return  void
	 */
	public function editTask()
	{
		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		Request::setVar('hidemainmenu', 1);

		// Incoming project ID
		$id = Request::getArray('id', array(0));
		if (is_array($id))
		{
			$id = (!empty($id) ? intval($id[0]) : 0);
		}

		$model = new Models\Project($id);

		if ($id)
		{
			if (!$model->exists())
			{
				Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
				return $this->cancelTask();
			}
		}

		if (!$id)
		{
			Notify::error(Lang::txt('COM_PROJECTS_NOTICE_NEW_PROJECT_FRONT_END'));
			return $this->cancelTask();
		}

		$this->view->config = $this->config;

		// Get project types
		$objT = $model->table('Type');
		$this->view->types = $objT->getTypes();

		// Get activity counts
		$counts = Event::trigger('projects.onProjectCount', array($model, 1));
		$counts = Helpers\Html::getCountArray($counts);
		$counts['activity'] = \Hubzero\Activity\Recipient::all()
			->whereEquals('scope', 'project')
			->whereEquals('scope_id', $model->get('id'))
			->total();
		$this->view->counts = $counts;

		// Get team
		$objO = $model->table('Owner');

		// Sync with system group
		$objO->sysGroup($model->get('alias'), $this->config->get('group_prefix', 'pr-'));

		// Get members and managers
		$this->view->managers  = $objO->getOwnerNames($id, 0, '1', 1);
		$this->view->members   = $objO->getOwnerNames($id, 0, '0', 1);
		$this->view->authors   = $objO->getOwnerNames($id, 0, '2', 1);
		$this->view->reviewers = $objO->getOwnerNames($id, 0, '5', 1);

		// Get last activity
		$log = \Hubzero\Activity\Log::all();

		$l = $log->getTableName();
		$r = \Hubzero\Activity\Recipient::blank()->getTableName();

		$this->view->last_activity = $log
			->join($r, $r . '.log_id', $l . '.id', 'inner')
			->whereEquals($r . '.scope', 'project')
			->whereEquals($r . '.scope_id', $id)
			->order($l . '.created', 'desc')
			->row();

		// Was project suspended?
		$this->view->suspended = false;
		$setup_complete = $this->config->get('confirm_step', 0) ? 3 : 2;
		if ($model->isInactive())
		{
			$log = \Hubzero\Activity\Log::all();

			$l = $log->getTableName();
			$r = \Hubzero\Activity\Recipient::blank()->getTableName();

			$result = $log
				->join($r, $r . '.log_id', $l . '.id', 'inner')
				->whereEquals($r . '.scope', 'project')
				->whereEquals($r . '.scope_id', $id)
				->whereEquals($l . '.description', Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_SUSPENDED'))
				->order($l . '.created', 'desc')
				->row();

			$this->view->suspended = null;
			if ($result)
			{
				$this->view->suspended = $result->details->get('admin');
			}
		}

		// Get project params
		$this->view->params = $model->params;

		$content = Event::trigger('projects.diskspace', array($model, 'local', 'admin'));
		$this->view->diskusage = isset($content[0])  ? $content[0] : '';

		// Set any errors
		if ($this->getError())
		{
			$this->view->setError($this->getError());
		}

		// Get tags on this item
		$cloud = new Models\Tags($id);
		$this->view->tags = $cloud->render('string');

		// Output the HTML
		$this->view->model = $model;
		$this->view->publishing = $this->_publishing;
		$this->view->display();
	}

	/**
	 * Save a project and fall through to edit view
	 *
	 * @return  void
	 */
	public function applyTask()
	{
		$this->saveTask(true);
	}

	/**
	 * Saves a project
	 * Redirects to main listing
	 *
	 * @param   boolean  $redirect
	 * @return  void
	 */
	public function saveTask($redirect = false)
	{
		// Check for request forgeries
		Request::checkToken();

		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Config
		$setup_complete = $this->config->get('confirm_step', 0) ? 3 : 2;

		// Incoming
		$formdata = $_POST;
		$id       = Request::getInt('id', 0);
		$action   = Request::getString('admin_action', '');
		$message  = rtrim(\Hubzero\Utility\Sanitize::clean(Request::getString('message', '')));

		// Load model
		$model = new Models\Project($id);

		if (!$model->exists())
		{
			Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
			return $this->cancelTask();
		}

		$title = $formdata['title'] ? rtrim($formdata['title']) : $model->get('title');
		$type  = isset($formdata['type']) ? $formdata['type'] : 1;
		$model->set('title', $title);
		$model->set('about', rtrim(\Hubzero\Utility\Sanitize::clean($formdata['about'])));
		$model->set('type', $type);
		$model->set('modified', Date::toSql());
		$model->set('modified_by', User::get('id'));
		$model->set('private', Request::getInt('private', $this->config->get('private', 1)));
		$model->set('access', Request::getInt('access', $this->config->get('access', 5)));

		$this->_message = Lang::txt('COM_PROJECTS_SUCCESS_SAVED');

		// Was project suspended?
		$suspended = false;
		if ($model->isInactive())
		{
			$log = \Hubzero\Activity\Log::all();

			$l = $log->getTableName();
			$r = \Hubzero\Activity\Recipient::blank()->getTableName();

			$result = $log
				->join($r, $r . '.log_id', $l . '.id', 'inner')
				->whereEquals($r . '.scope', 'project')
				->whereEquals($r . '.scope_id', $model->get('id'))
				->whereEquals($l . '.description', Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_SUSPENDED'))
				->order($l . '.created', 'desc')
				->row();

			$suspended = null;
			if ($result)
			{
				$suspended = $result->details->get('admin');
			}
		}

		$subject  = Lang::txt('COM_PROJECTS_PROJECT') . ' "' . $model->get('alias') . '" ';
		$sendmail = 0;

		// Get project managers
		$managers = $model->table('Owner')->getIds($id, 1, 1);

		// Admin actions
		if ($action)
		{
			switch ($action)
			{
				case 'delete':
					$model->set('state', 2);
					$what           = Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_DELETED');
					$subject       .= Lang::txt('COM_PROJECTS_MSG_ADMIN_DELETED');
					$this->_message = Lang::txt('COM_PROJECTS_SUCCESS_DELETED');
				break;

				case 'suspend':
					$model->set('state', 0);
					$what           = Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_SUSPENDED');
					$subject       .= Lang::txt('COM_PROJECTS_MSG_ADMIN_SUSPENDED');
					$this->_message = Lang::txt('COM_PROJECTS_SUCCESS_SUSPENDED');
				break;

				case 'reinstate':
					$model->set('state', 1);
					$what = $suspended
						? Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_REINSTATED')
						: Lang::txt('COM_PROJECTS_ACTIVITY_PROJECT_ACTIVATED');
					$subject .= $suspended
						? Lang::txt('COM_PROJECTS_MSG_ADMIN_REINSTATED')
						: Lang::txt('COM_PROJECTS_MSG_ADMIN_ACTIVATED');

					$this->_message = $suspended
						? Lang::txt('COM_PROJECTS_SUCCESS_REINSTATED')
						: Lang::txt('COM_PROJECTS_SUCCESS_ACTIVATED');
				break;
			}

			// Add activity
			$model->recordActivity($what, 0, '', '', 'project', 0, $admin = 1);
			$sendmail = 1;
		}
		elseif ($message)
		{
			$subject .= ' - ' . Lang::txt('COM_PROJECTS_MSG_ADMIN_NEW_MESSAGE');
			$sendmail = 1;
			$this->_message = Lang::txt('COM_PROJECTS_SUCCESS_MESSAGE_SENT');
		}

		if (!$action && !$message)
		{
			$model->recordActivity(Lang::txt('COM_PROJECTS_MSG_ADMIN_UPDATED'), 0, '', '', 'project', 0, $admin = 1);
		}

		// Save changes
		if (!$model->store())
		{
			Notify::error($model->getError());
			return $this->cancelTask();
		}

		// Incoming tags
		$tags = Request::getString('tags', '', 'post');

		// Save the tags
		$cloud = new Models\Tags($model->get('id'));
		$cloud->setTags($tags, User::get('id'), 1);

		// Save params
		$incoming = Request::getArray('params', array());
		if (!empty($incoming))
		{
			foreach ($incoming as $key => $value)
			{
				if ($key == 'quota' || $key == 'pubQuota')
				{
					// convert GB to bytes
					$value = Helpers\Html::convertSize(floatval($value), 'GB', 'b');
				}
				
				if ($key == "grant_agency")
				{
					$grant_agency_id = $this->getGrantAgencyId($value);
					
					if ($grant_agency_id != false && !empty($grant_agency_id))
					{
						$model->saveParam("grant_agency_id", $grant_agency_id);
					}
				}

				$model->saveParam($key, $value);
			}
		}

		// Add members if specified
		$this->model = $model;
		$this->_saveMember();

		// Change ownership
		$this->_changeOwnership();

		// Allow plugins to respond to changes
		Event::trigger('projects.onProjectAfterSave', array($this->model));

		// Send message
		if ($this->config->get('messaging', 0) && $sendmail && count($managers) > 0)
		{
			// Email config
			$from = array();
			$from['name']  = Config::get('sitename') . ' ' . Lang::txt('COM_PROJECTS');
			$from['email'] = Config::get('mailfrom');

			// Html email
			$from['multipart'] = md5(date('U'));

			// Message body
			$eview = new \Hubzero\Mail\View(array(
				'name'   => 'emails',
				'layout' => 'admin_plain'
			));
			$eview->option  = $this->_option;
			$eview->subject = $subject;
			$eview->action  = $action;
			$eview->project = $model;
			$eview->message = $message;

			$body = array();
			$body['plaintext'] = $eview->loadTemplate(false);
			$body['plaintext'] = str_replace("\n", "\r\n", $body['plaintext']);

			// HTML email
			$eview->setLayout('admin_html');
			$body['multipart'] = $eview->loadTemplate();
			$body['multipart'] = str_replace("\n", "\r\n", $body['multipart']);

			// Send HUB message
			Event::trigger('xmessage.onSendMessage', array('projects_admin_notice', $subject, $body, $from, $managers, $this->_option));
		}

		Notify::message($this->_message, 'success');

		// Redirect to edit view?
		if ($redirect)
		{
			App::redirect(Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false));
		}
		else
		{
			App::redirect(Route::url('index.php?option=' . $this->_option, false));
		}
	}

	/**
	 * Save member
	 *
	 * @return  void
	 */
	protected function _saveMember()
	{
		// New member added?
		$members = urldecode(trim(Request::getString('newmember', '', 'post' )));
		$role = Request::getInt('role', 0);

		$mbrs = explode(',', $members);

		foreach ($mbrs as $mbr)
		{
			// Retrieve user's account info
			$profile = User::getInstance(trim($mbr));

			// Ensure we found an account
			if ($profile->get('id'))
			{
				$this->model->table('Owner')->saveOwners(
					$this->model->get('id'),
					User::get('id'),
					$profile->get('id'),
					0,
					$role,
					$status = 1,
					0
				);
			}
		}
	}

	/**
	 * Change ownership
	 *
	 * @return  void
	 */
	protected function _changeOwnership()
	{
		// Incoming
		$user    = Request::getInt('owned_by_user', $this->model->get('owned_by_user'), 'post');
		$group   = Request::getInt('owned_by_group', 0, 'post');

		// Load project owner table class
		$objO = $this->model->table('Owner');
		$objO->loadOwner($this->model->get('id'), $user);

		if (!$objO->id)
		{
			throw new \Exception(Lang::txt('Error loading user'), 404);
		}

		// Change in individual ownership
		if ($user != $this->model->get('owned_by_user'))
		{
			$this->model->set('owned_by_user', $user);
			$this->model->store();

			// Make sure user is manager
			$objO->role = 1;
			$objO->store();
		}

		// Change in group ownership
		if ($group != $this->model->get('owned_by_group'))
		{
			$this->model->set('owned_by_group', $group);
			if (!$group)
			{
				$this->model->set('sync_group', 0);
			}
			$this->model->store();

			// Make sure project lead is affiliated with group
			$objO->groupid = $group;
			$objO->store();
		}
	}

	/**
	 * Sets the access of one or more entries
	 *
	 * @return  void
	 */
	public function accessTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$private = ($this->getTask() == 'accesspublic' ? 0 : 1);
		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			Notify::warning(Lang::txt('COM_PROJECTS_SELECT_ENTRY_TO_CHANGE_PRIVACY'));
			return $this->cancelTask();
		}

		$i = 0;

		foreach ($ids as $id)
		{
			// Update record(s)
			$model = new Models\Project($id);

			if (!$model->exists())
			{
				Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
				continue;
			}

			$model->set('private', $private);
			$model->set('access', ($private ? 5 : 4));

			if (!$model->store())
			{
				Notify::error($model->getError());
				continue;
			}

			// Allow plugins to respond to changes
			Event::trigger('projects.onProjectAfterSave', array($model));

			$i++;
		}

		if ($i)
		{
			Notify::success(Lang::txt('COM_PROJECTS_ITEMS_PRIVACY_CHANGED', $i));
		}

		$this->cancelTask();
	}

	/**
	 * Archive one or more projects
	 *
	 * @return  void
	 */
	public function archiveTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$i = 0;

		// Do we have any IDs?
		if (!empty($ids))
		{
			//foreach group id passed in
			foreach ($ids as $id)
			{
				// Update record(s)
				$model = new Models\Project($id);

				if (!$model->exists())
				{
					Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
					continue;
				}

				$model->set('state', 3);

				if (!$model->store())
				{
					Notify::error($model->getError());
					continue;
				}

				// Allow plugins to respond to changes
				Event::trigger('projects.onProjectAfterSave', array($model));

				$i++;
			}

			// Output messsage and redirect
			if ($i)
			{
				Notify::success(Lang::txt('COM_PROJCTS_SUCCESS_ARCHIVED', $i));
			}
		}

		$this->cancelTask();
	}

	/**
	 * Unarchive one or more projects
	 *
	 * @return  void
	 */
	public function unarchiveTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$i = 0;

		// Do we have any IDs?
		if (!empty($ids))
		{
			//foreach group id passed in
			foreach ($ids as $id)
			{
				// Update record(s)
				$model = new Models\Project($id);

				if (!$model->exists())
				{
					Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
					continue;
				}

				$model->set('state', 1);

				if (!$model->store())
				{
					Notify::error($model->getError());
					continue;
				}

				// Allow plugins to respond to changes
				Event::trigger('projects.onProjectAfterSave', array($model));

				$i++;
			}

			// Output messsage and redirect
			if ($i)
			{
				Notify::success(Lang::txt('COM_PROJCTS_SUCCESS_UNARCHIVED', $i));
			}
		}

		$this->cancelTask();
	}

	/**
	 * Set featured state of project(s)
	 *
	 * @return  void
	 */
	public function featuredTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getArray('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$i = 0;

		//foreach group id passed in
		foreach ($ids as $id)
		{
			// Update record(s)
			$model = new Models\Project($id);

			if (!$model->exists())
			{
				Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
				continue;
			}

			$model->set('featured', $this->getTask() == 'feature' ? 1 : 0);

			if (!$model->store())
			{
				Notify::error($model->getError());
				continue;
			}

			// Allow plugins to respond to changes
			Event::trigger('projects.onProjectAfterSave', array($model));

			$i++;
		}

		// Output messsage and redirect
		if ($i)
		{
			Notify::success(Lang::txt('COM_PROJECTS_SUCCESS_' . strtoupper($this->getTask()), $i));
		}

		$this->cancelTask();
	}

	/**
	 * Erases all project information (to be used for test projects only)
	 *
	 * @return  void
	 */
	public function eraseTask()
	{
		$id = Request::getInt('id', 0);
		$permanent = 1;

		// Initiate extended database class
		$obj = new Tables\Project($this->database);
		if (!$id or !$obj->loadProject($id))
		{
			Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
			return $this->cancelTask();
		}

		// Get project group
		$group_prefix = $this->config->get('group_prefix', 'pr-');
		$prGroup = $group_prefix . $obj->alias;

		// Store project info
		$alias = $obj->alias;
		$identifier = $alias;

		// Delete project
		$obj->delete();

		// Erase all owners
		$objO = new Tables\Owner($this->database);
		$objO->removeOwners($id, '', 0, $permanent, '', $all = 1);

		// Erase owner group
		$group = new \Hubzero\User\Group();
		$group->read($prGroup);
		if ($group)
		{
			$group->delete();
		}

		// Erase all comments
		$objC = new Tables\Comment($this->database);
		$objC->deleteProjectComments($id, $permanent);

		// Erase all activities
		$objA = new Tables\Activity($this->database);
		$objA->deleteActivities($id, $permanent);

		// Erase all todos
		$objTD = new Tables\Todo($this->database);
		$objTD->deleteTodos($id, '', $permanent);

		// Erase all blog entries
		$objB = new Tables\Blog($this->database);
		$objB->deletePosts($id, $permanent);

		// Erase all notes
		if (file_exists(Component::path('com_wiki') . DS . 'models' . DS . 'page.php'))
		{
			include_once Component::path('com_wiki') . DS . 'models' . DS . 'page.php';

			// Get all notes
			$this->database->setQuery(
				"SELECT DISTINCT p.id FROM `#__wiki_pages` AS p
				WHERE p.scope_id=" . $this->database->quote($id)
				. " AND p.scope=" . $this->database->quote('project')
			);
			$notes = $this->database->loadObjectList();

			if ($notes)
			{
				foreach ($notes as $note)
				{
					$page = \Components\Wiki\Models\Page::oneOrFail($note->id);
					// Finally, delete the page itself
					$page->destroy();
				}
			}
		}

		// Erase all files, remove files repository
		if ($alias)
		{
			// Delete base dir for .git repos
			$dir     = $alias;
			$prefix  = $this->config->get('offroot', 0) ? '' : PATH_CORE;
			$repodir = DS . trim($this->config->get('webpath'), DS);
			$path    = $prefix . $repodir . DS . $dir;

			if (is_dir($path))
			{
				Filesystem::deleteDirectory($path);
			}

			// Delete images/preview directories
			$webdir  = DS . trim($this->config->get('imagepath', '/site/projects'), DS);
			$webpath = PATH_APP . $webdir . DS . $dir;

			if (is_dir($webpath))
			{
				Filesystem::deleteDirectory($webpath);
			}
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option='.$this->_option, false),
			Lang::txt('COM_PROJECTS_PROJECT') . ' #' . $id . ' (' . $alias . ') ' . Lang::txt('COM_PROJECTS_PROJECT_ERASED')
		);
	}

	/**
	 * Add and commit untracked/changed files
	 *
	 * This is helpful in case git add/commit failed during file upload
	 *
	 * @return  void
	 */
	public function gitaddTask()
	{
		$id   = Request::getInt('id', 0);
		$file = Request::getString('file', '');

		// Initiate extended database class
		$obj = new Tables\Project($this->database);
		if (!$id or !$obj->loadProject($id))
		{
			Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
			return $this->cancelTask();
		}

		$url = Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false);

		if (!$file)
		{
			App::redirect(
				$url,
				Lang::txt('Please specify a file/directory path to add and commit into project'),
				'error'
			);
			return;
		}

		// Delete base dir for .git repos
		$prefix  = $this->config->get('offroot', 0) ? '' : PATH_APP;
		$repodir = trim($this->config->get('webpath'), DS);
		$path    = $prefix . DS . $repodir . DS . $obj->alias . DS . 'files';

		if (!is_file($path . DS . $file))
		{
			Notify::error(Lang::txt('Error: File not found in the project, cannot add and commit'));
			return $this->cancelTask();
		}

		// Git helper
		require_once dirname(dirname(__DIR__)) . DS . 'helpers' . DS . 'githelper.php';
		$gitHelper = new Helpers\Git($path);

		$commitMsg = '';

		// Git add & commit
		$gitHelper->gitAdd($file, $commitMsg);
		$gitHelper->gitCommit($commitMsg);

		Notify::success(Lang::txt('File checked into project Git repo'));

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false)
		);
	}

	/**
	 * Optimize git repo
	 *
	 * @return  void
	 */
	public function gitgcTask()
	{
		$id = Request::getInt('id', 0);

		// Get repo model
		require_once dirname(dirname(__DIR__)) . DS . 'models' . DS . 'repo.php';

		$project = new Models\Project($id);
		if (!$project->exists())
		{
			Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
			return $this->cancelTask();
		}

		$repo = new \Components\Projects\Models\Repo($project, 'local');
		$params = array(
			'path' => $repo->get('path'),
			'adv'  => true
		);
		$repo->call('optimize', $params);

		Notify::success(Lang::txt('Git repo optimized'));

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false)
		);
	}

	/**
	 * Unlock sync and view sync log for project
	 *
	 * @return  void
	 */
	public function fixsyncTask()
	{
		$id = Request::getInt('id', 0);
		$service = 'google';

		// Initiate extended database class
		$obj = new Tables\Project($this->database);
		if (!$id or !$obj->loadProject($id))
		{
			Notify::error(Lang::txt('COM_PROJECTS_NOTICE_ID_NOT_FOUND'));
			return $this->cancelTask();
		}

		// Unlock sync
		$obj->saveParam($id, $service . '_sync_lock', '');

		// Get log file
		$repodir = Helpers\Html::getProjectRepoPath($obj->alias, 'logs');
		$sfile   = $repodir . DS . 'sync.' . Date::format('Y-m') . '.log';

		if (file_exists($sfile))
		{
			// Serve up file
			$server = new \Hubzero\Content\Server();
			$server->filename($sfile);
			$server->disposition('attachment');
			$server->acceptranges(false);
			$server->saveas('sync.' . Date::format('Y-m') . '.txt');
			$result = $server->serve_attachment($sfile, 'sync.' . Date::format('Y-m') . '.txt', false);
			exit;
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&task=edit&id=' . $id, false),
			Lang::txt('Sync log unavailable')
		);
	}

	/**
	 * Display a form for customizing the description
	 * 
	 * @return  void
	 */
	public function customizeDescriptionTask()
	{
		// Shamelessly ripped off from Shawn

		// Authorization check
		if (!User::authorise('core.manage', $this->_option)
		 && !User::authorise('core.admin', $this->_option))
		{
			return $this->cancelTask();
		}

		// Fields that we have, ordered
		$fields = Field::all()
			->including(['options', function ($option){
				$option
					->select('*')
					->ordered();
			}])
			->ordered()
			->rows();

		$this->view
			->set('fields', $fields)
			->setLayout('description')
			->display();
	}

	/**
	 * Save custom description schema
	 * 
	 * @return  void
	 */
	public function applyDescriptionTask()
	{
		$this->saveDescriptionTask();
	}

	/**
	 * Save custom description schema
	 * 
	 * @return  void
	 */
	public function saveDescriptionTask()
	{
		// Check for request forgeries
		Request::checkToken();

		if (!User::authorise('core.manage', $this->_option)
		 && !User::authorise('core.admin', $this->_option))
		{
			return $this->cancelTask();
		}

		// Incoming data
		$description = json_decode(Request::getString('descriptionFields', '{}', 'post', 'none', 2));

		// Get the old schema
		$fields = Field::all()
			->including(['options', function ($option){
				$option
					->select('*')
					->ordered();
			}])
			->ordered()
			->rows();

		// Collect old fields
		$oldFields = array();
		foreach ($fields as $oldField)
		{
			$oldFields[$oldField->get('id')] = $oldField;
		}

		foreach ($description->fields as $i => $element)
		{
			$field = null;

			$fid = (isset($element->field_id) ? $element->field_id : 0);

			if ($fid && isset($oldFields[$fid]))
			{
				$field = $oldFields[$fid];

				// Remove found fields from the list
				// Anything remaining will be deleted
				unset($oldFields[$fid]);
			}

			$field = ($field ?: Field::oneOrNew($fid));
			$field->set(array(
				'type'          => (string) $element->field_type,
				'label'         => (string) $element->label,
				'name'          => (string) $element->name,
				'description'   => (isset($element->field_options->description) ? (string) $element->field_options->description : ''),
				'ordering'      => ($i + 1),
				'access'        => (isset($element->access) ? (int) $element->access : 0),
				'option_other'  => (isset($element->field_options->include_other_option) ? (int) $element->field_options->include_other_option : ''),
				'option_blank'  => (isset($element->field_options->include_blank_option) ? (int) $element->field_options->include_blank_option : ''),
				'action_create' => (isset($element->create) ? (int) $element->create : 1),
				'action_update' => (isset($element->update) ? (int) $element->update : 1),
				'action_edit'   => (isset($element->edit)   ? (int) $element->edit   : 1)
			));

			if ($field->get('type') == 'dropdown')
			{
				$field->set('type', 'select');
			}
			if ($field->get('type') == 'paragraph')
			{
				$field->set('type', 'textarea');
			}

			if (!$field->save())
			{
				Notify::error($field->getError());
				continue;
			}

			// Collect old options
			$oldOptions = array();
			foreach ($field->options as $oldOption)
			{
				$oldOptions[$oldOption->get('id')] = $oldOption;
			}

			// Does this field have any set options?
			if (isset($element->field_options->options))
			{
				foreach ($element->field_options->options as $k => $opt)
				{
					$option = null;

					$oid = (isset($opt->field_id) ? $opt->field_id : 0);

					if ($oid && isset($oldOptions[$oid]))
					{
						$option = $oldOptions[$oid];

						// Remove found options from the list
						// Anything remaining will be deleted
						unset($oldOptions[$oid]);
					}

					$dependents = array();
					if (isset($opt->dependents))
					{
						$dependents = explode(',', trim($opt->dependents));
						$dependents = array_map('trim', $dependents);
						foreach ($dependents as $j => $dependent)
						{
							if (!$dependent)
							{
								unset($dependents[$j]);
							}
						}
					}

					$option = ($option ?: Option::oneOrNew($oid));
					$option->set(array(
						'field_id'   => $field->get('id'),
						'label'      => (string) $opt->label,
						'value'      => (isset($opt->value)   ? (string) $opt->value : ''),
						'checked'    => (isset($opt->checked) ? (int) $opt->checked : 0),
						'ordering'   => ($k + 1),
						'dependents' => json_encode($dependents)
					));

					if (!$option->save())
					{
						Notify::error($option->getError());
						continue;
					}
				}
			}

			// Remove any options not in the incoming list
			foreach ($oldOptions as $option)
			{
				if (!$option->destroy())
				{
					Notify::error($option->getError());
					continue;
				}
			}
		}

		// Remove any fields not in the incoming list
		foreach ($oldFields as $field)
		{
			if (!$field->destroy())
			{
				Notify::error($field->getError());
				continue;
			}
		}

		// Set success message
		Notify::success(Lang::txt('COM_PROJECTS_DESCRIPTION_SCHEMA_SAVED'));

		// Drop through to edit form?
		if ($this->getTask() == 'applydescription')
		{
			// Redirect, instead of falling through, to avoid caching issues
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=customizeDescription', false)
			);
		}

		// Redirect
		$this->cancelTask();
	}
	
	/**
	 * Querying the grant agency name based on the input value
	 *
	 * @return  array   grant agency names that match the input value
	 */
	public function getGrantAgencyTask()
	{
		$term = trim(Request::getString('term', ''));
		$term = \Components\Members\Helpers\Utility::escapeSpecialChars($term);
		
		$verNum = \Component::params('com_members')->get('rorApiVersion');
		
		if (!empty($verNum))
		{
			$queryURL = "https://api.ror.org/$verNum/organizations?filter=types:funder&query.advanced=names.value:" . urlencode($term);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $queryURL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$result = curl_exec($ch);
			
			if (!$result)
			{
				return false;
			}
			
			$info = curl_getinfo($ch);
			
			$code = $info['http_code'];
			
			if (($code != 201) && ($code != 200))
			{
				return false;
			}
			
			$agencies = [];
			
			$resultObj = json_decode($result);
			
			foreach ($resultObj->items as $orgObj)
			{
				foreach ($orgObj->names as $nameObj)
				{
					if ($nameObj->lang == "en" && !in_array($nameObj->value, $agencies))
					{
						$agencies[] = $nameObj->value;
					}
				}
			}
			
			curl_close($ch);
			
			echo json_encode($agencies);
			exit();
		}
	}
	
	/**
	 * Get the grant agency identifier on CrossRef
	 * @param   string   $grantAgency
	 *
	 * @return  string   grant agency identifier
	 */
	public function getGrantAgencyId($grantAgency)
	{
		$agency = trim($grantAgency);
		$agencyQry = \Components\Members\Helpers\Utility::escapeSpecialChars($agency);
		
		$verNum = \Component::params('com_members')->get('rorApiVersion');
		
		if (!empty($verNum))
		{
			$queryURL = "https://api.ror.org/$verNum/organizations?filter=types:funder&query.advanced=names.value:" . urlencode($agencyQry);
					
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $queryURL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$result = curl_exec($ch);
			
			if (!$result)
			{
				return false;
			}
			
			$info = curl_getinfo($ch);
			
			$code = $info['http_code'];
			
			if (($code != 201) && ($code != 200))
			{
				return false;
			}
			
			$resultObj = json_decode($result);
			
			foreach ($resultObj->items as $orgObj)
			{
				foreach ($orgObj->names as $nameObj)
				{
					if (strcmp($nameObj->value, $agency) == 0)
					{
						curl_close($ch);
						return $orgObj->id;
					}
				}
			}
			
			curl_close($ch);
			return "";
		}
	}
}
