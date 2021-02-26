<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$input = $this->decoration;
$fieldName = $input->get('name');
$userInputName = $fieldName . '[response]';
$value = $input->get('default_value');
?>

<div>
	<input type="hidden"
		name="<?php echo $userInputName; ?>"
		value="<?php echo $value; ?>">

	<?php
		$this->view('_form_field_metadata_fields')
			->set('field', $input)
			->display();
	?>
</div>
