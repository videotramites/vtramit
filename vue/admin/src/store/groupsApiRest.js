// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import groups from './groups.js'

// actions
const actions = {
	readAll({ commit }) {
		if ((Date.now() - groups.state.timeStamp) > 180000) {
			groups.state.timeStamp = Date.now()
			return axios({
				method: 'GET',
				url: generateUrl('apps/vtramit/web/2.0/context/groups'),
			}).then(
				(result) => {
					// console.log('readAll', result)
					if (result.data?.result && result.data.result === 'OK' && result.data.list) {
						result.data.list.forEach((item) => {
							item.id = item.key
							item.name = item.value
						})
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
		}
	},
}

export default actions
