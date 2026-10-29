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
	<v-row no-gutters ref="desktopRow" style="height:100%; display:flex; position:relative; flex-wrap:nowrap;">
		<v-col
			v-if="hasColLeft"
			v-show="!isFullscreen || fullscreenSide === 'left'"
			class="d-flex flex-column"
			:class="leftColClass"
			:style="leftStyle"
			style="position:relative;">

			<v-toolbar class="flex-grow-0" v-if="!isFullscreen">

				<!-- Main buttons -->
				<v-tabs v-model="tabsLeft" background-color="shadowBackground" left>
					<v-tab v-if="hasDocumentation" data-test="help">
						<slot name="default">
							<v-btn :color="tabsLeft === documentationOffset ? 'primary' : undefined">
								{{ t('vtramit', 'Help') }}
							</v-btn>
						</slot>
					</v-tab>
					<v-tab v-if="hasTopicHelpUrl"
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
					<v-tab v-if="hasRGPDOptions" data-test="rgdp">
						<slot name="default">
							<v-btn :color="tabsLeft === rgpdTabOffset ? 'primary' : undefined">
								{{ t('vtramit', 'RGPD') }}
							</v-btn>
						</slot>
					</v-tab>
					<v-tab v-if="hasVideocall" data-test="videocall">
						<slot name="default">
							<v-btn :color="tabsLeft === videoCallTabOffset ? 'primary' : undefined">
								{{ t('vtramit', 'Access to videocall') }}
							</v-btn>
						</slot>
					</v-tab>
					<v-tab v-if="hasVideocallTest" data-test="videocall-test">
						<slot name="default">
							<v-btn :color="tabsLeft === videocallTestTabOffset ? 'primary' : undefined">
								{{ t('vtramit', 'Test videocall') }}
							</v-btn>
						</slot>
					</v-tab>
				</v-tabs>
				<!-- END -- Main buttons -->

				<v-spacer />

				<!-- Additional buttons: language selector | fullscreen -->
				<LanguageSelector v-if="hasMultipleLanguages" :allowedLanguages="config.languages" />
				<v-btn icon @click="enterFullscreen('left')">
					<v-icon>mdi-fullscreen</v-icon>
				</v-btn>
				<!-- END -- Additional buttons -->
			</v-toolbar>

			<v-tabs-items v-model="tabsLeft" class="flex-grow-1" style="height:100%;">
				<v-tab-item v-if="hasDocumentation" style="height:100%;">
					<Documentation :appointment="appointment" :config="config" />
				</v-tab-item>
				<v-tab-item v-if="hasTopicHelpUrl" style="height:100%;">
					<TopicHelp :appointment="appointment" :config="config" />
				</v-tab-item>
				<v-tab-item v-if="hasRGPDOptions" style="height:100%;">
					<div class="d-flex flex-column align-center pt-40">
						<RGPD
							ref="rpgd"
							mode="edit"
							:appointment="appointment"
							:stakeholder="stakeholder"
							:rgpd-options="rgpdOptions" />
					</div>
				</v-tab-item>
				<v-tab-item v-if="hasVideocall" style="height:100%;">
					<VideoCall :appointment="appointment" :config="config" />
				</v-tab-item>
				<v-tab-item v-if="hasVideocallTest" style="height:100%;">
					<VideocallTest :appointment="appointment" :config="config" :stakeholder="stakeholder" />
				</v-tab-item>
			</v-tabs-items>
		</v-col>

		<div
			v-if="hasColLeft && hasColRight && !isFullscreen"
			class="splitter"
			:class="{ dragging }"
			:style="{ flex: `0 0 ${splitterWidth}px` }"
			@pointerdown="startDrag">
		</div>

		<v-col
			v-if="hasColRight && (!isFullscreen || fullscreenSide==='right')"
			class="d-flex flex-column"
			:class="rightColClass"
			:style="rightStyle"
			style="position:relative;">

			<v-toolbar class="flex-grow-0" v-if="!isFullscreen">
				<v-tabs v-model="tabsRight" background-color="shadowBackground" right>
					<v-tab v-if="hasUploads" data-test="upload">
						<v-btn :color="tabsRight === uploadOffset ? 'primary' : undefined">
							{{ t('vtramit', 'Upload') }}
						</v-btn>
					</v-tab>
					<v-tab v-if="hasDownloads" @click="clickTabDownloads" data-test="download">
						<v-btn :color="tabsRight === downloadOffset ? 'primary' : undefined">
							{{ t('vtramit', 'Download') }}
						</v-btn>
					</v-tab>
					<v-tab v-if="hasSignature" data-test="sign">
						<slot name="default">
							<v-btn :color="tabsRight === signatureOffset ? 'primary' : undefined">
								{{ t('vtramit', 'Sign') }}
							</v-btn>
						</slot>
					</v-tab>
				</v-tabs>
			</v-toolbar>

			<v-tabs-items v-model="tabsRight" class="flex-grow-1" style="height:100%;">
				<v-tab-item v-if="hasUploads" eager style="height:100%;">
					<NextcloudPanel
						data-test="fileUploads"
						:src="appointment.sharedUrlUploads"
						:active="tabsRight === uploadOffset"
					/>
				</v-tab-item>
				<v-tab-item v-if="hasDownloads" eager style="height:100%;">
					<NextcloudPanel
						data-test="fileDownloads"
						ref="fileDownloads"
						:src="appointment.sharedUrlDownloads"
						:active="tabsRight === downloadOffset"
						:autoDownload="config.outputAutodownload"
					/>
				</v-tab-item>
				<v-tab-item v-if="hasSignature" style="height:100%;">
					<SignDocuments data-test="signDocuments" :appointment="appointment" :config="config" />
				</v-tab-item>
			</v-tabs-items>
		</v-col>

		<!-- Floating action bar (fullscreen): includes all left/right actions -->
		<div v-if="isFullscreen" class="fullscreen-action-bar">
			<v-btn icon large @click="exitFullscreen" :title="t('vtramit','Exit fullscreen')">
				<v-icon>mdi-fullscreen-exit</v-icon>
			</v-btn>
			<v-btn v-if="hasDocumentation" icon large @click="goToLeftTab(documentationOffset)" :title="t('vtramit','Help')">
				<v-icon>mdi-help-circle-outline</v-icon>
			</v-btn>
			<v-btn v-if="hasTopicHelpUrl" icon large @click="openTopicHelpAction" :title="t('vtramit','My procedure')">
				<v-icon>mdi-open-in-new</v-icon>
			</v-btn>
			<v-btn v-if="hasRGPDOptions" icon large @click="goToLeftTab(rgpdTabOffset)" :title="t('vtramit','Manage RGPD options')">
				<v-icon>mdi-shield-lock-outline</v-icon>
			</v-btn>
			<v-btn v-if="hasVideocall" icon large @click="goToLeftTab(videoCallTabOffset)" :title="t('vtramit','Access to videocall')">
				<v-icon>mdi-video-outline</v-icon>
			</v-btn>
			<v-btn v-if="hasUploads" icon large @click="goToRightTab(0, true)" :title="t('vtramit','Upload documents')">
				<v-icon>mdi-upload</v-icon>
			</v-btn>
			<v-btn v-if="hasDownloads" icon large @click="goToRightDownload(true)" :title="t('vtramit','Download documents')">
				<v-icon>mdi-download</v-icon>
			</v-btn>
			<v-btn v-if="hasSignature" icon large @click="goToRightTab(signatureOffset, true)" :title="t('vtramit','Sign documents')">
				<v-icon>mdi-pen</v-icon>
			</v-btn>
			<LanguageSelector v-if="hasMultipleLanguages" :allowedLanguages="config.languages" />
		</div>
	</v-row>
</template>

<script>
import NextcloudPanel from './NextcloudPanel.vue'
import VideoCall from './VideoCall.vue'
import VideocallTest from './VideocallTest.vue'
import Documentation from './Documentation.vue'
import SignDocuments from './SignDocuments.vue'
import TopicHelp from './TopicHelp.vue'
import RGPD from '../../../components/RGPD.vue'
import LanguageSelector from '../../../components/LanguageSelector.vue'

export default {
	name: 'DashboardDesktop',
	components: {
		NextcloudPanel,
		VideoCall,
		VideocallTest,
		Documentation,
		SignDocuments,
		TopicHelp,
		RGPD,
		LanguageSelector,
	},
	props: {
		appointment: { type: Object, required: true },
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
		}
	},
	computed: {
		hasMultipleLanguages() {
			const langs = this.config && this.config.languages;
			if (!langs) return false;

			// Array de códigos
			if (Array.isArray(langs)) {
				return new Set(langs.filter(Boolean)).size > 1;
			}

			// Objeto { ca: 'Català', es: 'Castellano', ... } o { ca: true, es: true }
			if (typeof langs === 'object') {
				return new Set(Object.keys(langs)).size > 1;
			}

			// Cadena "ca,es,en" (por si acaso)
			if (typeof langs === 'string') {
				return new Set(langs.split(',').map(s => s.trim()).filter(Boolean)).size > 1;
			}

			return false;
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
			return !!(this.appointment.jitsiRoomUrl)
		},
		hasVideocallTest() {
			return !!(this.config.robotTestUrl)
		},
		hasUploads() {
			return !!(this.appointment.sharedUrlUploads) && !this.config.uploadsHide
		},
		hasDownloads() {
			return !!(this.appointment.sharedUrlDownloads) && !this.config.downloadsHide
		},
		hasSignature() {
			return this.config.isSignatureEnabled === true && !this.config.signatureHide
		},
		hasColLeft() {
			return (this.hasDocumentation || this.hasTopicHelpUrl || this.hasVideocall || this.hasRGPDOptions || this.hasVideocallTest)
		},
		hasColRight() {
			return (this.hasUploads || this.hasDownloads || this.hasSignature)
		},
		numColsLeft() {
			return this.hasColRight ? 9 : 12
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

		// Left column actions offset
		documentationOffset() {
			return 0
		},
		topicHelpTabOffset() {
			return (this.hasDocumentation ? 1 : 0)
		},
		rgpdTabOffset() {
			return (this.hasDocumentation ? 1 : 0) + (this.hasTopicHelpUrl ? 1 : 0)
		},
		videoCallTabOffset() {
			return (this.hasDocumentation ? 1 : 0) + (this.hasTopicHelpUrl ? 1 : 0) + (this.hasRGPDOptions ? 1 : 0)
		},
		videocallTestTabOffset() {
			return this.videoCallTabOffset + (this.hasVideocall ? 1 : 0)
		},

    // Right column actions offset
		uploadOffset() {
			return 0
		},
		downloadOffset() {
			return this.hasUploads ? 1 : 0
		},
		signatureOffset() {
			return (this.hasUploads ? 1 : 0) + (this.hasDownloads ? 1 : 0)
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
	},
	created() {
		this.setDefaultTab()
		if (this.config && this.config.forceFullscreen) {
			this.$nextTick(() => this.enterFullscreen('left'))
		}
	},
	methods: {
		clickTabDownloads() {
			this.$nextTick(() => {
				this.$refs.fileDownloads && this.$refs.fileDownloads.triggerDownload()
			})
		},
		setDefaultTab() {
			// Left column: honour the admin's "Default view Desktop (Left)" when
			// set; otherwise fall back to the historical priority.
			if (!this.applyConfiguredTab('defaultViewDesktopLeft', this.leftTabMap(), 'tabsLeft')) {
				if (this.hasVideocall) {
					this.tabsLeft = this.videoCallTabOffset
				}

				else if (this.hasDocumentation) {
					this.tabsLeft = this.documentationOffset
				}

				else if (this.hasRGPDOptions) {
					this.tabsLeft = this.rgpdTabOffset
				}
			}

			// Right column: honour "Default view Desktop (Right)" when set.
			this.applyConfiguredTab('defaultViewDesktopRight', this.rightTabMap(), 'tabsRight')
		},
		leftTabMap() {
			return {
				'videocall':      { has: this.hasVideocall,     offset: this.videoCallTabOffset },
				'documentation':  { has: this.hasDocumentation, offset: this.documentationOffset },
				'rgpd':           { has: this.hasRGPDOptions,   offset: this.rgpdTabOffset },
				'videocall-test': { has: this.hasVideocallTest, offset: this.videocallTestTabOffset },
			}
		},
		rightTabMap() {
			return {
				'uploads':   { has: this.hasUploads,   offset: this.uploadOffset },
				'downloads': { has: this.hasDownloads, offset: this.downloadOffset },
				'signature': { has: this.hasSignature, offset: this.signatureOffset },
			}
		},
		// Apply a configured default view to the given tab model. Returns true
		// when a valid, available view was applied; false when unset/unavailable
		// so the caller can fall back.
		applyConfiguredTab(configKey, map, tabModel) {
			const view = this.config && this.config[configKey]
			if (!view) return false
			const target = map[view]
			if (target && target.has) {
				this[tabModel] = target.offset
				return true
			}
			return false
		},
		onClickTopicHelp() {
			if (this.config.topicHelpTarget) {
				window.open(this.config.topicHelpUrl, this.config.topicHelpTarget);
			} else {
				this.tabsLeft = this.topicHelpTabOffset;
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
        this.clickTabDownloads()
        if (exitFullscreen && this.isFullscreen) {
          this.exitFullscreen()
        }
      })
		},
	},
}
</script>

<style scoped>
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
}
</style>
