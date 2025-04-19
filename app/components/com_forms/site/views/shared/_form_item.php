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
require_once "$componentPath/helpers/formsRouter.php";

use Components\Forms\Helpers\FormsAuth;
use Components\Forms\Helpers\FormsRouter;

$formsAuth = new FormsAuth();
$routes = new FormsRouter();

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

<tr class="fr-item" data-share-link="https://example.com<?php echo $routes->formResponseStartUrl($formId); ?>">

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
		echo "<div class='fr-icon-row'>";

		// Manage
		$this->view('_protected_link', 'shared')
			->set('authMethod', 'canCurrentUserEditForm')
			->set('authArgs', [$form])
			->set('textKey', '<div class="fr-icon"><i class="icon-cog"></i></div>')
			->set('tooltip', 'Manage')
			->set('urlFunction', 'formsDisplayUrl')
			->set('urlFunctionArgs', [$formId])
			->set('classes', 'fr-icon-link tooltips')
			->display();

		// Copy fill link to clipboard
		$this->view('_link', 'shared')
			->set('content', '<div class="fr-icon"><i class="icon-link"></i></div>')
			->set('tooltip', 'Share link')
			->set('classes', 'fr-icon-link fr-clipboard tooltips')
			->display();

		// Delete
		$this->view('_protected_link', 'shared')
			->set('authMethod', 'canCurrentUserDeleteForm')
			->set('authArgs', [$form])
			->set('textKey', '<div class="fr-icon"><i class="icon-delete"></i></div>')
			->set('tooltip', 'Delete')
			->set('urlFunction', 'formsDeleteUrl')
			->set('urlFunctionArgs', [$formId])
			->set('classes', 'fr-icon-link tooltips')
			->set('confirm', 'Are you sure you want to delete this form?')
			->display();

		echo "</div>";
		echo "</td>";
	endif;
	?>

	<?php endforeach; ?>
</tr>
