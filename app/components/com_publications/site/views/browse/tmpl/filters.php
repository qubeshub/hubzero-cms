<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$this->css()
     ->css('browse.css');

$no_html = Request::getInt('no_html', 0);
?>

<?php if (!$no_html) { ?>
<div id="accord">
<?php } ?>
<?php foreach ($this->fas as $fa) { 
    $fa_tag = $fa->tag->tag;
    $facets = $this->facets->getFacet($fa_tag)->getValues();
    $filters = (array_key_exists($fa_tag, $this->filters) && $this->filters[$fa_tag] ? array_merge(...array_values($this->filters[$fa_tag])) : array());
    $disable = ($facets[$fa_tag] == 0); ?>
    <div class="accordion-section" for="tagfa-<?php echo $fa_tag ?>" <?php echo ($disable ? "style='display:none;'" : "") ?>>
        <h5>
            <i class="fas fa-chevron-down"></i><?php echo $fa->label; ?>
            <span class="facet-count" for="<?php echo $fa_tag ?>">(<?php echo $facets[$fa_tag]; ?>)</span>
        </h5>
        <div class="filter-panel">
            <?php echo $fa->render('filter', array('root' => $fa_tag, 'filters' => $filters, 'facets' => $facets)); ?>
        </div>
    </div>
<?php } ?>
<?php if (!$no_html) { ?>
</div>
<?php } ?>
