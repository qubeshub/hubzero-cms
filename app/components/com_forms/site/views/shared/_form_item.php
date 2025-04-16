<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$componentPath = \Component::path('com_forms');

require_once "$componentPath/helpers/formsAuth.php";

use Components\Forms\Helpers\FormsAuth;

$formsAuth = new FormsAuth();

$form = $this->form;
$formCreated = $form->get('created');
$formId = $form->get('id');
$formName = $form->get('name');
$formModified = $form->get('modified');
$selectable = $this->selectable;
$creatorId = $form->get('created_by');
$creatorsName = $form->getCreator()->get('name');
$formOpeningTime = $form->get('opening_time');
$formClosingTime = $form->get('closing_time');
$columns = $this->columns;
?>

<tr class="fr-item">

	<?php	if ($selectable): ?>
		<td>
			<input type="checkbox" name="item_ids[]" value="<?php echo $formId; ?>">
		</td>
	<?php	endif; ?>
	<?php foreach ($columns as $field => $title): ?>
	<?php 
	if ($field == 'id'):
		echo "<td>";
		$this->view('_link', 'shared')
			->set('content', $formId)
			->set('urlFunction', 'formsDisplayUrl')
			->set('urlFunctionArgs', [$formId])
			->display();
		echo "</td>";
	endif;
	?>

	<?php 
	if ($field == 'name'):
		echo "<td>";
		$this->view('_link', 'shared')
			->set('content', $formName)
			->set('urlFunction', 'formsDisplayUrl')
			->set('urlFunctionArgs', [$formId])
			->display();
		echo "</td>";
	endif;
	?>

	<?php 
	if ($field == 'created'):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $formCreated)
			->display();
		echo "</td>";
	endif;
	?>

<?php 
	if ($field == 'created_by'):
		echo "<td>";
		$this->view('_link', 'shared')
			->set('content', $creatorsName)
			->set('urlFunction', 'userProfileUrl')
			->set('urlFunctionArgs', [$creatorId])
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if ($field == 'modified'):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $formModified)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if ($field == 'opening_time'):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $formOpeningTime)
			->display();
		echo "</td>";
	endif;
	?>

    <?php
	if ($field == 'closing_time'):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $formClosingTime)
			->display();
		echo "</td>";
	endif;
	?>

	<?php 
	if ($field == 'action'):
		echo "<td>";
		if ($formsAuth->canCurrentUserEditForm($form)) {
			$this->view('_link', 'shared')
				->set('content', 'Edit')
				->set('urlFunction', 'formsEditUrl')
				->set('urlFunctionArgs', [$formId])
				->set('classes', 'icon-edit btn')
				->display();
		} else {
			$this->view('_link', 'shared')
				->set('content', 'View')
				->set('urlFunction', 'formsEditUrl')
				->set('urlFunctionArgs', [$formId])
				->set('classes', 'icon-eye-open btn')
				->display();
		}
		echo "</td>";
	endif;
	?>

	<?php endforeach; ?>
</tr>
