<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css();
?>
<header id="content-header">
	<h2><?php echo $this->title; ?></h2>

	<div id="content-header-extra">
		<p>
			<a class="icon-main main-page btn" href="<?php echo Route::url('index.php?option=' . $this->option); ?>">
				<?php echo Lang::txt('COM_FEEDBACK_MAIN'); ?>
			</a>
		</p>
	</div><!-- / #content-header-extra -->
</header><!-- / #content-header -->

<section class="main section">
	<div class="section-inner">
		<h3><?php echo Lang::txt('COM_FEEDBACK_HAVE_AN_OPINION'); ?> <span><?php echo Lang::txt('COM_FEEDBACK_CAST_A_VOTE'); ?></span></h3>

		<?php if (Module::isEnabled('mod_poll')) { ?>
			<div class="introtext">
				<?php echo Module::render(Module::byName('mod_poll')); ?>
			</div>
		<?php } else { ?>
			<p class="warning"><?php echo Lang::txt('COM_FEEDBACK_NO_ACTIVE_POLLS'); ?></p>
		<?php } ?>
	</div>
</section><!-- / .main section -->
