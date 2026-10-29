// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

export default {
	data: () => ({
		status: {
			initialising: false,
			loading: false,

			creating: false,
			createSuccess: false,
			showCreateSuccess: false,
			createError: false,
			showCreateError: false,

			reading: false,
			readSuccess: false,
			showReadSuccess: false,
			readError: false,
			showReadError: false,

			readingMore: false,
			readMoreSuccess: false,
			showReadMoreSuccess: false,
			readMoreError: false,
			showReadMoreError: false,

			updating: false,
			updateSuccess: false,
			showUpdateSuccess: false,
			updateError: false,
			showUpdateError: false,

			deleting: false,
			deleteSuccess: false,
			showDeleteSuccess: false,
			deleteError: false,
			showDeleteError: false,

			formValid: false,
		},
	}),

	methods: {
		setStatusCreating() {
			this.status.loading = true
			this.status.creating = true
			this.status.createSuccess = false
			this.status.showCreateSuccess = false
			this.status.createError = false
			this.status.showCreateError = false
		},
		setStatusCreateSuccess() {
			this.status.loading = false
			this.status.creating = false
			this.status.createSuccess = true
			this.status.showCreateSuccess = true
		},
		setStatusShowCreateSuccess(value) {
			this.status.showCreateSuccess = value
		},
		setStatusCreateError() {
			this.status.loading = false
			this.status.creating = false
			this.status.createError = true
			this.status.showCreateError = true
		},
		setStatusShowCreateError(value) {
			this.status.showCreateError = value
		},

		setStatusReading(initialising = false) {
			this.status.initialising = initialising
			this.status.loading = true
			this.status.reading = true
			this.status.readSuccess = false
			this.status.showReadSuccess = false
			this.status.readError = false
			this.status.showReadError = false
		},
		setStatusReadSuccess() {
			this.status.initialising = false
			this.status.loading = false
			this.status.reading = false
			this.status.readSuccess = true
			this.status.showReadSuccess = true
		},
		setStatusShowReadSuccess(value) {
			this.status.showReadSuccess = value
		},
		setStatusReadError() {
			this.status.initialising = false
			this.status.loading = false
			this.status.reading = false
			this.status.readError = true
			this.status.showReadError = true
		},
		setStatusShowReadError(value) {
			this.status.showReadError = value
		},

		setStatusReadingMore() {
			this.status.loading = false
			this.status.readingMore = true
			this.status.readMoreSuccess = false
			this.status.showReadMoreSuccess = false
			this.status.readMoreError = false
			this.status.showReadMoreError = false
		},
		setStatusReadMoreSuccess() {
			this.status.loading = false
			this.status.readingMore = false
			this.status.readMoreSuccess = true
			this.status.showReadMoreSuccess = true
		},
		setStatusShowReadMoreSuccess(value) {
			this.status.showReadMoreSuccess = value
		},
		setStatusReadMoreError() {
			this.status.loading = false
			this.status.readingMore = false
			this.status.readMoreError = true
			this.status.showReadMoreError = true
		},
		setStatusShowReadMoreError(value) {
			this.status.showReadMoreError = value
		},

		setStatusUpdating() {
			this.status.loading = true
			this.status.updating = true
			this.status.updateSuccess = false
			this.status.showUpdateSuccess = false
			this.status.updateError = false
			this.status.showUpdateError = false
		},
		setStatusUpdateSuccess() {
			this.status.loading = false
			this.status.updating = false
			this.status.updateSuccess = true
			this.status.showUpdateSuccess = true
		},
		setStatusShowUpdateSuccess(value) {
			this.status.showUpdateSuccess = value
		},
		setStatusUpdateError() {
			this.status.loading = false
			this.status.updating = false
			this.status.updateError = true
			this.status.showUpdateError = true
		},
		setStatusShowUpdateError(value) {
			this.status.showUpdateError = value
		},

		setStatusDeleting() {
			this.status.loading = true
			this.status.deleting = true
			this.status.deleteSuccess = false
			this.status.showDeleteSuccess = false
			this.status.deleteError = false
			this.status.showDeleteError = false
		},
		setStatusDeleteSuccess() {
			this.status.loading = false
			this.status.deleting = false
			this.status.deleteSuccess = true
			this.status.showDeleteSuccess = true
		},
		setStatusShowDeleteSuccess(value) {
			this.status.showDeleteSuccess = value
		},
		setStatusDeleteError() {
			this.status.loading = false
			this.status.deleting = false
			this.status.deleteError = true
			this.status.showDeleteError = true
		},
		setStatusShowDeleteError(value) {
			this.status.showDeleteError = value
		},
	},
}
