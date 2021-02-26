<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$responses = $this->responses;

?>

<ul class="responses-list">
	<?php
		foreach ($responses as $response):
			$this->view('_response_item')
				->set('response', $response)
				->display();
		endforeach;
	?>
</ul>
