/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

FORMS = HUB.FORMS

$(document).ready(() => {

	FORMS.$itemActionButtons = FORMS.getItemActionButtons()
	FORMS.$masterCheckbox = FORMS.getMasterCheckbox()
	FORMS.$itemsList = FORMS.getItemsList()

	FORMS.registerCheckboxHandlers(FORMS.$masterCheckbox)
	FORMS.registerListActionHandlers(FORMS.$itemActionButtons)
	FORMS.registerSortHandlers(FORMS.$itemsList)

	$(document).on('click', '.fr-clipboard', (e) => {
		const $target = $(e.target).closest('tr');
		navigator.clipboard.writeText($target.data('share-link'));

		FORMS.Notify['success']('Copied to clipboard: ' + $target.data('share-link'));
	});
})
