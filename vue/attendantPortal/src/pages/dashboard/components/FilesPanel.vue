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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
-->

<template>
	<div class="files-panel">

		<!-- Upload toolbar (visible only if uploadUrl is provided) -->
		<div v-if="allowUpload" class="files-panel--uploader">
			<input
				ref="fileInput"
				type="file"
				:accept="accept || null"
				@change="onFileChange"
				class="files-panel--input-hidden"
			>

			<!-- Dropzone + click-to-browse -->
			<div
				class="files-panel--dropzone"
				:class="{ 'is-dragging': isDragging, 'has-file': hasSelection }"
				@dragover.prevent="onDragOver"
				@dragleave.prevent="onDragLeave"
				@drop.prevent="onDrop"
				@click="pickFile"
				role="button"
				tabindex="0"
				@keydown.enter.prevent="pickFile"
				@keydown.space.prevent="pickFile"
				:aria-busy="isUploading ? 'true' : 'false'"
				:aria-label="hasSelection
					? (t('vtramit', 'Selected') + ': ' + (selectedFiles[0]?.name || ''))
					: t('vtramit', 'Drop a file here or click to choose')"
			>
				<div class="files-panel--dropzone--text">
					<strong>{{ t('vtramit', 'Drag & drop a file here,') }}</strong> {{ t('vtramit', 'or') }}
					<u>{{ t('vtramit', 'click to choose') }}</u>
					<div v-if="accept" class="files-panel--dropzone--hint">
						{{ t('vtramit', 'Allowed') }}: {{ accept }}
					</div>
					<div v-if="hasSelection && !isUploading" class="files-panel--dropzone--selected">
						{{ t('vtramit', 'Selected') }}: {{ selectedFiles[0].name }} ({{ formatSize(selectedFiles[0].size) }})
					</div>
					<div v-if="isUploading" class="files-panel--dropzone--uploading">
						{{ t('vtramit', 'Uploading…') }}
					</div>
				</div>
			</div>

		</div>
		<!-- END -- Upload toolbar -->

		<!-- Files list table -->
		<table class="files-panel--table">
			<thead>
				<tr>
					<th class="column-file">{{ t('vtramit', 'File') }}</th>
					<th class="column-size">{{ t('vtramit', 'Size') }}</th>
					<th v-if="showSignatureInfo" class="column-signature-type">{{ t('vtramit', 'Signature type') }}</th>
					<th v-if="showSignatureInfo" class="column-status">{{ t('vtramit', 'Status') }}</th>
					<th v-if="showSignatureInfo" class="column-status-date">{{ t('vtramit', 'Status date') }}</th>
					<th v-if="hasActionButtons" class="column-action">{{ t('vtramit', 'Actions') }}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="file in list" :key="file.id || file.path">
					<td :title="file.path" class="column-file">
						{{ file.name }}
					</td>
					<td class="column-size">
						{{ formatSize(file.size) }}
					</td>
					<td v-if="showSignatureInfo" class="column-signature-type">
						{{ file.signature_type || '-' }}
					</td>
					<td v-if="showSignatureInfo" class="column-status">
						{{ signatureStatusLabel(file.status) }}
					</td>
					<td v-if="showSignatureInfo" class="column-status-date">
						{{ formatDate(file.status_date) }}
					</td>
					<td v-if="hasActionButtons" class="column-action">
						<div class="files-panel--row-actions">
							<button
								v-if="allowDownload"
								type="button"
								class="files-panel--btn files-panel--btn-icon"
								@click="onDownload(file)"
								:title="t('vtramit', 'Download file')"
								:aria-label="t('vtramit', 'Download file')"
							>
								<span class="v-icon notranslate mdi mdi-download theme--light" aria-hidden="true" />
							</button>
							<button
								v-if="allowDelete && canDeleteFile(file)"
								type="button"
								class="files-panel--btn files-panel--btn-icon files-panel--btn--danger"
								@click="requestDelete(file)"
								:title="t('vtramit', 'Delete file')"
								:aria-label="t('vtramit', 'Delete file')"
							>
								<span class="v-icon notranslate mdi mdi-delete theme--light" aria-hidden="true" />
							</button>
						</div>
					</td>
				</tr>
				<tr v-if="!list || list.length === 0">
					<td :colspan="headerColspan" class="files-panel--empty">{{ t('vtramit', 'No files to display') }}</td>
				</tr>
			</tbody>
		</table>
		<!-- END -- Files list table -->
	</div>
</template>

<script>
import ApiService from '../../../mixins/apiService.mixin.js'
import { showError } from '@nextcloud/dialogs'

const SIGNATURE_STATUS_SIGNED = 5

export default {
	name: 'FilesPanel',
	mixins: [ApiService],

	// ------------------------------------------------------------------------
	// Props
	props: {
		// Expects the "list" array from the API response.
		list: {
			type: Array,
			required: true,
			default: () => [],
		},

		// Optional: endpoint to PUT { filename, data } (data is a full dataURL)
		uploadUrl: {
			type: String,
			required: false,
			default: '',
		},

		// Optional: accept attribute (e.g. ".pdf,.jpg" or "application/pdf")
		accept: {
			type: String,
			required: false,
			default: '',
		},

		// Optional: endpoint to GET the file to download
		downloadUrl: {
			type: String,
			required: false,
			default: '',
		},

		// Optional: endpoint to DELETE a file
		deleteUrl: {
			type: String,
			required: false,
			default: '',
		},

		// Optional: show signature-related columns (status, type, status_date)
		showSignatureInfo: {
			type: Boolean,
			required: false,
			default: false,
		},
	},

	// ------------------------------------------------------------------------
	// Data
	data() {
		return {
			selectedFiles: [],
			isUploading: false,
			isDragging: false,
		}
	},

	// ------------------------------------------------------------------------
	// Computed
	computed: {
		hasSelection() {
			return Array.isArray(this.selectedFiles) && this.selectedFiles.length > 0
				&& this.selectedFiles[0] instanceof File
		},

		/**
		 * Computes the number of columns to span in the "no files" row
		 * depending on signature columns and actions.
		 */
		headerColspan() {
			let cols = 2 // File + Size
			if (this.showSignatureInfo) {
				cols += 3 // Status + Type + Status date
			}
		if (this.hasActionButtons) {
			cols += 1 // Actions
		}
			return cols
		},

		allowUpload() {
			return typeof this.uploadUrl === 'string' && this.uploadUrl.length > 0
		},

		allowDownload() {
			if (this.downloadUrl) {
				return true
			}
			return Array.isArray(this.list) && this.list.some(file => typeof file?.downloadUrl === 'string' && file.downloadUrl.length > 0)
		},

		allowDelete() {
			if (this.deleteUrl) {
				return true
			}
			return Array.isArray(this.list) && this.list.some(file => typeof file?.deleteUrl === 'string' && file.deleteUrl.length > 0)
		},

		hasActionButtons() {
			return this.allowDownload || this.allowDelete
		},
	},

	// ------------------------------------------------------------------------
	// Methods
	methods: {
		signatureStatusLabel(statusCode) {
			if (statusCode === null || statusCode === undefined) {
				return '-'
			}

			try {
				const item = this.$store.getters['signatureStatuses/readById'](statusCode)
				if (!item || !item.name) {
					return statusCode
				}

				return t('vtramit', item.name)
			} catch (e) {
				return statusCode
			}
		},

		// ------------------------------------------------------------------------
		// Format a UNIX timestamp (seconds or milliseconds) into a readable
		// local date/time string. Returns '-' if empty or invalid.
		formatDate(ts) {
			if (ts === null || ts === undefined || ts === '') {
				return '-'
			}
			let ms = Number(ts)
			if (Number.isNaN(ms)) {
				return '-'
			}
			// If provided in seconds, convert to milliseconds
			if (ms < 1e12) {
				ms = ms * 1000
			}
			const d = new Date(ms)
			return isNaN(d.getTime()) ? '-' : d.toLocaleString()
		},

		// ------------------------------------------------------------------------
		// Human-readable file size (bytes)
		formatSize(bytes) {
			const b = Number(bytes)
			if (!Number.isFinite(b) || b < 0) return '-'
			if (b === 0) return '0 B'
			const units = ['B', 'KB', 'MB', 'GB', 'TB']
			const i = Math.floor(Math.log(b) / Math.log(1024))
			const value = b / Math.pow(1024, i)
			return `${value.toFixed(value >= 100 || i === 0 ? 0 : 1)} ${units[i]}`
		},

		// ------------------------------------------------------------------------
		// Open native file picker
		pickFile() {
			if (this.$refs.fileInput) {
				this.$refs.fileInput.click()
			}
		},

		// ------------------------------------------------------------------------
		// Handle native input change (single file)
		onFileChange(e) {
			const files = Array.from(e?.target?.files || [])
			this.selectedFiles = files.slice(0, 1) // enforce single file
			if (this.selectedFiles.length > 0) {
				this.triggerAutoUpload()
			}
		},

		// ------------------------------------------------------------------------
		// Drag & drop handlers
		onDragOver() {
			this.isDragging = true
		},
		onDragLeave() {
			this.isDragging = false
		},
		onDrop(e) {
			this.isDragging = false
			const files = Array.from(e.dataTransfer?.files || [])
			if (files.length > 0) {
				this.selectedFiles = files.slice(0, 1) // enforce single file
				this.triggerAutoUpload()
			}
		},

		// ------------------------------------------------------------------------
		// Clear current selected file (cancel pending upload)
		cancelSelection() {
			this.selectedFiles = []
			if (this.$refs.fileInput) {
				this.$refs.fileInput.value = ''
			}
		},

		triggerAutoUpload() {
			this.$nextTick(() => {
				this.uploadFiles()
			})
		},

		// ------------------------------------------------------------------------
		// Upload the selected file using PUT JSON via apiService.mixin.js
		async uploadFiles() {
			if (!this.allowUpload || !this.uploadUrl || !this.hasSelection || this.isUploading) {
				return
			}

			this.isUploading = true
			try {
				const file = this.selectedFiles[0]
				if (!file) {
					return
				}

				// Read the file as full DataURL (including the "data:*;base64," prefix)
				const dataUrl = await new Promise((resolve, reject) => {
					const reader = new FileReader()
					reader.onload = () => resolve(reader.result)
					reader.onerror = reject
					reader.readAsDataURL(file)
				})

				// Build payload { filename, data }
				const payload = {
					filename: file.name,
					data: dataUrl,
				}

				// Send via PUT using apiService
				const resp = await this.post(this.uploadUrl, payload, true)

				// Notify parent to reload the list
				this.$emit('uploaded', resp || null)

				// Reset file input
				this.cancelSelection()
			} catch (err) {
				// apiService already handles generic error messages
				console.error('error')
			} finally {
				this.isUploading = false
			}
		},

		// ------------------------------------------------------------------------
		// Resolve a concrete download URL for the given file
		resolveDownloadUrl(file) {
			if (!file) {
				return ''
			}

			if (file.downloadUrl && typeof file.downloadUrl === 'string') {
				return file.downloadUrl
			}

			if (this.downloadUrl) {
				let url = this.downloadUrl

				// Replace %filename% placeholder if present
				if (url.includes('%filename%')) {
					const name = encodeURIComponent(file.name || file.path || '')
					url = url.replace('%filename%', name)
					return url
				}

				// Fallback: append filename at the end
				if (file.name) {
					const base = url.replace(/\/$/, '')
					const name = encodeURIComponent(file.name || file.path || '')
					return `${base}/${name}`
				}
			}

			return ''
		},

		// ------------------------------------------------------------------------
		// Resolve a concrete delete URL for the given file
		resolveDeleteUrl(file) {
			if (!file) {
				return ''
			}

			if (file.deleteUrl && typeof file.deleteUrl === 'string') {
				return file.deleteUrl
			}

			if (this.deleteUrl) {
				let url = this.deleteUrl

				if (url.includes('%filename%')) {
					const name = encodeURIComponent(file.name || file.path || '')
					return url.replace('%filename%', name)
				}

				if (file.name) {
					const base = url.replace(/\/$/, '')
					const name = encodeURIComponent(file.name || file.path || '')
					return `${base}/${name}`
				}
			}

			return ''
		},

		// ------------------------------------------------------------
		// Helper: parse a dataURL and returns { blob, mime }
		parseDataUrl(dataUrl) {
			const m = typeof dataUrl === 'string'
				? RegExp(/^data:([^;]+);base64,(.+)$/).exec(dataUrl)
				: null
			if (!m) return null
			const mime = m[1]
			const bin = atob(m[2])
			const bytes = new Uint8Array(bin.length)
			for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i)
			return { blob: new Blob([bytes], { type: mime }), mime }
		},

		// ------------------------------------------------------------
		// Helper: normalize download response to { blob, filename }
		normalizeDownloadResponse(res, fallbackFilename = 'download') {
			// Case: Direct blob
			if (res instanceof Blob) {
				return { blob: res, filename: fallbackFilename }
			}

			// Case: ArrayBuffer or TypedArray
			if (res && (res.byteLength !== undefined || ArrayBuffer.isView(res))) {
				const buf = res.byteLength !== undefined ? res : res.buffer
				return { blob: new Blob([buf]), filename: fallbackFilename }
			}

			// Case: dataURL string
			if (typeof res === 'string') {
				const parsed = this.parseDataUrl(res)
				if (parsed) {
					return { blob: parsed.blob, filename: fallbackFilename }
				}
				return { blob: null, filename: fallbackFilename }
			}

			// Case: Object { filename?, mimetype?, data?|bytes? }
			if (res && typeof res === 'object') {
				const outName = res.filename || fallbackFilename

				// Case: Object with dataURL in res.data
				if (typeof res.data === 'string') {
					const parsed = this.parseDataUrl(res.data)
					if (parsed) {
						return { blob: parsed.blob, filename: outName }
					}

					// base64 without prefix + mimetype
					if (/^[A-Za-z0-9+/=\s]+$/.test(res.data) && typeof res.mimetype === 'string') {
						try {
							const bin = atob(res.data.replace(/\s+/g, ''))
							const bytes = new Uint8Array(bin.length)
							for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i)
							return { blob: new Blob([bytes], { type: res.mimetype }), filename: outName }
						} catch {
							/* noop */
						}
					}
				}

				// Case: Object with bytes in res.bytes
				if (res.bytes && (res.bytes.byteLength !== undefined || ArrayBuffer.isView(res.bytes))) {
					const buf = res.bytes.byteLength !== undefined ? res.bytes : res.bytes.buffer
					return { blob: new Blob([buf], { type: res.mimetype || 'application/octet-stream' }), filename: outName }
				}
			}

			return { blob: null, filename: fallbackFilename }
		},

		// ------------------------------------------------------------
		// Download file
		async onDownload(file) {
			try {
				if (!file) return
				const url = this.resolveDownloadUrl(file)
				if (!url) {
					showError(t('vtramit', 'Download URL is not available'))
					return
				}

				const fallbackName = file.name || 'download'
				let out = { blob: null, filename: fallbackName }

				try {
					// Try to obtain binary
					const res = await this.get(url, null, false, { responseType: 'blob' })
					out = this.normalizeDownloadResponse(res, fallbackName)

					// If blob is not valid, try with text/JSON
					if (!out.blob) {
						const resJson = await this.get(url, null, true, { responseType: 'text' })
						out = this.normalizeDownloadResponse(resJson, fallbackName)
					}
				} catch (_) {
					// Fallback: fetch natively with credentials
					const r = await fetch(url, { credentials: 'include' })
					if (!r.ok) throw new Error(`HTTP ${r.status}`)

					// Try to detect if the backend returns a JSON with {filename,data}
					const ct = r.headers.get('content-type') || ''
					if (ct.includes('application/json')) {
						const j = await r.json()
						out = this.normalizeDownloadResponse(j, fallbackName)
					} else if (ct.startsWith('text/')) {
						const txt = await r.text()
						out = this.normalizeDownloadResponse(txt, fallbackName)
					} else {
						const buf = await r.arrayBuffer()
						out = this.normalizeDownloadResponse(buf, fallbackName)
					}
				}

				if (!out.blob) {
					showError(t('vtramit', 'Could not download the file'))
					return
				}

				// Trigger browser download
				const a = document.createElement('a')
				a.href = URL.createObjectURL(out.blob)
				a.download = out.filename
				document.body.appendChild(a)
				a.click()
				URL.revokeObjectURL(a.href)
				a.remove()
			} catch (_) {
				showError(t('vtramit', 'An unknown error has occurred while downloading the file'))
			}
		},

		// ------------------------------------------------------------------------
		// Determines whether the delete action should be shown for a file.
		canDeleteFile(file) {
			if (!file || typeof file !== 'object') {
				return true
			}
			if (typeof file.status === 'number' && file.status === SIGNATURE_STATUS_SIGNED) {
				return false
			}
			return true
		},

		// ------------------------------------------------------------
		requestDelete(file) {
			const proceed = () => {
				this.performDelete(file)
			}

			if (this.$listeners && this.$listeners['before-delete']) {
				this.$emit('before-delete', { file, proceed })
			} else {
				proceed()
			}
		},

		// Delete file
		async performDelete(file) {
			if (!file) {
				return
			}

			const url = this.resolveDeleteUrl(file)
			if (!url) {
				showError(t('vtramit', 'Delete URL is not available'))
				return
			}

			try {
				await this.delete(url, true)
				this.$emit('deleted', file)
			} catch (_) {
				// apiService handles the error feedback
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.files-panel {

	/* Enable container queries based on the component width */
	container-type: inline-size;
	container-name: files-panel;

	&--btn {
		align-self: flex-start;
		padding: 8px 12px;
		border-radius: 8px;
		border: 1px solid rgba(0,0,0,.12);
		background: #fff;
		cursor: pointer;
		font-size: 14px;
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

	&--btn-icon {
		width: 36px;
		height: 36px;
		padding: 6px;
		display: inline-flex;
		align-items: center;
		justify-content: center;

		.v-icon {
			font-size: 16px;
			line-height: 1;
		}
	}

	&--btn--danger {
		background: #fddede;
		border-color: #f19999;
		color: #a60000;

		&:hover:enabled {
			background: #f9c9c9;
		}
	}

	/* Table */
	&--table {
		width: 100%;
		border-collapse: collapse;

		th,
		td {
			padding: 8px 10px;
			border-bottom: 1px solid #ddd;
			text-align: left;
			font-size: 14px;
		}

		th {
			font-weight: 600;
			min-width: 100px;
		}

		th.column-file {
			width: 100%;
		}

		.column-size {
			width: 100px;
			text-align: right;
		}

		.column-action {
			width: 100px;
			text-align: center;
		}

		td.column-action {
			text-align: center;
		}
	}

	&--row-actions {
		display: inline-flex;
		gap: 6px;
		flex-wrap: wrap;
		justify-content: center;
	}

	/* No content */
	&--empty {
		text-align: center;
		color: #777;
		font-style: italic;
	}

	/* Hide control */
	&--input-hidden {
		position: absolute;
		width: 1px;
		height: 1px;
		opacity: 0;
		pointer-events: none;
	}

	/* Uploader */
	&--uploader {
		position: relative;
		display: flex;
		flex-direction: column;
		gap: 10px;
		margin: 20px 10px;
	}

	/* Modern dropzone */
	&--dropzone {
		border: 2px dashed rgba(0,0,0,.25);
		border-radius: 10px;
		padding: 18px 14px;
		display: flex;
		align-items: center;
		gap: 12px;
		transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
		cursor: pointer;
		user-select: none;
		background: rgba(0,0,0,.02);

		&:hover {
			border-color: rgba(0,0,0,.35);
			background: rgba(0,0,0,.03);
		}

		&.is-dragging {
			border-color: #2b8cff;
			background: rgba(43,140,255,.06);
			box-shadow: 0 0 0 3px rgba(43,140,255,.12) inset;
		}

		&.has-file {
			border-color: #28a745;
		}

		&--icon {
			font-size: 20px;
			line-height: 1;
		}

		&--text {
			font-size: 14px;
			text-align: center;
			margin: 0 auto;
		}

		&--hint {
			margin-top: 4px;
			font-size: 12px;
			color: #666;
		}

		&--selected {
			margin-top: 6px;
			font-size: 13px;
			color: #333;
		}

		&--uploading {
			margin-top: 6px;
			font-size: 13px;
			color: #1a73e8;
		}
	}

}

/* ------------------------------------------------------------------------ */
/* Responsive column visibility rules based on FilesPanel width             */
/* Hidden in this order as width shrinks:
   1) Size
   2) Status date
   3) Signature type                                                        */

@container files-panel (max-width: 720px) {
	.files-panel--table {
		th.column-size,
		td.column-size {
			display: none !important;
		}
	}
}

@container files-panel (max-width: 600px) {
	.files-panel--table {
		th.column-status-date,
		td.column-status-date {
			display: none !important;
		}
	}
}

@container files-panel (max-width: 500px) {
	.files-panel--table {
		th.column-signature-type,
		td.column-signature-type {
			display: none !important;
		}
	}
}
</style>
