// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import actionsApiRest from './contextActionsApiRest.js'

// initial state
const state = {
	timezone: '',
	phoneLink: null,
	isAdmin: false,
	mode: 'production',
}

// getters
const getters = {
	isAdmin(state) {
		return state.isAdmin
	},
	getPhoneLink(state) {
		return state.phoneLink
	},
	getTimezone(state) {
		return state.timezone
	},
	getMode(state) {
		return state.mode
	},
	isTest(state) {
		return state.mode === 'test'
	},
}

// actions
const actions = actionsApiRest

// mutations
const mutations = {
	readContextSuccess(state, context) {
		if (context.isAdmin && context.isAdmin === true) {
			state.isAdmin = true
		} else {
			state.isAdmin = false
		}
		if (context.phoneLink) {
			state.phoneLink = context.phoneLink
		} else {
			state.phoneLink = null
		}
		if (context.timezone) {
			state.timezone = context.timezone
		} else {
			state.timezone = ''
		}
		if (context.mode) {
			state.mode = context.mode
		} else {
			state.mode = 'production'
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
