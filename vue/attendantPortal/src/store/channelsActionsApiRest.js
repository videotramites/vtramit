// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

// actions
const actions = {
	readAll({ commit }) {
		return axios({
			method: 'GET',
			url: generateUrl('apps/vtramit/web/2.0/context/attendance-channel'),
		}).then(
			(result) => {
				// console.log('readAll', result)
				if (result.data && result.data.result && result.data.result === 'OK' && result.data.list) {
					// Converting the format of the list to the format we use to process
					// the data we store.
					// const dataKeys = Object.keys(result.data.list)
					// const dataList = []
					// dataKeys.forEach((key) => {
					// dataList.push({
					// id: parseInt(key),
					// name: result.data.list[key],
					// })
					// })
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
	},
}

export default actions
