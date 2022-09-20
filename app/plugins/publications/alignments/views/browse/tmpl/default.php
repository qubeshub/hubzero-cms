<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$url = 'index.php?option=' . $this->option . '&' . ($this->publication->alias ? 'alias=' . $this->publication->alias : 'id=' . $this->publication->id) . '&active=alignments';

$db = App::get('db');

$this->css();
?>
<h3 id="plg-alignments-header" class="section-header">
	<?php echo Lang::txt('PLG_PUBLICATIONS_ALIGNMENTS'); ?>
</h3>

<div class="publication-alignments">
	<?php if ($this->cloud) { ?>
		<div class="pub-content">
		<?php echo $this->cloud; ?>
		</div>
	<?php } else { ?>
		<p class="nocontent">No alignments</p>	
	<?php } ?>
</div>
