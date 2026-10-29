// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import { showError } from '@nextcloud/dialogs'

export default {
	methods: {
		// ------------------------------------------------------------------------
		// Gets the DOM reference to the field in the given item.
		//
		getField(field, item = null) {
			if (item !== null) {
				const idx = this.itemList.indexOf(item)
				if (idx < 0) {
					return null
				}

				return this.$el.querySelector('#' + this.getElementId(field, idx))
			}

			return this.$el.querySelector('#' + field)
		},
		// ------------------------------------------------------------------------
		// Focus the cursor to the field in the given item.
		//
		focusField(field, item = null) {
			const el = this.getField(field, item)
			if (el) {
				if (item !== null) {
					item.isExpanded = true
				}
				this.$nextTick(() => {
					el.focus()
				})
			}
		},
		// ------------------------------------------------------------------------
		// Validates if field in given item has a valid JSON.
		//
		isFieldJsonValid(field, item, showMessage = false) {
			const el = this.getField(field, item)
			if (!el) {
				return true
			}

			let isValid

			// 1) Dataset set by the JSON mixin
			if (typeof el.dataset.jsonValid !== 'undefined') {
				isValid = el.dataset.jsonValid !== 'false'

			// 2) Fallback: validate current value (empty = valid)
			} else {
				const v = (el.value || '').trim()
				if (!v) {
					isValid = true
				} else {
					try { JSON.parse(v); isValid = true } catch { isValid = false }
				}
			}

			if (!isValid && showMessage) {
				showError(t('vtramit', 'Cannot save: review your JSON before safe'), true)
			}

			return isValid
		},
  },
}
