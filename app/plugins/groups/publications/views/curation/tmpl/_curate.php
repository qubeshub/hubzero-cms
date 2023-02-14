<?php 
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2023 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$this->css('curation'); 
?>

<div class="panel">
    <h3 class="section-header">Pending Action: <?php echo $this->status; ?></h3>
    <a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=curate&status=qc'; ?>" class="actionable"><?php echo $this->actionable; ?></a>
</div>