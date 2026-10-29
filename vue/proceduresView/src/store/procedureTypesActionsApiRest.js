// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

// actions
const actions = {
	readAll({ commit }) {
		return axios({
			method: 'GET',
			url: generateUrl('apps/vtramit/web/2.0/procedure-type'),
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
	readAllCreate({ commit }) {
		return axios({
			filter: null,
			method: 'POST',
			url: generateUrl('apps/vtramit/web/2.0/procedure-type/search/filter'),
			headers: { 'content-type': 'application/json' },
			data: {
				filter: {
					allowCreate: true,
				},
			},
		}).then(
			(result) => {
				// console.log('readAll', result)
				if (result.data && result.data.result && result.data.result === 'OK' && result.data.list) {
					commit('readAllCreateSuccess', result.data.list)
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
	readProcedureTypeInfoById({ commit, rootGetters }, id) {
		if (id) {
			return axios({
				method: 'GET',
				url: generateUrl(`apps/vtramit/web/2.0/context/procedure-type/${id}`),
			}).then(
				(result) => {
					if (result.data && result.data.result && result.data.result === 'OK') {
						// Storing the groups allowed for this procedure type.
						if (result.data.groups && result.data.groups.length) {
							const tempGroups = result.data.groups.map((item) => {
								return {
									id: item.key,
									name: item.value,
								}
							})
							commit('procedureTypes/readAllowedGroupsAllSuccess', tempGroups, { root: true })
						} else {
							commit('procedureTypes/readAllowedGroupsAllSuccess', [], { root: true })
						}

						// Storing the users allowed for this procedure type.
						if (result.data.groups && result.data.groups.length) {
							const tempUsers = {}
							result.data.groups.forEach(group => {
								group.users.forEach(user => {
									if (tempUsers[user.key] !== undefined) {
										tempUsers[user.key].groupId.push(group.key)
									} else {
										tempUsers[user.key] = {
											id: user.key,
											name: user.value,
											groupId: [group.key],
										}
									}
								})
							})
							commit('procedureTypes/readAllowedUsersAllSuccess', Object.values(tempUsers), { root: true })
						} else {
							commit('procedureTypes/readAllowedUsersAllSuccess', [], { root: true })
						}

						// Storing the attendance channels for this procedure type.
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
									}
								} else {
                  return null
                }
							})
							commit('procedureTypes/readAllowedChannelsAllSuccess', tempChannels, { root: true })
						} else {
							commit('procedureTypes/readAllowedChannelsAllSuccess', [], { root: true })
						}
						if (result.data.allowCreate != null && result.data.allowAssignGroup != null && result.data.allowAssignUser != null && result.data.bypassRequired != null) {
							const tempPermissions = [result.data.allowCreate, result.data.allowAssignGroup, result.data.allowAssignUser, result.data.bypassRequired]
							commit('procedureTypes/readPermissionsSuccess', tempPermissions, { root: true })
						} else {
							commit('procedureTypes/readPermissionsSuccess', [], { root: true })
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
}

export default actions
