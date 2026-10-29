// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import BaseModel from './baseModel.js'

export default class Model extends BaseModel {

	constructor(config = {}) {
		super()

		this.appointmentId = null
		this.procedureId = null
		this.firstConnection = null
		this.lastConnection = null

		Object.assign(this, config)
	}

}
