// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Vuex from 'vuex'
import procedure from './procedure.js'
import procedureType from './procedureType.js'
import stakeholders from './stakeholders.js'
import procedureStatuses from './procedureStatuses.js'
import signatureStatuses from './signatureStatuses.js'
import channels from './channels.js'
import signDocuments from './signDocuments.js'
import protocolSteps from './protocolSteps.js'

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		rgpdOptions: false,
	},
	getters: {
		hasRgpdOptions(state) {
			return state.rgpdOptions
		},
	},
	mutations: {
		setRgpdOptions: (state, bool) => {
			state.rgpdOptions = bool
		},
	},
	actions: {
	},
	modules: {
		procedure,
		procedureType,
		stakeholders,
		signDocuments,
		procedureStatuses,
		signatureStatuses,
		channels,
		protocolSteps,
	},
})
