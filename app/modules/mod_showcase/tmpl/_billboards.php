<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

// Initialize boards from new collection
$collection = $item["content"];
if (!array_key_exists($collection, $boards)) {
	$boards[$collection] = $this->_getBillboards($collection);
}

// Make sure we don't ask for too much
$n = min($item["n"], count($boards[$collection]));
if ($n < $item["n"]) {
	echo 'Showcase Module Error: Not enough billboards left in collection "' . $collection . '"!';
}

if ($item["ordering"] === "ordered") {
	// Pulls billboards based on their ordering
	$item_boards = array_slice($boards[$collection], 0, $n);
	$boards[$collection] = array_slice($boards[$collection], $n, count($boards[$collection]));
} elseif ($item["ordering"] === "random") {
	// Pulls billboards randomly
	$rind = array_flip((array) array_rand($boards[$collection], $n));
	$item_boards = array_intersect_key($boards[$collection], $rind);
	shuffle($item_boards);
	$boards[$collection] = array_diff_key($boards[$collection], $rind);
} elseif ($item["ordering"] === "indexed") {
	// Pulls billboards based on id - this should be everything
	$item_boards = array();
	$remove_keys = array();
	foreach ($boards[$collection] as $key => $board)
	{
		if (in_array($board->ordering, $item["indices"]))
		{
			$item_boards[] = $board;
			$remove_keys[] = $key;
		}
	}
	$boards[$collection] = array_diff_key($boards[$collection], array_flip($remove_keys));
} else {
	echo 'Showcase Module Error: Unknown ordering "' . $item["ordering"] . '".  Possible values include "ordered" or "random".';
}

// Display individual boards
foreach ($item_boards as $board) { ?>
	<div class="<?php echo $item['class'] ?> billboard">
		<?php
		if (!empty($board->learn_more_target))
		{
			echo '<a href="' . $board->learn_more_target . '" aria-hidden="true" tabindex="-1">';
		}
		?>
		<div class="billboard-image">
			<img src="<?php echo $this->image_location; ?><?php echo $board->background_img; ?>" alt=""/>
		</div>
		<?php
		if (!empty($board->learn_more_target))
		{
			echo '</a>';
		}
		?>
		<?php if ($item['tag']): ?>
			<?php if ($item['tag-target']): ?>
		  <a href="<?php echo $item['tag-target']; ?>">
			<?php endif; ?>
			  <div class="billboard-tag">
					<span><?php echo $item['tag']; ?></span>
  			</div>
			<?php if ($item['tag-target']): ?>
			</a>
		  <?php endif; ?>
		<?php endif; ?>
		<div class="billboard-header">
			<?php
			if (!empty($board->learn_more_target))
			{
				echo '<a href="' . $board->learn_more_target . '">';
			}
			?>
			<span><?php echo $board->header; ?></span>
			<?php
			if (!empty($board->learn_more_target))
			{
				echo '</a>';
			}
			?>
		</div>
		<div class="billboard-content">
			<?php echo $board->text; ?>
		</div>
	</div>
<?php } ?>
