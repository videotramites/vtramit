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
		<v-app-bar v-if="hasLogo" app color="#fff">
			<div class="flex-grow-1" />
			<img v-if="hasLogo" :src="config.logoUrl" height="100%">
			<div class="flex-grow-1" />
		</v-app-bar>
		<v-main>
			<v-container fluid class="d-flex flex-column align-stretch pa-0" style="height:100%;">
				<v-sheet tile color="#fff" class="fill-height">
					<v-row justify="center" class="fill-height flex-grow-1">
						<v-col cols="12" md="6" lg="3">
							<div class="fill-height d-flex flex-column align-center justify-center text-h5 text-center pa-2">
								{{ t('vtramit', 'The process has finished successfully, you can close this tab now.') }}
							</div>
						</v-col>
					</v-row>
				</v-sheet>
			</v-container>
		</v-main>
	</v-app>
</template>

<script>
export default {
	name: 'ClosePage',
	components: {
	},
	data() {
		return {
			// Data from the server
			appointment: {},
			config: {},
		}
	},
	computed: {
		hasLogo() {
			if ('logoUrl' in this.config && this.config.logoUrl && this.config.logoUrl !== '') {
				return true
			}
			return false
		},
		hasAppointment() {
			return 'id' in this.appointment
		},
	},
	created() {
		const serverDataJson = document.getElementsByName('server-data')[0].innerHTML
		const serverData = JSON.parse(serverDataJson)
		if ('appointment' in serverData && serverData.appointment && 'id' in serverData.appointment) {
			this.$set(this, 'appointment', serverData.appointment)
		}
		if ('config' in serverData && serverData.config && 'documentationUrl' in serverData.config) {
			this.$set(this, 'config', serverData.config)
		}
		// eslint-disable-next-line no-console
		// console.log('appointment', this.appointment)
	},
}
</script>

<!-- <style lang="scss"></style> -->
