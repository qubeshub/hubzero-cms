<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$option = $this->option;
$value = htmlspecialchars($option->value);
$label = htmlspecialchars($option->label);
$selected = $this->selected;
?>

<option value="<?php echo $value; ?>" <?php if ($selected) echo 'selected'; ?>>
	<?php echo $label; ?>
</option>
