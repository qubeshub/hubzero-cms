<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css()
     ->js();

if (Pathway::count() <= 0)
{
	Pathway::append(
		Lang::txt('COM_KB'),
		'index.php?option=' . $this->option
	);
}
Pathway::append(
	$this->category->get('title'),
	$this->category->link()
);

Document::setTitle(Lang::txt('COM_KB') . ': ' . $this->category->get('title'));
?>
<header id="content-header">
	<h2><?php echo Lang::txt('COM_KB'); ?></h2>

	<div id="content-header-extra">
		<p>
			<a class="icon-main main-page btn" href="<?php echo Route::url('index.php?option=' . $this->option); ?>"><?php echo Lang::txt('COM_KB_MAIN'); ?></a>
		</p>
	</div>
</header>

<section class="main section">
	<div class="section-inner hz-layout-with-aside">
		<?php if ($this->getError()) { ?>
			<p class="error"><?php echo $this->getError(); ?></p>
		<?php } ?>
		<div class="subject">
			<form action="<?php echo Route::url('index.php?option=' . $this->option . '&section=all'); ?>" method="get">

				<div class="container data-entry">
					<input class="entry-search-submit" type="submit" value="<?php echo Lang::txt('COM_KB_SEARCH'); ?>" />
					<fieldset class="entry-search">
						<legend><?php echo Lang::txt('COM_KB_SEARCH_LEGEND'); ?></legend>
						<label for="entry-search-field"><?php echo Lang::txt('COM_KB_SEARCH_LABEL'); ?></label>
						<input type="text" name="search" id="entry-search-field" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo Lang::txt('COM_KB_SEARCH_PLACEHOLDER'); ?>" />
					</fieldset>
				</div><!-- / .container -->

				<div class="container">
					<nav class="entries-filters" aria-label="<?php echo Lang::txt('JGLOBAL_FILTER_AND_SORT_RESULTS'); ?>">
						<ul class="entries-menu">
							<li>
								<a<?php echo ($this->filters['sort'] == 'popularity') ? ' class="active"' : ''; ?> href="<?php echo Route::url($this->category->link() . '&sort=popularity'); ?>" title="<?php echo Lang::txt('COM_KB_SORT_BY_POPULAR'); ?>">
									<?php echo Lang::txt('COM_KB_SORT_POPULAR'); ?>
								</a>
							</li>
							<li>
								<a<?php echo ($this->filters['sort'] == 'recent') ? ' class="active"' : ''; ?> href="<?php echo Route::url($this->category->link() . '&sort=recent'); ?>" title="<?php echo Lang::txt('COM_KB_SORT_BY_RECENT'); ?>">
									<?php echo Lang::txt('COM_KB_SORT_RECENT'); ?>
								</a>
							</li>
						</ul>
					</nav>

					<table class="articles entries">
						<tbody>
						<?php
						$filters = array('state' => 1, 'access' => User::getAuthorisedViewLevels());

						$categories = $this->archive->categories($filters);

						if (!$this->category->get('id'))
						{
							$articles = $this->archive->articles();
						}
						else
						{
							$articles = $this->category->articles();
						}

						$articles->whereEquals('state', 1)
								->whereIn('access', User::getAuthorisedViewLevels());

						if (isset($this->filters['search']) && $this->filters['search'])
						{
							$articles->whereLike('title', $this->filters['search'], 1)
								->orWhereLike('fulltxt', $this->filters['search'], 1)
								->resetDepth();
						}
						if ($this->filters['sort'] == 'popularity')
						{
							$articles->order('helpful', 'desc');
						}
						else
						{
							$articles->order('modified', 'desc')
									->order('created', 'desc');
						}

						$articles = $articles->paginated();

						foreach ($articles as $row)
						{
							if (!$this->category->get('id'))
							{
								foreach ($categories as $cat)
								{
									if ($cat->get('id') == $row->get('category'))
									{
										$row->set('ctitle', $cat->get('title'));
										$row->set('calias', $cat->get('path'));
										break;
									}
								}
							}
							else
							{
								$row->set('calias', $this->category->get('path'));
								$row->set('ctitle', $this->category->get('title'));
							}
							?>
							<tr>
								<th>
									<span class="entry-identifier icon-file"><?php echo $row->get('id'); ?></span>
								</th>
								<td>
									<a class="entry-title" href="<?php echo Route::url($row->link()); ?>"><?php echo $this->escape(stripslashes($row->get('title',''))); ?></a><br />
									<span class="entry-details">
										<?php if ($this->catid <= 0) { echo Lang::txt('COM_KB_IN_CATEGORY', $this->escape(stripslashes($row->get('ctitle','')  ?? ''))); } ?>
										<?php echo Lang::txt('COM_KB_LAST_MODIFIED'); ?>
										<span class="entry-time-at"><?php echo Lang::txt('COM_KB_DATETIME_AT'); ?></span>
										<span class="entry-time"><?php echo $row->modified('time'); ?></span>
										<span class="entry-date-on"><?php echo Lang::txt('COM_KB_DATETIME_ON'); ?></span>
										<span class="entry-date"><?php echo $row->modified('date'); ?></span>
									</span>
								</td>
								<td class="voting">
									<?php
									$view = $this->view('_vote')
											 ->set('option', $this->option)
											 ->set('item', $row)
											 ->set('type', 'entry')
											 ->set('vote', '')
											 ->set('id', '');
									if (!User::isGuest())
									{
										if ($row->get('user_id') == User::get('id'))
										{
											$view->set('vote', $row->get('vote'));
											$view->set('id', $row->get('id'));
										}
									}
									$view->display();
									?>
								</td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
					<?php
					echo $articles
							->pagination
							->setAdditionalUrlParam('search', $this->filters['search'])
							->setAdditionalUrlParam('sort', $this->filters['sort']);
					?>
					<div class="clearfix"></div>
				</div><!-- / .container -->
			</form>
		</div><!-- / .subject -->
		<aside class="aside">
			<div class="container">
				<h3><?php echo Lang::txt('COM_KB_CATEGORIES'); ?></h3>
				<ul class="categories">
					<li>
						<a<?php if ($this->catid <= 0) { echo ' class="active"'; } ?> href="<?php echo Route::url('index.php?option=' . $this->option . '&section=all'); ?>">
							<?php echo Lang::txt('COM_KB_ALL_ARTICLES'); ?>
						</a>
					</li>
					<?php foreach ($categories as $row) { ?>
						<?php
						if ($row->get('articles', 0) <= 0)
						{
							continue;
						}
						?>
						<li>
							<a <?php if ($this->catid == $row->get('id')) { echo 'class="active" '; } ?> href="<?php echo Route::url($row->link()); ?>">
								<?php echo $this->escape(stripslashes($row->get('title'))); ?> <span class="item-count"><?php echo $row->get('articles', 0); ?></span>
							</a>
							<?php if ($this->catid == $row->get('id') && $row->children($filters)->total() > 0) { ?>
								<ul class="categories">
								<?php foreach ($row->children() as $cat) { ?>
									<li>
										<a <?php if ($this->category->get('id') == $cat->get('id')) { echo 'class="active" '; } ?> href="<?php echo Route::url($cat->link()); ?>">
											<?php echo $this->escape(stripslashes($cat->get('title'))); ?> <span class="item-count"><?php echo $cat->get('articles', 0); ?></span>
										</a>
									</li>
								<?php } ?>
								</ul>
							<?php } ?>
						</li>
					<?php } ?>
				</ul>
			</div><!-- / .container -->
		</aside><!-- / .aside -->
	</div><!-- / .section-inner -->
</section><!-- / .main section -->
