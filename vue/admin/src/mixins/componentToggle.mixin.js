// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

export default {
	data: () => ({
		itemList: [],
	}),
	methods: {
		// ------------------------------------------------------------------------
		// Expand current item and collapse others.
		//
		toggleItem(item, event) {
			const target = event?.currentTarget || null

			if (item.isExpanded !== true) {
				this.collapseOtherItems(item)
			}

			item.isExpanded = !item.isExpanded

			if (item.isExpanded && target) {
				this.$nextTick(() => {
					// Ensure the opened item header is aligned to the top of the viewport.
					requestAnimationFrame(() => {
						if (typeof target.scrollIntoView === 'function') {
							target.scrollIntoView({ block: 'start', inline: 'nearest', behavior: 'auto' })
						}
					})
				})
			}
		},
		// ------------------------------------------------------------------------
		// Collapse all items but the given one.
		//
		collapseOtherItems(itemToHold) {
			this.itemList.forEach((item) => {
				if (item !== itemToHold) {
					item.isExpanded = false
				}
			})
		},
		// ------------------------------------------------------------------------
		// Collapse all items.
		//
		collapseAllItems() {
			this.itemList.forEach((item) => {
				item.isExpanded = false
			})
		},
	},
}
