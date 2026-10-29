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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
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
					{{ procedure.appointmentDate | formatDate }} {{ procedure.appointmentDate | formatTime }}
				</div>
			</div>
			<div v-else-if="isAppointmentFuture" class="d-flex flex-column align-center justify-center" :style="`width: 100%; height: ${height}`">
				<div class="text-h5 text-center mb-4" v-html="config.textFuture" />
				<div class="text-h5 text-center font-weight-bold">
					{{ procedure.appointmentDate | formatDate }} {{ procedure.appointmentDate | formatTime }}
				</div>
			</div>
		</template>
		<template v-else>
			<!-- Jitsi IFrame API container -->
			<Jitsi v-if="procedureInfoReady"
				ref="jitsi"
				:url="procedure.fullJitsiRoomStaffUrl"
				:height="height"
				@started="onStarted"
				@ended="onEnded"
				@snapshot="onSnapshot"
				@snapshot-error="onSnapshotError" />
		</template>
	</v-sheet>
</template>

<script>
import moment from '@nextcloud/moment'
import { showError } from '@nextcloud/dialogs'
import ApiService from '../../../mixins/apiService.mixin.js'
import Jitsi from './Jitsi.vue'

export default {
	name: 'VideoCall',
	mixins: [ApiService],
	components: {
		Jitsi,
	},
	props: {
		height: {
			type: String,
			default: '100%',
		},
		procedure: {
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
		// ------------------------------------------------------------------------
		// Appointment window checks based on a UNIX timestamp (seconds) in procedure.date if present
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

		// ------------------------------------------------------------------------
		// Converts procedure.date (unix seconds) into a Moment instance if available
		appointmentDate() {
			if ('appointmentDate' in this.procedure && this.procedure.appointmentDate) {
				return moment(this.procedure.appointmentDate, 'YYYY-MM-DD HH:mm:ss')
			}
			return ''
		},

		// ------------------------------------------------------------------------
		// Ready guard for Jitsi panel using the staff URL
		procedureInfoReady() {
			if ('fullJitsiRoomStaffUrl' in this.procedure && this.procedure.fullJitsiRoomStaffUrl) {
				return true
			}
			return false
		},
	},
	methods: {
		// ------------------------------------------------------------------------
		// Jitsi lifecycle: START
		onStarted() {
			this.$emit('videocall-started')
		},

		// ------------------------------------------------------------------------
		// Jitsi lifecycle: END
		onEnded() {
			this.$emit('videocall-ended')
		},

		// ------------------------------------------------------------------------
		// Public proxy to trigger snapshot on child Jitsi component
		captureSnapshot() {
			// console.info('[videocall] forwarding to Jitsi.captureSnapshot()')

			const j = this.$refs && this.$refs.jitsi
			if (j && typeof j.captureSnapshot === 'function') {
				j.captureSnapshot()
			}
		},

		// ------------------------------------------------------------------------
		// Upload snapshot to backend (uploads folder)
		// @param {string} dataUrl - Schema data content, according to RFC 2397
		async onSnapshot({ dataUrl }) {
			try {
				const procedureId = this.procedure?.id
				if (!dataUrl || !procedureId) {
					return
				}

				const url = `/apps/vtramit/web/2.0/procedure/${encodeURIComponent(procedureId)}/file/upload-folder`

				const now = new Date()
				const ts = now.getFullYear().toString()
					+ String(now.getMonth() + 1).padStart(2, '0')
					+ String(now.getDate()).padStart(2, '0')
					+ '-'
					+ String(now.getHours()).padStart(2, '0')
					+ String(now.getMinutes()).padStart(2, '0')
					+ String(now.getSeconds()).padStart(2, '0')
				const filename = `snapshot-${ts}`

				await this.post(url, {
					filename: filename,
					data: dataUrl
				}, true)

			} catch (e) {
				showError(t('vtramit', 'An unknown error has occurred while saving the snapshot'))
				// console.error('[snapshot] Error uploading snapshot', e)
			}
		},

		// ------------------------------------------------------------------------
		// Optional feedback hook for errors (kept silent to avoid UI noise)
		onSnapshotError(err) {
			showError(err)
		},
	},
}
</script>
