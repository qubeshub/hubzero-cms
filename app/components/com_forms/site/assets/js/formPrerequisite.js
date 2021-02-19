/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

class FormPrerequisite {

	static get apiEndpoint() {
		return 'v1.0/forms/formprerequisites/'
	}

	static get api() {
		return HUB.FORMS.Api
	}

	constructor({ id }) {
		this.id = id
	}

	destroy() {
		const endpoint = `${this.constructor.apiEndpoint}destroy`
		const requestData = { id: this.id }

		const promise = this.constructor.api.delete(endpoint, requestData)

		return promise
	}

}

HUB.FORMS.FormPrerequisite = FormPrerequisite
