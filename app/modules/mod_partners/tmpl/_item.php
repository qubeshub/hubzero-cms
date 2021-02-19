<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();
?>
<div class="logo tooltips" title="<?php echo $partner->get('name'); ?>">
	<a href="<?php echo ($partner->get('groups_cn') ? Route::url('groups' . DS . $partner->get('groups_cn')) : $partner->get('site_url')); ?>"><img src="<?php echo 'app/site/media/images/partners/' . $partner->get('logo_img') ?>" alt="<?php echo $partner->get('name'); ?>"></a>
</div>
