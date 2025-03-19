<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$action = $this->action;
$deleteButton = 'Unsubmit responses';
$formId = $this->formId;
$returnUrl = $this->returnUrl;
?>


<span>
	<span class="fontcon icon-unlock"></span>
	<?php echo $deleteButton; ?>
</span>

<form id="delete-responses-form" action="<?php echo $action; ?>">
	<input type="hidden" name="form_id" value="<?php echo $formId; ?>">
    <input type="hidden" name="return_url" value="<?php echo $returnUrl; ?>">
</form>