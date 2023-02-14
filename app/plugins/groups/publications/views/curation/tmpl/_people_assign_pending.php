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
    <h3 class="section-header"><?php echo $this->role; ?> Assignment: Pending response</h3>
    <a href="/community/groups/coursesource/publications/curation?stage=curate&status=editor_assign" class="actionable">Assign new <?php echo $this->role; ?></a>
    <div id="accordion">
        <h4 class="accordion-header">Accepted<span class="count">(0)</span><span class="hz-icon icon-chevron-up"></span></h4>
        <div class="accordion-panel">
            <!-- Responsive table using flexbox: https://codepen.io/Thiru/pen/bmxvZK -->
            <div class="wrapper">
                <div class="Rtable Rtable--5cols Rtable--collapse">
                    <div class="Rtable-row Rtable-row--head">
                        <div class="Rtable-cell name-cell column-heading">Name</div>
                        <div class="Rtable-cell status-cell column-heading">Status</div>
                        <div class="Rtable-cell contacts-cell column-heading">Number of contacts</div>
                        <div class="Rtable-cell history-cell column-heading">Last correspondance</div>
                    </div>

                    <div class="Rtable-row odd">
                        <div class="Rtable-cell name-cell">
                            <div class="Rtable-cell--content title-content">None</div>
                        </div>
                        <div class="Rtable-cell status-cell">
                            <div class="Rtable-cell--heading">Status</div>
                            <div class="Rtable-cell--content access-link-content"></div>
                        </div>
                        <div class="Rtable-cell contacts-cell">
                            <div class="Rtable-cell--heading">Last correspondance</div>
                            <div class="Rtable-cell--content replay-link-content"></div>
                        </div>
                        <div class="Rtable-cell correspondance-cell">
                            <div class="Rtable-cell--heading">Last correspondance</div>
                            <div class="Rtable-cell--content corresepondance-content"></div>
                        </div>
                    </div>
                </div>
            </div> <!-- end table -->
        </div>
        <h4 class="accordion-header">Pending<span class="count">(1)</span><span class="hz-icon icon-chevron-down"></span></h4>
        <div class="accordion-panel">
            <div class="wrapper">
                <div class="Rtable Rtable--5cols Rtable--collapse">
                    <div class="Rtable-row Rtable-row--head">
                        <div class="Rtable-cell name-cell column-heading">Name</div>
                        <div class="Rtable-cell status-cell column-heading">Status</div>
                        <div class="Rtable-cell contacts-cell column-heading">Number of contacts</div>
                        <div class="Rtable-cell history-cell column-heading">Last correspondance</div>
                    </div>

                    <div class="Rtable-row odd">
                        <div class="Rtable-cell name-cell">
                            <div class="Rtable-cell--content title-content"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Editor O'Editor</a></div>
                        </div>
                        <div class="Rtable-cell status-cell">
                            <div class="Rtable-cell--heading">Status</div>
                            <div class="Rtable-cell--content status-content">Pending response</div>
                        </div>
                        <div class="Rtable-cell contacts-cell">
                            <div class="Rtable-cell--heading">Number of contacts</div>
                            <div class="Rtable-cell--content contacts-content">1</div>
                        </div>
                        <div class="Rtable-cell correspondance-cell">
                            <div class="Rtable-cell--heading">Last correspondance</div>
                            <div class="Rtable-cell--content corresepondance-content">23 Jan. 2023</div>
                        </div>
                    </div>
                    <div class="Rtable-row people-assigned-response">
                        <button class="resend btn">Resend Request</button>
                        <button class="accept btn">Accept</button>
                        <button class="decline btn">Decline</button>
                        <button class="no-reponse btn">No response</button>
                    </div>
                </div>
            </div>
        </div>
        <h4 class="accordion-header">Declined<span class="count">(1)</span><span class="hz-icon icon-chevron-down"></span></h4>
        <div class="accordion-panel">
            <div class="wrapper">
                <div class="people-wrapper">
                    <p>Name: Busy Person</p>
                    <p>Reason: Too busy</p>
                    <p>Comments: Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec sed odio dui.</p>
                    <p>Reviewer Suggestions: </p>
                </div>
            </div>
        </div>
    </div>
</div>