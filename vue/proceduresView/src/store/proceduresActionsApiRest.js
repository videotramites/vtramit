// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Model from '../models/procedures.js'

// actions
const actions = {
	create({ commit }, payload) {
		let forApi = new Model(payload)
		forApi = forApi.filterForApi()
		forApi.filterOutEmptyKeys()
		return axios({
			method: 'POST',
			url: generateUrl('apps/vtramit/web/2.0/procedure'),
			headers: { 'content-type': 'application/json' },
			data: {
				data: forApi,
			},
		}).then(
			(result) => {
				// console.log('create', result)
				commit('createOnSuccess', result.data)
				return result.data
			},
			(error) => {
				// console.error('ERROR create', error);
				throw error
			},
		)
	},
	readAll({ commit }, payload) {
		const filter = {}
		if (payload.newFilter.freeSearch) {
			filter.freeSearch = payload.newFilter.freeSearch
		}
		if (payload.newFilter.dueDate) {
			filter.dueDate = payload.newFilter.dueDate
		}
		if (payload.newFilter.statuses) {
			filter.status = payload.newFilter.statuses
		}
		if (payload.newFilter.groups) {
			filter.groups = payload.newFilter.groups
		}
		if (payload.newFilter.users) {
			filter.users = payload.newFilter.users
		}
		if (payload.newFilter.unassigned) {
			filter.users.push('unassigned')
		}
		if (payload.newFilter.dueDateFrom) {
			filter.dueDateFrom = payload.newFilter.dueDateFrom
		}
		if (payload.newFilter.dueDateTo) {
			filter.dueDateTo = payload.newFilter.dueDateTo
		}
		if (payload.newFilter.stakeholderName) {
			filter.stakeholderName = payload.newFilter.stakeholderName
		}
		if (payload.newFilter.stakeholderCitizenId) {
			filter.stakeholderCitizenId = payload.newFilter.stakeholderCitizenId
		}
		if (payload.newFilter.stakeholderPhone) {
			filter.stakeholderPhone = payload.newFilter.stakeholderPhone
		}
		if (payload.newFilter.stakeholderEmail) {
			filter.stakeholderEmail = payload.newFilter.stakeholderEmail
		}
		const requestedPage = payload.pageNumber === false ? 1 : payload.pageNumber
		const isDownloadRequest = payload.isDownloadRequested

		return axios({
			method: 'POST',
			url: generateUrl('apps/vtramit/web/2.0/procedure/search/filter'),
			headers: { 'content-type': 'application/json', accept: 'text/csv' },
			data: {
				filter,
				requestedPage,
				isDownloadRequest,
			},
		}).then(
			(result) => {
				// console.log('readAll', result)
				if (result.data && result.data.result && result.data.result === 'OK' && result.data.list) {
					commit('readAllSuccess', result.data)
					return true
				} else if (result.data && result.headers.csvdownload === 'true' && result.status === 200) {
					const response = result
					commit('downloadCsvSuccess', response)
					return true
				}
				return false
			},
			(error) => {
				// console.error('ERROR readAll', error)
				throw error
			},
		)
	},
	readById({ commit }, id) {
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
			return new Promise((resolve, reject) => {
				reject(new Error('Not enough parameters'))
			})
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
					commit('updateOnSuccess', result.data)
					return result.data
				},
				(error) => {
					// console.error('ERROR update', error)
					throw error
				},
			)
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Not enough parameters'))
			})
		}
	},
	delete({ commit }, id) {
		if (id) {
			return axios({
				method: 'DELETE',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${id}`),
			}).then(
				(/* result */) => {
					// console.log('delete', result)
					commit('deleteOnSuccess', id)
					return true
				},
				(error) => {
					// console.error('ERROR delete', error)
					throw error
				},
			)
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Not enough parameters'))
			})
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
							commit('procedures/readAllowedChannelsAllSuccess', tempChannels, { root: true })
						} else {
							commit('procedures/readAllowedChannelsAllSuccess', [], { root: true })
						}

						// Storing the allowed statuses for this procedure.
						if (result.data.transitions && result.data.transitions.length) {
							const statuses = rootGetters['statuses/readAll']
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
							commit('procedures/readAllowedStatusesAllSuccess', tempStatuses, { root: true })
						} else {
							commit('procedures/readAllowedStatusesAllSuccess', [], { root: true })
						}

						// Storing the signature buttons for this procedure.
						if (result.data.signatureButtons && result.data.signatureButtons.length) {
							commit('procedures/readSignatureButtonsAllSuccess', result.data.signatureButtons, { root: true })
						} else {
							commit('procedures/readSignatureButtonsAllSuccess', [], { root: true })
						}

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
			return new Promise((resolve, reject) => {
				reject(new Error('Not enough parameters'))
			})
		}
	},
	readActiveVideoconferences({ commit }) {
		return axios({
			method: 'GET',
			url: generateUrl('apps/vtramit/web/2.0/procedure/videoconference/op/waiting'),
		}).then(
			(result) => {
				// console.log('readActiveVideoconferences', result)
				if (result.data && result.data.result && result.data.result === 'OK' && result.data.list) {
					commit('readActiveVideconferencesAllSuccess', result.data.list)
					return true
				}
				return false
			},
			(error) => {
				// console.error('ERROR readActiveVideoconferences', error)
				throw error
			},
		)
	},
	readFilteredSignatures({ commit }, payload) {
		const filter = {}
		if (payload.newFilter.freeSearch) {
			filter.freeSearch = payload.newFilter.freeSearch
		}
		if (payload.newFilter.dueDate) {
			filter.dueDate = payload.newFilter.dueDate
		}
		if (payload.newFilter.statuses) {
			filter.status = payload.newFilter.statuses
		}
		if (payload.newFilter.groups) {
			filter.groups = payload.newFilter.groups
		}
		if (payload.newFilter.users) {
			filter.users = payload.newFilter.users
		}
		if (payload.newFilter.unassigned) {
			filter.users.push('unassigned')
		}
		if (payload.newFilter.dueDateFrom) {
			filter.dueDateFrom = payload.newFilter.dueDateFrom
		}
		if (payload.newFilter.dueDateTo) {
			filter.dueDateTo = payload.newFilter.dueDateTo
		}
		if (payload.newFilter.stakeholderName) {
			filter.stakeholderName = payload.newFilter.stakeholderName
		}
		if (payload.newFilter.stakeholderCitizenId) {
			filter.stakeholderCitizenId = payload.newFilter.stakeholderCitizenId
		}
		if (payload.newFilter.stakeholderPhone) {
			filter.stakeholderPhone = payload.newFilter.stakeholderPhone
		}
		if (payload.newFilter.stakeholderEmail) {
			filter.stakeholderEmail = payload.newFilter.stakeholderEmail
		}

		return axios({
			method: 'POST',
			url: generateUrl('apps/vtramit/web/2.0/signature/search/filter'),
			headers: { 'content-type': 'application/json' },
			data: {
				filter,
			},
		}).then(
			(result) => {
				// console.log('readActiveVideoconferences', result)
				if (result.data && result.data.result && result.data.result === 'OK' && result.data.list) {
					commit('readFilteredSignaturesAllSuccess', result.data.list)
					return true
				}
				return false
			},
			(error) => {
				// console.error('ERROR readActiveVideoconferences', error)
				throw error
			},
		)
	},
	updateSignature({ commit }, payload) {
		if ('id' in payload && payload.id && 'signatureButtonId' in payload && payload.signatureButtonId) {
			return axios({
				method: 'POST',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${payload.id}/signature/initialize/${payload.signatureButtonId}`),
				headers: { 'content-type': 'application/json' },
				data: {},
			}).then(
				(result) => {
					commit('updateOnSuccess', result.data)
					return result.data
				},
				(error) => {
					// console.error('ERROR update', error)
					throw error
				},
			)
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Not enough parameters'))
			})
		}
	},
	startVideoconference({ commit }, payload) {
		if ('jitsiRoomCode' in payload && payload.jitsiRoomCode) {
			return axios({
				method: 'POST',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/videoconference/${payload.jitsiRoomCode}/started`),
				headers: { 'content-type': 'application/json' },
				data: {
				},
			}).then(
				(result) => {
					commit('updateOnSuccess', result.data)
					return result.data
				},
				(error) => {
					// console.error('ERROR update', error)
					throw error
				},
			)
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Not enough parameters'))
			})
		}
	},
}

export default actions
