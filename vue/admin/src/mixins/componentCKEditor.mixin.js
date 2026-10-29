// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * CKEditorMixin
 *
 * This mixin provides utility methods to initialize CKEditor 5
 * on all elements with the 'ckeditor' class within a given DOM wrapper.
 */

import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
import '../styles/ckeditor.nextcloud.css'

export default {
	methods: {
		// ------------------------------------------------------------------------
		// Ensure we always work with a real DOM Element (this.$el or wrapper.$el)
		//
		// @param {Element|null|undefined} wrapper - The provided wrapper or component.
		// @returns {Element|null} A DOM element or null if invalid.
		_getRootEl(wrapper) {
			const candidate = wrapper ?? this.$el
			const el = candidate && candidate.nodeType === 1
				? candidate
				: (candidate && candidate.$el ? candidate.$el : null)

			if (!el || typeof el.querySelectorAll !== 'function') {
				console.warn('[CKEditor] Invalid wrapper. Expected a DOM Element.')
				return null
			}
			return el
		},

		// ------------------------------------------------------------------------
		// Dispatches a DOM event on a target element.
		// Used to re-emit 'input' and 'blur' so Vue listeners keep working.
		//
		// @param {HTMLElement} el - The target element.
		// @param {string} type - Event type to dispatch (e.g., 'input', 'blur').
		_dispatchDomEvent(el, type) {
			if (!el) return
			const evt = new Event(type, { bubbles: true, cancelable: true })
			el.dispatchEvent(evt)
		},

		// ------------------------------------------------------------------------
		// Keeps CKEditor data aligned with the underlying <textarea> value.
		// - Performs an initial, silent hydration from textarea -> editor.
		// - For a short window, mirrors late async updates (textarea -> editor) when user is not typing.
		//
		// @param {HTMLTextAreaElement} element - The original textarea element.
		// @param {import('@ckeditor/ckeditor5-core').Editor} editor - CKEditor instance.
		_syncFromTextareaToEditor(element, editor) {
			// Internal guard to suppress change:data -> Vue emission during programmatic setData
			if (!element._ckSync) element._ckSync = { suppress: false }

			const silentSetData = (val) => {
				element._ckSync.suppress = true
				editor.setData(val)
				// Release the suppression on next macrotask to avoid reentrancy
				setTimeout(() => { element._ckSync.suppress = false }, 0)
			}

			// Initial hydration (do not emit Vue input/blur)
			const initial = element.value || ''
			if (editor.getData() !== initial) {
				silentSetData(initial)
			}
			// Keep textarea aligned with what the editor actually holds
			element.value = editor.getData()

			// Short async window to capture late-arriving data (e.g., after API loads)
			const startedAt = Date.now()
			const SYNC_WINDOW_MS = 8000
			const SYNC_INTERVAL_MS = 200
			let stop = false

			const tick = () => {
				if (stop) return
				if (!element.isConnected) return
				if (Date.now() - startedAt > SYNC_WINDOW_MS) return

				const textareaVal = element.value || ''
				const editorVal = editor.getData() || ''
				const editorHasFocus = editor.editing.view.document.isFocused

				if (!editorHasFocus && textareaVal !== editorVal) {
					silentSetData(textareaVal)
				}

				setTimeout(tick, SYNC_INTERVAL_MS)
			}
			setTimeout(tick, SYNC_INTERVAL_MS)

			// Expose a stopper for future cleanup if needed
			element._ckSync.stop = () => { stop = true }
		},

		// ------------------------------------------------------------------------
		// Initializes CKEditor on all elements with class 'ckeditor'
		// inside the specified wrapper element.
		//
		// @param {Element|null|undefined} wrapper - The DOM element containing the CKEditor fields (e.g., this.$el).
		applyCKEditor(wrapper) {
			const root = this._getRootEl(wrapper)
			if (!root) {
				return
			}

			root.querySelectorAll('.ckeditor').forEach((element) => {
				if (element.getAttribute('data-ckeditor-initialized')) {
					return
				}
				element.setAttribute('data-ckeditor-initialized', 'true')

				ClassicEditor.create(element, {
					licenseKey: 'GPL',
					// We don't run any upload/file-manager backend, so disable
					// every image-source plugin except the URL form. Without
					// removing CKFinder/CKBox the `insertImage` button falls
					// back to "Insert image with file manager", which then
					// fails because there's no CKFinder server. With these
					// removed, the dropdown collapses to the bare "Image via
					// URL" form — exactly what tenants need to paste a logo
					// URL into a message template.
					removePlugins: [
						'ImageUpload',
						'EasyImage',
						'CKFinder',
						'CKBox',
						'CloudServices',
					],
					toolbar: [
						'undo',
						'redo',
						'|',
						'heading',
						'|',
						'bold',
						'italic',
						'|',
						'link',
						'insertImage',
						'insertTable',
						'blockQuote',
						'mediaEmbed',
						'|',
						'bulletedList',
						'numberedList',
						'indent',
						'outdent',
					],
					image: {
						toolbar: [
							'imageTextAlternative',
							'|',
							'imageStyle:inline',
							'imageStyle:block',
							'imageStyle:side',
						],
					},
				})
				.then((editor) => {
					// Keep a reference in case is needed
					element._ckEditor = editor

					// Initial + late async sync from <textarea> -> CKEditor
					// (prevents losing initial/late v-model values)
					this._syncFromTextareaToEditor(element, editor)

					// Bridge CKEditor events back to the original <textarea> so that:
					//  - v-model updates (by setting element.value)
					//  - Vue listeners like @input / @blur keep working
					editor.model.document.on('change:data', () => {
						// Skip emission if this change was programmatic (silentSetData)
						if (element._ckSync?.suppress) return

						// Update underlying textarea value so v-model sees the new content
						element.value = editor.getData()

						// Re-dispatch 'input' so Vue updates reactive state and triggers @input
						this._dispatchDomEvent(element, 'input')
					})

					// Propagate 'blur' when the editor loses focus
					editor.editing.view.document.on('blur', () => {
						this._dispatchDomEvent(element, 'blur')
					})

					// Add a "HTML" toggle next to the editor so users can drop
					// into raw markup when CKEditor's UI doesn't expose what
					// they need (e.g. an <img src="..."> tag pointing to an
					// externally hosted logo). The Classic Build doesn't
					// bundle SourceEditing, so we swap the editor for the
					// underlying textarea instead.
					this._addHtmlToggleButton(element, editor)
				})
				.catch((error) => {
					console.error('[CKEditor] Error:', error)
				})
			})
		},

		// ------------------------------------------------------------------------
		// Add a small "HTML" toggle button above the editor that swaps the
		// CKEditor instance for the raw textarea. Useful when the WYSIWYG
		// toolbar can't express what the user needs (e.g. <img> tags from an
		// external URL — the Classic Build doesn't ship SourceEditing).
		//
		// While in raw mode the underlying textarea is the source of truth
		// for v-model; on toggle back to rich mode CKEditor re-mounts on it.
		_addHtmlToggleButton(element, editor) {
			const editorEl = editor.ui.view.element
			if (!editorEl || !editorEl.parentNode) return
			if (element._htmlToggleBtn) return

			const btn = document.createElement('button')
			btn.type = 'button'
			btn.textContent = 'HTML'
			btn.title = 'Toggle raw HTML editing'
			btn.setAttribute('data-cke-html-toggle', '')
			btn.style.cssText = 'display:inline-block;margin:4px 0;padding:4px 10px;'
				+ 'font:600 12px/1 system-ui,sans-serif;border:1px solid #ccc;'
				+ 'border-radius:4px;background:#f7f7f7;cursor:pointer'

			editorEl.parentNode.insertBefore(btn, editorEl)
			element._htmlToggleBtn = btn
			element._mode = 'rich'

			btn.addEventListener('click', () => {
				// Read the current editor instance off the textarea so we
				// don't keep a stale closure reference across recreations.
				const live = element._ckEditor
				if (element._mode === 'rich') {
					if (!live) return
					// Rich → raw: pull current HTML, destroy editor, show textarea.
					const html = live.getData()
					this._destroyCKEditorInstance(element)
					element.value = html
					element.style.display = 'block'
					element.style.width = '100%'
					element.style.minHeight = '240px'
					element.style.fontFamily = 'ui-monospace,Menlo,monospace'
					element.style.fontSize = '13px'
					element._mode = 'raw'
					btn.textContent = 'WYSIWYG'
					btn.title = 'Back to formatted editor'
					// Re-dispatch input so Vue picks up textarea-driven edits
					if (!element._rawInputHandler) {
						element._rawInputHandler = () => this._dispatchDomEvent(element, 'input')
						element.addEventListener('input', element._rawInputHandler)
					}
					element.focus()
				} else {
					// Raw → rich: re-create CKEditor on the textarea.
					if (element._rawInputHandler) {
						element.removeEventListener('input', element._rawInputHandler)
						element._rawInputHandler = null
					}
					// Wipe the inline styles we added so CKEditor's own UI
					// can take over the visual area cleanly.
					element.style.cssText = ''
					element._mode = 'rich'
					btn.textContent = 'HTML'
					btn.title = 'Toggle raw HTML editing'
					// applyCKEditor() is idempotent; reset the marker first.
					element.removeAttribute('data-ckeditor-initialized')
					this.applyCKEditor(element.parentNode)
				}
			})
		},

		// ------------------------------------------------------------------------
		// Waits until at least one CKEditor-compatible element exists inside the wrapper,
		// then initializes them. Useful when DOM is rendered asynchronously (e.g., after v-if or v-for).
		//
		// @param {Element|null|undefined} wrapper - The DOM element to watch.
		// @param {number} interval - How often to check (ms).
		// @param {number} timeout - Max wait time (ms).
		initializeCkeditor(wrapper, interval = 100, timeout = 3000) {
			const root = this._getRootEl(wrapper)
			if (!root) {
				return
			}

			let waited = 0

			const check = () => {
				if (!root.isConnected) {
					return
				}

				const editors = root.querySelectorAll('textarea.ckeditor:not([data-ckeditor-initialized])')
				if (editors.length > 0) {
					this.applyCKEditor(root)
				} else if (waited < timeout) {
					waited += interval
					setTimeout(check, interval)
				} else {
					console.warn('[CKEditor] No eligible fields found after timeout.')
				}
			}

			check()
		},

		// ------------------------------------------------------------------------
		// Destroy a CKEditor instance bound to a textarea (safe cleanup)
		//
		// @param {HTMLTextAreaElement} element - The textarea hosting the editor.
		_destroyCKEditorInstance(element) {
			if (!element?._ckEditor) return
			try { element._ckSync?.stop?.() } catch {}
			const editor = element._ckEditor
			delete element._ckEditor
			element.removeAttribute('data-ckeditor-initialized')
			try {
				editor.destroy()
			} catch (e) {
				console.warn('[CKEditor] destroy error:', e)
			}
		},

		// ------------------------------------------------------------------------
		// Destroy obsolete editors:
		//  - textarea switched from 'rich' to 'plain'
		//  - textarea removed from DOM
		//
		// @param {Element|null|undefined} wrapper - The DOM element where editors live.
		cleanupCKEditors(wrapper) {
			const root = this._getRootEl(wrapper)
			if (!root) {
				return
			}

			root.querySelectorAll('textarea[data-ckeditor-initialized]')
				.forEach((el) => {
					if (!el.isConnected || !el.classList.contains('ckeditor')) {
						this._destroyCKEditorInstance(el)
					}
				})
		},
	},
}
