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

$checkboxName = $this->checkboxName;
$response = $this->response;
$reviewer = $response->getReviewer();
$reviewerId = $reviewer->get('id');
$form = $response->getForm();
$formName = $form->get('name');
$formId = $form->get('id');
$reviewerName = $reviewer->get('name');
$responseAccepted = $response->get('accepted');
$responseCreated = $response->get('created');
$responseId = $response->get('id');
$responseModified = $response->get('modified');
$responseProgress = $response->requiredCompletionPercentage();
$responseSubmitted = $response->get('submitted');
$selectable = $this->selectable;
$user = $response->getUser();
$userId = $user->get('id');
$usersName = $user->get('name');
$columns = $this->columns;
?>

<tr class="response-item">

	<?php	if ($selectable): ?>
		<td>
			<input type="checkbox" name="response_ids[]" value="<?php echo $responseId; ?>">
		</td>
	<?php	endif; ?>
	<?php foreach ($columns as $field => $title): ?>
	<?php 
	if ($field == 'id'):
		echo "<td>";
		$this->view('_link', 'shared')
			->set('content', $responseId)
			->set('urlFunction', 'responseFeedUrl')
			->set('urlFunctionArgs', [$responseId])
			->display();
		echo "</td>";
	endif;
	?>

	<?php 
	if ($field == 'form'):
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
	if ($field == 'user_id'):
		echo "<td>";
		$this->view('_link', 'shared')
			->set('content', $usersName)
			->set('urlFunction', 'userProfileUrl')
			->set('urlFunctionArgs', [$userId])
			->display();
		echo "</td>";
	endif;
	?>

	<?php 
	if ($field == 'completion_percentage'):
		echo "<td>";
		echo "$responseProgress%"; 
		echo "</td>";
	endif;
	?>

	<?php 
	if ($field == 'created'):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $responseCreated)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if ($field == 'modified'):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $responseModified)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if ($field == 'submitted'):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $responseSubmitted)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if ($field == 'accepted'):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $responseAccepted)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if ($field == 'reviewed_by'):
		echo "<td>";
		$this->view('_link', 'shared')
			->set('content', $reviewerName)
			->set('urlFunction', 'userProfileUrl')
			->set('urlFunctionArgs', [$reviewerId])
			->display();
		echo "</td>";
	endif;
	?>

	<?php 
	if ($field == 'action'):
		echo "<td>";
		if ($formsAuth->canCurrentUserFillResponse($response)) {
			$this->view('_link', 'shared')
				->set('content', 'Edit')
				->set('urlFunction', 'formResponseFillUrl')
				->set('urlFunctionArgs', [$responseId])
				->set('classes', 'icon-edit btn')
				->display();
		} else {
			$this->view('_link', 'shared')
				->set('content', 'View')
				->set('urlFunction', 'formResponseViewUrl')
				->set('urlFunctionArgs', [$responseId])
				->set('classes', 'icon-eye-open btn')
				->display();
		}
		echo "</td>";
	endif;
	?>

	<?php endforeach; ?>
</tr>
