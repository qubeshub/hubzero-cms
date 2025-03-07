<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

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

	<?php 
	if (isset($columns['id'])):
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
	if (isset($columns['form'])):
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
	if (isset($columns['user_id'])):
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
	if (isset($columns['completion_percentage'])):
		echo "<td>";
		echo "$responseProgress%"; 
		echo "</td>";
	endif;
	?>

	<?php 
	if (isset($columns['created'])):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $responseCreated)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if (isset($columns['modified'])):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $responseModified)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if (isset($columns['submitted'])):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $responseSubmitted)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if (isset($columns['accepted'])):
		echo "<td>";
		$this->view('_date', 'shared')
			->set('date', $responseAccepted)
			->display();
		echo "</td>";
	endif;
	?>

	<?php
	if (isset($columns['reviewed_by'])):
		echo "<td>";
		$this->view('_link', 'shared')
			->set('content', $reviewerName)
			->set('urlFunction', 'userProfileUrl')
			->set('urlFunctionArgs', [$reviewerId])
			->display();
		echo "</td>";
	endif;
	?>

</tr>
