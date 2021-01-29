/**
 * @copyright Copyright (c) 2020-2021 Ajuntament de Barcelona
 * 
 * @author Daniel Tamajon <daniel@floss.cat>
 * @author Kenneth Peiruza <kenneth@floss.cat>
 * @author Letizia Benítez <letizia@floss.cat>
 * @author Marta González <gonzalez.marta@gmail.com>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan Reyné <ivanreyne@gmail.com>
 *
 * @license GNU AGPL version 3 or any later version
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
import App from './App'
import router from './router'
import moment from '@nextcloud/moment'

import { translate, translatePlural } from '@nextcloud/l10n'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA
Vue.prototype.t = translate
Vue.prototype.n = translatePlural

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
	el: '#content',
	router,
	render: h => h(App),
})
