<template>
	<AppSidebar :title="t('vtramit', 'Edit procedure')"
		@close="clickClose">
		<template #secondary-actions>
			<ActionLink v-if="procedure.fullJitsiRoomStaffUrl"
				icon="icon-external"
				:href="procedure.fullJitsiRoomStaffUrl"
				target="_blank">
				{{ t('vtramit', 'External Jitsi Link') }}
			</ActionLink>
			<ActionLink v-if="procedure.staffPortalUrl"
				data-test="attendant"
				icon="icon-external"
				:href="procedure.staffPortalUrl">
				{{ t('vtramit', 'Open attendant portal') }}
			</ActionLink>
			<ActionLink v-if="isTest && stakeholderPortalUrl"
				data-test="stakeholder"
				icon="icon-external"
				:href="stakeholderPortalUrl"
				target="_blank">
				{{ t('vtramit', 'Open stakeholder portal') }}
			</ActionLink>
			<ActionLink icon="icon-mail"
				href="#"
				:close-after-click="true"
				@click="clickSendMail()">
				{{ t('vtramit', 'Re-send mail to citizen') }}
			</ActionLink>
			<ActionButton data-test="delete" icon="icon-delete" @click="clickDelete()">
				{{ t('vtramit', 'Delete') }}
			</ActionButton>
		</template>

		<div class="ma">
			<!-- ================================================================ -->
			<!-- Procedure info ================================================= -->
			<!-- ================================================================ -->

			<!-- External ID ---------------------------------------------------- -->
			<label :class="{
				'field-required': selectedProcedureType.requireExternalId && !bypassRequired
			}">{{ t('vtramit', 'Appointment ID') }}<span v-if="selectedProcedureType.requireExternalId && !bypassRequired"> *</span>
				<input data-test="cita-id" v-model="editExternalId"
					type="text"
					:placeholder="t('vtramit', 'Appointment identifier')"
					@input="changed = true; $v.editExternalId.$touch();"
					@blur="changed = true; $v.editExternalId.$touch();">
			</label>
			<div class="error_message">
				<span v-if="selectedProcedureType.requireExternalId && !bypassRequired && $v.editExternalId.$dirty && !$v.editExternalId.required">{{ t('vtramit', 'This field is required') }}</span>
			</div>

			<!-- Assigned group ------------------------------------------------- -->
			<label :class="{
				'field-required': selectedProcedureType.requireAssignedGroup && !bypassRequired && allowAssignGroup
			}">{{ t('vtramit', 'Department') }}<span v-if="selectedProcedureType.requireAssignedGroup && !bypassRequired && allowAssignGroup"> *</span>
				<select data-test="department" v-model="editAssignedGroupId"
					:disabled="!allowAssignGroup"
					:value="editAssignedGroupId"
					@change="changed = true; changedAssignedGroup()">
					<option v-if="!selectedProcedureType.requireAssignedGroup || bypassRequired || !allowAssignGroup || !editAssignedGroupId" key="0" value="0">
						&nbsp;
					</option>
					<option v-for="item in groups" :key="item.id" :value="item.id">
						{{ item.name }}
					</option>
				</select>
			</label>
			<div class="error_message">
				<span v-if="selectedProcedureType.requireAssignedGroup && !bypassRequired && allowAssignGroup && $v.editAssignedGroupId.$dirty && !$v.editAssignedGroupId.required">{{ t('vtramit', 'This field is required') }}</span>
			</div>

			<!-- Assigned user id ----------------------------------------------- -->
			<label>{{ t('vtramit', 'Assigned to') }}
				<select data-test="user" v-model="editAssignedUserId"
					:disabled="!allowAssignUser"
					:value="editAssignedUserId"
					@change="changed = true">
					<option key="0" value="0">
						&nbsp;
					</option>
					<option v-for="item in users" :key="item.id" :value="item.id">
						{{ item.name }}
					</option>
				</select>
			</label>
			<div class="error_message" />

			<!-- Attendance channel --------------------------------------------- -->
			<label :class="{
				'field-required': selectedProcedureType.requireAttendanceChannel && !bypassRequired
			}">{{ t('vtramit', 'Attendance channel') }}<span v-if="selectedProcedureType.requireAttendanceChannel && !bypassRequired"> *</span>
				<select data-test="channel" v-model="editAttendanceChannelId"
					:value="editAttendanceChannelId"
					@change="changed = true">
					<option v-if="!selectedProcedureType.requireAttendanceChannel || bypassRequired" key="0" value="0">
						&nbsp;
					</option>
					<option v-for="item in channels" :key="item.id" :value="item.id">
						{{ item.name }}
					</option>
				</select>
			</label>
			<div class="error_message">
				<span v-if="selectedProcedureType.requireAttendanceChannel && !bypassRequired && $v.editAttendanceChannelId.$dirty && !$v.editAttendanceChannelId.required">{{ t('vtramit', 'This field is required') }}</span>
			</div>

			<!-- Topic ---------------------------------------------------------- -->
			<label>{{ t('vtramit', 'Topic') }}
				<TopicSelector
					data-test="topic"
					v-model="editTopic"
					:placeholder="t('vtramit', 'Topic')"
					:min-chars="2"
					@change="changed = true"
				/>
			</label>
			<div class="error_message" />

			<!-- Appointment date ----------------------------------------------- -->
			<label :class="{
				'field-required': selectedProcedureType.requireAppointmentDate && !bypassRequired
			}">{{ t('vtramit', 'Appointment date and time') }}<span v-if="selectedProcedureType.requireAppointmentDate && !bypassRequired"> *</span>
				<div>
					<DatetimePicker data-test="date" v-model="editAppointmentDate"
						format="YYYY-MM-DD H:mm"
						type="datetime"
						:default-value="new Date()"
						@change="changed = true; editAppointmentDateChanged = true" />
				</div>
			</label>
			<div class="error_message">
				<span v-if="selectedProcedureType.requireAppointmentDate && $v.editAppointmentDate.$dirty && !$v.editAppointmentDate.required">{{ t('vtramit', 'This field is required') }}</span>
			</div>

			<!-- Deadline date -------------------------------------------------- -->
			<label :class="{
				'field-required': selectedProcedureType.requireDeadlineDate && !bypassRequired
			}">{{ t('vtramit', 'Deadline date and time') }}<span v-if="selectedProcedureType.requireDeadlineDate && !bypassRequired"> *</span>
				<div>
					<DatetimePicker data-test="deadline" v-model="editDeadlineDate"
						format="YYYY-MM-DD H:mm"
						type="datetime"
						:default-value="new Date()"
						@change="changed = true; editDeadlineDateChanged = true" />
				</div>
			</label>
			<div class="error_message">
				<span v-if="selectedProcedureType.requireDeadlineDate && $v.editDeadlineDate.$dirty && !$v.editDeadlineDate.required">{{ t('vtramit', 'This field is required') }}</span>
			</div>

			<!-- Comments ------------------------------------------------------- -->
			<label>{{ t('vtramit', 'Comments') }}
				<textarea data-test="comment" v-model="editComments"
					:placeholder="t('vtramit', 'Comments')"
					@change="changed = true" />
			</label>
			<div class="error_message" />

			<!-- ================================================================ -->
			<!-- Stakeholders info ============================================== -->
			<!-- ================================================================ -->
			<div class="mt">
				{{ t('vtramit', 'Stakeholders') }}:
			</div>
			<hr>

			<!-- TODO: bloquear botón y mostrar mensaje 'Loading stakeholders' mientras se cargan los datos -->
			<div v-for="(v, index) in $v.editStakeholders.$each.$iter" :key="`stkh_${index}`">
				<div v-if="!editStakeholders[index].editActions.toDelete" class="d-flex justify-space-between align-center">
					<span># {{ parseInt(index) + 1 }}</span>
					<Actions>
						<ActionButton icon="icon-delete" @click.stop="changed = true; clickRemoveStakeholder(index)">
							{{ t('vtramit', 'Remove stakeholder') }}
						</ActionButton>
					</Actions>
				</div>
				<div v-if="!editStakeholders[index].editActions.toDelete" class="ml">
					<!-- Is main? ------------------------------------------------- -->
					<div>
						<input :id="`isMain_${v.isMainId.$model}`"
							v-model="editIsMainStakeholder"
							type="radio"
							class="radio"
							:value="v.isMainId.$model"
							@input="changed = true">
						<label :for="`isMain_${v.isMainId.$model}`">{{ t('vtramit', 'Is main?') }}</label>
					</div>
					<div class="error_message" />
					<!-- Personal ID ------------------------------------------------ -->
					<label :class="{
						'field-required': selectedProcedureType.requireStakeholderPid && !bypassRequired
					}">{{ t('vtramit', 'Citizen ID') }}<span v-if="selectedProcedureType.requireStakeholderPid && !bypassRequired"> *</span>
						<input data-test="id-edit" v-model="v.personalId.$model"
							type="text"
							:placeholder="t('vtramit', 'Citizen Card ID')"
							@input="changed = true; clickChangeStakeholder(index); v.personalId.$touch();"
							@blur="changed = true; clickChangeStakeholder(index); v.personalId.$touch();">
					</label>
					<div class="error_message">
						<span v-if="selectedProcedureType.requireStakeholderPid && !bypassRequired && v.personalId.$dirty && !v.personalId.required">{{ t('vtramit', 'This field is required') }}</span>
					</div>

					<!-- Name ------------------------------------------------------- -->
					<label :class="{
						'field-required': !bypassRequired
					}">{{ t('vtramit', 'Name') }}<span v-if="!bypassRequired"> *</span>
						<input data-test="name-edit" v-model="v.name.$model"
							type="text"
							:placeholder="t('vtramit', 'Name')"
							@input="changed = true; clickChangeStakeholder(index); v.name.$touch();"
							@blur="changed = true; clickChangeStakeholder(index); v.name.$touch();">
					</label>
					<div class="error_message">
						<span v-if="!bypassRequired && v.name.$dirty && !v.name.required">{{ t('vtramit', 'This field is required') }}</span>
					</div>

					<!-- Phone ------------------------------------------------------ -->
					<label :class="{
						'field-required': selectedProcedureType.requireStakeholderPhone && !bypassRequired
					}">{{ t('vtramit', 'Phone') }}<span v-if="selectedProcedureType.requireStakeholderPhone && !bypassRequired"> *</span>
						<input data-test="phone-edit" v-model="v.phone.$model"
							type="text"
							:placeholder="t('vtramit', 'Phone')"
							@input="changed = true; clickChangeStakeholder(index); v.phone.$touch();"
							@blur="changed = true; clickChangeStakeholder(index); v.phone.$touch();">
					</label>
					<div class="error_message">
						<span v-if="selectedProcedureType.requireStakeholderPhone && !bypassRequired && v.phone.$dirty && !v.phone.required">{{ t('vtramit', 'This field is required') }}</span>
						<span v-else-if="v.phone.$dirty && !v.phone.phoneValid">{{ t('vtramit', 'Phone number is not valid') }}</span>
					</div>

					<!-- Email ------------------------------------------------------ -->
					<label :class="{
						'field-required': selectedProcedureType.requireStakeholderEmail && !bypassRequired
					}">{{ t('vtramit', 'Email') }}<span v-if="selectedProcedureType.requireStakeholderEmail && !bypassRequired"> *</span>
						<input data-test="email-edit" v-model="v.email.$model"
							type="text"
							:placeholder="t('vtramit', 'Email')"
							@input="changed = true; clickChangeStakeholder(index); v.email.$touch();"
							@blur="changed = true; clickChangeStakeholder(index); v.email.$touch();">
					</label>
					<div class="error_message">
						<span v-if="selectedProcedureType.requireStakeholderEmail && !bypassRequired && v.email.$dirty && !v.email.required">{{ t('vtramit', 'This field is required') }}</span>
						<span v-else-if="v.email.$dirty && !v.email.email">{{ t('vtramit', 'Email field is not valid') }}</span>
					</div>
				</div>
			</div>

			<div class="d-flex justify-end mb">
				<!-- <Actions>
					<ActionButton icon="icon-add" @click.stop="changed = true; clickAddNewStakeholder()">
						{{ t('vtramit', 'Add new stakeholder') }}
					</ActionButton>
				</Actions> -->
				<input data-test="add-stakeholder" type="button"
					class="primary"
					:value="t('vtramit', 'Add new stakeholder')"
					@click="clickAddNewStakeholder">
			</div>

			<div class="fixed-bottom">
				<div class="d-flex align-center">
					<input data-test="save" type="button"
						class="primary"
						:value="t('vtramit', 'Save')"
						:disabled="!formReadyForSubmit"
						@click="clickUpdate">

					<div v-if="changed" class="unsaved-changes">
						{{ t('vtramit', 'There are unsaved changes') }}
					</div>
				</div>

				<template v-if="hasSignatureButtons">
					<hr>
					<div>
						<input v-for="signatureButton in signatureButtons"
							:key="signatureButton.id"
							type="button"
							:value="getSignatureLabel(signatureButton)"
							:disabled="!formReadyForSubmit"
							@click="clickSignatureButton(signatureButton)">
					</div>
				</template>

				<div v-if="statuses">
					<hr>
					<input v-for="status in statuses"
						:key="status.id"
						type="button"
						:value="t('vtramit', status.action)"
						:disabled="!formReadyForSubmit"
						@click="clickChangeStatus(status.id)">
				</div>
			</div>
		</div>
	</AppSidebar>
</template>

<script>
/**
 * @module components/procedures/ProceduresEdit
 *
 * Component to edit one procedure.
 *
 */
import moment from '@nextcloud/moment'
import TopicSelector from '../fields/TopicSelector.vue'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import MessageManager from '../../mixins/messageManager.mixin.js'
import { required, email } from 'vuelidate/lib/validators/index.js'
import { AppSidebar, DatetimePicker, Actions, ActionButton, ActionLink } from '@nextcloud/vue'

const phoneValid = (value) => !value || value.replace(/\D/g, '').length >= 7

export default {
	name: 'ProceduresEdit',
	components: {
		TopicSelector,
		AppSidebar,
		DatetimePicker,
		Actions,
		ActionButton,
		ActionLink,
	},
	mixins: [ComponentStatus, MessageManager],
	data: () => ({
		firstFormClick: true,
		changed: false,
		previousProcedureId: null,

		// New proxy form data
		editProcedureTypeId: null,
		editExternalId: null,
		editAssignedGroupId: null,
		editAssignedUserId: null,
		editAttendanceChannelId: null,
		editTopic: null,
		editComments: null,
		editAppointmentDate: null,
		editDeadlineDate: null,

		// Due to the way the datepicker component has to be initialised we need
		// a couple of flags to determine if the data has been changed
		editAppointmentDateChanged: false,
		editDeadlineDateChanged: false,

		editStakeholders: [],
		editIsMainStakeholder: null,
		editIsMainStakeholderInit: null,
	}),
	validations() {
		const validations = {
			editProcedureTypeId: {},
			editExternalId: {},
			editAssignedGroupId: {},
			editAssignedUserId: {},
			editAttendanceChannelId: {},
			editTopic: {},
			editComments: {},
			editAppointmentDate: {},
			editDeadlineDate: {},
			editStakeholders: {
				required,
				$each: {
					isMainId: {},
					personalId: {},
					name: {},
					phone: { phoneValid },
					email: {
						email,
					},
				},
			},
		}

		if (!this.bypassRequired) {
			validations.editStakeholders.$each.name.required = required
			if (this.selectedProcedureType.requireAppointmentDate) {
				validations.editAppointmentDate.required = required
			}
			if (this.selectedProcedureType.requireAssignedGroup && this.allowAssignUser) {
				validations.editAssignedGroupId.required = required
			}
			if (this.selectedProcedureType.requireAttendanceChannel) {
				validations.editAttendanceChannelId.required = required
			}
			if (this.selectedProcedureType.requireDeadlineDate) {
				validations.editDeadlineDate.required = required
			}
			if (this.selectedProcedureType.requireExternalId) {
				validations.editExternalId.required = required
			}
			if (this.selectedProcedureType.requireStakeholderEmail) {
				validations.editStakeholders.$each.email.required = required
			}
			if (this.selectedProcedureType.requireStakeholderPid) {
				validations.editStakeholders.$each.personalId.required = required
			}
			if (this.selectedProcedureType.requireStakeholderPhone) {
				validations.editStakeholders.$each.phone.required = required
			}
		}

		return validations
	},
	computed: {
		objectId() {
			let objectId = ''
			if (this.$route.params.id) {
				objectId = parseInt(this.$route.params.id.toString())
			}
			return objectId
		},
		// When the procedure id changes we use the store getter to "get" the
		// object that has (or the temporary one that will have) the procedure data.
		// Note that this is different than the store action used to read the
		// actual data from the backend, they both work "in parallel"
		procedure() {
			return this.$store.getters['procedures/readById'](this.objectId)
		},
		stakeholders() {
			return this.$store.getters['stakeholders/readSidebarByProcedureId'](this.objectId)
		},
		stakeholderPortalUrl() {
			return this.mainStakeholder?.id ? this.mainStakeholder.fullEntryPage : null
		},
		formReadyForSubmit() {
			if (this.status.loading) {
				return false
			}
			if (this.status.readError) {
				return false
			}
			if (this.firstFormClick) {
				return true
			}
			return true
		},
		hasSignatureButtons() {
			if (this.signatureButtons && this.signatureButtons.length > 0) {
				return true
			}
			return false
		},
		groups() {
			return this.$store.getters['procedureTypes/readAllowedGroupsAll']
		},
		users() {
			return this.$store.getters['procedureTypes/readAllowedUsersByGroupId'](this.editAssignedGroupId)
		},
		channels() {
			return this.$store.getters['procedures/readAllowedChannelsAll']
		},
		statuses() {
			return this.$store.getters['procedures/readAllowedStatusesAll']
		},
		signatureButtons() {
			return this.$store.getters['procedures/readSignatureButtonsAll']
		},
		allowAssignGroup() {
			return this.$store.getters['procedureTypes/allowAssignGroup']
		},
		allowAssignUser() {
			return this.$store.getters['procedureTypes/allowAssignUser']
		},
		bypassRequired() {
			return this.$store.getters['procedureTypes/bypassRequired']
		},
		procedureTypes() {
			return this.$store.getters['procedureTypes/readAll']
		},
		selectedProcedureType() {
			return this.$store.getters['procedureTypes/readById'](this.editProcedureTypeId)
		},
		timezone() {
			return this.$store.getters['context/getTimezone']
		},
		isTest() {
			return this.$store.getters['context/isTest']
		},
		mainStakeholder() {
			return (this.stakeholders || []).find(s => s.isMain) || null
		},
	},
	mounted() {
		this.ensureStakeholderEntryPage()
	},
	watch: {
		// If the procedure id changes and we have not edit any field we trigger
		// the store action to load the data. Note that this is different than
		// the getter used to display the data, they both work "in parallel"
		objectId: {
			immediate: true,
			handler() {
				this.setStatusReading()
				this.$store
					.dispatch('procedures/readById', this.objectId)
					.then((/* result */) => {
						return this.$store
							.dispatch('procedures/readProcedureInfoById', this.objectId)
					})
					.then((/* result */) => {
						return this.$store
							.dispatch('procedureTypes/readProcedureTypeInfoById', this.editProcedureTypeId)
					})
					.then((/* result */) => {
						return this.$store
							.dispatch('stakeholders/readSidebarByProcedureId', this.objectId)
					})
					.then((/* result */) => {
						this.setStatusReadSuccess()
					})
					.catch((/* error */) => {
						this.setStatusReadError()
						OCP.Toast.error(t('vtramit', 'Could not load the procedure data'))
					})
			},
		},
		procedure: {
			immediate: true,
			handler() {
				if (this.previousProcedureId !== this.procedure.id) {
					this.changed = false
					this.previousProcedureId = this.procedure.id
				}
				this.updateFormDataFromModel()
			},
		},
		stakeholders: {
			immediate: true,
			handler() {
				this.updateFormDataFromModel()
			},
		},
		mainStakeholder(newVal, oldVal) {
			if (newVal && !newVal.fullEntryPage) {
				this.ensureStakeholderEntryPage()
			}
		},
	},
	created() {
	},
	methods: {
		// ------------------------------------------------------------------------
		// Resolve localized label without translation, using Nextcloud locale
		getSignatureLabel(button) {
			// Get full locale, e.g. "es_ES"
			const locale = (typeof OC !== 'undefined' && OC.getLocale) ? OC.getLocale() : 'en'
			// Derive base language, e.g. "es"
			const base = locale.includes('_') ? locale.split('_')[0] : locale

			// Try exact match (es_ES), then base (es), then explicit fallbacks, then first available
			const labels = button?.label || {}
			return labels[locale]
				|| labels[base]
				|| labels['es']
				|| labels['en']
				|| Object.values(labels)[0]
				|| ''
		},
		// ------------------------------------------------------------------------
		// Ensure fullEntryPage is fetched and stored
		ensureStakeholderEntryPage() {
			if (!this.isTest) return
			const id = this.mainStakeholder?.id
			if (!id) return
			if (!this.mainStakeholder?.fullEntryPage && !this.mainStakeholder.loadingEntryPage) {
				this.$store.dispatch('stakeholders/fetchEntryPage', id).catch(() => {})
			}
		},
		updateFormDataFromModel() {
			if (!this.changed) {
				this.editProcedureTypeId = this.procedure.procedureTypeId ? this.procedure.procedureTypeId : ''
				this.editExternalId = this.procedure.externalId ? this.procedure.externalId : ''
				this.editAssignedGroupId = this.procedure.assignedGroupId ? this.procedure.assignedGroupId : ''
				this.editAssignedUserId = this.procedure.assignedUserId ? this.procedure.assignedUserId : ''
				this.editAttendanceChannelId = this.procedure.attendanceChannel ? this.procedure.attendanceChannel : 0
				this.editTopic = this.procedure.topic ? this.procedure.topic : ''
				this.editComments = this.procedure.comments ? this.procedure.comments : ''
				this.editAppointmentDate = this.procedure.appointmentDate ? moment(this.procedure.appointmentDate).toDate() : null
				this.editDeadlineDate = this.procedure.deadlineDate ? moment(this.procedure.deadlineDate).toDate() : null

				this.editStakeholders.splice(0, this.editStakeholders.length)
				this.stakeholders.forEach(element => {
					const tempStakeholder = this.newStakeHolder()
					tempStakeholder.id = element.id ? element.id : null
					tempStakeholder.personalId = element.personalId ? element.personalId : ''
					tempStakeholder.name = element.name ? element.name : ''
					tempStakeholder.phone = element.phone ? element.phone : ''
					tempStakeholder.email = element.email ? element.email : ''
					this.editIsMainStakeholder = element.isMain ? tempStakeholder.isMainId : false
					this.editIsMainStakeholderInit = element.isMain ? tempStakeholder.isMainId : false
					this.editStakeholders.push(tempStakeholder)
				})
			}
		},
		newStakeHolder() {
			return {
				isMainId: `${Date.now()}_${Math.floor(Math.random() * 1000)}`,
				id: null,
				personalId: null,
				name: null,
				phone: null,
				email: null,
				editActions: {
					isNew: false,
					isChanged: false,
					toDelete: false,
				},
			}
		},
		changedAssignedGroup(newValue) {
			if (this.editAssignedGroupId !== newValue) {
				this.editAssignedUserId = null
			}
		},
		clickClose() {
			this.$router.push({ name: 'dashboard' })
		},
		clickAddNewStakeholder() {
			this.changed = true
			const tempStakeholder = this.newStakeHolder()
			tempStakeholder.editActions.isNew = true
			this.editStakeholders.push(tempStakeholder)
		},
		clickRemoveStakeholder(index) {
			this.changed = true
			this.editStakeholders[index].editActions.toDelete = !this.editStakeholders[index].editActions.toDelete
		},
		clickChangeStakeholder(index) {
			this.changed = true
			this.editStakeholders[index].editActions.isChanged = true
		},
		clickSendMail() {
			if (this.editStakeholders.length === 0) {
				OCP.Toast.error(t('vtramit', 'You must define the stakeholders to receive the e-mails'))
				return
			}
			if (this.changed) {
				OCP.Toast.error(t('vtramit', 'Cannot send the e-mail while editing the procedure. Please, safe before send the e-mail.'))
				return
			}

			this.setStatusUpdating()

			const stakeholdersPromises = []
			for (const element of this.editStakeholders) {
				stakeholdersPromises.push(
					this.$store.dispatch('stakeholders/sendEmail', {
						procedureId: this.objectId,
						id: element.id,
					}),
				)
			}
			if (stakeholdersPromises.length > 0) {
				Promise.all(stakeholdersPromises)
					.then((result) => {
						// Setting component status to success
						this.setStatusUpdateSuccess()
						OCP.Toast.success(t('vtramit', 'Email in queue to be sent'))
					})
					.catch((/* error */) => {
						// Setting component status to failure
						this.setStatusUpdateError()
						OCP.Toast.error(t('vtramit', 'Could not send the e-mail'))
					})
			} else {
				this.setStatusUpdateError()
				OCP.Toast.error(t('vtramit', 'Could not send the e-mail'))
			}
		},
		clickSignatureButton(button) {
			this.setStatusUpdating()
			const payload = {
				id: this.objectId,
				signatureButtonId: button.id,
			}
			this.$store
				.dispatch('procedures/updateSignature', payload)
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch()
					this.manageMessage(result)

					if (result.result === 'OK' && result.entity.id) {
						this.$store.commit('appStatus/setForceProcedureListRefresh')
					}
				})
				.catch((error) => {
					if (error.message !== 'IGNORE') {
						// Setting the appropriate error markers from the server response
						this.$v.$touch()
						this.manageException(error, t('vtramit', 'Could not update the procedure'))

						this.$store.commit('appStatus/setForceProcedureListRefresh')
					}
				})
		},
		clickChangeStatus(newStatusId) {
			this.setStatusUpdating()
			const payload = {
				id: this.objectId,
				statusId: newStatusId,
			}

			// We will first update the procedure itself and then all the
			// stakeholders with an array of api call promises
			this.$store
				.dispatch('procedures/update', payload)
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch()
					this.manageMessage(result)

					if (result.result === 'OK' && result.entity.id) {
						if (typeof result.redirect === 'string' && result.redirect) {
							window.open(result.redirect)
						}
						return this.$store.dispatch('procedures/readProcedureInfoById', this.objectId)
					}

					// Throw an exception to be ignored because we have already managed the messages to be shown from the server
					throw new Error('IGNORE')
				})
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch()
					this.manageMessage(result)

					this.$store.commit('appStatus/setForceProcedureListRefresh')
				})
				.catch((error) => {
					if (error.message !== 'IGNORE') {
						// Setting the appropriate error markers from the server response
						this.$v.$touch()
						this.manageException(error, t('vtramit', 'Could not update the procedure'))

						this.$store.commit('appStatus/setForceProcedureListRefresh')
					}
				})
		},
		clickUpdate() {
			if (this.firstFormClick) {
				this.firstFormClick = false
				this.$v.$touch()
			}
			this.$nextTick(() => {
				if (!this.$v.$invalid && this.changed) {
					this.sendUpdate()
				}
			})
		},
		sendUpdate() {
			this.setStatusUpdating()
			// First we need to see if the isMain stakeholder mark has been changed
			// and mark the stakeholders accordingly. We only mark that they changed
			// as we send every bit of information about the stakeholders in
			// case of change.
			if (this.editIsMainStakeholder !== this.editIsMainStakeholderInit) {
				for (const element of this.editStakeholders) {
					if (this.editIsMainStakeholder === element.isMainId) {
						element.editActions.isChanged = true
					}
					if (this.editIsMainStakeholderInit === element.isMainId) {
						element.editActions.isChanged = true
					}
				}
			}

			const payload = {
				id: this.objectId,
			}
			if (this.procedure.procedureTypeId !== this.editProcedureTypeId) {
				payload.procedureTypeId = this.editProcedureTypeId
			}
			if (this.procedure.externalId !== this.editExternalId) {
				payload.externalId = this.editExternalId
			}
			if (this.procedure.assignedGroupId !== this.editAssignedGroupId) {
				payload.assignedGroupId = this.editAssignedGroupId
			}
			if (this.procedure.assignedUserId !== this.editAssignedUserId) {
				payload.assignedUserId = this.editAssignedUserId
			}
			if (this.procedure.attendanceChannel !== this.editAttendanceChannelId) {
				payload.attendanceChannel = this.editAttendanceChannelId
			}
			if (this.procedure.topic !== this.editTopic) {
				payload.topic = this.editTopic
			}
			if (this.procedure.comments !== this.editComments) {
				payload.comments = this.editComments
			}
			if (this.editAppointmentDateChanged) {
				payload.appointmentDate = moment(this.editAppointmentDate).local().format('YYYY-MM-DD HH:mm:00')
			}
			if (this.editDeadlineDateChanged) {
				payload.deadlineDate = moment(this.editDeadlineDate).local().format('YYYY-MM-DD HH:mm:00')
			}

			// We will first update the procedure itself and then all the
			// stakeholders with an array of api call promises.
			this.$store
				.dispatch('procedures/update', payload)
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch(result)
					this.manageMessage(result)

					if (result.result !== 'OK') {
						// Throw an exception to be ignored because we have already managed the messages to be shown from the server
						throw new Error('IGNORE')
					}

					const newStakeholders = []
					for (const element of this.editStakeholders) {
						if (element.editActions.isNew && !element.editActions.toDelete) {
							const tempStakeholder = {
								procedureId: this.objectId,
								personalId: element.personalId,
								name: element.name,
								phone: element.phone,
								email: element.email,
							}
							if (this.editIsMainStakeholder === element.isMainId) {
								tempStakeholder.isMain = true
							} else {
								tempStakeholder.isMain = false
							}
							newStakeholders.push(tempStakeholder)
						}
					}
					if (newStakeholders.length > 0) {
						return this.$store.dispatch('stakeholders/createBatch', newStakeholders)
					}
					return true
				})
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch(result)
					this.manageMessage(result)

					const updateStakeholders = []
					for (const element of this.editStakeholders) {
						if (element.editActions.isChanged && !element.editActions.isNew && !element.editActions.toDelete) {
							const tempStakeholder = {
								id: element.id,
								procedureId: this.objectId,
								personalId: element.personalId,
								name: element.name,
								phone: element.phone,
								email: element.email,
							}
							if (this.editIsMainStakeholder === element.isMainId) {
								tempStakeholder.isMain = true
							} else {
								tempStakeholder.isMain = false
							}
							updateStakeholders.push(tempStakeholder)
						}
					}
					if (updateStakeholders.length > 0) {
						return this.$store.dispatch('stakeholders/updateBatch', updateStakeholders)
					}
					return true
				})
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch(result)
					this.manageMessage(result)

					const deleteStakeholders = []
					for (const element of this.editStakeholders) {
						if (element.editActions.toDelete && !element.editActions.isNew) {
							deleteStakeholders.push({
								procedureId: this.objectId,
								id: element.id,
							})
						}
					}
					if (deleteStakeholders.length > 0) {
						return this.$store.dispatch('stakeholders/deleteBatch', deleteStakeholders)
					}
					return true
				})
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch()
					this.manageMessage(result)

					this.$store.commit('appStatus/setForceProcedureListRefresh')
					this.$router.push({
						name: 'dashboard',
					})
				})
				.catch((error) => {
					if (error.message !== 'IGNORE') {
						// Setting the appropriate error markers from the server response.
						this.$v.$touch()
						this.manageException(error, t('vtramit', 'Could not update the procedure'))

						this.$store.commit('appStatus/setForceProcedureListRefresh')
					}
				})
		},
		clickDelete() {
			// To delete the procedure first we need to delete the stakeholders
			// and then the procedure itself.
			this.$store
				.dispatch('stakeholders/deleteByProcedureId', this.objectId)
				.then(() => {
					return this.$store
						.dispatch('procedures/delete', this.objectId)
				})
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch()
					this.manageMessage(result)

					this.$store.commit('appStatus/setForceProcedureListRefresh')
					this.$router.push({
						name: 'dashboard',
					})
				})
				.catch((error) => {
					if (error.message !== 'IGNORE') {
						// Setting the appropriate error markers from the server response.
						this.$v.$touch()
						this.manageException(error, t('vtramit', 'Could not delete the procedure'))

						this.$store.commit('appStatus/setForceProcedureListRefresh')
					}
				})
		},
	},
}
</script>
