// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import BaseModel from './baseModel.js'

export default class Model extends BaseModel {

	constructor(config = {}) {
		super()

		this.procedureId = null
		this.personalId = null
		this.name = null
		this.phone = null
		this.email = null
		this.stakeholderUploadsUrl = null
		this.stakeholderDownloadsUrl = null
		this.jitsiRoomStakeholderUrl = null
		this.attendanceChannel = null
		this.entryPage = null
		this.entryPassword = null
		this.fullEntryPage = null
		this.deadlineDate = null
		this.isMain = null
		this.deckCardId = null

		Object.assign(this, config)
	}

}
