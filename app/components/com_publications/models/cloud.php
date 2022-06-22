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
		// Default to keywords
		if (!isset($filters['focusarea'])) {
			$filters['focusarea'] = false;
		}

		switch (strtolower($rtrn))
		{
			case 'string':
				if (!isset($this->_cache['tags.string']) || $clear)
				{
					$tags = array();
					foreach ($this->tags('list', $filters, $clear) as $tag)
					{
						$tags[] = $tag->get('raw_tag');
					}
					$this->_cache['tags.string'] = implode(', ', $tags);
				}
				return $this->_cache['tags.string'];
			break;

			case 'array':
				$tags = array();
				foreach ($this->tags('list', $filters, $clear) as $tag)
				{
					$tags[] = $tag->get('tag');
				}
				return $tags;
			break;

			case 'cloud':
			case 'html':
			default:
				$layout = (!$filters['focusarea'] ? 'cloud' : 'focusareas');
				if (!isset($this->_cache['tags.' . $layout]) || $clear) {
					$tags = $this->tags('list', $filters, $clear);
					
					$view = new View(array(
						'base_path' => dirname(__DIR__) . '/site',
						'name'      => 'tags',
						'layout'    => '_' . $layout
					));
					$view->set('config', \Component::params('com_tags'))
						->set('tags', $tags);

					$this->_cache['tags.' . $layout] = $view->loadTemplate();
				}
				return $this->_cache['tags.' . $layout];
			break;
		}
	}
}  