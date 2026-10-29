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
	<div ref="jitsiContainer" :style="`width: 100%; height: ${height}`"></div>
</template>

<script>
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
		}
	},
	mounted() {
		this.mountJitsi()
	},
	beforeDestroy() {
		this.disposeJitsi()
	},
	methods: {
		parseJitsiUrl(fullUrl) {
			try {
				const u = new URL(fullUrl)
				const segments = u.pathname.split('/').filter(Boolean)
				const room = segments.pop() || ''
				const basePath = segments.join('/')
				const jwt = u.searchParams.get('jwt') || undefined
				const params = new URLSearchParams(u.search)
				params.delete('jwt')
				return { domain: u.host, basePath, roomName: room, jwt, params }
			} catch (e) {
				return { domain: 'meet.jit.si', basePath: '', roomName: '', jwt: undefined, params: new URLSearchParams() }
			}
		},
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
		applyIframeParams(extraParams) {
			if (!extraParams || extraParams.size === 0) return
			const iframe = this.jitsiApi?.getIFrame?.()
			if (!iframe || !iframe.src) return
			try {
				const iframeUrl = new URL(iframe.src)
				extraParams.forEach((value, key) => {
					iframeUrl.searchParams.set(key, value)
				})
				iframe.src = iframeUrl.toString()
			} catch (e) {
				// no-op if URL parsing fails
			}
		},
		async mountJitsi() {
			if (!this.$refs.jitsiContainer) return
			const { domain, basePath, roomName, jwt, params } = this.parseJitsiUrl(this.url)
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

			// Re-apply query parameters (eg. ak) after Jitsi initializes the iframe
			this.applyIframeParams(params)
		},
		disposeJitsi() {
			try {
				if (this.jitsiApi) {
					this.jitsiApi.dispose()
				}
			} catch (e) {
				// no-op
			} finally {
				this.jitsiApi = null
				if (this.$refs.jitsiContainer) {
					this.$refs.jitsiContainer.innerHTML = ''
				}
			}
		},
	},
}
</script>
