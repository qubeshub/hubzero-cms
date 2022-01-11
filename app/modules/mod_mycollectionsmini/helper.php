<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Modules\MyCollectionsMini;

use Hubzero\Module\Module;
use Components\Collections\Models;
use User;

/**
 * Module class for displaying a list of groups for a user
 */
class Helper extends Module
{
	/**
	 * Get groups for a user
	 *
	 * @param   integer  $uid   User ID
	 * @return  array
	 */
	private function _getPosts($uid)
	{
		include_once \Component::path('com_collections') . DS . 'models' . DS . 'archive.php';

		// Filters for returning results
		$filters = array(
			'limit'       => $this->limit,
			'created_by'  => $uid,
			'state'       => 1,
			'object_id'   => $uid, // Remove this and object_type to get group posts as well
			'object_type' => 'member',
			'user_id'	  => $uid,
			'access'      => -1,
			'sort'        => 'created',
			'sort_Dir'    => 'desc'
		);
		
		$archive = new \Components\Collections\Models\Archive('member', $uid);

		// Pull relevant info
		$posts = new \Hubzero\Base\ItemList();
		foreach ($archive->posts($filters) as $post) {
			$pinfo = new \stdClass();

			$item = $post->item();
			$pinfo->title = ($item->get('title') ? $item->get('title') : '<em>Missing title</em>');
			$pinfo->post_url = Route::url('index.php?option=com_collections&controller=posts&post=' . $post->get('id') . '&task=comment');
			$pinfo->special = null;

			if ($item->type() == 'publication') {
				include_once \Component::path('com_publications') . DS . 'models' . DS . 'publication.php';
				$resource = new \Components\Publications\Models\Publication(null, null, $item->get('object_id'));
				$imgPath = Route::url($resource->link('masterimage'));
				$alt = $this->escape(stripslashes($resource->get('title', '')));
				$pinfo->special = array(
					'class' => 'publication',
					'link_name' => 'Resource',
					'link_url' => Route::url($resource->link('version'))
				);
			} else {
				// File type
				$path = $item->filespace() . DS . $item->get('id');
				$assets = $item->assets();
				$imgPath = null;
				foreach ($assets as $asset) {
					if ($asset->image()) {
						// $isLocal = (filter_var($asset->file('original'), FILTER_VALIDATE_URL)) ? false : true;
						// $imgPath = $isLocal ? $path . DS . $asset->file('thumbnail') : $asset->file('original');
						$imgPath = $asset->link('thumb');
						$alt = ($asset->get('description')) ? $this->escape(stripslashes($asset->get('description'))) : Lang::txt('COM_COLLECTIONS_IMAGE_ALT', ltrim($asset->get('filename'), DS));
						break;
					}
				}
			}
			if ($imgPath) {
				$pinfo->img_html = "<img src='" . $imgPath . "' alt='" . $alt . "'>";
			} else {
				// Missing image - use default image based on type
				$pinfo->img_html = file_get_contents(PATH_CORE . DS . "assets/icons/" . ($item->type() == 'file' ? 'file': 'book') . ".svg");
			}

			$posts->add($pinfo);
		}

		return $posts;
	}

	private function _getThumbnail()
	{
	}

	/**
	 * Display module contents
	 *
	 * @return  void
	 */
	public function display()
	{
		// Get the module parameters
		$this->moduleclass = $this->params->get('moduleclass');
		$this->limit = intval($this->params->get('limit', 100));

		// Get the user's posts
		$this->allposts = $this->_getPosts(User::get('id'));

		$layout = 'default';

		require $this->getLayoutPath($layout);
	}
}
