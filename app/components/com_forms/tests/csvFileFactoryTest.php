<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Tests;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/csvFileFactory.php";

use Hubzero\Test\Basic;
use Components\Forms\Helpers\CsvFileFactory;

class CsvFileFactoryTest extends Basic
{

	public function testCreateReturnsFileInstance()
	{
		$factory = new CsvFileFactory();

		$file = $factory->create('', '');
		$fileClass = get_class($file);

		$this->assertEquals('Components\Forms\Helpers\CsvFile', $fileClass);
	}

}
