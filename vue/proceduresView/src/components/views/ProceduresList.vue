<template>
	<div tyle="height: 100%;">
		<!-- TODO: the controls bar should be a separate component -->
		<div class="controls">
			<!-- <div id="app-navigation-toggle-custom" class="icon-menu" @click="toggleNavigation" /> -->
			<div class="flex-spacer" />
			<div class="board-actions">
				<Actions data-test="new">
					<ActionButton icon="icon-add" @click.stop="clickNew">
						{{ t('vtramit', 'Add new appointment') }}
					</ActionButton>
				</Actions>
				<Actions slot="trigger" :title="t('vtramit', 'Apply filter')" data-test="filter">
					<ActionButton v-if="isFilterActive"
						icon="icon-filter"
						data-cy="btn-filter-active"
						@click="toggleFiltersPanel" />
					<ActionButton v-else
						icon="icon-filter"
						data-cy="btn-filter"
						@click="toggleFiltersPanel" />
				</Actions>
				<Actions data-test="download">
					<ActionButton icon="icon-download" :disabled="downloadRequested" @click="downloadCsv">
						{{ t('vtramit', 'Download CSV') }}
					</ActionButton>
				</Actions>
			</div>
		</div>

		<div v-show="showFiltersPanel" id="filter-panel" ref="filterPanel">
			<ProceduresListFilterVue @filter-changed="onFilterChanged" />
		</div>

		<div v-if="proceduresTable.length > 0" :class="{'procedures-table': true}" :style="getFilterOpenClass(getFilterPanelHeight)">
			<table>
				<tr>
					<th>
						<a id="colAppointmentId"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Appointment ID') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						<a id="colDepartment"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Department') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						<a id="colAssignedTo"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Assigned to') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						<a id="colStatus"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Status') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						<a id="colTopic"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Topic') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						<a id="colDate"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Date') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						<a id="colName"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Name') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						<a id="colCitizenId"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Citizen ID') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						<a id="colPhone"
							orderedBy="false"
							class="procedure-list-header"
							@click="clickColumn($event)">
							{{ t('vtramit', 'Phone') }}
							<i class="icon-more" />
						</a>
					</th>
					<th>
						{{ t('vtramit', 'Actions') }}
					</th>
				</tr>
				<tr v-for="procedure,i in proceduresTable"
					:key="procedure.id"
					:data-test="'procedure'+ i">
					<td :data-test="'id'+i" @click="clickRow(procedure)">
						{{ procedure.externalId }}
					</td>
					<td :data-test="'grup'+i"
						:title="getGroupName(procedure.assignedGroupId)"
						@click="clickRow(procedure)">
						{{ stripString(getGroupName(procedure.assignedGroupId), 14) }}
					</td>
					<td :data-test="'user'+i"
						:title="procedure.assignedUserId"
						@click="clickRow(procedure)">
						{{ stripString(getUserName(procedure.assignedUserId), 28) }}
					</td>
					<td @click="clickRow(procedure)">
						{{ procedure.statusValue ? t('vtramit', procedure.statusValue) : '' }}
					</td>
					<td :data-test="'titol'+i"
						:title="procedure.topic"
						@click="clickRow(procedure)">
						{{ stripString(procedure.topic, 28) }}
					</td>
					<td :data-test="'date'+i" @click="clickRow(procedure)">
						{{ procedure.appointmentDate | formatDate }} {{ procedure.appointmentDate | formatTime }}
					</td>
					<td :data-test="'stakeholderName'+i" @click="clickRow(procedure)">
						<template v-for="stakeholder in getStakeholders(procedure.id)">
							<div :key="stakeholder.id" :title="stakeholder.name">
								{{ stripString(stakeholder.name, 28) }}
							</div>
						</template>
					</td>
					<td :data-test="'stakeholderID'+i" @click="clickRow(procedure)">
						<div v-for="stakeholder in getStakeholders(procedure.id)" :key="stakeholder.id">
							{{ stripString(stakeholder.personalId, 28) }}
						</div>
					</td>
					<td :data-test="'stakeholderPhone'+i" @click="clickRow(procedure)">
						<div v-for="stakeholder in getStakeholders(procedure.id)" :key="stakeholder.id">
							<template v-if="phoneLink">
								<a :href="`${phoneLink}:${stakeholder.phone}`">{{ stakeholder.phone }}</a>
							</template>
							<template v-else>
								{{ stakeholder.phone }}
							</template>
						</div>
					</td>
					<td>
						<!-- Folders -->
						<Actions v-if="procedure.staffDownloadsUrl && procedure.staffDownloadsUrl === procedure.staffUploadsUrl">
							<ActionLink  :class="getClassForUploads(procedure.uploadsQty)"
								icon="icon-folder"
								:href="procedure.staffDownloadsUrl"
								:close-after-click="true"
								target="_blank">
								{{ t('vtramit', 'Shared folder for Uploads/Downloads') }}
							</ActionLink>
						</Actions>
						<template v-else>
							<Actions v-if="procedure.staffDownloadsUrl" :data-test="'downloads'+i">
								<ActionLink icon="icon-folder"
									:href="procedure.staffDownloadsUrl"
									:close-after-click="true"
									target="_blank">
									{{ t('vtramit', 'Shared folder for Downloads') }}
								</ActionLink>
							</Actions>
							<Actions v-if="procedure.staffUploadsUrl" :data-test="'uploads'+i">
								<ActionLink :class="getClassForUploads(procedure.uploadsQty)"
									icon="icon-files-dark"
									:href="procedure.staffUploadsUrl"
									:close-after-click="true"
									target="_blank">
									{{ t('vtramit', 'Shared folder for Uploads') }}
								</ActionLink>
							</Actions>
						</template>

						<!-- Signature -->
						<Actions v-if="procedure.staffSignatureUploadsUrl && procedure.staffSignatureUploadsUrl === procedure.staffSignatureDownloadsUrl"
							:data-test="'signature'+i"
							:class="getClassFromSignatureState(procedure.id)">
							<ActionLink icon="icon-category-auth"
								:href="procedure.staffSignatureUploadsUrl"
								:close-after-click="true"
								target="_blank">
								{{ t('vtramit', 'Documents to sign') }}
							</ActionLink>
						</Actions>
						<template v-else>
							<Actions v-if="procedure.staffSignatureUploadsUrl"
								:class="getClassFromSignatureState(procedure.id)">
								<ActionLink icon="icon-category-auth"
									:href="procedure.staffSignatureUploadsUrl"
									:close-after-click="true"
									target="_blank">
									{{ t('vtramit', 'Documents to sign') }}
								</ActionLink>
							</Actions>
							<!--
							<Actions v-if="procedure.staffSignatureDownloadsUrl">
								<ActionLink
									icon="icon-category-auth"
									:href="procedure.staffSignatureDownloadsUrl"
									:close-after-click="true"
									target="_blank">
									{{ t('vtramit', 'Signed documents') }}
								</ActionLink>
							</Actions>
							-->
						</template>

						<!-- Videoconference -->
						<Actions v-if="procedure.jitsiRoomCode" :data-test="'videoconference'+i" :class="{ video_waiting: getIsVideoconferenceActive(procedure.id) }">
							<ActionLink icon="icon-video"
								href="#"
								:close-after-click="true"
								@click="clickVideoconferenceLink(procedure)">
								<!-- TODO: isWaitingForModerator is from the old "tramits" -->
								{{ procedure.isWaitingForModerator ? t('vtramit', 'User is waiting to be attended') : t('vtramit', 'Videconference') }}
							</ActionLink>
						</Actions>
						<!-- <Actions>
							<ActionLink v-if="!procedure.requestSignatureUrl && procedure.allowSendEmail"
								icon="icon-mail"
								href="#"
								:close-after-click="true"
								@click="clickSendMailLink(procedure.id)">
								{{ t('vtramit', 'Re-send mail to citizen') }}
							</ActionLink>
						</Actions> -->
					</td>
				</tr>
			</table>
			<!-- Pagination -->
			<nav v-if="isPaginationActive && totalPages > 1" class="paginator-row">
				<a class="paginator-item first" :style="getDisplayStyle('first')" @click="requestPage(1)"> &lt;&lt; </a>
				<a class="paginator-item prev" :style="getDisplayStyle('first')" @click="requestPage(previousPage())"> &lt; </a>
				<!-- Less than 6 pages in total -->
				<template v-if="totalPages < 6">
					<template v-for="page in totalPages">
						<a :key="page"
							class="paginator-item page"
							:class="getClassForActivePage(page)"
							@click="requestPage(page)">
							{{ page }}
						</a>
					</template>
				</template>
				<!-- More than 6 pages in total -->
				<template v-else>
					<template v-for="page in pages">
						<a :key="page"
							class="paginator-item page"
							:class="getClassForActivePage(page)"
							@click="requestPage(page)">
							{{ page }}
						</a>
					</template>
				</template>
				<a class="paginator-item next" :style="getDisplayStyle('last')" @click="requestPage(nextPage())"> > </a>
				<a class="paginator-item last" :style="getDisplayStyle('last')" @click="requestPage(lastPage())"> >> </a>
			</nav>
		</div>
		<div v-else id="emptycontent">
			<div class="icon-file" />
			<h2>{{ t('vtramit', 'Create an appointment to get started') }}</h2>
		</div>
	</div>
</template>

<script>
/**
 * @module views/ProceduresList
 *
 * Component to show the main procedures dashboard.
 *
 */
import axios from '@nextcloud/axios'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ProceduresListFilterVue from '../../components/procedures/ProceduresListFilter.vue'
import MessageManager from '../../mixins/messageManager.mixin.js'
import { Actions, ActionButton, ActionLink } from '@nextcloud/vue'

export default {
	name: 'ProceduresList',
	components: {
		ProceduresListFilterVue,
		Actions,
		ActionButton,
		ActionLink,
	},
	mixins: [ComponentStatus, MessageManager],
	data: () => ({
		timeRefresh: null,
		filter: {},
		isFilterActive: false,
		orderingColumnsBy: false,
		requestedPage: 1,
		maxVisibleButtons: 5,
		showFiltersPanel: false,
		downloadRequested: false,
		filterPanelHeight: 0,
		// TODO: a 2.0 això s'ha de carregar a través d'un endpoint de context
		constants: {
			SIGNATURE_STATUS_INITIALIZING: 1,
			SIGNATURE_STATUS_REQUEST_TO_START: 2,
			SIGNATURE_STATUS_WAITING_MAILING: 3,
			SIGNATURE_STATUS_WAITING_SIGNATURE: 4,
			SIGNATURE_STATUS_SIGNED: 5,
			SIGNATURE_STATUS_CANCELLED: 6,
			SIGNATURE_STATUS_CANCELLED_OUT_OF_TIME: 7,
		},
	}),
	computed: {
		stateIndex() {
			let state = 'noSearch'

			if (this.status.loading) {
				state = 'loading'
			} else if (this.procedures.length === 0) {
				state = 'noResults'
			} else {
				state = 'results'
			}
			return state
		},
		itemsPerPage() {
			return this.procedures.length
		},
		proceduresTable() {
			let proceduresArray = this.procedures
			if (this.orderingColumnsBy != false) {
				proceduresArray = this.sortColumns(this.procedures, this.orderingColumnsBy[0], this.orderingColumnsBy[1])
			}
			return proceduresArray.map((item) => {
					// Calculating the status name.
					let statusValue = '-'
					const status = this.statuses.find((status) => {
						if (status.id === item.statusId) {
							return true
						}
						return false
					})
					if (status) {
						statusValue = status.value
					}
					return {
						id: item.id,
						externalId: item.externalId,
						assignedGroupId: item.assignedGroupId,
						assignedUserId: item.assignedUserId,
						statusValue,
						topic: item.topic,
						appointmentDate: item.appointmentDate,
						appointmentTime: item.appointmentDate,
						staffDownloadsUrl: item.staffDownloadsUrl,
						staffUploadsUrl: item.staffUploadsUrl,
						staffSignatureUploadsUrl: item.staffSignatureUploadsUrl,
						staffSignatureDownloadsUrl: item.staffSignatureDownloadsUrl,
						jitsiRoomStaffUrl: item.jitsiRoomStaffUrl,
						jitsiRoomCode: item.jitsiRoomCode,
						uploadsQty: item.uploadsQty,
					}
				})
		},
		phoneLink() {
			return this.$store.getters['context/getPhoneLink']
		},
		procedures() {
			return this.$store.getters['procedures/readAll']
		},
		statuses() {
			return this.$store.getters['statuses/readAll']
		},
		forceProcedureListRefresh() {
			return this.$store.getters['appStatus/getForceProcedureListRefresh']
		},
		procedureIdSidebar() {
			return this.$store.getters['procedures/getItemKeySideBar']
		},
		// Pagination
		pageNumber() {
			return this.$store.getters['procedures/getPageNumber']
		},
		totalPages() {
			return this.$store.getters['procedures/getTotalPages']
		},
		isPaginationActive() {
			return this.$store.getters['procedures/isPaginationActive']
		},
		startPage() {
      if (this.requestedPage === 1 || this.requestedPage === 2 || this.requestedPage === false) {
        return 1
      }

      if (this.requestedPage === this.totalPages) {
        return this.totalPages - this.maxVisibleButtons + 1
      }

      return this.requestedPage - 2

    },
    endPage() {

      return Math.min(this.startPage + this.maxVisibleButtons - 1, this.totalPages)

    },
    pages() {
      const range = []

      for (let i = this.startPage; i <= this.endPage; i += 1) {
				range.push(i)
      }
			if (range.length < this.maxVisibleButtons) {
				const diff = this.maxVisibleButtons - range.length
				for (let i = 0; i < diff; i++) {
					range.unshift(range[0] - 1)
				}
			}

      return range
    },
    isInFirstPage() {
      return this.requestedPage === 1
    },
    isInLastPage() {
      return this.requestedPage === this.totalPages
    },
		displayFiltersPanel() {
			return this.showFiltersPanel
		},
		getFilterPanelHeight() {
			return this.filterPanelHeight
		},
	},
	watch: {
		forceProcedureListRefresh() {
			this.refreshProcedures()
		},
	},
	created() {
		this.$store.dispatch('procedureTypes/readAllCreate', this.filter)
		this.refreshProcedures()
		// TODO: development! the old time interval was 5000
		this.timeRefresh = setInterval(this.refreshProcedures, 10001)
	},
	async mounted() {
		if (this.$route.query.videoconferencefinished) {
			return axios({
				method: 'POST',
				url: OC.generateUrl(`/apps/vtramit/api/2.0/procedure/videoconference/${this.$route.query.videoconferencefinished}/finished`),
			}).then(
				result => {

					if (result.data.entity) {
						this.$router.push({ name: 'procedures-edit', params: { id: result.data.entity.id.toString() } })
					}

					this.manageMessage(result.data)

					// eslint-disable-next-line no-console
					// console.log('readAll', result)
					commit('readAllSuccess', result.data)
					return true
				},
				error => {
					// eslint-disable-next-line no-console
					console.error('ERROR readAll', error)
					throw error
				},
			)
		}
	},
	methods: {
		toggleNavigation() {
			this.$store.commit('appStatus/setShowNavigation', true)
		},
		clickNew() {
			if (this.$router.currentRoute.name !== 'procedures-new') {
				this.$router.push({ name: 'procedures-new' })
			}
		},
		clickRow(item) {
			if (this.$router.currentRoute.name !== 'procedures-edit' || this.$router.currentRoute.params.id !== item.id.toString()) {
				this.$router.push({ name: 'procedures-edit', params: { id: item.id.toString() } })
			} else if (this.$router.currentRoute.name === 'procedures-edit' || this.$router.currentRoute.params.id === item.id.toString()) {
				this.$router.push({ name: 'dashboard' })
			}
		},
		clickColumn(event) {
			const column = (event.target.tagName.toLowerCase() == 'a') ? event.target : event.target.closest('a.procedure-list-header')
			const id = column.getAttribute('id')
			const orderedBy = column.getAttribute('orderedBy')
			const icon = column.querySelector('i')
			let newOrder = ''
			let newIcon = ''
			let newState = null
			if (this.orderingColumnsBy == false) {
				// All columns are still neutrally ordered. Toggle this column to be ordered by asc
				newOrder = 'asc'
				newIcon = 'icon-triangle-n'
				newState = [id, 'asc']
			} else {
				// One of the columns is currently ordered. Toggle new order on self or on new column
				if (this.orderingColumnsBy[0] == id) {
					// It is the same column. Toggle next available state for itself
					if (orderedBy == 'asc') {
						newOrder = 'desc'
						newIcon = 'icon-triangle-s'
						newState = [id, 'desc']
					}
					if (orderedBy == 'desc') {
						newOrder = 'false'
						newIcon = 'icon-more'
						newState = false
					}
				} else {
					// It is a different column. Reset all columns and toggle first state for itself
					const columns = document.querySelectorAll('th')
					columns.forEach(element => {
						const button = element.querySelector('a')
						if (button) {
							button.setAttribute('orderedBy', false)
							button.querySelector('i').setAttribute('class', 'icon-more')
						}
					})
					newOrder = 'asc'
					newIcon = 'icon-triangle-n'
					newState = [id, 'asc']
				}
			}
			column.setAttribute('orderedBy', newOrder)
			icon.setAttribute('class', newIcon)
			this.orderingColumnsBy = newState
			// Trigger a refresh
			this.refreshProcedures()
		},
		downloadCsv() {
			this.downloadRequested = true
			OCP.Toast.success(t('vtramit', 'Download requested'))
			this.refreshProcedures()
		},
		sortColumns(procedures, column = false, order = false) {
			// Equivalence between columns and procedure properties
			if (column != false) {
				const columnName = {
					colAppointmentId: 'externalId',
					colDepartment: 'assignedGroupId',
					colAssignedTo: 'assignedUserId',
					colStatus: 'statusValue',
					colTopic: 'topic',
					colDate: 'appointmentDate',
					colName: 'name', // -> it's computed by procedure
					colCitizenId: 'personalId', // -> it's computed by procedure
					colPhone: 'phone', // -> it's computed by procedure
				}
				column = columnName[column]
			}
			// Sorting on the existing procedures table list
			return procedures.sort((a, b) => {
				// Get the missing procedure properties if the ordering column is one of them
				let stakeholderA = null
				let stakeholderB = null
				let stakeholderColumn = false
				if (column == 'name' || column == 'personalId' || column == 'phone') {
					stakeholderA = this.getStakeholders(a.id)
					stakeholderB = this.getStakeholders(b.id)
					stakeholderColumn = true
				}

				// Determine the values to compare as strings
				let value1 = null
				let value2 = null
				if (!stakeholderColumn) {
					value1 = a[column] == null ? '' : a[column].toLowerCase()
					value2 = b[column] == null ? '' : b[column].toLowerCase()
				} else {
					value1 = stakeholderA[0][column] == null ? '' : stakeholderA[0][column].toLowerCase()
					value2 = stakeholderB[0][column] == null ? '' : stakeholderB[0][column].toLowerCase()
				}

				// Sort
				if (order == 'asc') {
					if (value1 < value2) {
						return -1
					} else if (value1 > value2) {
						return 1
					}
					return 0
				} else if (order == 'desc') {
					if (value1 < value2) {
						return 1
					} else if (value1 > value2) {
						return -1
					}
					return 0
				} else if (order == false) {
					// Neutral. Order by procedure id asc
					return (a.id - b.id)
				}
			})
		},
		async clickVideoconferenceLink(procedure) {
			if (procedure.jitsiRoomCode) {

				if (!procedure.fullJitsiRoomStaffUrl || !procedure.staffPortalUrl) {
					await this.$store.dispatch('procedures/readById', procedure.id)
					procedure = this.$store.getters['procedures/readById'](procedure.id)
				}
				const payload = {
					jitsiRoomCode: procedure.jitsiRoomCode,
				}

				this.$store
					.dispatch('procedures/startVideoconference', payload)
					.then((result) => {
						if (result.result && result.result === 'ERROR') {
							throw new Error(result.message)
						} else if (result.entity.id) {
							return result.entity.id
						} else {
							throw new Error(result.message)
						}
					})
					.then((result) => {
						// Setting component status to success
						this.setStatusUpdateSuccess()
						this.$store.commit('appStatus/setForceProcedureListRefresh')
						window.open(procedure.staffPortalUrl, '_blank')
					})
					.catch((error) => {
						if (error) {
							OCP.Toast.error(t('vtramit', error))
						} else {
							OCP.Toast.error(t('vtramit', 'Could not update the procedure'))
						}
						this.$store.commit('appStatus/setForceProcedureListRefresh')
					})
			}
		},
		onFilterChanged(newFilter) {
			this.filter = Object.assign({}, newFilter)
			if (Object.keys(this.filter).length > 0) {
				this.isFilterActive = true
			} else {
				this.isFilterActive = false
			}
			this.requestPage(1) // Reset pagination on filter change
			this.refreshProcedures()
		},
		refreshProcedures() {
			this.setStatusReading()
			this.$store
				.dispatch('procedures/readAll', { newFilter: this.filter, pageNumber: this.requestedPage, isDownloadRequested: this.downloadRequested })
				.then((result) => {
					if (result && this.downloadRequested) {
						this.downloadRequested = false
						OCP.Toast.success(t('vtramit', 'Download started'))
					}
				})
				.then((/* result */) => {
					return this.$store.dispatch('stakeholders/readAll', this.filter)
				})
				.then((/* result */) => {
					return this.$store.dispatch('procedures/readActiveVideoconferences')
				})
				.then((/* result */) => {
					return this.$store.dispatch('procedures/readFilteredSignatures', { newFilter: this.filter })
				})
				.then((/* result */) => {
					this.setStatusReadSuccess()
				})
				.catch((/* error */) => {
					// console.log('eee', error)
					this.setStatusReadError()
					if (this.downloadRequested) {
						this.downloadRequested = false
						OCP.Toast.error(t('vtramit', 'Could not download the CSV file'))
						return
					}
					OCP.Toast.error(t('vtramit', 'Could not load the procedure data'))
				})
		},
		toggleFiltersPanel() {
			this.showFiltersPanel = !this.showFiltersPanel
			this.$nextTick(() => {
				this.filterPanelHeight = document.querySelector('#filter-panel').clientHeight
			})

		},
		requestPage(page) {
			this.requestedPage = page
			this.refreshProcedures()
		},
		previousPage() {
			return this.getPageNumber() - 1
		},
		nextPage() {
			return this.getPageNumber() + 1
		},
		lastPage() {
			return this.totalPages
		},
		getPageNumber() {
			return this.pageNumber
		},
		getGroupName(groupId) {
			const group = this.$store.getters['groups/readById'](groupId)
			return group.name
		},
		getUserName(userId) {
			const user = this.$store.getters['users/readById'](userId)
			return user.name
		},
		getStakeholders(procedureId) {
			return this.$store.getters['stakeholders/readMainsByProcedureId'](procedureId)
		},
		getIsVideoconferenceActive(procedureId) {
			const activeVideoconferences = this.$store.getters['procedures/readActiveVideoconferencesByProcedureId'](procedureId)
			if (activeVideoconferences.length > 0) {
				return true
			}
			return false
		},
		getIsSignatureActive(procedureId) {
			const activeSignatures = this.$store.getters['procedures/readFilteredSignaturesByProcedureId'](procedureId)
			if (activeSignatures.length > 0) {
				return true
			}
			return false
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
		getClassForUploads(qty) {
			qty = Number(qty)
			if (qty > 0) {
				return 'uploads-state loaded'
			}

			return ''
		},
		getClassForDownloads(qty) {
			qty = Number(qty)
			if (qty > 0) {
				return 'downloads-state loaded'
			}

			return ''
		},
		getClassForActivePage(page) {
			if (page == this.pageNumber) {
				return 'active'
			}
			return ''
		},
		getDisplayStyle(page) {
			if (page === 'first') {
				if (this.isInFirstPage || page == this.totalPages) {
					return 'visibility: hidden;'
				}
				return ''
			}
			if (page === 'last' || page == this.totalPages) {
				if (this.isInLastPage) {
					return 'visibility: hidden;'
				}
				return ''
			}
		},
		getFilterOpenClass(panelHeight) {
			if (panelHeight > 0 && this.showFiltersPanel) {
				const total = panelHeight + 44
				return {
						height: 'calc(100% - ' + total + 'px) !important',
				}
			}
			return { }
		},
		getClassFromSignatureState(procedureId) {
			const signatureList = this.$store.getters['procedures/readFilteredSignaturesByProcedureId'](procedureId)
			let status = 99
			signatureList.forEach((signature) => {
				if (signature.status < status) {
					status = signature.status
				}
			})

			switch (status) {
			case this.constants.SIGNATURE_STATUS_INITIALIZING:
				return 'signature-status initializing'
			case this.constants.SIGNATURE_STATUS_REQUEST_TO_START:
				return 'signature-status request-to-start'
			case this.constants.SIGNATURE_STATUS_WAITING_MAILING:
				return 'signature-status waiting-mailing'
			case this.constants.SIGNATURE_STATUS_WAITING_SIGNATURE:
				return 'signature-status waiting-signature'
			case this.constants.SIGNATURE_STATUS_SIGNED:
				return 'signature-status signed'
			case this.constants.SIGNATURE_STATUS_CANCELLED:
				return 'signature-status cancelled'
			case this.constants.SIGNATURE_STATUS_CANCELLED_OUT_OF_TIME:
				return 'signature-status cancelled-out-of-time'
			}
			return 'signature-status unknown'
		},
	},
}
</script>
