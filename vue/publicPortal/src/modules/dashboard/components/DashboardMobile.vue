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
	<v-sheet tile color="#fff" class="dashboard-mobile">
		<v-row justify="center" class="flex-grow-1" style="height:100%;">
			<v-col cols="12">
				<div class="toolbar-wrap">
					<div class="toolbar" ref="toolbar">
						<v-btn v-if="hasDocumentation" data-test="help" icon large @click="panelOptions = documentationOffset" :title="t('vtramit','Help')">
							<v-icon>mdi-help-circle-outline</v-icon>
						</v-btn>
						<v-btn v-if="hasTopicHelpUrl" icon large @click="openTopicHelpAction" :title="t('vtramit','My procedure')">
							<v-icon>mdi-open-in-new</v-icon>
						</v-btn>
						<v-btn v-if="hasRGPDOptions" data-test="rgdp" icon large @click="panelOptions = RGPDOffset" :title="t('vtramit','Manage RGPD options')">
							<v-icon>mdi-shield-lock-outline</v-icon>
						</v-btn>
						<v-btn v-if="hasVideocall" data-test="videocall" icon large @click="panelOptions = videoCallTabOffset" :title="t('vtramit','Access to videocall')">
							<v-icon>mdi-video-outline</v-icon>
						</v-btn>
						<v-btn v-if="hasVideocallTest" data-test="videocall-test" icon large @click="panelOptions = videocallTestTabOffset" :title="t('vtramit','Test videocall')">
							<v-icon>mdi-video-check</v-icon>
						</v-btn>
						<v-btn v-if="hasUploads" data-test="upload" icon large @click="panelOptions = uploadsOffest" :title="t('vtramit','Upload documents')">
							<v-icon>mdi-upload</v-icon>
						</v-btn>
						<v-btn v-if="hasDownloads" data-test="download" icon large @click="panelOptions = donwloadsOffset" :title="t('vtramit','Download documents')">
							<v-icon>mdi-download</v-icon>
						</v-btn>
						<v-btn v-if="hasSignature" data-test="sign" icon large @click="panelOptions = signatureOffset" :title="t('vtramit','Sign documents')">
							<v-icon>mdi-pen</v-icon>
						</v-btn>
						<LanguageSelector v-if="hasMultipleLanguages" :allowedLanguages="config.languages" />
					</div>

					<!-- Overlay arrows -->
					<button v-if="showLeftArrow" class="toolbar__arrow toolbar__arrow--left" @click="scrollToolbarBy(-120, 0)" aria-label="More left"><v-icon class="toolbar__icon">mdi-chevron-left</v-icon></button>
					<button v-if="showRightArrow" class="toolbar__arrow toolbar__arrow--right" @click="scrollToolbarBy(120, 0)" aria-label="More right"><v-icon class="toolbar__icon">mdi-chevron-right</v-icon></button>
					<button v-if="showUpArrow" class="toolbar__arrow toolbar__arrow--top" @click="scrollToolbarBy(0, -120)" aria-label="More up"><v-icon class="toolbar__icon">mdi-chevron-up</v-icon></button>
					<button v-if="showDownArrow" class="toolbar__arrow toolbar__arrow--bottom" @click="scrollToolbarBy(0, 120)" aria-label="More down"><v-icon class="toolbar__icon">mdi-chevron-down</v-icon></button>
				</div>

				<!-- NOTICE: Be careful on moving panels as some raises actions based on the index offset of the panel -->
				<!--				 Follow where "panelOptions" is used to validate changes -->
				<v-expansion-panels v-model="panelOptions" accordion>
					<v-expansion-panel v-if="hasDocumentation">
						<v-expansion-panel-content>
							<Documentation :appointment="appointment" :config="config" />
						</v-expansion-panel-content>
					</v-expansion-panel>

					<v-expansion-panel v-if="hasTopicHelpUrl">
						<v-expansion-panel-content>
							<TopicHelp :appointment="appointment" :config="config" />
						</v-expansion-panel-content>
					</v-expansion-panel>

					<v-expansion-panel v-if="hasRGPDOptions">
						<v-expansion-panel-content>
							<RGPD
								ref="rpgd"
								mode="edit"
								:appointment="appointment"
								:stakeholder="stakeholder"
								:rgpd-options="rgpdOptions" />
						</v-expansion-panel-content>
					</v-expansion-panel>

					<v-expansion-panel v-if="hasVideocall">
						<v-expansion-panel-content>
							<VideoCall data-test="videocall" :appointment="appointment" :config="config" />
						</v-expansion-panel-content>
					</v-expansion-panel>

					<v-expansion-panel v-if="hasVideocallTest">
						<v-expansion-panel-content>
							<VideocallTest :appointment="appointment" :config="config" :stakeholder="stakeholder" />
						</v-expansion-panel-content>
					</v-expansion-panel>

					<v-expansion-panel v-if="hasUploads">
						<v-expansion-panel-content>
							<AppointmentFilesUploads
								data-test="fileUploads"
								:appointment="appointment"
								:config="config"
								:active-acc="panelOptions"
								:acc-index="uploadsOffest" />
						</v-expansion-panel-content>
					</v-expansion-panel>

					<v-expansion-panel v-if="hasDownloads">
						<v-expansion-panel-content>
							<AppointmentFilesDownloads
								data-test="fileDownloads"
								ref="fileDownloadsMobile"
								:appointment="appointment"
								:config="config"
								:active-acc="panelOptions"
								:acc-index="donwloadsOffset" />
						</v-expansion-panel-content>
					</v-expansion-panel>

					<v-expansion-panel v-if="hasSignature">
						<v-expansion-panel-content>
							<SignDocuments data-test="signDocuments" :appointment="appointment" :config="config" />
						</v-expansion-panel-content>
					</v-expansion-panel>
				</v-expansion-panels>
			</v-col>
		</v-row>
	</v-sheet>
</template>

<script>
import AppointmentFilesUploads from './AppointmentFilesUploads'
import AppointmentFilesDownloads from './AppointmentFilesDownloads'
import VideoCall from './VideoCall'
import VideocallTest from './VideocallTest'
import Documentation from './Documentation'
import SignDocuments from './SignDocuments'
import TopicHelp from './TopicHelp'
import RGPD from '../../../components/RGPD.vue'
import LanguageSelector from '../../../components/LanguageSelector.vue'

export default {
	name: 'DashboardMobile',
	components: {
		AppointmentFilesUploads,
		AppointmentFilesDownloads,
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
			panelOptions: 0,
			showLeftArrow: false,
			showRightArrow: false,
			showUpArrow: false,
			showDownArrow: false,
		}
	},
	created() {
		this.setDefaultPanel()
	},
	mounted() {
		const el = this.$refs.toolbar
		if (!el) return

		this._onScrollToolbar = () => this.checkArrows()
		this._onResize = () => this.checkArrows()

		this.checkArrows()

		el.addEventListener('scroll', this._onScrollToolbar, { passive: true })
		window.addEventListener('resize', this._onResize)
		},
	beforeDestroy() {
		const el = this.$refs.toolbar
		if (el && this._onScrollToolbar) {
			el.removeEventListener('scroll', this._onScrollToolbar)
		}
		if (this._onResize) {
			window.removeEventListener('resize', this._onResize)
		}
	},
	computed: {
		hasMultipleLanguages() {
			const langs = this.config && this.config.languages;
			if (!langs) return false;

			if (Array.isArray(langs)) {
				return new Set(langs.filter(Boolean)).size > 1;
			}
			if (typeof langs === 'object') {
				return new Set(Object.keys(langs)).size > 1;
			}
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
		hasUploads() {
			return !!(this.appointment.sharedUrlUploads) && !this.config.uploadsHide
		},
		hasDownloads() {
			return !!(this.appointment.sharedUrlDownloads) && !this.config.downloadsHide
		},
		hasVideocall() {
			return !!(this.appointment.jitsiRoomUrl)
		},
		hasVideocallTest() {
			return !!(this.config.robotTestUrl)
		},
		hasSignature() {
			return this.config.isSignatureEnabled === true && !this.config.signatureHide
		},
		hasRGPDOptions() {
			return this.rgpdOptions.length !== 0
		},

		documentationOffset() {
			return 0
		},
		topicHelpOffset() {
			return (this.hasDocumentation ? 1 : 0)
		},
		RGPDOffset() {
			return (this.hasTopicHelpUrl ? 1 : 0) + (this.hasDocumentation ? 1 : 0)
		},
		videoCallTabOffset() {
			return (this.hasTopicHelpUrl ? 1 : 0) + (this.hasDocumentation ? 1 : 0) + (this.hasRGPDOptions ? 1 : 0)
		},
		videocallTestTabOffset() {
			return this.videoCallTabOffset + (this.hasVideocall ? 1 : 0)
		},
		uploadsOffest() {
			return this.videocallTestTabOffset + (this.hasVideocallTest ? 1 : 0)
		},
		donwloadsOffset() {
			return this.uploadsOffest + (this.hasUploads ? 1 : 0)
		},
		signatureOffset() {
			return this.donwloadsOffset + (this.hasDownloads ? 1 : 0)
		}
	},
	watch: {
		panelOptions(newValue) {
			// The downloads panel index is dynamic — compare against the
			// computed offset, not a recount that ignored documentation,
			// topic-help, RGPD and videocall-test panels.
			if (newValue === this.donwloadsOffset) this.clickTabDownloadsMobile()
		},
	},
	methods: {
		// Select the initial panel from the admin's "Default view Mobile"
		// (config.defaultViewMobile). Empty/'auto' or an unavailable view keeps
		// the current default (first available panel, index 0).
		setDefaultPanel() {
			const view = this.config && this.config.defaultViewMobile
			if (!view) return

			const map = {
				'videocall':      { has: this.hasVideocall,     offset: this.videoCallTabOffset },
				'videocall-test': { has: this.hasVideocallTest, offset: this.videocallTestTabOffset },
				'documentation':  { has: this.hasDocumentation, offset: this.documentationOffset },
				'uploads':        { has: this.hasUploads,       offset: this.uploadsOffest },
				'downloads':      { has: this.hasDownloads,     offset: this.donwloadsOffset },
			}
			const target = map[view]
			if (target && target.has) {
				this.panelOptions = target.offset
			}
		},
		onClickTopicHelp() {
			if (this.config.topicHelpTarget) {
				window.open(this.config.topicHelpUrl, this.config.topicHelpTarget)
			}
		},
		clickTabDownloadsMobile() {
			this.$nextTick(() => {
				this.$refs.fileDownloadsMobile && this.$refs.fileDownloadsMobile.clickLinkDownload()
			})
		},
		scrollToolbarBy(dx = 0, dy = 0) {
			const el = this.$refs.toolbar
			if (!el) return
			el.scrollBy({ left: dx, top: dy, behavior: 'smooth' })
		},
		checkArrows() {
			const el = this.$refs.toolbar
			if (!el) return
			const isLandscape = window.innerWidth > window.innerHeight

			if (isLandscape) {
				const top = el.scrollTop
				const maxTop = el.scrollHeight - el.clientHeight
				this.showUpArrow = top > 0
				this.showDownArrow = maxTop > 0 && top < (maxTop - 1)
				this.showLeftArrow = false
				this.showRightArrow = false
			} else {
				const left = el.scrollLeft
				const maxLeft = el.scrollWidth - el.clientWidth
				this.showLeftArrow = left > 0
				this.showRightArrow = maxLeft > 0 && left < (maxLeft - 1)
				this.showUpArrow = false
				this.showDownArrow = false
			}
		},
		openTopicHelpAction() {
			if (this.config.topicHelpTarget) {
				window.open(this.config.topicHelpUrl, this.config.topicHelpTarget)
			} else {
				this.panelOptions = this.topicHelpOffset
			}
		},
		onLangChanged (lang) {
			document.documentElement.setAttribute('lang', lang)
			this.$forceUpdate()
		},
	},
}
</script>

<style>
.dashboard-mobile > .row > .col {
	padding-bottom: 0;
}

.dashboard-mobile .v-expansion-panels--accordion {
	margin-top: 52px;
}

.dashboard-mobile .v-expansion-panel-content {
	inset: 0;
	min-height: calc(100dvh - 52px);
}

.dashboard-mobile .v-expansion-panel-content__wrap {
	padding: 0;
}

.dashboard-mobile .v-expansion-panels--accordion > .v-expansion-panel::before,
.dashboard-mobile .v-expansion-panels--accordion > .v-expansion-panel::after {
	display: none;
}

.dashboard-mobile .toolbar-wrap {
	position: fixed !important;
	top: 0;
	right: 0;
	left: 0;
	height: 52px;
	z-index: 10000;
	background: rgba(255,255,255,.98);
	backdrop-filter: saturate(1.2) blur(6px);
	padding-left: 10px;
	padding-right: 10px;
}

.dashboard-mobile .toolbar {
	height: 52px;
	padding: 4px 0;
	display: flex;
	flex-direction: row;
	justify-content: center;
	align-items: center;
	overflow-x: auto;
	overflow-y: hidden;
	-webkit-overflow-scrolling: touch;
	white-space: nowrap;
}

.dashboard-mobile .toolbar__arrow {
	position: absolute;
	border: 0;
	background: rgba(255,255,255,.98);
	box-shadow: 0px 0px 7px 3px rgba(0, 0, 0, .12), 0 0 0 1px rgba(0, 0, 0, .06);
	cursor: pointer;
	opacity: 1;
	z-index: 10001;
	padding: 0;
}

.dashboard-mobile .toolbar__icon {
	font-size: 13px;
	color: rgba(0,0,0,.95);
	text-shadow:
		0 0 0 currentColor,
		0 .5px 0 currentColor,
		0 -.5px 0 currentColor,
		.5px 0 0 currentColor,
		-.5px 0 0 currentColor;
}

.dashboard-mobile .toolbar__arrow--left .toolbar__icon,
.dashboard-mobile .toolbar__arrow--right .toolbar__icon {
	transform: scaleY(1.5);
}

.dashboard-mobile .toolbar__arrow--top .toolbar__icon,
.dashboard-mobile .toolbar__arrow--bottom .toolbar__icon {
	transform: scaleX(1.5);
}

.dashboard-mobile .toolbar__arrow--left,
.dashboard-mobile .toolbar__arrow--right {
	top: 0;
	margin-top: 4px;
	width: 10px;
	height: 44px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.dashboard-mobile .toolbar__arrow--left {
	left: 0;
}

.dashboard-mobile .toolbar__arrow--right {
	right: 0;
}

@media (orientation: landscape) {
	.dashboard-mobile .toolbar-wrap {
		top: 0;
		right: 0;
		bottom: 0;
		left: auto;
		width: 56px;
		height: 100dvh;
		padding-top: 10px;
		padding-bottom: 10px;
		padding-left: 0;
		padding-right: 0;
	}

	.dashboard-mobile .toolbar {
		width: 56px;
		height: 100dvh;
		padding: 0 4px;
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
		align-items: center;
		overflow-y: auto;
		overflow-x: hidden;
	}

	.dashboard-mobile .v-expansion-panels--accordion {
		margin-top: 0;
		margin-right: 56px;
	}

	.dashboard-mobile .v-expansion-panel-content {
		min-height: 100dvh;
	}

	.dashboard-mobile .toolbar__arrow--top,
	.dashboard-mobile .toolbar__arrow--bottom {
		right: 0;
		margin-right: 6px;
		width: 44px;
		height: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.dashboard-mobile .toolbar__arrow--top {
		top: 0;
	}

	.dashboard-mobile .toolbar__arrow--bottom {
		bottom: 0;
	}
}
</style>
