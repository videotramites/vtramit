// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Model from '../models/stakeholders.js'

// actions
const actions = {
	readByProcedureId({ commit }, id) {
		if (id) {
			return axios({
				method: 'GET',
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${id}/stakeholder`),
			}).then(
				(result) => {
					if (result.data && result.data.result && result.data.result === 'OK' && result.data.list) {
						commit('readByProcedureIdSuccess', result.data.list)
						return true
					}
					return false
				},
				(error) => {
					// console.error('ERROR readByProcedureId', error)
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
				url: generateUrl(`apps/vtramit/web/2.0/procedure/${forApi.procedureId}/stakeholder`),
				headers: { 'content-type': 'application/json' },
				data: {
					data: forApi,
				},
			}).then(
				(result) => {
					// console.log('update', result)
					if (result.data && result.data.result && result.data.result === 'OK' && result.data.entity) {
						commit('updateOnSuccess', result.data.entity)
						return result.data
					}
					return false
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
			return Promise.reject(new Error('Payload must be an array'))
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
			return Promise.reject(new Error('Not enough parameters'))
		}
	},
}

export default actions
