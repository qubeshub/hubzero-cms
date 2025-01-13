<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$componentPath = \Component::path('com_forms');

require_once "$componentPath/helpers/formsRouter.php";

use Components\Forms\Helpers\FormsRouter;

$routes = new FormsRouter();

$classes = isset($this->classes) ? $this->classes : '';
$confirm = isset($this->confirm) ? $this->confirm : false;
$content = $this->content;
$urlFunction = $this->urlFunction;
$urlFunctionArgs = $this->urlFunctionArgs;
$url = $routes->$urlFunction(...$urlFunctionArgs);
?>

<a href="<?php echo $url; ?>" class="protected-link <?php echo $classes; ?>" <?php if ($confirm): ?>onclick="return confirm('<?php echo $confirm; ?>');"<?php endif; ?>>
	<?php echo $content; ?>
</a>
