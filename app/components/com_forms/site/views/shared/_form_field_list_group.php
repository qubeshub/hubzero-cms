<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$field = $this->field;
$inline = $field->get('inline');
$name = htmlspecialchars($field->get('name'), ENT_COMPAT);
$options = $field->getOptions();
$renderOther = $field->get('other');
$type = $this->type;
?>

<div class="field-wrap">
	<?php
		foreach($options as $option):
			$this->view("_form_field_$type")
				->set('field', $field)
				->set('inline', $inline)
				->set('name', $name)
				->set('option', $option)
				->display();
		endforeach;

		if ($renderOther):
			$this->view('_form_field_' . $type . '_other')
				->set('field', $field)
				->set('inline', $inline)
				->set('name', $name)
				->display();
		endif;
	?>
</div>

