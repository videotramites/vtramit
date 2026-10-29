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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 -->

<template>
	<div ref="jitsiContainer" :style="`width: 100%; height: ${height}`"></div>
</template>

<script>
import '@nextcloud/dialogs/styles/toast.scss'

export default {
	name: 'Jitsi',
	props: {
		url: { type: String, required: true },
		height: { type: String, default: '100%' },
	},
	data() {
		return {
			jitsiApi: null,
			externalScriptPromise: null,
			conferenceStarted: false,
		}
	},
	mounted() {
		this.mountJitsi()
	},
	beforeDestroy() {
		this.disposeJitsi()
	},
	methods: {
		// ------------------------------------------------------------------------
		// Parses a Jitsi URL and extracts domain, basePath, roomName and optional JWT
		parseJitsiUrl(fullUrl) {
			try {
				const u = new URL(fullUrl)
				const segments = u.pathname.split('/').filter(Boolean)
				const room = segments.pop() || ''
				const basePath = segments.join('/')
				const jwt = u.searchParams.get('jwt') || undefined
				return { domain: u.host, basePath, roomName: room, jwt }
			} catch (e) {
				return { domain: 'meet.jit.si', basePath: '', roomName: '', jwt: undefined }
			}
		},

		// ------------------------------------------------------------------------
		// Ensures external_api.js is available before instantiating Jitsi
		async ensureJitsiApiLoaded(domain, basePath = '') {
			if (window.JitsiMeetExternalAPI) return
			if (this.externalScriptPromise) { await this.externalScriptPromise; return }
			this.externalScriptPromise = new Promise((resolve, reject) => {
				const s = document.createElement('script')
				s.async = true
				s.src = `https://${domain}${basePath ? '/' + basePath : ''}/external_api.js`
				s.onload = () => resolve()
				s.onerror = () => reject(new Error('Failed to load Jitsi external_api.js'))
				document.head.appendChild(s)
			})
			await this.externalScriptPromise
		},

		// ------------------------------------------------------------------------
		// Mounts Jitsi IFrame API in the component container
		async mountJitsi() {
			if (!this.$refs.jitsiContainer) return
			const { domain, basePath, roomName, jwt } = this.parseJitsiUrl(this.url)
			if (!domain || !roomName) return

			await this.ensureJitsiApiLoaded(domain, basePath)

			const options = {
				roomName: `${basePath ? basePath + '/' : ''}${roomName}`,
				parentNode: this.$refs.jitsiContainer,
				width: '100%',
				height: this.height,
				...(jwt ? { jwt } : {}),
			}

			this.jitsiApi = new window.JitsiMeetExternalAPI(domain, options)

			// Emit lifecycle events to parent when the conference starts or ends
			this.jitsiApi.addListener('videoConferenceJoined', () => {
				this.conferenceStarted = true
				this.$emit('started')
			})

			this.jitsiApi.addListener('readyToClose', () => {
				this.conferenceStarted = false
				this.$emit('ended')
			})
		},

		// ------------------------------------------------------------------------
		// Disposes Jitsi instance and clears container
		disposeJitsi() {
			try {
				if (this.jitsiApi) {
					this.jitsiApi.dispose()
				}
			} catch (e) {
				// no-op
			} finally {
				this.jitsiApi = null
				this.conferenceStarted = false
				if (this.$refs.jitsiContainer) {
					this.$refs.jitsiContainer.innerHTML = ''
				}
			}
		},

		// ------------------------------------------------------------------------
		// Capture the large video as a PNG data and notify parent
		async captureSnapshot() {
			// console.info('[jitsi] captureSnapshot invoked')

			if (!this.jitsiApi) {
				return
			}

			if (!this.conferenceStarted) {
				const error = t('vtramit', 'Please, log into the videoconference to take a snapshot')
				this.$emit('snapshot-error', new Error(error))
				return
			}

			try {
				// Some deployments expose getLargeVideoScreenshot, others captureLargeVideoScreenshot
				const fn =
					(typeof this.jitsiApi.getLargeVideoScreenshot === 'function' && 'getLargeVideoScreenshot') ||
					(typeof this.jitsiApi.captureLargeVideoScreenshot === 'function' && 'captureLargeVideoScreenshot')

				if (!fn) {
					const error = t('vtramit', 'Snapshot is not supported!')
					this.$emit('snapshot-error', new Error(error))
					return
				}

				const screenshot = await this.jitsiApi[fn]()
				const dataUrl = typeof screenshot === 'string' ? screenshot : (screenshot && screenshot.dataURL)

				// Validate dataUrl before emit to parent
				if (!dataUrl || !dataUrl.startsWith('data:')) {
					const error = t('vtramit', 'Snapshot data is invalid. Be sure there is an active video to capture')
					this.$emit('snapshot-error', new Error(error))
					return
				}

				// Emit to parent for any custom handling (download, upload in a later phase, etc.)
				this.$emit('snapshot', { dataUrl: dataUrl })
			} catch (_) {
				// Emit the error to parent; parent may add a toast if needed by listening to 'snapshot-error'
				const error = t('vtramit', 'An unknown error in Jitsi has occurred while taking a snapshot')
				this.$emit('snapshot-error', new Error(error))
			}
		},
	},
}
</script>
