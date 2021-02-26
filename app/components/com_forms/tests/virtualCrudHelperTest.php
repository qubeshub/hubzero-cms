<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forms\Tests;

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/virtualCrudHelper.php";
require_once "$componentPath/tests/helpers/canMock.php";

use Hubzero\Test\Basic;
use Components\Forms\Helpers\VirtualCrudHelper as CrudHelper;
use Components\Forms\Tests\Traits\canMock;

class VirtualCrudHelperTest extends Basic
{
	use canMock;

	public function testfailedCreateInvokesRedirect()
	{
		$url = 'url';
		$notify = $this->mock(['class' => 'Notify', 'methods' => ['error']]);
		$record = $this->mock([
			'class' => 'Relational', 'methods' => ['getErrors' => []]
		]);
		$router = $this->mock(['class' => 'App', 'methods' => ['redirect']]);
		$crudHelper = new CrudHelper([
			'notify' => $notify,
			'router' => $router
		]);

		$router->expects($this->once())
			->method('redirect')
			->with($this->equalTo($url));

		$crudHelper->failedCreate($record, $url);
	}

}
