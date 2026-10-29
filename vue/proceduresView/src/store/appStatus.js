// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

// initial state
const state = {
	showNavigation: false,
	showSidebar: false,
	forceProcedureListRefresh: 0,
}

// getters
const getters = {
	getShowNavigation(state) {
		return state.showNavigation
	},
	getShowSidebar(state) {
		return state.showSidebar
	},
	getForceProcedureListRefresh(state) {
		return state.forceProcedureListRefresh
	},
}

// actions
const actions = {}

// mutations
const mutations = {
	setShowNavigation(state, showNavigation) {
		state.showNavigation = showNavigation
	},
	setShowSidebar(state, showSidebar) {
		state.showSidebar = showSidebar
	},
	setForceProcedureListRefresh(state) {
		state.forceProcedureListRefresh++
	},
}

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
}
