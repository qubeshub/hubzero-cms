<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Installer\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Installer\Admin\Helpers\Cli;
use Components\Installer\Admin\Models\Custom_extensions;
use Request;
use Config;
use Notify;
use Event;
use Route;
use App;

include_once dirname(__DIR__) . '/helpers/cli.php';
include_once dirname(__DIR__) . DS . 'models' . DS . 'custom_extensions.php';

/**
 * Custom Exentions Controller
 */
class Customexts extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->registerTask('unpublish', 'publish');
		$this->registerTask('publish', 'publish');

		parent::execute();
	}

	/**
	 * Display a list of extensions
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Include the component HTML helpers.
		Html::addIncludePath(dirname(__DIR__) . '/helpers/html');

		$limit = Request::getState(
			$this->_option . '.' . $this->_controller . '.limit',
			'limit',
			\Config::get('list_limit'),
			'int'
		);
		$start = Request::getState(
			$this->_option . '.' . $this->_controller . '.limitstart',
			'limitstart',
			0,
			'int'
		);

		$filters = array(
			'search' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.search',
				'filter_search',
				''
			)),
			'client_id' => Request::getState(
				$this->_option . '.' . $this->_controller . '.client_id',
				'filter_location',
				''
			),
			'status' => Request::getState(
				$this->_option . '.' . $this->_controller . '.status',
				'filter_status',
				'',
				''
			),
			'type' => Request::getState(
				$this->_option . '.' . $this->_controller . '.type',
				'filter_type',
				'',
				''
			),
			'group' => Request::getState(
				$this->_option . '.' . $this->_controller . '.group',
				'filter_group',
				'',
				''
			),
			// Get sorting variables
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'name'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);

		$entries = Custom_extensions::all();

		$e = $entries->getTableName();

		$entries
			->select($e . '.*');

		// Filter by search in id
		if (!empty($filters['search']))
		{
			if (stripos($filters['search'], 'id:') === 0)
			{
				$entries->whereEquals($e . '.extension_id', (int) substr($filters['search'], 3));
			}
			else
			{
				$entries->whereLike($e . '.name', $filters['search'], 1)
					->orWhereLike($e . '.folder', $filters['search'], 1)
					->resetDepth();
			}
		}

		if (isset($filters['client_id']) && $filters['client_id'] != '')
		{
			$entries->whereEquals('client_id', (int)$filters['client_id']);
		}

		if (isset($filters['status']) && $filters['status'] != '')
		{
			if ($filters['status'] == '2')
			{
				$entries->whereEquals('protected', 1);
			}
			else
			{
				$entries->whereEquals('enabled', (int)$filters['status']);
			}
		}


		if (isset($filters['type']) && $filters['type'] != '')
		{
			$entries->whereEquals('type', $filters['type']);
		}

		if (isset($filters['group']) && $filters['group'])
		{
			$entries->whereEquals('folder', $filters['group']);
		}

		// Get records
		if ($filters['sort'] == 'name' || (!empty($filters['search']) && stripos($filters['search'], 'id:') !== 0))
		{
			$rows = $entries->rows();

			$lang = App::get('language');

			$result = array();
			foreach ($rows as $i => $item)
			{
				if (!empty($filters['search']))
				{
					if (!preg_match("/" . $filters['search'] . "/i", $item->name))
					{
						unset($result[$i]);
					}
				}

				$result[$i] = $item;
			}

			\Hubzero\Utility\Arr::sortObjects($result, $filters['sort'], $filters['sort_Dir'] == 'desc' ? -1 : 1, true, $lang->getLocale());

			$total = count($result);

			if ($total < $start)
			{
				$start = 0;
			}

			$rows = array_slice($result, $start, $limit ? $limit : null);
		}
		else
		{
			$total = with(clone $entries)->total();

			$rows = $entries
				->order($filters['sort'], $filters['sort_Dir'])
				->limit($limit)
				->start($start)
				->rows();
		}

		$pagination = new \Hubzero\Pagination\Paginator($total, $start, $limit);

		// Check if there are no matching items
		if (!count($rows))
		{
			Notify::warning(Lang::txt('COM_INSTALLER_CUSTOMEXTS_MSG_MANAGE_NO_EXTENSIONS'));
		}

		// Output the HTML
		$this->view
			->set('rows', $rows)
			->set('pagination', $pagination)
			->set('filters', $filters)
			->display();
	}

	/**
	 * Edit or create
	 *
	 * @return  void
	 */
	public function editTask($row=null)
	{
		if (!User::authorise('core.edit', $this->_option)
		&& !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// This is a flag to disable the main menu. This makes sure the user
		// doesn't navigate away while int he middle of editing an entry.
		// To leave the form, one must explicitly call the "cancel" task.
		Request::setVar('hidemainmenu', 1);

		if (!is_object($row))
		{
			// Grab the incoming ID and load the record for editing
			//
			// IDs can come arrive in two formts: single integer or
			// an array of integers. If it's the latter, we'll only take
			// the first ID in the list.
			$id = Request::getArray('id', array(0));
			if (is_array($id) && !empty($id))
			{
				$id = $id[0];
			}

			$row = Custom_extensions::oneOrNew($id);
		}

		// If this is a new record, we'll set the creator data
		if ($row->isNew())
		{
			$row->set('created_by', User::get('id'));
			$row->set('created', Date::of('now')->toSql());
		}

		$row->set('modified_by', User::get('id'));
		$row->set('modified', Date::of('now')->toSql());
		$row->set('modified', null);



		// Get the form.
		\Hubzero\Form\Form::addFormPath(dirname(__DIR__) . '/models/forms');
		// \Hubzero\Form\Form::addFieldPath(dirname(__DIR__) . '/models/fields');
		$form = new \Hubzero\Form\Form('customexts');
		$form->loadFile(dirname(__DIR__) . '/models/forms/customexts.xml', false, '//form');

		// Check the session for previously entered form data.
		$data = User::getState($this->_option . '.data', array());

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind($data);
		}

		// Output the view
		$this->view
			->set('row', $row)
			->setLayout('edit')
			->set('form', $form)
			->display();
	}

	/**
	 * Save an entry
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		Request::checkToken();

		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$fields = Request::getArray('fields', array(), 'post', 'none', 2);

		// Initiate extended database class
		$model = Custom_extensions::oneOrNew($fields['extension_id'])->set($fields);

		// Get parameters
		$params = Request::getArray('params', array(), 'post');

		$p = $model->params;

		if (is_array($params))
		{
			foreach ($params as $k => $v)
			{
				$p->set($k, $v);
			}
			$model->set('params', $p->toString());
		}

		// pluralize types
		switch ($model->type) {
			case "component":
				$path_type = "components";
				break;
			case "file":
				$path_type = "files";
				break;
			case "language":
				$path_type = "languages";
				break;
			case "library":
				$path_type = "libraries";
				break;
			case "module":
				$path_type = "modules";
				break;
			case "plugin":
				$path_type = "plugins";
				break;
			case "template":
				$path_type = "templates";
				break;
		}

		// Set path
		if ($model->folder)
		{
			$model->set('path', PATH_APP . "/" . $path_type . "/" . $model->folder . "/"  . $model->alias);
		}
		else if ($model->type == "non-standard")
		{
			$model->set('path', PATH_APP . "/" . $model->alias);
		}
		else {
			$model->set('path', PATH_APP . "/" . $path_type . "/"  . $model->alias);
		}

		// Validate and save the data
		if (!$model->save())
		{
			Notify::error($model->getError());
			return $this->editTask($model);
		}

		Notify::success(Lang::txt('COM_INSTALLER_CUSTOMEXTS_SAVED'));

		if ($this->getTask() == 'apply')
		{
			return $this->editTask($row);
		}

		$this->cancelTask();
	}

	/**
	 * Enable/Disable an extension
	 *
	 * @return  void
	 */
	public function publishTask()
	{
		// Check for request forgeries.
		Request::checkToken() or exit(Lang::txt('JINVALID_TOKEN'));

		// Initialise variables.
		$ids    = Request::getArray('cid', array());
		$values = array('update' => 1, 'publish' => 1, 'unpublish' => 0);
		$incomingtask   = $this->getTask();
		$value  = \Hubzero\Utility\Arr::getValue($values, $incomingtask, 0, 'int');

		if (empty($ids))
		{
			App::abort(500, Lang::txt('COM_INSTALLER_CUSTOMEXTS_ERROR_NO_EXTENSIONS_SELECTED'));
		}
		else
		{
			$success = 0;

			foreach ($ids as $id)
			{
				$model = Custom_extensions::oneOrFail($id);

				if ($value)
				{
					$pieces = explode("/", $model->path);
					$repodir = array_pop($pieces);
					$extdir = implode("/", $pieces);

					if (is_dir($extdir . '/__' . $repodir))
					{
						$museCmd = 'renameRepo currPath=' . $extdir . '/__' . $repodir . ' targetPath=' . $model->path;

						$rename_response = Cli::call($museCmd, $task='repository');
						$rename_response = json_decode($rename_response);
					}

					if (!$model->publish())
					{
						Notify::error($model->getError());
						continue;
					}
				}
				else
				{
					if (is_dir($model->path))
					{
						$pieces = explode("/", $model->path);
						$repoPath = array_pop($pieces);
						$extdir = implode("/", $pieces);

						$museCmd = 'renameRepo currPath=' . $model->path . ' targetPath=' . $extdir . '/__' . $repoPath;

						$rename_response = Cli::call($museCmd, $task='repository');
						$rename_response = json_decode($rename_response);
					}

					if (!$model->unpublish())
					{
						Notify::error($model->getError());
						continue;
					}
				}

				$success++;
			}

			// Change the state of the records.
			if ($success)
			{
				if ($value == 1)
				{
					$ntext = 'COM_INSTALLER_CUSTOMEXTS_N_EXTENSIONS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = 'COM_INSTALLER_CUSTOMEXTS_N_EXTENSIONS_UNPUBLISHED';
				}

				if ($incomingtask == "update")
				{
					return;
				}

				Notify::success(Lang::txts($ntext, $success));
			}
		}

		$this->cancelTask();
	}

	/**
	 * Fetch from Git
	 *
	 * @return  void
	 */
	public function updateTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$ids = Request::getArray('cid', array());

		// Get the single ID we're working with
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		// empty list?
		if (empty($ids))
		{
			return $this->cancelTask();
		}

		// Publish extnetion if not to prevent cloning a new dir.
		$this->publishTask();

		// vars to hold results of pull
		$success = array();
		$failed  = array();

		// loop through each extension and pull code from repos
		foreach ($ids as $id)
		{
			$extension = Custom_extensions::oneOrNew($id);

			// Do we have a git repo?  If not clone the specified repo.
			if (!is_dir($extension->path . DS . '.git'))
			{
				if ($extension->get('apikey'))
				{
					$newURL = "https://oauth2:" . $extension->get('apikey') . "@" . parse_url($extension->get('url'), PHP_URL_HOST) . parse_url($extension->get('url'), PHP_URL_PATH);
					$museCmd = 'cloneRepo repoPath=' . $extension->path . ' sourceUrl=' . $newURL;
				}
				else
				{
					$museCmd = 'cloneRepo repoPath=' . $extension->path . ' sourceUrl=' . $extension->get('url');
				}

				$clone_response = Cli::call($museCmd, $task='repository');
				$clone_response = json_decode($clone_response == null ? '' : $clone_response);
			}

			// Display clone error if errors
			if (isset($clone_response))
			{
				// was an empty repo cloned?
				if (preg_grep("/cloned an empty repository.../uis", $clone_response))
				{
					$output = array(Lang::txt('COM_INSTALLER_CUSTOMEXTS_CLONE_EMPTY_BRANCH'));
					$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $output);
				}
				else if (preg_grep("/could not read Username.../uis", $clone_response))
				{
					$output = array(Lang::txt('COM_INSTALLER_CUSTOMEXTS_MISSING_TOKEN'));
					$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $output);
				}
				else if (preg_grep("/fatal: Authentication failed.../uis", $clone_response))
				{
					$output = array(Lang::txt('COM_INSTALLER_CUSTOMEXTS_TOKEN_FAILURE'));
					$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $output);
				}
				// Try and determine a successful clone.  The string "cloning into" is always in the ouptut message.
				// Ex: Cloning into '/var/www/dev/app/modules/mod_home_hero_stats'... 
				else if (preg_grep("/Cloning into '" . preg_quote($extension->path, '/') . "'.../uis", $clone_response))
				{
					$output = array(Lang::txt('COM_INSTALLER_CUSTOMEXTS_CLONE_SUCCESSFUL'));
					$success[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $output);

					// Run migrations
					$migrations_response = Cli::migration($dryRun=false, $ignoreDates=true, $file=null, $dir='up', $folder=$extension->path);
					$migrations_response = json_decode($migrations_response);
				}
				else {
					// Try and catch other errors.  If other errors are found, it's best to add them above.
					$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $clone_response);
				}
			}
			else 
			{
				// If  have a cloned repo, let's check for incoming form changes.
				// Update the repo to the specified Access token.
				if ($extension->get('apikey'))
				{
					$newURL = "https://oauth2:" . $extension->get('apikey') . "@" . parse_url($extension->get('url'), PHP_URL_HOST) . parse_url($extension->get('url'), PHP_URL_PATH);
				}
				else
				{
					$newURL = $extension->get('url');
				}

				$museCmd = 'updateGitURLconf repoPath=' . $extension->path . ' newsourceUrl=' . $newURL;
				$updateGitURLconf_response = Cli::call($museCmd, $task='repository');
				$updateGitURLconf_response = json_decode($updateGitURLconf_response == null ? '' : $updateGitURLconf_response);

				// did we fail
				if ($updateGitURLconf_response === NULL)
				{
					// add failed MUSE message
					$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => array("MUSE updateGitURLconf returned NULL", $museCmd));
				}
				else if( $updateGitURLconf_response[0] != '' || json_last_error() != JSON_ERROR_NONE)
				{
					// add failed message
					$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $updateGitURLconf_response);
				}
				else
				{
					// Check if specified branch is being used.  If not checkout out specified branch
					$museCmd = 'checkoutRepoBranch repoPath=' . $extension->path . ((!empty($extension->get('git_branch'))) ? ' git_branch=' . $extension->get('git_branch') : '');
					$checkoutRepoBranch_response = Cli::call($museCmd, $task='repository');
					$checkoutRepoBranch_response = json_decode($checkoutRepoBranch_response);

					// check for command not found failure
					if (preg_grep("/command not found/uis", $checkoutRepoBranch_response))
					{
						// add failed message
						$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $checkoutRepoBranch_response);
					}
					else
					{
						// Check for updates in remote branch
						$museCmd = 'update -r=' . $extension->path . ((!empty($extension->get('git_branch'))) ? ' source=' . $extension->get('git_branch') : '');

						$fetch_response = Cli::call($museCmd, $task='repository');
						$fetch_response = json_decode($fetch_response == null ? '' : $fetch_response);

						// check for several failure scenarios, otherwise consider the muse update sucessful
						if (!is_null($fetch_response) && preg_grep("/command not found/uis", $fetch_response))
						{
							$output = array(Lang::txt('COM_INSTALLER_CUSTOMEXTS_CLONE_EMPTY_BRANCH2'));
							$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $output);
						}
						else if (!is_null($fetch_response) && preg_grep("/unknown revision or path not in the working tree/uis", $fetch_response))
						{
							$output = array(Lang::txt('COM_INSTALLER_CUSTOMEXTS_FETCH_UNKNOWN_BRANCH'));
							$failed[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $output);
						}
						else
						{
							// Run migrations
							$migrations_response = Cli::migration($dryRun=false, $ignoreDates=true, $file=null, $dir='up', $folder=$extension->path);
							$migrations_response = json_decode($migrations_response == null ? '' : $migrations_response);

							// if the response from the fetch was empty no changes needed, otherwise display error.
							// Possible the message here is considered an error because we're only testing for several specific 
							// errors above, but everything else will fall through to here. 
							$output = ($fetch_response == '') ? array(Lang::txt('COM_INSTALLER_CUSTOMEXTS_FETCH_CODE_UP_TO_DATE')) : $fetch_response;

							// add success message
							$success[] = array('ext_id' => $id, 'extension' => $extension->get('name'), 'message' => $output);
						}
					}
				}
			}
		}

		// display view
		$this->view
			->set('success', $success)
			->set('failed', $failed)
			->setLayout('fetched')
			->display();
	}


	/**
	 * doUpdateTask
	 *
	 * @return  void
	 */
	public function doUpdateTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$ids = Request::getArray('id', array());

		// Get the single ID we're working with
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		// empty list?
		if (empty($ids))
		{
			Notify::warning(Lang::txt('COM_INSTALLER_CUSTOMEXTS_NO_ELIGIBLE_COMMITS'));
			return $this->cancelTask();
		}

		// vars to hold results of pull
		$msg = array();

		// loop through each extension and pull code from repos
		foreach ($ids as $id)
		{
			$extension = Custom_extensions::oneOrNew($id);

			$museCmd = 'update -r=' . $extension->path . ' -f --no-colors';
			$update_response = Cli::call($museCmd, $task='repository');
			$update_response = json_decode($update_response == null ? '' : $update_response);

			// add output message, success or failure, just output.
			$msg[] = array(
				'extension'   => $extension->get('name'),
				'message' => $update_response
			);

			// Run migrations
			$migrations_response = Cli::migration($dryRun=false, $ignoreDates=true, $file=null, $dir='up', $folder=$extension->path);
			$migrations_response = json_decode($migrations_response == null ? '' : $migration_response);

		}

		// display view
		$this->view
			->setLayout('merged')
			->set('msg', $msg)
			->display();
	}

	/**
	 * removeTask
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		Request::checkToken();

		if (!User::authorise('core.delete', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getArray('cid', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$extension = Custom_extensions::oneOrNew($id);

			// If enextion is enabled
			if ($extension->enabled == 1)
			{
				$museCmd = 'removeRepo -path=' . $extension->path;
			}  // If enextion is disabled
			else if ($extension->enabled == 0)
			{
				$pieces = explode("/", $extension->path);
				$repodir = array_pop($pieces);
				$extdir = implode("/", $pieces);

				$museCmd = 'removeRepo -path=' . $extdir . '/__' . $repodir;
			}

			// Run migrations
			$migrations_response = Cli::migration($dryRun=false, $ignoreDates=true, $file=null, $dir='down', $folder=$extension->path);
			$migrations_response = json_decode($migrations_response);

			$remove_response = Cli::call($museCmd, $task='repository');
			$remove_response = json_decode($remove_response);
			// MUSE command failed
			if ($remove_response === NULL){
                               $failed[] = array(
                                        'extension'   => $extension->get('name'),
                                        'message' => array("MUSE Call returned NULL", $museCmd)
                                );
			}
			// did we succeed
			else if (preg_grep("/Updating the repository.../uis", $remove_response))
			{
				// add success message
				$success[] = array(
					'extension'   => $extension->get('name'),
					'message' => $remove_response
				);
			}
			else
			{
				// add failed message
				$failed[] = array(
					'extension'   => $extension->get('name'),
					'message' => $remove_response
				);
			}

			// Load the record
			$model = Custom_extensions::oneOrFail(intval($id));

			// Attempt to delete the record
			if (!$model->destroy())
			{
				Notify::error($model->getError());
				continue;
			}
			$success++;
		}

		if ($success)
		{
			// Set the success message
			Notify::success(Lang::txt('COM_INSTALLER_CUSTOMEXTS_N_ITEMS_DELETED', $success));
		}

		$this->cancelTask();
	}
}
