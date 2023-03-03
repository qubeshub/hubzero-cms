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
        <h4>Reviewer Evaluations</h4>
        <h5>Reviewer: Reviewer Reviewerton</h5>
        <p>Recommendation: Minor Revisions</p>
        <p>Comments to Editor (<strong><em>confidential</em></strong>)</p>
        <div class="reviewer-comments">
            <p>Vestibulum id ligula porta felis euismod semper. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Curabitur blandit tempus porttitor. Curabitur blandit tempus porttitor. Donec sed odio dui. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.</p>
            <p>Aenean lacinia bibendum nulla sed consectetur. Vestibulum id ligula porta felis euismod semper. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Aenean lacinia bibendum nulla sed consectetur. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
            <p>Maecenas sed diam eget risus varius blandit sit amet non magna. Curabitur blandit tempus porttitor. Donec sed odio dui. Maecenas faucibus mollis interdum. Maecenas faucibus mollis interdum. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
        </div>

        <h5>Reviewer: Views Reviews</h5>
        <p>Recommendation: Minor Revisions</p>
        <p>Comments to Editor (<strong><em>confidential</em></strong>)</p>
        <div class="reviewer-comments">
            <p>Vestibulum id ligula porta felis euismod semper. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Curabitur blandit tempus porttitor. Curabitur blandit tempus porttitor. Donec sed odio dui. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.</p>
            <p>Aenean lacinia bibendum nulla sed consectetur. Vestibulum id ligula porta felis euismod semper. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Aenean lacinia bibendum nulla sed consectetur. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
            <p>Maecenas sed diam eget risus varius blandit sit amet non magna. Curabitur blandit tempus porttitor. Donec sed odio dui. Maecenas faucibus mollis interdum. Maecenas faucibus mollis interdum. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
        </div>
    </div>

    <form action="#">
        <div class="reviewer-rubric">
            <div class="reviewer-rubric-header">
                <div class="reviewer-info">
                    <h4>Reviewer 1 Comments</h4>
                    <p>Review file: <a href="#">Review_File.doc</a></p>
                    <p>Additional files:</p>
                </div>

                <div class="comment-action">
                    <fieldset>
                        <legend>Include all comments to author?</legend>
                        <label>
                            <input type="radio" name="send_all" id="send_all" checked>
                            <span>Send all</span>
                        </label>
                        <label>
                            <input type="radio" name="do_not_send" id="do_not_send">
                            <span>Do not send</span>
                        </label>
                        <label>
                            <input type="radio" name="custom" id="custom">
                            <span>Custom</span>
                        </label>
                    </fieldset>
                </div>
            </div>
            <div class="review-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th colspan="1"></th>
                            <th colspan="2">Comments</th>
                            <th colspan="2">Send to Author?</th>
                        </tr>
                        <tr>
                            <th><em>CourseSource</em> Lesson Rubric</th>
                            <th>Decision</th>
                            <th>Comments</th>
                            <th>Yes</th>
                            <th>No</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Lesson Context</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>The learning goals are clearly stated, relevant to the Lesson, and, if applicable, authors have listed professional society generated goals that align with their Lesson.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item1" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item1"></td>
                        </tr>
                        <tr>
                            <td>The learning objectives define what students should be able to do upon completion of the Lesson (i.e. measurable behaviors).</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item2" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item2"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Introduction</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>Sufficient background information is provided to allow the reader to evaluate the usefulness of the Lesson without referring to extensive outside sources.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item3" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item3"></td>
                        </tr>
                        <tr>
                            <td>The introduction includes background scientific information along with references to similar lessons or approaches, if they exist (i.e. documentation of the author's analysis/synthesis of related published articles). These resources may also contain links to other CourseSource lessons.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item4" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item4"></td>
                        </tr>
                        <tr>
                            <td>The intended student population for the Lesson is described, including level and major. The Lesson is appropriate for the intended audience.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item5" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item5"></td>
                        </tr>
                        <tr>
                            <td>Prerequisite student knowledge and skills required (or assumed) for the students to successfully complete the Lesson are clearly stated.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item6" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item6"></td>
                        </tr>
                        <tr>
                            <td>The Lesson includes references to resources that provide the instructor with additional background knowledge/reading.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item7" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item7"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Scientific Teaching Themes</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>The Active Learning section describes suitable active learning techniques for the Lesson. Appropriate literature is referenced.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item8" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item8"></td>
                        </tr>
                        <tr>
                            <td>The Assessment section briefly explains how teachers and students will assess learning. Appropriate literature is referenced.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item9" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item9"></td>
                        </tr>
                        <tr>
                            <td>The Inclusive Teaching section describes how the Lesson is inclusive and leverages diversity. Appropriate literature is referenced.</td>
                            <td>Major Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item10" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item10"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Lesson Plan</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>A table recommending the timeline for the Lesson is included and the total time suggested is reasonable.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item11" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item11"></td>
                        </tr>
                        <tr>
                            <td>The description of the Lesson is sufficient to enable the reader to replicate the activity in their class in the same way as the authors taught it.  This description will typically require instructions and a "script" of what the teacher says and does.  The description may include what discussion prompts are used, how students typically respond to questions, and information on instructional transitions.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item12" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item12"></td>
                        </tr>
                        <tr>
                            <td>Logistical information for teaching the Lesson is included.  This information may include details such as where materials are purchased, how materials are distributed, methods for selecting student groups etc.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item13" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item13"></td>
                        </tr>
                        <tr>
                            <td>Supporting Materials are referenced throughout the Lesson Plan.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item14" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item14"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Discussion</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>The effectiveness of the Lesson is discussed. This discussion could include observations, reflections, student performance outcomes etc.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item15" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item15"></td>
                        </tr>
                        <tr>
                            <td>Possible modifications and extensions that broaden the appeal or usefulness of the Lesson are provided.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item16" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item16"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Supporting Materials</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>Adequate and well-written supporting materials (references, presentations, answer keys, student handouts, etc.) are provided to enable the reader to reproduce the lesson in their own class.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item17" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item17"></td>
                        </tr>
                        <tr>
                            <td>The supporting materials contain original work from the author, or if it is from another source proper permissions and attribution are noted.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item18" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item18"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>General</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>All sections of the Lesson (ex. introduction, lesson plan, supporting materials) include relevant and accurate scientific content.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item19" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item19"></td>
                        </tr>
                        <tr>
                            <td>The title and abstract clearly and succinctly expresses the content of the Lesson.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item20" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item20"></td>
                        </tr>
                        <tr>
                            <td>The grammar and writing style are of high quality with no significant distractions, such as spelling or grammatical errors.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item21" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item21"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="reviewer-rubric">
            <div class="reviewer-rubric-header">
                <div class="reviewer-info">
                    <h4>Reviewer 2 Comments</h4>
                    <p>Review file: <a href="#">Review_File.doc</a></p>
                    <p>Additional files:</p>
                </div>
            </div>
            <div class="review-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th colspan="1"></th>
                            <th colspan="2">Comments</th>
                            <th colspan="2">Send to Author?</th>
                        </tr>
                        <tr>
                            <th><em>CourseSource</em> Lesson Rubric</th>
                            <th>Decision</th>
                            <th>Comments</th>
                            <th>Yes</th>
                            <th>No</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Lesson Context</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>The learning goals are clearly stated, relevant to the Lesson, and, if applicable, authors have listed professional society generated goals that align with their Lesson.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item1" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item1"></td>
                        </tr>
                        <tr>
                            <td>The learning objectives define what students should be able to do upon completion of the Lesson (i.e. measurable behaviors).</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item2" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item2"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Introduction</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>Sufficient background information is provided to allow the reader to evaluate the usefulness of the Lesson without referring to extensive outside sources.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item3" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item3"></td>
                        </tr>
                        <tr>
                            <td>The introduction includes background scientific information along with references to similar lessons or approaches, if they exist (i.e. documentation of the author's analysis/synthesis of related published articles). These resources may also contain links to other CourseSource lessons.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item4" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item4"></td>
                        </tr>
                        <tr>
                            <td>The intended student population for the Lesson is described, including level and major. The Lesson is appropriate for the intended audience.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item5" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item5"></td>
                        </tr>
                        <tr>
                            <td>Prerequisite student knowledge and skills required (or assumed) for the students to successfully complete the Lesson are clearly stated.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item6" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item6"></td>
                        </tr>
                        <tr>
                            <td>The Lesson includes references to resources that provide the instructor with additional background knowledge/reading.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item7" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item7"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Scientific Teaching Themes</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>The Active Learning section describes suitable active learning techniques for the Lesson. Appropriate literature is referenced.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item8" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item8"></td>
                        </tr>
                        <tr>
                            <td>The Assessment section briefly explains how teachers and students will assess learning. Appropriate literature is referenced.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item9" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item9"></td>
                        </tr>
                        <tr>
                            <td>The Inclusive Teaching section describes how the Lesson is inclusive and leverages diversity. Appropriate literature is referenced.</td>
                            <td>Major Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item10" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item10"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Lesson Plan</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>A table recommending the timeline for the Lesson is included and the total time suggested is reasonable.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item11" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item11"></td>
                        </tr>
                        <tr>
                            <td>The description of the Lesson is sufficient to enable the reader to replicate the activity in their class in the same way as the authors taught it.  This description will typically require instructions and a "script" of what the teacher says and does.  The description may include what discussion prompts are used, how students typically respond to questions, and information on instructional transitions.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item12" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item12"></td>
                        </tr>
                        <tr>
                            <td>Logistical information for teaching the Lesson is included.  This information may include details such as where materials are purchased, how materials are distributed, methods for selecting student groups etc.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item13" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item13"></td>
                        </tr>
                        <tr>
                            <td>Supporting Materials are referenced throughout the Lesson Plan.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item14" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item14"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Discussion</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>The effectiveness of the Lesson is discussed. This discussion could include observations, reflections, student performance outcomes etc.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item15" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item15"></td>
                        </tr>
                        <tr>
                            <td>Possible modifications and extensions that broaden the appeal or usefulness of the Lesson are provided.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item16" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item16"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>Supporting Materials</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>Adequate and well-written supporting materials (references, presentations, answer keys, student handouts, etc.) are provided to enable the reader to reproduce the lesson in their own class.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item17" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item17"></td>
                        </tr>
                        <tr>
                            <td>The supporting materials contain original work from the author, or if it is from another source proper permissions and attribution are noted.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item18" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item18"></td>
                        </tr>
                        <tr class="rubric-header">
                            <td colspan="5">
                                <div class="right-arrow"></div>
                                <strong>General</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>All sections of the Lesson (ex. introduction, lesson plan, supporting materials) include relevant and accurate scientific content.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item19" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item19"></td>
                        </tr>
                        <tr>
                            <td>The title and abstract clearly and succinctly expresses the content of the Lesson.</td>
                            <td>Acceptable</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item20" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item20"></td>
                        </tr>
                        <tr>
                            <td>The grammar and writing style are of high quality with no significant distractions, such as spelling or grammatical errors.</td>
                            <td>Minor Revisions</td>
                            <td><textarea name="" id="" cols="30" rows="5">Vestibulum id ligula porta felis euismod semper. Maecenas faucibus mollis interdum.</textarea></td>
                            <td><input type="checkbox" name="send" id="" value="item21" checked></td>
                            <td><input type="checkbox" name="no_not_send" id="" value="item21"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="decision-wrapper">
            <p>Editor: Editor O'Editor</p>
            <p>Date due: 08 March 2023</p>
            <p><a href="https://qubeshub.org/community/groups/coursesource/File:/uploads/authorfiles/Rubric.Lesson.Dec2022.docx">Review Rubric for Lesson Articles</a></p>
            <div class="recommendation">
                <fieldset>
                    <legend><h4>Recommendation <span class="required">*required</span></h4></legend>
                    <label>
                        <input type="radio" name="decision" id="accept" checked>
                        Accept
                    </label>
                    <label>
                        <input type="radio" name="decision" id="minor_revisions">
                        Minor Revisions
                    </label>
                    <label>
                        <input type="radio" name="decision" id="major_revisions">
                        Major Revisions
                    </label>
                    <label>
                        <input type="radio" name="decision" id="reject">
                        Reject
                    </label>
                </fieldset>
            </div>
            <h4>Comments to Editor-in-Chief</h4>
            <p>Confidential comments to the Editor</p>
            <p>Please eleborate on your evaluation of the strengths and weaknesses of the manuscript, paying close attention to the desired items and attributes discussed in the Lesson Review Rubric.</p>
            <textarea name="editor_comments" id="editor_in_chief_comments" rows="10"></textarea>
            <h4>Comments to the Author <span class="required">*required</span></h4>
            <p>Please include all appropriate suggestions for revision from reviewers, adding any that you feel are missing. Please also include any general comments either from the reviewers or your own careful reading of the manuscript. Do not indicate any ethical issues in Comments to the Author; those should go in the Comments to the Editor-in-Chief box. Also, do not reveal your decision recommendation to the author.</p>
            <textarea name="editor_comments" id="editor_in_chief_comments" rows="10"></textarea>

            <h4>Email to the author</h4>
            <?php 
                $view = $this->view('_email_messaging', 'curation')
                    ->set('group', $this->group)
                    ->set('role', 'editor_decision')
                    ->set('corresponding_author', 'authorauthorson@uni.edu')
                    // set 'from' field based on who is logged in
                    ->set('from', 'coursesourceeditor@gmail.com') 
                    ->display();
            ?>
        </div>
        <h4>Finalize</h4>
        <p>By clicking "submit" below, you will be submitting your decision to the Editor-in-Chief.</p>
        <input type="submit" value="submit" class="btn">
        <input type="submit" value="save & exit" class="btn">
    </form>
</div>