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
	<div :class="{
		'rgpd-options-wrapper': true,
		'disabled': true
	}">
		<div class="info-block">
			{{ t('vtramit', 'The following information corresponds to the selection of the attended person. These data are not editable and are shown for informational purposes only.') }}
		</div>
		<div v-for="option in rgpdOptions" :key="option.id" class="rgpd-option">
			<template v-if="option.type == 'checkbox'">
				<input :id="option.id"
					v-model="option.checked"
					type="checkbox"
					:required="option.required">
				<label :for="option.id">
					<span v-html="rgpdHtml(option)"></span>
				</label>
			</template>
			<template v-else-if="option.type == 'markup'">
				<div v-html="rgpdLabel(option)" />
			</template>
		</div>
		<div v-if="info" class="vtramit-info">
			{{ infoMessage }}
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import Vue from 'vue'
import { generateUrl } from '@nextcloud/router'
import { getLanguage } from '@nextcloud/l10n'

export default {
	props: {
		mode: {
			type: String,
			default: () => 'new',
		},
		procedure: {
			type: Object,
			default: () => {},
		},
		stakeholder: {
			type: Object,
			default: () => {},
		},
		rgpdOptions: {
			type: Array,
			default: () => [],
		},
	},
	data: () => ({
		error: false,
		info: false,
		infoMessage: '',
		rgpdDisabled: true,
		rgpdActivity: {},
		rgpdActivityUrlForLoad: 'apps/vtramit/web/2.0/procedure/%procedureId/stakeholder/%procedureStakeholderId/rgpd-activity/last',
		rgpdActivityUrlForInsert: 'apps/vtramit/web/2.0/rgpd-activity',
	}),
	computed: {
		isEditMode() {
			return this.mode === 'edit'
		},
	},
	created() {
		this.rgpdOptions.forEach((option, index) => {
			// Define the new 'checked' property with reactivity as we
			// want to change them using last RGPD actibity
			Vue.set(this.rgpdOptions[index], 'checked', false)
		})

		if (this.stakeholder && this.stakeholder.procedureId && this.stakeholder.id) {
			this.loadFormData()
		}
	},
	methods: {
		// ------------------------------------------------------------------------
		// Obtain the proper label to print for given option using user language.
		rgpdLabel(option) {
			const language = getLanguage()
			let label = option.label.find((label) => { return (label.key === language) })
			if (label) {
				return label.value
			}
			label = option.label.find((label) => { return (label.key === 'ca') })
			if (label) {
				return label.value
			}
			label = option.label.find((label) => { return (label.key === 'es') })
			if (label) {
				return label.value
			}
			return ''
		},
		// ------------------------------------------------------------------------
		// Build final HTML: base label + required star + help injected in last <p>.
		rgpdHtml(option) {
			const base = this.rgpdLabel(option) || ''
			if (!option.required) return base

			const helpText = t('vtramit', 'This option is required')
			const inject = `<span class="required" aria-hidden="true">*</span><span class="required-help">${helpText}</span>`

			return base + inject
		},
		// ------------------------------------------------------------------------
		// Select the options defined in the REGP activity.
		//
		updateOptionsDefaultSelection() {
			if (this.rgpdActivity && this.rgpdActivity.optionList) {
				this.rgpdOptions.forEach((option, index) => {
					Vue.set(this.rgpdOptions[index], 'checked', this.rgpdActivity.optionList.includes(option.id))
				})
			}
			this.rgpdDisabled = false
		},
		// ------------------------------------------------------------------------
		// Loads the data from the backend API.
		//
		loadFormData() {
			this.info = true
			this.infoMessage = t('vtramit', 'Data is loading, please wait')

			// Prepare the URL to load all items
			const urlForApi = this.rgpdActivityUrlForLoad.replace('%procedureId', this.stakeholder.procedureId).replace('%procedureStakeholderId', this.stakeholder.id)
			return axios({
				method: 'GET',
				url: generateUrl(urlForApi),
			}).then((result) => {
				if (result.data && result.data.result && result.data.result === 'OK' && result.data.entity) {
					this.rgpdActivity = result.data.entity
					this.updateOptionsDefaultSelection()
					this.info = false
					return true
				}
				return false
			}).catch((error) => {
				this.info = false
				console.log('Loading RGPD activity error', error)
				return false
			})
		},
	},
}
</script>

<style lang="scss" scoped>
	.rgpd-options-wrapper {
		display: inline-grid;
		margin: 10px 20px;

		&.disabled {
			pointer-events: none;
			opacity: 0.8;
		}

		.info-block {
			background-color: #eeeeee;
			margin-top: 1rem;
			margin-bottom: 3rem;
			padding: 16px;
			max-width: 800px;
			text-align: center;
			font-size: 1.2em;
		}

		.rgpd-option {
			display: inline-flex;
			position: relative;
			word-break: break-all;

			+ .rgpd-option {
				margin-top: 10px;
			}

			input[type='checkbox'] {
				align-self: start;
				min-height: auto;
				height: 24px;
				margin-right: 4px;
			}

			input[type='checkbox'] + label {
				margin-left: 3px;
				display: inline-block;
				position: relative;
			}

			p:last-of-type {
				display: inline-block;
			}

			span.required {
				color: red;
				padding-left: 4px;
			}

			span.required-help {
				display: none;
				background: #7e7e7e;
				border-radius: 9px;
				color: #FFFFFF;
				font-size: 0.8rem;
				white-space: nowrap;
				position: absolute;
				padding: 0 8px;
				bottom: 19px;
				margin-left: -70px;
				pointer-events: none;
			}

			span.required:hover + span.required-help {
				display: initial;
			}
		}
	}
</style>
