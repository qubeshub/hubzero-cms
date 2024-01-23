/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

const fields = [
	{
		label: "Country",
		type: "select",
		subtype: "country",
		icon: "⚐"
	},
	{
		label: "Url",
		type: "text",
		subtype: "url",
		icon: "✉"
	},
	{
		label: "Orcid",
		type: "text",
		subtype: "orcid",
		icon: "iD"
	},
	{
		label: "Tags",
		type: "text",
		subtype: "tags",
		icon: "✎"
	},
	{
		label: "Address",
		type: "text",
		subtype: "address",
		icon: "⚲"
	},
	{
		label: "Editor",
		type: "textarea",
		subtype: "editor",
		icon: "¶"
	}
]

class FormBuilder {

	constructor({$anchor}) {
		this.$anchor = $anchor
		this._builder = undefined
		this._defaultOptions = {
			disabledActionButtons: ['clear', 'data', 'save'],
			disabledAttrs: ['access', 'className', 'placeholder', 'style', 'subtype'],
			disableFields: ['autocomplete', 'button', 'file'],
			fields: fields
		}
	}

	render(options = {}) {
		const combinedOptions = { ...this._defaultOptions, ...options }
	

		this._builder = this.$anchor.formBuilder(combinedOptions)
	}

	getFields() {
		let fieldsState = this._builder.actions.getData('js')

		return fieldsState
	}

	setFields(fields) {
		const fieldsJson = JSON.stringify(fields)

		this._builder.actions.setData(fieldsJson)
	}

}

HUB.FORMS.FormBuilder = FormBuilder
