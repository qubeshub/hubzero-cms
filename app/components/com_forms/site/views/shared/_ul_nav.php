<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$current = $this->current;
$steps = $this->steps;
?>

<ul class="ul-nav">
	<?php foreach ($steps as $text => $url): ?>

		<li <?php if ($current == $text) echo 'class="current"'; ?>>
			<a href="<?php echo $url; ?>">
				<?php echo $text; ?>
			</a>
		</li>

	<?php endforeach; ?>
</ul>
