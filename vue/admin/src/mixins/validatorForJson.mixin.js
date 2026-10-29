// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * JsonTextareaValidationMixin
 *
 * This mixin validates JSON content in <textarea> elements marked with
 * the 'json-validate' class. It works with plain textareas and with CKEditor 5
 * instances created by your CKEditor mixin (listens to change:data / blur).
 *
 * Usage:
 *   1) Add the class 'json-validate' to the textarea you want to validate.
 *   2) Call this.applyJsonValidation(this.$el) after your DOM is ready,
 *      or use initializeJsonValidator(this.$el) if the DOM is async.
 *
 * Example:
 *   <textarea class="ckeditor json-validate" v-model="myJson"></textarea>
 */

export default {
	methods: {
		// ------------------------------------------------------------------------
		// Resolve a valid DOM root element from a wrapper or Vue component.
		//
		// @param {Element|null|undefined} wrapper - Provided wrapper or component.
		// @returns {Element|null} The DOM element or null if invalid.
		_getRootEl(wrapper) {
			const candidate = wrapper ?? this.$el
			let el = null

			if (candidate && candidate.nodeType === 1) {
				el = candidate
			} else if (candidate && candidate.$el) {
				el = candidate.$el
			}

			if (!el || typeof el.querySelectorAll !== 'function') {
				console.warn('[JSON-Validate] Invalid wrapper. Expected a DOM Element.')
				return null
			}
			return el
		},

		/**
		 * Creates a top-right error badge for invalid JSON.
		 *
		 * @return {HTMLSpanElement} The badge element.
		 */
		_createBadge() {
			const badge = document.createElement('span')
			badge.textContent = 'JSON is not valid!'
			badge.className = 'json-invalid-badge'
			// Inline minimal styling; you can move this to CSS if preferred.
			Object.assign(badge.style, {
				position: 'absolute',
				top: '12px',
				right: '8px',
				padding: '2px 6px',
				fontSize: '12px',
				borderRadius: '4px',
				background: '#ffe5e5',
				color: '#c20000',
				border: '1px solid #ffb3b3',
				display: 'none',
				zIndex: '2',
				pointerEvents: 'none',
			})
			return badge
		},

		/*
		 * Sanitizes a JSON-like text removing zero-width chars, BOM,
		 * converting NBSP to normal spaces, and LS/PS to newline.
		 *
		 * @param {string} raw
		 * @return {string}
		 */
		_sanitizeJsonInput(raw) {
			let s = (raw ?? '').toString().normalize('NFKC')
			// Remove BOM
			s = s.replace(/\uFEFF/gu, '')
			// Remove zero-width characters (ESLint-safe alternation, unicode flag)
			s = s.replace(/(?:\u200B|\u200C|\u200D|\u2060)/gu, '')
			// NBSP -> normal space
			s = s.replace(/\u00A0/gu, ' ')
			// Line/Paragraph separators -> newline
			s = s.replace(/(?:\u2028|\u2029)/gu, '\n')
			return s
		},

		// ------------------------------------------------------------------------
		// Replace %token% placeholders with a JSON-safe literal for validation.
		// We use 0 (number) to keep unquoted positions valid; if the token appears
		// inside a quoted string it remains valid JSON as part of the string.
		//
		// @param {string} text
		// @return {string}
		_normalizeJsonTokens(text) {
			return (text || '').replace(/%[A-Za-z0-9_]+%/g, '0')
		},

		/**
		 * Returns true when the provided value is valid JSON.
		 * Empty strings are considered valid (no error shown).
		 *
		 * @param {string} value - The string to validate as JSON.
		 * @return {boolean} True if value is valid JSON or empty.
		 */
		_isValidJson(value) {
			const v = (value || '').trim()
			if (!v) return true
			try {
				const prepared = this._normalizeJsonTokens(v)
				JSON.parse(prepared)
				return true
			} catch {
				return false
			}
		},

		/**
		 * Ensures there is a positioned wrapper for placing the badge.
		 * If the textarea's parent uses position: static, it will be set to relative.
		 *
		 * @param {HTMLElement} el - The textarea element that needs a badge container.
		 * @return {HTMLElement|null} The container element or null if not found.
		 */
		_ensureWrapperPosition(el) {
			const wrapper = el?.parentElement || null
			if (!wrapper) return null
			const style = window.getComputedStyle(wrapper)
			if (style.position === 'static') {
				wrapper.style.position = 'relative'
			}
			return wrapper
		},

		/**
		 * Renders badge visibility based on the current textarea value.
		 *
		 * @param {HTMLTextAreaElement} el - The textarea being validated.
		 * @param {{badge: HTMLElement}} state - Internal state holding the badge.
		 * @return {void}
		 */
		_validateValue(el, state) {
			const original = el.value
			const sanitized = this._sanitizeJsonInput(original)

			// Keep CKEditor and textarea mirrored without causing loops
			if (state.editor) {
				if (sanitized !== original && !state._sanitizing) {
					try {
						state._sanitizing = true
						state.editor.setData(sanitized)
						el.value = sanitized
					} finally {
						state._sanitizing = false
					}
				}
			} else if (sanitized !== original) {
				// Preserve caret/selection when we rewrite the value
				const start = el.selectionStart
				const end = el.selectionEnd

				// Compute where strings first differ and length delta
				const diff = this._firstDiffIndex(original, sanitized)
				const delta = sanitized.length - original.length

				el.value = sanitized

				// Adjust selection only if caret was after the first difference
				if (diff !== -1) {
					const newStart = start > diff ? Math.max(diff, start + delta) : start
					const newEnd = end > diff ? Math.max(diff, end + delta) : end
					try {
						el.setSelectionRange(newStart, newEnd)
					} catch (e) {
						// noop: some browsers may throw if field is not focused
					}
				}
			}

			// Validate JSON using sanitized value
			const valid = this._isValidJson(el.value)
			state.badge.style.display = valid ? 'none' : ''

			// Store validity in dataset and in a property
			el.dataset.jsonValid = valid ? 'true' : 'false'
			el._jsonIsValid = valid
		},

		/*
		 * Returns the first index where two strings differ, or -1 if equal.
		 */
		_firstDiffIndex(a, b) {
			const len = Math.min(a.length, b.length)
			for (let i = 0; i < len; i++) {
				if (a[i] !== b[i]) return i
			}
			return a.length === b.length ? -1 : len
		},

		/**
		 * Attaches listeners for a plain <textarea> (no CKEditor).
		 *
		 * @param {HTMLTextAreaElement} el - The textarea to validate.
		 * @param {{badge: HTMLElement, cleanup: Function[]}} state - Internal state.
		 * @return {void}
		 */
		_attachPlainListeners(el, state) {
			const handler = () => this._validateValue(el, state)
			const onPaste = () => setTimeout(() => this._validateValue(el, state), 0)

			el.addEventListener('input', handler)
			el.addEventListener('blur', handler)
			el.addEventListener('paste', onPaste)

			state.cleanup.push(() => el.removeEventListener('input', handler))
			state.cleanup.push(() => el.removeEventListener('blur', handler))
			state.cleanup.push(() => el.removeEventListener('paste', onPaste))

			this._validateValue(el, state) // initial paint
		},

		/**
		 * Attaches listeners for CKEditor 5 if present on the element.
		 * Relies on your CKEditor mixin setting el._ckEditor and syncing el.value.
		 *
		 * @param {HTMLTextAreaElement} el - The textarea associated to a CKEditor instance.
		 * @param {{badge: HTMLElement, cleanup: Function[], editor?: any, _sanitizing?: boolean}} state - Internal state.
		 * @return {boolean} True if CKEditor listeners were attached.
		 */
		_attachCkeditorListeners(el, state) {
			const editor = el._ckEditor
			if (!editor) return false

			state.editor = editor
			const validateFromEditor = () => this._validateValue(el, state)

			// CKEditor returns EmitterMixin listeners with .off()
			const changeToken = editor.model.document.on('change:data', validateFromEditor)
			const blurToken = editor.editing.view.document.on('blur', validateFromEditor)

			state.cleanup.push(() => changeToken?.off && changeToken.off())
			state.cleanup.push(() => blurToken?.off && blurToken.off())

			validateFromEditor() // initial paint
			return true
		},

		/**
		 * Applies JSON validation to all '.json-validate' textareas within the wrapper.
		 *
		 * @param {Element|null|undefined} wrapper - DOM subtree containing the target textareas (e.g., this.$el).
		 * @return {void}
		 */
		applyJsonValidation(wrapper) {
			const root = this._getRootEl(wrapper)
			if (!root) {
				return
			}

			root.querySelectorAll('textarea.json-validate').forEach((el) => {
				// Avoid double init
				if (el.getAttribute('data-json-validate-initialized')) return
				el.setAttribute('data-json-validate-initialized', 'true')

				// Per-element state
				const state = { badge: this._createBadge(), cleanup: [], poll: null }
				// eslint-disable-next-line no-underscore-dangle
				el.__jsonValidateState = state

				// Place badge
				const container = this._ensureWrapperPosition(el) || el
				container.appendChild(state.badge)

				if (el.classList.contains('ckeditor')) {
					// Try to hook into CKEditor if it will arrive soon
					let attached = this._attachCkeditorListeners(el, state)
					if (!attached) {
						// Poll briefly waiting for your CKEditor mixin to set el._ckEditor
						const start = Date.now()
						const MAX_WAIT = 6000
						const TICK = 150
						const tryAttach = () => {
							if (!el.isConnected) return
							if (!attached && el._ckEditor) {
								attached = this._attachCkeditorListeners(el, state)
								if (attached) return
							}
							if (!attached && Date.now() - start < MAX_WAIT) {
								state.poll = setTimeout(tryAttach, TICK)
							} else if (!attached) {
								// Fallback: plain textarea
								this._attachPlainListeners(el, state)
							}
						}
						tryAttach()
					}
				} else {
					this._attachPlainListeners(el, state)
				}
			})
		},

		/**
		 * Waits until at least one '.json-validate' textarea exists, then applies validation.
		 * Useful when DOM is rendered asynchronously (e.g., inside v-if/v-for).
		 *
		 * @param {Element|null|undefined} wrapper - DOM subtree to scan for targets (e.g., this.$el).
		 * @param {number} interval - Polling interval in milliseconds (100 by default if not set).
		 * @param {number} timeout - Maximum waiting time in milliseconds (3000 by default if not set).
		 * @return {void}
		 */
		initializeJsonValidator(wrapper, interval = 100, timeout = 3000) {
			const root = this._getRootEl(wrapper)
			if (!root) {
				return
			}

			let waited = 0
			const check = () => {
				if (!root.isConnected) return
				const nodes = root.querySelectorAll('textarea.json-validate:not([data-json-validate-initialized])')
				if (nodes.length > 0) {
					this.applyJsonValidation(root)
				} else if (waited < timeout) {
					waited += interval
					setTimeout(check, interval)
				} else {
					console.warn('[JSON-Validate] No eligible fields found after timeout.')
				}
			}
			check()
		},

		/**
		 * Removes listeners and badges for all validated fields under wrapper.
		 *
		 * @param {Element|null|undefined} wrapper - DOM subtree containing the targets to clean.
		 * @return {void}
		 */
		destroyJsonValidation(wrapper) {
			const root = this._getRootEl(wrapper)
			if (!root) {
				return
			}

			root.querySelectorAll('textarea.json-validate[data-json-validate-initialized]')
				.forEach((el) => {
					// eslint-disable-next-line no-underscore-dangle
					const state = el.__jsonValidateState
					if (!state) return
					if (state.poll) clearTimeout(state.poll)
					state.cleanup.forEach(fn => { try { fn() } catch (e) {} })
					if (state.badge?.parentNode) state.badge.parentNode.removeChild(state.badge)
					// eslint-disable-next-line no-underscore-dangle
					delete el.__jsonValidateState
					el.removeAttribute('data-json-validate-initialized')
				})
		},
	},
}
