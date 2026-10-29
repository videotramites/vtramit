// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Model from '../models/stakeholders.js'
import actionsApiRest from './stakeholdersActionsApiRest.js'

// initial state
const state = {
	itemsKeys: [],
	items: {},
}

// getters
const getters = {
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
	readByProcedureIdSuccess(state, items) {
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
}

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
}
