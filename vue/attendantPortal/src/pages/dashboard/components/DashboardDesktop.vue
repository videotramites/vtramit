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
	<v-row ref="desktopRow" no-gutters style="height:100%; display:flex; position:relative; flex-wrap:nowrap;">
		<v-col v-if="hasColLeft"
			v-show="!isFullscreen || fullscreenSide === 'left'"
			class="d-flex flex-column"
			:class="leftColClass"
			:style="leftStyle"
			style="position:relative;">
			<v-toolbar v-if="!isFullscreen" class="flex-grow-0">
				<!-- Main buttons -->
				<v-tabs v-model="tabsLeft"
					background-color="shadowBackground"
					left
					class="tab--toolbar--left">
					<v-tab v-if="hasDocumentation" :tab-value="documentationOffset">
						<slot name="default">
							<v-btn data-test="help" :color="tabsLeft === documentationOffset ? 'primary' : undefined">
								{{ t('vtramit', 'Help') }}
							</v-btn>
						</slot>
					</v-tab>
					<v-tab v-if="hasTopicHelpUrl"
						:tab-value="topicHelpTabOffset"
						@mousedown.stop.prevent="onClickTopicHelp"
						@click.stop.prevent="onClickTopicHelp"
						@keydown.enter.stop.prevent="onClickTopicHelp"
						@keydown.space.stop.prevent="onClickTopicHelp">
						<slot name="default">
							<v-btn :color="tabsLeft === topicHelpTabOffset ? 'primary' : undefined">
								{{ t('vtramit', 'My procedure') }}
							</v-btn>
						</slot>
					</v-tab>
					<v-tab v-if="hasRGPDOptions" :tab-value="rgpdTabOffset">
						<slot name="default">
							<v-btn data-test="rgdp" :color="tabsLeft === rgpdTabOffset ? 'primary' : undefined">
								{{ t('vtramit', 'RGPD') }}
							</v-btn>
						</slot>
					</v-tab>
					<v-tab v-if="hasVideocall" :tab-value="videoCallTabOffset">
						<slot name="default">
							<v-btn data-test="videocall" :color="tabsLeft === videoCallTabOffset ? 'primary' : undefined">
								{{ t('vtramit', 'Access to videocall') }}
							</v-btn>
						</slot>
					</v-tab>
				</v-tabs>
				<!-- END -- Main buttons -->

				<v-spacer />

				<!-- Additional buttons: language selector | snapshot | fullscreen -->
				<template v-if="!isLeftToolbarCompact">
					<v-btn v-if="hasVideocall && isVideocallStarted"
						icon
						:title="t('vtramit','Take snapshot')"
						@click="captureSnapshot">
						<v-icon>mdi-camera</v-icon>
					</v-btn>
					<RemoteCommands v-if="allowRemoteCommands && jitsiRoomCode" ref="" :jitsi-room-code="jitsiRoomCode" />
					<LanguageSelector v-if="hasMultipleLanguages" :allowed-languages="config.languages" />
					<BackToAdmin v-if="procedure" :procedure-id="procedure.id" />
					<v-btn icon :title="t('vtramit','Enter fullscreen')" @click="enterFullscreen('left')">
						<v-icon>mdi-fullscreen</v-icon>
					</v-btn>
				</template>

				<!-- Compact mode: collapse additional buttons into a "more" menu -->
				<template v-else>
					<v-menu v-model="leftMoreMenu"
						offset-y
						left>
						<template #activator="{ on, attrs }">
							<v-btn icon
								v-bind="attrs"
								:title="t('vtramit','More actions')"
								v-on="on">
								<v-icon>mdi-dots-vertical</v-icon>
							</v-btn>
						</template>

						<v-list dense>
							<!-- Snapshot -->
							<v-list-item v-if="hasVideocall && isVideocallStarted">
								<div v-bind="attrs" v-on="on">
									<v-btn icon
										v-bind="attrs"
										:title="t('vtramit','Take snapshot')"
										v-on="on"
										@click="captureSnapshot">
										<v-icon>mdi-camera</v-icon>
									</v-btn>
								</div>
							</v-list-item>

							<!-- Remote commands -->
							<v-list-item v-if="allowRemoteCommands && jitsiRoomCode">
								<div v-bind="attrs" v-on="on">
									<RemoteCommands :jitsi-room-code="jitsiRoomCode" />
								</div>
							</v-list-item>

							<!-- Language selector -->
							<v-list-item v-if="hasMultipleLanguages">
								<div v-bind="attrs" v-on="on">
									<LanguageSelector :allowed-languages="config.languages" />
								</div>
							</v-list-item>

							<!-- Back to admin -->
							<v-list-item v-if="procedure">
								<div v-bind="attrs" v-on="on">
									<BackToAdmin :procedure-id="procedure.id" />
								</div>
							</v-list-item>

							<!-- Fullscreen -->
							<v-list-item>
								<div v-bind="attrs" v-on="on">
									<v-btn icon
										v-bind="attrs"
										:title="t('vtramit','Enter fullscreen')"
										v-on="on"
										@click="enterFullscreen('left')">
										<v-icon>mdi-fullscreen</v-icon>
									</v-btn>
								</div>
							</v-list-item>
						</v-list>
					</v-menu>
				</template>
				<!-- END -- Additional buttons -->
			</v-toolbar>

			<v-tabs-items v-model="tabsLeft" class="flex-grow-1" style="height:100%;">
				<v-tab-item v-if="hasDocumentation" style="height:100%;" :value="documentationOffset">
					<Documentation :procedure="procedure" :config="config" />
				</v-tab-item>
				<v-tab-item v-if="hasTopicHelpUrl" style="height:100%;" :value="topicHelpTabOffset">
					<TopicHelp :procedure="procedure" :config="config" />
				</v-tab-item>
				<v-tab-item v-if="hasRGPDOptions" style="height:100%;" :value="rgpdTabOffset">
					<div class="d-flex flex-column align-center pt-40">
						<RGPD ref="rpgd"
							:procedure="procedure"
							:stakeholder="stakeholder"
							:rgpd-options="rgpdOptions" />
					</div>
				</v-tab-item>
				<v-tab-item v-if="hasVideocall"
					eager
					style="height:100%;"
					:value="videoCallTabOffset">
					<VideoCall ref="videoCall"
						:procedure="procedure"
						:config="config"
						@videocall-started="isVideocallStarted = true"
						@videocall-ended="isVideocallStarted = false" />
				</v-tab-item>
			</v-tabs-items>
		</v-col>

		<div v-if="hasColLeft && hasColRight && !isFullscreen"
			class="splitter"
			:class="{ dragging }"
			:style="{ flex: `0 0 ${splitterWidth}px` }"
			@pointerdown="startDrag" />

		<v-col v-if="hasColRight"
			v-show="!isFullscreen || fullscreenSide === 'right'"
			class="d-flex flex-column"
			:class="rightColClass"
			:style="rightStyle"
			style="position:relative;">
			<v-toolbar v-if="!isFullscreen" class="flex-grow-0 tab--toolbar--right">
				<v-tabs v-model="tabsRight" background-color="shadowBackground" right>
					<!-- Appointment -->
					<v-tab :tab-value="procedureInfoOffset">
						<v-tooltip bottom>
							<template #activator="{ on, attrs }">
								<v-btn data-test="appointment"
									icon
									v-bind="attrs"
									:color="tabsRight === procedureInfoOffset ? 'primary' : undefined"
									v-on="on">
									<v-icon>mdi-calendar</v-icon>
								</v-btn>
							</template>
							<span>{{ t('vtramit', 'Appointment') }}</span>
						</v-tooltip>
					</v-tab>

					<!-- Protocol steps -->
					<v-tab v-if="hasProtocolSteps" :tab-value="protocolStepsOffset">
						<v-tooltip bottom>
							<template #activator="{ on, attrs }">
								<v-btn data-test="protocol-steps"
									icon
									v-bind="attrs"
									:color="tabsRight === protocolStepsOffset ? 'primary' : undefined"
									v-on="on">
									<v-icon>mdi-format-list-checks</v-icon>
								</v-btn>
							</template>
							<span>{{ t('vtramit', 'Protocol steps') }}</span>
						</v-tooltip>
					</v-tab>

					<!-- Uploads -->
					<v-tab v-if="hasUploads" :tab-value="uploadOffset">
						<v-badge :color="badgeColor(uploadsQty)"
							:value="true"
							overlap>
							<template #badge>
								{{ uploadsQty }}
							</template>
							<v-tooltip bottom>
								<template #activator="{ on, attrs }">
									<v-btn data-test="uploads"
										icon
										v-bind="attrs"
										:color="tabsRight === uploadOffset ? 'primary' : undefined"
										v-on="on">
										<v-icon>mdi-upload</v-icon>
									</v-btn>
								</template>
								<span>{{ t('vtramit', 'Shared folder for Uploads') }}</span>
							</v-tooltip>
						</v-badge>
					</v-tab>

					<!-- Downloads -->
					<v-tab v-if="hasDownloads" :tab-value="downloadOffset">
						<v-badge :color="badgeColor(downloadsQty)"
							:value="true"
							overlap>
							<template #badge>
								{{ downloadsQty }}
							</template>
							<v-tooltip bottom>
								<template #activator="{ on, attrs }">
									<v-btn data-test="downloads"
										icon
										v-bind="attrs"
										:color="tabsRight === downloadOffset ? 'primary' : undefined"
										v-on="on">
										<v-icon>mdi-download</v-icon>
									</v-btn>
								</template>
								<span>{{ t('vtramit', 'Shared folder for Downloads') }}</span>
							</v-tooltip>
						</v-badge>
					</v-tab>

					<!-- Signatures -->
					<v-tab v-if="hasSignature" :tab-value="signatureOffset">
						<v-badge :color="badgeColor(signatureDownloadsQty)"
							:value="true"
							overlap
							class="signature-tab--badge">
							<template #badge>
								<span class="signature-tab--badge--badge-label">
									{{ signatureDownloadsQty }} / {{ signatureUploadsQty }}
								</span>
							</template>
							<v-tooltip bottom>
								<template #activator="{ on, attrs }">
									<v-btn data-test="sign"
										icon
										v-bind="attrs"
										:color="tabsRight === signatureOffset ? 'primary' : undefined"
										v-on="on">
										<v-icon>mdi-pen</v-icon>
									</v-btn>
								</template>
								<span>{{ t('vtramit', 'Signatures') }}</span>
							</v-tooltip>
						</v-badge>
					</v-tab>
				</v-tabs>
			</v-toolbar>

			<v-tabs-items v-model="tabsRight" class="flex-grow-1" style="height:100%;">
				<v-tab-item eager style="height:100%;" :value="procedureInfoOffset">
					<Procedure v-if="procedure && procedure.id" :procedure-id="procedure.id" />
				</v-tab-item>
				<v-tab-item v-if="hasUploads"
					eager
					style="height:100%;"
					:value="uploadOffset">
					<FilesPanel :list="filesUploads"
						:download-url="`/apps/vtramit/web/2.0/procedure/${procedure.id}/file/upload-folder?filename=%filename%`"
						accept=".pdf, .png, .jpg"
						@uploaded="loadUploads"
						@deleted="loadUploads" />
				</v-tab-item>
				<v-tab-item v-if="hasDownloads"
					eager
					style="height:100%;"
					:value="downloadOffset">
					<FilesPanel :list="filesDownloads"
						:upload-url="`/apps/vtramit/web/2.0/procedure/${procedure.id}/file/download-folder`"
						:download-url="`/apps/vtramit/web/2.0/procedure/${procedure.id}/file/download-folder?filename=%filename%`"
						:delete-url="`/apps/vtramit/web/2.0/procedure/${procedure.id}/file/download-folder?filename=%filename%`"
						accept=".pdf, .png, .jpg"
						@uploaded="loadDownloads"
						@deleted="loadDownloads" />
				</v-tab-item>
				<v-tab-item v-if="hasSignature"
					eager
					style="height:100%;"
					:value="signatureOffset">
					<SignaturePanel :procedure="procedure" @counts-change="updateSignatureCounts" />
				</v-tab-item>
				<v-tab-item style="height:100%;" :value="protocolStepsOffset">
					<ProtocolSteps :procedure-id="procedure && procedure.id ? procedure.id : null" />
				</v-tab-item>
			</v-tabs-items>
		</v-col>

		<!-- Floating action bar (fullscreen): includes all left/right actions -->
		<div v-if="isFullscreen" class="fullscreen-action-bar">
			<v-btn icon
				large
				:title="t('vtramit','Exit fullscreen')"
				@click="exitFullscreen">
				<v-icon>mdi-fullscreen-exit</v-icon>
			</v-btn>
			<v-btn v-if="hasDocumentation"
				icon
				large
				:title="t('vtramit','Help')"
				@click="goToLeftTab(documentationOffset)">
				<v-icon>mdi-help-circle-outline</v-icon>
			</v-btn>
			<v-btn v-if="hasTopicHelpUrl"
				icon
				large
				:title="t('vtramit','My procedure')"
				@click="openTopicHelpAction">
				<v-icon>mdi-open-in-new</v-icon>
			</v-btn>
			<v-btn v-if="hasRGPDOptions"
				icon
				large
				:title="t('vtramit','Manage RGPD options')"
				@click="goToLeftTab(rgpdTabOffset)">
				<v-icon>mdi-shield-lock-outline</v-icon>
			</v-btn>
			<v-btn v-if="hasVideocall"
				icon
				large
				:title="t('vtramit','Access to videocall')"
				@click="goToLeftTab(videoCallTabOffset)">
				<v-icon>mdi-video-outline</v-icon>
			</v-btn>
			<v-btn v-if="hasVideocall && isVideocallStarted"
				icon
				large
				:title="t('vtramit','Take snapshot')"
				@click="captureSnapshot">
				<v-icon>mdi-camera</v-icon>
			</v-btn>

			<v-btn v-if="hasProtocolSteps"
				icon
				large
				:title="t('vtramit','Protocol steps')"
				@click="goToRightTab(protocolStepsOffset, true)">
				<v-icon>mdi-format-list-checks</v-icon>
			</v-btn>

			<v-badge v-if="hasUploads"
				:color="badgeColor(uploadsQty)"
				:value="true"
				overlap>
				<template #badge>
					{{ uploadsQty }}
				</template>
				<v-btn icon
					large
					:title="t('vtramit','Upload documents')"
					@click="goToRightTab(uploadOffset, true)">
					<v-icon>mdi-upload</v-icon>
				</v-btn>
			</v-badge>

			<v-badge v-if="hasDownloads"
				:color="badgeColor(downloadsQty)"
				:value="true"
				overlap>
				<template #badge>
					{{ downloadsQty }}
				</template>
				<v-btn icon
					large
					:title="t('vtramit','Download documents')"
					@click="goToRightDownload(true)">
					<v-icon>mdi-download</v-icon>
				</v-btn>
			</v-badge>

			<v-badge v-if="hasSignature"
				:color="badgeColor(signatureDownloadsQty)"
				:value="true"
				overlap>
				<template #badge>
					<span>{{ signatureDownloadsQty }}</span>
				</template>
				<v-btn icon
					large
					:title="t('vtramit','Sign documents')"
					@click="goToRightTab(signatureOffset, true)">
					<v-icon>mdi-pen</v-icon>
				</v-btn>
			</v-badge>
			<RemoteCommands v-if="allowRemoteCommands && jitsiRoomCode" :jitsi-room-code="jitsiRoomCode" />
			<LanguageSelector v-if="hasMultipleLanguages" :allowed-languages="config.languages" />
			<BackToAdmin v-if="procedure" :procedure-id="procedure.id" />
		</div>
	</v-row>
</template>

<script>
import Procedure from './Procedure.vue'
// import NextcloudPanel from './NextcloudPanel.vue'
import BackToAdmin from './BackToAdmin.vue'
import ProtocolSteps from './ProtocolSteps.vue'
import VideoCall from './VideoCall.vue'
import Documentation from './Documentation.vue'
import SignaturePanel from './SignaturePanel.vue'
import TopicHelp from './TopicHelp.vue'
import RGPD from './RGPD.vue'
import LanguageSelector from './LanguageSelector.vue'
import RemoteCommands from './RemoteCommands.vue'
import FilesPanel from './FilesPanel.vue'
import ComponentStatus from '../../../mixins/componentStatus.mixin.js'
import MessageManager from '../../../mixins/messageManager.mixin.js'
import ApiService from '../../../mixins/apiService.mixin.js'

export default {
	name: 'DashboardDesktop',
	components: {
		Procedure,
		// NextcloudPanel,
		BackToAdmin,
		VideoCall,
		Documentation,
		SignaturePanel,
		TopicHelp,
		RGPD,
		LanguageSelector,
		RemoteCommands,
		FilesPanel,
		ProtocolSteps,
	},
	mixins: [ComponentStatus, MessageManager, ApiService],
	props: {
		procedure: { type: Object, required: true },
		config: { type: Object, required: true },
		stakeholder: { type: Object, required: true },
		rgpdOptions: { type: Array, required: true },
	},
	data() {
		return {
			tabsLeft: 0,
			tabsRight: 0,
			leftWidthPx: 960,
			minLeftPx: 420,
			minRightPx: 360,
			splitterWidth: 2,
			dragging: false,
			rafId: null,
			lastClientX: null,
			splitterEl: null,
			isFullscreen: false,
			fullscreenSide: null,
			prevBodyOverflow: '',
			tabValueUploads: 0,
			isVideocallStarted: false,
			filesDownloads: [],
			isLoadingDownloads: false,
			filesUploads: [],
			isLoadingUploads: false,
			signatureUploadsQty: 0,
			signatureDownloadsQty: 0,
			autoRefreshIntervalMs: 10000,
			uploadsRefreshTimer: null,
			downloadsRefreshTimer: null,
			lastUploadsRefresh: 0,
			lastDownloadsRefresh: 0,
			leftMoreMenu: false,
			hasAutoSelectedLeftTab: false,
		}
	},
	computed: {
		procedureType() {
			const id = this.procedure && this.procedure.procedureTypeId
			return this.$store.getters['procedureType/readById'](id)
		},
		jitsiRoomCode() {
			return this.procedure && this.procedure.jitsiRoomCode
		},
		allowRemoteCommands() {
			const properties = this.procedureType && this.procedureType.attendantPortalProperties
			return !!(properties && properties.allowRemoteCommands === true)
		},
		hasMultipleLanguages() {
			const langs = this.config && this.config.languages
			if (!langs) return false

			// Array of codes
			if (Array.isArray(langs)) {
				return new Set(langs.filter(Boolean)).size > 1
			}

			// Object { ca: 'Català', es: 'Castellano', ... } or { ca: true, es: true }
			if (typeof langs === 'object') {
				return new Set(Object.keys(langs)).size > 1
			}

			// String "ca,es,en" (just in case)
			if (typeof langs === 'string') {
				return new Set(langs.split(',').map(s => s.trim()).filter(Boolean)).size > 1
			}

			return false
		},
		hasDocumentation() {
			return !!(this.config.documentationUrl)
		},
		hasTopicHelpUrl() {
			return !!(this.config.topicHelpUrl)
		},
		hasRGPDOptions() {
			return this.rgpdOptions.length !== 0
		},
		hasVideocall() {
			return !!(this.procedure.fullJitsiRoomStaffUrl)
		},
		hasProtocolSteps() {
			return this.$store.getters['protocolSteps/steps'].length > 0
		},
		hasUploads() {
			return !!(this.procedure.staffUploadsUrl)
		},
		hasDownloads() {
			return !!(this.procedure.staffDownloadsUrl)
		},
		hasSignature() {
			return this.config.isSignatureEnabled === true
		},
		hasColLeft() {
			return (this.hasDocumentation || this.hasTopicHelpUrl || this.hasVideocall || this.hasRGPDOptions)
		},
		hasColRight() {
			return true // (this.hasUploads || this.hasDownloads || this.hasSignature)
		},
		numColsLeft() {
			return this.hasColRight ? 7 : 12
		},
		leftStyle() {
			if (!this.hasColRight) {
				return {
					flex: '1 1 auto',
					maxWidth: 'none',
					minWidth: '0',
				}
			}
			return {
				flex: `0 0 ${this.leftWidthPx}px`,
				maxWidth: 'none',
				minWidth: '0',
			}
		},
		rightStyle() {
			return {
				flex: '1 1 auto',
				minWidth: this.minRightPx + 'px',
			}
		},
		leftColClass() {
			return { 'region--fullscreen': this.isFullscreen && this.fullscreenSide === 'left' }
		},
		rightColClass() {
			return { 'region--fullscreen': this.isFullscreen && this.fullscreenSide === 'right' }
		},
		// Indicate when the left column is narrow and we should collapse extra actions
		isLeftToolbarCompact() {
			if (!this.hasColRight) {
				return false
			}
			return this.leftWidthPx < 600
		},
		// Left column actions offset
		documentationOffset() {
			return 'documentationOffset'
		},
		topicHelpTabOffset() {
			return 'topicHelpTabOffset'
		},
		rgpdTabOffset() {
			return 'rgpdTabOffset'
		},
		videoCallTabOffset() {
			return 'videoCallTabOffset'
		},

		// Right column actions offset
		procedureInfoOffset() {
			return 'procedureInfoOffset'
		},
		protocolStepsOffset() {
			return 'protocolStepsOffset'
		},
		uploadOffset() {
			return 'uploadOffset'
		},
		downloadOffset() {
			return 'downloadOffset'
		},
		signatureOffset() {
			return 'signatureOffset'
		},

		// Counters for badges
		uploadsQty() {
			return Array.isArray(this.filesUploads) ? this.filesUploads.length : 0
		},
		downloadsQty() {
			return Array.isArray(this.filesDownloads) ? this.filesDownloads.length : 0
		},
	},
	watch: {
		'procedure.procedureTypeId': {
			immediate: true,
			handler(newId) {
				if (newId) {
					this.$store
						.dispatch('procedureType/readById', { id: newId })
						.catch(() => {})
				}
			},
		},
		tabsRight(newVal) {
			if (newVal === this.downloadOffset) {
				this.loadDownloads()
				this.loadUploads()
			}
		},
		'procedure.id': {
			immediate: true,
			handler(id) {
				if (id) {
					this.$store.commit('protocolSteps/setSteps', [])
					this.$store.dispatch('protocolSteps/fetchSteps', id).catch(() => {})
					this.loadDownloads()
					this.loadUploads()
					this.startDownloadsAutoRefresh()
					this.startUploadsAutoRefresh()
					if (!this.hasAutoSelectedLeftTab) {
						this.setDefaultTab()
						this.hasAutoSelectedLeftTab = true
					}
				} else {
					this.$store.commit('protocolSteps/setSteps', [])
					this.filesDownloads = []
					this.filesUploads = []
					this.stopDownloadsAutoRefresh()
					this.stopUploadsAutoRefresh()
				}
			},
		},
		hasUploads: {
			immediate: true,
			handler(enabled) {
				if (enabled) {
					this.startUploadsAutoRefresh()
				} else {
					this.stopUploadsAutoRefresh()
				}
			},
		},
		hasDownloads: {
			immediate: true,
			handler(enabled) {
				if (enabled) {
					this.startDownloadsAutoRefresh()
				} else {
					this.stopDownloadsAutoRefresh()
				}
			},
		},
	},
	mounted() {
		this.$nextTick(() => {
			const row = this.$refs.desktopRow
			if (!row) return
			const rect = row.getBoundingClientRect()
			const total = rect.width
			const desiredLeft = this.hasColLeft && this.hasColRight ? (total * (this.numColsLeft / 12)) : (total - this.minRightPx - this.splitterWidth)
			const maxLeftPx = total - this.minRightPx - this.splitterWidth
			this.leftWidthPx = Math.round(Math.max(this.minLeftPx, Math.min(desiredLeft, maxLeftPx)))
		})

		this._onResize = () => {
			const row = this.$refs.desktopRow
			if (!row) return
			const rect = row.getBoundingClientRect()
			const maxLeftPx = rect.width - this.minRightPx - this.splitterWidth
			this.leftWidthPx = Math.round(Math.max(this.minLeftPx, Math.min(this.leftWidthPx, maxLeftPx)))
			this.stopDrag()
		}
		window.addEventListener('resize', this._onResize)
	},
	beforeDestroy() {
		this.stopDrag()
		if (this._onResize) {
			window.removeEventListener('resize', this._onResize)
		}
		if (this.rafId) {
			cancelAnimationFrame(this.rafId)
			this.rafId = null
		}
		document.body.style.cursor = ''
		document.body.style.userSelect = ''
		document.body.style.overflow = this.prevBodyOverflow || ''
		this.stopUploadsAutoRefresh()
		this.stopDownloadsAutoRefresh()
	},
	created() {
		const id = this.procedure && this.procedure.procedureTypeId
		if (id) {
			this.$store.dispatch('procedureType/readById', { id }).catch(() => {})
		}
	},
	methods: {
		setDefaultTab() {
			if (this.hasVideocall) {
				this.tabsLeft = this.videoCallTabOffset
			} else if (this.hasDocumentation) {
				this.tabsLeft = this.documentationOffset
			} else if (this.hasRGPDOptions) {
				this.tabsLeft = this.rgpdTabOffset
			}
		},
		onClickTopicHelp() {
			if (this.config.topicHelpTarget) {
				window.open(this.config.topicHelpUrl, this.config.topicHelpTarget)
			} else {
				this.tabsLeft = this.topicHelpTabOffset
			}
		},
		startDrag(e) {
			e.preventDefault()
			this.dragging = true
			this.lastClientX = e.clientX
			this.splitterEl = e.currentTarget
			if (this.splitterEl && this.splitterEl.setPointerCapture) {
				try { this.splitterEl.setPointerCapture(e.pointerId) } catch (_) {}
			}
			if (this.splitterEl) {
				this.splitterEl.addEventListener('pointermove', this.onPointerMove, { passive: false })
				this.splitterEl.addEventListener('pointerup', this.stopDrag, { passive: false })
				this.splitterEl.addEventListener('pointercancel', this.stopDrag, { passive: false })
			}
			document.body.style.cursor = 'col-resize'
			document.body.style.userSelect = 'none'
		},
		onPointerMove(e) {
			e.preventDefault()
			this.lastClientX = e.clientX
			if (this.rafId) return
			this.rafId = requestAnimationFrame(() => {
				this.rafId = null
				const row = this.$refs.desktopRow
				if (!row) return
				const rect = row.getBoundingClientRect()
				let proposed = this.lastClientX - rect.left - (this.splitterWidth / 2)
				const maxLeftPx = rect.width - this.minRightPx - this.splitterWidth
				proposed = Math.max(this.minLeftPx, Math.min(proposed, maxLeftPx))
				this.leftWidthPx = Math.round(proposed)
			})
		},
		stopDrag(e) {
			if (!this.dragging) return
			this.dragging = false
			if (this.splitterEl) {
				if (e && e.pointerId != null && this.splitterEl.releasePointerCapture) {
					try { this.splitterEl.releasePointerCapture(e.pointerId) } catch (_) {}
				}
				this.splitterEl.removeEventListener('pointermove', this.onPointerMove)
				this.splitterEl.removeEventListener('pointerup', this.stopDrag)
				this.splitterEl.removeEventListener('pointercancel', this.stopDrag)
			}
			if (this.rafId) {
				cancelAnimationFrame(this.rafId)
				this.rafId = null
			}
			document.body.style.cursor = ''
			document.body.style.userSelect = ''
			this.splitterEl = null
		},
		enterFullscreen(side) {
			if (!this.isFullscreen) {
				this.prevBodyOverflow = document.body.style.overflow
				document.body.style.overflow = 'hidden'
			}
			this.isFullscreen = true
			this.fullscreenSide = side
		},
		exitFullscreen() {
			this.isFullscreen = false
			this.fullscreenSide = null
			document.body.style.overflow = this.prevBodyOverflow || ''
		},
		updateSignatureCounts(payload = {}) {
			const { uploads, downloads } = payload || {}
			const uploadCount = Number(uploads)
			const downloadCount = Number(downloads)
			this.signatureUploadsQty = Number.isFinite(uploadCount) ? uploadCount : 0
			this.signatureDownloadsQty = Number.isFinite(downloadCount) ? downloadCount : 0
		},
		startUploadsAutoRefresh() {
			this.stopUploadsAutoRefresh()
			if (!this.hasUploads || !this.procedure || !this.procedure.id) return
			this.uploadsRefreshTimer = setInterval(() => this.maybeRefreshUploads(), this.autoRefreshIntervalMs)
		},
		stopUploadsAutoRefresh() {
			if (this.uploadsRefreshTimer) {
				clearInterval(this.uploadsRefreshTimer)
				this.uploadsRefreshTimer = null
			}
		},
		maybeRefreshUploads() {
			if (!this.hasUploads || this.isLoadingUploads || !this.procedure || !this.procedure.id) return
			const elapsed = Date.now() - (this.lastUploadsRefresh || 0)
			if (elapsed < this.autoRefreshIntervalMs) return
			this.loadUploads()
		},
		startDownloadsAutoRefresh() {
			this.stopDownloadsAutoRefresh()
			if (!this.hasDownloads || !this.procedure || !this.procedure.id) return
			this.downloadsRefreshTimer = setInterval(() => this.maybeRefreshDownloads(), this.autoRefreshIntervalMs)
		},
		stopDownloadsAutoRefresh() {
			if (this.downloadsRefreshTimer) {
				clearInterval(this.downloadsRefreshTimer)
				this.downloadsRefreshTimer = null
			}
		},
		maybeRefreshDownloads() {
			if (!this.hasDownloads || this.isLoadingDownloads || !this.procedure || !this.procedure.id) return
			const elapsed = Date.now() - (this.lastDownloadsRefresh || 0)
			if (elapsed < this.autoRefreshIntervalMs) return
			this.loadDownloads()
		},
		goToLeftTab(index, exitFullscreen = false) {
			if (this.isFullscreen) this.fullscreenSide = 'left'
			this.$nextTick(() => {
				this.tabsLeft = index
				if (exitFullscreen && this.isFullscreen) {
					this.exitFullscreen()
				}
			})
		},
		openTopicHelpAction() {
			if (this.config.topicHelpTarget) {
				window.open(this.config.topicHelpUrl, this.config.topicHelpTarget)
			} else {
				this.goToLeftTab(this.topicHelpTabOffset)
			}
		},
		goToRightTab(index, exitFullscreen = false) {
			if (this.isFullscreen) this.fullscreenSide = 'right'
			this.$nextTick(() => {
				this.tabsRight = index
				if (exitFullscreen && this.isFullscreen) {
					this.exitFullscreen()
				}
			})
		},
		goToRightDownload(exitFullscreen = false) {
			this.goToRightTab(this.downloadOffset)
			this.$nextTick(() => {
				if (exitFullscreen && this.isFullscreen) {
					this.exitFullscreen()
				}
			})
		},

		// ------------------------------------------------------------------------
		// Returns Vuetify badge color based on a numeric value (0 => blue, >0 => green)
		badgeColor(value) {
			return Number(value) > 0 ? 'green' : 'blue'
		},

		// ------------------------------------------------------------------------
		// Triggers a snapshot on the nested VideoCall -> Jitsi component, if present
		captureSnapshot() {
			// console.info('[dashboard] captureSnapshot click')

			const vc = this.$refs && this.$refs.videoCall
			if (!vc || typeof vc.captureSnapshot !== 'function') return

			// Ensure the video tab is visible so the component is mounted
			if (this.tabsLeft !== this.videoCallTabOffset) {
				this.tabsLeft = this.videoCallTabOffset
			}

			this.$nextTick(() => vc.captureSnapshot())
		},

		// ------------------------------------------------------------------------
		// Load files for the procedure download folder using apiService.mixin
		async loadDownloads() {
			if (!this.procedure || !this.procedure.id) {
				return
			}

			if (this.isLoadingDownloads) {
				return
			}

			this.isLoadingDownloads = true
			try {
				const url = `/apps/vtramit/web/2.0/procedure/${this.procedure.id}/file/download-folder`
				const data = await this.get(url)
				this.filesDownloads = Array.isArray(data && data.list) ? data.list : []
			} catch (e) {
				// apiService already shows a generic error
				this.filesDownloads = []
			} finally {
				this.isLoadingDownloads = false
				this.lastDownloadsRefresh = Date.now()
			}
		},

		// ------------------------------------------------------------------------
		// Load files for the procedure upload folder using apiService.mixin
		async loadUploads() {
			if (!this.procedure || !this.procedure.id) {
				return
			}

			if (this.isLoadingUploads) {
				return
			}

			this.isLoadingUploads = true
			try {
				const url = `/apps/vtramit/web/2.0/procedure/${this.procedure.id}/file/upload-folder`
				const data = await this.get(url)
				this.filesUploads = Array.isArray(data && data.list) ? data.list : []
			} catch (e) {
				// apiService already shows a generic error
				this.filesUploads = []
			} finally {
				this.isLoadingUploads = false
				this.lastUploadsRefresh = Date.now()
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.tab--toolbar--left {
	flex: 1 1 auto;
	min-width: 0; /* Allow left tabs to shrink so right-side buttons always have room */
	overflow: hidden; /* Prevent tabs content from overlapping the right buttons */
}
.tab--toolbar--right {
	$tab-color-active: #005CD1;
	$tab-color-inactive: #656D72;
	$tab-hairline: #E5E6E8;

	/* Flat toolbar — no shadow, white surface, hairline bottom matches the panel below. */
	&.v-toolbar {
		box-shadow: none !important;
		border-bottom: 1px solid $tab-hairline;
	}
	::v-deep .v-toolbar__content { padding: 0; }

	::v-deep .v-tabs-bar {
		background: #fff !important;
	}

	/* Active indicator: 2px bar at the bottom in the new primary blue. */
	::v-deep .v-tabs-slider {
		background: $tab-color-active !important;
		height: 2px !important;
	}

	/* Tab buttons: equal flex, no bg, full-height clickable region. */
	::v-deep .v-tab {
		flex: 1;
		min-width: 0;
		padding: 0;
		text-transform: none;
		letter-spacing: 0;
		opacity: 1;

		&::before { display: none; }

		.v-btn--icon {
			width: 100%;
			height: 56px;
			border-radius: 0;
			color: $tab-color-inactive !important;

			.v-icon { font-size: 22px; transition: color .15s; }
		}

		&:hover .v-btn--icon { background: rgba(0, 92, 209, .04); }

		&.v-tab--active .v-btn--icon,
		&.v-tab--active .v-btn--icon .v-icon {
			color: $tab-color-active !important;
		}
	}

	/* Badge restyle: pill shape, top-right of the icon.
	 * Background color is still driven by the Vuetify :color prop (badgeColor()),
	 * we only override geometry so the pill matches the redesign. */
	::v-deep .v-badge__badge {
		font-weight: 700;
		font-size: 10px;
		min-width: 18px;
		height: 18px;
		padding: 0 5px;
		border-radius: 9999px;
		inset: auto auto calc(100% - 18px) calc(100% - 12px) !important;
	}
}
.splitter {
	height: 100%;
	cursor: col-resize;
	background: rgba(0,0,0,.08);
	transition: background .15s ease;
	will-change: width;
	z-index: 2;
}
.splitter.dragging {
	background: rgba(0,0,0,.18);
}
.region--fullscreen {
	position: fixed !important;
	inset: 0;
	width: calc(100% - 44px);
	height: 100vh;
	z-index: 10000;
	background: #fff;
	display: flex;
	flex-direction: column;
	overflow: auto;
}
.fullscreen-action-bar {
	position: fixed !important;
	top: 0;
	right: 0;
	height: 100vh;
	width: 44px;
	z-index: 10000;
	background: rgba(255,255,255,.92);
	backdrop-filter: saturate(1.2) blur(6px);
	display: flex;
	flex-direction: column;
	overflow: auto;
	align-items: center;

	::v-deep .v-badge__badge {
    inset: auto auto calc(100% - 20px) calc(100% - 20px) !important;
    background-color: unset !important;
    border: transparent !important;

		&.green {
			color: #4CAF50;
		}

		&.blue {
			color: #2196F3;
		}
	}
}
.signature-tab--badge {
	margin-right: 16px;
}
.signature-tab--badge ::v-deep .v-badge__wrapper {
	left: auto;
	right: 7px;
}
</style>

<style>
.v-list-item > div {
	margin: 0 auto;
}
</style>
