<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

// Push the module CSS to the template
$this->css()
     ->js();
?>

<li class="component-parent" id="mycollectionsmini">
  <a class="component-button"><span class="nav-icon-collections"><?php echo file_get_contents(PATH_CORE . DS . "assets/icons/archive.svg") ?></span><span>My Collections</span><span class="nav-icon-more"><?php echo file_get_contents(PATH_CORE . DS . "assets/icons/chevron-right.svg") ?></span></a>
  <div class="component-panel">
    <header><h2>My Collections</h2></header>
    <a class="component-button"><span class="nav-icon-back"><?php echo file_get_contents(PATH_CORE . DS . "assets/icons/chevron-left.svg") ?></span>Back</a>
    <div<?php echo ($this->moduleclass) ? ' class="' . $this->moduleclass . '"' : '';?>>
    <div class="module-section">Recent Posts</div>
    <ul class="module-nav grouped">
      <?php $total = count($this->allposts);
      if ($total) { 
        foreach($this->allposts as $post) {
          require $this->getLayoutPath('_item');
        }
        ?>
      <?php } else { ?>
        <li><em><?php echo Lang::txt('MOD_MYCOLLECTIONSMINI_NO_POSTS'); ?></em></li>
      <?php } ?>
      
      <?php if ($total > $this->limit) { ?>
  			<li class="note"><?php echo Lang::txt('MOD_MYCOLLECTIONSMINI_YOU_HAVE_MORE', $this->limit, $total, Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=collections&action=posts')); ?></li>
  		<?php } ?>
      
      <li>
        <a class="icon-browse" href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=collections&action=posts'); ?>">
          <?php echo Lang::txt('MOD_MYCOLLECTIONSMINI_SHOW_POSTS'); ?>
        </a>
      </li>
      
      <?php if ($this->params->get('button_show_add', 1)) { ?>
        <li>
          <a class="icon-plus" href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=collections&action=newpost'); ?>">
            <?php echo Lang::txt('MOD_MYCOLLECTIONSMINI_NEW_POST'); ?>
          </a>
        </li>
      </ul>
    <?php } ?>
  </div>
</div>
</li>
