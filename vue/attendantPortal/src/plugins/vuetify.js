// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

// src/plugins/vuetify.js

import Vue from 'vue'
import Vuetify from 'vuetify/lib/index.js'

Vue.use(Vuetify)

const opts = {
	theme: {
		themes: {
			light: {
				primary: '#0082c9',
				secondary: '#424242',
				mainBackground: '#424242',
				shadowBackground: '#F5F5F5',
				footer: '#F5F5F5',
				trash: '#B71C1C',
				error: '#FF5252',
				info: '#2196F3',
				success: '#4CAF50',
				warning: '#FFC107',
			},
		},
	},
}

export default new Vuetify(opts)
