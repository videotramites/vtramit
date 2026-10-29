// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import { showSuccess, showError } from '@nextcloud/dialogs'
import '@nextcloud/dialogs/styles/toast.scss'

export default {
	methods: {
		manageMessage(data, defaultMessage) {
			if (data?.result) {
				if (data.result === 'OK') {
					if (Array.isArray(data.message)) {
						data.message.forEach(message => showSuccess(message))
					} else if (data.message) {
						showSuccess(data.message)
					} else if (defaultMessage) {
						showSuccess(t('vtramit', defaultMessage), true)
					}
					return true
				} else {
					if (Array.isArray(data.message)) {
						data.message.forEach(message => showError(message))
					} else {
						showError(data.message)
					}
					return false
				}
			}
		},
		manageException(error, defaultMessage, updateStatus = true) {
			if (error?.result) {
				this.manageMessage(error)
			} else if (error) {
				showError(t('vtramit', error))
			} else if (defaultMessage) {
				showError(t('vtramit', defaultMessage), true)
			}
			if (updateStatus === true) {
				// Setting component status to failure.
				this.setStatusUpdateError()
			}
		},
	},
}
