<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$form = $this->form;
?>

<form id="surveyjsForm">
	<?php
		$this->view('_surveyjs_creator')
			->display();
	?>
	<input type="hidden" name="id" value="<?php echo $form->get('id'); ?>">
</form>