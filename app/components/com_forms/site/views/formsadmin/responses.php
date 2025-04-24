<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formsAdminResponses')
	 ->css('surveyjs/survey-core.min.css')
	 ->css('tabulator/tabulator.min.css')
	 ->css('surveyjs/survey.analytics.tabulator.min.css');

$this->js('notify')
	->js('formsAdminItemsListActions')
	->js('formsAdminItemsListCheckbox')
	->js('formsAdminItemsListSorting')
	->js('formsAdminItemsList')
	->js('jspdf/jspdf.umd.min.js')
	->js('jspdf/jspdf.plugin.autotable.min.js')
	->js('sheetjs/xlsx.full.min.js')
	->js('tabulator/tabulator.min.js')
	->js('surveyjs/survey.core.min.js')
	->js('surveyjs/survey-js-ui.min.js')
	->js('surveyjs/survey.analytics.tabulator.min.js')
	->js('formSetup.js')
	->js('formLibrary.js');

$responsesEmailUrl = $this->responsesEmailUrl;
$responsesTagsUrl = $this->responsesTagsUrl;
$responsesDeleteUrl = $this->responsesDeleteUrl;
$responsesUnsubmitUrl = $this->responsesUnsubmitUrl;
$form = $this->form;
$formId = $form->get('id');
$formName = $form->get('name');
$responses = $this->responses;
$responseListUrl = $this->responseListUrl;
$sortingCriteria = $this->sortingCriteria;
$responsesCount = $responses->count();
$breadcrumbs = [
	 $formName => ['formsDisplayUrl', [$formId]],
	'Manage' => ['formsEditUrl', [$formId]],
	'Responses' => ['formsResponseList', [$formId]]
];

$this->view('_forms_breadcrumbs', 'shared')
	->set('breadcrumbs', $breadcrumbs)
	->set('page', "$formName Responses")
	->display();
?>

<section class="main section">
	<div class="grid">
		
		<div class="row">
			<div class="col span12 nav omega">
				<?php
					$this->view('_form_edit_nav', 'shared')
						->set('current', 'Responses')
						->set('formId', $formId)
						->display();
				?>
			</div>
		</div>

		<div class="row">
			<div class="col span12 omega list-actions">
				<?php if ($responsesCount > 0): ?>
					<span id="email-respondents-button" class="list-action">
						<?php
							$this->view('_email_respondents_form')
								->set('action', $responsesEmailUrl)
								->set('formId', $formId)
								->display();
						?>
					</span>

					<!-- <span id="tag-responses-button" class="list-action">
						<?php
							$this->view('_tag_responses_form')
								->set('action', $responsesTagsUrl)
								->set('formId', $formId)
								->display();
						?>
					</span> -->

					<span id="delete-responses-button" class="list-action">
						<?php
							$this->view('_delete_responses_form')
								->set('action', $responsesDeleteUrl)
								->set('returnUrl', $responseListUrl)
								->set('formId', $formId)
								->display();
						?>
					</span>

					<?php if ($form->get('responses_locked')): ?>
					<span id="unsubmit-responses-button" class="list-action">
						<?php
							$this->view('_unsubmit_responses_form')
								->set('action', $responsesUnsubmitUrl)
								->set('returnUrl', $responseListUrl)
								->set('formId', $formId)
								->display();
						?>
					</span>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="row">
			<div class="col span12 omega">
				<?php
					$this->view('_response_list_area')
						->set('responses', $responses)
						->set('sortingAction', $responseListUrl)
						->set('sortingCriteria', $sortingCriteria)
						->set('formId', $formId)
						->display();

					$this->view('_pagination', 'shared')
						->set('minDisplayLimit', 4)
						->set('pagination', $responses->pagination)
						->set('paginationUrl', $responseListUrl)
						->set('recordsCount', $responsesCount)
						->display();
				?>

				<div id="surveyjsForm">
					<strong>Note</strong>: Click the <span><svg style="width:15px; height:15px;"><use xlink:href="#sa-svg-detail"></use></svg></span> icon in the first column of each row to show/expand response metadata. While the response metadata is expanded, click the "Show as Column" button to have the metadata column appear in the table and exported PDF, Excel, or CSV files.
					<div id="responsesTable"></div>
					<input type="hidden" name="form_id" value="<?php echo $formId; ?>">
				</div>
			</div>

		</div>
	</div>
</section>
