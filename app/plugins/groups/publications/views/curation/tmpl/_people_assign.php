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
    <h3 class="section-header"><?php echo $this->role; ?> Assignment: In progress</h3>
    <form>
        <div id="accordion">
            <h4 class="accordion-header">Suggested<span class="hz-icon icon-chevron-up"></span></h4>
            <div class="accordion-panel">
                <h6>Developmental Biology</h6>
                <p>Primary</p>
                <!-- Responsive table using flexbox: https://codepen.io/Thiru/pen/bmxvZK -->
                <div class="wrapper">
                    <div class="Rtable Rtable--5cols Rtable--collapse">
                        <div class="Rtable-row Rtable-row--head">
                            <div class="Rtable-cell name-cell column-heading">Name</div>
                            <div class="Rtable-cell assignment-cell column-heading">Assignments: current / past (year)</div>
                            <div class="Rtable-cell conflict-cell column-heading">Potential Conflicts</div>
                        </div>

                        <div class="Rtable-row odd">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor1"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Editor O'Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">2</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>

                        <div class="Rtable-row even">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor2"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Another Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">1</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end table -->
                <p>Secondary</p>
                <div class="wrapper">
                    <div class="Rtable Rtable--5cols Rtable--collapse">
                        <div class="Rtable-row Rtable-row--head">
                            <div class="Rtable-cell name-cell column-heading">Name</div>
                            <div class="Rtable-cell assignment-cell column-heading">Assignments: current / past (year)</div>
                            <div class="Rtable-cell conflict-cell column-heading">Potential Conflicts</div>
                        </div>

                        <div class="Rtable-row odd">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info"><input type="checkbox" name="editor" id="editor1">Editor O'Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">2</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h4 class="accordion-header">Biology<span class="hz-icon icon-chevron-down"></span></h4>
            <div class="accordion-panel">
                <h5>Anatomy-Physiology</h6>
                <p>Primary</p>
                <div class="wrapper">
                    <div class="Rtable Rtable--5cols Rtable--collapse">
                        <div class="Rtable-row Rtable-row--head">
                            <div class="Rtable-cell name-cell column-heading">Name</div>
                            <div class="Rtable-cell assignment-cell column-heading">Assignments: current / past (year)</div>
                            <div class="Rtable-cell conflict-cell column-heading">Potential Conflicts</div>
                        </div>

                        <div class="Rtable-row odd">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor3"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Editor O'Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">2</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>

                        <div class="Rtable-row even">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor4"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Another Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">1</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>
                <p>Secondary</p>
                <div class="wrapper">
                    <div class="Rtable Rtable--5cols Rtable--collapse">
                        <div class="Rtable-row Rtable-row--head">
                            <div class="Rtable-cell name-cell column-heading">Name</div>
                            <div class="Rtable-cell assignment-cell column-heading">Assignments: current / past (year)</div>
                            <div class="Rtable-cell conflict-cell column-heading">Potential Conflicts</div>
                        </div>

                        <div class="Rtable-row odd">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor5"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Editor O'Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">2</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>

                        <div class="Rtable-row even">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor6"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Another Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">1</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h4 class="accordion-header">Physics<span class="hz-icon icon-chevron-down"></span></h4>
            <div class="accordion-panel">
                <h6>Astronomy</h6>
                <p>Primary</p>
                <div class="wrapper">
                    <div class="Rtable Rtable--5cols Rtable--collapse">
                        <div class="Rtable-row Rtable-row--head">
                            <div class="Rtable-cell name-cell column-heading">Name</div>
                            <div class="Rtable-cell assignment-cell column-heading">Assignments: current / past (year)</div>
                            <div class="Rtable-cell conflict-cell column-heading">Potential Conflicts</div>
                        </div>

                        <div class="Rtable-row odd">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor7"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Editor O'Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">2</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>

                        <div class="Rtable-row even">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor8"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Another Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">1</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>
                <p>Secondary</p>
                <div class="wrapper">
                    <div class="Rtable Rtable--5cols Rtable--collapse">
                        <div class="Rtable-row Rtable-row--head">
                            <div class="Rtable-cell name-cell column-heading">Name</div>
                            <div class="Rtable-cell assignment-cell column-heading">Assignments: current / past (year)</div>
                            <div class="Rtable-cell conflict-cell column-heading">Potential Conflicts</div>
                        </div>

                        <div class="Rtable-row odd">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor9"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Editor O'Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">2</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>

                        <div class="Rtable-row even">
                            <div class="Rtable-cell name-cell">
                                <div class="Rtable-cell--content title-content"><input type="checkbox" name="editor" id="editor10"><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/people_info'; ?>" class="show-people-info">Another Editor</a></div>
                            </div>
                            <div class="Rtable-cell assignment-cell">
                                <div class="Rtable-cell--heading">Assignments: current / past (year)</div>
                                <div class="Rtable-cell--content access-link-content">0 / <a href="#">1</a></div>
                            </div>
                            <div class="Rtable-cell conflict-cell">
                                <div class="Rtable-cell--heading">Potential Conflicts</div>
                                <div class="Rtable-cell--content replay-link-content">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="submit" class="btn" value="submit">
        <input type="submit" class="btn" value="cancel">
    </form>
</div>