<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css('formResponsesList') // This one can probably go, or at least pull feed into formsAdminResponses
	 ->css('formsAdminResponses')
     ->css('formList');

$this->js('notify')
	 ->js('formsAdminItemsListActions')
	 ->js('formsAdminItemsListCheckbox')
	 ->js('formsAdminItemsListSorting')
	 ->js('formsAdminItemsList');

$forms = $this->forms;
$formsCount = $forms->count();
$formsListUrl = $this->listUrl;
$filter = $this->filter;
$sortingCriteria = $this->sortingCriteria;

$breadcrumbs = [
	'Forms' => ['formListUrl']
];
$this->view('_forms_breadcrumbs', 'shared')
	->set('breadcrumbs', $breadcrumbs)
	->set('page', "Forms")
	->display();
?>

<section class="main section">
    <?php
		$this->view('_landing_header')
			->display();
	?>

	<div class="grid">

		<div class="col span12 omega">
			<?php
            $this->view('_form_list_area')
                ->set('forms', $forms)
                ->set('filter', $filter)
                ->set('sortingAction', $formsListUrl)
                ->set('sortingCriteria', $sortingCriteria)
                ->display();
                
				$this->view('_pagination', 'shared')
					->set('minDisplayLimit', 4)
					->set('pagination', $forms->pagination)
					->set('paginationUrl', $formsListUrl)
					->set('recordsCount', $formsCount)
					->display();
				?>
		</div>
	</div>
</section>
