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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 -->

<template>
	<div class="vtramit">
		<NavigationTabs activeTab="procedures" />
		<ProcedureTypes />
	</div>
</template>

<script>
import NavigationTabs from './../components/NavigationTabs.vue'
import ProcedureTypes from './../components/settings/ProcedureTypes.vue'

export default {
	name: 'ProceduresPage',
  components: {
		NavigationTabs,
		ProcedureTypes
	},
	data: () => ({
	}),
	created() {
		this.$store.dispatch('configuration/readConfiguration')
	},
}
</script>
