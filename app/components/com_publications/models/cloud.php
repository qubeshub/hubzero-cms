<?php  
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Models;  
  
use Components\Tags\Models\Tag;
use Components\Tags\Models\Cloud;
use Components\Tags\Models\FocusArea;
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
	 * $rtrn:
	 * 		"html" (default) => Return html of tag cloud
	 * 		"string" => Return comma-separated string of raw_tag or label (depending on "type" setting).
	 * 					The "for" $filter can be used to prepare tags for ckeditor (see above).
	 * 		"array" or "list" => Return array. $filter['key'] determines which column to return (default is 'tag')
	 * 		"search" => Return array of 'tag' column (if keywords) or flattened tags (if focus area). Used for solr indexing.
	 * 
	 * $filters:
	 * 		"type" => "keywords" or "focusareas"
	 * 		"key" => Tag variable of interest (defaults to "tag"). Used when $rtrn='array' or 'list'.
	 *		"for" => "ckeditor" (used when "type" is "keywords" and $rtrn='string')
	 *		Any other filters for $this->tags() method.
	 *
	 * @param   string   $rtrn     Format to render
	 * @param   array    $filters  Filters to apply
	 * @param   boolean  $clear    Clear cached data?
	 * 
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
		$tags = $this->tags('list', $filters, true);

		switch (strtolower($rtrn))
		{
			case 'string':
				if (!isset($this->_cache['tags.string.' . $type]) || $clear)
				{
					if ($type == 'all' || $type == 'keywords') {
						$strings = $tags->fieldsByKey('raw_tag');
						if (isset($filters['for']) && $filters['for'] == 'ckeditor') {
							$strings = array_map(function($k) {
								return str_replace('"', '&quot;', str_replace(',', '&#44;', $k));
							}, $strings);
						}
					} else {
						$strings = FocusArea::fromTags($tags)
							->rows()
							->fieldsByKey('label');
					}
					$this->_cache['tags.string.' . $type] = implode(', ', $strings);
				}
				return $this->_cache[('tags.string.' . $type)];
			break;

			case 'array':
			case 'list':
				$key = isset($filters['key']) ? $filters['key'] : 'tag';
				$array = array();
				foreach ($tags as $tag)
				{
					$array[] = $tag->get($key);
				}
				return $array;
			break;

			case 'search':
				if (!isset($this->_cache['tags.search.' . $type]) || $clear) {
					if ($type == 'keywords') {
						$search = $tags->fieldsByKey('tag');
					} else {
						$fas = FocusArea::fromTags($tags);
						$roots = $fas->parents(true);
						$search = array();
						foreach ($roots as $root) {
							$search[] = $root->render('search', array('selected' => $fas));
						}
						$search = (count($search) ? array_merge(...$search) : $search);
					}
					$this->_cache['tags.search.' . $type] = $search;
				}
				return $this->_cache['tags.search.' . $type];
			break;

			case 'cloud':
			case 'html':
			default:
				if (!isset($this->_cache['tags.cloud.' . $type]) || $clear) {
					$view = new View(array(
						'base_path' => dirname(__DIR__) . '/site',
						'name'      => 'tags',
						'layout'    => '_' . $type
					));
					$view->set('config', \Component::params('com_tags'))
						->set('scope_id', $this->_scope_id)
						->set('tags', $tags);

					$this->_cache['tags.cloud.' . $type] = $view->loadTemplate();
				}
				return $this->_cache['tags.cloud.' . $type];
			break;
		}
	}
}  