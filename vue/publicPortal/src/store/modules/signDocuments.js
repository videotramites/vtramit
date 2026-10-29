// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

// initial state
const state = {
	itemsKeys: [],
	items: {},
}

// getters
const getters = {
	readAll(state) {
		return state.itemsKeys.filter((id) => {
			if (id && state.items[id].temp === false) {
				return true
			}
			return false
		}).map((id) => {
			return state.items[id]
		})
	},
	readById(state) {
		return (id) => {
			if (id === undefined) {
				return {}
			} else {
				if (!Object.prototype.hasOwnProperty.call(state, id)) {
					Vue.set(state.items, id, {})
				}
				return state.items[id]
			}
		}
	},
}

// actions
const actions = {
	readAll({ commit }, id) {
		if (id) {
			return axios({
				method: 'GET',
				url: generateUrl(`/apps/vtramit/api/2.0/signature/pendingfiles/${id}`),
			}).then(
				result => {
					// eslint-disable-next-line no-console
					// console.log('readAll', result)
					commit('readAllSuccess', result.data)
					return true
				},
				error => {
					// eslint-disable-next-line no-console
					console.error('ERROR readAll', error)
					throw error
				},
			)
		} else {
			return false
		}
	},
}

// mutations
const mutations = {
	readAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if (Object.prototype.hasOwnProperty.call(items[i], 'id') && items[i].id) {
				Vue.set(state.items, items[i].id, Object.assign({}, state.items[items[i].id], items[i], { temp: false }))
				readAllKeys.push(items[i].id)
				if (state.itemsKeys.indexOf(items[i].id) < 0) {
					state.itemsKeys.push(items[i].id)
				}
			}
		}

		const toDelete = state.itemsKeys.filter(function(key) { return (readAllKeys.indexOf(key) < 0) })
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
