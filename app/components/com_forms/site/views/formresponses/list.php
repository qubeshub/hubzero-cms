<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formResponsesList') // This one can probably go, or at least pull feed into formsAdminResponses
	 ->css('formsAdminResponses');

$this->js('notify')
	 ->js('formsAdminResponsesListActions')
	 ->js('formsAdminResponsesListCheckbox')
	 ->js('formsAdminResponsesListSorting')
	 ->js('formsAdminResponsesList');

$feedItems = $this->feedItems;
$responses = $this->responses;
$responsesCount = $responses->count();
$responsesListUrl = $this->listUrl;
$form = $this->form;
$filter = $this->filter;
$formId = ($this->form ? $this->form->get('id') : 0);
$formName = ($this->form ? $this->form->get('name') : 'All');
$sortingCriteria = $this->sortingCriteria;

$breadcrumbs = [
	'Forms' => ['formListUrl'],
	$formName => ['formsDisplayUrl', [$formId]],
	'My Responses' => ['usersResponsesUrl']
];
if (!$formId) {
	unset($breadcrumbs['All']);
}
$this->view('_forms_breadcrumbs', 'shared')
	->set('breadcrumbs', $breadcrumbs)
	->set('page', "Responses")
	->display();
?>

<section class="main section">
	<div class="grid">

		<div class="col span7">
			<?php
				$this->view('_response_list_area')
					->set('responses', $responses)
					->set('form', $form)
					->set('filter', $filter)
					->set('sortingAction', $responsesListUrl)
					->set('sortingCriteria', $sortingCriteria)
					->display();

				$this->view('_pagination', 'shared')
					->set('minDisplayLimit', 4)
					->set('pagination', $responses->pagination)
					->set('paginationUrl', $responsesListUrl)
					->set('recordsCount', $responsesCount)
					->display();
				?>
		</div>

		<div class="col span5 omega">
			<div class="feed-comments">
				<?php
					$this->view('_feed', 'shared')
						->set('feedItems', $feedItems)
						->set('itemView', '_responses_feed_item')
						->set('noticeView', '_responses_feed_empty_notice')
						->set('subviewsSource', 'formresponses')
						->display();
				?>
			</div>
		</div>

	</div>
</section>
