<template>
	<div class="filter-panel-container">
		<div class="filter">
			<div v-if="isFilterAvailable('status')" class="filter--group">
				<!-- Filter by state -->
				<div>{{ t('vtramit', 'Status') }}</div>
				<div class="filter--control--checkbox-group">
					<div v-for="status in statuses" :key="`status_${status.id}`" class="filter--item filter--item--checkbox">
						<template v-if="status.filterable">
							<input :id="`val_status_${status.id}`"
								v-model="filterStatuses"
								type="checkbox"
								class="checkbox"
								:value="status.id"
								>
							<label :for="`val_status_${status.id}`"><span class="label">{{ status.name ? t('vtramit', status.name) : '' }}</span></label>
						</template>
					</div>
				</div>
			</div>

			<div v-if="isFilterAvailable('group')" class="filter--group department">
				<!-- Filter by department -->
				<div>{{ t('vtramit', 'Department') }}</div>
				<div class="filter--control--checkbox-group department">
					<div v-for="group in groups" :key="`groups_${group.id}`" class="filter--item filter--item--checkbox">
						<input :id="`val_group_${group.id}`"
							v-model="filterGroups"
							type="checkbox"
							class="checkbox"
							:value="group.id"
							>
						<label :for="`val_group_${group.id}`">{{ group.name }}</label>
					</div>
				</div>
			</div>

			<div v-if="isFilterAvailable('assignedUser')" class="filter--group">
				<!-- Filter by assigned user -->
				<div>{{ t('vtramit', 'Assigned to') }}</div>
				<div class="filter--control--checkbox-group">
					<div class="filter--item">
						<input id="unassigned"
							v-model="filterUnassigned"
							type="checkbox"
							class="checkbox"
							value="unassigned"
							>
						<label for="unassigned">{{ t('vtramit', 'Unassigned') }}</label>
					</div>
					<div v-for="user in users" :key="`users_${user.id}`" class="filter--item filter--item--checkbox">
						<input :id="`val_user_${user.id}`"
							v-model="filterUsers"
							type="checkbox"
							class="checkbox"
							:value="user.id"
							>
						<label :for="`val_user_${user.id}`">{{ user.name }}</label>
					</div>
				</div>
			</div>

			<div v-if="isFilterAvailable('dueDate')" class="filter--group">
				<!-- Filter by date -->
				<div>{{ t('vtramit', 'Range') }}</div>
				<div class="filter--control--radio-group">
					<div class="filter--item filter--item--radio">
						<input id="overdue"
							v-model="filterDueDate"
							name="filterData"
							type="radio"
							class="radio"
							value="overdue"

							>
						<label for="overdue">{{ t('vtramit', 'Overdue') }}</label>
					</div>
					<div class="filter--item filter--item--radio">
						<input id="dueNow"
							v-model="filterDueDate"
							name="filterData"
							type="radio"
							class="radio"
							value="dueNow"

							>
						<label for="dueNow">{{ t('vtramit', 'Now') }}</label>
					</div>
					<div class="filter--item filter--item--radio">
						<input id="dueToday"
							v-model="filterDueDate"
							name="filterData"
							type="radio"
							class="radio"
							value="dueToday"
							>
						<label for="dueToday">{{ t('vtramit', 'Today') }}</label>
					</div>
					<div class="filter--item filter--item--radio">
						<input id="dueWeek"
							v-model="filterDueDate"
							name="filterData"
							type="radio"
							class="radio"
							value="dueWeek"

							>
						<label for="dueWeek">{{ t('vtramit', 'Next 7 days') }}</label>
					</div>
					<div class="filter--item filter--item--radio">
						<input id="dueMonth"
							v-model="filterDueDate"
							name="filterData"
							type="radio"
							class="radio"
							value="dueMonth"

							>
						<label for="dueMonth">{{ t('vtramit', 'Next 30 days') }}</label>
					</div>
					<div class="filter--item" style="display: none">
						<input id="customDueRange"
							v-model="filterDueDate"
							name="filterData"
							type="radio"
							class="radio"
							value="customDueRange"
							>
						<label for="customDueRange">{{ t('vtramit', 'Custom') }}</label>
					</div>
				</div>
			</div>

			<div v-if="isFilterAvailable('dueDateRange')" class="filter--group filter--group--datetime">
				<!-- From due date -------------------------------------------------- -->
				<label>{{ t('vtramit', 'Range From') }}
					<div>
						<DatetimePicker data-test="initial-date" v-model="filterDueDateFrom"
							format="YYYY-MM-DD H:mm"
							type="datetime"
							:default-value="new Date()"
							/>
					</div>
				</label>
				<!-- To due date -------------------------------------------------- -->
				<label>{{ t('vtramit', 'To') }}
					<div>
						<DatetimePicker data-test="end-date" v-model="filterDueDateTo"
							format="YYYY-MM-DD H:mm"
							type="datetime"
							:default-value="new Date()"
							/>
					</div>
				</label>
			</div>

			<div class="two-col-grid">

				<!-- Filter by text -->
				<div v-if="isFilterAvailable('appointmentId')" class="filter--group">
					<label :for="'filter_free_search'"><span class="label">{{ t('vtramit', 'Appointment ID') }}</span></label>
					<input :data-test="'ID'" :id="'filter_free_search'"
						v-model="filterFreeSearch"
						type="text"
						>
				</div>

				<!-- Filter by name -->
				<div v-if="isFilterAvailable('stakeholderName')" class="filter--group">
					<label :for="'filter_name'"><span class="label">{{ t('vtramit', 'Name') }}</span></label>
					<input :data-test="'name'" :id="'filter_name'"
						v-model="filterName"
						type="text"
						>
				</div>

				<!-- Filter by citizen ID -->
				<div v-if="isFilterAvailable('stakeholderCitizenId')" class="filter--group">
					<label :for="'filter_citizenId'"><span class="label">{{ t('vtramit', 'Citizen ID') }}</span></label>
					<input :data-test="'citizen-id'" :id="'filter_citizenId'"
						v-model="filterCitizenId"
						type="text"
						>
				</div>

				<!-- Filter by phone -->
				<div v-if="isFilterAvailable('stakeholderPhone')" class="filter--group">
					<label  :for="'filter_phone'"><span class="label">{{ t('vtramit', 'Phone') }}</span></label>
					<input :data-test="'phone'" :id="'filter_phone'"
						v-model="filterPhone"
						type="text"
						>
				</div>

				<!-- Filter by email -->
				<div v-if="isFilterAvailable('stakeholderEmail')" class="filter--group">
					<label :for="'filter_email'"><span class="label">{{ t('vtramit', 'Email') }}</span></label>
					<input :data-test="'email'" :id="'filter_email'"
						v-model="filterEmail"
						type="text"
						>
				</div>
			</div>
		</div>

		<div class="filter-actions">
			<Button @click="applyFilter" data-test="apply">
				{{ t('vtramit', 'Apply filter') }}
			</Button>
			<Button @click="clearFilter" data-test="clear">
				{{ t('vtramit', 'Clear filter') }}
			</Button>
		</div>
	</div>
</template>

<script>
/**
 * @module procedures/components/ProceduresListFilter
 *
 * Component to handle the list filter options.
 *
 */
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { DatetimePicker } from '@nextcloud/vue'
export default {
	name: 'ProceduresListFilter',
	components: {
		DatetimePicker,
	},
	data: () => ({
		filterDepartmentsSorted: [],
		filterUsersSorted: [],
		// Available filters
		filterOptions: [],
		// Filter options.
		filterFreeSearch: '',
		filterName: '',
		filterCitizenId: '',
		filterPhone: '',
		filterEmail: '',
		filterDueDateFrom: '',
		filterDueDateTo: '',
		filterDueDate: 'dueToday',
		filterStatuses: [],
		filterGroups: [],
		filterUsers: [],
		filterUnassigned: false,
	}),
	beforeMount() {
		this.loadFilters()
	},
	computed: {
		statuses() {
			return this.$store.getters['statuses/readAll']
		},
		groups() {
			return this.$store.getters['groups/readAll']
		},
		users() {
			return this.$store.getters['users/readAll']
		},
	},
	methods: {
		applyFilter() {
			this.filterEmit()
		},
		clearFilter() {
			this.filterFreeSearch = ''
			this.filterName = ''
			this.filterCitizenId = ''
			this.filterPhone = ''
			this.filterEmail = ''
			this.filterDueDateFrom = ''
			this.filterDueDateTo = ''
			if (this.isFilterAvailable('dueDate')) {
				this.filterDueDate = 'dueToday'
			} else {
				this.filterDueDate = ''
			}
			this.filterStatuses = []
			this.filterGroups = []
			this.filterUsers = []
			this.filterUnassigned = false
			this.applyFilter()
		},
		filterEmit() {
			const newFilter = {}

			if (this.filterFreeSearch) {
				newFilter.freeSearch = this.filterFreeSearch
			}
			if (this.filterDueDate) {
				newFilter.dueDate = this.filterDueDate
			}
			if (this.filterStatuses.length > 0) {
				newFilter.statuses = this.filterStatuses
			}
			if (this.filterGroups.length > 0) {
				newFilter.groups = this.filterGroups
			}
			if (this.filterUsers.length > 0) {
				newFilter.users = this.filterUsers
			}
			if (this.filterUnassigned) {
				newFilter.unassigned = this.filterUnassigned
			}
			if (this.filterName) {
				newFilter.stakeholderName = this.filterName
			}
			if (this.filterCitizenId) {
				newFilter.stakeholderCitizenId = this.filterCitizenId
			}
			if (this.filterPhone) {
				newFilter.stakeholderPhone = this.filterPhone
			}
			if (this.filterEmail) {
				newFilter.stakeholderEmail = this.filterEmail
			}
			if (this.filterDueDateFrom) {
				newFilter.dueDateFrom = this.filterDueDateFrom
			}
			if (this.filterDueDateTo) {
				newFilter.dueDateTo = this.filterDueDateTo
			}
			this.$emit('filter-changed', newFilter)
		},
		isFilterAvailable(filterName) {
			let filter = this.filterOptions.find((element)=> element[0] == filterName);
			if (filter){
				return filter[1]
			}
			return false
		},
		loadFilters() {
			// Load the filter configuration
			return axios({
				method: 'GET',
				url: generateUrl('apps/vtramit/web/2.0/configuration'),
			}).then((result) => {
				if (result.data && result.data.result && result.data.result === 'OK') {
					// Assign new results
					this.filterOptions = result.data.configuration.filterOption
					return true
				}
				return false
			}).catch((error) => {
				// console.error('ERROR readAll', error)
				this.manageException(error, t('vtramit', 'Error loading available filters'))
				throw error
			})
		},
	},
}
</script>
