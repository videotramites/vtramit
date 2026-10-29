// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Vuex from 'vuex'
import acceptanceChannels from './acceptanceChannels.js'
import groups from './groups.js'
import mailTemplates from './mailTemplates.js'
import messageChannels from './messageChannels.js'
import messageTemplates from './messageTemplates.js'
import configuration from './configuration.js'
import supportedLanguages from './supportedLanguages.js'
import procedureTypeGroups from './procedureTypeGroups.js'
import signatureTypes from './signatureTypes.js'
import webhooks from './webhooks.js'
import rgpds from './rgpds.js'

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
	},
	getters: {
	},
	mutations: {
	},
	actions: {
	},
	modules: {
		groups,
		mailTemplates,
		acceptanceChannels,
		messageChannels,
		configuration,
		supportedLanguages,
		rgpds,
		procedureTypeGroups,
		signatureTypes,
		webhooks,
		messageTemplates,
	},
})
