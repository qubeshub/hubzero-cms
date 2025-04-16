/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

FORMS = HUB.FORMS

FORMS.listActionsClass = 'list-action'
FORMS.itemIdFieldsName = 'item_ids[]'

FORMS.getItemActionButtons = () => {
	return $(`.${FORMS.listActionsClass}`)
}

FORMS.registerListActionHandlers = ($listActions) => {
	$listActions.on('click', FORMS.submitActionForm)
}

FORMS.submitActionForm = (e) => {
	const $form = FORMS.getActionForm(e.target)

	if (FORMS.itemsSelected()) {
		FORMS.populateActionForm($form)
		$form.submit()
	} else {
		FORMS.adviseUserToSelectResponses()
	}
}

FORMS.getActionForm = (eventTarget) => {
	const $actionSpan = $(eventTarget).closest(`.${FORMS.listActionsClass}`)

	return $actionSpan.find('form')
}

FORMS.itemsSelected = () => {
	return FORMS.getSelectedItemsIds().length > 0
}

FORMS.adviseUserToSelectResponses = () => {
	FORMS.Notify['warn']('Select at least one item from the list below')
}

FORMS.populateActionForm = ($form) => {
	const selectedItemsIds = FORMS.getSelectedItemsIds()

	FORMS.addSelectedItemsIds($form, selectedItemsIds)
}

FORMS.getSelectedItemsIds = () => {
	const $selectedResponsesCheckboxes = FORMS.getSelectedItemsCheckboxes()
	const selectedItemsIds = []

	$selectedResponsesCheckboxes.each((i, checkbox) => {
		selectedItemsIds.push($(checkbox).val())
	})

	return selectedItemsIds
}

FORMS.getSelectedItemsCheckboxes = () => {
	const itemCheckboxes = $(`input[name="${FORMS.itemIdFieldsName}"]`)

	const $selectedCheckboxes = itemCheckboxes.filter((i, checkbox) => {
		return $(checkbox).is(':checked')
	})

	return $selectedCheckboxes
}

FORMS.addSelectedItemsIds = ($form, selectedItemsIds) => {
	selectedItemsIds.forEach((itemId) => {
		FORMS.appendItemIdInput(itemId, $form)
	})
}

FORMS.appendItemIdInput = (itemId, $form) => {
	const itemIdInput = $(`<input type="hidden" name="item_ids[${itemId}]" value="${itemId}">`)

	$form.append(itemIdInput)
}
