// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import actionsApiRest from './configurationActionsApiRest.js'

// initial state
const state = {
	languages: {},
	resultsPerPage: 0,
	filterOption: {},
	filterOverdueDays: 2,
	timeStamp: 0,
	dateFormat: '',
	timeFormat: '',
	datetimeFormat: '',
	admin: '',
	mailsAllowed: false,
	phoneLink: '',
	phonePrefix: '',
	phoneDefaultCountryCode: '',
	publicDocumentationUrl: '',
	publicLogoUrl: '',
	publicLogoLogin: '',
	publicTextExpired: '',
	publicTextFuture: '',
	publicPasswordHide: false,
	publicDownloadsHide: false,
	publicUploadsHide: false,
	publicSignatureHide: false,
	publicOutputAutodownload: false,
}

// getters
const getters = {
	getLanguages(state) {
		return state.languages
	},
	getResultsPerPage(state) {
		return state.resultsPerPage
	},
	getFilterOption(state) {
		return state.filterOption
	},
}

// actions
const actions = actionsApiRest

// mutations
const mutations = {
	readConfigurationSuccess(state, configuration) {
		if (configuration.languages) {
			state.languages = configuration.languages
		}
		if (configuration.resultsPerPage) {
			state.resultsPerPage = configuration.resultsPerPage
		}
		if (configuration.filterOption) {
			state.filterOption = configuration.filterOption
		}
		if (configuration.filterOverdueDays) {
			state.filterOverdueDays = configuration.filterOverdueDays
		}
		if (configuration.timeStamp) {
			state.timeStamp = configuration.timeStamp
		}
		if (configuration.dateFormat) {
			state.dateFormat = configuration.dateFormat
		}
		if (configuration.timeFormat) {
			state.timeFormat = configuration.timeFormat
		}
		if (configuration.datetimeFormat) {
			state.datetimeFormat = configuration.datetimeFormat
		}
		if (configuration.admin) {
			state.admin = configuration.admin
		}
		if (configuration.mailsAllowed) {
			state.mailsAllowed = configuration.mailsAllowed
		}
		if (configuration.phoneLink) {
			state.phoneLink = configuration.phoneLink
		}
		if (configuration.phonePrefix) {
			state.phonePrefix = configuration.phonePrefix
		}
		if (configuration.phoneDefaultCountryCode) {
			state.phoneDefaultCountryCode = configuration.phoneDefaultCountryCode
		}
		if (configuration.publicDocumentationUrl) {
			state.publicDocumentationUrl = configuration.publicDocumentationUrl
		}
		if (configuration.publicLogoUrl) {
			state.publicLogoUrl = configuration.publicLogoUrl
		}
		if (configuration.publicLogoLogin) {
			state.publicLogoLogin = configuration.publicLogoLogin
		}
		if (configuration.publicTextExpired) {
			state.publicTextExpired = configuration.publicTextExpired
		}
		if (configuration.publicTextFuture) {
			state.publicTextFuture = configuration.publicTextFuture
		}
		if (configuration.publicPasswordHide) {
			state.publicPasswordHide = configuration.publicPasswordHide
		}
		if (configuration.publicDownloadsHide) {
			state.publicDownloadsHide = configuration.publicDownloadsHide
		}
		if (configuration.publicUploadsHide) {
			state.publicUploadsHide = configuration.publicUploadsHide
		}
		if (configuration.publicSignatureHide) {
			state.publicSignatureHide = configuration.publicSignatureHide
		}
		if (configuration.publicOutputAutodownload) {
			state.publicOutputAutodownload = configuration.publicOutputAutodownload
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
