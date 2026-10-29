// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import store from '../store/index.js'

/**
 * Handle routes.
 * @param {string} to - to route.
 * @param {string} from - from route.
 * @param {string} next - next route.
 */
function loggedInGuard(to, from, next) {
	// console.log("loggedInGuard begin");
	if (store.state.loggedIn) {
		next()
	} else {
		next({ name: 'login' })
	}
}

export { loggedInGuard }
