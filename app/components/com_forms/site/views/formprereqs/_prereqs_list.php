<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$forms = $this->forms;
$prereqs = $this->prereqs;
?>

<ul class="prereq-list">
	<?php
		foreach ($prereqs as $prereq):
			$this->view('_prereq_item')
				->set('forms', $forms)
				->set('prereq', $prereq)
				->display();
		endforeach;
	?>
</ul>

