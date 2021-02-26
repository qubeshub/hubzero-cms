<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$isOpen = $this->isOpen;
$openingTime = $this->openingTime;

if ($isOpen)
{
	$openTitle = Lang::txt('COM_FORMS_HEADINGS_DATES_OPENED');
}
else
{
	$openTitle = Lang::txt('COM_FORMS_HEADINGS_DATES_OPENS');
}
?>

<div>
	<h3>
		<?php echo $openTitle; ?>
	</h3>
	<?php echo date('F jS, Y', strtotime($openingTime)); ?>
</div>
