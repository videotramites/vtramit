<!--
/**
 * @copyright Copyright (c) 2020-2021 Ajuntament de Barcelona
 *
 * @author Daniel Tamajon <daniel@floss.cat>
 * @author Kenneth Peiruza <kenneth@floss.cat>
 * @author Letizia Benítez <letizia@floss.cat>
 * @author Marta González <gonzalez.marta@gmail.com>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan Reyné <ivanreyne@gmail.com>
 *
 * @license GNU AGPL version 3 or any later version
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
	<Content :class="{'icon-loading': loading}" app-name="appVTramit">
		<AppNavigation v-show="navShown" :class="{ 'hidden': !navShown }">
			<AppNavigationItem :title="t('vtramit', 'Appointments list')" icon="icon-home" />
			<AppNavigationSettings v-show="navSettingsShown">
				<div>
					<Multiselect v-model="groupLimit"
						:class="{'icon-loading-small': groupLimitDisabled}"
						open-direction="bottom"
						:options="groups"
						:multiple="true"
						:disabled="groupLimitDisabled"
						:placeholder="t('vtramit', 'Group assignation')"
						label="displayname"
						track-by="id"
						@input="updateConfig" />
					<p>{{ t('vtramit', 'Select groups that have permission to assign appointments to other users. Every user in the groups selected will be capable to do it.') }}</p>
				</div>
			</AppNavigationSettings>
		</AppNavigation>
		<AppContent>
			<!--Controles-->
			<div class="controls">
				<div id="app-navigation-toggle-custom" class="icon-menu" @click="toggleNav" />
				<div class="flex-spacer" />
				<div class="board-actions">
					<Actions>
						<ActionButton icon="icon-add" data-cy="btn-new-appointment" @click.stop="showCreate">
							{{ t('vtramit', 'Add new appointment') }}
						</ActionButton>
					</Actions>

					<Popover>
						<Actions slot="trigger" :title="t('vtramit', 'Apply filter')">
							<ActionButton v-if="isFilterActive" icon="icon-filter" data-cy="btn-filter-active" />
							<ActionButton v-else icon="icon-filter" data-cy="btn-filter" />
						</Actions>

						<template>
							<div class="filter">
								<h3>{{ t('vtramit', 'Filter by id') }}</h3>
								<label :for="'filter_state_id'"><span class="label">{{ t('vtramit', 'Appointment ID') }}</span></label>
								<input
									:id="'filter_id'"
									v-model="filter.id"
									type="text"
									@change="setFilter">

								<h3>{{ t('vtramit', 'Filter by state') }}</h3>
								<div v-for="state in states" :key="'filter_state_'+state.value" class="filter--item">
									<input
										:id="'filter_state_'+state.value"
										v-model="filter.states"
										type="checkbox"
										class="checkbox"
										:value="state.value"
										@change="setFilter">
									<label :for="'filter_state_'+state.value"><span class="label">{{ state.name }}</span></label>
								</div>

								<h3>{{ t('vtramit', 'Filter by department') }}</h3>
								<div v-for="item in filterDepartmentsSorted" :key="'filter_department_'+item.key" class="filter--item">
									<input
										:id="'filter_department_'+item.key"
										v-model="filter.departments"
										type="checkbox"
										class="checkbox"
										:value="item.key"
										@click="setFilterDepartment">
									<label :for="'filter_department_'+item.key">{{ item.value }}</label>
								</div>

								<h3>{{ t('vtramit', 'Filter by assigned user') }}</h3>
								<div class="filter--item">
									<input
										id="unassigned"
										v-model="filter.unassigned"
										type="checkbox"
										class="checkbox"
										value="unassigned"
										@click="setFilterUnassigned">
									<label for="unassigned">{{ t('vtramit', 'Unassigned') }}</label>
								</div>
								<div v-for="item in filterUsersSorted" :key="'filter_user_'+item.key" class="filter--item">
									<input
										:id="'filter_user_'+item.key"
										v-model="filter.users"
										type="checkbox"
										class="checkbox"
										:value="item.key"
										@click="setFilterUser">
									<label :for="'filter_user_'+item.key">{{ item.value }}</label>
								</div>

								<h3>{{ t('vtramit', 'Filter by due date') }}</h3>

								<div class="filter--item">
									<input
										id="overdue"
										v-model="filter.due"
										type="radio"
										class="radio"
										value="overdue"
										@click="setFilterDate">
									<label for="overdue">{{ t('vtramit', 'Overdue') }}</label>
								</div>

								<div class="filter--item">
									<input
										id="dueToday"
										v-model="filter.due"
										type="radio"
										class="radio"
										value="dueToday"
										@click="setFilterDate">
									<label for="dueToday">{{ t('vtramit', 'Today') }}</label>
								</div>

								<div class="filter--item">
									<input
										id="dueWeek"
										v-model="filter.due"
										type="radio"
										class="radio"
										value="dueWeek"
										@click="setFilterDate">
									<label for="dueWeek">{{ t('vtramit', 'Next 7 days') }}</label>
								</div>

								<div class="filter--item">
									<input
										id="dueMonth"
										v-model="filter.due"
										type="radio"
										class="radio"
										value="dueMonth"
										@click="setFilterDate">
									<label for="dueMonth">{{ t('vtramit', 'Next 30 days') }}</label>
								</div>
								<Button :disabled="!isFilterActive" @click="clearFilter">
									{{ t('vtramit', 'Clear filter') }}
								</Button>
							</div>
						</template>
					</Popover>
				</div>
			</div>

			<div v-if="appointments.length > 0"
				style="position: absolute; left: 0; right: 0; overflow:scroll;">
				<table data-cy="table-appointments">
					<tr>
						<th>{{ t('vtramit', 'Appointment ID') }}</th>
						<th>{{ t('vtramit', 'Department') }}</th>
						<th>{{ t('vtramit', 'Assigned to') }}</th>
						<th>{{ t('vtramit', 'Status') }}</th>
						<th>{{ t('vtramit', 'Topic') }}</th>
						<th>{{ t('vtramit', 'Date') }}</th>
						<th>{{ t('vtramit', 'Time') }}</th>
						<th>{{ t('vtramit', 'Name') }}</th>
						<th>{{ t('vtramit', 'Citizen ID') }}</th>
						<th>{{ t('vtramit', 'Phone') }}</th>
						<th>{{ t('vtramit', 'Actions') }}</th>
					</tr>
					<tr
						v-for="appointment in appointments"
						:key="appointment.id">
						<td
							@click="showEdit(appointment.id)">
							{{ appointment.externalId }}
						</td>
						<td
							@click="showEdit(appointment.id)">
							{{ appointment.department }}
						</td>
						<td
							:title="getNameOfAssignedUser(appointment)"
							@click="showEdit(appointment.id)">
							{{ stripString(getNameOfAssignedUser(appointment), 32) }}
						</td>
						<td
							@click="showEdit(appointment.id)">
							{{ appointment.stateDesc }}
						</td>
						<td
							:title="appointment.topic"
							@click="showEdit(appointment.id)">
							{{ stripString(appointment.topic, 32) }}
						</td>
						<td
							@click="showEdit(appointment.id)">
							{{ appointment.date | formatDate }}
						</td>
						<td
							@click="showEdit(appointment.id)">
							{{ appointment.date | formatTime }}
						</td>
						<td
							:title="appointment.name"
							@click="showEdit(appointment.id)">
							{{ stripString(appointment.name, 32) }}
						</td>
						<td
							@click="showEdit(appointment.id)">
							{{ appointment.citizenId }}
						</td>
						<td
							@click="showEdit(appointment.id)">
							<a :href="'ciscotel:' + appointment.phone">{{ appointment.phone }}</a>
						</td>
						<td>
							<Actions>
								<ActionLink v-if="appointment.urlDownloads"
									icon="icon-folder"
									:href="appointment.urlDownloads"
									:close-after-click="true"
									target="_blank">
									{{ t('vtramit', 'Downloads folder') }}
								</ActionLink>
							</Actions>
							<Actions>
								<ActionLink v-if="appointment.urlUploads"
									icon="icon-files-dark"
									:href="appointment.urlUploads"
									:close-after-click="true"
									target="_blank">
									{{ t('vtramit', 'Uploads folder') }}
								</ActionLink>
							</Actions>
							<Actions :class="{video_waiting: (appointment.isWaitingForModerator === true)}">
								<ActionLink v-if="appointment.allowedForConference"
									icon="icon-video"
									:href="appointment.jitsiRoomInformador"
									target="_blank"
									:close-after-click="true"
									@click="clickVideoconferenceLink(appointment)">
									{{ appointment.isWaitingForModerator ? t('vtramit', 'User is waiting to be attended') : t('vtramit', 'Videconference') }}
								</ActionLink>
							</Actions>
							<Actions>
								<ActionLink v-if="appointment.allowSendEmail"
									icon="icon-mail"
									href="#"
									:close-after-click="true"
									@click="clickSendMailLink(appointment.id)">
									{{ t('vtramit', 'Re-send mail to citizen') }}
								</ActionLink>
							</Actions>
						</td>
					</tr>
				</table>
			</div>
			<div v-else id="emptycontent">
				<div class="icon-file" />
				<h2>{{ t('vtramit', 'Create an appointment to get started') }}</h2>
			</div>
		</AppContent>
		<AppSidebar v-show="show"
			:title="t('vtramit', 'Appointment info')"
			:starred.sync="starred"
			data-cy="sidebar-right"
			@close="cancelNewAppointment(currentAppointment)">
			<template #primary-actions />
			<template #secondary-actions>
				<ActionButton icon="icon-delete" @click="deleteAppointment(currentAppointment)">
					Delete
				</ActionButton>
			</template>
			<AppSidebarTab id="details" :name="t('vtramit', 'Appointment')" icon="icon-calendar">
				<div v-if="currentAppointment">
					<label>{{ t('vtramit', 'Appointment ID') }} *
						<input ref="externalId"
							v-model="currentAppointment.externalId"
							type="text"
							required="true"
							:disabled="updating || update"
							:placeholder="t('vtramit', 'Appointment identifier')"
							data-cy="input-appointment-id">
					</label>
					<label>{{ t('vtramit', 'Citizen ID') }} *
						<input ref="citizenId"
							v-model="currentAppointment.citizenId"
							type="text"
							required="true"
							:disabled="updating || update"
							:placeholder="t('vtramit', 'Citizen Card ID')"
							data-cy="input-citizen-id">
					</label>
					<label>{{ t('vtramit', 'Department') }} *
						<select ref="department"
							v-model="currentAppointment.department"
							required="true"
							:value="currentAppointment.department"
							:disabled="updating"
							data-cy="select-department"
							@change="setUsersByDepartment">
							<option v-for="item in departmentsSorted" :key="item.key" :value="item.key">
								{{ item.value }}
							</option>
						</select>
					</label>
					<label>{{ t('vtramit', 'Assigned to') }}
						<select ref="assignedTo"
							v-model="currentAppointment.assignedTo"
							:value="currentAppointment.assignedTo"
							:disabled="updating"
							:hidden="!userAssignation"
							data-cy="select-assigned-to">
							<option value="0">{{ t('vtramit', 'Select a user...') }}</option>
							<option v-for="(item) in authorizedUsers" :key="item.key" :value="item.key">
								{{ item.value }}
							</option>
						</select>
					</label>
					<label>{{ t('vtramit', 'Topic') }}
						<input ref="topic"
							v-model="currentAppointment.topic"
							type="text"
							:disabled="updating"
							:placeholder="t('vtramit', 'Topic')"
							data-cy="input-topic">
					</label>
					<label>{{ t('vtramit', 'Appointment date and time') }} *
						<span>
							<DatetimePicker
								ref="date"
								v-model="dateTime"
								required="true"
								format="YYYY-MM-DD H:mm"
								type="datetime"
								:default-value="new Date()"
								data-cy="datepicker-date" />
						</span>
					</label>
					<label>{{ t('vtramit', 'Name') }} *
						<input ref="name"
							v-model="currentAppointment.name"
							required="true"
							type="text"
							:disabled="updating"
							:placeholder="t('vtramit', 'Name')"
							data-cy="input-name">
					</label>
					<label>{{ t('vtramit', 'Phone') }}
						<input ref="phone"
							v-model="currentAppointment.phone"
							type="text"
							:disabled="updating"
							:placeholder="t('vtramit', 'Phone')"
							data-cy="input-phone">
					</label>
					<label>{{ t('vtramit', 'Email') }} *
						<input ref="email"
							v-model="currentAppointment.email"
							required="true"
							type="text"
							:disabled="updating"
							:placeholder="t('vtramit', 'Email')"
							data-cy="input-email"
							@change="isEmailValid">
					</label>
					<label>{{ t('vtramit', 'Comments') }}
						<textarea ref="comments"
							v-model="currentAppointment.comments"
							:disabled="updating"
							:placeholder="t('vtramit', 'Comments')"
							data-cy="input-comments" />
					</label>
					<input type="button"
						class="primary"
						:value="t('vtramit', 'Save')"
						:disabled="updating || !savePossible"
						data-cy="btn-appointment-new"
						@click="saveAppointment">
					<p class="error_message" :hidden="isEmailValid">
						Email field is not valid
					</p>
					<input type="button"
						class="secondary"
						:value="t('vtramit', 'Change state to Pendant')"
						:hidden="updating || !currentAppointment.allowStatePendant"
						@click="stateChange(constants.STATE_PENDANT, currentAppointment.id)">
					<input type="button"
						class="secondary"
						:value="t('vtramit', 'Change state to Finished')"
						:hidden="updating || !currentAppointment.allowStateFinished"
						@click="stateChange(constants.STATE_FINISHED, currentAppointment.id)">
					<input type="button"
						class="secondary"
						:value="t('vtramit', 'Change state to Completed')"
						:hidden="updating || !currentAppointment.allowStateCompleted"
						@click="stateChange(constants.STATE_COMPLETED, currentAppointment.id)">
					<input type="button"
						class="secondary"
						:value="t('vtramit', 'Change state to Cancelled')"
						:hidden="updating || !currentAppointment.allowStateCancelled"
						@click="stateChange(constants.STATE_CANCELLED, currentAppointment.id)">
				</div>
			</AppSidebarTab>
			<!--
			<AppSidebarTab id="activity" name="Activity" icon="icon-activity">
				this is the activity tab
			</AppSidebarTab>
			<AppSidebarTab id="comments" name="Comments" icon="icon-comment">
				this is the comments tab
			</AppSidebarTab>
			<AppSidebarTab id="sharing" name="Sharing" icon="icon-shared">
				this is the sharing tab
			</AppSidebarTab>
			<AppSidebarTab id="versions" name="Versions" icon="icon-history">
				this is the versions tab
			</AppSidebarTab>
			-->
		</AppSidebar>
	</Content>
</template>

<script>
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppSidebar from '@nextcloud/vue/dist/Components/AppSidebar'
import AppSidebarTab from '@nextcloud/vue/dist/Components/AppSidebarTab'
import { Actions, ActionButton, ActionLink, Popover, AppNavigationSettings, Multiselect } from '@nextcloud/vue'
import DatetimePicker from '@nextcloud/vue/dist/Components/DatetimePicker'
import Vue from 'vue'
import Router from 'vue-router'

import axios from '@nextcloud/axios'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'

Vue.use(Router)

export default {
	name: 'App',
	components: {
		Content,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppSidebar,
		AppSidebarTab,
		Actions,
		ActionButton,
		ActionLink,
		DatetimePicker,
		Popover,
		AppNavigationSettings,
		Multiselect,
	},
	props: {
		board: {
			type: Object,
			default: () => { return {} },
		},
	},
	data() {
		return {
			navShown: false,
			navSettingsShown: false,
			appointments: [],
			currentAppointmentId: null,
			currentAppointmentCitizenId: null,
			currentAppointmentDepartment: null,
			updating: false,
			update: false,
			loading: false,
			show: false,
			starred: false,
			stateDatetime: null,
			dateTime: null,
			filter: { id: '', due: 'dueToday', states: [], departments: [], users: [], unassigned: false },
			showFilters: false,
			tags: [t('vtramit', 'Initializing'), t('vtramit', 'Created'), t('vtramit', 'Pendant'), t('vtramit', 'On course'), t('vtramit', 'Finished'), t('vtramit', 'Completed'), t('vtramit', 'Cancelled')],
			states: [ { 'value': 0, 'name': t('vtramit', 'Initializing') }, { 'value': 1, 'name': t('vtramit', 'Created') }, { 'value': 2, 'name': t('vtramit', 'Pendant') }, { 'value': 3, 'name': t('vtramit', 'On course') }, { 'value': 4, 'name': t('vtramit', 'Finished') }, { 'value': 5, 'name': t('vtramit', 'Completed') }, { 'value': 6, 'name': t('vtramit', 'Cancelled') } ],
			constants: { 'STATE_INITIALIZING': 0, 'STATE_CREATED': 1, 'STATE_PENDANT': 2, 'STATE_ON_COURSE': 3, 'STATE_FINISHED': 4, 'STATE_COMPLETED': 5, 'STATE_CANCELLED': 6, 'STATE_DUPLICATED': 7, 'STATE_EXPIRED': 8 },
			due: [t('vtramit', 'Avui'), t('vtramit', '48h'), t('vtramit', 'This week')],
			departments: [],
			userContext: [],
			userAssignation: true,
			authorizedUsers: [],
			groups: [],
			groupLimit: [],
			groupLimitDisabled: true,
			timer: '',
			reg: /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@(([[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/,
		}
	},
	computed: {
		routeTo() {
			return {
				name: 'board',
				params: { id: this.board.id },
			}
		},
		limitedAcl() {
			return [...this.board.acl].splice(0, 5)
		},
		otherAcl() {
			return [...this.board.acl].splice(6).map((item) => item.participant.displayname || item.participant).join(', ')
		},
		/**
		 * Return the currently selected appointment object
		 * @returns {Object|null}
		 */
		currentAppointment() {
			if (this.currentAppointmentId === null) {
				return null
			}
			return this.appointments.find((appointment) => appointment.id === this.currentAppointmentId)
		},

		/**
		 * Returns true if an appointment is selected and its citizenId is not empty
		 * @returns {Boolean}
		 */
		savePossible() {
			return (this.currentAppointment && this.currentAppointment.citizenId !== '') && this.isEmailValid()
		},
		isFilterActive() {
			if (this.filter.due !== '') {
				return true
			}
			return false
		},
		isAdmin() {
			return OC.isUserAdmin()
		},
		departmentsSorted() {
			const tempDepartments = []
			if (this.userContext && this.userContext.departments) {
				// The list of departments is actually an object, we need to convert
				// it to an array to be able to sort it.
				const departments = this.userContext.departments
				const tempDepartmentsKeys = Object.keys(departments)
				tempDepartmentsKeys.forEach(key => tempDepartments.push({
					key: key,
					value: key,
				}))
				// Now we create a locale comparison object.
				const compareFunc = new Intl.Collator(OC.getLanguage(), { sensitivity: 'base' }).compare
				tempDepartments.sort((a, b) => { return compareFunc(a.value, b.value) })
			}
			return tempDepartments
		},
		filterDepartmentsSorted() {
			const tempDepartments = []
			if (this.userContext && this.userContext.filterDepartments) {
				// The list of departments is actually an object, we need to convert it to an
				// array to be able to sort it.
				const departments = this.userContext.filterDepartments
				const tempDepartmentsKeys = Object.keys(departments)
				tempDepartmentsKeys.forEach(key => tempDepartments.push({
					key: key,
					value: departments[key],
				}))
				// Now we create a locale comparison object.
				const compareFunc = new Intl.Collator(OC.getLanguage(), { sensitivity: 'base' }).compare
				tempDepartments.sort((a, b) => { return compareFunc(a.value, b.value) })
			}
			return tempDepartments
		},
		filterUsersSorted() {
			const tempUsers = []
			if (this.userContext && this.userContext.filterUsers) {
				// The list of users is actually an object, we need to convert it to an
				// array to be able to sort it.
				const users = this.userContext.filterUsers
				const tempUsersKeys = Object.keys(users)
				tempUsersKeys.forEach(key => tempUsers.push({
					key: key,
					value: users[key],
				}))
				// Now we create a locale comparison object.
				const compareFunc = new Intl.Collator(OC.getLanguage(), { sensitivity: 'base' }).compare
				tempUsers.sort((a, b) => { return compareFunc(a.value, b.value) })
			}
			return tempUsers
		},
	},
	beforeMount() {
		if (this.isAdmin) {
			axios.get(generateUrl('apps/vtramit/config')).then((response) => {
				this.groupLimit = response.data.groupLimit
				this.groupLimitDisabled = false
			}, (error) => {
				console.error('Error while loading groupLimit', error.response)
			})
			axios.get(generateOcsUrl('cloud', 2) + 'groups').then((response) => {
				this.groups = response.data.ocs.data.groups.reduce((obj, item) => {
					obj.push({
						id: item,
						displayname: item,
					})
					return obj
				}, [])
			}, (error) => {
				console.error('Error while loading group list', error.response)
			})
		}
	},
	/**
	 * Fetch list of appointments when the component is loaded
	 */
	async mounted() {
		try {
			const userContextResponse = await axios.get(OC.generateUrl('/apps/vtramit/getusercontext'))
			this.userContext = userContextResponse.data

			const response = await axios.get(OC.generateUrl('/apps/vtramit/appointments'))
			this.appointments = response.data

			this.navSettingsShown = this.userContext.isAdmin

			// console.debug(JSON.stringify(this.userContext))

			if (this.$route.query.videoconferencefinished) {
				const response = await axios.post(OC.generateUrl(`/apps/vtramit/videoconferencefinished/${this.$route.query.videoconferencefinished}`))
				if (response.data.appointment !== '') {
					const index = this.appointments.findIndex((match) => match.id === response.data.appointment.id)
					this.$set(this.appointments, index, response.data.appointment)
				}

				if (response.data.redirect !== false) {
					window.open(response.data.redirect)
				}

				this.manageResponse(response, 'Appointment updated', 'Could not update the appointment')
			}
			// TODO: Add appointments filter
			// const response = await axios.get(OC.generateUrl('/apps/vtramit/filterappointments'), params)
			// this.appointments = response.data
		} catch (e) {
			console.error(e)
			OCP.Toast.error(t('vtramit', 'Could not fetch appointments'))
		}
		this.timer = setInterval(this.refreshGrid, 5000)
		this.loading = false
		this.show = false
	},
	methods: {
		toggleNav() {
			this.navShown = !this.navShown
		},
		showCreate() {
			this.show = true
			this.update = false
			this.newAppointment()
		},
		setFilterDate(e) {
			if (this.filter.due === e.target.value) {
				this.filter.due = ''
			} else {
				this.filter.due = e.target.value
			}
			this.refreshGrid()
		},
		setFilterUser(e) {
			if (e.target.checked) {
				this.filter.unassigned = false
				if (!this.filter.users.includes(e.target.value)) {
					this.filter.users.push(e.target.value)
				}
			} else {
				if (this.filter.users.includes(e.target.value)) {
					const index = this.filter.users.indexOf(e.target.value)
					if (index > -1) {
						this.filter.users.splice(index, 1)
					}
				}
			}
			this.refreshGrid()
		},
		setFilterDepartment(e) {
			if (e.target.checked) {
				if (!this.filter.departments.includes(e.target.value)) {
					this.filter.departments.push(e.target.value)
				}
			} else {
				if (this.filter.departments.includes(e.target.value)) {
					const index = this.filter.departments.indexOf(e.target.value)
					if (index > -1) {
						this.filter.departments.splice(index, 1)
					}
				}
			}
			this.refreshGrid()
		},
		setFilterUnassigned(e) {
			if (e.target.checked) {
				this.filter.unassigned = true
				this.filter.users = []
			} else {
				this.filter.unassigned = false
			}
			this.refreshGrid()
		},
		async setFilter() {
			this.refreshGrid()
		},
		clearFilter() {
			this.filter = { id: '', due: 'dueToday', states: [], departments: [], users: [], unassigned: false }
			this.refreshGrid()
		},
		showFilter() {
			if (this.showFilters) {
				this.showFilters = false
			} else {
				this.showFilters = true
			}
		},
		showEdit(appointmentId) {
			if (appointmentId === this.currentAppointmentId) {
				this.currentAppointmentId = null
				this.show = false
			} else {
				this.currentAppointmentId = appointmentId
				this.currentAppointmentCitizenId = this.currentAppointment.citizenId
				this.currentAppointmentDepartment = this.currentAppointment.department
				this.setUsersByDepartment()
				this.update = true
				this.show = true
				this.dateTime = new Date(this.currentAppointment.date * 1000)
			}
		},
		getNameOfAssignedUser(appointment) {
			if (typeof this.userContext.users === 'object' && this.userContext.users !== null) {
				return this.userContext.users[appointment.assignedTo]
			}
			return appointment.assignedTo
		},
		hideEdit() {
			this.currentAppointmentId = null
			this.show = false
		},
		/**
		 * Create a new appointment and focus the appointment content field automatically
		 * @param {Object} appointment Appointment object
		 */
		openAppointment(appointment) {
			if (this.updating) {
				return
			}
			this.currentAppointmentId = appointment.id
			this.$nextTick(() => {
				this.$refs.citizenId.focus()
			})
		},
		/**
		 * Action tiggered when clicking the save button
		 * create a new appointment or save
		 */
		saveAppointment() {
			if (this.currentAppointmentId === -1) {
				this.currentAppointment.date = (this.$refs.date.value === '' || this.$refs.date.value === null) ? 0 : (Date.parse(this.$refs.date.value) / 1000)
				this.createAppointment(this.currentAppointment)
			} else {
				this.currentAppointment.date = (this.$refs.date.value === '' || this.$refs.date.value === null) ? 0 : (Date.parse(this.$refs.date.value) / 1000)
				this.updateAppointment(this.currentAppointment)
			}
		},
		isEmailValid() {
			return true // TODO: review usage of this.reg.test(this.currentAppointment.email)
		},
		async stateChange(state, appointmentId) {
			try {
				let endpoint = ''
				switch (state) {
				case 2:
					endpoint = 'updatestatetopendant'
					break
				case 3:
					endpoint = 'updatestatetooncourse'
					break
				case 4:
					endpoint = 'updatestatetofinished'
					break
				case 5:
					endpoint = 'updatestatetocompleted'
					break
				case 6:
					endpoint = 'updatestatetocancelled'
					break
				}
				const response = await axios.post(OC.generateUrl(`/apps/vtramit/${endpoint}/${appointmentId}`))
				this.manageResponse(response, 'State changed', 'Could not change state')

				if (Object.keys(response.data.appointment).length !== 0) {
					const index = this.appointments.findIndex((match) => match.id === response.data.appointment.id)
					this.$set(this.appointments, index, response.data.appointment)
				}

			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('vtramit', 'Could not change state'))
			}
		},
		async refreshGrid() {
			if (this.update === false && this.show === false) {
				try {
					const response = await axios.post(OC.generateUrl('/apps/vtramit/getappointments'), this.filter)
					this.appointments = response.data
				} catch (e) {
					console.error(e)
					OCP.Toast.error(t('vtramit', 'Could not fetch appointments'))
				}
			}
		},
		/**
		 * Create a new appointment and focus the appointment content field automatically
		 * The appointment is not yet saved, therefore an id of -1 is used until it
		 * has been persisted in the backend
		 */
		newAppointment() {
			if (this.currentAppointmentId !== -1) {
				this.currentAppointmentId = -1
				this.appointments.push({
					id: -1,
					externalId: '',
					citizenId: '',
					department: '',
					userId: '',
					comments: '',
					date: '',
					name: '',
					phone: '',
					email: '',
					topic: '',
					assignedTo: '',
					roomCode: '',
					stateDate: '',
					stateDesc: t('vtramit', 'Initializing'),
				})
				this.$nextTick(() => {
					this.$refs.externalId.focus()
				})
			}
		},
		/**
		 * Abort creating a new appointment
		 */
		cancelNewAppointment() {
			this.show = false
			if (this.currentAppointmentId === -1) {
				this.appointments.splice(this.appointments.findIndex((appointment) => appointment.id === -1), 1)
				this.currentAppointmentId = null
			}
		},
		/**
		 * Create a new appointment by sending the information to the server
		 * @param {Object} appointment Appointment object
		 */
		async createAppointment(appointment) {
			this.updating = true
			try {
				const response = await axios.post(OC.generateUrl(`/apps/vtramit/appointments`), appointment)
				const go = this.manageResponse(response, 'Appointment created', 'Could not create the appointment')

				if (go) {
					const index = this.appointments.findIndex((match) => match.id === this.currentAppointmentId)
					this.$set(this.appointments, index, response.data.appointment)
					this.currentAppointmentId = response.data.appointment.id

					this.show = false
					this.refreshGrid()
				}
			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('vtramit', 'Could not create the appointment'))
			}
			this.updating = false
		},
		/**
		 * Update an existing appointment on the server
		 * @param {Object} appointment Appointment object
		 */
		async updateAppointment(appointment) {
			this.updating = true
			try {
				const response = await axios.put(OC.generateUrl(`/apps/vtramit/appointments/${appointment.id}`), appointment)

				this.manageResponse(response, 'Appointment updated', 'Could not update the appointment')
				this.show = false
			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('vtramit', 'Could not update the appointment'))
			}
			this.updating = false
		},
		/**
		 * Delete an appointment, remove it from the frontend and show a hint
		 * @param {Object} appointment Appointment object
		 */
		async deleteAppointment(appointment) {
			try {
				const response = await axios.delete(OC.generateUrl(`/apps/vtramit/appointments/${appointment.id}`))
				this.manageResponse(response, 'Appointment deleted', 'Could not delete the appointment')

				// Remove appointment from appointments collection
				this.appointments.splice(this.appointments.indexOf(appointment), 1)
				if (this.currentAppointmentId === appointment.id) {
					this.currentAppointmentId = null
				}

				this.show = false
			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('vtramit', 'Could not delete the appointment'))
			}
		},
		clickVideoconferenceLink(appointment) {
			if (appointment.state === this.constants.STATE_PENDANT.toString()) {
				this.stateChange(this.constants.STATE_ON_COURSE, appointment.id)
			}
		},
		async clickSendMailLink(appointmentId) {
			try {
				const response = await axios.post(OC.generateUrl(`/apps/vtramit/sendmailbyappointmentid/${appointmentId}`))
				this.manageResponse(response, 'Email in queue to be sent', 'Could not send the e-mail')
			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('vtramit', 'Could not send the e-mail'))
			}
		},
		manageResponse(response, defaultSuccessMessage, defaultErrorMessage) {
			if (response.data.message && response.data.result === 'KO') {
				if (Array.isArray(response.data.message)) {
					response.data.message.forEach(message => OCP.Toast.error(message))
				} else {
					OCP.Toast.error(response.data.message)
				}
				return false
			} else if (response.data.message) {
				if (Array.isArray(response.data.message)) {
					response.data.message.forEach(message => OCP.Toast.success(message))
				} else {
					OCP.Toast.success(response.data.message)
				}
				return true
			} else if (!response.data.result || response.data.result === 'OK') {
				if (Array.isArray(response.data.message)) {
					response.data.message.forEach(message => OCP.Toast.success(message))
				} else {
					OCP.Toast.success(t('vtramit', defaultSuccessMessage))
				}
				return true
			} else {
				if (Array.isArray(response.data.message)) {
					response.data.message.forEach(message => OCP.Toast.error(message))
				} else {
					OCP.Toast.error(t('vtramit', defaultErrorMessage))
				}
				return false
			}
		},
		setUsersByDepartment() {
			const tempUsers = []
			if (this.userContext && this.userContext.departments) {
				// The list of users is actually an object, we need to convert it to an
				// array to be able to sort it.
				const users = this.userContext.departments[this.currentAppointment.department]
				const tempUsersKeys = Object.keys(users)
				tempUsersKeys.forEach(key => tempUsers.push({
					key: key,
					value: users[key],
				}))
				// Now we create a locale comparison object.
				const compareFunc = new Intl.Collator(OC.getLanguage(), { sensitivity: 'base' }).compare
				tempUsers.sort((a, b) => { return compareFunc(a.value, b.value) })
			}
			this.authorizedUsers = tempUsers
		},
		updateConfig() {
			this.groupLimitDisabled = true
			axios.post(generateUrl('apps/vtramit/config/groupLimit'), {
				value: this.groupLimit,
			}).then(() => {
				this.groupLimitDisabled = false
			}, (error) => {
				console.error('Error while saving groupLimit', error.response)
			})
		},
		stripString(value, length) {
			if (!value) {
				return ''
			}
			value = value.toString()

			if (value.length <= length) {
				return value
			}

			return value.substr(0, length) + '...'
		},
	},
}

</script>
<style lang="scss">
	input[type='text'] {
		width: 100%;
	}

	.mx-datepicker {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}

	select {
		width: 100%;
	}

	.board-list {

		.board-list-row {
			align-items: center;
			border-bottom: 1px solid #ededed;
			display: flex;
		}

		.board-list-row:not(.board-list-header-row):hover {
			transition: background-color 0.3s ease;
			background-color: var(--color-background-dark);
		}

		.board-list-header-row {
			color: var(--color-text-lighter);
		}

		.board-list-bullet-cell,
		.board-list-avatars-cell {
			padding: 6px 15px;
		}

		.board-list-avatars-cell {
			flex: 0 0 50px;
		}

		.board-list-avatar,
		.board-list-bullet {
			height: 32px;
			width: 32px;
		}

		.board-list-title-cell {
			flex: 1 0 auto;
			padding: 15px;
		}

		.board-list-actions-cell {
			// placeholder
			flex: 0 0 50px;
		}
	}
</style>
<style lang="scss" scoped>
	.flex-spacer {
		flex-grow: 1;
	}

	.controls {
		display: flex;

		.board-title {
			display: flex;
			align-items: center;

			h2 {
				margin: 0;
				margin-right: 10px;
			}

			.board-bullet {
				display: inline-block;
				width: 20px;
				height: 20px;
				border: none;
				border-radius: 50%;
				background-color: #aaa;
				margin: 12px;
				margin-left: -4px;
			}
		}

		#stack-add form {
			display: flex;
		}
	}

	#app-navigation-toggle-custom {
		position: static;
		width: 44px;
		height: 44px;
		cursor: pointer;
		opacity: 1;
		display: inline-block !important;
	}

	.board-actions {
		order: 100;
		display: flex;
		justify-content: flex-end;
	}

	.board-action-buttons {
		display: flex;
		button {
			border: 0;
			width: 44px;
			margin: 0 0 0 -1px;
			background-color: transparent;
		}
	}

	.filter--item {
		input + label {
			display: block;
			padding: 6px 0;
			vertical-align: middle;
			.avatardiv {
				vertical-align: middle;
				margin-bottom: 2px;
				margin-right: 3px;
			}
			.label {
				padding: 5px;
				border-radius: 3px;
			}
		}
	}

	.filter {
		width: 250px;
		max-height: 80vh;
		overflow: auto;
	}

	.filter h3 {
		margin-top: 0px;
		margin-bottom: 5px;
	}

	#filter_id {
		width: 90% !important;
		margin: 3px 0;
	}
</style>
<style lang="scss">
	.tooltip-inner.popover-inner {
		text-align: left;
	}

	table {
		border-collapse: collapse;
		width: 100%;

		tr {
			border-bottom: 1px solid #ededed;
		}

		tr:hover {
			transition: background-color 0.3s ease;
			background-color: var(--color-background-dark);
		}

		th, td {
			text-align: left;
			padding: 8px;
			padding: 15px;
		}

	}

</style>
<style scoped lang="scss">
	.app-navigation-entry {
		position: absolute;
	}

	#app-settings-content {
		p {
			margin-top: 20px;
			margin-bottom: 20px;
			color: var(--color-text-light);
		}
	}

	.video_waiting {
		background-color: #a4fcb4;
		border-radius: 50%;
	}
</style>
