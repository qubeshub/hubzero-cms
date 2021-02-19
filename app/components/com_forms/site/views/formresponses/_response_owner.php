<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$response = $this->response;
$ownerTitle = Lang::txt('COM_FORMS_HEADINGS_OWNER');
$owner = $response->getUser();
$ownerName = $owner->get('name');
?>

<div>
	<h3>
		<?php echo $ownerTitle; ?>
	</h3>

	<?php echo $ownerName; ?>
</div>

