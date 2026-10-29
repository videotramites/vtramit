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

// Hold this import in the first line to apply paths properly
import './public-path.js'

import Vue from 'vue'
import App from './App.vue'
import moment from '@nextcloud/moment'
import router from './router/index.js'
import store from './store/index.js'
import vuetify from './plugins/vuetify.js'

import { translate, translatePlural } from '@nextcloud/l10n'

Vue.prototype.t = window.t || translate
Vue.prototype.n = window.n || translatePlural
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA

Vue.filter('formatDate', function(value) {
	if (value) {
		return moment(value * 1000).format('DD/MM/YYYY')
	}
})
Vue.filter('formatTime', function(value) {
	if (value) {
		return moment(value * 1000).format('HH:mm')
	}
})

export default new Vue({
	el: '#q-attendantPortal',
	router,
	store,
	vuetify,
	render: h => h(App),
})
