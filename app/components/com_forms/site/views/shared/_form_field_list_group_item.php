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
$option = $this->option;
$label = htmlspecialchars($option->label);
$isSelected = $this->isSelected;
$value = htmlspecialchars($option->value);
$type = htmlspecialchars($this->type);
?>

<label class="list-item <?php if(!$inline) echo 'block'; ?>">
	<input type="<?php echo $type; ?>"
		name="<?php echo $name; ?>"
		value="<?php echo $value; ?>"
		<?php if ($isSelected) echo 'checked'; ?>>
	<?php echo $label; ?>
</label>
