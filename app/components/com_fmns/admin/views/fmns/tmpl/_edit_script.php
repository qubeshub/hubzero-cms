<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();
?>
<script type="text/javascript">
// Note:  Future self, ideally this would use AJAX. As it works now, it kicks you out of the page
// on all actions (in particular, event actions).
function submitbutton(pressbutton)
{
	var form = document.adminForm;

	if (pressbutton == 'updatefmnevent') {
		if (confirm('<?php echo Lang::txt('COM_FMNS_CONFIRM_UPDATE_FMN_EVENT'); ?>')){
			submitform(pressbutton);
			return;
		} else {
			return;
		}
	}

	if (pressbutton == 'updateregevent') {
		if (confirm('<?php echo Lang::txt('COM_FMNS_CONFIRM_UPDATE_REG_EVENT'); ?>')){
			submitform(pressbutton);
			return;
		} else {
			return;
		}
	}

	if (pressbutton == 'deletefmnevent') {
		if (confirm('<?php echo Lang::txt('COM_FMNS_CONFIRM_DELETE_FMN_EVENT'); ?>')){
			submitform(pressbutton);
			return;
		} else {
			return;
		}
	}

	if (pressbutton == 'deleteregevent') {
		if (confirm('<?php echo Lang::txt('COM_FMNS_CONFIRM_DELETE_REG_EVENT'); ?>')){
			submitform(pressbutton);
			return;
		} else {
			return;
		}
	}


	if ((pressbutton == 'cancel') ||
	    (pressbutton == 'createfmnevent') ||
			(pressbutton == 'createregevent') ||
		  (pressbutton == 'editfmnevent') ||
		  (pressbutton == 'editregevent')) {
		submitform(pressbutton);
		return;
	}

	// do field validation, where we make sure required fields were not left blank
	if ($('#field-name').val() == '') {
		alert("<?php echo Lang::txt('COM_FMNS_ERROR_MISSING_FIELDS'); ?>");
	} else {
		<?php echo $this->editor()->save('text'); ?>

		submitform(pressbutton);
	}
}
</script>
