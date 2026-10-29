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
	<v-menu v-model="menu" offset-y bottom left>
		<template v-slot:activator="{ on, attrs }">
			<v-btn icon v-bind="attrs" v-on="on" :disabled="isSending" :title="t('vtramit', 'Remote commands')">
				<v-icon>mdi-remote-desktop</v-icon>
			</v-btn>
		</template>

		<v-list class="remote-commands--list" dense>
			<v-list-item @click="sendCommand('change-camera')">
				<v-list-item-title>{{ t('vtramit', 'Change camera') }}</v-list-item-title>
			</v-list-item>

			<v-list-item @click="sendCommand('print')">
				<v-list-item-title>{{ t('vtramit', 'Print') }}</v-list-item-title>
			</v-list-item>
		</v-list>
	</v-menu>
</template>

<script>
import ApiService from '../../../mixins/apiService.mixin.js'

export default {
	name: 'RemoteCommands',
	mixins: [ApiService],
	props: {
		jitsiRoomCode: { type: String, required: true },
	},

	data() {
		return {
			menu: false,
			isSending: false,
		}
	},

	methods: {
		// ------------------------------------------------------------------------
		// Sends a remote command to the backend endpoint.
		//
		// @param {String} command
		//
		async sendCommand(command) {
			if (!this.jitsiRoomCode || this.isSending) {
				return
			}

			this.isSending = true
      const url = `/apps/vtramit/web/2.0/videoconference/${encodeURIComponent(this.jitsiRoomCode)}/command`

      await this.post(url, {
        data: { body: command }
      }, true).finally(() => {
        this.isSending = false
        this.menu = false
      })
		},
	},
}
</script>

<style lang="scss" scoped>
.remote-commands--list {
	.v-list-item__title {
		font-size: 16px;
	}
}
</style>
