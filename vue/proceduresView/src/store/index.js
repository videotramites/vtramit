// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Vuex from 'vuex'
import appStatus from './appStatus.js'
import context from './context.js'
import statuses from './statuses.js'
import channels from './channels.js'
import groups from './groups.js'
import users from './users.js'
import procedureTypes from './procedureTypes.js'
import procedures from './procedures.js'
import stakeholders from './stakeholders.js'

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		loggedIn: false,
		password: '',
		sessionHolder: '',
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
	},
	actions: {
	},
	modules: {
		appStatus,
		context,
		statuses,
		channels,
		groups,
		users,
		procedureTypes,
		procedures,
		stakeholders,
	},
})
