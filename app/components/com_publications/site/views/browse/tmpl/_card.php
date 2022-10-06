<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$database = App::get('db');
// Get the component params and merge with resource params
$config = Component::params('com_publications');
$rparams = new \Hubzero\Config\Registry($this->row->params);
$params = $config;
$params->merge($rparams);
?>

<div class="resource coursesource-card">
	<div class="coursesource-card-img">
		<img src="<?php echo Route::url($this->row->link('masterimage')); ?>">
	</div>

	<div class="coursesource-card-contents">
		<h5><a href="<?php echo '#'; ?>"><?php echo $this->row->title; ?></a></h5>

		<?php echo \Hubzero\Utility\Str::truncate(strip_tags($this->row->abstract), 250); ?>
	</div>
</div>
