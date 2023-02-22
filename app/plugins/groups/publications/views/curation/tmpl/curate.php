<?php 
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2023 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$this->css('curation')
    ->css('jquery.fancybox.css', 'system')
    ->js('curation'); 

?>

<div class="article-curation-header">
    <img src="https://qubeshub.org/publications/3631/3869?media=Image:master" alt="Article Image" class="article-curation-img">
    <div class="basic-details">
        <h2>Title of Something Neat</h2>
        <p class="corresponding-author">Author(s): Author Authorson</p>
        <p class="article-id">ID: 122</p>
        <div class="curation-version">Version: 1</div>
    </div>
    <div class="submission-details">
        <p class="submittion-date">Submitted: 18 Jan 2023</p>
        <p class="article-status">status <br>
        Pending Quality Check
        </p>
    </div>
</div>

<div class="article-curation-body">
    <div class="article-curation-menu">
        <div class="pending-actions">
            <h5>Pending</h5>
            <a href="#" class="pending-status">Quality Check</a>
        </div>
        <div class="static-actions">
            <a href="#">History</a>
            <a href="#">Emails</a>
            <a href="#">View Draft</a>
        </div>
        <div>
            <h5>Prototype navigation only</h5>
            <ul>
                <li><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=curate'; ?>">Landing Curate Page</a></li>
                <li><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=qc'; ?>">QC In Progress</a></li>
                <li><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=editor_assign'; ?>">Editor Assign</a></li>
                <li><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=editor_assign_email'; ?>">Editor Assign Email</a></li>
                <li><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=editor_assign_pending'; ?>">Pending Editor Response</a></li>
                <li><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=reviewer_assign'; ?>">Reviewer Assign</a></li>
                <li><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=reviewer_assign_email'; ?>">Reviewer Assign Email</a></li>
                <li><a href="<?php echo '/community/groups/' . $this->group->get('cn') . '/publications/curation?stage=reviewer_assign_pending'; ?>">Pending Reviewer Response</a></li>
            </ul>
        </div>
    </div>

    <div class="article-curation-panel">
        <?php
        $stage = Request::getString('stage', '');

        switch ($stage) {
            case 'curate':
                $view = $this->view('_curate', 'curation')
                ->set('group', $this->group)
                ->set('status', 'pending quality check')
                ->set('actionable', 'start quality check')
                ->display();
                break;
            case 'qc':
                $view = $this->view('_qc', 'curation')
                ->set('group', $this->group)
                ->set('role', 'quality check')
                ->display();
                break;
            case 'editor_assign':
                $view = $this->view('_people_assign', 'curation')
                ->set('group', $this->group)
                ->set('role', 'editor')
                ->display();
                break;
            case 'editor_assign_email':
                $view = $this->view('_people_assign_email', 'curation')
                ->set('group', $this->group)
                ->set('role', 'editor')
                ->display();
                break;
            case 'editor_assign_pending':
                $view = $this->view('_people_assign_pending', 'curation')
                ->set('group', $this->group)
                ->set('role', 'editor')
                ->display();
                break;
            case 'reviewer_assign':
                $view = $this->view('_people_assign', 'curation')
                ->set('group', $this->group)
                ->set('role', 'reviewer')
                ->display();
                break;
            case 'reviewer_assign_email':
                $view = $this->view('_people_assign_email', 'curation')
                ->set('group', $this->group)
                ->set('role', 'reviewer')
                ->display();
                break;
            case 'reviewer_assign_pending':
                $view = $this->view('_people_assign_pending', 'curation')
                ->set('group', $this->group)
                ->set('role', 'reviewer')
                ->display();
                break;
        }
        ?>
    </div>
</div>

<div class="article-curation-summary">
    <h3>Summary</h3>
    <div class="top-relavence">
        <div class="top-info">
            <p><span class="strong">Corresponding Author(s)</span>: Author Authorson <span class="strong"><- Maybe have this as a link for quick email correspondance?</span></p>
            <p><span class="strong">Editor-in-Chief</span>: Jenny Knight <span class="strong"><- This assigned dependent on courses selected? What if both bio and physics are selected?</span></p>
            <p><span class="strong">Editor</span>: not assigned <span class="strong"><- needs to be dynamically updated</span></p>
            <p><span class="strong">Reviewers</span>: not assigned <span class="strong"><- needs to be dynamically updated</span></p>
            <p><span class="strong">Original Preferred Reviewers</span>: N/A</p>
            <p><span class="strong">Original Non-Preferred Reviewers</span>: N/A</p>
            <p><span class="strong">Course(s)</span>: Developmental Biology</p>
            <p><span class="strong">Key Concepts in Biology and/or Physics</span>: keywords, keywords, keywords</p>
        </div>
        <div class="top-files">
            <p><span class="strong">Files</span>: </p>
            <ol>
                <li><a href="#">Title of File</a><span class="file-info">MERGED PDF (110kb)</span></li>
                <li><a href="#">Title of File</a><span class="file-info">JPEG (590kb)</span></li>
                <li><a href="#">Title of File</a><span class="file-info">JPEG (340kb)</span></li>
            </ol>
        </div>
    </div>
    <div class="bottom-content">
        <p><span class="strong">Article Type</span>: Lesson</p>
        <p><span class="strong">Abstract</span>: Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Nullam id dolor id nibh ultricies vehicula ut id elit. Sed posuere consectetur est at lobortis. Nullam quis risus eget urna mollis ornare vel eu leo.</p>
        <p><span class="strong">Course Level</span>: Introductory</p>
        <p><span class="strong">Class Type</span>: Lecture</p>
        <p><span class="strong">Audience</span>: Life Science Major</p>
        <p><span class="strong">Class Size</span>: 1-50</p>
        <p><span class="strong">Lesson Length</span>: One year</p>
        <p><span class="strong">Key Scientific Process Skills</span>: Formulating hypothesis</p>
        <p><span class="strong">Pedigogical Approaches</span>: Work</p>
        <p><span class="strong">Blooms Cognitive Level (based on objectives & assessments)</span>: Foundational: factual knowledge & comprehension</p>
        <p><span class="strong">Principles of how people learn</span>: Motivates student to learn material</p>
        <p><span class="strong">Vision and Change Core Concepts</span>: Evolution</p>
        <p><span class="strong">Vision and Change Core Competencies (Biology only; N/A for any Physics lessons)</span>: Ability to tap into the interdisciplinary nature of science</p>
        <p><span class="strong">Assessment Type</span>: Assessment of student groups/teams</p>
        <p><span class="strong">Conflict of Interest</span>: No, there is no duality of interest that I should disclose, having read the statement</p>
        <p><span class="strong">Contributing Author Notification</span>: Yes</p>
        <p><span class="strong">Copyright Release</span>: <span class="strong">Do we need this?</span></p>
    </div>
</div>