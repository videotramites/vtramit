// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Model from '../models/procedures.js'
import GroupsModel from '../models/groups.js'
import UsersModel from '../models/users.js'
import ChannelsModel from '../models/channels.js'
import StatusesModel from '../models/statuses.js'
import VideoconferencesModel from '../models/videoconferences.js'
import SignaturesModel from '../models/signatures.js'
import actionsApiRest from './proceduresActionsApiRest.js'
// Vue.config.devtools = true

// initial state
const state = {
	itemsKeys: [],
	itemKeySidebar: '',
	items: {},

	// Pagination
	pageNumber: false,
	totalPages: false,
	paginationActive: false,

	// Active videoconferences
	activeVideoconferencesKeys: [],
	activeVideoconferences: {},

	// Active videoconferences
	filteredSignaturesKeys: [],
	filteredSignatures: {},

	// Info for the specific procedure we are displaying in edit/show view.
	// Note that this means we can only show one procedure full info at a time.
	allowedGroupsKeys: [],
	allowedGroups: {},
	allowedUsersKeys: [],
	allowedUsers: {},
	allowedChannelsKeys: [],
	allowedChannels: {},
	allowedStatusesKeys: [],
	allowedStatuses: {},
	signatureButtonsKeys: [],
	signatureButtons: {},
}

// getters
const getters = {
	getItemKeySideBar(state) {
		return state.itemKeySidebar
	},
	readAll(state) {
		return state.itemsKeys
			.filter((id) => {
				if (id && state.items[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.items[id]
			})
	},
	readById(state) {
		return (id) => {
			if (id === undefined) {
				return new Model()
			} else {
				if (!(id in state.items)) {
					Vue.set(state.items, id, new Model())
				}
				return state.items[id]
			}
		}
	},
	// Pagination
	// --------------------------------------------------------------------------
	getPageNumber() {
		return state.pageNumber
	},
	getTotalPages() {
		return state.totalPages
	},
	isPaginationActive() {
		return state.paginationActive
	},
	// Allowed channels
	// --------------------------------------------------------------------------
	readAllowedChannelsAll(state) {
		return state.allowedChannelsKeys
			.filter((id) => {
				if (id && state.allowedChannels[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.allowedChannels[id]
			})
	},
	readAllowedChannelsById(state) {
		return (id) => {
			if (id === undefined) {
				return new ChannelsModel()
			} else {
				if (!(id in state.allowedChannels)) {
					Vue.set(state.allowedChannels, id, new ChannelsModel())
				}
				return state.allowedChannels[id]
			}
		}
	},
	// Allowed statuses
	// --------------------------------------------------------------------------
	readAllowedStatusesAll(state) {
		return state.allowedStatusesKeys
			.filter((id) => {
				if (id && state.allowedStatuses[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.allowedStatuses[id]
			})
	},
	readAllowedStatusesById(state) {
		return (id) => {
			if (id === undefined) {
				return new StatusesModel()
			} else {
				if (!(id in state.allowedStatuses)) {
					Vue.set(state.allowedStatuses, id, new StatusesModel())
				}
				return state.allowedStatuses[id]
			}
		}
	},
	// Signature buttons
	// --------------------------------------------------------------------------
	readSignatureButtonsAll(state) {
		return state.signatureButtonsKeys
			.filter((id) => {
				if (id && state.signatureButtons[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.signatureButtons[id]
			})
	},
	readSignatureButtonsById(state) {
		return (id) => {
			if (id === undefined) {
				return new StatusesModel()
			} else {
				if (!(id in state.signatureButtons)) {
					Vue.set(state.signatureButtons, id, new StatusesModel())
				}
				return state.signatureButtons[id]
			}
		}
	},
	// Active videoconferences
	// --------------------------------------------------------------------------
	readActiveVideoconferencesAll(state) {
		return state.activeVideoconferencesKeys
			.filter((id) => {
				if (id && state.activeVideoconferences[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.activeVideoconferences[id]
			})
	},
	readActiveVideoconferencesById(state) {
		return (id) => {
			if (id === undefined) {
				return new VideoconferencesModel()
			} else {
				if (!(id in state.activeVideoconferences)) {
					Vue.set(state.activeVideoconferences, id, new VideoconferencesModel())
				}
				return state.activeVideoconferences[id]
			}
		}
	},
	readActiveVideoconferencesByProcedureId(state) {
		return (procedureId) => {
			if (procedureId === undefined) {
				return null
			} else {
				return state.activeVideoconferencesKeys
					.filter((id) => {
						if (id && state.activeVideoconferences[id].procedureId === procedureId && state.activeVideoconferences[id].temp === false) {
							return true
						}
						return false
					})
					.map((id) => {
						return state.activeVideoconferences[id]
					})
			}
		}
	},
	// Active signatures
	// --------------------------------------------------------------------------
	readFilteredSignaturesAll(state) {
		return state.filteredSignaturesKeys
			.filter((id) => {
				if (id && state.filteredSignatures[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.filteredSignatures[id]
			})
	},
	readFilteredSignaturesById(state) {
		return (id) => {
			if (id === undefined) {
				return new SignaturesModel()
			} else {
				if (!(id in state.filteredSignatures)) {
					Vue.set(state.filteredSignatures, id, new SignaturesModel())
				}
				return state.filteredSignatures[id]
			}
		}
	},
	readFilteredSignaturesByProcedureId(state) {
		return (procedureId) => {
			if (procedureId === undefined) {
				return null
			} else {
				return state.filteredSignaturesKeys
					.filter((id) => {
						if (id && state.filteredSignatures[id].procedureId === procedureId && state.filteredSignatures[id].temp === false) {
							return true
						}
						return false
					})
					.map((id) => {
						return state.filteredSignatures[id]
					})
			}
		}
	},
}

// actions
const actions = actionsApiRest

// mutations
const mutations = {
	createOnSuccess(state, item) {
		let forStore = new Model(item)
		forStore = forStore.filterForStore()

		if (!(forStore.id in state.items)) {
			Vue.set(state.items, forStore.id, new Model())
			state.itemsKeys.push(forStore.id)
		}
		Vue.set(
			state.items,
			forStore.id,
			Object.assign(new Model(), state.items[forStore.id], forStore, {
				temp: false,
			}),
		)
	},
	readAllSuccess(state, data) {
		const items = data.list
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new Model(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.items,
					forStore.id,
					Object.assign(new Model(), state.items[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.itemsKeys.indexOf(forStore.id) < 0) {
					state.itemsKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.itemsKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			// We do not delete the item that is showing on the sidebar
			if (state.itemKeySidebar !== toDelete[i]) {
				Vue.delete(state.items, toDelete[i])
			}
			// But we remove it from the itemKeys so it does not appear on the
			// filtered lists.
			state.itemsKeys.splice(state.itemsKeys.indexOf(toDelete[i]), 1)
		}

		// Check pagination settings
		if (data.pages && data.page) {
			state.paginationActive = true
			state.pageNumber = data.page
			state.totalPages = data.pages
		} else {
			state.paginationActive = false
			state.pageNumber = false
			state.totalPages = false
		}
	},
	readByIdSuccess(state, item) {
		let forStore = new Model(item)
		forStore = forStore.filterForStore()

		if (!(forStore.id in state.items)) {
			Vue.set(state.items, forStore.id, new Model())
		}
		// When we create a temporary item we put it on state.items but not on
		// state.itemKeys as we don't know if it really exists or not. When we
		// receive confirmation that the item exists on the backend then we
		// can put it on state.itemKeys.
		// if (state.itemsKeys.indexOf(forStore.id) < 0) {
		// state.itemsKeys.push(forStore.id)
		// }
		state.itemKeySidebar = forStore.id
		Vue.set(
			state.items,
			forStore.id,
			Object.assign(new Model(), state.items[forStore.id], forStore, {
				temp: false,
			}),
		)
	},
	updateOnSuccess(state, item) {
		let forStore = new Model(item)
		forStore = forStore.filterForStore()

		Vue.set(
			state.items,
			forStore.id,
			Object.assign(new Model(), state.items[forStore.id], forStore, {
				temp: false,
			}),
		)
	},
	deleteOnSuccess(state, id) {
		if (id in state.items) {
			Vue.delete(state.items, id)
			state.itemsKeys.splice(state.itemsKeys.indexOf(id), 1)
		}
	},
	// Pagination
	// --------------------------------------------------------------------------
	isPaginationActive(data) {
		if (data.pages) {
			this.state.paginationActive = true
			this.state.pageNumber = 1
			return true
		}
		this.state.paginationActive = false
		this.state.pageNumber = false
		return false
	},
	// Download CSV
	// --------------------------------------------------------------------------
	downloadCsvSuccess(state, response) {
		// Extract the CSV data and file name from the response
		const csvData = response.data
		const fileName = response.headers['content-disposition']
			.split('filename=')[1]
			.replace(/"/g, '') // Extract the file name from content-disposition

		// Create a Blob from the CSV data
		const blob = new Blob([csvData], { type: 'text/csv;charset=UTF-8' })

		// Create a temporary anchor element
		const url = window.URL.createObjectURL(blob)
		const a = document.createElement('a')

		// Set the href and download attributes
		a.href = url
		a.download = fileName

		// Append the anchor to the body (it won't be visible)
		document.body.appendChild(a)

		// Programmatically click the anchor to trigger the download
		a.click()

		// Cleanup the Blob URL and remove the anchor element
		window.URL.revokeObjectURL(url)
		document.body.removeChild(a)
	},
	// Allowed groups
	// --------------------------------------------------------------------------
	readAllowedGroupsAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new GroupsModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.allowedGroups,
					forStore.id,
					Object.assign(new GroupsModel(), state.allowedGroups[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.allowedGroupsKeys.indexOf(forStore.id) < 0) {
					state.allowedGroupsKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.allowedGroupsKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.allowedGroups, toDelete[i])
			state.allowedGroupsKeys.splice(state.allowedGroupsKeys.indexOf(toDelete[i]), 1)
		}
	},
	// Allowed users
	// --------------------------------------------------------------------------
	readAllowedUsersAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new UsersModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.allowedUsers,
					forStore.id,
					Object.assign(new UsersModel(), state.allowedUsers[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.allowedUsersKeys.indexOf(forStore.id) < 0) {
					state.allowedUsersKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.allowedUsersKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.allowedUsers, toDelete[i])
			state.allowedUsersKeys.splice(state.allowedUsersKeys.indexOf(toDelete[i]), 1)
		}
	},
	// Channels
	// --------------------------------------------------------------------------
	readAllowedChannelsAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new ChannelsModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.allowedChannels,
					forStore.id,
					Object.assign(new ChannelsModel(), state.allowedChannels[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.allowedChannelsKeys.indexOf(forStore.id) < 0) {
					state.allowedChannelsKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.allowedChannelsKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.allowedChannels, toDelete[i])
			state.allowedChannelsKeys.splice(state.allowedChannelsKeys.indexOf(toDelete[i]), 1)
		}
	},
	// Statuses
	// --------------------------------------------------------------------------
	readAllowedStatusesAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new StatusesModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.allowedStatuses,
					forStore.id,
					Object.assign(new StatusesModel(), state.allowedStatuses[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.allowedStatusesKeys.indexOf(forStore.id) < 0) {
					state.allowedStatusesKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.allowedStatusesKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.allowedStatuses, toDelete[i])
			state.allowedStatusesKeys.splice(state.allowedStatusesKeys.indexOf(toDelete[i]), 1)
		}
	},
	// Signature buttons
	// --------------------------------------------------------------------------
	readSignatureButtonsAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new StatusesModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.signatureButtons,
					forStore.id,
					Object.assign(new StatusesModel(), state.signatureButtons[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.signatureButtonsKeys.indexOf(forStore.id) < 0) {
					state.signatureButtonsKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.signatureButtonsKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.signatureButtons, toDelete[i])
			state.signatureButtonsKeys.splice(state.signatureButtonsKeys.indexOf(toDelete[i]), 1)
		}
	},
	// Active videoconferences
	// --------------------------------------------------------------------------
	readActiveVideconferencesAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new VideoconferencesModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.activeVideoconferences,
					forStore.id,
					Object.assign(new VideoconferencesModel(), state.activeVideoconferences[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.activeVideoconferencesKeys.indexOf(forStore.id) < 0) {
					state.activeVideoconferencesKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.activeVideoconferencesKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.activeVideoconferences, toDelete[i])
			state.activeVideoconferencesKeys.splice(state.activeVideoconferencesKeys.indexOf(toDelete[i]), 1)
		}
	},
	// Active signatures
	// --------------------------------------------------------------------------
	readFilteredSignaturesAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new SignaturesModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.filteredSignatures,
					forStore.id,
					Object.assign(new SignaturesModel(), state.filteredSignatures[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.filteredSignaturesKeys.indexOf(forStore.id) < 0) {
					state.filteredSignaturesKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.filteredSignaturesKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.filteredSignatures, toDelete[i])
			state.filteredSignaturesKeys.splice(state.filteredSignaturesKeys.indexOf(toDelete[i]), 1)
		}
	},
}

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
}
