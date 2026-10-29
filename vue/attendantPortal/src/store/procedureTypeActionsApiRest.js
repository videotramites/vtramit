// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import procedureType from './procedureType.js'

// actions
const actions = {
	readById({ commit }, { id }) {
		if ((Date.now() - procedureType.state.timeStamp) > 180000) {
			procedureType.state.timeStamp = Date.now()
			if (id) {
				return axios({
					method: 'GET',
					url: generateUrl(`apps/vtramit/web/2.0/procedure-type/${id}`),
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
		}
	},

}

export default actions
