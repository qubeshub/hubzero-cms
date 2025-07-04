<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

$this->css()
     ->js();

$txt = '';
$mode = strtolower(Request::getWord('mode', ''));

if ($mode != 'preview')
{
	switch ($this->model->published)
	{
		case 1:
			$txt .= '';
			break; // published
		case 2:
			$txt .= '<span>[' . Lang::txt('COM_RESOURCES_DRAFT_EXTERNAL') . ']</span> ';
			break;  // external draft
		case 3:
			$txt .= '<span>[' . Lang::txt('COM_RESOURCES_PENDING') . ']</span> ';
			break;  // pending
		case 4:
			$txt .= '<span>[' . Lang::txt('COM_RESOURCES_DELETED') . ']</span> ';
			break;  // deleted
		case 5:
			$txt .= '<span>[' . Lang::txt('COM_RESOURCES_DRAFT_INTERNAL') . ']</span> ';
			break;  // internal draft
		case 0:
			$txt .= '<span>[' . Lang::txt('COM_RESOURCES_UNPUBLISHED') . ']</span> ';
			break;  // unpublished
	}
}

$tconfig  = $this->tconfig;
$thistool = $this->thistool;
$curtool  = $this->curtool;
$revision = $this->revision;
?>
<section class="main section upperpane <?php echo $this->model->params->get('pageclass_sfx', ''); ?>">
	<div class="section-inner hz-layout-with-aside">
		<div class="subject">
			<div class="grid overviewcontainer">
				<div class="col span8">
					<header id="content-header">
						<h2>
							<?php echo $txt . $this->escape(stripslashes($this->model->title)); ?>
							<?php if ($this->model->params->get('access-edit-resource')) { ?>
								<a class="icon-edit edit btn" href="<?php echo Route::url('index.php?option=com_tools&task=resource&step=1&app=' . $this->model->alias); ?>"><?php echo Lang::txt('COM_RESOURCES_EDIT'); ?></a>
							<?php } ?>
						</h2>
						<input type="hidden" name="rid" id="rid" value="<?php echo $this->model->id; ?>" />
					</header>

					<?php if ($this->model->params->get('show_authors', 1)) { ?>
						<div id="authorslist">
							<?php
							$this->view('_contributors')
								->set('option', $this->option)
								->set('contributors', $this->model->contributors('tool'))
								->display();
							?>
						</div>
					<?php } ?>

					<p class="ataglance">
						<?php
						if (!$this->model->introtext)
						{
							$this->model->introtext = $this->model->fulltxt;
						}
						echo \Hubzero\Utility\Str::truncate(stripslashes($this->model->introtext), 255);
						?>
					</p>
				</div><!-- / .overviewcontainer -->

				<div class="col span4 omega launcharea">
					<?php
					// Private/Public resource access check
					if (!$this->model->access('view-all'))
					{
						$ghtml = array();
						foreach ($this->model->groups as $allowedgroup)
						{
							$ghtml[] = '<a href="' . Route::url('index.php?option=com_groups&cn=' . $allowedgroup) . '">' . $allowedgroup . '</a>';
						}
						?>
							<p class="warning">
								<?php if (User::isGuest()): ?>
									<?php echo Lang::txt('COM_RESOURCES_ERROR_MUST_BE_LOGGED_IN', base64_encode(Request::path())); ?>
								<?php elseif ($this->get('group_owner')): ?>
									<?php echo Lang::txt('COM_RESOURCES_ERROR_MUST_BE_PART_OF_GROUP') . ' ' . implode(', ', $ghtml); ?>
								<?php else: ?>
									<?php echo Lang::txt('COM_RESOURCES_ALERTNOTAUTH'); ?>
								<?php endif; ?>
							</p>
						<?php
					}
					else
					{
						// get launch button
						$children = $this->model->children()
							->whereEquals('published', \Components\Resources\Models\Entry::STATE_PUBLISHED)
							->whereEquals('standalone', 0)
							->order('ordering', 'asc')
							->rows();

						$firstChild = $children->first();

						echo \Components\Resources\Helpers\Html::primary_child($this->option, $this->model, $firstChild, '');

						$html = '';

						// get launch button
						$versiontext = '<strong>';
						if ($revision && $thistool)
						{
							$versiontext .= $thistool->version.'</strong>';
							if ($this->model->revision!='dev')
							{
								$versiontext .=  '<br /> '.ucfirst(Lang::txt('COM_RESOURCES_PUBLISHED_ON')).' ';
								$versiontext .= ($thistool->released && $thistool->released != '0000-00-00 00:00:00') ? Date::of($thistool->released)->toLocal(Lang::txt('DATE_FORMAT_HZ1')): Date::of($this->model->publish_up)->toLocal(Lang::txt('DATE_FORMAT_HZ1'));
								$versiontext .= ($thistool->unpublished && $thistool->unpublished != '0000-00-00 00:00:00') ? ', '.Lang::txt('COM_RESOURCES_UNPUBLISHED_ON').' '.Date::of($thistool->unpublished)->toLocal(Lang::txt('DATE_FORMAT_HZ1')): '';
							}
							else
							{
								$versiontext .= ' ('.Lang::txt('COM_RESOURCES_IN_DEVELOPMENT').')';
							}
						}
						else if ($curtool)
						{
							$versiontext .= $curtool->version.'</strong> - '.Lang::txt('COM_RESOURCES_PUBLISHED_ON').' ';
							$versiontext .= ($curtool->released && $curtool->released != '0000-00-00 00:00:00') ? Date::of($curtool->released)->toLocal(Lang::txt('DATE_FORMAT_HZ1')): Date::of($this->model->publish_up)->toLocal(Lang::txt('DATE_FORMAT_HZ1'));
						}

						if (!$thistool)
						{
							$html .= "\t\t\t\t".'<p class="curversion">'.Lang::txt('COM_RESOURCES_VERSION').' '.$versiontext.'</p>'."\n";
						}
						else if ($revision == 'dev')
						{
							$html .= "\t\t\t\t".'<p class="devversion">'.Lang::txt('COM_RESOURCES_VERSION').' '.$versiontext;
							$html .= $this->model->toolpublished ? ' <span>'.Lang::txt('View').' <a href="'.Route::url($this->model->link() . '&active=versions').'">'.Lang::txt('other versions').'</a></span>' : '';
							$html .='</p>'."\n";
						}
						else
						{
							// Show archive message
							$msg = '<strong>'.Lang::txt('COM_RESOURCES_ARCHIVE').'</strong> '.Lang::txt('COM_RESOURCES_VERSION').' '.$versiontext;
							if ($this->model->curversion)
							{
								$msg .= ' <br />'.Lang::txt('COM_RESOURCES_LATEST_VERSION').': <a href="'.Route::url($this->model->link().'&rev='.$curtool->revision).'">'.$this->model->curversion.'</a>.';
							}
							$msg .= ' <a href="'.Route::url($this->model->link() . '&active=versions').'">'.Lang::txt('COM_RESOURCES_TOOL_ALL_VERSIONS').'</a>';
							$html .= '<p class="archive">' . $msg . '</p>' . "\n";
						}

						// doi message
						if ($revision != 'dev' && $this->model->doi && ($this->model->doi_shoulder || $tconfig->get('doi_shoulder')))
						{
							$doi = 'doi:' . ($this->model->doi_shoulder ? $this->model->doi_shoulder : $tconfig->get('doi_shoulder')) . '/' . strtoupper($this->model->doi);

							$html .= "\t\t".'<p class="doi">'.$doi.' <span><a href="'.Route::url($this->model->link() . '&active=about').'#citethis">'.Lang::txt('cite this').'</a></span></p>'."\n";
						}

						// Open/closed source
						if ($this->model->toolsource && $this->model->tool)
						{ // open source
							$html .= '<p class="opensource_license">'.Lang::txt('Open source').': <a class="popup" href="' . Route::url('index.php?option='.$this->option.'&task=license&tool='.$this->model->tool.'&tmpl=component') . '">license</a> ';
							$html .= ($this->model->taravailable) ? ' |  <a href="' . Route::url('index.php?option='.$this->option.'&task=sourcecode&tool='.$this->model->tool).'">'.Lang::txt('download').'</a> '."\n" : ' | <span class="unavail">'.Lang::txt('code unavailable').'</span>'."\n";
							$html .= '</p>'."\n";
						}
						elseif (!$this->model->toolsource)
						{ // closed source, archive page
							$html .= '<p class="closedsource_license">'.Lang::txt('COM_RESOURCES_TOOL_IS_CLOSED_SOURCE').'</p>'."\n";
						}
						// do we have a first-time user guide?
						if (!$thistool)
						{
							$guide = 0;
							foreach ($children as $child)
							{
								$title = ($child->logicaltitle)
										? $child->logicaltitle
										: stripslashes($child->title);
								if ($child->access == 0 || ($child->access == 1 && !User::isGuest()))
								{
									if (strtolower($title) !=  preg_replace('/user guide/', '', strtolower($title)))
									{
										$guide = $child;
									}
								}
							}
							$url = $guide ? \Components\Resources\Helpers\Html::processPath($this->option, $guide, $this->model->id) : '';
							$html .= "\t\t".'<p class="supdocs">'."\n";
							if ($url)
							{
								$html .= "\t\t\t".'<span><span class="guide"><a href="'.$url.'">'.Lang::txt('COM_RESOURCES_TOOL_FIRT_TIME_USER_GUIDE').'</a></span></span>'."\n";
							}
							$html .= "\t\t\t".'<span class="viewalldocs"><a href="'.Route::url($this->model->link().'&active=supportingdocs').'">'.Lang::txt('COM_RESOURCES_TOOL_VIEW_ALL_SUPPORTING_DOCS').'</a></span>'."\n";
							$html .= "\t\t".'</p>'."\n";
						}

						echo $html;

					} // --- end else (if group check passed)
					?>
				</div><!-- / .aside launcharea -->
			</div>

			<?php
			// Display canonical
			$this->view('_canonical')
				->set('option', $this->option)
				->set('model', $this->model)
				->display();
			?>
		</div><!-- / .subject -->
		<aside class="aside rankarea">
		<?php
		// Show resource ratings
		if (!$thistool)
		{
			if ($this->model->params->get('show_metadata', 1))
			{
				$this->view('_metadata')
					->set('option', $this->option)
					->set('sections', $this->sections)
					->set('model', $this->model)
					->display();
			}
		}
		else if ($revision == 'dev' or !$this->model->toolpublished)
		{
			?>
			<div class="metaplaceholder">
				<p>
					<?php echo ($revision=='dev')
							? Lang::txt('This section will be filled when this tool version gets published.')
							: Lang::txt('This section is unavailable in an archive version of a tool.');

					if ($this->model->curversion)
					{
						echo ' '.Lang::txt('Consult the latest published version').' <a href="'.Route::url($this->model->link().'&rev='.$curtool->revision).'">'.$this->model->curversion.'</a> '.Lang::txt('for most current information.');
					}
					?>
				</p>
			</div>
			<?php
		}
		?>
	</aside><!-- / .aside -->
	</div>
</section>

<?php if ($this->model->access('view-all')) { ?>
	<section class="main section <?php echo $this->model->params->get('pageclass_sfx', ''); ?>">
		<div class="section-inner hz-layout-with-aside">
			<div class="subject tabbed">
				<?php
				$this->view('_tabs')
					->set('option', $this->option)
					->set('cats', $this->cats)
					->set('resource', $this->model)
					->set('active', $this->tab)
					->display();

				$this->view('_sections')
					->set('option', $this->option)
					->set('sections', $this->sections)
					->set('resource', $this->model)
					->set('active', $this->tab)
					->display();
				?>
			</div><!-- / .subject -->
			<aside class="aside extracontent">
			<?php
			// Show related content
			$out = Event::trigger('resources.onResourcesSub', array($this->model, $this->option, 1));
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
		</aside><!-- / .aside extracontent -->
		</div>
	</section><!-- / .main section -->
<?php }
