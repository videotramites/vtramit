<!--
/**
 * @copyright Copyright (c) 2020-2025 VIDEOATENCION
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
	<div class="signature-panel">

		<!-- Top area -->
    <template v-if="hasSignatureButtons">
      <div class="signature-panel--top">
        <input v-for="signatureButton in signatureButtons"
          :key="signatureButton.id"
          type="button"
          :value="getSignatureLabel(signatureButton)"
          @click="clickSignatureButton(signatureButton)">
      </div>
      <hr>
    </template>

		<FilesPanel
			:list="uploadFolderFiles"
			:upload-url="`/apps/vtramit/web/2.0/procedure/${procedure.id}/signature/upload-folder`"
			:download-url="`/apps/vtramit/web/2.0/procedure/${procedure.id}/signature/upload-folder?filename=%filename%`"
			:delete-url="`/apps/vtramit/web/2.0/procedure/${procedure.id}/signature/upload-folder?filename=%filename%`"
			accept=".pdf, .png, .jpg"
			:show-signature-info=true
			@uploaded="loadFilesSignatureUploadFolder"
			@before-delete="handleBeforeDelete"
			@deleted="loadFilesSignatureUploadFolder"
		/>

		<h3>{{ t('vtramit', 'Signed documents') }}</h3>
		<FilesPanel
			:list="downloadFolderFiles"
			:download-url="`/apps/vtramit/web/2.0/procedure/${procedure.id}/signature/download-folder?filename=%filename%`"
		/>

		<!-- Left panel -->
		<!--
		<v-row no-gutters class="signature-panel--content">
			<v-col
				v-if="hasLeftPanel"
				class="d-flex flex-column signature-panel--column">
				<h3>{{ t('vtramit', 'Documents to sign') }}</h3>
				<NextcloudPanel
					:src="procedure.staffSignatureUploadsUrl"
				/>
			</v-col>
-->
			<!-- Right panel -->
			<!--
			<v-col
				v-if="hasRightPanel"
				class="d-flex flex-column signature-panel--column">
				<h3>{{ t('vtramit', 'Signed documents') }}</h3>
				<NextcloudPanel
					:src="procedure.staffSignatureDownloadsUrl"
				/>
			</v-col>
		</v-row>
		-->
	</div>
</template>

<script>
import NextcloudPanel from './NextcloudPanel.vue'
import FilesPanel from './FilesPanel.vue'
import ComponentStatus from '../../../mixins/componentStatus.mixin.js'
import MessageManager from '../../../mixins/messageManager.mixin.js'
import ApiService from '../../../mixins/apiService.mixin.js'

export default {
	name: 'SignaturePanel',
	components: {
		NextcloudPanel,
		FilesPanel,
	},
	mixins: [ComponentStatus, MessageManager, ApiService],
	props: {
		procedure: { type: Object, required: true },
	},

	data() {
		return {
			// ------------------------------------------------------------------------
			// Loaded files for the uploads/signature folder (API: .../signature/upload-folder)
			uploadFolderFiles: [],
			// ------------------------------------------------------------------------
			// Loaded files for the download/signature folder (API: .../signature/download-folder)
			downloadFolderFiles: [],
			autoRefreshIntervalMs: 10000,
			uploadRefreshTimer: null,
			downloadRefreshTimer: null,
			lastUploadRefresh: 0,
			lastDownloadRefresh: 0,
			isLoadingSignatureUploadFolder: false,
			isLoadingSignatureDownloadFolder: false,
		}
	},

	created() {
		const id = this.procedure && this.procedure.id
		if (id) {
			return this.$store.dispatch('procedure/readProcedureInfoById', id)
		}
	},
	beforeDestroy() {
		this.stopUploadAutoRefresh()
		this.stopDownloadAutoRefresh()
	},

	watch: {
		// ------------------------------------------------------------------------
		// Load files when the procedure becomes available or changes
		procedure: {
			immediate: true,
			handler(proc) {
				const id = proc && proc.id
				if (id) {
					this.$store
						.dispatch('procedure/readProcedureInfoById', id)
						.catch(() => {})

					this.loadFilesSignatureUploadFolder()
					this.loadFilesSignatureDownloadFolder()
					this.startUploadAutoRefresh()
					this.startDownloadAutoRefresh()
				} else {
					this.uploadFolderFiles = []
					this.downloadFolderFiles = []
					this.lastUploadRefresh = 0
					this.lastDownloadRefresh = 0
					this.stopUploadAutoRefresh()
					this.stopDownloadAutoRefresh()
					this.emitCounts()
				}
			},
		},
	},

	computed: {
		// ------------------------------------------------------------------------
		// True if uploads panel should be displayed
		hasLeftPanel() {
			return !!(this.procedure && this.procedure.staffSignatureUploadsUrl)
		},

		// ------------------------------------------------------------------------
		// True if downloads panel should be displayed
		hasRightPanel() {
			return !!(this.procedure && this.procedure.staffSignatureDownloadsUrl)
		},

		// ------------------------------------------------------------------------
		//
		hasSignatureButtons() {
			if (this.signatureButtons && this.signatureButtons.length > 0) {
				return true
			}
			return false
		},

		// ------------------------------------------------------------------------
		//
		signatureButtons() {
			return this.$store.getters['procedure/readSignatureButtonsAll']
		},
	},

	methods: {
		emitCounts() {
			const uploads = Array.isArray(this.uploadFolderFiles) ? this.uploadFolderFiles.length : 0
			const downloads = Array.isArray(this.downloadFolderFiles) ? this.downloadFolderFiles.length : 0
			this.$emit('counts-change', { uploads, downloads })
		},
		startUploadAutoRefresh() {
			this.stopUploadAutoRefresh()
			if (!this.procedure || !this.procedure.id) return
			this.uploadRefreshTimer = setInterval(() => this.maybeRefreshUploadFolder(), this.autoRefreshIntervalMs)
		},
		stopUploadAutoRefresh() {
			if (this.uploadRefreshTimer) {
				clearInterval(this.uploadRefreshTimer)
				this.uploadRefreshTimer = null
			}
		},
		maybeRefreshUploadFolder() {
			if (!this.procedure || !this.procedure.id || this.isLoadingSignatureUploadFolder) return
			const elapsed = Date.now() - (this.lastUploadRefresh || 0)
			if (elapsed < this.autoRefreshIntervalMs) return
			this.loadFilesSignatureUploadFolder()
		},
		startDownloadAutoRefresh() {
			this.stopDownloadAutoRefresh()
			if (!this.procedure || !this.procedure.id) return
			this.downloadRefreshTimer = setInterval(() => this.maybeRefreshDownloadFolder(), this.autoRefreshIntervalMs)
		},
		stopDownloadAutoRefresh() {
			if (this.downloadRefreshTimer) {
				clearInterval(this.downloadRefreshTimer)
				this.downloadRefreshTimer = null
			}
		},
		maybeRefreshDownloadFolder() {
			if (!this.procedure || !this.procedure.id || this.isLoadingSignatureDownloadFolder) return
			const elapsed = Date.now() - (this.lastDownloadRefresh || 0)
			if (elapsed < this.autoRefreshIntervalMs) return
			this.loadFilesSignatureDownloadFolder()
		},

		// ------------------------------------------------------------------------
		// Resolve localized label without translation, using Nextcloud locale
		getSignatureLabel(button) {
			// Get full locale, e.g. "es_ES"
			const locale = (typeof OC !== 'undefined' && OC.getLocale) ? OC.getLocale() : 'en'
			// Derive base language, e.g. "es"
			const base = locale.includes('_') ? locale.split('_')[0] : locale

			// Try exact match (es_ES), then base (es), then explicit fallbacks, then first available
			const labels = button?.label || {}
			return labels[locale]
				|| labels[base]
				|| labels['es']
				|| labels['en']
				|| Object.values(labels)[0]
				|| ''
		},

		// ------------------------------------------------------------------------
		// Load files from /apps/vtramit/web/2.0/procedure/{id}/signature/upload-folder
		async loadFilesSignatureUploadFolder() {
			if (!this.procedure || !this.procedure.id) {
				this.uploadFolderFiles = []
				this.emitCounts()
				return
			}
			if (this.isLoadingSignatureUploadFolder) {
				return
			}
			this.isLoadingSignatureUploadFolder = true
			try {
				const url = `apps/vtramit/web/2.0/procedure/${this.procedure.id}/signature/upload-folder`
				const data = await this.get(url)
				this.uploadFolderFiles = Array.isArray(data?.list) ? data.list : []
			} catch (e) {
				// Error is already handled by apiService; keep local state consistent
				this.uploadFolderFiles = []
			} finally {
				this.isLoadingSignatureUploadFolder = false
				this.lastUploadRefresh = Date.now()
				this.emitCounts()
			}
		},

		// ------------------------------------------------------------------------
		// Load files from /apps/vtramit/web/2.0/procedure/{id}/signature/download-folder
		async loadFilesSignatureDownloadFolder() {
			if (!this.procedure || !this.procedure.id) {
				this.downloadFolderFiles = []
				this.emitCounts()
				return
			}
			if (this.isLoadingSignatureDownloadFolder) {
				return
			}
			this.isLoadingSignatureDownloadFolder = true
			try {
				const url = `apps/vtramit/web/2.0/procedure/${this.procedure.id}/signature/download-folder`
				const data = await this.get(url)
				this.downloadFolderFiles = Array.isArray(data?.list) ? data.list : []
			} catch (e) {
				// Error is already handled by apiService; keep local state consistent
				this.downloadFolderFiles = []
			} finally {
				this.isLoadingSignatureDownloadFolder = false
				this.lastDownloadRefresh = Date.now()
				this.emitCounts()
			}
		},

		async handleBeforeDelete(payload) {
			const { file, proceed } = payload || {}
			if (!file) {
				proceed && proceed()
				return
			}

			const signatureId = file.signatureId ?? file.signature_id
			const fileId = file.FileId ?? file.file_id

			if (!signatureId || !fileId) {
				proceed && proceed()
				return
			}

			try {
				const url = `apps/vtramit/web/2.0/signature/${signatureId}/file/${fileId}/cancel`
				await this.post(url, {})
				proceed && proceed()
			} catch (error) {
				// apiService handles error feedback; do not proceed with deletion
			}
		},

		// ------------------------------------------------------------------------
		//
		clickSignatureButton(button) {
			// this.setStatusUpdating()
			const payload = {
				id: this.procedure.id,
				signatureButtonId: button.id,
			}
			this.$store
				.dispatch('procedure/updateSignature', payload)
				.then((result) => {
					// Clean current errors and manage new messages
					this.manageMessage(result)
					// Refresh files after triggering a signature flow
					this.loadFilesSignatureUploadFolder()
				})
				.catch((error) => {
					if (error.message !== 'IGNORE') {
						// Setting the appropriate error markers from the server response
						this.manageException(error, t('vtramit', 'Could not update the procedure'))
					}
				})
		},
	},
}
</script>

<style lang="scss" scoped>

:root {
	--default-clickable-area: 34px;
	--default-font-size: 15px;
	--default-grid-baseline: 4px;
	--color-primary-element-light-text: #00293f;
	--color-primary-element-light: #e5eff5;
}

h3 {
	margin: 30px auto 10px auto;
}

.signature-panel {

	height: 100%;
	display: flex;
	flex-direction: column;
	min-height: 0;

	&--top {
		flex: 0 0 44px;
		text-align: right;
    padding: 0 5px;
		border-bottom: 1px solid rgba(0,0,0,.08);
		background: rgba(0,0,0,.02);

		input {
			margin: 10px;
		}

		input[type=button] {
			box-shadow: 0 2px 4px rgba(0,0,0,.2);
			transition: background .15s ease, box-shadow .15s ease, transform .02s ease-in-out;

			&:hover:enabled {
				background: rgba(0,0,0,.04);
			}

			&:active:enabled {
				transform: translateY(1px);
			}

			&:disabled {
				opacity: .6;
				cursor: not-allowed;
			}
		}
	}

	&--content {
		flex: 1 1 auto;
		min-height: 0;
	}

	&--column {
		min-width: 0;
		min-height: 0;

		h3 {
			margin: 20px 6px 6px 6px;
		}
	}

}
</style>
