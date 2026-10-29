// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import Model from '../models/procedureTypes.js'
import GroupsModel from '../models/groups.js'
import UsersModel from '../models/users.js'
import ChannelsModel from '../models/channels.js'
import actionsApiRest from './procedureTypesActionsApiRest.js'

// initial state
const state = {
	itemsKeys: [],
	items: {},

	// filter items only allowCreate
	itemsKeysCreate: [],
	itemsCreate: {},

	// Info for the specific procedure type we are displaying in edit/show view.
	// Note that this means we can only show one procedure type full info at a
	// time.
	allowedGroupsKeys: [],
	allowedGroups: {},
	allowedUsersKeys: [],
	allowedUsers: {},
	allowedChannelsKeys: [],
	allowedChannels: {},
	allowCreate: false,
	allowAssignGroup: false,
	allowAssignUser: false,
	bypassRequired: false,
}

// getters
const getters = {
	// Procedure types
	// --------------------------------------------------------------------------
	readAll(state) {
		return state.itemsKeys
			.filter((id) => {
				if (id && state.items[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.items[id]
			})
	},
	readById(state) {
		return (id) => {
			if (id === undefined) {
				return new Model()
			} else {
				if (!(id in state.items)) {
					Vue.set(state.items, id, new Model())
				}
				return state.items[id]
			}
		}
	},
	readAllCreate(state) {
		return state.itemsKeysCreate
			.filter((id) => {
				if (id && state.itemsCreate[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.itemsCreate[id]
			})
	},
	// Allowed groups
	// --------------------------------------------------------------------------
	readAllowedGroupsAll(state) {
		return state.allowedGroupsKeys
			.filter((id) => {
				if (id && state.allowedGroups[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.allowedGroups[id]
			})
	},
	readAllowedGroupsById(state) {
		return (id) => {
			if (id === undefined) {
				return new GroupsModel()
			} else {
				if (!(id in state.allowedGroups)) {
					Vue.set(state.allowedGroups, id, new GroupsModel())
				}
				return state.allowedGroups[id]
			}
		}
	},
	// Allowed users
	// --------------------------------------------------------------------------
	readAllowedUsersAll(state) {
		return state.allowedUsersKeys
			.filter((id) => {
				if (id && state.allowedUsers[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.allowedUsers[id]
			})
	},
	readAllowedUsersById(state) {
		return (id) => {
			if (id === undefined) {
				return new UsersModel()
			} else {
				if (!(id in state.allowedUsers)) {
					Vue.set(state.allowedUsers, id, new UsersModel())
				}
				return state.allowedUsers[id]
			}
		}
	},
	readAllowedUsersByGroupId(state) {
		return (groupId) => {
			return state.allowedUsersKeys
				.filter((id) => {
					if (id && state.allowedUsers[id].groupId.indexOf(groupId) !== -1 && state.allowedUsers[id].temp === false) {
						return true
					}
					return false
				})
				.map((id) => {
					return state.allowedUsers[id]
				})
		}
	},
	// Allowed channels
	// --------------------------------------------------------------------------
	readAllowedChannelsAll(state) {
		return state.allowedChannelsKeys
			.filter((id) => {
				if (id && state.allowedChannels[id].temp === false) {
					return true
				}
				return false
			})
			.map((id) => {
				return state.allowedChannels[id]
			})
	},
	readAllowedChannelsById(state) {
		return (id) => {
			if (id === undefined) {
				return new ChannelsModel()
			} else {
				if (!(id in state.allowedChannels)) {
					Vue.set(state.allowedChannels, id, new ChannelsModel())
				}
				return state.allowedChannels[id]
			}
		}
	},
	// Permissions
	// --------------------------------------------------------------------------
	allowCreate(state) {
		return state.allowCreate
	},
	allowAssignGroup(state) {
		return state.allowAssignGroup
	},
	allowAssignUser(state) {
		return state.allowAssignUser
	},
	bypassRequired(state) {
		return state.bypassRequired
	},
}

// actions
const actions = actionsApiRest

// mutations
const mutations = {
	// Procedure types
	// --------------------------------------------------------------------------
	readAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new Model(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.items,
					forStore.id,
					Object.assign(new Model(), state.items[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.itemsKeys.indexOf(forStore.id) < 0) {
					state.itemsKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.itemsKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.items, toDelete[i])
			state.itemsKeys.splice(state.itemsKeys.indexOf(toDelete[i]), 1)
		}
	},
	readAllCreateSuccess(state, itemsCreate) {
		const readAllKeys = []
		for (let i = 0; i < itemsCreate.length; i++) {
			if ('id' in itemsCreate[i] && itemsCreate[i].id) {
				let forStore = new Model(itemsCreate[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.itemsCreate,
					forStore.id,
					Object.assign(new Model(), state.itemsCreate[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.itemsKeysCreate.indexOf(forStore.id) < 0) {
					state.itemsKeysCreate.push(forStore.id)
				}
			}
		}

		const toDelete = state.itemsKeysCreate.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.itemsCreate, toDelete[i])
			state.itemsKeysCreate.splice(state.itemsKeysCreate.indexOf(toDelete[i]), 1)
		}
	},
	// Allowed groups
	// --------------------------------------------------------------------------
	readAllowedGroupsAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new GroupsModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.allowedGroups,
					forStore.id,
					Object.assign(new GroupsModel(), state.allowedGroups[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.allowedGroupsKeys.indexOf(forStore.id) < 0) {
					state.allowedGroupsKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.allowedGroupsKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.allowedGroups, toDelete[i])
			state.allowedGroupsKeys.splice(state.allowedGroupsKeys.indexOf(toDelete[i]), 1)
		}
	},
	// Allowed users
	// --------------------------------------------------------------------------
	readAllowedUsersAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new UsersModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.allowedUsers,
					forStore.id,
					Object.assign(new UsersModel(), state.allowedUsers[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.allowedUsersKeys.indexOf(forStore.id) < 0) {
					state.allowedUsersKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.allowedUsersKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.allowedUsers, toDelete[i])
			state.allowedUsersKeys.splice(state.allowedUsersKeys.indexOf(toDelete[i]), 1)
		}
	},
	// Channels
	// --------------------------------------------------------------------------
	readAllowedChannelsAllSuccess(state, items) {
		const readAllKeys = []
		for (let i = 0; i < items.length; i++) {
			if ('id' in items[i] && items[i].id) {
				let forStore = new ChannelsModel(items[i])
				forStore = forStore.filterForStore()

				Vue.set(
					state.allowedChannels,
					forStore.id,
					Object.assign(new ChannelsModel(), state.allowedChannels[forStore.id], forStore, {
						temp: false,
					}),
				)
				readAllKeys.push(forStore.id)
				if (state.allowedChannelsKeys.indexOf(forStore.id) < 0) {
					state.allowedChannelsKeys.push(forStore.id)
				}
			}
		}

		const toDelete = state.allowedChannelsKeys.filter(function(key) {
			return readAllKeys.indexOf(key) < 0
		})
		for (let i = 0; i < toDelete.length; i++) {
			Vue.delete(state.allowedChannels, toDelete[i])
			state.allowedChannelsKeys.splice(state.allowedChannelsKeys.indexOf(toDelete[i]), 1)
		}
	},
	readPermissionsSuccess(state, items) {
		state.allowCreate = items[0]
		state.allowAssignGroup = items[1]
		state.allowAssignUser = items[2]
		state.bypassRequired = items[3]
	},
}

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
}
