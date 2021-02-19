<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$action = $this->action;
$tagButton = Lang::txt('COM_FORMS_HEADINGS_TAG_RESPONSES');
$formId = $this->formId;
?>


<span>
	<span class="fontcon">&#xf02b;</span>
	<?php echo $tagButton; ?>
</span>

<form id="tag-responses-form" action="<?php echo $action; ?>">
	<input type="hidden" name="form_id" value="<?php echo $formId; ?>">
</form>
