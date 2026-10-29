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
		<!-- <div>
			AppointmentFilesDownload
		</div> -->
		<v-sheet v-if="iframeLoadedTimes <= 1"
			class="d-flex justify-center align-center"
			height="100%">
			<v-progress-circular indeterminate size="64" class="ma-4" />
		</v-sheet>
		<iframe v-show="iframeLoadedTimes > 1"
			id="filesDownloads"
			:height="height"
			width="100%"
			:style="`width: 100%; height: ${height}`" />
	</v-sheet>
</template>

<script>

export default {
	name: 'AppointmentFilesDownloads',
	components: {
	},
	props: {
		activeAcc: {
			type: Number,
			default: -1,
		},
		// See AppointmentFilesUploads: parent passes our accordion offset.
		accIndex: {
			type: Number,
			default: 2,
		},
		activeTab: {
			type: Number,
			default: -1,
		},
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
			iframe: null,
			iframeLoadedTimes: 0,
			failbackTimer: null,
		}
	},
	watch: {
		activeAcc: {
			immediate: true,
			handler(newValue) {
				if (newValue === this.accIndex) {
					this.$nextTick(() => {
						this.forceFrameLoad()
					})
				}
			},
		},
		activeTab: {
			immediate: true,
			handler(newValue) {
				if (newValue === 1) {
					this.$nextTick(() => {
						this.forceFrameLoad()
					})
				}
			},
		},
	},
	mounted() {
		// this.forceFrameLoad()
	},
	updated() {
	},
	methods: {
		forceFrameLoad() {
			this.iframeLoadedTimes = 0
			if (!this.iframe) {
				this.iframe = document.getElementById('filesDownloads')
				if (this.iframe) {
					this.iframe.onload = this.frameLoaded
				}
			}
			if (this.iframe) {
				this.iframe.src = this.appointment.sharedUrlDownloads
				this.failbackTimer = setTimeout(() => {
					this.iframeLoadedTimes = 100
				}, 10000)
			}
		},
		frameLoaded() {
			const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document
			if (iframeDoc) {
				if (this.iframeLoadedTimes === 0) {
					this.iframeLoadedTimes = 1
					const password = this.$store.getters.getPassword
					if (iframeDoc.getElementById('password')) {
						iframeDoc.getElementById('password').value = password
						iframeDoc.getElementById('password').click()
						iframeDoc.getElementById('password-submit').click()
					}
				} else if (this.iframeLoadedTimes === 1) {
					this.iframeLoadedTimes = 2
					if (iframeDoc.getElementById('body-login')) {
						// Nothing
					} else {
						// The login succeded.
						setTimeout(() => {
							this.clickLinkDownload()
						}, 500)
					}
				}
			}
		},
		clickLinkDownload() {
			if (this.config.outputAutodownload) {
				const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document
				if (iframeDoc && iframeDoc.getElementById('download')) {
					iframeDoc.getElementById('download').firstChild.click()
				}
			}
		},
	},
}
</script>
