<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$field = $this->field;
$fieldName = $field->get('name');
$inputValue = $field->getInputValue();
$maxLength = $field->get('max_length');
$rows = $field->get('rows');
$userInputName = $fieldName . '[response]';
?>

<textarea name="<?php echo $userInputName; ?>"
	maxlength="<?php echo $maxLength; ?>"
	rows="<?php echo $rows; ?>"><?php echo $inputValue; ?>
</textarea>

<?php
	$this->view('_form_field_metadata_fields')
		->set('field', $field)
		->display();
?>
