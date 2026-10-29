// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

// actions
const actions = {
	readContext({ commit }) {
		return axios({
			method: 'GET',
			url: generateUrl('apps/vtramit/web/2.0/context/app'),
		}).then(
			(result) => {
				if (result.data && result.data.result && result.data.result === 'OK') {
					commit('readContextSuccess', result.data)

					if (result.data.groups && result.data.groups.length) {
						// TODO: clean groups if not present?
						const tempGroups = result.data.groups.map((item) => {
							return {
								id: item.key,
								name: item.value,
							}
						})
						commit('groups/readAllSuccess', tempGroups, { root: true })
					}

					if (result.data.users && result.data.users.length) {
						// TODO: clean users if not present?
						const tempUsers = result.data.users.map((item) => {
							return {
								id: item.key,
								name: item.value,
							}
						})
						commit('users/readAllSuccess', tempUsers, { root: true })
					}
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
