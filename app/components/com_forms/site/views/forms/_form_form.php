<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$action = $this->action;
$form = $this->form;
?>

<form id="hubForm" class="full" method="post" action="<?php echo $action; ?>">
	<?php
		$this->view('_dates_option_fields')
			->set('form', $form)
			->display();
	?>
</form>

<form>
	<?php
		$this->view('_surveyjs')
			->display();
	?>
	<input type="hidden" name="id" value="<?php echo $form->get('id'); ?>">
</form>