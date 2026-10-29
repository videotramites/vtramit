<template>
	<div class="section">
		<!-- Section header -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'General configuration') }}</h2>
		</div>
		<!-- Section body -->
		<div class="field-group grid-4-columns" data-test="generalConfig">
			<div class="field-item">
				<label for="admin">{{ t('vtramit', 'Admin username') }}</label>
				<input v-model="configuration.admin"
					id="admin"
					type="text"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div class="field-item">
				<label for="dateFormat">{{ t('vtramit', 'Date format') }}</label>
				<input v-model="configuration.dateFormat"
					id="dateFormat"
					type="text"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div class="field-item">
				<label for="timeFormat">{{ t('vtramit', 'Time format') }}</label>
				<input v-model="configuration.timeFormat"
					id="timeFormat"
					type="text"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div class="field-item">
				<label for="datetimeFormat">{{ t('vtramit', 'DateTime format') }}</label>
				<input v-model="configuration.datetimeFormat"
					id="datetimeFormat"
					type="text"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div class="field-item">
				<label for="phoneLink">{{ t('vtramit', 'Phone link') }}</label>
				<input v-model="configuration.phoneLink"
					id="phoneLink"
					type="text"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div class="field-item">
				<label for="phonePrefix">{{ t('vtramit', 'Phone prefix') }}</label>
				<input v-model="configuration.phonePrefix"
					id="phonePrefix"
					type="text"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div class="field-item">
				<label for="phoneDefaultCountryCode">{{ t('vtramit', 'Phone country code') }}</label>
				<input v-model="configuration.phoneDefaultCountryCode"
					id="phoneDefaultCountryCode"
					type="text"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div class="field-item">
					<!-- empty -->
			</div>
			<div class="field-item field-checkbox">
				<label for="mailsAllowed">{{ t('vtramit', 'Mail system enabled') }}</label>
				<input v-model="configuration.mailsAllowed"
					id="mailsAllowed"
					type="checkbox"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div class="field-item field-checkbox">
				<label for="citizenQueueInfo">{{ t('vtramit', 'Tell waiting citizen how many appointments are ahead') }}</label>
				<input v-model="configuration.citizenQueueInfo"
					id="citizenQueueInfo"
					type="checkbox"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
			</div>
			<div v-if="configuration.citizenQueueInfo" class="field-item">
				<label for="citizenQueueScope">{{ t('vtramit', 'Queue scope (count appointments ahead by)') }}</label>
				<select v-model="configuration.citizenQueueScope"
					id="citizenQueueScope"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
					<option value="assigned_user">{{ t('vtramit', 'Same assigned user') }}</option>
					<option value="assigned_group">{{ t('vtramit', 'Same assigned group') }}</option>
					<option value="procedure_type_and_group">{{ t('vtramit', 'Same procedure type and group') }}</option>
					<option value="topic">{{ t('vtramit', 'Same topic') }}</option>
				</select>
			</div>
		</div>

		<div class="field-group">
			<h3>{{  t('vtramit', 'Allowed languages') }}</h3>
			<div class="field-item" data-test="languages">
				<TwoColumnsSelector :optionsList="supportedLanguages"
					v-model="configuration.languages"
					wrapperClass="language-selector"
					:sort="true"
					@changed="formIsChanged = true" />
			</div>
		</div>

		<!-- Manager (gestor) groups -->
		<div class="field-group">
			<h3>{{  t('vtramit', 'Manager groups') }}</h3>
			<div class="help-inline">
				<p>
					{{ t('vtramit', 'Members of these groups (and global admins) can see every user of the groups they belong to, so appointment assignees are shown by name and can be used in the search filter.') }}
				</p>
			</div>
			<div class="field-item" data-test="managerGroups">
				<TwoColumnsSelector :optionsList="availableGroups"
					v-model="configuration.groupLimit"
					wrapperClass="manager-groups-selector"
					:sort="true"
					@changed="formIsChanged = true" />
			</div>
		</div>

		<!-- Procedures list -->
		<div class="field-group">
			<h3>{{  t('vtramit', 'Procedures list') }}</h3>
			<div class="field-item" data-test="procedureList">
				<label for="resultsPerPage">{{ t('vtramit', 'Total results per page') }}</label>
				<input v-model="configuration.resultsPerPage"
					id="resultsPerPage"
					type="number"
					@input="formIsChanged = true"
					@blur="formIsChanged = true">
				<div class="help-inline">
					<p>
						{{ t('vtramit', 'Pagination for procedures list') }}:
					</p>
					<ul>
						<li>
							{{ t('vtramit', '-1 (or any negative value) or 0') }}: {{ t('vtramit', 'Pagination deactivated') }}
						</li>
						<li>
							{{ t('vtramit', 'Any positive value') }}: {{ t('vtramit', 'The maximum number of procedures in view at once') }}
						</li>
					</ul>
				</div>
			</div>
		</div>

		<!-- Procedures list - Active filters -->
		<div class="field-group">
			<h3>{{  t('vtramit', 'Procedures list: Filters') }}</h3>
			<div>
				{{ t('vtramit', 'Select which filters will be available for the user on the procedures list') }}
			</div>
			<div class="vtramit grid-2-columns" data-test="procedureFilter">
				<div class="field-item field-checkbox">
					<label for="appointmentId-filter">{{ t('vtramit', 'Appointment ID') }}</label>
					<input v-model="filters.appointmentId"
						id="appointmentId-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
				<div class="field-item field-checkbox">
					<label for="status-filter">{{ t('vtramit', 'Status') }}</label>
					<input v-model="filters.status"
						id="status-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
				<div class="field-item field-checkbox">
					<label for="group-filter">{{ t('vtramit', 'Group') }}</label>
					<input v-model="filters.group"
						id="group-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
				<div class="field-item field-checkbox">
					<label for="assignedUser-filter">{{ t('vtramit', 'Assigned User') }}</label>
					<input v-model="filters.assignedUser"
						id="assignedUser-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
				<div class="field-item field-group">
					<div class="field-item field-checkbox">
						<label for="dueDate-filter">{{ t('vtramit', 'Due date (list of options)') }}</label>
						<input v-model="filters.dueDate"
							id="dueDate-filter"
							type="checkbox"
							@input="formIsChanged = true"
							@blur="formIsChanged = true">
					</div>
					<div class="field-item field-group--children">
						<div class="field-item" v-if="filters.dueDate">
							<label for="filterOverdueDays">{{ t('vtramit', 'Overdue days') }}</label>
							<input v-model="configuration.filterOverdueDays"
								id="filterOverdueDays"
								type="number"
								@input="formIsChanged = true"
								@blur="formIsChanged = true">

							<div class="help-inline">
								Defines the number of days to look back from today when using the "<em>Overdue</em>" filter.
							</div>
						</div>
					</div>
				</div>
				<div class="field-item field-checkbox align-top">
					<label for="dueDateRange-filter">{{ t('vtramit', 'Due date (range of dates)') }}</label>
					<input v-model="filters.dueDateRange"
						id="dueDateRange-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
				<div class="field-item field-checkbox">
					<label for="stakeholderName-filter">{{ t('vtramit', 'Stakeholder Name') }}</label>
					<input v-model="filters.stakeholderName"
						id="stakeholderName-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
				<div class="field-item field-checkbox">
					<label for="stakeholderCitizenId-filter">{{ t('vtramit', 'Stakeholder Citizen ID') }}</label>
					<input v-model="filters.stakeholderCitizenId"
						id="stakeholderCitizenId-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
				<div class="field-item field-checkbox">
					<label for="stakeholderPhone-filter">{{ t('vtramit', 'Stakeholder Phone') }}</label>
					<input v-model="filters.stakeholderPhone"
						id="stakeholderPhone-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
				<div class="field-item field-checkbox">
					<label for="stakeholderEmail-filter">{{ t('vtramit', 'Stakeholder Email') }}</label>
					<input v-model="filters.stakeholderEmail"
						id="stakeholderEmail-filter"
						type="checkbox"
						@input="formIsChanged = true"
						@blur="formIsChanged = true">
				</div>
			</div>
		</div>

		<!-- Action buttons -->
		<div class="d-flex justify-end form-actions">
			<button v-tooltip="{
					placement: 'auto',
					trigger: 'hover',
					content: t('vtramit', 'Update'),
					autoHide: false,
					html: true
				}"
				:disabled="!formReadyForSubmit || !formIsChanged"
				class="primary btn-save"
				@click="clickUpdateItem(configuration)">
				<span class="icon icon-upload-white" />
				<span class="label">
					{{ t('vtramit', 'Update') }}
				</span>
			</button>
		</div>
	</div>
</template>

<script>
/**
 * @module components/settings/General
 *
 * Component to edit the general configiguration
 *
 */
import TwoColumnsSelector from '../ui/TwoColumnsSelector.vue'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import MessageManager from '../../mixins/messageManager.mixin.js'
import axios from '@nextcloud/axios'
import { showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'General',
	components: {
		TwoColumnsSelector
	},
	mixins: [ComponentStatus, MessageManager],
	data: () => ({
		formIsChanged: false,
		// List of Nextcloud groups (from the server) for the manager-groups
		// picker. Kept out of `configuration` so it is never persisted back.
		availableGroups: [],
		configuration: {
			languages: [],
			resultsPerPage: 0,
			filterOption: [],
			filterOverdueDays: 0,
			dateFormat: '',
			timeFormat: '',
			datetimeFormat: '',
			admin: '',
			mailsAllowed: false,
			citizenQueueInfo: false,
			citizenQueueScope: 'assigned_user',
			groupLimit: [],
			phoneLink: '',
			phonePrefix: '',
			phoneDefaultCountryCode: '',
		},
		// Filters
		filters: {
			appointmentId: false,
			status: false,
			group: false,
			assignedUser: false,
			dueDate: false,
			dueDateRange: false,
			stakeholderName: false,
			stakeholderCitizenId: false,
			stakeholderPhone: false,
			stakeholderEmail: false,
		},

		urlForLoad: 'apps/vtramit/web/2.0/configuration',
		urlForInsert: 'apps/vtramit/web/2.0/configuration',
		urlForUpdate: 'apps/vtramit/web/2.0/configuration',
	}),
	computed: {
		// ------------------------------------------------------------------------
		// Computed to control a bit when the CRUD actions are happening (with
		// the help of the ComponentStatus mixin!) so we can easily prevent people
		// clicking buttons twice while they are waiting for the server response.
		//
		formReadyForSubmit() {
			if (this.status.loading) {
				return false
			}
			if (this.status.readError) {
				return false
			}
			return true
		},
		supportedLanguages() {
			return this.$store.getters['supportedLanguages/readAll']
		},
		/*resultsPerPage() {
			return this.$store.getters['configuration/getResultsPerPage']
		},
		/*filterOption() {
			return this.$store.getters['configuration/getFilterOption']
		},*/
	},
	created() {
		// Once the component is created we load the data needed for the form.
		this.loadFormData()
		this.$store.dispatch('supportedLanguages/readAll')
	},
	methods: {
		// ------------------------------------------------------------------------
		// Updates the filterOption in the configuration property
		//
		updateFilterOption() {
			this.configuration.filterOption = [];
			for(let i in this.filters) {
    		this.configuration.filterOption.push([i, this.filters[i]])
			}
		},
		// ------------------------------------------------------------------------
		// Loads the filters properties from the server
		//
		loadFilters() {
			for(let i in this.configuration.filterOption) {
    		let propertyName = this.configuration.filterOption[i][0]
				let propertyValue = this.configuration.filterOption[i][1]
				this.filters[propertyName] = propertyValue
			}
		},
		// ------------------------------------------------------------------------
		// Preserves state of filters after a PUT request while the new results are
		// not ready
		//
		keepFilters(){
			this.loadFilters()
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				languages: [],
				resultsPerPage: 0,
				filterOption: [],
				filterOverdueDays: 0,
				dateFormat: '',
				timeFormat: '',
				datetimeFormat: '',
				admin: '',
				mailsAllowed: false,
				citizenQueueInfo: false,
				citizenQueueScope: 'assigned_user',
				groupLimit: [],
				phoneLink: '',
				phonePrefix: '',
				phoneDefaultCountryCode: '',
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields and proper initial
		// values.
		//
		newItem(data = {}) {
			const newItem = this.prepareNewItem()
			// Data needed to control the state of the item.
			Object.assign(newItem, data)
			return newItem
		},
		// ------------------------------------------------------------------------
		// Loads the data from the backend API.
		//
		loadFormData() {
			// Setting the proper "ComponentStatus" so we can enable/disable things
			// whilst we are waiting for the API calls.
			this.setStatusReading()
			return axios({
				method: 'GET',
				url: generateUrl(this.urlForLoad),
			}).then((result) => {
				if (result.data?.result && result.data.result === 'OK') {
					// Assign new results
					this.availableGroups = result.data.availableGroups || []
					this.configuration = this.newItem(result.data.configuration)
					this.setStatusReadSuccess()
					return true
				}
				return false
			}).then(()=>{
				this.loadFilters()
			}).catch((error) => {
				// console.error('ERROR readAll', error)
				this.setStatusReadError()
				this.manageException(error, t('vtramit', 'Error loading initial configuration data'))
				throw error
			})
		},
		// ------------------------------------------------------------------------
		// Update item click handler.
		//
		clickUpdateItem(item) {
			// Setting the proper "ComponentStatus" so we can enable/disable things
			// whilst we are waiting for the API calls.
			this.setStatusUpdating()
			// Update the filterOption value with current values
			this.updateFilterOption()
			// Marking the state of the items for its particular CRUD buttons to
			// have the right state.
			item.isChanged = false
			const forApi = item
			return axios({
				method: 'PUT',
				url: generateUrl(this.urlForUpdate),
				headers: { 'content-type': 'application/json' },
				data: {
					data: forApi,
				},
			}).then((result) => {
				if (result.data?.result && result.data.result === 'OK') {
          showSuccess(t('vtramit', 'General configuration updated successfully'));
          this.setStatusUpdateSuccess();
					return this.$store.dispatch('configuration/readConfiguration')
				}
				return false
			}).then(() => {
				this.loadFormData(item)
			}).catch((error) => {
				// console.error('ERROR readAll', error)
				this.manageException(error, t('vtramit', 'Error updating the configuration'))
				// Marking the state of the items for its particular CRUD buttons to
				// have the right state.
				item.isChanged = true
			})
		},
	},
}
</script>
