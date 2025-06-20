<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css()
	->css('jquery.fancybox.css', 'system')
	->js();

$this->publication->authors();
$this->publication->attachments();
$this->publication->license();

// New launcher layout?
if ($this->config->get('launcher_layout', 0))
{
	$this->view('launcher')
	     ->set('option', $this->option)
	     ->set('publication', $this->publication)
	     ->set('config', $this->config)
	     ->set('contributable', $this->contributable)
	     ->set('authorized', $this->authorized)
	     ->set('restricted', $this->restricted)
	     ->set('database', $this->database)
	     ->set('lastPubRelease', $this->lastPubRelease)
	     ->set('version', $this->version)
	     ->set('sections', $this->sections)
	     ->set('cats', $this->cats)
	     ->display();
}
else
{
	?>

	<?php echo \Components\Publications\Helpers\Html::title($this->publication); ?>
	<section class="main section upperpane">
		<div class="subject">
			<div class="grid overviewcontainer">
				<div class="col span8">
				<?php if ($this->publication->params->get('show_authors') && $this->publication->_authors) { ?>
					<div id="authorslist">
						<?php echo \Components\Publications\Helpers\Html::showContributors($this->publication->_authors, true, false, false, false, $this->publication->params->get('format_authors', 0)); ?>
					</div><!-- / #authorslist -->
				<?php } ?>

				<p class="ataglance">
					<?php
						$abstractSnippet = \Hubzero\Utility\Str::truncate(stripslashes(strip_tags($this->publication->abstract)), 250);
						echo $this->publication->abstract ?  $abstractSnippet : '';
					?>
				</p>

				<?php
				// Show published date and category
				echo \Components\Publications\Helpers\Html::showSubInfo($this->publication);
				?>
			</div><!-- / .overviewcontainer -->
			<div class="col span4 omega launcharea">
				<?php if ($this->publication->version->get('downloadDisabled')): ?>
						<p>
							<?php echo Lang::txt('COM_PUBLICATIONS_DOWNLOAD_DATASET_DISABLED'); echo Lang::txt('COM_PUBLICATIONS_PLEASE')?>
							<a href="/support/ticket/new" target="_blank"><?php echo Lang::txt('COM_PUBLICATIONS_SUBMIT_TICKET');?></a>
							<?php echo Lang::txt('COM_PUBLICATIONS_TO_INQUIRE_DATASET_STATUS'); ?>
						</p>
				<?php else: ?>
				<?php
				// Sort out primary files and draw a launch button
				if ($this->tab != 'play')
				{
					// Get primary elements
					$elements = $this->publication->_curationModel->getElements(1);

					// Get attachment type model
					$attModel = new \Components\Publications\Models\Attachments($this->database);

					if ($elements)
					{
						$element = $elements[0];

						// Draw button
						$launcher = $attModel->drawLauncher(
							$element->manifest->params->type,
							$this->publication,
							$element,
							$elements,
							$this->publication->access('view-all')
						);

						echo $launcher;
					}
				}

				if ($this->tab != 'play')
				{
					// Show additional docs
					echo \Components\Publications\Helpers\Html::drawSupportingItems($this->publication);

					// Show version information
					echo \Components\Publications\Helpers\Html::showVersionInfo($this->publication);

					// Show license information
					if ($this->publication->license() && $this->publication->license()->name != 'standard')
					{
						echo \Components\Publications\Helpers\Html::showLicense($this->publication, 'play');
					}
				}
				?>
				<?php endif; ?>
			</div><!-- / .aside launcharea -->
			</div>

			<?php
			// Show fork attribution
			if ($v = $this->publication->forked_from)
			{
				$db = App::get('db');
				$db->setQuery("SELECT publication_id FROM `#__publication_versions` WHERE `id`=" . $db->quote($v));

				$p = $db->loadResult();

				$ancestor = new Components\Publications\Models\Publication($p, 'default', $v);

				$from = '';
				if ($ancestor->version->get('state') == 1 &&
					(!$ancestor->version->get('published_up') || $ancestor->version->get('published_up') == '0000-00-00 00:00:00' || ($ancestor->version->get('published_up') != '0000-00-00 00:00:00' && $ancestor->version->get('published_up') <= Date::toSql())) &&
					(!$ancestor->version->get('published_down') || $ancestor->version->get('published_down') == '0000-00-00 00:00:00' || ($ancestor->version->get('published_down') != '0000-00-00 00:00:00' && $ancestor->version->get('published_down') > Date::toSql())))
				{
					$from = '<a href="' . Route::url('index.php?option=com_publications&id=' . $ancestor->get('id') . '&v=' . $ancestor->version->get('version_number')) . '">' . $this->escape($ancestor->version->get('title')) . '</a>';
				}
				else
				{
					$from = $this->escape($ancestor->version->get('title')) . ' <span class="publication-status">' . Lang::txt('(unpublished)') . '</span>';
				}
				$from .= ' <span class="publication-version"><abbr title="' . Lang::txt('Version') . '">v</abbr> ' . $this->escape($ancestor->version->get('version_label')) . '</span>';

				echo '<p class="icon-fork fork-source">' . Lang::txt('Forked from: %s', $from) . '</p>';
			}

			// Show status for authorized users
			if ($this->contributable)
			{
				echo \Components\Publications\Helpers\Html::showAccessMessage($this->publication);
			}
			?>
		</div><!-- / .subject -->

		<div class="aside rankarea">
				<?php
				// Show metadata
				$this->view('_metadata')
				     ->set('option', $this->option)
				     ->set('publication', $this->publication)
				     ->set('config', $this->config)
				     ->set('version', $this->version)
				     ->set('sections', $this->sections)
				     ->set('cats', $this->cats)
				     ->set('params', $this->publication->params)
				     ->set('lastPubRelease', $this->lastPubRelease)
				     ->display();
				?>
		</div><!-- / .aside -->
	</section><!-- / .main section -->
	<?php
}

// Part below
if ($this->publication->access('view-all'))
{
	?>
	<div class="clear sep"></div>
	<section class="main section noborder">
			<div class="subject tabbed">
				<?php
				echo \Components\Publications\Helpers\Html::tabs(
					$this->option,
					$this->publication->id,
					$this->cats,
					$this->tab,
					$this->publication->alias,
					$this->version
				);

				echo \Components\Publications\Helpers\Html::sections($this->sections, $this->cats, $this->tab, 'hide', 'main');

				// Add footer notice
				if ($this->tab == 'about')
				{
					echo \Components\Publications\Helpers\Html::footer($this->publication);
				}
				?>
			</div><!-- / .subject -->
			<div class="aside extracontent">
			<?php
			// Show related content
			$out = Event::trigger('publications.onPublicationSub', array($this->publication, $this->option, 1));
			if (count($out) > 0)
			{
				foreach ($out as $ou)
				{
					if (isset($ou['html']))
					{
						echo $ou['html'];
					}
				}
			}

			// Show what's popular
			if ($this->tab == 'about')
			{
				echo \Hubzero\Module\Helper::renderModules('extracontent');
			}
			?>
		</div><!-- / .aside extracontent -->
	</section><!-- / .main section -->
	<div class="clear"></div>
	<?php
}
