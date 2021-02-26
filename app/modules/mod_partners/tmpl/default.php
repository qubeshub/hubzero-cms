<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

// Push the module CSS to the template
// $this->getLayoutPath('_item');
$this->css();
?>
<div <?php echo 'class="network-logos' . (($this->moduleclass) ? ' ' . $this->moduleclass : '') . '"';?>>
	<?php
	foreach($this->partners as $partner) {
		require $this->getLayoutPath('_item');
	} ?>
</div>
