// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import acceptanceChannels from './acceptanceChannels.js'

// actions
const actions = {
	readAll({ commit }) {
		if ((Date.now() - acceptanceChannels.state.timeStamp) > 180000) {
			acceptanceChannels.state.timeStamp = Date.now()
			return axios({
				method: 'GET',
				url: generateUrl('apps/vtramit/web/2.0/context/attendance-channel'),
			}).then(
				(result) => {
					// console.log('readAll', result)
					if (result.data?.result && result.data.result === 'OK' && result.data.list) {
						// Converting the format of the list to the format we use to process
						// the data we store.
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
