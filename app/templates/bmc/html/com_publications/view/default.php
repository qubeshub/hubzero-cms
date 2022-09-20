<?php

/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

// HTML Helper class
include_once PATH_APP . DS . 'templates' . DS . 'bmc' . DS . 'html' . DS . 'com_publications' . DS . 'helpers' . DS . 'html.php';

$this->css()
	->css('jquery.fancybox.css', 'system')
	->js();

// Load select publication information
$this->publication->authors();
$this->publication->attachments();
$this->publication->license();

// Get sub-sections from plugins
$subSections = Event::trigger('publications.onPublicationSub', array($this->publication, $this->option, 1));

// Set tab overrides
$tabOverrides = array(
	"about" => "Description",
	"supportingdocs" => "File Contents",
	"versions" => "Version History",
	"usage" => "Views/Downloads"
)
?>

<div class="wrapper">
	<div class="title-wrapper-holder"></div>
	<div class="title-wrapper">
		<img alt="Resource Image" src="<?php echo Route::url($this->publication->link('masterimage')) ?>" />
		<div class="title">
			<?php echo \Templates\Bmc\Publications\Helpers\Html::title($this->publication); ?>

			<?php if ($this->publication->params->get('show_authors') && $this->publication->_authors) { ?>
				<?php echo \Templates\Bmc\Publications\Helpers\Html::showContributors($this->publication->_authors, true, false, false, false, $this->publication->params->get('format_authors', 0)); ?>
			<?php }	?>
		</div>

		<div class="cta-wrapper">
			<div class="stats">
				<?php
				// Show usage statistics
				echo \Templates\Bmc\Publications\Helpers\Html::showSection($this->sections, "usage", "metadata");
				echo \Templates\Bmc\Publications\Helpers\Html::showSection($this->sections, "comments", "metadata");
				?>
			</div>

			<div class="cta-buttons">
				<?php
				// Show download and adaptation buttons
				echo \Templates\Bmc\Publications\Helpers\Html::primaryButton($this->publication, "icon-download");
				echo \Templates\Bmc\Publications\Helpers\Html::showSection($subSections, "forks", "html");
				if ($this->publication->params->get('instructor_only') && 
							    $this->publication->hasInstructorAttachments() &&
								!$this->publication->access('instructor')) {
									$request  = '<a class="btn instructor-access" href="' . Route::url('index.php?option=com_groups&cn=' . $this->publication->params->get('instructor_group')) . DS . 'join">';
									$request .= Lang::txt('COM_PUBLICATIONS_REQUEST_INSTRUCTOR_ACCESS');
									$request .= '</a>';
									echo $request;
								}
				?>
			</div>
		</div>
	</div>

	<div class="content-top">
		<div class="image-wrapper">
			<div class="share">
				<?php
				// Show share, collect, and watch
				echo \Templates\Bmc\Publications\Helpers\Html::showSection($this->sections, "collect", "metadata");
				echo \Templates\Bmc\Publications\Helpers\Html::showSection($subSections, "watch", "html");
				echo \Templates\Bmc\Publications\Helpers\Html::showSection($this->sections, "share", "metadata");
				?>
			</div>
			<?php
			// Show group owner (if exists)
			echo \Templates\Bmc\Publications\Helpers\Html::showSection($subSections, "groups", "html");
			?>
		</div>

		<div class="abstract-wrapper">
			Summary:
			<?php
			$short = null;
			if (strlen(strip_tags($this->publication->abstract)) > 250)
			{
				$short = Hubzero\Utility\Str::truncate($this->publication->abstract, 250, array('html' => true));
				?>
				<div class="abstract-preview">
					<?php echo $short; ?>
					<p>
						<a class="more-content" href="#abstract-full">
							more
						</a>
					</p>
				</div>
				<?php
			}
			?>
			<div class="abstract-full<?php echo ($short) ? ' hide' : ''; ?>" id="abstract-full">
				<?php echo $this->publication->abstract; ?>
			</div>

			<div class="meta-top">
				<?php
				// Show license information
				if ($this->publication->license() && $this->publication->license()->name != 'standard') {
					echo \Templates\Bmc\Publications\Helpers\Html::showLicense($this->publication, 'play');
				}

				// Show version information
				echo \Templates\Bmc\Publications\Helpers\Html::showVersionInfo($this->publication);

				// Show fork attribution
				echo \Templates\Bmc\Publications\Helpers\Html::showForkAttribution($this->publication);
				?>

				<div class="tags">
					<?php
					// Show keywords
					echo \Templates\Bmc\Publications\Helpers\Html::showKeywords($this->publication);
					
					// Show alignments
					echo \Templates\Bmc\Publications\Helpers\Html::showSection($this->sections, "alignments", "metadata");
					?>
				</div>

				<?php 
				// Show status for authorized users
				if ($this->contributable)
				{
					echo \Templates\Bmc\Publications\Helpers\Html::showAccessMessage($this->publication);
				}
				?>
			</div>
		</div>

		<div class="files-wrapper">
			<div class="files">
				Contents:
				<div class="file">
					<?php
						echo \Templates\Bmc\Publications\Helpers\Html::drawAllItems($this->publication);
					
					?>
					<div class="more-files hide">
						<?php
						echo '<a href="' . Route::url('index.php?option=com_publications&id=' . $this->publication->get('id') . '&v=' . $this->publication->version->get('version_number') . '&active=supportingdocs#supportingdocs') . '">view all files</a>';
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="content-bottom">
		<div class="content-menu">
			<nav>
				<?php
				echo \Templates\Bmc\Publications\Helpers\Html::tabs(
					$this->base_url,
					$this->cats,
					$this->active_key,
					$this->tab,
					$tabOverrides
				);
				?>
			</nav>
		</div>

		<div class="content-wrapper">
			<div class="content-display">
				<?php
				echo \Templates\Bmc\Publications\Helpers\Html::sections($this->sections, $this->cats, $this->tab, 'hide', 'main');
				?>
			</div>
		</div>
	</div>
</div>
