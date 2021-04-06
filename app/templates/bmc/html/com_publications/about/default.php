<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$description = $this->publication->describe('parsed');
?>

<h4><?php echo Lang::txt('COM_PUBLICATIONS_DESCRIPTION'); ?></h4>
<?php echo $description; ?>