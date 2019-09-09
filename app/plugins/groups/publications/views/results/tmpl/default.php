<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2019 HUBzero Foundation, LLC.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$this->css()
     ->css('publications.css', 'com_publications')
     ->css('resource_plugin.min.css');

$this->js()
     ->js('publications.js', 'com_publications')
     ->js('resource_plugin.js');

//<group:include type="stylesheet" source="/uploads/css/resource_plugin.min.css" />
//<group:include type="stylesheet" source="/uploads/css/list_view.css" />
//<group:include type="script" source="/uploads/js/resource_plugin.js" />
$config = Component::params('com_publications');
// An array for storing all the links we make
$links = array();
//$html = '';
if ($this->cats)
{
	// Loop through each category
	foreach ($this->cats as $cat)
	{
		// Only show categories that have returned search results
		if ($cat['total'] > 0)
		{
			// Is this the active category?
			$a = ($cat['category'] == $this->active) ? ' class="active"' : '';
			// If we have a specific category, prepend it to the search term
			$blob = ($cat['category']) ? $cat['category'] : '';
			// Build the HTML
			$l = "\t" . '<li' . $a . '><a href="' . Route::url('index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=publications&area='. urlencode(stripslashes($blob))) . '">' . $this->escape(stripslashes($cat['title'])) . ' <span class="item-count">' . $cat['total'] . '</span></a>';
			// Are there sub-categories?
			if (isset($cat['_sub']) && is_array($cat['_sub']))
			{
				// An array for storing the HTML we make
				$k = array();
				// Loop through each sub-category
				foreach ($cat['_sub'] as $subcat)
				{
					// Only show sub-categories that returned search results
					if ($subcat['total'] > 0)
					{
						// Is this the active category?
						$a = ($subcat['category'] == $this->active) ? ' class="active"' : '';
						// If we have a specific category, prepend it to the search term
						$blob = ($subcat['category']) ? $subcat['category'] : '';
						// Build the HTML
						$k[] = "\t\t\t" . '<li' . $a . '><a href="' . Route::url('index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=publications&area='. urlencode(stripslashes($blob))) . '">' . $this->escape(stripslashes($subcat['title'])) . ' <span class="item-count">' . $subcat['total'] . '</span></a></li>';
					}
				}
				// Do we actually have any links?
				// NOTE: this method prevents returning empty list tags "<ul></ul>"
				if (count($k) > 0)
				{
					$l .= "\t\t" . '<ul>' . "\n";
					$l .= implode("\n", $k);
					$l .= "\t\t" . '</ul>' . "\n";
				}
			}
			$l .= '</li>';
			$links[] = $l;
		}
	}
}
?>

<div class="group_resources">
  <h2>Resources</h2>

  <ul id="page_options">
    <li><a href= "#" class="icon-add add btn">Submit a Resource</a></li>
    <li><a href="#" class="icon-config config btn">Settings</a></li>
  </ul>

<main class="main section">
  <div class="filter">
     Filter
     <form>
       <div class="filter-category">
         <span>Type</span>

         <div>
           <input type="checkbox" id="teaching_material" name="type" value="teaching_material" checked>
           <label for="teaching_material">Teaching Material</label>
         </div>

         <div>
           <input type="checkbox" id="reference_material" name="type" value="reference_material" checked>
           <label for="reference_material">Reference Material</label>
         </div>

         <div>
           <input type="checkbox" id="software" name="type" value="software" checked>
           <label for="software">Software</label>
         </div>
       </div>

       <div class="filter-category">
         <span>Tags</span>

         <div>
           <input type="checkbox" id="tag1" name="tag" value="tag1" checked>
           <label for="tag1">Tag 1</label>
         </div>

         <div>
           <input type="checkbox" id="tag2" name="tag" value="tag2" checked>
           <label for="tag2">Tag 2</label>
         </div>

         <div>
           <input type="checkbox" id="tag3" name="tag" value="tag3" checked>
           <label for="tag3">Tag 3</label>
         </div>

         <div>
           <input type="checkbox" id="tag4" name="tag" value="tag4" checked>
           <label for="tag4">Tag 4</label>
         </div>
       </div>

       <button type="submit" name="submit">Filter</button>
       <button type="reset" name="reset">Reset</button>
     </form>
   </div>

   <div class="resource_contents">
     <div class="resource_header">
       <nav class="dis-in-bl entries-filter">
         Sort by:
         <ul class="sort-option entries-menu filter-options">
           <li class="active" ><a>Newest</a></li>
           <li><a>Activity</a></li>
         </ul>
       </nav>

       <ul class="entries-menu view-option">
         <li><a href="<?php echo Route::url('index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=publications&viewType=card') ?>" class="info-th">Card View</a></li>
         <li><a href="<?php echo Route::url('index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=publications&viewType=list') ?>" class="info-th-list">List View</a></li>
       </ul>
     </div>

     <div class="resource_content">
       <ul class="img-option entries-menu filter-options">
         Images:
         <li><a>On</a></li>
         <li class="active"><a>Off</a></li>
       </ul>

		 <div class="container-block">
			 <?php
       //$html='';
			 if ($this->html)
			 {
				 echo $this->html;
			 }

       else
       {
         echo '<p class="warning">' . Lang::txt('PLG_GROUPS_PUBLICATIONS_NONE') . '</p>';
       }
			 ?>
		 </div><!-- / .container-block -->

     <?php

     $pageNav = $this->pagination(
			 $this->total,
			 $this->limitstart,
			 $this->limit
		 );
		 $pageNav->setAdditionalUrlParam('cn', $this->group->get('cn'));
		 $pageNav->setAdditionalUrlParam('active', 'publications');
		 $pageNav->setAdditionalUrlParam('area', urlencode(stripslashes($this->active)));
		 $pageNav->setAdditionalUrlParam('sort', $this->sort);
		 $pageNav->setAdditionalUrlParam('access', $this->access);
		 echo $pageNav->render();

     ?>

		 <div class="clearfix"></div>

     </div>
   </div>
 </main>
</div>
