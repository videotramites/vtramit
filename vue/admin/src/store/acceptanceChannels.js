// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Model from '../models/acceptanceChannels.js'
import actionsApiRest from './acceptanceChannelsActionsApiRest.js'

// initial state
const state = {
	itemsKeys: [],
	items: {},
	timeStamp: 0,
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
}

// actions
const actions = actionsApiRest

// mutations
const mutations = {
	readAllSuccess(state, items) {
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
			Vue.delete(state.items, toDelete[i])
			state.itemsKeys.splice(state.itemsKeys.indexOf(toDelete[i]), 1)
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
