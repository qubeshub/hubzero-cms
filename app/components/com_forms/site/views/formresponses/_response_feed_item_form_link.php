<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$formName = $this->formName;
$responseId = $this->responseId;
?>

<span>
	<?php
		$this->view('_link', 'shared')
			->set('content', $formName)
			->set('urlFunction', 'responseFeedUrl')
			->set('urlFunctionArgs', [$responseId])
			->display();
	?>
</span>
