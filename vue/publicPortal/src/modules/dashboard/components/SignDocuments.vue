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
	<v-sheet v-if="config.isSignatureEnabled" width="100%" height="100%">
		<div class="d-flex justify-end pa-2">
			<v-btn color="primary"
				:disabled="!firstDocToSign"
				:href="signUrl"
				target="_blank">
				{{ t('vtramit', 'Sign this document') }}
			</v-btn>
		</div>
		<div v-if="!firstDocToSign" class="d-flex justify-center mx-2 my-4">
			<div v-if="status.reading">
				{{ t('vtramit', 'Loading documents to sign ...') }}
			</div>
			<div v-else>
				{{ t('vtramit', 'No documents to sign') }}
			</div>
		</div>
		<iframe v-show="firstDocToSign"
			id="signDocument"
			:height="height"
			width="100%"
			:style="`width: 100%; height: ${height}`" />
	</v-sheet>
</template>

<script>

export default {
	name: 'SignDocuments',
	components: {
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
			status: {
				reading: false,
				readSuccess: false,
				readError: false,
			},
			iframe: null,
			iframeLoadedOnce: false,
			loadTimer: null,
			// Having this properties as variables instead of computer properties so
			// they don't change every time the refresh is fired.
			signUrl: '',
			signDocumentUrl: '',
		}
	},
	computed: {
		firstDocToSign() {
			if (this.allSignDocuments && this.allSignDocuments.length) {
				return this.allSignDocuments[0]
			}
			return false
		},
		allSignDocuments() {
			return this.$store.getters['signDocuments/readAll']
		},
	},
	watch: {
		appointment: {
			immediate: true,
			handler(newValue) {
				if (newValue && newValue.jitsiRoomCode) {
					this.status.reading = true
					this.status.readSuccess = false
					this.status.readError = false

					this.$store
						.dispatch('signDocuments/readAll', newValue.jitsiRoomCode)
						.then((/* result */) => {
							this.status.reading = false
							this.status.readSuccess = true

							if (!this.loadTimer) {
								this.loadTimer = setInterval(this.refreshSignDocuments, 10000)
							}
						})
						.catch((/* error */) => {
							this.status.reading = false
							this.status.readError = true
						})
				}
			},
		},
		firstDocToSign: {
			immediate: true,
			handler(newValue) {
				if (newValue && newValue.sharedUrl) {
					if (this.signDocumentUrl !== newValue.sharedUrl) {
						this.signDocumentUrl = newValue.sharedUrl
						if (this.iframe) {
							this.iframe.src = this.signDocumentUrl
						}
					}
				}
				if (newValue && newValue.signatureUrl) {
					if (this.signUrl !== newValue.signatureUrl) {
						this.signUrl = newValue.signatureUrl
					}
				}
			},
		},
	},
	mounted() {
		this.iframe = document.getElementById('signDocument')
		if (this.iframe) {
			this.iframe.onload = this.frameLoaded
			// this.iframe.src = this.appointment.sharedUrlDownloads
		}
	},
	beforeDestroy() {
		if (this.loadTimer) {
			clearInterval(this.loadTimer)
			this.loadTimer = null
		}
	},
	methods: {
		refreshSignDocuments() {
			if (this.appointment && this.appointment.jitsiRoomCode) {
				this.status.reading = true
				this.status.readSuccess = false
				this.status.readError = false

				this.$store
					.dispatch('signDocuments/readAll', this.appointment.jitsiRoomCode)
					.then((/* result */) => {
						this.status.reading = false
						this.status.readSuccess = true
					})
					.catch((/* error */) => {
						this.status.reading = false
						this.status.readError = true
					})
			}
		},
		frameLoaded() {
			if (!this.iframeLoadedOnce) {
				this.iframeLoadedOnce = true
				const password = this.$store.getters.getPassword
				if (this.iframe.contentWindow.document.getElementById('password')) {
					this.iframe.contentWindow.document.getElementById('password').value = password
					this.iframe.contentWindow.document.getElementById('password').click()
					this.iframe.contentWindow.document.getElementById('password-submit').click()
				}
			}
		},
	},
}
</script>
