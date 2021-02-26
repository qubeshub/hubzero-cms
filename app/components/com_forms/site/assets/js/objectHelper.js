/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

class ObjectHelper {

	invert(object) {
		let attr, value
		const invertedObject = {}

		for (attr in object) {
			value = object[attr]
			invertedObject[value] = attr
		}

		return invertedObject
	}

}

HUB.FORMS.ObjectHelper = ObjectHelper
