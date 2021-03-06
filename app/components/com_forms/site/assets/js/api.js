/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

class Api {

	static get(url, data) {
		const promise = this._makeApiRequest(url, data, 'GET')

		return promise
	}

	static post(url, data) {
		const promise = this._makeApiRequest(url, data, 'POST')

		return promise
	}

	static delete(url, data) {
		const promise = this._makeApiRequest(url, data, 'DELETE')

		return promise
	}

	static _makeApiRequest(url, data, method) {
		const baseApiUrl = '/api/'

		const promise = $.ajax({
			url: `${baseApiUrl}${url}`,
			data,
			method
		})

		return promise
	}

}

HUB.FORMS.Api = Api
