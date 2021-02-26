<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$inline = $this->inline;
$name = htmlspecialchars($this->name, ENT_COMPAT);
$type = $this->type;

$otherOption = new stdClass();
$otherOption->label = 'other';
$otherOption->value = '1';

$this->view("_form_field_$type")
	->set('inline', $inline)
	->set('name', $name)
	->set('option', $otherOption)
	->display();

?>

<label>
	<input type="text" name="<?php echo $name; ?>[other]" />
</label>
