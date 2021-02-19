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
		<img src="<?php echo 'app/site/media/images/partners/' . $this->record->get('logo_img') ?>" alt="<?php echo $this->record->get('name'); ?>" class="card-logo">
    </div>
	<div class="card__expander">
	   <i class="fa fa-close [ js-collapser ]" aria-hidden="true"></i>
	   <div class="inner-expander">
	       <?php echo 'About: ' . '<p>' . $this->record->get('about') . '</p>'; ?>
            <div class="liason">
            Partner Liaison:
			 <p><?php echo $this->record->get('partner_liason_primary'); ?></p>
            </div>
            <div class="liason">
			QUBES Liaison:
			 <p><?php echo $this->record->get('QUBES_liason_primary'); ?></p>
            </div>
	   </div>
	   <div class="inner-expander">
		<?php echo 'Activities: ' . '<p>' . $this->record->get('activities') . '</p>'; ?>
            <div class="social">
			 <a class="card-link" href="<?php echo ($this->record->get('groups_cn') ? Route::url('groups' . DS . $this->record->get('groups_cn')) : $this->record->get('site_url')); ?>">Learn more</a><br>
			 <a class="social-icon" href="https://twitter.com/<?php echo $this->record->get('twitter_handle'); ?>" target="_blank"><i class="fa fa-twitter" aria-hidden="true"></i></a>
            </div>
	   </div>
    </div>
</div>
