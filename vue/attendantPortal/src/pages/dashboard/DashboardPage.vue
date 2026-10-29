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
	<v-app style="width:100%;" class="vtramit">
		<v-app-bar v-if="hasLogo && !$vuetify.breakpoint.mobile && !hideLogo" app color="#fff">
			<div class="flex-grow-1" />
			<img v-if="hasLogo" :src="config.logoUrl" height="100%">
			<div class="flex-grow-1" />
		</v-app-bar>

		<v-main>
			<v-container fluid class="d-flex flex-column align-stretch pa-0" style="height:100%;">
				<DashboardDesktop
					:procedure="procedure"
					:config="config"
					:stakeholder="stakeholder"
					:rgpd-options="rgpdOptions"
					@fullscreen-change="hideLogo = $event" />
			</v-container>
		</v-main>
	</v-app>
</template>

<script>
import DashboardDesktop from './components/DashboardDesktop.vue'

export default {
	name: 'DashboardPage',
	components: {
		DashboardDesktop,
	},
	data() {
		return {
			procedureId: null,
			config: {},
			stakeholder: {},
			rgpdOptions: [],
			hideLogo: false,
			_pollTimer: null,
		}
	},
	computed: {
		hasLogo() {
			return !!(this.config.logoUrl)
		},
		procedure() {
			return this.$store.getters['procedure/readById'](this.procedureId)
		},
	},
	created() {
		const el = document.getElementsByName('server-data')[0]
		if (!el) return
		const serverData = JSON.parse(el.innerHTML || '{}')

		if (serverData.procedureId) {
			this.procedureId = serverData.procedureId
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
	mounted() {
		this.loadProcedure()
		this._pollTimer = setInterval(this.loadProcedure, 10000)
	},
	beforeDestroy() {
		if (this._pollTimer) {
			clearInterval(this._pollTimer)
			this._pollTimer = null
		}
	},
	methods: {
		async loadProcedure() {
			if (!this.procedureId) return
			try {
				await this.$store.dispatch('procedure/readById', { id: this.procedureId })
				await this.$store.dispatch('stakeholders/readByProcedureId', this.procedureId)
			} catch (e) {
				// silent fail
			}
		},
	},
}
</script>

<style lang="scss">
.vtramit {

	iframe {
		border: 0px;
	}

	input {

		&[type=button] {
			cursor: pointer;
			background-color: var(--color-primary-element-light);
			font-weight: bold;
			border: none;
			border-radius: var(--border-radius-element);
			padding: calc((var(--default-clickable-area) - 1lh) / 2) calc(3 * var(--default-grid-baseline));
			font-size: var(--default-font-size);
			width: auto;
			min-height: var(--default-clickable-area);
			box-sizing: border-box;
			outline: none;
			margin: 10px;
		}
	}
}
</style>
