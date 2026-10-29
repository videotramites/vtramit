// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import procedureTypeGroups from './procedureTypeGroups.js'

// actions
const actions = {
	readAll({ commit }) {
		procedureTypeGroups.state.timeStamp = Date.now()
		return axios({
			method: 'GET',
			url: generateUrl(`/apps/vtramit/web/2.0/procedure-type-group`),
		}).then(
			(result) => {
				// console.log('readAll', result)
				if (result.data?.result && result.data.result === 'OK' && result.data.list) {
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
}

export default actions
