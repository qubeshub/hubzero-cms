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
				if (!isset($this->_cache['tags.cloud']) || $clear)
				{
                    // Get pub pid information; _scope_id is version
                    $query = "SELECT publication_id FROM `#__publication_versions` WHERE id = " . $this->_scope_id;
                    $this->_db->setQuery($query); 
                    $pid = $this->_db->loadResult();

                    // Get focus area tags
                    $recommendedTagsHelper = new \Components\Publications\Helpers\RecommendedTags($pid, $this->_scope_id, App::get('db'));
                    $fa_flat = $recommendedTagsHelper->get_existing_focus_areas_map();
                    $fa_tree = $recommendedTagsHelper->loadFocusAreas(null, null, null, $fa_flat);

                    // Get admin info for keywords and add to end of $fa_tree
                    foreach ($recommendedTagsHelper->get_existing_tags() as $raw_tag) {
                        $tag_model = Tag::oneByTag($raw_tag);
                        $atag = array(
                            'tag' => $tag_model->get('tag'),
                            'raw_tag' => $raw_tag,
                            'admin' => $tag_model->get('admin'),
                            'children' => array()
                        );
                        $fa_tree[$raw_tag] = $atag;
                    }

					$view = new View(array(
						'base_path' => dirname(__DIR__) . '/site',
						'name'      => 'tags',
						'layout'    => '_cloud'
					));
					$view->set('config', \Component::params('com_tags'))
					     ->set('tags', $recommendedTagsHelper->flatten_paths($fa_tree));

					$this->_cache['tags.cloud'] = $view->loadTemplate();
				}
				return $this->_cache['tags.cloud'];
			break;
		}
	}
}  