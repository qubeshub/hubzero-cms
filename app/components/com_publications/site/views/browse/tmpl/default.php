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
     ->css('browse')
     ->js('browse');

$relevance_classes = array();
if ($this->sortBy == 'relevance') { $relevance_classes[] = 'active'; }
if (!$this->search) { $relevance_classes[] = 'disabled'; }
$relevance_classes = implode($relevance_classes, ' ');
?>

<?php include_once Component::path('com_publications') . DS . 'site' . DS . 'views' . DS . 'publications' . DS . 'tmpl' . DS . 'intro.php';  ?>

<section class="section live-update">
    <div aria-live="polite" id="live-update-wrapper">
        <div class="browse-mobile-btn-wrapper">
            <button class="browse-mobile-btn"><span class="hz-icon icon-filter">Filter</span></button>
        </div>
        <form action="<?php echo Route::url('index.php?option=' . $this->option . '&task=browse'); ?>" method="post" id="filter-form" enctype="multipart/form-data">
            <div class="resource-container">
                <div class="filter-container">
                    <div class="text-search-options">
                        <fieldset>
                            <input type="hidden" name="action" value="browse" />
                            <input type="hidden" name="no_html" value="1" />
                            <input type="hidden" name="sort" value="<?php echo $this->sortBy; ?>" />
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
                        
                    <?php
                    // Calling filters view
                    echo $this->view('filters')
                        ->set('fas', $this->fas)
                        ->set('filters', $this->filters)
                        ->set('facets', $this->facets)
                        ->loadTemplate();
                    ?> 
                </div>
                <div class="container">
                    <div class="active-filters-wrapper">
                        <h6>Applied Filters</h6>
                        <ul class="active-filters"></ul>
                    </div>
                    <div class="container" id="sortby">
                        <nav class="entries-filters">
                            <ul class="entries-menu order-options">
                                <li><a <?php echo ($this->sortBy == 'publish_up') ? 'class="active"' : ''; ?> data-value="date" title="Date">Date</a></li>
                                <li><a <?php echo ($relevance_classes) ? 'class="' . $relevance_classes . '"' : ''; ?> data-value="relevance" title="Relevance">Relevance</a></li>
                            </ul>
                        </nav>
                    </div>
                        <?php
                        // Calling cards view
                        echo $this->view('cards')
                            ->set('results', $this->results)
                            ->set('pageNav', $this->pageNav)
                            ->loadTemplate();
                        ?>
                </div>
            </div>
        </form>
    </div> <!-- .live-update-wrapper -->
</section>

