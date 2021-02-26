<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$title = $this->title;
$isRequired = $this->isRequired;
?>

<h4 class="collapsible-field-header">
	<span>
		<?php echo $title; ?>

		<?php if ($isRequired): ?>
			<span class="required">
				<?php echo Lang::txt('COM_FORMS_FIELDS_META_REQUIRED'); ?>
			</span>
		<?php endif; ?>

	</span>
	<span class="caret fontcon">&#x2303;</span>
</h4>
