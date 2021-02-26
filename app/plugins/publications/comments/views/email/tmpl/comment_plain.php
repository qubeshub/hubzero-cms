<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$base = rtrim(Request::base(), '/');
$sef  = Route::url('index.php?option=' . $this->option . '&id=' . $this->publication->get('id') . '&v=' . $this->publication->get('version_number') . '&active=comments#c' . $this->comment->get('id'));
$link = $base . '/' . trim($sef, '/');

// Build message
$message = '';
$message .= ($this->comment->get('anonymous')) ? Lang::txt('PLG_PUBLICATIONS_COMMENTS_ANONYMOUS') : $this->comment->creator->get('name') . ' (' . $this->comment->creator->get('username') . ')';
$message .= ' wrote (in ' . $this->publication->title . '):';

$output = html_entity_decode(strip_tags($this->comment->content), ENT_COMPAT, 'UTF-8');
$output = preg_replace_callback(
	"/(&#[0-9]+;)/",
	function($m)
	{
		return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
	},
	$output
);

$message .= $output;

$message = preg_replace('/\n{3,}/', "\n\n", $message);

// Output message
echo preg_replace('/<a\s+href="(.*?)"\s?(.*?)>(.*?)<\/a>/i', '\\1', $message) . "\n\n" . $link . "\n";
