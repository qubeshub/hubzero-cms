<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2019 HUBzero Foundation, LLC.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$no_html = Request::getInt('no_html');

// No direct access
defined('_HZEXEC_') or die();

$database = App::get('db');

$this->css()
     ->css('intro')
     ->js();
?>

<?php if (!$no_html) { ?>

  <?php include_once Component::path('com_publications') . DS . 'site' . DS . 'views' . DS . 'publications' . DS . 'tmpl' . DS . 'intro.php';  ?>

  <section class="section live-update">
    <div aria-live="polite" id="live-update-wrapper">
<?php } ?>

    <div class="browse-mobile-btn-wrapper">
        <button class="browse-mobile-btn"><i class="fas fa-bars"></i><span>Filters</span></button>
    </div>
    <form action="<?php echo Route::url('index.php?option=' . $this->option . '&task=browse'); ?>" method="post" id="filter-form" enctype="multipart/form-data">
        <div class="resource-container">
            <div class="filter-container">
                <div class="active-filters-wrapper">
                    <h6>Applied Filters</h6>
                    <ul class="active-filters"></ul>
                </div>
                <div class="text-search-options">
                    <fieldset>
                        <input type="hidden" name="action" value="search" />
                        <input type="hidden" name="no_html" value="1" />
                        <input type="hidden" name="sort" value="<?php echo $this->sortby; ?>" />
                    </fieldset>
                    <fieldset>
                        <h5>Text Search:</h5>
                        <div class="search-text-wrapper">
                            <?php echo \Hubzero\Html\Builder\Input::text("search", $this->search); ?>
                            <input type="submit" class="btn" id="search-btn" value="Apply">
                        </div>
                    </fieldset>
                    <input type="submit" class="btn" id="reset-btn" value="Reset All Filters">
                </div>
                    
                    <div id="accordion">
                        <h5><i class="fas fa-chevron-down"></i>Courses</h5>
                        <div class="filter-panel">
                            <ul>
                                <?php foreach($courses as $course) { ?>
                                    <?php $id = $course->tag()->rows()->tag; ?>
                                    <li><input type="checkbox" name="tags[]" id="<?php echo $id; ?>" value="<?php echo $id; ?>" <?php if (in_array($id, $this->tags)) { echo 'checked'; } ?>><label for="<?php echo $id; ?>"><?php echo $course->name; ?></label></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php foreach($taxonomies as $name => $facet) { ?>
                            <h5><i class="fas fa-chevron-down"></i><?php echo $name; ?></h5>
                            <div class="filter-panel">
                                <ul>
                                    <?php foreach($facet["children"] as $name => $filter) { ?>
                                        <?php $id = $filter["model"]->tag()->rows()->tag; ?>
                                        <li><input type="checkbox" name="tags[]" id="<?php echo $id; ?>" value="<?php echo $id; ?>" <?php if (in_array($id, $this->tags)) { echo 'checked'; } ?>><label for="<?php echo $id; ?>"><?php echo $name; ?></label></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>
                    </div>
            </div>
            <div class="container">
                <div class="container" id="sortby">
                    <nav class="entries-filters">
                        <ul class="entries-menu order-options">
                            <li><a <?php echo ($this->sortby == 'downloads') ? 'class="active"' : ''; ?> data-value="downloads" title="Downloads">Downloads</a></li>
                            <li><a <?php echo ($this->sortby == 'views') ? 'class="active"' : ''; ?> data-value="views" title="Views">Views</a></li>
                            <li><a <?php echo ($this->sortby == 'date') ? 'class="active"' : ''; ?> data-value="date" title="Date">Date</a></li>
                            <li><a <?php echo ($relevance_classes) ? 'class="' . $relevance_classes . '"' : ''; ?> data-value="relevance" title="Relevance">Relevance</a></li>
                        </ul>
                    </nav>
                </div>
                <?php
                // Calling cards view
                echo $this->view('cards')
                    ->set('group', $this->group)
                    ->set('results', $this->results)
                    ->set('authorized', $this->authorized)
                    ->set('pageNav', $this->pageNav)
                    ->loadTemplate();
                ?>
            </div>
        </div>
    </form>

    <?php if (!$no_html) { ?>
        </div> <!-- .live-update-wrapper -->
      </section>
    <?php } ?>
