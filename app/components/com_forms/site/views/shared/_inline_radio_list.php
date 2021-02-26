<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$radios = $this->radios;

foreach($radios as $radio):
	$radio = $this->view('_inline_radio')
		->set('checked', $radio['checked'])
		->set('name', $radio['name'])
		->set('text', $radio['text'])
		->set('value', $radio['value'])
		->display();
endforeach;
?>
