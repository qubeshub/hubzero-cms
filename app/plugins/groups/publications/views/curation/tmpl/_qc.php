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
    <form>
        <h3>Quality Check: In Progress</h3>
        <fieldset>
            <legend>Quality Check</legend>
            <label>
                <input type="radio" name="checkOk" id="checkOk" value="pass">
                Quality Check OK
            </label>
            <br>
            <label>
                <input type="radio" name="sendBack" id="sendBack" value="fail">
                Send back to author: Does not pass quality check
            </label>
        </fieldset>
        <?php 
            $view = $this->view('_email_messaging', 'curation')
                ->set('group', $this->group)
                ->set('corresponding_author', 'eoeditor@gmail.com')
                // set 'from' field based on who is logged in
                ->set('from', 'coursesourceeditor@gmail.com') 
                ->display();
        ?>
    </form>
</div>

