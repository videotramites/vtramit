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
import store from './store/index.js'
import { Tooltip } from '@nextcloud/vue'
import './styles/common.scss'
import './styles/componentToggle.css'

// eslint-disable-next-line import/no-unresolved
import CKEditor from '@ckeditor/ckeditor5-vue2'

import { translate, translatePlural } from '@nextcloud/l10n'

import GeneralPage from './views/GeneralPage.vue'
import IntegrationsPage from './views/IntegrationsPage.vue'
import ProceduresPage from './views/ProceduresPage.vue'
import PublicPage from './views/PublicPage.vue'
import SignaturesPage from './views/SignaturesPage.vue'

Vue.prototype.t = window.t || translate
Vue.prototype.n = window.n || translatePlural
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA
Vue.directive('tooltip', Tooltip)
Vue.use(CKEditor)

// Detect which container is present and mount the correct component
const urlParams = new URLSearchParams(window.location.search)
const tab = urlParams.get('tab') || 'general'

let RootComponent = GeneralPage
if (tab === 'integrations') {
	RootComponent = IntegrationsPage
}
if (tab === 'procedures') {
	RootComponent = ProceduresPage
}
if (tab === 'public') {
	RootComponent = PublicPage
}
if (tab === 'signatures') {
	RootComponent = SignaturesPage
}

const vueInstance = new Vue({
	el: '#q-admin',
	store,
	render: (h) => h(RootComponent),
})

export default vueInstance
