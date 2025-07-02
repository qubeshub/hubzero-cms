<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$componentPath = Component::path('com_forms');

require_once "$componentPath/helpers/formsRouter.php";

use Components\Forms\Helpers\FormsRouter as Routes;

$routes = new Routes();

$formId = $this->formId;
$current = $this->current;
$steps = $this->steps;
?>

<div class="nav-button-container">
	<?php if ($this->showAccessBtn): ?>
	<a class="btn surveyjs-popup-btn icon-group">
		<?php echo Lang::txt('COM_FORMS_LINKS_RESPONSE_ACCESS'); ?>
	</a>
	<?php endif; ?>
	<a class="btn icon-arrow-left" href="<?php echo Route::url($routes->formsDisplayUrl($formId)); ?>">
		<?php echo Lang::txt('COM_FORMS_LINKS_OVERVIEW'); ?>
	</a>
</div>

<ul class="ul-nav">
	<?php foreach ($steps as $text => $url): ?>

		<li <?php if ($current == $text) echo 'class="current"'; ?>>
			<a href="<?php echo $url; ?>">
				<?php echo $text; ?>
			</a>
		</li>

	<?php endforeach; ?>
</ul>
