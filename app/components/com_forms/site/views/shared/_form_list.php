<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formsAdminItemsList');

$checkboxesName = 'items_ids[]';
$columns = [
    'id' => 'ID',
    'name' => 'Name',
	'created' => 'Started',
	'created_by' => 'Owner',
	'modified' => 'Last Activity',
    'opening_time' => 'Opening Time',
    'closing_time' => 'Closing Time',
	'action' => 'Action'
];
$columns = isset($this->columns) ? array_intersect_key($columns, array_flip($this->columns)) : $columns;
$forms = $this->forms;
$formsSelectable = isset($this->selectable) ? $this->selectable : true;
$sortingAction = $this->sortingAction;
$sortingCriteria = $this->sortingCriteria;
?>

<table id="item-list">
	<thead>
		<tr>

			<?php	if ($formsSelectable): ?>
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
			foreach ($forms as $form):
				$this->view('_form_item')
					->set('columns', $columns)
					->set('form', $form)
					->set('selectable', $formsSelectable)
					->display();
			endforeach;
		?>
	</tbody>
</table>

<form id="sort-form" action="<?php echo $sortingAction; ?>">
	<input type="hidden" name="sort_direction">
	<input type="hidden" name="sort_field">
</form>
