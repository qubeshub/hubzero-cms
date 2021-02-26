<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Tests;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/tagResultFactory.php";
require_once "$componentPath/tests/helpers/canMock.php";

use Hubzero\Test\Basic;
use Components\Forms\Helpers\TagResultFactory;

class TagResultFactoryTest extends Basic
{

	public function testAddResultReturnsAddResult()
	{
		$factory = new TagResultFactory();

		$result = $factory->addResult();

		$this->assertEquals('Components\Forms\Helpers\AddTagsResult', get_class($result));
	}

	public function testUpdateResultReturnsUpdateResult()
	{
		$factory = new TagResultFactory();

		$result = $factory->updateResult();

		$this->assertEquals('Components\Forms\Helpers\UpdateTagsResult', get_class($result));
	}

}
