<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();
?>

<div class="card [ is-collapsed ]">
    <div class="card__inner [ js-expander ]">
    <i class="fa fa-info-circle"></i>
		<img src="<?php echo 'app/site/media/images/partners/' . $this->record->get('logo_img') ?>" alt="<?php echo $this->record->get('name'); ?>" class="card-logo" title="<?php echo $this->record->get('name'); ?>">
    </div>
	<div class="card__expander">
	   <i class="fa fa-close [ js-collapser ]" aria-hidden="true"></i>
	   <div class="inner-expander">
	       <?php echo 'About: ' . '<p>' . $this->record->get('about') . '</p>'; ?>
		   <div class="social">
			   <?php if($this->record->get('groups_cn') || $this->record->get('site_url')) { ?>
					<a href="<?php echo ($this->record->get('groups_cn') ? Route::url('groups' . DS . $this->record->get('groups_cn')) : $this->record->get('site_url')); ?>"><span class="social-icon"><?php echo file_get_contents(PATH_CORE . DS . "assets/icons/globe.svg") ?></span>website</a>
				<?php } ?>
				<?php if($this->record->get('twitter_handle')){ ?>
					<a href="https://twitter.com/<?php echo $this->record->get('twitter_handle'); ?>"><span class="social-icon"><?php echo file_get_contents(PATH_CORE . DS . "assets/icons/twitter.svg") ?></span>twitter</a>
				<?php } ?>
            </div>
	   </div>
    </div>
</div>
