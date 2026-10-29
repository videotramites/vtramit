// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Model from '../models/procedureType.js'
import actionsApiRest from './procedureTypeActionsApiRest.js'

// initial state
const state = {
	items: {},
	timeStamp: 0,
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
}

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
}
