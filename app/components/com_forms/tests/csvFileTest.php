<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Tests;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/csvFile.php";

use Hubzero\Test\Basic;
use Components\Forms\Helpers\CsvFile;

class CsvFileTest extends Basic
{

	public function testGetPathReturnsPath()
	{
		$expectedPath = '/test/path';
		$file = new CsvFile(['path' => $expectedPath]);

		$path = $file->getPath();

		$this->assertEquals($expectedPath, $path);
	}

}
