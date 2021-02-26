<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$form = $this->form;
$prereqs = $form->getPrerequisites();
$stepsTitle = Lang::txt('COM_FORMS_HEADINGS_STEPS');
?>

<?php if ($prereqs->count() > 0): ?>
	<div>
		<h3>
			<?php echo $stepsTitle; ?>
		</h3>

		<?php
			$this->view('_forms_steps_list')
				->set('prereqs', $prereqs)
				->display();
		?>

	</div>
<?php endif; ?>
