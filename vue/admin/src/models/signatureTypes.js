// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

import BaseModel from './baseModel.js'

export default class Model extends BaseModel {

	constructor(config = {}) {
		super()

		this.name = ''
    this.requestLimit = null
		this.filesPerRequest = null
		this.fileType = null
    this.format = null
		this.method = null
		this.certifiedAuthority = null
		this.certifiedAuthorityProxy = null
		this.additionalInfo = null
		this.phoneRequired = null

		Object.assign(this, config)
	}

}
