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
	<v-sheet width="100%" height="100%">
		<template v-if="!isAppointmentActive">
			<div v-if="isAppointmentExpired" class="d-flex flex-column align-center justify-center" :style="`width: 100%; height: ${height}`">
				<div class="text-h5 text-center mb-4" v-html="config.textExpired" />
				<div class="text-h5 text-center font-weight-bold">
					{{ appointment.date | formatDate }} {{ appointment.date | formatTime }}
				</div>
			</div>
			<div v-else-if="isAppointmentFuture" class="d-flex flex-column align-center justify-center" :style="`width: 100%; height: ${height}`">
				<div class="text-h5 text-center mb-4" v-html="config.textFuture" />
				<div class="text-h5 text-center font-weight-bold">
					{{ appointment.date | formatDate }} {{ appointment.date | formatTime }}
				</div>
			</div>
		</template>
		<template v-else>
			<!-- Jitsi IFrame API container -->
			<Jitsi v-if="appointmentInfoReady"
				:url="jitsiUrlWithAk"
				:height="height" />
		</template>
	</v-sheet>
</template>

<script>
import moment from '@nextcloud/moment'
import Jitsi from './Jitsi.vue'

export default {
	name: 'VideoCall',
	components: {
		Jitsi,
	},
	props: {
		height: {
			type: String,
			default: '100%',
		},
		appointment: {
			type: Object,
			default: () => {},
		},
		config: {
			type: Object,
			default: () => {},
		},
	},
	data() {
		return {
			videocall: null,
		}
	},
	computed: {
		isAppointmentActive() {
			if (this.appointmentDate) {
				// Per si volem fer x horeas abans/després:
				// const initPeriod = moment().subtract(12, 'hours')
				// const finalPeriod = moment().add(12, 'hours')
				// return this.appointmentDate.isBetween(initPeriod, finalPeriod)

				const initPeriod = moment()
				return this.appointmentDate.isSame(initPeriod, 'day')
			}
			return false
		},
		isAppointmentExpired() {
			if (this.appointmentDate) {
				const initPeriod = moment()
				return this.appointmentDate.isBefore(initPeriod, 'day')
			}
			return true
		},
		isAppointmentFuture() {
			if (this.appointmentDate) {
				const initPeriod = moment()
				return this.appointmentDate.isAfter(initPeriod, 'day')
			}
			return true
		},
		appointmentDate() {
			if ('date' in this.appointment && this.appointment.date) {
				return moment(this.appointment.date * 1000)
			}
			return ''
		},
		appointmentInfoReady() {
			if ('jitsiRoomUrl' in this.appointment && this.appointment.jitsiRoomUrl) {
				return true
			}
			return false
		},
		jitsiUrlWithAk() {
			const baseUrl = this.appointment.jitsiRoomUrl
			if (!baseUrl) return ''

			// Only append ak when the URL matches https://FQDN/puntremot/<roomName>
			try {
				const url = new URL(baseUrl)
				const segments = url.pathname.split('/').filter(Boolean)
				const room = segments.pop()
				const basePath = segments.join('/')

				if (basePath === 'puntremot' && room) {
					const ak = this.$store.getters.getPassword
					if (ak) {
						url.searchParams.set('ak', ak)
					}
					return url.toString()
				}
			} catch (e) {
				// If URL parsing fails, fall back to the original URL
			}

			return baseUrl
		},
	},
}
</script>
