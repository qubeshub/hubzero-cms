<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$form = $this->form;
$formName = $form->get('name');
$response = $this->response;
?>

<div class="grid form-overview">

	<div class="row">
		<h2><?php echo  $formName; ?></h2>
	</div>

	<div class="row">
		<?php
			$this->view('_forms_dates')
				->set('form', $form)
				->display();
		?>
	</div>

	<div class="row">
		<?php
			$this->view('_forms_steps')
				->set('form', $form)
				->display();
		?>
	</div>

</div>
