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
		'disabled': isRgpdDisabled
	}">
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
		<div v-if="isEditMode && error" class="vtramit-error">
			{{ errorMessage }}
		</div>
		<div v-if="isEditMode && info" class="vtramit-info">
			{{ infoMessage }}
		</div>
		<div v-if="isEditMode" class="d-flex flex-column align-center">
			<div class="mt-4 w-200">
				<v-btn color="primary"
					@click="validateAndSave">
					{{ t('vtramit', 'Save') }}
				</v-btn>
			</div>
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
		appointment: {
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
		errorMessage: '',
		info: false,
		infoMessage: '',
		rgpdDisabled: true,
		rgpdActivity: {},
		rgpdActivityUrlForLoad: 'apps/vtramit/api/2.0/procedure/%procedureId/stakeholder/%procedureStakeholderId/rgpd-activity/last',
		rgpdActivityUrlForInsert: 'apps/vtramit/api/2.0/rgpd-activity',
	}),
	computed: {
		isEditMode() {
			return this.mode === 'edit'
		},
		isRgpdDisabled() {
			return this.isEditMode && this.rgpdDisabled
		},
	},
	created() {
		this.rgpdOptions.forEach((option, index) => {
			// Define the new 'checked' property with reactivity as we
			// want to change them using last RGPD actibity
			Vue.set(this.rgpdOptions[index], 'checked', false)
		})

		if (this.isEditMode) {
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
		// Prepare the new activity item with all the needed fields and proper
		// values to be saved.
		//
		prepareNewActivity() {
			const activity = {}
			activity.procedureId = this.stakeholder.procedureId
			activity.procedureStakeholderId = this.stakeholder.id
			// data.jitsiRoomCode = appointment.jitsiRoomCode
			activity.labelList = []
			activity.optionList = []
			this.rgpdOptions.forEach(option => {
				if (option.type === 'checkbox' && option.checked === true) {
					activity.labelList.push(this.rgpdLabel(option))
					activity.optionList.push(option.id)
				}
			})
			return activity
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
		// ------------------------------------------------------------------------
		// Validate if all required options are selected and, if so, save user's
		// selection.
		//
		validateAndSave() {
			this.error = false
			this.info = false

			this.rgpdOptions.forEach(option => {
				if (option.required === true && option.type === 'checkbox' && option.checked === false) {
					this.error = true
					this.errorMessage = t('vtramit', 'Please, accept the required conditions to continue')
					this.$emit('onError', this.errorMessage)
				}
			})
			if (this.error === false) {
				return this.saveActivity().then((result) => {
					if (result === false) {
						this.error = true
						this.errorMessage = t('vtramit', 'An internal error has been produced; please try it again later')
						this.$emit('onError', this.errorMessage)
					} else {
						this.info = true
						this.infoMessage = t('vtramit', 'Your selection has been saved')
					}

					return !this.error
				})
			}
			// Return a promise accomplished at the very same time
			return Promise.resolve(!this.error)
		},
		// ------------------------------------------------------------------------
		// Send the activity information to be saved.
		//
		saveActivity() {
			// Preapre the URL to create the new item
			const forApi = this.prepareNewActivity()
			return axios({
				method: 'POST',
				url: generateUrl(this.rgpdActivityUrlForInsert),
				headers: {
					'content-type': 'application/json',
					requesttoken: undefined,
				},
				data: {
					data: forApi,
				},
			}).then((result) => {
				return result.data && result.data.result && result.data.result === 'OK'
			}).catch((error) => {
				console.log('RGPD activity error', error)
				this.$emit('onError', t('vtramit', 'An internal error has been produced; please try it again later'))
				return false
			})
		},
	},
}
</script>

<style lang="scss">
	.rgpd-options-wrapper {
		display: inline-grid;
		margin: 10px 20px;
	}

	.rgpd-options-wrapper .rgpd-option {
		display: inline-flex;
		position: relative;
		word-break: break-all;
	}

	.rgpd-options-wrapper .rgpd-option + .rgpd-option {
		margin-top: 10px;
	}

	.rgpd-options-wrapper .rgpd-option input[type='checkbox'] {
		align-self: start;
		min-height: auto;
		height: 24px;
		margin-right: 4px;
	}

	.rgpd-options-wrapper .rgpd-option input[type='checkbox'] + label {
		margin-left: 3px;
		display: inline-block;
		position: relative;
	}

	.rgpd-options-wrapper .rgpd-option p:last-of-type {
		display: inline-block;
	}

	.rgpd-options-wrapper .rgpd-option span.required {
		color: red;
		padding-left: 4px;
	}

	.rgpd-options-wrapper .rgpd-option span.required-help {
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

	.rgpd-options-wrapper .rgpd-option span.required:hover + span.required-help {
		display: initial;
	}
</style>
