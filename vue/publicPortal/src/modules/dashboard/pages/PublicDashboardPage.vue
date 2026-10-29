<!--
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
 -->

<template>
	<v-app style="width:100%;">
		<v-app-bar v-if="hasLogo && !$vuetify.breakpoint.mobile && !hideLogo" app color="#fff">
			<div class="flex-grow-1" />
			<img v-if="hasLogo" :src="config.logoUrl" height="100%">
			<div class="flex-grow-1" />
		</v-app-bar>

		<v-main>
			<v-container fluid class="d-flex flex-column align-stretch pa-0" style="height:100%;">
				<DashboardMobile
					v-if="$vuetify.breakpoint.mobile"
					:appointment="appointment"
					:config="config"
					:stakeholder="stakeholder"
					:rgpd-options="rgpdOptions" />

				<DashboardDesktop
					v-else
					:appointment="appointment"
					:config="config"
					:stakeholder="stakeholder"
					:rgpd-options="rgpdOptions"
					@fullscreen-change="hideLogo = $event" />
			</v-container>
		</v-main>
	</v-app>
</template>

<script>
import DashboardMobile from '../components/DashboardMobile.vue'
import DashboardDesktop from '../components/DashboardDesktop.vue'

export default {
	name: 'PublicDashboardPage',
	components: {
		DashboardMobile,
		DashboardDesktop,
	},
	data() {
		return {
			appointment: {},
			config: {},
			stakeholder: {},
			rgpdOptions: [],
			hideLogo: false,
		}
	},
	computed: {
		hasLogo() {
			return !!(this.config.logoUrl)
		},
	},
	created() {
		const el = document.getElementsByName('server-data')[0]
		if (!el) return
		const serverData = JSON.parse(el.innerHTML || '{}')
		if (serverData.appointment && 'id' in serverData.appointment) {
			this.$set(this, 'appointment', serverData.appointment)
		}
		if (serverData.config && 'documentationUrl' in serverData.config) {
			this.$set(this, 'config', serverData.config)
		}
		if (serverData.rgpdOptions && 'list' in serverData.rgpdOptions) {
			this.$set(this, 'rgpdOptions', serverData.rgpdOptions.list)
		}
		if (serverData.procedureStakeholder && 'id' in serverData.procedureStakeholder) {
			this.$set(this, 'stakeholder', serverData.procedureStakeholder)
		}
	},
}
</script>

<style>
iframe { border: 0px; }
</style>
