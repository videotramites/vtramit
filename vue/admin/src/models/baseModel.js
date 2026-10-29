// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

export default class BaseModel {

	constructor(/* config = {} */) {
		this.version = 0
		this.temp = true
		this.lastFetched = 0
		this.id = null

		// Object.assign(this, config)
	}

	randomId(length = 10) {
		let retString = ''
		const possible
      = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'

		for (let i = 0; i < length; i++) {
			retString += possible.charAt(Math.floor(Math.random() * possible.length))
		}

		return retString
	}

	// ----------------------------------------------------------------------------
	// Converts a variable name from snake-case to camelCase.
	//
	snakeToCamel(str) {
		return str.replace(/([-_][a-zA-Z0-9])/g, (all, group) => {
			return group.toUpperCase().replace('-', '').replace('_', '')
		})
	}

	// ----------------------------------------------------------------------------
	// Converts a variable name from camelCase to snake-case.
	//
	camelToSnake(str) {
		return str.replace(/([a-z])([A-Z0-9])/g, (all, group1, group2) => {
			return group1 + '_' + group2.toLowerCase()
		})
	}

	// ----------------------------------------------------------------------------
	// Converting data from the backend Api format to plain objects. It will
	// aply any variable naming conventions changes and adapt any format
	// discrepancies.
	//
	copyValuesFromAPI(data = {}) {
		Object.assign(this, data)

		// Object.keys(this).forEach((key) => {
		//   if (key !== this.snakeToCamel(key)) {
		//     this[this.snakeToCamel(key)] = this[key];
		//     delete this[key];
		//   }
		// });
	}

	// ----------------------------------------------------------------------------
	// Converting data from the plain objects for the store to the format needed
	// by the backend Api. It will aply any variable naming conventions changes
	// and adapt any format discrepancies.
	//
	copyValuesFromModel(data = {}) {
		Object.assign(this, data)

		// Object.keys(this).forEach((key) => {
		//   if (key !== this.camelToSnake(key)) {
		//     this[this.camelToSnake(key)] = this[key];
		//     delete this[key];
		//   }
		// });
	}

	// ----------------------------------------------------------------------------
	// Filters out some fields we do not need to send to the Api and converts
	// the object store format to the one needed by the Api.
	//
	filterForApi(Model = BaseModel) {
		const forStorage = new Model()
		forStorage.copyValuesFromModel(this)
		delete forStorage.temp
		delete forStorage.lastFetched
		delete forStorage.version
		return forStorage
	}

	filterForStore() {
		const forStorage = new BaseModel()
		forStorage.copyValuesFromAPI(this)
		delete forStorage.temp
		return Object.assign({}, forStorage)
	}

	static migrate() {}

	filterOutEmptyKeys() {
		Object.keys(this).forEach((key) => {
			if ((key in this) && this[key] == null) {
				delete this[key]
			}
		})
		return this
	}

}
