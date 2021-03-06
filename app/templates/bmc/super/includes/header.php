<?php
/**
 * Header
 *
 * Template used for Special Groups. Will now be auto-created
 * when admin switches group from type HUB to type Special.
 *
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2019 HUBzero Foundation, LLC.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// $sections = Event::trigger('groups.onGroupAreas', array());
// print_r ( $sections );

use Components\Groups\Models\Page\Archive;

// Build the pages for menu
$pageArchive = Archive::getInstance();
$pages = $pageArchive->pages('tree', array(
	'gidNumber' => $this->group->get('gidNumber'),
	'state'     => array(1),
	'orderby'   => 'lft ASC'
), true);

// Grab logo
if ($this->group->get('logo') == NULL) {
	$logo = NULL;
} else {
	$logo = rtrim(str_replace(PATH_ROOT, '', __DIR__), 'template/includes') . DS . 'uploads' . DS . $this->group->get('logo');
}
?>

<div class="super-group-header-wrap">
	<div class="super-group-header cf">
	</div>
</div>

<header class="super-group-header-overlay-wrap" onclick="location.href='<?php echo Route::url('index.php?option=com_groups&cn=' . $this->group->get('cn')); ?>';" style="cursor: pointer;">
	<div class="super-group-header-overlay">
		<h1 class="header-id">
			<a href="<?php echo Route::url('index.php?option=com_groups&cn=' . $this->group->get('cn')); ?>" title="<?php echo $this->group->get('description'); ?> Home">
				<?php if ($logo) : ?>
					<img src="<?php echo $logo ?>" class="header-id-logo">
				<?php endif; ?>
				<span><?php echo $this->group->get('description'); ?></span>
			</a>
		</h1>
	</div>
</header>

<div class="super-group-menu-wrap">
	<nav class="super-group-menu" aria-label="main navigation">
		<!-- ###  Start Menu Include  ### -->
		<?php include_once dirname(__DIR__) . DS . 'helpers' . DS . 'menu.php'; ?>
		<!-- ###  End Menu Include  ### -->
		<button class="hidden-menu" aria-label="more main menu items" aria-haspopup="true" aria-expanded="false">
      <div class="hamburger"></div>
    </button>
	</nav>
</div>
