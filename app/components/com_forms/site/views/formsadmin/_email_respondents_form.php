<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$action = $this->action;
$emailButton = Lang::txt('COM_FORMS_HEADINGS_EMAIL_RESPONDENTS');
$formId = $this->formId;
?>


<span>
	<span class="fontcont">&#x2709; </span>
	<?php echo $emailButton; ?>
</span>

<form id="email-respondents-form" action="<?php echo $action; ?>">
	<input type="hidden" name="form_id" value="<?php echo $formId; ?>">
</form>
