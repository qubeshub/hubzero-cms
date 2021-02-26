<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$action = $this->action;
?>

<form method="post" action="<?php echo $action; ?>">

	<div class="row">
			<?php echo Html::input('token'); ?>
			<input class="btn btn-warning" type="submit"
				value="<?php echo Lang::txt('COM_FORMS_FIELDS_CLEAR_SEARCH'); ?>">
	</div>

</form>
