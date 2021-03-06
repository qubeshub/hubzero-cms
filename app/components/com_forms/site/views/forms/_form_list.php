<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$componentPath = Component::path('com_forms');

use Components\Forms\Helpers\FormsRouter as RoutesHelper;

$forms = $this->forms;
$routes = new RoutesHelper();
?>

<ul class="form-list">
	<?php
		foreach ($forms as $form):
			$this->view('_form_item')
				->set('form', $form)
				->set('displayUrl', $routes->formsDisplayUrl($form->get('id')))
				->display();
		endforeach;
	?>
</ul>
