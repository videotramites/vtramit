// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const state = {
	steps: [],
	loading: false,
}

const getters = {
	steps(state) {
		return state.steps
	},
	loading(state) {
		return state.loading
	},
}

const actions = {
	fetchSteps({ commit }, procedureId) {
		if (!procedureId) {
			return Promise.reject(new Error('Not enough parameters'))
		}
		commit('setLoading', true)
		return axios({
			method: 'GET',
			url: generateUrl(`apps/vtramit/web/2.0/procedure/${procedureId}/protocol-step`),
		}).then(
			(result) => {
				if (result.data && result.data.result === 'OK') {
					commit('setSteps', result.data.list || [])
				}
			},
			(error) => {
				throw error
			},
		).finally(() => {
			commit('setLoading', false)
		})
	},

	completeStep({ commit }, { procedureId, stepId }) {
		if (!procedureId || !stepId) {
			return Promise.reject(new Error('Not enough parameters'))
		}
		return axios({
			method: 'POST',
			url: generateUrl(`apps/vtramit/web/2.0/procedure/${procedureId}/protocol-step/${stepId}`),
			headers: { 'content-type': 'application/json' },
			data: {},
		}).then(
			(result) => {
				if (result.data && result.data.result === 'OK') {
					commit('setSteps', result.data.list || [])
				}
			},
			(error) => {
				throw error
			},
		)
	},
}

const mutations = {
	setSteps(state, steps) {
		state.steps = steps
	},
	setLoading(state, loading) {
		state.loading = loading
	},
}

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
}
