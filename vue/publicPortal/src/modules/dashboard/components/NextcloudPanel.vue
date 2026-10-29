<!-- NextcloudPanel.vue -->
<template>
	<v-sheet width="100%" height="100%" class="vtramit-nc-embed">
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
		if (this._embedObserver) {
			this._embedObserver.disconnect()
			this._embedObserver = null
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
		// Public API: trigger the default download link inside the iframe
		// Looks for #download and clicks its first child if available
		triggerDownload() {
			const doc = this.getIframeDocument()
			if (!doc) return
			const d = doc.getElementById('download')
			d?.firstChild?.click?.()
		},
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

			iframe.onload = this.onFrameLoad
			iframe.src = this.src

			// Fallback to stop spinner if the iframe never reaches stage 2
			this.failbackTimer = setTimeout(() => {
				this.failbackTimer = null
				this.iframeLoadedStage = 100
				this.$emit('loaded')
			}, 10000)
		},

		// ------------------------------------------------------------------------
		// Handle iframe load progression. Two paths:
		//	- Pre-auth already worked (publicPortal-snippet.php): the very first
		//	  GET /s/<token> returns the file list directly (no password page).
		//	  → jump straight to stage 2 on the first onload.
		//	- Pre-auth missing/failed: the first onload lands on the login page.
		//	  → autofill the password, submit; the next onload lands on the file
		//	  list and we go to stage 2.
		// In both cases inject the embed CSS so NC's header / footer / login
		// chrome don't show up around the file widget.
		onFrameLoad() {
			const doc = this.getIframeDocument()
			if (!doc) return

			this.injectEmbedStyles(doc)

			const onLoginPage = !!doc.getElementById('body-login')
				|| !!doc.getElementById('password-submit')

			if (onLoginPage && this.iframeLoadedStage === 0) {
				// Login page; autofill + submit, wait for the next onload.
				this.iframeLoadedStage = 1
				const password = this.$store.getters.getPassword
				const passEl = doc.getElementById('password')
				if (passEl) {
					passEl.value = password
					passEl.click()
					const submitBtn = doc.getElementById('password-submit')
					submitBtn && submitBtn.click()
				}
				return
			}

			// Either we landed directly on the file list (preauth worked) or
			// we're returning post-autofill. Reveal the iframe.
			this.iframeLoadedStage = 2

			if (this.failbackTimer) {
				clearTimeout(this.failbackTimer)
				this.failbackTimer = null
			}

			if (onLoginPage) {
				// Stage 1, still on login → password autofill failed.
				this.$emit('login-failed')
			} else {
				this.$emit('login-success')
				if (this.autoDownload) {
					setTimeout(() => this.triggerDownload(), 500)
				}
			}
			this.$emit('loaded')
		},

		// ------------------------------------------------------------------------
		// Inject a stylesheet into the iframe document that hides NC's outer
		// chrome (top <header>, public-page <footer>, skip-navigation links).
		// The share is same-origin (demo.vtramit.com → demo.vtramit.com) so we
		// can reach the iframe document. NC's public share is a Vue/React SPA
		// that may reset its head later in the lifecycle, so we re-inject on
		// childList mutations of the head and retry briefly while head is
		// still being parsed.
		injectEmbedStyles(doc) {
			if (!doc) return

			const css = `
				/* NC top header bar (logo + menu) — semantic <header>, no id */
				body > header, body > div > header, header[id="header"] { display: none !important; }
				/* NC public-page footer — semantic <footer> */
				body > footer, #body-public > footer { display: none !important; }
				/* Skip-navigation a11y links */
				.skip-navigation, a[href="#main-content"], a[href="#app-content"] { display: none !important; }
				/* Reset top spacing now that the header is gone */
				body, #content, #app-content, #content-vue, main {
					padding-top: 0 !important;
					margin-top: 0 !important;
				}
			`

			const tryInject = () => {
				if (doc.getElementById('vtramit-embed-styles')) return true
				if (!doc.head) return false
				const style = doc.createElement('style')
				style.id = 'vtramit-embed-styles'
				style.textContent = css
				doc.head.appendChild(style)
				return true
			}

			if (!tryInject()) {
				// Head not ready yet: retry up to ~1s
				let tries = 0
				const id = setInterval(() => {
					if (tryInject() || ++tries >= 10) clearInterval(id)
				}, 100)
			}

			// NC's SPA sometimes blanks/re-renders the head between routes.
			// Watch for it and re-inject if our <style> disappears. Stored on
			// the panel instance so we can disconnect on destroy.
			if (!this._embedObserver && doc.body) {
				try {
					this._embedObserver = new MutationObserver(() => {
						if (!doc.getElementById('vtramit-embed-styles')) tryInject()
					})
					this._embedObserver.observe(doc.documentElement || doc.body, {
						childList: true,
						subtree: true,
					})
				} catch (e) {
					// Cross-origin or detached doc; harmless to skip.
				}
			}
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
