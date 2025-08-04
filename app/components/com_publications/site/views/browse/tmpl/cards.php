<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$this->css()
     ->css('browse.css');

$no_html = Request::getInt('no_html', 0);
?>

<?php if (!$no_html) { ?>
<div class="card-container">
<?php } ?>
	<?php
	$html = '';
	foreach ($this->results as $row)
	{
		$html .= $this->view('_card') //calling _card view here
			->set('row', $row)
			->set('base', $this->base)
			->loadTemplate();
	}
	echo $html;

	if (!count($this->results))
	{
		echo '<p class="warning">' . Lang::txt('COM_PUBLICATIONS_NO_RESULTS') . '</p>';
	}

	echo $this->pageNav->render();
	?>
<?php if (!$no_html) { ?>
</div>
<?php } ?>
