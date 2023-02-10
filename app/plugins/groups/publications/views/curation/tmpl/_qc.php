<?php 
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
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
        <p>Correspondance: First contact</p>
        <div class="email-message-template">
            <label for="email-template">Email template:</label>
            <select name="templates" id="email-template">
                <option value="">--Please choose an option--</option>
                <option value="pass">Article has been accepted</option>
                <option value="fail">Article needs revision</option>
            </select>
            <p><a href="#snippets" class="modal">Email snippets</a> <span class="instructions">a collection of saved "snippets" that can be copied into your email</span></p>
            <div id="snippets">
                <div class="snippet-wrapper">
                    <div class="snippet-category">
                    <ul>
                        <li><a href="#category-1">Category 1</a></li>
                        <li><a href="#category-2">Category 2</a></li>
                        <li><a href="#category-3">Category 3</a></li>
                    </ul>
                    </div>
                    <div class="snippets">
                        <h6 id="#category-1">Category 1</h6>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                        <h6 id="#category-2">Category 2</h6>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                        <h6 id="#category-3">Category 3</h6>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                        <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>
                    </div>
                </div>
            </div>
            <div class="email-address">
                <label>
                    <span class="email-field">To:</span>
                    <input type="email" name="email-to" id="email-to" required>
                </label>
                <label>
                    <span class="email-field">From:</span>
                    <input type="email" name="email-from" id="email-from" value="coursesourceeditor@gmail.com" required>
                </label>
                <label>
                    <span class="email-field">CC:</span>
                    <input type="email" name="email-cc" id="email-cc">
                </label>
                <label>
                    <span class="email-field">BCC:</span>
                    <input type="email" name="email-bcc" id="email-bcc">
                </label>
                <label>
                    <span class="email-field">Subject:</span>
                    <input type="text" name="email-subject" id="email-subject" required>
                </label>
            </div>
            <div class="message-box">
                <label>
                    <textarea name="message" id="message" rows="10"></textarea>
                </label>
            </div>
            <input type="submit" class="btn" value="submit">
            <input type="submit" class="btn" value="cancel">
        </div>
    </form>
</div>

