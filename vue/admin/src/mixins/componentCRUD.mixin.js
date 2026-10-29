// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import MessageManager from './messageManager.mixin.js'
import CKEditor from './componentCKEditor.mixin.js'
import Validator from './validator.mixin.js'
import ValidatorForJson from './validatorForJson.mixin.js'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import '@nextcloud/dialogs/styles/toast.scss'

export default {
	mixins: [MessageManager, CKEditor, Validator, ValidatorForJson],
	data: () => ({
		// Proxy form data
		itemList: [],
		urlForLoad: '',
		urlForInsert: '',
		urlForUpdate: '',
		urlForDelete: '',
	}),
	// ------------------------------------------------------------------------
	// Vue's mounted lifecycle hook.
	// Executes once the component has been inserted into the DOM.
	// This ensures initialization after all DOM elements are available.
	//
	mounted() {
		this.initializeCkeditorSafely(this.$el)
		this.initializeJsonValidatorSafely(this.$el)
	},
	methods: {
		// ------------------------------------------------------------------------
		// Initializes CKEditor only if the method is already available.
		//
		initializeCkeditorSafely(wrapper) {
			if (typeof this.initializeCkeditor === 'function') {
				this.initializeCkeditor(wrapper)
			}
		},
		// ------------------------------------------------------------------------
		// Initializes the JSON Validator only if the method is already available.
		//
		initializeJsonValidatorSafely(wrapper) {
			if (typeof this.initializeJsonValidator === 'function') {
				this.initializeJsonValidator(wrapper)
			}
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		// This method MUST be OVERRIDED
		//
		prepareNewItem() {
			return {}
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		// This method MUST be OVERRIDED
		//
		prepareItemForCreate(item) {
			return {}
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to update the item.
		// This method MUST be OVERRIDED
		//
		prepareItemForUpdate(item) {
			return {}
		},
		// ------------------------------------------------------------------------
		// Indicates wether the data is right for creation.
		// This method MUST be OVERRIDED
		//
		validateBeforeCreate(item) {
			return true
		},
		// ------------------------------------------------------------------------
		// Indicates wether the data is right for update.
		// This method MUST be OVERRIDED
		//
		validateBeforeUpdate(item) {
			return true
		},
		// ------------------------------------------------------------------------
		// Ensures the item has an original snapshot for later restore.
		//
		// @param {Object} item
		// @returns {void}
		//
		createSnapshot(item) {
			if (!item || typeof item !== 'object' || item.isNew) {
				return
			}

			const meta = new Set(['isNew', 'isChanged', 'isExpanded', '_snapshot'])
			const state = {}
			for (const key of Object.keys(item)) {
				if (!meta.has(key)) {
					state[key] = item[key]
				}
			}

			item._snapshot = JSON.parse(JSON.stringify(state))
		},
		// ------------------------------------------------------------------------
		// Restores the editable fields from the original snapshot.
		//
		// @param {number} index
		// @returns {void}
		//
		restoreSnapshot(index) {
			const item = this.itemList[index]
			if (!item || !item._snapshot) {
				return
			}

			const state = JSON.parse(JSON.stringify(item._snapshot))

			for (const key of Object.keys(state)) {
				item[key] = state[key]
			}
			item.isChanged = false
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields and proper initial
		// values.
		//
		newItem(data = {}) {
			const newItem = this.prepareNewItem()
			// Data needed to control the state of the item.
			newItem.isNew = true
			newItem.isExpanded = true
			newItem.isChanged = false
			Object.assign(newItem, data)
			return newItem
		},
		// ------------------------------------------------------------------------
		// Preprocess variables in the URL.
		//
		processUrl(url, urlTokens = null) {
			let processedUrl = url

			if (urlTokens !== undefined && urlTokens !== null) {
				if (urlTokens.id !== undefined && urlTokens.id !== null) {
					processedUrl = processedUrl.replace('%id', urlTokens.id)
				}
			}

			return processedUrl
		},
		// ------------------------------------------------------------------------
		// Loads the data from the backend API.
		//
		loadFormData(selectedItem = null, urlTokens = null) {
			// Setting the proper "ComponentStatus" so we can enable/disable things
			// whilst we are waiting for the API calls.
			this.setStatusReading()
			// Preapre the URL to load all items
			const urlForApi = this.processUrl(this.urlForLoad, urlTokens !== undefined && urlTokens !== null ? urlTokens : selectedItem)
			return axios({
				method: 'GET',
				url: generateUrl(urlForApi),
			}).then((result) => {
				if (result.data?.result && result.data.result === 'OK' && result.data.list) {
					// Emptying the array using splice to activate Vue's responsiveness.
					this.itemList.splice(0, this.itemList.length)
					// For each data item from the backend API, we initialise the
					// proxy from data array with new objects both to decouple the
					// response array from the actual data and also to have it
					// initialised with all proper fields and initial values.
					result.data.list.forEach((item) => {
						const newItem = this.newItem(item)
						newItem.isNew = false
						newItem.isExpanded = selectedItem !== null && selectedItem.id === newItem.id
						this.itemList.push(newItem)
						this.createSnapshot(newItem)
					})
					this.setStatusReadSuccess()
					return true
				}
				return false
			}).catch((error) => {
				// console.error('ERROR readAll', error)
				this.setStatusReadError()
				showError(t('vtramit', 'Error loading initial configuration data'))
				throw error
			})
		},
		// ------------------------------------------------------------------------
		// Add new item to form click handler.
		//
		clickAddNewItem() {
			this.addNewItem()
		},
		// ------------------------------------------------------------------------
		// Adding new item to the form.
		//
		addNewItem() {
			// Collapse all existing items before create the new one.
			this.collapseAllItems()

			// Push the new item's model to the proxy form data.
			this.itemList.push(this.newItem())
			const item = this.itemList[this.itemList.length - 1]
			this.createSnapshot(item)

			// After the DOM updates, initialize additional components
			this.$nextTick(() => {
				this.initializeCkeditorSafely(this.$el)
				this.initializeJsonValidatorSafely(this.$el)
			})
		},
		// ------------------------------------------------------------------------
		// Create item click handler.
		//
		clickCreateItem(item, tokens) {
			if (this.validateBeforeCreate(item)) {
				return this.createItem(item, tokens)
			}
		},
		// ------------------------------------------------------------------------
		// Create item.
		//
		createItem(item, tokens) {
			// Setting the proper "ComponentStatus" so we can enable/disable things
			// whilst we are waiting for the API calls.
			this.setStatusCreating()
			// Preapre the URL to create the new item
			const urlForApi = this.processUrl(this.urlForInsert, item)
			// Marking the state of the items for its particular CRUD buttons to
			// have the right state.
			item.isNew = false
			const forApi = this.prepareItemForCreate(item)
			return axios({
				method: 'POST',
				url: generateUrl(urlForApi),
				headers: { 'content-type': 'application/json' },
				data: {
					data: forApi,
				},
			}).then((result) => {
				this.setStatusCreateSuccess()
				this.manageMessage(result.data, 'Successfully created')
				this.createSnapshot(item)
				// this.loadFormData(item, tokens)
			}).catch((error) => {
				// console.error('ERROR readAll', error)
				this.setStatusCreateError()
				this.manageException(error, t('vtramit', 'Error updating the configuration'))
				// Marking the state of the items for its particular CRUD buttons to
				// have the right state.
				item.isNew = true
			})
		},
		// ------------------------------------------------------------------------
		// Update item click handler.
		//
		clickUpdateItem(item, tokens) {
			if (this.validateBeforeUpdate(item)) {
				return this.updateItem(item, tokens)
			}
		},
		// ------------------------------------------------------------------------
		// Update item.
		//
		updateItem(item, tokens) {
			// Setting the proper "ComponentStatus" so we can enable/disable things
			// whilst we are waiting for the API calls.
			this.setStatusUpdating()
			// Preapre the URL to update the item
			const urlForApi = this.processUrl(this.urlForUpdate, item)
			// Marking the state of the items for its particular CRUD buttons to
			// have the right state.
			item.isChanged = false
			const forApi = this.prepareItemForUpdate(item)
			return axios({
				method: 'PUT',
				url: generateUrl(urlForApi),
				headers: { 'content-type': 'application/json' },
				data: {
					data: forApi,
				},
			}).then((result) => {
				this.setStatusUpdateSuccess()
				this.manageMessage(result.data, 'Successfully updated')
				this.createSnapshot(item)
				// this.loadFormData(item, tokens)
			}).catch((error) => {
				// console.error('ERROR readAll', error)
				this.setStatusUpdateError()
				this.manageException(error, t('vtramit', 'Error updating the configuration'))
				// Marking the state of the items for its particular CRUD buttons to
				// have the right state.
				item.isChanged = true
			})
		},
		// ------------------------------------------------------------------------
		// Delete item click handler.
		//
		clickDeleteItem(item, tokens) {
			return this.deleteItem(item, tokens)
		},
		// ------------------------------------------------------------------------
		// Delete item.
		//
		deleteItem(item, tokens) {
			// Setting the proper "ComponentStatus" so we can enable/disable things
			// whilst we are waiting for the API calls.
			this.setStatusDeleting()
			// Prepare the URL to delete the item
			const urlForApi = this.processUrl(this.urlForDelete, item)
			return axios({
				method: 'DELETE',
				url: generateUrl(urlForApi),
			}).then((result) => {
				this.setStatusDeleteSuccess()
				this.manageMessage(result.data, 'Successfully deleted')

				// Remove the item from the local form list (UI update).
				const idx = this.itemList.findIndex(it => it === item || (item.id && it.id === item.id))
				if (idx !== -1) {
					this.itemList.splice(idx, 1)
				}
				// this.loadFormData(item, tokens)
			}).catch((error) => {
				// console.error('ERROR readAll', error)
				this.setStatusDeleteError()
				this.manageException(error, t('vtramit', 'Error updating the configuration'))
			})
		},
		// ------------------------------------------------------------------------
		// Cancel item click handler.
		//
		clickCancelItem(index) {
			const item = this.itemList[index]
			if (!item) {
				return
			}
			if (item.isNew) {
				this.cancelNewItem(index)
			} else {
				this.restoreSnapshot(index)
			}
		},

		// ------------------------------------------------------------------------
		// Cancel item edition.
		//
		cancelNewItem(index) {
			this.itemList.splice(index, 1)
		},
		// ------------------------------------------------------------------------
		// Generic list loader.
		//
		loadList(url, list) {
			// Setting the proper "ComponentStatus" so we can enable/disable things
			// whilst we are waiting for the API calls.
			return axios({
				method: 'GET',
				url: generateUrl(url),
			}).then((result) => {
				if (result.data?.result && result.data.result === 'OK' && result.data.list) {
					// Emptying the array using splice to activate Vue's responsiveness.
					list.splice(0, list.length)
					// For each data item from the backend API, we initialise the
					// proxy from data array with new objects both to decouple the
					// response array from the actual data and also to have it
					// initialised with all proper fields and initial values.
					result.data.list.forEach((item) => {
						list.push(item)
					})
					return true
				}
				return false
			}).catch((error) => {
				this.manageException(error, t('vtramit', 'Error loading initial configuration data'))
			})
		},
	},
}
