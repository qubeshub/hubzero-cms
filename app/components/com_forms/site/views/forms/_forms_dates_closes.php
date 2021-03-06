<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$isClosed = $this->isClosed;
$closingTime = $this->closingTime;

if ($isClosed)
{
	$closeTitle = Lang::txt('COM_FORMS_HEADINGS_DATES_CLOSED');
}
else
{
	$closeTitle = Lang::txt('COM_FORMS_HEADINGS_DATES_CLOSES');
}
?>

<div>
	<h3>
		<?php echo $closeTitle; ?>
	</h3>
	<?php echo date('F dS, Y', strtotime($closingTime)); ?>
</div>
