<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forum\Site\Controllers;

use Hubzero\Component\SiteController;
use Hubzero\Utility\Str;
use Components\Forum\Models\Manager;
use Components\Forum\Models\Section;
use Components\Forum\Models\Category;
use Components\Forum\Models\Post;
use Components\Forum\Models\Attachment;
use Document;
use Pathway;
use Request;
use Notify;
use Config;
use Route;
use User;
use Lang;
use App;
use DOMDocument;

/**
 * Forum controller class for threads
 */
class Threads extends SiteController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Allow anonymous posts by default
		$this->config->def('allow_anonymous', 1);

		$this->forum = new Manager('site', 0);

		$this->registerTask('latest', 'feed');
		$this->registerTask('latest', 'feed.rss');
		$this->registerTask('latest', 'latest.rss');

		parent::execute();
	}

	/**
	 * Method to set the document path
	 *
	 * @param   object  $section
	 * @param   object  $category
	 * @param   object  $thread
	 * @return  void
	 */
	protected function buildPathway($section=null, $category=null, $thread=null)
	{
		if (Pathway::count() <= 0)
		{
			Pathway::append(
				Lang::txt(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}
		if (isset($section))
		{
			Pathway::append(
				Str::truncate(stripslashes($section->get('title')), 100, array('exact' => true)),
				'index.php?option=' . $this->_option . '&section=' . $section->get('alias')
			);
		}
		if (isset($category))
		{
			Pathway::append(
				Str::truncate(stripslashes($category->get('title')), 100, array('exact' => true)),
				'index.php?option=' . $this->_option . '&section=' . $section->get('alias') . '&category=' . $category->get('alias')
			);
		}
		if (isset($thread) && $thread->get('id'))
		{
			Pathway::append(
				'#' . $thread->get('id') . ' - ' . Str::truncate(stripslashes($thread->get('title')), 100, array('exact' => true)),
				'index.php?option=' . $this->_option . '&section=' . $section->get('alias') . '&category=' . $category->get('alias') . '&thread=' . $thread->get('id')
			);
		}
	}

	/**
	 * Method to build and set the document title
	 *
	 * @param   object  $section
	 * @param   object  $category
	 * @param   object  $thread
	 * @return  void
	 */
	protected function buildTitle($section=null, $category=null, $thread=null)
	{
		$this->_title = Lang::txt(strtoupper($this->_option));
		if (isset($section))
		{
			$this->_title .= ': ' . Str::truncate(stripslashes($section->get('title')), 100, array('exact' => true));
		}
		if (isset($category))
		{
			$this->_title .= ': ' . Str::truncate(stripslashes($category->get('title')), 100, array('exact' => true));
		}
		if (isset($thread) && $thread->get('id'))
		{
			$this->_title .= ': #' . $thread->get('id') . ' - ' . Str::truncate(stripslashes($thread->get('title')), 100, array('exact' => true));
		}

		Document::setTitle($this->_title);
	}

	/**
	 * Display a thread
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Incoming
		$filters = array(
			'limit'    => Request::getInt('limit', 25),
			'start'    => Request::getInt('limitstart', 0),
			'section'  => Request::getCmd('section', ''),
			'category' => Request::getCmd('category', ''),
			'parent'   => Request::getInt('parent', ''),
			'thread'   => Request::getInt('thread', 0),
			'state'    => Post::STATE_PUBLISHED,
			'access'   => User::getAuthorisedViewLevels()
		);

		// Section
		$section = Section::all()
			->whereEquals('alias', $filters['section'])
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Section::STATE_DELETED)
			->row();
		if (!$section->get('id'))
		{
			App::abort(404, Lang::txt('COM_FORUM_SECTION_NOT_FOUND'));
		}

		// Get the category
		$category = Category::all()
			->whereEquals('alias', $filters['category'])
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Category::STATE_DELETED)
			->row();
		if (!$category->get('id'))
		{
			App::abort(404, Lang::txt('COM_FORUM_CATEGORY_NOT_FOUND'));
		}

		$filters['category_id'] = $category->get('id');

		// Load the topic
		$thread = Post::oneOrFail($filters['thread']);

		// Check logged in status
		if (!in_array($thread->get('access'), User::getAuthorisedViewLevels()))
		{
			$return = base64_encode(Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&section=' . $filters['section'] . '&category=' . $filters['category'] . '&thread=' . $filters['parent'], false, true));
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . $return)
			);
		}

		$filters['state'] = array(1, 3);

		// Get authorization
		$this->_authorize('category', $category->get('id'));
		$this->_authorize('thread', $thread->get('id'));
		$this->_authorize('post');

		// Set the page title
		$this->buildTitle($section, $category, $thread);

		// Set the pathway
		$this->buildPathway($section, $category, $thread);

		// Get all the likes of this thread
		$db = \App::get('db');
		$queryLikes = "SELECT LIKES.threadId as 'threadId', LIKES.postId as 'postId', 
		  LIKES.userId as 'userId', USERS.name as 'userName', USERS.email as 'userEmail' 
		  FROM jos_forum_posts_like as LIKES, jos_users AS USERS
		  WHERE LIKES.userId = USERS.id AND LIKES.threadId = " . $thread->get('id');

		$db->setQuery($queryLikes);
		$initialLikesList = $db->loadObjectList();

		// Output the view
		$this->view
			->set('config', $this->config)
			->set('forum', $this->forum)
			->set('section', $section)
			->set('category', $category)
			->set('thread', $thread)
			->set('filters', $filters)
			->set('likes', $initialLikesList)
			->setErrors($this->getErrors())
			->display();
	}

	/**
	 * Show a form for creating a new entry
	 *
	 * @return  void
	 */
	public function newTask()
	{
		$this->editTask();
	}

	/**
	 * Show a form for editing an entry
	 *
	 * @param   mixed  $post
	 * @return  void
	 */
	public function editTask($post=null)
	{
		$id       = Request::getInt('thread', 0);
		$category = Request::getCmd('category', '');
		$section  = Request::getCmd('section', '');

		if (User::isGuest())
		{
			$return = Route::url('index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category . '&task=new');
			if ($id)
			{
				$return = Route::url('index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category . '&thread=' . $id . '&task=edit');
			}
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($return)) . Lang::txt('COM_FORUM_LOGIN_NOTICE'),
				'warning'
			);
		}

		// Section
		$section = Section::all()
			->whereEquals('alias', $section)
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Section::STATE_DELETED)
			->row();
		if (!$section->get('id'))
		{
			App::abort(404, Lang::txt('COM_FORUM_SECTION_NOT_FOUND'));
		}

		// Get the category
		$category = Category::all()
			->whereEquals('alias', $category)
			->whereEquals('scope', $this->forum->get('scope'))
			->whereEquals('scope_id', $this->forum->get('scope_id'))
			->where('state', '!=', Category::STATE_DELETED)
			->row();
		if (!$category->get('id'))
		{
			App::abort(404, Lang::txt('COM_FORUM_CATEGORY_NOT_FOUND'));
		}

		// Incoming
		if (!is_object($post))
		{
			$post = Post::oneOrNew($id);
		}

		$this->_authorize('thread', $id);

		if ($post->isNew())
		{
			$post->set('scope', $this->forum->get('scope'));
			$post->set('created_by', User::get('id'));
		}
		elseif ($post->get('created_by') != User::get('id') && !$this->config->get('access-edit-thread'))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category),
				Lang::txt('COM_FORUM_NOT_AUTHORIZED'),
				'warning'
			);
		}

		// Set the page title
		$this->buildTitle($section, $category, $post);

		// Set the pathway
		$this->buildPathway($section, $category, $post);

		$this->view
			->set('config', $this->config)
			->set('forum', $this->forum)
			->set('section', $section)
			->set('category', $category)
			->set('post', $post)
			->setErrors($this->getErrors())
			->setLayout('edit')
			->display();
	}

	/**
	 * Save an entry
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		if (User::isGuest())
		{
			$return = Route::url('index.php?option=' . $this->_option, false, true);
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($return))
			);
		}

		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$section = Request::getString('section', '');
		$fields  = Request::getArray('fields', array(), 'post', 'none', 2);
		$fields  = array_map('trim', $fields);

		$fields['sticky']    = (isset($fields['sticky']))    ? $fields['sticky']    : 0;
		$fields['closed']    = (isset($fields['closed']))    ? $fields['closed']    : 0;
		$fields['anonymous'] = (isset($fields['anonymous'])) ? $fields['anonymous'] : 0;

		// Instantiate a Post record
		$post = Post::oneOrNew($fields['id']);

		// Set authorization if the current user is the creator
		// of an existing post.
		$assetType = $fields['parent'] ? 'post' : 'thread';

		if ($post->get('id'))
		{
			if ($post->get('created_by') == User::get('id'))
			{
				$this->config->set('access-edit-' . $assetType, true);
			}
			$fields['modified'] = \Date::toSql();
			$fields['modified_by'] = User::get('id');
		}

		// Extracting emails from the new post submitted
		$domComment = new DOMDocument();
		$domComment->loadHTML($fields['comment']);
		$mentionEmailList = array();
		foreach ($domComment->getElementsByTagName('a') as $item) {
			$userId = $item->getAttribute('data-user-id');
            $user = User::getInstance($userId);
            $email = $user->get('email');

            $mentionEmailList[] = $email;
		}

		// Authorization check
		$this->_authorize($assetType, intval($fields['id']));

		if (!$this->config->get('access-edit-' . $assetType)
		 && !$this->config->get('access-create-' . $assetType))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option)
			);
		}

		// Bind data
		if (!$fields['id'])
		{
			$fields['id'] = null;
		}
		$post->set($fields);

		// Make sure the thread exists and is accepting new posts
		if ($post->get('parent') && isset($fields['thread']))
		{
			$thread = Post::oneOrFail($fields['thread']);

			if (!$thread->get('id') || $thread->get('closed'))
			{
				Notify::error(Lang::txt('COM_FORUM_ERROR_THREAD_CLOSED'));
				return $this->editTask($post);
			}
		}

		// Make sure the category exists and is accepting new posts
		$category = Category::oneOrFail($post->get('category_id'));

		if ($category->get('closed'))
		{
			Notify::error(Lang::txt('COM_FORUM_ERROR_CATEGORY_CLOSED'));
			return $this->editTask($post);
		}

		// Store new content
		if (!$post->save())
		{
			Notify::error($post->getError());
			return $this->editTask($post);
		}

		// Upload files
		if (!$this->uploadTask($post->get('thread', $post->get('id')), $post->get('id')))
		{
			Notify::error($this->getError());
			return $this->editTask($post);
		}

		// Save tags
		$post->tag(Request::getString('tags', '', 'post'), User::get('id'));

		// Determine message
		if (!$fields['id'])
		{
			$message = Lang::txt('COM_FORUM_POST_ADDED');

			if (!$fields['parent'])
			{
				$message = Lang::txt('COM_FORUM_THREAD_STARTED');
			}
		}
		else
		{
			$message = ($post->get('modified_by')) ? Lang::txt('COM_FORUM_POST_EDITED') : Lang::txt('COM_FORUM_POST_ADDED');
		}

		$url = 'index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category->get('alias') . '&thread=' . $post->get('thread') . '#c' . $post->get('id');

		// Record the activity
		$recipients = array(
			['forum.site', 1],
			['forum.section', $category->get('section_id')],
			['user', $post->get('created_by')]
		);
		$type = 'thread';
		$desc = Lang::txt(
			'COM_FORUM_ACTIVITY_' . strtoupper($type) . '_' . ($fields['id'] ? 'UPDATED' : 'CREATED'),
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
				'COM_FORUM_ACTIVITY_' . strtoupper($type) . '_' . ($fields['id'] ? 'UPDATED' : 'CREATED'),
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

		// Email to Users that were mentioned in the post
		if ($mentionEmailList) 
		{
			$comment = $fields['comment'];
			$createdByUserId = $post->get('created_by');
			$createdByUser = User::getInstance($createdByUserId);
			$createdUserName = $createdByUser->get('name');

			$urlExt = 'forum/' . $section . '/' . $category->get('alias') . '/' . $post->get('thread') . '#c' . $post->get('id');
			$host = $_SERVER['HTTP_HOST'];
			$externalUrl = 'https://' . $host . '/' . $urlExt;

			$this->emailToAllMentionedUsers($mentionEmailList, $comment, $externalUrl, $createdUserName);
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
			Route::url($url),
			$message,
			'message'
		);
	}

	/**
	 * Email the mentioned user with a PHP mail template
	 *
	 * @return  void
	 */
	public function emailToAllMentionedUsers($emails, $comment, $url, $postAuthor) 
	{
		$from = array();
		$from['name']  = Config::get('sitename') . ' ' . Lang::txt(strtoupper($this->_name));
		$from['email'] = Config::get('mailfrom');

		$subject = $postAuthor . " mentioned you on forum thread";

		// BUILDING THE EMAIL TEMPLATE
		$dirTemplate = dirname(dirname(__DIR__));
		$eView = new \Hubzero\Mail\View(array(
			'base_path' => $dirTemplate . DS . 'site',
			'name'   => 'emails',
			'layout' => 'mentions_html'
		));

		$eView->comment = $comment;
		$eView->commentNoTags = strip_tags($comment);
		$eView->postLink = $url;
		$eView->postAuthor = $postAuthor;

		$html = $eView->loadTemplate(false);
		$html = str_replace("\n", "\r\n", $html);

		// Create NEW message object and send
		$message = new \Hubzero\Mail\Message();

		$message->setSubject($subject)
			->addFrom($from['email'], $from['name'])
			->setTo($from['email'])
			->setBcc($emails)
			->addHeader('X-Mailer', 'PHP/' . phpversion())
			->addHeader('X-Component', 'com_forum')
			->addHeader('X-Component-Object', 'com_forum_mentions_email')
			->addPart($html, 'text/html')
			->send();

		return true;
	}

	/**
	 * Delete an entry
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		$section  = Request::getString('section', '');
		$category = Request::getString('category', '');

		// Is the user logged in?
		if (User::isGuest())
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category),
				Lang::txt('COM_FORUM_LOGIN_NOTICE'),
				'warning'
			);
		}

		// Incoming
		$id = Request::getInt('thread', 0);

		// Load the post
		$post = Post::oneOrFail($id);

		// Make the sure the category exist
		if (!$post->get('id'))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category),
				Lang::txt('COM_FORUM_MISSING_ID'),
				'error'
			);
		}

		// Check if user is authorized to delete entries
		$this->_authorize('thread', $id);

		if (!$this->config->get('access-delete-thread'))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category),
				Lang::txt('COM_FORUM_NOT_AUTHORIZED'),
				'warning'
			);
		}

		// Trash the post
		// Note: this will carry through to all replies
		//       and attachments
		$post->set('state', $post::STATE_DELETED);

		if (!$post->save())
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category),
				$post->getError(),
				'error'
			);
		}

		// Record the activity
		$url  = $post->link();
		$type = 'thread';
		$desc = Lang::txt(
			'COM_FORUM_ACTIVITY_' . strtoupper($type) . '_DELETED',
			'<a href="' . Route::url($url) . '">' . $post->get('title') . '</a>'
		);
		if ($post->get('parent'))
		{
			$thread = Post::oneOrFail($post->get('thread'));

			$type = 'post';
			$desc = Lang::txt(
				'COM_FORUM_ACTIVITY_' . strtoupper($type) . '_DELETED',
				$post->get('id'),
				'<a href="' . Route::url($url) . '">' . $thread->get('title') . '</a>'
			);
		}

		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => 'deleted',
				'scope'       => 'forum.' . $type,
				'scope_id'    => $post->get('id'),
				'description' => $desc,
				'details'     => array(
					'thread' => $post->get('thread'),
					'url'    => Route::url($url)
				)
			],
			'recipients' => array(
				['forum.site', 1],
				['user', $post->get('created_by')]
			)
		]);

		// Redirect to main listing
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&section=' . $section . '&category=' . $category),
			Lang::txt('COM_FORUM_THREAD_DELETED'),
			'message'
		);
	}

	/**
	 * Serves up files only after passing access checks
	 *
	 * @return  void
	 */
	public function downloadTask()
	{
		// Incoming
		$section   = Request::getString('section', '');
		$category  = Request::getString('category', '');
		$thread_id = Request::getInt('thread', 0);
		$post_id   = Request::getInt('post', 0);
		$file      = Request::getString('file', '');

		// Instantiate an attachment object
		if (!$post_id)
		{
			$attach = Attachment::oneByThread($thread_id, $file);
		}
		else
		{
			$attach = Attachment::oneByPost($post_id);
		}

		if (!$attach->get('filename'))
		{
			App::abort(404, Lang::txt('COM_FORUM_FILE_NOT_FOUND'));
		}

		// Get the parent ticket the file is attached to
		$post = $attach->post();

		if (!$post->get('id') || $post->get('state') == $post::STATE_DELETED)
		{
			App::abort(404, Lang::txt('COM_FORUM_POST_NOT_FOUND'));
		}

		// Check logged in status
		if (User::isGuest() && !in_array($post->get('access'), User::getAuthorisedViewLevels()))
		{
			$return = base64_encode(Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&section=' . $section . '&category=' . $category . '&thread=' . $thread_id . '&post=' . $post_id . '&file=' . $file));
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . $return)
			);
		}

		// Load ACL
		$this->_authorize('thread', $post->get('thread'));

		// Ensure the user is authorized to view this file
		if (!$this->config->get('access-view-thread'))
		{
			App::abort(403, Lang::txt('COM_FORUM_NOT_AUTH_FILE'));
		}

		// Get the configured upload path
		$filename = $attach->path();

		// Ensure the file exist
		if (!file_exists($filename))
		{
			App::abort(404, Lang::txt('COM_FORUM_FILE_NOT_FOUND') . ' ' . substr($filename, strlen(PATH_ROOT)));
		}

		// Initiate a new content server and serve up the file
		$server = new \Hubzero\Content\Server();
		$server->filename($filename);
		$server->disposition('inline');
		$server->acceptranges(false); // @TODO fix byte range support

		if (!$server->serve())
		{
			// Should only get here on error
			App::abort(500, Lang::txt('COM_FORUM_SERVER_ERROR'));
		}

		exit;
	}

	/**
	 * Uploads a file to a given directory and returns an attachment string
	 * that is appended to report/comment bodies
	 *
	 * @param   integer  $thread_id  Directory to upload files to
	 * @param   integer  $post_id    Post ID
	 * @return  boolean
	 */
	public function uploadTask($thread_id, $post_id)
	{
		// Check if they are logged in
		if (User::isGuest())
		{
			return false;
		}

		if (!$thread_id)
		{
			$this->setError(Lang::txt('COM_FORUM_NO_UPLOAD_DIRECTORY'));
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

			$this->setError(Lang::txt('COM_FORUM_ERROR_UPLOADING_FILE_TOO_BIG', $max));
			return false;
		}

		// Ensure file names fit.
		$ext = \Filesystem::extension($file['name']);

		// Check that the file type is allowed
		$allowed = array_values(array_filter(explode(',', $mediaConfig->get('upload_extensions'))));

		if (!empty($allowed) && !in_array(strtolower($ext), $allowed))
		{
			$this->setError(Lang::txt('COM_FORUM_ERROR_UPLOADING_INVALID_FILE', implode(', ', $allowed)));
			return false;
		}

		// Upload file
		if (!$attachment->upload($file['name'], $file['tmp_name']))
		{
			$this->setError($attachment->getError());
		}
		else
		{
			// Save entry
			if (!$attachment->save())
			{
				$this->setError($attachment->getError());
			}
		}

		return true;
	}

	/**
	 * Set access permissions for a user
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
			if ($assetId)
			{
				$asset .= ($assetType != 'component') ? '.' . $assetType : '';
				$asset .= ($assetId) ? '.' . $assetId : '';
			}

			$at = '';
			if ($assetType != 'component')
			{
				$at .= '.' . $assetType;
			}

			// Admin
			$this->config->set('access-admin-' . $assetType, User::authorise('core.admin', $asset));
			$this->config->set('access-manage-' . $assetType, User::authorise('core.manage', $asset));
			// Permissions
			if ($assetType == 'post' || $assetType == 'thread')
			{
				$this->config->set('access-create-' . $assetType, true);
				$val = User::authorise('core.create' . $at, $asset);
				if ($val !== null)
				{
					$this->config->set('access-create-' . $assetType, $val);
				}

				$this->config->set('access-edit-' . $assetType, true);
				$val = User::authorise('core.edit' . $at, $asset);
				if ($val !== null)
				{
					$this->config->set('access-edit-' . $assetType, $val);
				}

				$this->config->set('access-edit-own-' . $assetType, true);
				$val = User::authorise('core.edit.own' . $at, $asset);
				if ($val !== null)
				{
					$this->config->set('access-edit-own-' . $assetType, $val);
				}

				$this->config->set('access-delete-' . $assetType, true);
				$val = User::authorise('core.delete' . $at, $asset);
				if ($val !== null)
				{
					$this->config->set('access-delete-' . $assetType, $val);
				}
			}
			else
			{
				$this->config->set('access-create-' . $assetType, User::authorise('core.create' . $at, $asset));
				$this->config->set('access-edit-' . $assetType, User::authorise('core.edit' . $at, $asset));
				$this->config->set('access-edit-own-' . $assetType, User::authorise('core.edit.own' . $at, $asset));
				$this->config->set('access-delete-' . $assetType, User::authorise('core.delete' . $at, $asset));
			}

			//$this->config->set('access-delete-' . $assetType, User::authorise('core.delete' . $at, $asset));
			$this->config->set('access-edit-state-' . $assetType, User::authorise('core.edit.state' . $at, $asset));
		}
	}
}
