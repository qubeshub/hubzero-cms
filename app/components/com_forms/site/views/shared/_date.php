<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$convertToLocal = isset($this->toLocal) ? $this->toLocal : true;
$date = $this->date;
$format = isset($this->format) ? $this->format : 'F j, Y';

if ($convertToLocal)
{
	$date = Date::of($date)->toLocal();
}

if (!!$date)
{
	$dateString = (new DateTime($date))->format($format);
}
else
{
	$dateString = '';
}

echo $dateString;
