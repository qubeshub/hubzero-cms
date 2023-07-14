<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$url = 'index.php?option=' . $this->option . '&' . ($this->publication->alias ? 'alias=' . $this->publication->alias : 'id=' . $this->publication->id) . '&active=usage';

$totalDownloads = $this->totalDownloads;
$totalViews = $this->totalViews;
$results = $this->results;


$this->css();
$this->js('usage.js');

$heights = array();
?>

<h3 id="plg-usage-header">
	<?php echo Lang::txt('PLG_PUBLICATIONS_USAGE'); ?>
</h3>

<form method="get" action="<?php echo Route::url($url); ?>" id="usage-form" version="<?php echo $this->publication->version->version_number; ?>">
<?php if (count($results)) { ?>
	<div class="usage-wrap">
		<div class="grid charts">
			<div class="col span3 usage-stat">
				<h4><?php echo Lang::txt('PLG_PUBLICATIONS_USAGE_VIEWS'); ?></h4>
				<p class="total">
					<strong class="usage-value" id="publication-views"><?php echo number_format($totalViews); ?></strong>
				</p>
			</div>
			<div class="col span9 omega usage-stat">
				<div class="chart-wrap">
					<div id="chart-views" class="chart line">
						<!-- <?php
						// This doesn't look like it does anything
						// $sparkline  = '<span class="sparkline">' . "\n";
						// foreach ($results as $result)
						// {
						// 	$height = ($viewshighest) ? round(($result->page_views / $viewshighest)*100) : 0;

						// 	$nm = 'count' . $height;
						// 	if (!in_array($nm, $heights))
						// 	{
						// 		$this->css('
						// 			.sparkline .' . $nm . ' {
						// 				height: ' . $height . '%;
						// 			}
						// 		');
						// 		$heights[] = $nm;
						// 	}

						// 	$sparkline .= "\t" . '<span class="index">';
						// 	$sparkline .= '<span class="count count' . $height . '" title="20' . $result->year . '-' . \Hubzero\Utility\Str::pad($result->month, 2) . ': ' . $result->page_views . '">';
						// 	$sparkline .= number_format($result->page_views);
						// 	$sparkline .= '</span> ';
						// 	$sparkline .= '</span>' . "\n";
						// }
						// $sparkline .= '</span>' . "\n";
						// echo $sparkline;
						?> -->
					</div>
				</div>
				<p><?php echo Lang::txt('PLG_PUBLICATIONS_USAGE_VIEWS_EXPLANATION'); ?></p>
			</div>
		</div>
	</div>

	<div class="usage-wrap">
		<div class="grid charts">
			<div class="col span3 usage-stat">
				<h4><?php echo Lang::txt('PLG_PUBLICATIONS_USAGE_DOWNLOADS'); ?></h4>
				<p class="total">
					<strong class="usage-value" id="publication-downloads"><?php echo number_format($totalDownloads); ?></strong>
				</p>
			</div>
			<div class="col span9 omega usage-stat">
				<div class="chart-wrap">
					<div id="chart-downloads" class="chart line">
						<!-- <?php
						// This doesn't look like it does anything
						// $sparkline  = '<span class="sparkline">' . "\n";
						// foreach ($results as $result)
						// {
						// 	$height = ($downhighest) ? round(($result->primary_accesses / $downhighest)*100) : 0;

						// 	$nm = 'count' . $height;
						// 	if (!in_array($nm, $heights))
						// 	{
						// 		$this->css('
						// 			.sparkline .' . $nm . ' {
						// 				height: ' . $height . '%;
						// 			}
						// 		');
						// 		$heights[] = $nm;
						// 	}

						// 	$sparkline .= "\t" . '<span class="index">';
						// 	$sparkline .= '<span class="count count' . $height . '" title="20' . $result->year . '-' . \Hubzero\Utility\Str::pad($result->month, 2) . ': ' . $result->primary_accesses . '">';
						// 	$sparkline .= number_format($result->primary_accesses);
						// 	$sparkline .= '</span> ';
						// 	$sparkline .= '</span>' . "\n";
						// }
						// $sparkline .= '</span>' . "\n";
						// echo $sparkline;
						?> -->
					</div>
				</div>
				<p><?php echo Lang::txt('PLG_PUBLICATIONS_USAGE_DOWNLOADS_EXPLANATION'); ?></p>
			</div>
		</div>
	</div>

<?php } else { ?>
	<div id="no-usage">
		<p class="warning"><?php echo Lang::txt('PLG_PUBLICATIONS_USAGE_NO_DATA_AVAILABLE'); ?></p>
	</div>
<?php } ?>
</form>
