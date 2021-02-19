<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Tests;

$componentPath = Component::path('com_forms');

require_once "$componentPath/models/fieldResponse.php";

use Hubzero\Test\Basic;
use Components\Forms\Models\FieldResponse;

class FieldResponseTest extends Basic
{

	public function testInitiateHasCreated()
	{
		$response = FieldResponse::blank();

		$initiate = $response->initiate;
		$hasCreated = in_array('created', $initiate);

		$this->assertEquals(true, $hasCreated);
	}

	public function testRulesRequireFormResponseId()
	{
		$response = FieldResponse::blank();

		$validation = $response->rules['form_response_id'];

		$this->assertEquals('notempty', $validation);
	}

	public function testRulesRequireFieldId()
	{
		$response = FieldResponse::blank();

		$validation = $response->rules['field_id'];

		$this->assertEquals('notempty', $validation);
	}

}
