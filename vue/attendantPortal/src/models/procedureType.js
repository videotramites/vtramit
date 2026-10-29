// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import BaseModel from './baseModel.js'

export default class Model extends BaseModel {

	constructor(config = {}) {
		super()

		this.id = null
		this.name = null
		this.rgpdOptions = []
    this.allowUpload = null
		this.uploadsFolder = null
		this.allowDownload = null
		this.downloadsFolder = null
		this.allowSignature = null
		this.signatureUploadsFolder = null
		this.signatureDownloadsFolder = null
		this.signatureTimeframeSign = null
		this.signatureTimeframeDownload = null
		this.signatureLimit = null
		this.allowVideo = null
		this.jitsiStakeholderUrl = null
		this.jitsiStakeholderJwtSecret = null
		this.jitsiStakeholderJwtPayload = null
		this.jitsiStaffUrl = null
		this.jitsiStaffJwtSecret = null
		this.jitsiStaffJwtPayload = null
		this.allowedAttendanceChannels = []
		this.notifyStakeholder = null
		this.notifyStaff = null
		this.requireAssignedGroup = null
		this.requireExternalId = null
		this.requireAppointmentDate = null
		this.requireDeadlineDate = null
		this.requireStakeholderPid = null
		this.requireStakeholderPhone = null
		this.requireStakeholderEmail = null
		this.requireAttendanceChannel = null
		this.attendantPortalProperties = { allowRemoteCommands: false }
		this.deckIntegration = null
		this.deckBoard = null
		this.deckStack = null
		this.procedureAnonymizationPeriod = null
		this.filesDeletionPeriod = null
		this.procedureDeletionPeriod = null

		Object.assign(this, config)
	}

}
