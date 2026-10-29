// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import BaseModel from './baseModel.js'

export default class Model extends BaseModel {

  constructor(config = {}) {
    super()

    this.procedureTypeId = null
    this.externalId = null
    this.assignedGroupId = null
    this.assignedUserId = null
    this.topic = null
    this.comments = null
    this.staffUploadsUrl = null
    this.staffDownloadsUrl = null
    this.staffSignatureUploadsUrl = null
    this.staffSignatureDownloadsUrl = null
    this.jitsiRoomCode = null
    this.jitsiRoomStaffUrl = null
    this.statusId = null
    this.statusDate = null
    this.attendanceChannel = null
    this.appointmentDate = null
    this.deadlineDate = null
    this.foldersOwner = null
    this.modificationDate = null
    this.modificationUser = null

    Object.assign(this, config)
  }

}
