// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import configuration from './configuration.js'

// actions
const actions = {
	readConfiguration({ commit }) {
		if ((Date.now() - configuration.state.timeStamp) > 180000) {
			configuration.state.timeStamp = Date.now()
			return axios({
				method: 'GET',
				url: generateUrl('apps/vtramit/web/2.0/configuration'),
			}).then(
				(result) => {
					// console.log('readConfiguration', result)
					if (result.data?.result && result.data.result === 'OK') {
						commit('readConfigurationSuccess', result.data.configuration)
						return true
					}
					return false
				},
				(error) => {
					// console.error('ERROR readConfiguration', error)
					throw error
				},
			)
		}
	},
}

export default actions
