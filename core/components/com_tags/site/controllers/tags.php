<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Tags\Site\Controllers;

use Hubzero\Component\SiteController;
use Components\Tags\Models\Cloud;
use Components\Tags\Models\Tag;
use Hubzero\Utility\Str;
use Hubzero\Utility\Sanitize;
use Document;
use Request;
use Pathway;
use Config;
use Notify;
use Event;
use Cache;
use Route;
use Lang;
use User;
use App;

/**
 * Controller class for tags
 */
class Tags extends SiteController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->_authorize();

		$this->registerTask('feed.rss', 'feed');
		$this->registerTask('feedrss', 'feed');

		if ($tagstring = urldecode(trim(Request::getString('tag', ''))))
		{
			if (!Request::getCmd('task', ''))
			{
				Request::setVar('task', 'view');
			}
		}

		parent::execute();
	}

	/**
	 * Display the main page for this component
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Set the page title
		$this->_buildTitle(null);

		// Set the pathway
		$this->_buildPathway(null);

		$this->view
			->set('cloud', new Cloud())
			->set('config', $this->config)
			->display();
	}

	/**
	 * View items tagged with this tag
	 *
	 * @return  void
	 */
	public function viewTask()
	{
		// Incoming
		$tagstring = urldecode(htmlspecialchars_decode(trim(Request::getString('tag', ''))));

		$addtag = trim(Request::getString('addtag', ''));

		// Ensure we were passed a tag
		if (!$tagstring && !$addtag)
		{
			if (Request::getWord('task', '', 'get'))
			{
				App::redirect(
					Route::url('index.php?option=' . $this->_option)
				);
			}

			App::abort(404, Lang::txt('COM_TAGS_NO_TAG'));
		}

		if ($tagstring)
		{
			// Break the string into individual tags
			$tgs = explode(',', $tagstring);
			$tgs = array_map('trim', $tgs);
		}
		else
		{
			$tgs = array();
		}

		// See if we're adding any tags to the search list
		if ($addtag && !in_array($addtag, $tgs))
		{
			$tgs[] = $addtag;
		}

		// Sanitize the tag
		$tags  = array();
		$added = array();
		$rt    = array();
		foreach ($tgs as $tag)
		{
			// Load the tag
			$tagobj = Tag::oneByTag($tag);

			if (in_array($tagobj->get('tag'), $added))
			{
				continue;
			}

			if ($tagobj->get('admin') == 1 && !User::authorise('core.manage', $this->_option))
			{
				continue;
			}

			$added[] = $tagobj->get('tag');

			// Ensure we loaded the tag's info from the database
			if ($tagobj->get('id'))
			{
				$tags[] = $tagobj;
				$rt[]   = $tagobj->get('raw_tag');
			}
			else
			{
				$rt[]   = $tag;
			}
		}

		$this->view->total      = 0;
		$this->view->results    = null;
		$this->view->categories = array();

		// Incoming paging vars
		$this->view->filters = array(
			'limit' => Request::getInt('limit', Config::get('list_limit')),
			'start' => Request::getInt('limitstart', 0),
			'sort'  => Request::getCmd('sort', 'date')
		);
		if (!in_array($this->view->filters['sort'], array('date', 'title')))
		{
			App::abort(404, Lang::txt('Invalid sort value of "%s".', $this->view->filters['sort']));
		}

		$parent = Request::getString('parent', '');
		$this->view->parent = $parent;

		// Get the active category
		$area = Request::getString('area', '');

		// Ensure we loaded the tag's info from the database
		if (empty($tags))
		{
			//App::abort(404, Lang::txt('COM_TAGS_TAG_NOT_FOUND'));
		}
		else
		{
			$this->view->categories = Event::trigger('tags.onTagView', array(
				$tags,
				$this->view->filters['limit'],
				$this->view->filters['start'],
				$this->view->filters['sort'],
				$area
			));
			
			if (!$area)
			{
				$query = '';
				if ($this->view->categories)
				{
					$s = array();
					foreach ($this->view->categories as $response)
					{
						$this->view->total += $response['total'];

						if (is_array($response['sql']))
						{
							continue;
						}
						if (trim($response['sql']) != '')
						{
							$s[] = $response['sql'];
						}
						if (isset($response['children']))
						{
							foreach ($response['children'] as $sresponse)
							{
								//$this->view->total += $sresponse['total'];

								if (is_array($sresponse['sql']))
								{
									continue;
								}
								if (trim($sresponse['sql']) != '')
								{
									$s[] = $sresponse['sql'];
								}
							}
						}
					}
					$query .= "(";
					$query .= implode(") UNION (", $s);
					$query .= ") ORDER BY ";
					switch ($this->view->filters['sort'])
					{
						case 'title':
							$query .= 'title ASC, publish_up';
							break;
						case 'id':
							$query .= "id DESC";
							break;
						case 'date':
						default:
							$query .= 'publish_up DESC, title';
							break;
					}
					if ($this->view->filters['limit'] != 'all'
					 && $this->view->filters['limit'] > 0)
					{
						$query .= " LIMIT " . $this->view->filters['start'] . "," . $this->view->filters['limit'];
					}
				}
				$this->database->setQuery($query);
				$this->view->results = $this->database->loadObjectList();
			}
			else
			{
				if ($this->view->categories)
				{
					foreach ($this->view->categories as $response)
					{
						$this->view->total += $response['total'];
					}
					foreach ($this->view->categories as $response)
					{
						//$this->view->total += $response['total'];

						if (is_array($response['results']) && !empty($response['results']) && !$parent)
						{
							$this->view->results = $response['results'];
							break;
						}

						if (isset($response['children']) && $parent)
						{
							foreach ($response['children'] as $sresponse)
							{
								//$this->view->total += $sresponse['total'];

								if (is_array($sresponse['results']) && !empty($sresponse['results']) && ($parent == $response['name']))
								{
									$this->view->results = $sresponse['results'];
									break 2;
								}
							}
						}
					}
				}
			}
		}

		$related = null;
		if (count($tags) == 1)
		{
			$this->view->tagstring = $tags[0]->get('tag');
		}
		else
		{
			$tagstring = array();
			foreach ($tags as $tag)
			{
				$tagstring[] = $tag->get('tag');
			}
			$this->view->tagstring = implode('+', $tagstring);
		}

		// Set the pathway
		$this->_buildPathway($tags);

		// Set the page title
		$this->_buildTitle($tags);

		// Output HTML
		if (Request::getString('format', '') == 'xml')
		{
			$this->view->setLayout('view_xml');
		}

		$this->view
			->set('tags', $tags)
			->set('active', $area)
			->set('search', implode(', ', $rt))
			->setError($this->getErrors())
			->display();
	}

	/**
	 * Returns results (JSON format) for a search string
	 * Used for autocompletion scripts called via AJAX
	 *
	 * @return  string  JSON
	 */
	public function autocompleteTask()
	{
		$filters = array(
			'start'  => 0,
			'search' => trim(Request::getString('value', ''))
		);

		if (!User::authorise('core.manage'))
		{
			$filters['admin'] = 0;
		}

		// Create a Tag object
		$cloud = new Cloud();

		// Fetch results
		$rows = $cloud->tags('list', $filters);

		// Output search results in JSON format
		$json = array();
		$exactMatches = array();
		$beginsWithWord =array();
		$beginWith = array();

		if (count($rows) > 0)
		{
			foreach ($rows as $row)
			{
				$name = str_replace("\n", '', stripslashes(trim($row->get('raw_tag'))));
				$name = str_replace("\r", '', $name);

				$item = array(
					'id'   => $row->get('tag'),
					'name' => $name
				);

				// Find the exact match
				if (strtolower($row->get('tag')) == strtolower($filters['search']) || strtolower($name) == strtolower($filters['search']))
				{
					$exactMatches[] = $item;
				}
				elseif (stripos($row->get('tag'), $filters['search'] . ' ') === 0 || stripos($name, $filters['search'] . ' ') === 0)
				{
					$beginsWithWord[] = $item;
				}
				// Prioritize beginning of the string
				elseif (stripos($row->get('tag'), $filters['search']) === 0 || stripos($name, $filters['search']) === 0)
				{
					$beginWith[] = $item;
				}
				else
				{
					$json[] = $item;
				}
			}
		}

		// Push matches that start with the search to the front
		if (sizeof($beginWith))
		{
			// Sort the array
			$name = array_column($beginWith, 'name');
			array_multisort($name, SORT_DESC, SORT_NATURAL|SORT_FLAG_CASE , $beginWith);

			foreach ($beginWith as $match)
			{
				array_unshift($json, $match);
			}
		}

		// Push matches that begin with a word to the front
		if (sizeof($beginsWithWord))
		{
			// Sort the array
			$name = array_column($beginsWithWord, 'name');
			array_multisort($name, SORT_DESC, SORT_NATURAL|SORT_FLAG_CASE, $beginsWithWord);

			foreach ($beginsWithWord as $match)
			{
				array_unshift($json, $match);
			}
		}

		// Push exact matches to the front
		if (sizeof($exactMatches))
		{
			foreach ($exactMatches as $exactMatch)
			{
				array_unshift($json, $exactMatch);
			}
		}

		$json = array_slice($json, 0, 20);

		echo json_encode($json);
	}

	/**
	 * Generate an RSS feed
	 *
	 * @return  string  RSS
	 */
	public function feedTask()
	{
		// Incoming
		$tagstring = trim(Request::getString('tag', ''));

		// Ensure we were passed a tag
		if (!$tagstring)
		{
			App::abort(404, Lang::txt('COM_TAGS_NO_TAG'));
		}

		// Break the string into individual tags
		$tgs = explode(',', $tagstring);

		// Sanitize the tag
		$tags  = array();
		$added = array();
		foreach ($tgs as $tag)
		{
			// Load the tag
			$tagobj = Tag::oneByTag($tag);

			if (in_array($tagobj->get('tag'), $added))
			{
				continue;
			}

			$added[] = $tagobj->get('tag');

			// Ensure we loaded the tag's info from the database
			if ($tagobj->get('id'))
			{
				$tags[] = $tagobj;
			}
		}

		// Paging variables
		$limitstart = Request::getInt('limitstart', 0);
		$limit = Request::getInt('limit', Config::get('list_limit'));

		$areas = Event::trigger('tags.onTagAreas');

		// Get the active category
		$area = Request::getString('area', '');
		$sort = Request::getString('sort', '');

		// Get the search results
		if (!$area)
		{
			$sqls = Event::trigger('tags.onTagView',
				array(
					$tags,
					$limit,
					$limitstart,
					$sort,
					''
				)
			);
			if ($sqls)
			{
				$s = array();
				foreach ($sqls as $response)
				{
					if (is_array($response['sql']))
					{
						continue;
					}
					if (trim($response['sql']) != '')
					{
						$s[] = $response['sql'];
					}
					if (isset($response['children']))
					{
						foreach ($response['children'] as $sresponse)
						{
							if (is_array($sresponse['sql']))
							{
								continue;
							}
							if (trim($sresponse['sql']) != '')
							{
								$s[] = $sresponse['sql'];
							}
						}
					}
				}

				$query  = "(";
				$query .= implode(") UNION (", $s);
				$query .= ") ORDER BY ";
				switch ($sort)
				{
					case 'title':
						$query .= 'title ASC, publish_up';
						break;
					case 'id':
						$query .= "id DESC";
						break;
					case 'date':
					default:
						$query .= 'publish_up DESC, title';
						break;
				}
				if ($limit != 'all'
				 && $limit > 0)
				{
					$query .= " LIMIT " . $limitstart . "," . $limit;
				}
			}
			$this->database->setQuery($query);
			$rows = $this->database->loadObjectList();
		}
		else
		{
			$results = Event::trigger('tags.onTagView',
				array(
					$tags,
					$limit,
					$limitstart,
					$sort,
					$area
				)
			);

			// Run through the array of arrays returned from plugins and find the one that returned results
			$rows = array();
			if ($results)
			{
				foreach ($results as $result)
				{
					if (is_array($result['results']))
					{
						$rows = $result['results'];
						break;
					}

					if (isset($result['children']))
					{
						foreach ($result['children'] as $sresponse)
						{
							if (is_array($sresponse['results']))
							{
								$rows = $sresponse['results'];
								break;
							}
						}
					}
				}
			}
		}

		// Build some basic RSS document information
		$title = Lang::txt(strtoupper($this->_option)) . ': ';
		for ($i=0, $n=count($tags); $i < $n; $i++)
		{
			if ($i > 0)
			{
				$title .= '+ ';
			}
			$title .= $tags[$i]->get('raw_tag') . ' ';
		}
		$title = trim($title);
		$title .= ': ' . $area;

		// Set the mime encoding for the document
		Document::setType('feed');

		// Start a new feed object
		$doc = Document::instance();
		$doc->link        = Route::url('index.php?option=' . $this->_option);
		$doc->title       = Config::get('sitename') . ' - ' . $title;
		$doc->description = Lang::txt('COM_TAGS_RSS_DESCRIPTION', Config::get('sitename'), $title);
		$doc->copyright   = Lang::txt('COM_TAGS_RSS_COPYRIGHT', gmdate("Y"), Config::get('sitename'));
		$doc->category    = Lang::txt('COM_TAGS_RSS_CATEGORY');

		// Start outputing results if any found
		if (count($rows) > 0)
		{
			foreach ($rows as $row)
			{
				// Prepare the title
				$title = strip_tags($row->title);
				$title = html_entity_decode($title);

				// Strip html from feed item description text
				$description = html_entity_decode(Str::truncate(strip_tags(stripslashes($row->ftext)), 300));
				$author = '';
				@$date = ($row->publish_up ? date('r', strtotime($row->publish_up)) : '');

				if (isset($row->data3) || isset($row->rcount))
				{
					$author = strip_tags($row->data3);
				}

				// Load individual item creator class
				$item = new \Hubzero\Document\Type\Feed\Item();
				$item->title       = $title;
				$item->link        = \Route::url($row->href);
				$item->description = '<![CDATA[' . $description . ']]>';
				$item->date        = $date;
				$item->category    = (isset($row->data1) && $row->data1) ? $row->data1 : $row->section;
				$item->author      = $author;

				// Loads item info into rss array
				$doc->addItem($item);
			}
		}
	}

	/**
	 * Browse the list of tags
	 *
	 * @return  void
	 */
	public function browseTask()
	{
		// Instantiate a new view
		if (Request::getString('format', '') == 'xml')
		{
			$this->view->setLayout('browse_xml');
		}

		// Fallback support for deprecated sorting option
		if ($sortby = Request::getString('sortby'))
		{
			Request::setVar('sort', $sortby);
		}

		// Incoming
		$filters = array(
			'admin' => 0,
			'start' => Request::getState(
				$this->_option . '.' . $this->_controller . '.limitstart',
				'limitstart',
				0,
				'int'
			),
			'limit' => Request::getState(
				$this->_option . '.' . $this->_controller . '.limit',
				'limit',
				Config::get('list_limit'),
				'int'
			),
			'search' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.search',
				'search',
				'',
				'string'
			)),
			'sort' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'sort',
				'raw_tag',
				'string'
			)),
			'sort_Dir' => strtolower(Request::getState(
				$this->_option . '.' . $this->_controller . '.sort_Dir',
				'sortdir',
				'asc',
				'string'
			))
		);

		if (!in_array($filters['sort'], array('raw_tag', 'total')))
		{
			$filters['sort'] = 'raw_tag';
		}
		if (!in_array($filters['sort_Dir'], array('asc', 'desc')))
		{
			$filters['sort_Dir'] = 'asc';
		}

		$t = new Cloud();

		// Record count
		$total = $t->tags('count', $filters);

		// Get records
		$rows = $t->tags('list', $filters);

		// Set the pathway
		$this->_buildPathway();

		// Set the page title
		$this->_buildTitle();

		$this->view
			->set('rows', $rows)
			->set('total', $total)
			->set('filters', $filters)
			->set('config', $this->config)
			->display();
	}

	/**
	 * Create a new tag
	 *
	 * @return  void
	 */
	public function createTask()
	{
		$this->editTask();
	}

	/**
	 * Show a form for editing a tag
	 *
	 * @param   object  $tag
	 * @return  void
	 */
	public function editTask($tag=null)
	{
		// Check that the user is authorized
		if (!$this->config->get('access-edit-tag'))
		{
			App::abort(403, Lang::txt('ALERTNOTAUTH'));
		}

		// Load a tag object if one doesn't already exist
		if (!is_object($tag))
		{
			// Incoming
			$tag = Tag::oneOrNew(Request::getInt('id', 0));
		}

		$filters = array(
			'limit'    => Request::getInt('limit', 0),
			'start'    => Request::getInt('limitstart', 0),
			'sort'     => Request::getString('sort', ''),
			'sort_Dir' => Request::getString('sortdir', ''),
			'search'   => urldecode(Request::getString('search', ''))
		);

		// Set the pathway
		$this->_buildPathway();

		// Set the page title
		$this->_buildTitle();

		$this->view
			->set('tag', $tag)
			->set('filters', $filters)
			->setLayout('edit')
			->display();
	}

	/**
	 * Cancel a task and redirect to the main listing
	 *
	 * @return  void
	 */
	public function cancelTask()
	{
		$return = Request::getString('return', 'index.php?option=' . $this->_option . '&task=browse', 'get');

		App::redirect(
			Route::url($return, false)
		);
	}

	/**
	 * Save a tag
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Check that the user is authorized
		if (!$this->config->get('access-edit-tag'))
		{
			App::abort(403, Lang::txt('ALERTNOTAUTH'));
		}

		// Incoming
		$tag = Request::getArray('fields', array(), 'post');

		$subs = '';
		if (isset($tag['substitutions']))
		{
			$subs = $tag['substitutions'];
			unset($tag['substitutions']);
		}

		// Bind incoming data
		$row = Tag::oneOrFail(intval($tag['id']))->set($tag);

		// Trigger before save event
		$isNew  = $row->isNew();
		$result = Event::trigger('tags.onTagBeforeSave', array(&$row, $isNew));

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

		if (!$row->saveSubstitutions($subs))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Trigger after save event
		Event::trigger('tags.onTagAfterSave', array(&$row, $isNew));

		// Redirect to main listing
		$limit  = Request::getInt('limit', 0);
		$start  = Request::getInt('limitstart', 0);
		$sortby = Request::getInt('sortby', '');
		$search = urldecode(Request::getString('search', ''));

		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&task=browse&search=' . urlencode($search) . '&sortby=' . $sortby . '&limit=' . $limit . '&limitstart=' . $start)
		);
	}

	/**
	 * Delete one or more tags
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		// Check that the user is authorized
		if (!$this->config->get('access-delete-tag'))
		{
			App::abort(403, Lang::txt('ALERTNOTAUTH'));
		}

		// Incoming
		$ids = Request::getArray('id', array());
		if (!is_array($ids))
		{
			$ids = array();
		}

		// Make sure we have an ID
		if (empty($ids))
		{
			return $this->cancelTask();
		}

		foreach ($ids as $id)
		{
			$id = intval($id);

			// Trigger before delete event
			Event::trigger('tags.onTagDelete', array($id));

			// Remove the tag
			$tag = Tag::oneOrFail($id);
			$tag->destroy();
		}

		$this->cleancacheTask(false);

		// Get the browse filters so we can go back to previous view
		$search = Request::getString('search', '');
		$sortby = Request::getString('sortby', '');
		$limit  = Request::getInt('limit', 25);
		$start  = Request::getInt('limitstart', 0);
		$count  = Request::getInt('count', 1);

		// Redirect back to browse mode
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&task=browse&search=' . $search . '&sortby=' . $sortby . '&limit=' . $limit . '&limitstart=' . $start . '#count' . $count)
		);
	}

	/**
	 * Clean cached tags data
	 *
	 * @param   boolean  $redirect  Redirect after?
	 * @return  void
	 */
	public function cleancacheTask($redirect=true)
	{
		Cache::clean('tags');

		if (!$redirect)
		{
			return true;
		}

		$this->cancelTask();
	}

	/**
	 * Method to set the document path
	 *
	 * @param   array  $tags  Tags currently viewing
	 * @return  void
	 */
	protected function _buildPathway($tags=null)
	{
		if (Pathway::count() <= 0)
		{
			Pathway::append(
				Lang::txt(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}
		if ($this->_task && $this->_task != 'view' && $this->_task != 'display')
		{
			Pathway::append(
				Lang::txt(strtoupper($this->_option) . '_' . strtoupper($this->_task)),
				'index.php?option=' . $this->_option . '&task=' . $this->_task
			);
		}
		if (is_array($tags) && count($tags) > 0)
		{
			$t = array();
			$l = array();
			foreach ($tags as $tag)
			{
				$t[] = stripslashes($tag->get('raw_tag'));
				$l[] = $tag->get('tag');
			}

			Pathway::append(
				implode(' + ', $t),
				'index.php?option=' . $this->_option . '&tag=' . implode('+', $l)
			);
		}
	}

	/**
	 * Method to build and set the document title
	 *
	 * @param   array  $tags  Tags currently viewing
	 * @return  void
	 */
	protected function _buildTitle($tags=null)
	{
		$this->view->title = Lang::txt(strtoupper($this->_option));
		if ($this->_task && $this->_task != 'view' && $this->_task != 'display')
		{
			$this->view->title .= ': ' . Lang::txt(strtoupper($this->_option) . '_' . strtoupper($this->_task));
		}
		if (is_array($tags) && count($tags) > 0)
		{
			$t = array();
			foreach ($tags as $tag)
			{
				$t[] = stripslashes($tag->get('raw_tag'));
			}
			$this->view->title .= ': ' . implode(' + ', $t);
		}

		Document::setTitle($this->view->title);
	}

	/**
	 * Method to check admin access permission
	 *
	 * @return  boolean  True on success
	 */
	protected function _authorize($assetType='tag', $assetId=null)
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
			$this->config->set('access-create-' . $assetType, User::authorise('core.create' . $at, $asset));
			$this->config->set('access-delete-' . $assetType, User::authorise('core.delete' . $at, $asset));
			$this->config->set('access-edit-' . $assetType, User::authorise('core.edit' . $at, $asset));
			$this->config->set('access-edit-state-' . $assetType, User::authorise('core.edit.state' . $at, $asset));
			$this->config->set('access-edit-own-' . $assetType, User::authorise('core.edit.own' . $at, $asset));
		}
	}
}
