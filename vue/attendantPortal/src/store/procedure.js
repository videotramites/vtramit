// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Model from '../models/procedure.js'
import ChannelsModel from '../models/channels.js'
import ProcedureStatusesModel from '../models/procedureStatuses.js'
import actionsApiRest from './procedureActionsApiRest.js'

// initial state
const state = {
	itemsKeys: [],
	items: {},

	allowedChannelsKeys: [],
	allowedChannels: {},
	allowedStatusesKeys: [],
	allowedStatuses: {},
	signatureButtonsKeys: [],
	signatureButtons: {},
}

// getters
const getters = {
	readById(state) {
		return (id) => {
			if (id === undefined || id === null) {
				return new Model()
			} else {
				if (!(id in state.items)) {
					Vue.set(state.items, id, new Model())
				}
				return state.items[id]
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
}

// actions
const actions = actionsApiRest

// mutations
const mutations = {
	readByIdSuccess(state, item) {
		if (!item || !item.id) return
		let forStore = new Model(item)
		forStore = forStore.filterForStore()

		Vue.set(
			state.items,
			forStore.id,
			Object.assign(new Model(), state.items[forStore.id], forStore, {
				temp: false,
				lastFetched: Date.now(),
			}),
		)
	},
	// Channels
	// --------------------------------------------------------------------------
	readAllowedChannelsAllSuccess(state, items) {
		const readAllKeys = []
		for (const element of items) {
			if ('id' in element && element.id) {
				let forStore = new ChannelsModel(element)
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
		for (const element of toDelete) {
			Vue.delete(state.allowedChannels, element)
			state.allowedChannelsKeys.splice(state.allowedChannelsKeys.indexOf(element), 1)
		}
	},
	// Statuses
	// --------------------------------------------------------------------------
	readAllowedStatusesAllSuccess(state, items) {
		const readAllKeys = []
		for (const element of items) {
			if ('id' in element && element.id) {
				let forStore = new ProcedureStatusesModel(element)
				forStore = forStore.filterForStore()

				Vue.set(
					state.allowedStatuses,
					forStore.id,
					Object.assign(new ProcedureStatusesModel(), state.allowedStatuses[forStore.id], forStore, {
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
		for (const element of toDelete) {
			Vue.delete(state.allowedStatuses, element)
			state.allowedStatusesKeys.splice(state.allowedStatusesKeys.indexOf(element), 1)
		}
	},
	// Signature buttons
	// --------------------------------------------------------------------------
	readSignatureButtonsAllSuccess(state, items) {
		const readAllKeys = []
		for (const element of items) {
			if ('id' in element && element.id) {
				let forStore = new ProcedureStatusesModel(element)
				forStore = forStore.filterForStore()

				Vue.set(
					state.signatureButtons,
					forStore.id,
					Object.assign(new ProcedureStatusesModel(), state.signatureButtons[forStore.id], forStore, {
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
		for (const element of toDelete) {
			Vue.delete(state.signatureButtons, element)
			state.signatureButtonsKeys.splice(state.signatureButtonsKeys.indexOf(element), 1)
		}
	},
	// Update
	// --------------------------------------------------------------------------
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
