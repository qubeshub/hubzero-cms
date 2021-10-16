<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css();

$logo = $this->group->getLogo();
?>
<div id="group-owner">
	<div class="group-content">
		<span class='group-sponsorship'>Brought to you by</span>
	<?php if ($logo) { ?>
		<p class='group-description'><a href="<?php echo Route::url('index.php?option=com_groups&cn=' . $this->group->get('cn')) ?>"><?php echo $this->escape(stripslashes($this->group->get('description'))) ?></a></p>
		
		<p class="group-img">
			<a href="<?php echo Route::url('index.php?option=com_groups&cn=' . $this->group->get('cn')); ?>">
				<img src="<?php echo $logo; ?>" width="50" alt="<?php echo Lang::txt('PLG_PUBLICATIONS_GROUPS_IMAGE', $this->escape(stripslashes($this->group->get('description')))); ?>" />
			</a>
		</p>
	<?php } else { ?>
		<p class='group-description'><a href="<?php echo Route::url('index.php?option=com_groups&cn=' . $this->group->get('cn')) ?>"><?php echo $this->escape(stripslashes($this->group->get('description'))) ?></a></p>
	<?php } ?>
	</div>
</div>