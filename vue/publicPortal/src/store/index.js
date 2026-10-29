// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Vuex from 'vuex'
import signDocuments from './modules/signDocuments.js'

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		loggedIn: false,
		password: '',
		sessionHolder: '',
		rgpdOptions: false,
	},
	getters: {
		getLoggedIn(state) {
			return state.loggedIn
		},
		getPassword(state) {
			return state.password
		},
		getSessionHolder(state) {
			return state.sessionHolder
		},
		hasRgpdOptions(state) {
			return state.rgpdOptions
		},
	},
	mutations: {
		logIn: (state) => {
			state.loggedIn = true
		},
		logOut: (state) => {
			state.loggedIn = false
			state.password = ''
		},
		setPassword: (state, password) => {
			state.password = password
		},
		setSessionHolder: (state, holder) => {
			state.sessionHolder = holder
		},
		setRgpdOptions: (state, bool) => {
			state.rgpdOptions = bool
		},
	},
	actions: {
	},
	modules: {
		signDocuments,
	},
})
