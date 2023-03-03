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
    <h3 class="section-header">Review: In process</h3>
    <div class="primary-files">
        <h4>Primary Files</h4>
        <ul>
            <li><a href="#">Title of File <span class="file-info">MERGED PDF (110)kb</span></a></li>
            <li><a href="#">Title of File <span class="file-info">JPEG (590)kb</span></a></li>
            <li><a href="#">Title of File <span class="file-info">JPEG (340)kb</span></a></li>
        </ul>
    </div>

    <div class="review-instructions">
        <h4>Instructions</h4>
        <ol>
            <li>Download the <a href="https://qubeshub.org/community/groups/coursesource/File:/uploads/authorfiles/Rubric.Lesson.Dec2022.docx">Lesson Rubric</a></li>
            <li>Fill out the rubric on your computer and save the file</li>
            <li>Once saved, please upload the review under the section <strong>Review</strong></li>
            <li>Once uploaded, your reivew will be prepopulated into the appropriate sections. <span class="instructions-underline">Please review that these are correct</span></li>
        </ol>
        <p>Note: Comments made within the review file will be disclosed to the author(s). <br>
        Any confidential comments to the Editor can be completed in the "Comments to Editor" section of this page. <br>
        Any files uploaded to the "Additional" section will be viewed by the Editor and Editor-in-Chief.</p>
    </div>
    <div class="review-upload">
        <h4>Upload Review</h4>
        <div class="upload-wrapper">
            <p>Review <span class="required">&#42;required</span></p>
            <button class="btn">Browse</button>
        </div>
        <div class="upload-wrapper">
            <p>Additional Files</p>
            <button class="btn">Browse</button>
        </div>
    </div>
    <form action="#">
        <div class="review-recommendation">
            <fieldset>
                <legend>
                    <h4>Recommendation <span class="required">&#42;required</span></h4>
                </legend>
                <label>
                    <input type="radio" name="accept" id="accept" value="accept">
                    Accept
                </label>
                <br>
                <label>
                    <input type="radio" name="minor_revisions" id="minor_revisions" value="minor_revisions">
                    Minor Revisions
                </label>
                <br>
                <label>
                    <input type="radio" name="major_revisions" id="major_revisions" value="major_revisions">
                    Major Revisions
                </label>
                <br>
                <label>
                    <input type="radio" name="reject" id="reject" value="reject">
                    Reject
                </label>
            </fieldset>
            <h4>Comments to Editor</h4>
            <p>Confidential comments to the Editor</p>
            <textarea name="editor_comments" id="editor_comments" rows="10"></textarea>
        </div>
        <div class="review-wrapper">
            <p>Note to developers: These sections are different depending on the type of rubric being used, this prototype will be using the lesson rubric</p>
            <table>
                <thead>
                    <tr>
                        <th><em>CourseSource</em> Lesson Rubric</a></th>
                        <th>Acceptable</a></th>
                        <th>Minor Modifications</a></th>
                        <th>Major Modifications</a></th>
                        <th>Comments</a></th>
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
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_1" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_2" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_3" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_4" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The learning objectives define what students should be able to do upon completion of the Lesson (i.e. measurable behaviors).</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_5" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_6" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_7" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_8" rows="5"></textarea></td>
                    </tr>
                    <tr class="rubric-header">
                        <td colspan="5">
                            <div class="right-arrow"></div>
                            <strong>Introduction</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Sufficient background information is provided to allow the reader to evaluate the usefulness of the Lesson without referring to extensive outside sources. </td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_9" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_10" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_11" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_12" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The introduction includes background scientific information along with references to similar lessons or approaches, if they exist (i.e. documentation of the author's analysis/synthesis of related published articles). These resources may also contain links to other CourseSource lessons.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_13" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_14" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_15" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_16" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The intended student population for the Lesson is described, including level and major. The Lesson is appropriate for the intended audience.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_17" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_18" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_19" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_20" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>Prerequisite student knowledge and skills required (or assumed) for the students to successfully complete the Lesson are clearly stated.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_21" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_22" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_23" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_24" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The Lesson includes references to resources that provide the instructor with additional background knowledge/reading.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_25" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_26" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_27" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_28" rows="5"></textarea></td>
                    </tr>
                    <tr class="rubric-header">
                        <td colspan="5">
                            <div class="right-arrow"></div>
                            <strong>Scientific Teaching Themes</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>The Active Learning section describes suitable active learning techniques for the Lesson. Appropriate literature is referenced.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_29" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_30" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_31" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_32" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The Assessment section briefly explains how teachers and students will assess learning. Appropriate literature is referenced.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_33" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_34" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_35" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_36" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The Inclusive Teaching section describes how the Lesson is inclusive and leverages diversity. Appropriate literature is referenced.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_37" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_38" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_39" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_40" rows="5"></textarea></td>
                    </tr>
                    <tr class="rubric-header">
                        <td colspan="5">
                            <div class="right-arrow"></div>
                            <strong>Lesson Plan</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>A table recommending the timeline for the Lesson is included and the total time suggested is reasonable. </td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_41" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_42" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_43" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_44" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The description of the Lesson is sufficient to enable the reader to replicate the activity in their class in the same way as the authors taught it.  This description will typically require instructions and a "script" of what the teacher says and does.  The description may include what discussion prompts are used, how students typically respond to questions, and information on instructional transitions.  </td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_45" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_46" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_47" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_48" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>Logistical information for teaching the Lesson is included.  This information may include details such as where materials are purchased, how materials are distributed, methods for selecting student groups etc.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_49" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_50" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_51" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_52" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>Supporting Materials are referenced throughout the Lesson Plan. </td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_53" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_54" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_55" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_56" rows="5"></textarea></td>
                    </tr>
                    <tr class="rubric-header">
                        <td colspan="5">
                            <div class="right-arrow"></div>
                            <strong>Discussion</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>The effectiveness of the Lesson is discussed. This discussion could include observations, reflections, student performance outcomes etc.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_57" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_58" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_59" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_60" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>Possible modifications and extensions that broaden the appeal or usefulness of the Lesson are provided.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_61" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_62" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_63" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_64" rows="5"></textarea></td>
                    </tr>
                    <tr class="rubric-header">
                        <td colspan="5">
                            <div class="right-arrow"></div>
                            <strong>Supporting Materials</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Adequate and well-written supporting materials (references, presentations, answer keys, student handouts, etc.) are provided to enable the reader to reproduce the lesson in their own class.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_65" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_66" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_67" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_68" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The supporting materials contain original work from the author, or if it is from another source proper permissions and attribution are noted.  </td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_69" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_70" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_71" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_72" rows="5"></textarea></td>
                    </tr>
                    <tr class="rubric-header">
                        <td colspan="5">
                            <div class="right-arrow"></div>
                            <strong>General</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>All sections of the Lesson (ex. introduction, lesson plan, supporting materials) include relevant and accurate scientific content.  </td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_73" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_74" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_75" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_76" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>The title and abstract clearly and succinctly expresses the content of the Lesson.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_77" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_78" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_79" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_80" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <td>AThe grammar and writing style are of high quality with no significant distractions, such as spelling or grammatical errors.</td>
                        <td class="checkbox"><input type="checkbox" name="acceptable" id="item_81" value="acceptable"></td>
                        <td class="checkbox"><input type="checkbox" name="minor_revisions" id="item_82" value="minor_revisions"></td>
                        <td class="checkbox"><input type="checkbox" name="major_revisions" id="item_83" value="major_revisions"></td>
                        <td><textarea name="comments" id="item_84" rows="5"></textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <input type="submit" value="submit" class="btn">
        <input type="submit" value="save and close" class="btn">
    </form>
</div>