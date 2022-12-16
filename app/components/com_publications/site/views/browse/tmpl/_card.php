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
		<h5><a href="<?php echo Route::url($this->row->link('version')); ?>"><?php echo $this->row->title; ?></a></h5>

		<p class="pub-authors" style="display:none;"><span class="author">Author McAuthorson</span><span class="author">Author McAuthorson</span><span class="author">Author McAuthorson</span></p>
		<p class="pub-version" style="display:none;">Version: <?php echo $this->row->get('version_label'); ?></p>
		<p class="pub-fork" style="display:none;">Forked from: This other place</p>

		<?php echo \Hubzero\Utility\Str::truncate(strip_tags($this->row->abstract), 200); ?>

		<div class="pub-keywords">
			<p><span class="hz-icon icon-tags"></span> Keywords: <span class="keyword">hoomin</span><span class="keyword">hoomin</span><span class="keyword">hoomin</span>  </p>
		</div>
	</div>

	<div class="meta-panel">
		<div class="meta-item">
			<span class="count"><?php echo (int) $this->row->views; ?></span>
			<p>views</p>
		</div>

		<div class="divider"></div>

		<div class="meta-item">
			<span class="count"><?php echo (int) $this->row->downloads; ?></span>
			<p>downloads</p>
		</div>

		<div class="divider"></div>

		<div class="meta-item">
			<span class="count"><?php echo (int) $this->row->comments; ?></span>
			<p>comments</p>
		</div>

		<div class="divider"></div>
		<div class="meta-item">
			<span class="count"><?php echo (int) $this->row->forks; ?></span>
			<p>adaptations</p>
		</div>
	</div>
</div>
