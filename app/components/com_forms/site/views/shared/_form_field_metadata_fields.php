<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$field = $this->field;
$fieldId = $field->get('id');
$fieldName = $field->get('name');
$fieldResponse = $field->getCurrentUsersResponse();
$fieldResponseId = $fieldResponse->get('id');
$formResponseId = $fieldResponse->getFormResponseId();
$fieldIdInputName = $fieldName . '[field_id]';
$formResponseInputName = $fieldName . '[form_response_id]';
$responseIdInputName = $fieldName . '[id]';
?>

<input type="hidden"
	name="<?php echo $formResponseInputName; ?>"
	value="<?php echo $formResponseId; ?>">
<input type="hidden"
	name="<?php echo $fieldIdInputName; ?>"
	value="<?php echo $fieldId; ?>">
<input type="hidden"
	name="<?php echo $fieldIdInputName; ?>"
	value="<?php echo $fieldId; ?>">
<input type="hidden"
	name="<?php echo $responseIdInputName; ?>"
	value="<?php echo $fieldResponseId; ?>">
