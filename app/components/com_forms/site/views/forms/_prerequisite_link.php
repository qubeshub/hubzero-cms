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

use Components\Forms\Helpers\FormsRouter;

$router = new FormsRouter();
$prereq = $this->prereq;
$parentId = $prereq->get('prerequisite_id');
$prereqLink = $router->formsDisplayUrl($parentId);
$prereqName = $prereq->getParent('name');
?>

<a href="<?php echo $prereqLink; ?>">
	<?php echo $prereqName; ?>
</a>
