/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia Benítez <letizia@videoatencion.com>
 * @author Daniel Tamajon <dtamajon@litesolutions.es>
 * @author Marta González <marta.gonzalez@litesolutions.es>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan Reyné <ivanreyne@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

import Vue from 'vue'
import VueRouter from 'vue-router'
// import { generateUrl } from '@nextcloud/router'
import LoginPage from '../modules/auth/pages/LoginPage.vue'
import ClosePage from '../modules/auth/pages/ClosePage.vue'
import PublicDashboardPage from '../modules/dashboard/pages/PublicDashboardPage.vue'
import { loggedInGuard } from './routeGuards.js'

Vue.use(VueRouter)

export default new VueRouter({
	mode: 'hash',
	routes: [
		{
			path: '/login',
			name: 'login',
			// beforeEnter: loggedOutGuard,
			component: LoginPage,
		},
		{
			path: '/signature-finished',
			alias: '/close',
			name: 'signature-finished',
			// beforeEnter: loggedOutGuard,
			component: ClosePage,
		},
		{
			path: '/',
			name: 'dashboard',
			beforeEnter: loggedInGuard,
			component: PublicDashboardPage,
		},
		{
			path: '*',
			redirect: { name: 'dashboard' },
		},
	],
})
