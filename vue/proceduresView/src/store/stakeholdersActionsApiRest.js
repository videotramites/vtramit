// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Model from '../models/stakeholders.js'

// actions
const actions = {
	create({ commit }, payload) {
		if (payload.procedureId) {
			let forApi = new Model(payload)
			forApi = forApi.filterForApi()
			forApi.filterOutEmptyKeys()
			return axios({
				method: 'POST',
				// url: generateUrl(`apps/vtramit/web/2.0/procedure-stakeholder`),
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${forApi.procedureId}/stakeholder`),
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
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Missing procedure ID'))
			})
		}
	},
	createBatch({ commit }, payload) {
		if (Array.isArray(payload)) {
			const payloadForApi = []
			let procedureId = null
			payload.forEach((item) => {
				let forApi = new Model(item)
				forApi = forApi.filterForApi()
				forApi.filterOutEmptyKeys()
				procedureId = forApi.procedureId
				payloadForApi.push(forApi)
			})
			return axios({
				method: 'POST',
				// url: generateUrl(`apps/vtramit/web/2.0/procedure-stakeholder`),
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${procedureId}/stakeholder`),
				headers: { 'content-type': 'application/json' },
				data: {
					data: payloadForApi,
				},
			}).then(
				(result) => {
					// console.log('createBatch', result)
					commit('genericOnSuccess', result.data)
					return result.data
				},
				(error) => {
					// console.error('ERROR create', error);
					throw error
				},
			)
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Payload must be an array'))
			})
		}
	},
	readAll({ commit }, newFilter) {
		const filter = {}
		if (newFilter.freeSearch) {
			filter.freeSearch = newFilter.freeSearch
		}
		if (newFilter.dueDate) {
			filter.dueDate = newFilter.dueDate
		}
		if (newFilter.statuses) {
			filter.status = newFilter.statuses
		}
		if (newFilter.groups) {
			filter.groups = newFilter.groups
		}
		if (newFilter.users) {
			filter.users = newFilter.users
		}
		if (newFilter.unassigned) {
			filter.unassigned = newFilter.unassigned
		}
		if (newFilter.dueDateFrom) {
			filter.dueDateFrom = newFilter.dueDateFrom
		}
		if (newFilter.dueDateTo) {
			filter.dueDateTo = newFilter.dueDateTo
		}
		if (newFilter.stakeholderName) {
			filter.stakeholderName = newFilter.stakeholderName
		}
		if (newFilter.stakeholderCitizenId) {
			filter.stakeholderCitizenId = newFilter.stakeholderCitizenId
		}
		if (newFilter.stakeholderPhone) {
			filter.stakeholderPhone = newFilter.stakeholderPhone
		}
		if (newFilter.stakeholderEmail) {
			filter.stakeholderEmail = newFilter.stakeholderEmail
		}

		return axios({
			method: 'POST',
			url: generateUrl('apps/vtramit/web/2.0/procedure-stakeholder/search/filter'),
			headers: { 'content-type': 'application/json' },
			data: {
				filter,
			},
		}).then(
			(result) => {
				// console.log('readAll', result)
				if (result.data && result.data.result && result.data.result === 'OK' && result.data.list) {
					commit('readAllSuccess', result.data.list)
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
	// readById({ commit }, id) {
	// if (id) {
	// return axios({
	// method: 'GET',
	// url: generateUrl(`apps/vtramit/web/2.0/procedure/${id}`),
	// }).then(
	// (result) => {
	// if (result.data && result.data.result && result.data.result === 'OK' && result.data.entity) {
	// commit('readByIdSuccess', result.data.entity)
	// return true
	// }
	// return false
	// },
	// (error) => {
	// // console.error('ERROR readById', error)
	// throw error
	// }
	// )
	// } else {
	// return new Promise((resolve, reject) => {
	// reject(new Error('Not enough parameters'))
	// })
	// }
	// },
	readSidebarByProcedureId({ commit }, id) {
		if (id) {
			return axios({
				method: 'GET',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${id}/stakeholder`),
			}).then(
				(result) => {
					if (result.data && result.data.result && result.data.result === 'OK' && result.data.list) {
						commit('readSidebarSuccess', result.data.list)
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
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${forApi.procedureId}/stakeholder`),
				headers: { 'content-type': 'application/json' },
				data: {
					data: forApi,
				},
			}).then(
				(result) => {
					// console.log('update', result)
					commit('updateOnSuccess', result.data)
					return true
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
	updateBatch({ commit }, payload) {
		if (Array.isArray(payload)) {
			const payloadForApi = []
			let procedureId = null
			payload.forEach((item) => {
				let forApi = new Model(item)
				forApi = forApi.filterForApi()
				forApi.filterOutEmptyKeys()
				procedureId = forApi.procedureId
				payloadForApi.push(forApi)
			})
			return axios({
				method: 'PUT',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${procedureId}/stakeholder`),
				headers: { 'content-type': 'application/json' },
				data: {
					data: payloadForApi,
				},
			}).then(
				(result) => {
					// console.log('updateBatch', result)
					commit('genericOnSuccess', result.data)
					return result.data
				},
				(error) => {
					// console.error('ERROR update', error);
					throw error
				},
			)
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Payload must be an array'))
			})
		}
	},
	delete({ commit }, payload) {
		if (payload.procedureId && payload.id) {
			return axios({
				method: 'DELETE',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${payload.procedureId}/stakeholder/${payload.id}`),
			}).then(
				(/* result */) => {
					// console.log('delete', result)
					commit('deleteOnSuccess', payload.id)
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
	deleteBatch({ commit }, payload) {
		if (Array.isArray(payload)) {
			const payloadForApi = []
			let procedureId = null
			payload.forEach((item) => {
				payloadForApi.push(item.id)
				procedureId = item.procedureId
			})
			return axios({
				method: 'DELETE',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${procedureId}/stakeholder`),
				headers: { 'content-type': 'application/json' },
				data: {
					data: payloadForApi,
				},
			}).then(
				(result) => {
					// console.log('deleteBatch', result)
					commit('genericOnSuccess', result.data)
					return result.data
				},
				(error) => {
					// console.error('ERROR delete', error);
					throw error
				},
			)
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Payload must be an array'))
			})
		}
	},
	deleteByProcedureId({ commit }, procedureId) {
		if (procedureId) {
			return axios({
				method: 'DELETE',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${procedureId}/stakeholder`),
			}).then(
				(/* result */) => {
					// console.log('delete', result)
					// TODO: do we get a list of the stakeholder deleted ... do we needed it?
					commit('deleteOnSuccess', null)
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
	sendEmail({ commit }, payload) {
		if (payload.procedureId && payload.id) {
			return axios({
				method: 'POST',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${payload.procedureId}/stakeholder/${payload.id}/op/email`),
				headers: { 'content-type': 'application/json' },
				data: {},
			}).then(
				(result) => {
					// console.log('create', result)
					return result.data
				},
				(error) => {
					// console.error('ERROR create', error);
					throw error
				},
			)
		} else {
			return new Promise((resolve, reject) => {
				reject(new Error('Not enough parameters'))
			})
		}
	},
	// ------------------------------------------------------------------------
	// Fetch fullEntryPage for a stakeholder from API
	fetchEntryPage({ state, commit }, id) {
		// Validate input
		if (!id) {
			return Promise.reject(new Error('Missing stakeholder ID'))
		}

		// If we already have the URL, avoid a new request
		if (state.itemsSidebar?.[id]?.fullEntryPage) {
			return Promise.resolve({
				result: 'OK',
				fullEntryPage: state.itemsSidebar[id].fullEntryPage,
			})
		}

		// If a request is in-flight for this id, avoid duplicates
		if (state.itemsSidebar?.[id]?.loadingEntryPage) {
			return Promise.resolve({ result: 'PENDING' })
		}

		// Mark as loading
		commit('setEntryPageLoading', { id, loading: true })

		// Do the request
		return axios({
			method: 'GET',
			url: generateUrl(`apps/vtramit/api/2.0/procedure-stakeholder/${id}/entry-page`),
		}).then(
			(result) => {
				// Expected payload:
				// { "result": "OK", "fullEntryPage": "https://..."}
				if (result?.data?.result === 'OK' && result?.data?.fullEntryPage) {
					commit('setEntryPageSuccess', { id, fullEntryPage: result.data.fullEntryPage })
					return result.data
				}
				// Do not overwrite with null; keep previous if any
				throw new Error('Invalid response while fetching entry page')
			},
			(error) => {
				throw error
			},
		).finally(() => {
			// Clear loading flag
			commit('setEntryPageLoading', { id, loading: false })
		})
	},
}

export default actions
