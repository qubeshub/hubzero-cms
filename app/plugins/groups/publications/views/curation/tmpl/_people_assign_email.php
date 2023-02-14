<?php 
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2023 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$this->css('email_messaging')
    ->js('email_messaging.js'); 
?>

<div class="panel">
    <h3 class="section-header"><?php echo $this->role; ?> Assignment: In Progress</h3>
    <?php
        $view = $this->view('_email_messaging', 'curation')
                ->set('group', $this->group)
                ->set('corresponding_author', 'eoeditor@gmail.com')
                // set 'from' field based on who is logged in
                ->set('from', 'coursesourceeditor@gmail.com') 
                ->display();
    ?>
</div>