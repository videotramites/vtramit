// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Model from '../models/procedure.js'

// actions
const actions = {
	readById({ commit }, { id }) {
		if (id) {
			return axios({
				method: 'GET',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${id}`),
			}).then(
				(result) => {
					if (result.data && result.data.result && result.data.result === 'OK' && result.data.entity) {
						commit('readByIdSuccess', result.data.entity)
						return true
					}
					return false
				},
				(error) => {
					// console.error('ERROR readById', error)
					throw error
				},
			)
		} else {
			return Promise.reject(new Error('Not enough parameters'))
		}
	},

	readProcedureInfoById({ commit, rootGetters }, id) {
		if (id) {
			return axios({
				method: 'GET',
				url: generateUrl(`apps/vtramit/web/2.0/context/procedure/${id}`),
			}).then(
				(result) => {
					if (result.data && result.data.result && result.data.result === 'OK') {
						// Storing the attendance channels for this procedure.
						if (result.data.channels && result.data.channels.length) {
							const channels = rootGetters['channels/readAll']
							const tempChannels = result.data.channels.map((itemId) => {
								const channel = channels.find((itemChannel) => {
									return itemChannel.id === itemId
								})
								if (channel) {
									return {
										id: channel.id,
										name: channel.name,
										filterable: channel.filterable,
									}
								} else {
									return null
								}
							})
							commit('procedure/readAllowedChannelsAllSuccess', tempChannels, { root: true })
						} else {
							commit('procedure/readAllowedChannelsAllSuccess', [], { root: true })
						}

						// Storing the allowed statuses for this procedure.
						if (result.data.transitions && result.data.transitions.length) {
							const statuses = rootGetters['procedureStatuses/readAll']
							const tempStatuses = result.data.transitions.map((itemId) => {
								const status = statuses.find((itemStatus) => {
									return itemStatus.id === itemId
								})
								if (status) {
									return {
										id: status.id,
										name: status.name,
										action: status.action,
										filterable: status.filterable,
										editable: status.editable,
									}
								} else {
									return null
								}
							})
							commit('procedure/readAllowedStatusesAllSuccess', tempStatuses, { root: true })
						} else {
							commit('procedure/readAllowedStatusesAllSuccess', [], { root: true })
						}

						// Storing the signature buttons for this procedure.
						if (result.data.signatureButtons && result.data.signatureButtons.length) {
							commit('procedure/readSignatureButtonsAllSuccess', result.data.signatureButtons, { root: true })
						} else {
							commit('procedure/readSignatureButtonsAllSuccess', [], { root: true })
						}

						return true
					}
					return false
				},
				(error) => {
					// console.error('ERROR readProcedureInfoById', error)
					throw error
				},
			)
		} else {
			return Promise.reject(new Error('Not enough parameters'))
		}
	},

	updateSignature({ commit }, payload) {
		if ('id' in payload && payload.id && 'signatureButtonId' in payload && payload.signatureButtonId) {
			return axios({
				method: 'POST',
				url: generateUrl(`/apps/vtramit/web/2.0/procedure/${payload.id}/signature/initialize/${payload.signatureButtonId}`),
				headers: { 'content-type': 'application/json' },
				data: {},
			}).then(
				(result) => {
					commit('updateOnSuccess', result.data.entity)
					return result.data
				},
				(error) => {
					// console.error('ERROR updateSignature', error)
					throw error
				},
			)
		} else {
			return Promise.reject(new Error('Not enough parameters'))
		}
	},

	// --------------------------------------------------------------------------
	// Updates the comments of an existing procedure.
	// It uses the same endpoint as the general procedure update action.
	//
	// @param {Object} context
	//   Vuex context object.
	// @param {Object} payload
	//   Expected keys:
	//   - id (Number): The procedure ID.
	//   - comments (String): The updated comments text.
	//
	// @returns {Promise<Object>}
	//   Resolves with the updated entity if successful.
	// --------------------------------------------------------------------------
	updateComments({ commit }, payload) {
		if ('id' in payload && payload.id) {
			let forApi = new Model(payload)
			forApi = forApi.filterForApi()
			forApi.filterOutEmptyKeys()

			return axios({
				method: 'PUT',
				url: generateUrl('apps/vtramit/web/2.0/procedure'),
				headers: { 'content-type': 'application/json' },
				data: {
					data: forApi,
				},
			}).then(
				(result) => {
					if (result.data && result.data.result && result.data.result === 'OK') {
						commit('updateOnSuccess', result.data.entity)
						return result.data
					}
					return false
				},
				(error) => {
					// console.error('ERROR updateComments', error)
					throw error
				},
			)
		} else {
			return Promise.reject(new Error('Not enough parameters'))
		}
	},
	update({ commit }, payload) {
		if ('id' in payload && payload.id) {
			let forApi = new Model(payload)
			forApi = forApi.filterForApi()
			forApi.filterOutEmptyKeys()

			return axios({
				method: 'PUT',
				url: generateUrl(`apps/vtramit/web/2.0/procedure`),
				headers: { 'content-type': 'application/json' },
				data: {
					data: forApi,
				},
			}).then(
				(result) => {
					commit('updateOnSuccess', result.data.entity)
					return result.data
				},
				(error) => {
					// console.error('ERROR update', error)
					throw error
				},
			)
		} else {
			return Promise.reject(new Error('Not enough parameters'))
		}
	},
}

export default actions
