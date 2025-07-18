<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Blog\Site\Controllers;

use Components\Blog\Models\Archive;
use Components\Blog\Models\Comment;
use Components\Blog\Models\Entry;
use Hubzero\Component\SiteController;
use Hubzero\Utility\Str;
use Hubzero\Utility\Sanitize;
use Exception;
use Document;
use Request;
use Pathway;
use Event;
use Lang;
use Route;
use User;
use Date;

/**
 * Blog controller class for entries
 */
class Entries extends SiteController
{
	/**
	 * Determines task being called and attempts to execute it
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->model = new Archive('site', 0);

		$this->_authorize();
		$this->_authorize('entry');
		$this->_authorize('comment');

		$this->registerTask('comments.rss', 'comments');
		$this->registerTask('commentsrss', 'comments');

		$this->registerTask('feed.rss', 'feed');
		$this->registerTask('feedrss', 'feed');

		$this->registerTask('archive', 'display');
		$this->registerTask('new', 'edit');

		parent::execute();
	}

	/**
	 * Display a list of entries
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Filters for returning results
		$filters = array(
			'year'       => Request::getInt('year', 0),
			'month'      => Request::getInt('month', 0),
			'scope'      => $this->config->get('show_from', 'site'),
			'scope_id'   => 0,
			'search'     => Request::getString('search', ''),
			'authorized' => false,
			'state'      => 1,
			'access'     => User::getAuthorisedViewLevels()
		);

		if ($filters['year'] > date("Y"))
		{
			$filters['year'] = 0;
		}
		if ($filters['month'] > 12)
		{
			$filters['month'] = 0;
		}
		if ($filters['scope'] == 'both')
		{
			$filters['scope'] = '';
		}

		if (!User::isGuest())
		{
			if ($this->config->get('access-manage-component'))
			{
				//$filters['state'] = null;
				$filters['authorized'] = true;
				array_push($filters['access'], 5);
			}
		}

		// Output HTML
		$this->view
			->set('archive', $this->model)
			->set('config', $this->config)
			->set('filters', $filters)
			->display();
	}

	/**
	 * Display an entry
	 *
	 * @return  void
	 */
	public function entryTask()
	{
		$alias = Request::getString('alias', '');

		if (!$alias)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller)
			);
			return;
		}

		// Load entry
		$row = Entry::oneByScope(
			$alias,
			$this->model->get('scope'),
			$this->model->get('scope_id')
		);

		if (!$row->get('id') || $row->isDeleted())
		{
			App::abort(404, Lang::txt('COM_BLOG_NOT_FOUND'));
		}

		// Check authorization
		if (!$row->access('view'))
		{
			App::abort(403, Lang::txt('COM_BLOG_NOT_AUTH'));
		}

		// Filters for returning results
		$filters = array(
			'limit'      => 10,
			'start'      => 0,
			'scope'      => 'site',
			'scope_id'   => 0,
			'authorized' => false,
			'state'      => Entry::STATE_PUBLISHED,
			'access'     => User::getAuthorisedViewLevels()
		);

		if (!User::isGuest())
		{
			if ($this->config->get('access-manage-component'))
			{
				$filters['authorized'] = true;
			}
		}

		// Check session if this is a newly submitted entry. Trigger a proper event if so.
		if (Session::get('newsubmission.blog'))
		{
			// Unset the new submission session flag
			Session::set('newsubmission.blog');
			Event::trigger('content.onAfterContentSubmission', array('Blog'));
		}

		Event::trigger('blog.onBlogView', array($row));

		// Output HTML
		$this->view
			->set('archive', $this->model)
			->set('config', $this->config)
			->set('row', $row)
			->set('filters', $filters)
			->setLayout('entry')
			->display();
	}

	/**
	 * Show a form for editing an entry
	 *
	 * @param   object  $entry
	 * @return  void
	 */
	public function editTask($entry = null)
	{
		if (User::isGuest())
		{
			$rtrn = Request::getString('REQUEST_URI', Route::url('index.php?option=' . $this->_option . '&task=' . $this->_task, false, true), 'server');
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($rtrn)),
				Lang::txt('COM_BLOG_LOGIN_NOTICE'),
				'warning'
			);
			return;
		}

		if (!$this->config->get('access-create-entry')
		 && !$this->config->get('access-edit-entry')
		 && !$this->config->get('access-manage-entry'))
		{
			App::abort(403, Lang::txt('COM_BLOG_NOT_AUTH'));
		}

		if (!is_object($entry))
		{
			$entry = Entry::oneOrNew(Request::getInt('entry', 0));
		}

		if ($entry->isNew())
		{
			$entry->set('allow_comments', 1);
			$entry->set('state', Entry::STATE_PUBLISHED);
			$entry->set('scope', 'site');
			$entry->set('created_by', User::get('id'));
		}

		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		$this->view
			->set('archive', $this->model)
			->set('config', $this->config)
			->set('entry', $entry)
			->setLayout('edit')
			->display();
	}

	/**
	 * Save entry
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		if (User::isGuest())
		{
			$rtrn = Request::getString('REQUEST_URI', Route::url('index.php?option=' . $this->_option . '&task=' . $this->_task), 'server');
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($rtrn)),
				Lang::txt('COM_BLOG_LOGIN_NOTICE'),
				'warning'
			);
			return;
		}

		if (!$this->config->get('access-create-entry')
		 && !$this->config->get('access-edit-entry')
		 && !$this->config->get('access-manage-entry'))
		{
			App::abort(403, Lang::txt('COM_BLOG_NOT_AUTH'));
		}

		// Check for request forgeries
		Request::checkToken();

		$fields = Request::getArray('entry', array(), 'post', 'none', 2);

		// Make sure we don't want to turn off comments
		//$fields['allow_comments'] = (isset($fields['allow_comments'])) ? 1 : 0;

		if (isset($fields['publish_up']) && $fields['publish_up'] != '')
		{
			$fields['publish_up']   = Date::of($fields['publish_up'], Config::get('offset'))->toSql();
		}
		if (isset($fields['publish_down']) && $fields['publish_down'] != '')
		{
			$fields['publish_down'] = Date::of($fields['publish_down'], Config::get('offset'))->toSql();
		}
		$fields['scope'] = 'site';
		$fields['scope_id'] = 0;

		$row = Entry::oneOrNew($fields['id'])->set($fields);

		// Trigger before save event
		$isNew  = $row->isNew();
		$result = Event::trigger('onBlogBeforeSave', array(&$row, $isNew));

		if (in_array(false, $result, true))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Store new content
		if (!$row->save())
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Process tags
		if (!$row->tag(Request::getString('tags', '')))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Trigger after save event
		Event::trigger('onBlogAfterSave', array(&$row, $isNew));

		// Log activity
		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => ($fields['id'] ? 'updated' : 'created'),
				'scope'       => 'blog.entry',
				'scope_id'    => $row->get('id'),
				'description' => Lang::txt('COM_BLOG_ACTIVITY_ENTRY_' . ($fields['id'] ? 'UPDATED' : 'CREATED'), '<a href="' . Route::url($row->link()) . '">' . $row->get('title') . '</a>'),
				'details'     => array(
					'title' => $row->get('title'),
					'url'   => Route::url($row->link())
				)
			],
			'recipients' => [
				$row->get('created_by')
			]
		]);

		// If the new resource is published, set the session flag indicating the new submission
		if ($isNew)
		{
			Session::set('newsubmission.blog', true);
		}

		// Redirect to the entry
		App::redirect(
			Route::url($row->link())
		);
	}

	/**
	 * Mark an entry as deleted
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		if (User::isGuest())
		{
			$rtrn = Request::getString('REQUEST_URI', Route::url('index.php?option=' . $this->_option, false, true), 'server');
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($rtrn)),
				Lang::txt('COM_BLOG_LOGIN_NOTICE'),
				'warning'
			);
			return;
		}

		if (!$this->config->get('access-delete-entry')
		 && !$this->config->get('access-manage-entry'))
		{
			App::abort(403, Lang::txt('COM_BLOG_NOT_AUTH'));
		}

		// Incoming
		$id = Request::getInt('entry', 0);

		if (!$id)
		{
			return $this->displayTask();
		}

		$process    = Request::getString('process', '');
		$confirmdel = Request::getString('confirmdel', '');

		// Initiate a blog entry object
		$entry = Entry::oneOrFail($id);

		// Did they confirm delete?
		if (!$process || !$confirmdel)
		{
			if ($process && !$confirmdel)
			{
				$this->setError(Lang::txt('COM_BLOG_ERROR_CONFIRM_DELETION'));
			}

			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}

			$this->view
				->set('archive', $this->model)
				->set('config', $this->config)
				->set('entry', $entry)
				->display();
			return;
		}

		// Check for request forgeries
		Request::checkToken();

		// Delete the entry itself
		$entry->set('state', 2);

		if (!$entry->save())
		{
			Notify::error($entry->getError());
		}

		// Log the activity
		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => 'deleted',
				'scope'       => 'blog.entry',
				'scope_id'    => $id,
				'description' => Lang::txt('COM_BLOG_ACTIVITY_ENTRY_DELETED', '<a href="' . Route::url($entry->link()) . '">' . $entry->get('title') . '</a>'),
				'details'     => array(
					'title' => $entry->get('title'),
					'url'   => Route::url($entry->link())
				)
			],
			'recipients' => [
				$entry->get('created_by')
			]
		]);

		// Return the entries lsit
		App::redirect(
			Route::url('index.php?option=' . $this->_option)
		);
	}

	/**
	 * Generate an RSS feed of entries
	 *
	 * @return  void
	 */
	public function feedTask()
	{
		if (!$this->config->get('feeds_enabled'))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option)
			);
			return;
		}

		// Set the mime encoding for the document
		Document::setType('feed');

		// Start a new feed object
		$doc = Document::instance();
		$doc->link = Route::url('index.php?option=' . $this->_option);

		// Incoming
		$filters = array(
			'year'       => Request::getInt('year', 0),
			'month'      => Request::getInt('month', 0),
			'scope'      => $this->config->get('show_from', 'site'),
			'scope_id'   => 0,
			'search'     => Request::getString('search', ''),
			'authorized' => false,
			'state'      => 1,
			'access'     => User::getAuthorisedViewLevels()
		);

		if ($filters['year'] > date("Y"))
		{
			$filters['year'] = 0;
		}
		if ($filters['month'] > 12)
		{
			$filters['month'] = 0;
		}
		if ($filters['scope'] == 'both')
		{
			$filters['scope'] = '';
		}

		if (!User::isGuest())
		{
			if ($this->config->get('access-manage-component'))
			{
				//$filters['state'] = null;
				$filters['authorized'] = true;
				array_push($filters['access'], 5);
			}
		}

		// Build some basic RSS document information
		$doc->title  = Config::get('sitename') . ' - ' . Lang::txt(strtoupper($this->_option));
		$doc->title .= ($filters['year'])  ? ': ' . $filters['year'] : '';
		$doc->title .= ($filters['month']) ? ': ' . sprintf("%02d", $filters['month']) : '';

		$doc->description = Lang::txt('COM_BLOG_RSS_DESCRIPTION', Config::get('sitename'));
		$doc->copyright   = Lang::txt('COM_BLOG_RSS_COPYRIGHT', date("Y"), Config::get('sitename'));
		$doc->category    = Lang::txt('COM_BLOG_RSS_CATEGORY');

		// Get the records
		$rows = $this->model->entries($filters)
			->ordered()
			->paginated()
			->rows();

		// Start outputing results if any found
		if ($rows->count() > 0)
		{
			foreach ($rows as $row)
			{
				$item = new \Hubzero\Document\Type\Feed\Item();

				// Strip html from feed item description text
				$item->description = $row->content();
				$item->description = html_entity_decode(Sanitize::stripAll($item->description));
				if ($this->config->get('feed_entries') == 'partial')
				{
					$item->description = Str::truncate($item->description, 300);
				}
				$item->description = '<![CDATA[' . $item->description . ']]>';

				// Load individual item creator class
				$item->title       = html_entity_decode(strip_tags($row->get('title')));
				$item->link        = Route::url($row->link());
				$item->date        = date('r', strtotime($row->published()));
				$item->category    = '';
				$item->author      = $row->creator()->get('email') . ' (' . $row->creator()->get('name') . ')';

				// Loads item info into rss array
				$doc->addItem($item);
			}
		}
	}

	/**
	 * Save a comment
	 *
	 * @return  void
	 */
	public function savecommentTask()
	{
		// Ensure the user is logged in
		if (User::isGuest())
		{
			$rtrn = Request::getString('REQUEST_URI', Route::url('index.php?option=' . $this->_option), 'server');
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($rtrn)),
				Lang::txt('COM_BLOG_LOGIN_NOTICE'),
				'warning'
			);
			return;
		}

		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$data = Request::getArray('comment', array(), 'post', 'none', 2);

		// Instantiate a new comment object and pass it the data
		$comment = Comment::oneOrNew($data['id'])->set($data);

		// Trigger before save event
		$isNew  = $comment->isNew();
		$result = Event::trigger('onBlogCommentBeforeSave', array(&$comment, $isNew));

		if (in_array(false, $result, true))
		{
			$this->setError($comment->getError());
			return $this->entryTask();
		}

		// Store new content
		if (!$comment->save())
		{
			$this->setError($comment->getError());
			return $this->entryTask();
		}

		// Trigger after save event
		Event::trigger('onBlogCommentAfterSave', array(&$comment, $isNew));

		// Log the activity
		$entry = Entry::oneOrFail($comment->get('entry_id'));

		$recipients = array($comment->get('created_by'));
		if ($comment->get('created_by') != $entry->get('created_by'))
		{
			$recipients[] = $entry->get('created_by');
		}
		if ($comment->get('parent'))
		{
			$recipients[] = $comment->parent()->get('created_by');
		}

		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => ($data['id'] ? 'updated' : 'created'),
				'scope'       => 'blog.entry.comment',
				'scope_id'    => $comment->get('id'),
				'anonymous'   => $comment->get('anonymous', 0),
				'description' => Lang::txt('COM_BLOG_ACTIVITY_COMMENT_' . ($data['id'] ? 'UPDATED' : 'CREATED'), $comment->get('id'), '<a href="' . Route::url($entry->link() . '#c' . $comment->get('id')) . '">' . $entry->get('title') . '</a>'),
				'details'     => array(
					'title'    => $entry->get('title'),
					'entry_id' => $entry->get('id'),
					'url'      => $entry->link()
				)
			],
			'recipients' => $recipients
		]);

		return $this->entryTask();
	}

	/**
	 * Delete a comment
	 *
	 * @return  void
	 */
	public function deletecommentTask()
	{
		// Ensure the user is logged in
		if (User::isGuest())
		{
			$this->setError(Lang::txt('COM_BLOG_LOGIN_NOTICE'));
			return $this->entryTask();
		}

		// Incoming
		$id    = Request::getInt('comment', 0);
		$year  = Request::getString('year', '');
		$month = Request::getString('month', '');
		$alias = Request::getString('alias', '');

		if (!$id)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&year=' . $year . '&month=' . $month . '&alias=' . $alias, false)
			);
			return;
		}

		// Initiate a blog comment object
		$comment = Comment::oneOrFail($id);

		if (User::get('id') != $comment->get('created_by') && !$this->config->get('access-delete-comment'))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&year=' . $year . '&month=' . $month . '&alias=' . $alias, false)
			);
			return;
		}

		// Mark all comments as deleted
		$comment->set('state', Comment::STATE_DELETED);
		$comment->save();

		// Log the activity
		$entry = Entry::oneOrFail($comment->get('entry_id'));

		$recipients = array($comment->get('created_by'));
		if ($comment->get('created_by') != $entry->get('created_by'))
		{
			$recipients[] = $entry->get('created_by');
		}

		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => 'deleted',
				'scope'       => 'blog.entry.comment',
				'scope_id'    => $comment->get('id'),
				'description' => Lang::txt('COM_BLOG_ACTIVITY_COMMENT_DELETED', $comment->get('id'), '<a href="' . Route::url($entry->link()) . '">' . $entry->get('title') . '</a>'),
				'details'     => array(
					'title'    => $entry->get('title'),
					'entry_id' => $entry->get('id'),
					'url'      => $entry->link()
				)
			],
			'recipients' => $recipients
		]);

		// Return the topics list
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&year=' . $year . '&month=' . $month . '&alias=' . $alias),
			($this->getError() ? $this->getError() : null),
			($this->getError() ? 'error' : null)
		);
	}

	/**
	 * Display an RSS feed of comments
	 *
	 * @return  void
	 */
	public function commentsTask()
	{
		if (!$this->config->get('feeds_enabled'))
		{
			throw new Exception(Lang::txt('Feed not found.'), 404);
		}

		// Set the mime encoding for the document
		Document::setType('feed');

		// Start a new feed object
		$doc = Document::instance();
		$doc->link = Route::url('index.php?option=' . $this->_option);

		// Incoming
		$alias = Request::getString('alias', '');
		if (!$alias)
		{
			throw new Exception(Lang::txt('Feed not found.'), 404);
		}

		$this->entry = Entry::oneByScope($alias, 'site', 0);

		if (!$this->entry->isAvailable())
		{
			throw new Exception(Lang::txt('Feed not found.'), 404);
		}

		$year  = Request::getInt('year', date("Y"));
		$month = Request::getInt('month', 0);

		// Build some basic RSS document information
		$doc->title  = Config::get('sitename') . ' - ' . Lang::txt(strtoupper($this->_option));
		$doc->title .= ($year) ? ': ' . $year : '';
		$doc->title .= ($month) ? ': ' . sprintf("%02d", $month) : '';
		$doc->title .= stripslashes($this->entry->get('title', ''));
		$doc->title .= ': ' . Lang::txt('Comments');

		$doc->description = Lang::txt('COM_BLOG_COMMENTS_RSS_DESCRIPTION', Config::get('sitename'), stripslashes($this->entry->get('title')));
		$doc->copyright   = Lang::txt('COM_BLOG_RSS_COPYRIGHT', date("Y"), Config::get('sitename'));

		$rows = $this->entry->comments()
			->whereIn('state', array(1, 3))
			->ordered()
			->rows();

		// Start outputing results if any found
		if ($rows->count() <= 0)
		{
			return;
		}

		foreach ($rows as $row)
		{
			$this->_comment($doc, $row);
		}
	}

	/**
	 * Recursive method to add comments to a flat RSS feed
	 *
	 * @param   object  $doc  Document
	 * @param   object  $row  Comment
	 * @return  void
	 */
	private function _comment(&$doc, $row)
	{
		// Load individual item creator class
		$item = new \Hubzero\Document\Type\Feed\Item();
		$item->title = Lang::txt('Comment #%s', $row->get('id')) . ' @ ' . $row->created('time') . ' on ' . $row->created('date');
		$item->link  = Route::url($this->entry->link()  . '#c' . $row->get('id'));

		if ($row->isReported())
		{
			$item->description = Lang::txt('COM_BLOG_COMMENT_REPORTED_AS_ABUSIVE');
		}
		else
		{
			$item->description = html_entity_decode(Sanitize::stripAll($row->content()));
		}
		$item->description = '<![CDATA[' . $item->description . ']]>';

		if ($row->get('anonymous'))
		{
			//$item->author = Lang::txt('JANONYMOUS');
		}
		else
		{
			$item->author = $row->creator->get('email') . ' (' . $row->creator->get('name') . ')';
		}
		$item->date     = $row->created();
		$item->category = '';

		$doc->addItem($item);

		$replies = $row->replies()->whereIn('state', array(1, 3));

		if ($replies->count() > 0)
		{
			foreach ($replies as $reply)
			{
				$this->_comment($doc, $reply);
			}
		}
	}

	/**
	 * Method to check admin access permission
	 *
	 * @param   string   $assetType
	 * @param   integer  $assetId
	 * @return  void
	 */
	protected function _authorize($assetType='component', $assetId=null)
	{
		$this->config->set('access-view-' . $assetType, true);

		if (!User::isGuest())
		{
			$asset  = $this->_option;
			$at = '';

			if ($assetId)
			{
				$asset .= ($assetType != 'component') ? '.' . $assetType : '';
				$asset .= ($assetId) ? '.' . $assetId : '';

				if ($assetType != 'component')
				{
					$at = '.' . $assetType;
				}
			}

			// Admin
			$this->config->set('access-admin-' . $assetType, User::authorise('core.admin', $asset));
			$this->config->set('access-manage-' . $assetType, User::authorise('core.manage', $asset));
			// Permissions
			$this->config->set('access-create-' . $assetType, User::authorise('core.create' . $at, $asset));
			$this->config->set('access-delete-' . $assetType, User::authorise('core.delete' . $at, $asset));
			$this->config->set('access-edit-' . $assetType, User::authorise('core.edit' . $at, $asset));
			$this->config->set('access-edit-state-' . $assetType, User::authorise('core.edit.state' . $at, $asset));
			$this->config->set('access-edit-own-' . $assetType, User::authorise('core.edit.own' . $at, $asset));
		}
	}
}
