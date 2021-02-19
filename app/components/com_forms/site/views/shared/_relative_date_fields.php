<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$selectFieldName = $this->selectFieldName;

$dateFieldName = $this->dateFieldName;
$dateValue = $this->dateValue;
$relativeOperatorValue = $this->relativeOperatorValue;
?>

<span class="relative-date-fields">
	<div>
		<select name="<?php echo $selectFieldName; ?>">
			<option hidden value></option>
			<option value="<" <?php echo ($relativeOperatorValue == '<') ? 'selected' : ''; ?>>
				Before
			</option>
			<option value=">" <?php echo ($relativeOperatorValue == '>') ? 'selected' : ''; ?>>
				After
			</option>
		</select>
	</div>
	<input type="date" name="<?php echo $dateFieldName; ?>" value="<?php echo $dateValue; ?>">
</span>
