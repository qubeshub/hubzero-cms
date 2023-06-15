<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css();

$url_action = Route::url($this->publication->link('forks') . '&action=fork'); // For AJAX
$url = Route::url($this->publication->link('version') . (!Request::getString('base_url') ? '&active=forks' : '&tab_active=forks')) . '#forks-section';
if (User::isGuest())
{
	$url = Route::url('index.php?option=com_users&view=login&return=' . base64_encode($url));
}
else
{
	$this->js();
}
?>
<div class="btn-group item-fork">
	<a class="btn icon-fork" id="fork-this" href="<?php echo $url_action; ?>"><?php echo Lang::txt('PLG_PUBLICATIONS_FORKS_FORK'); ?></a>
	<a class="btn" href="<?php echo $url ?>" title="<?php echo Lang::txt('PLG_PUBLICATIONS_FORKS_FORKED_N_TIMES', $this->forks); ?>"><?php echo $this->forks; ?></a>
</div>
