<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$item = $this->item;
$comment = $item->get('description');
?>

<div class="feed-item">

	<div>
		<?php
			$this->view('_response_feed_item_user_link')
				->set('item', $item)
				->display();

			$this->view('_response_feed_item_date')
				->set('item', $item)
				->display();
		?>
	</div>

	<div class="comment-content">
		<?php echo $comment; ?>
	</div>

</div>
