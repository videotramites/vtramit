// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Model from '../models/stakeholders.js'
import actionsApiRest from './stakeholdersActionsApiRest.js'

// initial state
const state = {
	itemsKeys: [],
	items: {},

	itemsSidebarKeys: [],
	itemsSidebar: {},
}

// getters
const getters = {
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
	readByProcedureId(state) {
		return (procedureId) => {
			return state.itemsKeys
				.filter((id) => {
					if (id && state.items[id].procedureId === procedureId && state.items[id].temp === false) {
						return true
					}
					return false
				})
				.map((id) => {
					return state.items[id]
				})
		}
	},
	readSidebarByProcedureId(state) {
		return (procedureId) => {
			return state.itemsSidebarKeys
				.filter((id) => {
					if (id && state.itemsSidebar[id].procedureId === procedureId && state.itemsSidebar[id].temp === false) {
						return true
					}
					return false
				})
				.map((id) => {
					return state.itemsSidebar[id]
				})
		}
	},
	readMainsByProcedureId(state) {
		return (procedureId) => {
			return state.itemsKeys
				.filter((id) => {
					if (id && state.items[id].procedureId === procedureId && state.items[id].isMain && state.items[id].temp === false) {
						return true
					}
					return false
				})
				.map((id) => {
					return state.items[id]
				})
		}
	},
}

// actions
const actions = actionsApiRest

// mutations
const mutations = {
	genericOnSuccess() {
		// There are some messages that do not return data or that we don't need
		// to do anything with the data they return.
	},
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
	cleanStakeholders(state) {
		state.itemsKeys.forEach(id => {
			Vue.delete(state.items, id)
		})
		state.itemsKeys.splice(0, state.itemsKeys.length)
	},
	readAllSuccess(state, items) {
		const readAllKeys = []
		for (const element of items) {
			if ('id' in element && element.id) {
				let forStore = new Model(element)
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
		for (const element of toDelete) {
			Vue.delete(state.items, element)
			state.itemsKeys.splice(state.itemsKeys.indexOf(element), 1)
		}
	},
	readSidebarSuccess(state, items) {
		const readAllKeys = []
		for (const element of items) {
			if ('id' in element && element.id) {
				let forStore = new Model(element)
				forStore = forStore.filterForStore()

				Vue.set(
					state.itemsSidebar,
					forStore.id,
					Object.assign(new Model(), state.itemsSidebar[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.itemsSidebarKeys.indexOf(forStore.id) < 0) {
					state.itemsSidebarKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.itemsSidebarKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (const element of toDelete) {
			Vue.delete(state.itemsSidebar, element)
			state.itemsSidebarKeys.splice(state.itemsSidebarKeys.indexOf(element), 1)
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
		state.itemsKeys = forStore.id
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
	// ------------------------------------------------------------------------
	// Store mutation for setting fullEntryPage
	setEntryPageSuccess(state, payload) {
		const { id, fullEntryPage } = payload || {}
		if (!id) return

		// Bail out if the item does not exist (keep previous semantics)
		if (!state.itemsSidebar || !state.itemsSidebar[id]) return

		// Current ref
		const current = state.itemsSidebar[id]

		// Set reactively: if the key exists, assign; otherwise Vue.set to create it
		if (Object.hasOwn(current, 'fullEntryPage')) {
			current.fullEntryPage = fullEntryPage
		} else {
			Vue.set(current, 'fullEntryPage', fullEntryPage)
		}
	},
	// ------------------------------------------------------------------------
	// Mark entryPage loading state for a stakeholder
	setEntryPageLoading(state, payload) {
		const { id, loading } = payload || {}
		if (!id || !state.itemsSidebar || !state.itemsSidebar[id]) return

		// Ensure reactive create if missing
		if (Object.hasOwn(state.itemsSidebar[id], 'loadingEntryPage')) {
			state.itemsSidebar[id].loadingEntryPage = loading === true
		} else {
			Vue.set(state.itemsSidebar[id], 'loadingEntryPage', loading === true)
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
