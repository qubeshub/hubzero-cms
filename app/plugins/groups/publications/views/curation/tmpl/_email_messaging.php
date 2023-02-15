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

<div class="email-message-template">
    <p>Correspondance: First contact</p>
    <label for="email-template">Email template:</label>
    <select name="templates" id="email-template">
        <option value="">--Please choose an option--</option>
        <option value="pass">Article has been accepted</option>
        <option value="fail">Article needs revision</option>
    </select>
    <p><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/email_snippet'; ?>" class="modal">Email snippets</a> <span class="instructions">a collection of saved "snippets" that can be copied into your email</span></p>
    <div class="email-address">
        <label>
            <span class="email-field">To:</span>
            <input type="email" name="email-to" id="email-to" value="<?php $this->role === 'editor' ? $value = $this->corresponding_author : $value = $this->reviewer; echo $value; ?>" required>
        </label>
        <label>
            <span class="email-field">From:</span>
            <input type="email" name="email-from" id="email-from" value="<?php echo $this->from; ?>" required>
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
            <textarea name="message" id="message" rows="10">Can we do something like a maillist and use one email form to mail multiple people? Or do we need multiple email template views?</textarea>
        </label>
    </div>
    <input type="submit" class="btn" value="send">
    <input type="submit" class="btn" value="save and exit">
</div>