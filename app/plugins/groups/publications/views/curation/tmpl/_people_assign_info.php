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

<div id="modal-wrapper">
    <div class="modal-menu">
        <ul>
            <li><a href="#people-assignments" class="modal-menu-item">Assignments</a></li>
            <li><a href="#people-notes" class="modal-menu-item">Notes</a></li>
        </ul>
    </div>

    <div class="modal-contents">
        <div id="people-summary">
            <h6>Editor O'Editor</h6>
            <div class="primary-info">
                <p>University of Awesomeness</p>
                <p>eoeditor@uni.edu</p>
                <p>Primary Expertise: Developmental Biology</p>
                <p>Additional Expertise: </p>
            </div>
            <h6>Potential Conflicts</h6>
            <p>N/A</p>
        </div>
        <div id="people-assignments">
            <h6>Assignments</h6>
            <div class="asignment-summary">
                <p>Current: 0</p>
                <p>Past Completed: 1</p>
                <p>Other Stats:</p>
                <ul>
                    <li>Number of Contacts: 2</li>
                    <li>Number of Responses: 1</li>
                    <li>Number of Rejects/Non Response: 1</li>
                </ul>
            </div>
            <div class="assignment-history">
                <p>Current</p>
                <div class="wrapper">
                    <div class="Rtable Rtable--5cols Rtable--collapse">
                        <div class="Rtable-row Rtable-row--head">
                            <div class="Rtable-cell title-cell column-heading">Title</div>
                            <div class="Rtable-cell completed-cell column-heading">Completed</div>
                        </div>

                        <div class="Rtable-row odd">
                            <div class="Rtable-cell title-cell">
                                <div class="Rtable-cell--content title-content">None currently assigned</div>
                            </div>
                        </div>
                    </div>
                </div>
                <p>Past</p>
                <div class="wrapper">
                    <div class="Rtable Rtable--5cols Rtable--collapse">
                        <div class="Rtable-row Rtable-row--head">
                            <div class="Rtable-cell title-cell column-heading">Title</div>
                            <div class="Rtable-cell completed-cell column-heading">Completed</div>
                        </div>

                        <div class="Rtable-row odd">
                            <div class="Rtable-cell title-cell">
                                <div class="Rtable-cell--content title-content"><a href="#">Title of an Article</a></div>
                            </div>
                            <div class="Rtable-cell completed-cell">
                                <div class="Rtable-cell--heading">Completed</div>
                                <div class="Rtable-cell--content access-link-content">09 Sept 2021</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="people-notes">
            <h6>Notes</h6>
            <a href="#"><span class="hz-icon icon-plus"></span>Add note</a>
        </div>
    </div>
</div>
