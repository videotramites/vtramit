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
		<!-- Loading spinner shown until the final page appears inside the iframe -->
		<v-sheet v-if="iframeLoadedStage <= 1"
			class="d-flex justify-center align-center"
			height="100%">
			<v-progress-circular indeterminate size="64" class="ma-4" />
		</v-sheet>

		<!-- Keep the iframe mounted; do not toggle it with v-if to avoid reloads on tab switches -->
		<iframe
			v-show="iframeLoadedStage > 1"
			ref="frame"
			:height="height"
			width="100%"
			:style="`width: 100%; height: ${height}`" />
	</v-sheet>
</template>

<script>
export default {
	name: 'NextcloudPanel',

	// ------------------------------------------------------------------------
	// Props
	// - src: Nextcloud public share URL (uploads or downloads)
	// - height: CSS height of the iframe container
	// - active: when true, marks this panel as visible/selected by parent
	// - preload: if true, load once on mount even if not active yet
	// - autoDownload: when login succeeds, click default download link (if present)
	props: {
		src: { type: String, required: true },
		height: { type: String, default: '100%' },
		active: { type: Boolean, default: false },
		preload: { type: Boolean, default: true },
		autoDownload: { type: Boolean, default: false },
	},

	// ------------------------------------------------------------------------
	// State
	// - iframeLoadedStage: 0 initial, 1 login page loaded, 2 final page loaded
	// - failbackTimer: prevents infinite spinner on CSP/network issues
	data() {
		return {
			iframeLoadedStage: 0,
			failbackTimer: null,
		}
	},

	// ------------------------------------------------------------------------
	// Lifecycle
	mounted() {
		if (this.preload || this.active) {
			this.$nextTick(() => this.forceLoad())
		}
	},
	beforeDestroy() {
		if (this.failbackTimer) {
			clearTimeout(this.failbackTimer)
			this.failbackTimer = null
		}
	},

	// ------------------------------------------------------------------------
	// Reactivity
	// - Only trigger the first load once. If src changes before the first load,
	//	 allow it; after that, keep the iframe alive to avoid slow reloads.
	watch: {
		active(isActive) {
			if (isActive) {
				this.$nextTick(() => this.forceLoad())
			}
		},
		src(next, prev) {
			if (next && next !== prev) {
				this.$nextTick(() => this.forceLoad())
			}
		},
	},

	methods: {
		// ------------------------------------------------------------------------
		// Internal: perform the actual iframe load and attach onload handler
		forceLoad() {
			this.iframeLoadedStage = 0

			if (this.failbackTimer) {
				clearTimeout(this.failbackTimer)
				this.failbackTimer = null
			}

			const iframe = this.$refs.frame
			if (!iframe) return

			iframe.src = this.src

			// Fallback to stop spinner if the iframe never reaches stage 2
			this.failbackTimer = setTimeout(() => {
				this.failbackTimer = null
				this.iframeLoadedStage = 100
				this.$emit('loaded')
			}, 10000)
		},

		// ------------------------------------------------------------------------
		// Helpers
		getIframeDocument() {
			const iframe = this.$refs.frame
			return iframe && (iframe.contentDocument || iframe.contentWindow?.document)
		},
	},
}
</script>
