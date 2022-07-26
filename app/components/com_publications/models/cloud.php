<?php  
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Models;  
  
use Components\Tags\Models\Tag;
use Components\Tags\Models\Cloud;
use Hubzero\Component\View;
  
require_once \Component::path('com_tags') . DS . 'models' . DS . 'tag.php';
require_once \Component::path('com_tags') . DS . 'models' . DS . 'cloud.php';
require_once \Component::path('com_tags') . DS . 'models' . DS . 'focusarea.php';
  
class PubCloud extends Cloud  
{  
    /** 
     * Object type, used for linking objects (such as resources) to tags 
     *  
     * @var string 
     */  
    protected $_scope = 'publications';  

    /**
	 * Render a tag cloud
	 *
	 * @param   string   $rtrn     Format to render
	 * @param   array    $filters  Filters to apply
	 * @param   boolean  $clear    Clear cached data?
	 * @return  string
	 */
	public function render($rtrn='html', $filters=array(), $clear=false)
	{
		// Default to all
		if (!isset($filters['type'])) {
			$type = 'all';
		} else {
			$type = $filters['type']; // keywords (or) focusareas
		}

		switch (strtolower($rtrn))
		{
			case 'string':
				if (!isset($this->_cache['tags.string.' . $type]) || $clear)
				{
					if ($type == 'all' || $type == 'keywords') {
						$tags = array();
						foreach ($this->tags('list', $filters, true) as $tag)
						{
							$tags[] = $tag->get('raw_tag');
						}
					} else {
						$focus_area = new \Components\Tags\Models\FocusArea;
						$tags = $this->tags('list', $filters, true);
						$fas = $focus_area->fromTags($tags);
						$tags = array();
						foreach ($fas as $fa) {
							$tags[] = $fa->label;
						}
					}
					$this->_cache['tags.string.' . $type] = implode(', ', $tags);
				}
				return $this->_cache[('tags.string.' . $type)];
			break;

			case 'array':
				$tags = array();
				foreach ($this->tags('list', $filters, $clear) as $tag)
				{
					$tags[] = $tag->get('tag');
				}
				return $tags;
			break;

			case 'search':
				if (!isset($this->_cache['tags.search.' . $type]) || $clear) {
					if ($type == 'keywords') {
						$keywords = $this->tags('list', $filters, true);
						$tags = array();
						foreach ($keywords as $keyword)
						{
							$tags[] = $keyword->get('tag');
						}
					} else {
						$focus_area = new \Components\Tags\Models\FocusArea;
						$tags = $this->tags('list', $filters, true);
						$fas = $focus_area->fromTags($tags);
						$roots = $focus_area->parents($fas, true);
						$tags = array();
						foreach ($roots as $root) {
							$tags[] = $focus_area->render($root, 'search');
						}
						$tags = (count($tags) ? array_merge(...$tags) : $tags);
					}
					$this->_cache['tags.search.' . $type] = $tags;
				}
				return $this->_cache['tags.search.' . $type];
			break;

			case 'cloud':
			case 'html':
			default:
				if (!isset($this->_cache['tags.cloud.' . $type]) || $clear) {
					$tags = $this->tags('list', $filters, true);
					
					$view = new View(array(
						'base_path' => dirname(__DIR__) . '/site',
						'name'      => 'tags',
						'layout'    => '_' . $type
					));
					$view->set('config', \Component::params('com_tags'))
						->set('tags', $tags);

					$this->_cache['tags.cloud.' . $type] = $view->loadTemplate();
				}
				return $this->_cache['tags.cloud.' . $type];
			break;
		}
	}
}  