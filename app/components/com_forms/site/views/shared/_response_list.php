<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formsAdminResponsesList');

$checkboxesName = 'responses_ids[]';
$columns = [
	'id' => 'ID',
	'form' => 'Form',
	'user_id' => 'User',
	'created' => 'Started',
	'modified' => 'Last Activity',
	'completion_percentage' => 'Completion',
	'submitted' => 'Submitted',
	'accepted' => 'Accepted',
	'reviewed_by' => 'Reviewed By',
	'action' => 'Action'
];
$formId = isset($this->formId) ? $this->formId : null;
$columns = isset($this->columns) ? array_intersect_key($columns, array_flip($this->columns)) : $columns;
$responses = $this->responses;
$responsesSelectable = isset($this->selectable) ? $this->selectable : true;
$sortingAction = $this->sortingAction;
$sortingCriteria = $this->sortingCriteria;
?>

<table id="response-list">
	<thead>
		<tr>

			<?php	if ($responsesSelectable): ?>
				<td>
					<input id="master-checkbox" type="checkbox">
				</td>
			<?php	endif; ?>

			<?php
				$this->view('_sortable_column_headers', 'shared')
					->set('columns', $columns)
					->set('sortingCriteria', $sortingCriteria)
					->display();
			?>
		</tr>
	</thead>

	<tbody>
		<?php
			foreach ($responses as $response):
				$this->view('_response_item')
					->set('checkboxName', $checkboxesName)
					->set('columns', $columns)
					->set('response', $response)
					->set('selectable', $responsesSelectable)
					->display();
			endforeach;
		?>
	</tbody>
</table>

<form id="sort-form" action="<?php echo $sortingAction; ?>">
	<input type="hidden" name="sort_direction">
	<input type="hidden" name="sort_field">
	<?php if ($formId): ?>
		<input type="hidden" name="form_id" value="<?php echo $formId; ?>">
	<?php endif; ?>
</form>
