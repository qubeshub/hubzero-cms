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
    <div class="confidential-comments">
        <h4>Editor Comments</h4>
        <h5>Editor: Editor O'Editor</h5>
        <p>Recommendation: Minor Revisions</p>
        <p>Comments to Editor-in-Chief (<strong><em>confidential</em></strong>)</p>
        <div class="reviewer-comments">
            <p>Vestibulum id ligula porta felis euismod semper. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Curabitur blandit tempus porttitor. Curabitur blandit tempus porttitor. Donec sed odio dui. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.</p>
            <p>Aenean lacinia bibendum nulla sed consectetur. Vestibulum id ligula porta felis euismod semper. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Aenean lacinia bibendum nulla sed consectetur. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
            <p>Maecenas sed diam eget risus varius blandit sit amet non magna. Curabitur blandit tempus porttitor. Donec sed odio dui. Maecenas faucibus mollis interdum. Maecenas faucibus mollis interdum. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
        </div>
    </div>
    <div class="confidential-comments">
        <h4>Reviewer Comments</h4>
        <h5>Reviewer: Reviewer Reviewerton</h5>
        <p>Recommendation: Minor Revisions</p>
        <p>Files: <a href="#">Review_file.doc</a></p>
        <p>Additional files: </p>
        <p>Comments to Editor (<strong><em>confidential</em></strong>)</p>
        <div class="reviewer-comments">
            <p>Vestibulum id ligula porta felis euismod semper. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Curabitur blandit tempus porttitor. Curabitur blandit tempus porttitor. Donec sed odio dui. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.</p>
            <p>Aenean lacinia bibendum nulla sed consectetur. Vestibulum id ligula porta felis euismod semper. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Aenean lacinia bibendum nulla sed consectetur. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
            <p>Maecenas sed diam eget risus varius blandit sit amet non magna. Curabitur blandit tempus porttitor. Donec sed odio dui. Maecenas faucibus mollis interdum. Maecenas faucibus mollis interdum. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
        </div>

        <h5>Reviewer: Views Reviews</h5>
        <p>Recommendation: Minor Revisions</p>
        <p>Files: <a href="#">Review_file.doc</a></p>
        <p>Additional files: </p>
        <p>Comments to Editor (<strong><em>confidential</em></strong>)</p>
        <div class="reviewer-comments">
            <p>Vestibulum id ligula porta felis euismod semper. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Curabitur blandit tempus porttitor. Curabitur blandit tempus porttitor. Donec sed odio dui. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.</p>
            <p>Aenean lacinia bibendum nulla sed consectetur. Vestibulum id ligula porta felis euismod semper. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Aenean lacinia bibendum nulla sed consectetur. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
            <p>Maecenas sed diam eget risus varius blandit sit amet non magna. Curabitur blandit tempus porttitor. Donec sed odio dui. Maecenas faucibus mollis interdum. Maecenas faucibus mollis interdum. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
        </div>
    </div>
    <div class="proof_decision">
        <h4>Proof Decision Letter</h4>
        <p>Recommendation: Minor Revisions</p>
        <a href="#" class="btn">Modify recommendation</a>
        <?php 
                $view = $this->view('_email_messaging', 'curation')
                    ->set('group', $this->group)
                    ->set('role', 'proof_decision')
                    ->set('corresponding_author', 'authorauthorson@uni.edu')
                    // set 'from' field based on who is logged in
                    ->set('from', 'editor_in_chief@gmail.com') 
                    ->display();
            ?>

        <h4>Finalize</h4>
        <p>By clicking "submit" below, you will be submitting your decision as the Editor-in-Chief.</p>
        <input type="submit" value="submit" class="btn">
        <input type="submit" value="save & exit" class="btn">
    </div>
</div>