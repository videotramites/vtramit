<!--
/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia Benítez <letizia@videoatencion.com>
 * @author Daniel Tamajon <dtamajon@litesolutions.es>
 * @author Marta González <marta.gonzalez@litesolutions.es>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan Reyné <ivanreyne@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
-->

<template>
	<!-- Minimal button: icon-only, place it wherever you need -->
	<v-btn
		v-if="hasId"
		icon
		:title="t('vtramit', 'Go to Admin area')"
		@click="goToAdmin"
	>
		<v-icon>mdi-shield-account-outline</v-icon>
	</v-btn>
</template>

<script>
export default {
	name: 'BackToAdmin',
	props: {
		// Required procedure ID used to build the destination URL
		procedureId: {
			type: [Number, String],
			required: false,
			default: null,
		},
	},
	computed: {
		// ------------------------------------------------------------------------
		// Whether we have a non-empty ID
		hasId() {
			return String(this.procedureId || '').trim().length > 0
		},
	},
	methods: {
		// ------------------------------------------------------------------------
		// Builds the VTramit base URL using the current instance (with or without index.php)
		buildBaseUrl() {
			const { origin, pathname } = window.location
			const usesIndex = /\/index\.php\//.test(pathname)
			const m = RegExp(/^(.*?)(?:index\.php\/)?apps\/vtramit\//).exec(pathname)
			const prefix = m ? (m[1] || '/') : '/'
			return `${origin}${prefix}${usesIndex ? 'index.php/' : ''}apps/vtramit/#/`
		},

		// ------------------------------------------------------------------------
		// Navigates to the edit page for the given procedure
		goToAdmin() {
			const id = String(this.procedureId || '').trim()
			if (!id) return
			const base = this.buildBaseUrl()
			const url = `${base}${encodeURIComponent(id)}/edit`
			window.open(url, 'vtramit-admin')
		},
	},
}
</script>
