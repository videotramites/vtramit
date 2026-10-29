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
import { generateUrl } from '@nextcloud/router'

import ProceduresCreate from '../components/procedures/ProceduresCreate.vue'
import ProceduresShow from '../components/procedures/ProceduresShow.vue'
import ProceduresEdit from '../components/procedures/ProceduresEdit.vue'
import ProceduresList from '../components/views/ProceduresList.vue'

Vue.use(VueRouter)

export default new VueRouter({
	mode: 'hash',
	base: generateUrl('/apps/vtramit/procedures'),
	linkActiveClass: 'active',
	scrollBehavior() {
		return { x: 0, y: 0 }
	},
	routes: [
		{
			path: '/',
			name: 'dashboard',
			// redirect: { name: 'procedures-list' },
			component: ProceduresList,
		},
		{
			path: '/new',
			name: 'procedures-new',
			components: {
				default: ProceduresList,
				sidebar: ProceduresCreate,
			},
		},
		{
			path: '/:id',
			name: 'procedures-show',
			components: {
				default: ProceduresList,
				sidebar: ProceduresShow,
			},
		},
		{
			path: '/:id/edit',
			name: 'procedures-edit',
			components: {
				default: ProceduresList,
				sidebar: ProceduresEdit,
			},
		},
		{
			path: '*',
			redirect: { name: 'dashboard' },
		},
	],
})
