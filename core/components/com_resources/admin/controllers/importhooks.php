<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Resources\Admin\Controllers;

use Components\Resources\Models\Import\Hook;
use Components\Resources\Import\Importer;
use Hubzero\Component\AdminController;
use Request;
use Notify;
use User;
use Date;
use Lang;
use App;

require_once dirname(dirname(__DIR__)) . DS . 'models' . DS . 'import' . DS . 'hook.php';

/**
 * Resource importer hooks
 */
class ImportHooks extends AdminController
{
	/**
	 * Executes a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');

		parent::execute();
	}

	/**
	 * Display imports
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Incoming
		$filters = array(
			'sort' => Request::getState(
				$this->_option . '.hooks.sort',
				'filter_order',
				'type'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.hooks.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);

		// get all imports from archive
		$hooks = Hook::all()
			->ordered('filter_order', 'filter_order_Dir')
			->paginated('limitstart', 'limit')
			->rows();

		// Output the HTML
		$this->view
			->set('hooks', $hooks)
			->setLayout('display')
			->display();
	}

	/**
	 * Edit an Import
	 *
	 * @param   object  $hook
	 * @return  void
	 */
	public function editTask($hook = null)
	{
		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		Request::setVar('hidemainmenu', 1);

		if (!($hook instanceof Hook))
		{
			// get request vars
			$id = Request::getVar('id', array(0));
			if (is_array($id))
			{
				$id = (!empty($id)) ? $id[0] : 0;
			}

			// get the import object
			$hook = Hook::oneOrNew($id);
		}

		// Output the HTML
		$this->view
			->set('hook', $hook)
			->setLayout('edit')
			->display();
	}

	/**
	 * Save an Import
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		// check token
		Request::checkToken();

		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// get request vars
		$data = Request::getVar('hook', array());
		$file = Request::getVar('file', array(), 'FILES');

		// create hook model object
		$hook = Hook::oneOrNew($data['id'])->set($data);

		// is this a new import
		$isNew = false;
		if (!$hook->get('id'))
		{
			$isNew = true;

			// set the created by/at
			$hook->set('created_by', User::get('id'));
			$hook->set('created', Date::toSql());
		}

		// attempt to save
		if (!$hook->save())
		{
			Notify::error($hook->getError());
			return $this->editTask($hook);
		}

		// is this a new import
		if ($isNew)
		{
			// create folder for files
			$uploadPath = $hook->fileSpacePath();

			// if we dont have a filespace, create it
			if (!is_dir($uploadPath))
			{
				\Filesystem::makeDirectory($uploadPath, 0775);
			}
		}

		// if we have a file
		if ($file['size'] > 0 && $file['error'] == 0)
		{
			move_uploaded_file($file['tmp_name'], $hook->fileSpacePath() . DS . $file['name']);

			$hook->set('file', $file['name']);
			$hook->save();
		}

		// Inform user & redirect
		Notify::success(Lang::txt('COM_RESOURCES_IMPORTHOOK_CREATED'));

		$this->cancelTask();
	}

	/**
	 * Show Raw immport hook file
	 *
	 * @return  void
	 */
	public function rawTask()
	{
		// get request vars
		$id = Request::getVar('id', array());
		if (is_array($id))
		{
			$id = (!empty($id)) ? $id[0] : 0;
		}

		// create hook model object
		$hook = Hook::oneOrFail($id);

		// get path to file
		$file = $hook->fileSpacePath() . DS . $hook->get('file');

		// default contents
		$contents = '';

		// if we have a file
		if (file_exists($file))
		{
			// get contents of file
			$contents = file_get_contents($file);
		}

		// output contents of hook file
		highlight_string($contents);
		exit();
	}

	/**
	 * Delete Import
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		// check token
		Request::checkToken();

		if (!User::authorise('core.delete', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// get request vars
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// loop through all ids posted
		$removed = 0;
		foreach ($ids as $id)
		{
			// make sure we have an object
			$hook = Hook::oneOrFail($id);

			if (!$hook->destroy())
			{
				Notify::error($hook->getError());
				continue;
			}

			$removed++;
		}

		if ($removed)
		{
			Notify::success(Lang::txt('COM_RESOURCES_IMPORTHOOK_REMOVED'));
		}

		// inform user & redirect
		$this->cancelTask();
	}
}