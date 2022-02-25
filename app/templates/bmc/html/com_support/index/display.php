<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

require_once \Component::path('com_kb') . DS . 'models' . DS . 'archive.php';

$archive = new \Components\Kb\Models\Archive;

$this->css('introduction.css', 'system')
	->css()
    ->js();
?>

<header id="content-header">
	<h2><?php echo $this->title; ?></h2>
    <p>Find answers to frequently asked questions, helpful tips, and any other information we thought might be useful. Can't find the answers you are looking for? <a class="ticket-report" href="<?php echo Route::url('index.php?option=com_support&task=new'); ?>">Submit a ticket</a> and we will get back to you as soon as we can. Already have a ticket? You can <a class="ticket-track" href="<?php echo Route::url('index.php?option=com_support&task=tickets'); ?>">track your ticket</a> at any time.</p>
</header>

<section class="main section support-wrapper">
    <div class="support-wrapper">
        <section class="kb-section">
            <h3>Search Our Knowledge Base Articles</h3>
            <form action="<?php echo Route::url('index.php?option=com_kb&section=all'); ?>" method="get">
                <div class="container data-entry">
					<input class="entry-search-submit" type="submit" value="Search" />
					<fieldset class="entry-search">
						<legend><?php echo Lang::txt('COM_KB_SEARCH_LEGEND'); ?></legend>
						<label for="entry-search-field"><?php echo Lang::txt('COM_KB_SEARCH_LABEL'); ?></label>
						<input type="text" name="search" id="entry-search-field" value="" placeholder="Enter keyword or phrase" />
					</fieldset>
				</div><!-- / .container -->
            </form>
            <h3>Or Browse By Category</h3>
            <div class="kb-container">
                <?php
                    $i = 0;

                    $categories = $archive->categories(array('state' => 1, 'access' => User::getAuthorisedViewLevels()));

                    foreach ($categories as $row)
                    {
                        $articles = $row->articles()
                            ->whereEquals('state', 1)
                            ->whereIn('access', User::getAuthorisedViewLevels())
                            ->order('modified', 'desc')
                            ->order('created', 'desc')
                            ->rows();

                        if ($articles->count() <= 0)
                        {
                            continue;
                        }

                        $i++;
                        switch ($i)
                        {
                            case 1:
                                $cls = '';
                                break;
                            case 2:
                                $cls = ' omega';
                                break;
                        }
                        ?>
                       
                            <h4>
                                <?php echo $this->escape(stripslashes($row->get('title'))); ?> 
                                <span>(<?php echo $row->get('articles', 0); ?>)</span>
                                <span class="icon-chevron-down hz-icon"></span>
                            </h4>
                            <div>
                                <?php if ($articles->count() > 0) { ?>
                                <ul class="articles">
                                    <?php foreach ($articles as $article) {
                                        $article->set('calias', $row->get('path'));
                                        ?>
                                        <li>
                                            <a href="<?php echo Route::url($article->link()); ?>">
                                                <?php echo $this->escape(stripslashes($article->get('title'))); ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                                <?php } else { ?>
                                    <p><?php echo Lang::txt('COM_KB_NO_ARTICLES'); ?></p>
                                <?php } ?>
                            </div>
                        <?php
                        //echo ($i >= 2) ? '<div class="clearfix"></div>' : '';

                        if ($i >= 2)
                        {
                            $i = 0;
                        }
                    }
                ?>
            </div>
        </section>

        <section class="ticket-section">
            <h3><a class="ticket-report" href="<?php echo Route::url('index.php?option=com_support&task=new'); ?>">Report Problems</a></h3>
            <p><a class="ticket-report" href="<?php echo Route::url('index.php?option=com_support&task=new'); ?>">Report problems</a> with our form and have your problem entered into our <a class="ticket-track" href="<?php echo Route::url('index.php?option=com_support&task=tickets'); ?>">ticket tracking system</a>. We guarantee a response!</p>

            <h3><a class="ticket-track" href="<?php echo Route::url('index.php?option=com_support&task=tickets'); ?>">Track Tickets</a></h3>
            <p>Have a problem entered into our <a class="ticket-track" href="<?php echo Route::url('index.php?option=com_support&task=tickets'); ?>">ticket tracking system</a>? Track its progress, add comments and notes, or close resolved issues.</p>
        </section>
    </div>
</section>