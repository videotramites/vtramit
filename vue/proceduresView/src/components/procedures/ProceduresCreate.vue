<template>
	<!-- TODO: The sidebar itself ideally should not be part of this component -->
	<AppSidebar :title="t('vtramit', 'Add new procedure')"
		@close="clickClose">
		<div class="ma">
			<!-- ================================================================ -->
			<!-- Procedure type ================================================= -->
			<!-- ================================================================ -->
			<label class="field-required">{{ t('vtramit', 'Procedure type') }} *
				<select data-test="procedure"  v-model="editProcedureTypeId"
					:value="editProcedureTypeId"
					@change="changedProcedureType">
					<option v-for="item in procedureTypesCreate" :key="item.id" :value="item.id">
						{{ item.name }}
					</option>
				</select>
			</label>
			<div class="error_message" />

			<template v-if="editProcedureTypeId">
				<hr class="mb">

				<!-- ============================================================== -->
				<!-- Procedure info =============================================== -->
				<!-- ============================================================== -->

				<!-- External ID -------------------------------------------------- -->
				<label :class="{
					'field-required': selectedProcedureType.requireExternalId && !bypassRequired
				}">{{ t('vtramit', 'Appointment ID') }}<span v-if="selectedProcedureType.requireExternalId && !bypassRequired"> *</span>
					<input data-test="cita-id" v-model="editExternalId"
						type="text"
						:placeholder="t('vtramit', 'Appointment identifier')"
						@input="$v.editExternalId.$touch();"
						@blur="$v.editExternalId.$touch();">
				</label>
				<div class="error_message">
					<span v-if="selectedProcedureType.requireExternalId && !bypassRequired && $v.editExternalId.$dirty && !$v.editExternalId.required">{{ t('vtramit', 'This field is required') }}</span>
				</div>

				<!-- Assigned group ----------------------------------------------- -->
				<label :class="{
					'field-required': selectedProcedureType.requireAssignedGroup && !bypassRequired && allowAssignGroup
				}">{{ t('vtramit', 'Department') }}<span v-if="selectedProcedureType.requireAssignedGroup && !bypassRequired && allowAssignGroup"> *</span>
					<select data-test="department" v-model="editAssignedGroupId"
						:disabled="!allowAssignGroup"
						:value="editAssignedGroupId"
						@change="changedAssignedGroup">
						<option v-for="item in groups" :key="item.id" :value="item.id">
							{{ item.name }}
						</option>
					</select>
				</label>
				<div class="error_message">
					<span v-if="selectedProcedureType.requireAssignedGroup && !bypassRequired && allowAssignGroup && $v.editAssignedGroupId.$dirty && !$v.editAssignedGroupId.required">{{ t('vtramit', 'This field is required') }}</span>
				</div>

				<!-- Assigned user id --------------------------------------------- -->
				<label >{{ t('vtramit', 'Assigned to') }}
					<select data-test="user" v-model="editAssignedUserId"
						:disabled="!allowAssignUser"
						:value="editAssignedUserId">
						<option v-for="item in users" :key="item.id" :value="item.id">
							{{ item.name }}
						</option>
					</select>
				</label>
				<div class="error_message" />

				<!-- Attendance channel ------------------------------------------- -->
				<label :class="{
					'field-required': selectedProcedureType.requireAttendanceChannel && !bypassRequired
				}">{{ t('vtramit', 'Attendance channel') }}<span v-if="selectedProcedureType.requireAttendanceChannel && !bypassRequired"> *</span>
					<select data-test="channel" v-model="editAttendanceChannelId"
						:value="editAttendanceChannelId">
						<option v-for="item in channels" :key="item.id" :value="item.id">
							{{ item.name }}
						</option>
					</select>
				</label>
				<div class="error_message">
					<span v-if="selectedProcedureType.requireAttendanceChannel && !bypassRequired && $v.editAttendanceChannelId.$dirty && !$v.editAttendanceChannelId.required">{{ t('vtramit', 'This field is required') }}</span>
				</div>

				<!-- Topic -------------------------------------------------------- -->
				<label >{{ t('vtramit', 'Topic') }}
					<TopicSelector
						data-test="topic"
						v-model="editTopic"
						:placeholder="t('vtramit', 'Topic')"
						:min-chars="2"
					/>
				</label>
				<div class="error_message" />

				<!-- Appointment date --------------------------------------------- -->
				<label :class="{
					'field-required': selectedProcedureType.requireAppointmentDate && !bypassRequired
				}">{{ t('vtramit', 'Appointment date and time') }}<span v-if="selectedProcedureType.requireAppointmentDate && !bypassRequired"> *</span>
					<div>
						<DatetimePicker data-test="date" v-model="editAppointmentDate"
							format="YYYY-MM-DD H:mm"
							type="datetime"
							:default-value="new Date()" />
					</div>
				</label>
				<div class="error_message">
					<span v-if="selectedProcedureType.requireAppointmentDate && !bypassRequired && $v.editAppointmentDate.$dirty && !$v.editAppointmentDate.required">{{ t('vtramit', 'This field is required') }}</span>
				</div>

				<!-- Deadline date ------------------------------------------------ -->
				<label  :class="{
					'field-required': selectedProcedureType.requireDeadlineDate && !bypassRequired
				}">{{ t('vtramit', 'Deadline date and time') }}<span v-if="selectedProcedureType.requireDeadlineDate && !bypassRequired"> *</span>
					<div>
						<DatetimePicker data-test="deadline" v-model="editDeadlineDate"
							format="YYYY-MM-DD H:mm"
							type="datetime"
							:default-value="new Date()" />
					</div>
				</label>
				<div class="error_message">
					<span v-if="selectedProcedureType.requireDeadlineDate && !bypassRequired && $v.editDeadlineDate.$dirty && !$v.editDeadlineDate.required">{{ t('vtramit', 'This field is required') }}</span>
				</div>

				<!-- Comments ----------------------------------------------------- -->
				<label >{{ t('vtramit', 'Comments') }}
					<textarea data-test="comment" v-model="editComments"
						:placeholder="t('vtramit', 'Comments')" />
				</label>
				<div class="error_message" />

				<!-- ============================================================== -->
				<!-- Stakeholders info ============================================ -->
				<!-- ============================================================== -->
				<div class="mt">
					{{ t('vtramit', 'Stakeholders') }}:
				</div>
				<hr>

				<div data-test="stakeholder" v-for="(v, index) in $v.editStakeholders.$each.$iter" :key="`stkh_${index}`">
					<div class="d-flex justify-space-between align-center">
						<span># {{ parseInt(index) + 1 }}</span>
						<Actions>
							<ActionButton data-test="remove-stakeholder" icon="icon-delete" @click.stop="clickRemoveStakeholder(index)">
								{{ t('vtramit', 'Remove stakeholder') }}
							</ActionButton>
						</Actions>
					</div>
					<div class="ml" data-test="isMain">
						<!-- Is main? ------------------------------------------------- -->
						<div>
							<input :id="`isMain_${v.isMainId.$model}`"
								v-model="editIsMainStakeholder"
								type="radio"
								class="radio"
								:value="v.isMainId.$model">
							<label :for="`isMain_${v.isMainId.$model}`">{{ t('vtramit', 'Is main?') }}</label>
						</div>
						<div class="error_message" />
						<!-- Personal ID ---------------------------------------------- -->
						<label :class="{
							'field-required': selectedProcedureType.requireStakeholderPid && !bypassRequired
						}">{{ t('vtramit', 'Citizen ID') }}<span v-if="selectedProcedureType.requireStakeholderPid && !bypassRequired"> *</span>
							<input data-test="id" v-model="v.personalId.$model"
								type="text"
								:placeholder="t('vtramit', 'Citizen Card ID')"
								@input="v.personalId.$touch();"
								@blur="v.personalId.$touch();">
						</label>
						<div class="error_message">
							<span v-if="selectedProcedureType.requireStakeholderPid && !bypassRequired && v.personalId.$dirty && !v.personalId.required">{{ t('vtramit', 'This field is required') }}</span>
						</div>

						<!-- Name ----------------------------------------------------- -->
						<label  :class="{
							'field-required': !bypassRequired
						}">{{ t('vtramit', 'Name') }}<span v-if="!bypassRequired"> *</span>
							<input data-test="name" v-model="v.name.$model"
								type="text"
								:placeholder="t('vtramit', 'Name')"
								@input="v.name.$touch();"
								@blur="v.name.$touch();">
						</label>
						<div class="error_message">
							<span v-if="!bypassRequired && v.name.$dirty && !v.name.required">{{ t('vtramit', 'This field is required') }}</span>
						</div>

						<!-- Phone ---------------------------------------------------- -->
						<label  :class="{
							'field-required': selectedProcedureType.requireStakeholderPhone && !bypassRequired
						}">{{ t('vtramit', 'Phone') }}<span v-if="selectedProcedureType.requireStakeholderPhone && !bypassRequired"> *</span>
							<input data-test="phone" v-model="v.phone.$model"
								type="text"
								:placeholder="t('vtramit', 'Phone')"
								@input="v.phone.$touch();"
								@blur="v.phone.$touch();">
						</label>
						<div  class="error_message">
							<span v-if="selectedProcedureType.requireStakeholderPhone && !bypassRequired && v.phone.$dirty && !v.phone.required">{{ t('vtramit', 'This field is required') }}</span>
							<span v-else-if="v.phone.$dirty && !v.phone.phoneValid">{{ t('vtramit', 'Phone number is not valid') }}</span>
						</div>

						<!-- Email ---------------------------------------------------- -->
						<label  :class="{
							'field-required': selectedProcedureType.requireStakeholderEmail && !bypassRequired
						}">{{ t('vtramit', 'Email') }}<span v-if="selectedProcedureType.requireStakeholderEmail && !bypassRequired"> *</span>
							<input data-test="email" v-model="v.email.$model"
								type="text"
								:placeholder="t('vtramit', 'Email')"
								@input="v.email.$touch();"
								@blur="v.email.$touch();">
						</label>
						<div class="error_message">
							<span v-if="selectedProcedureType.requireStakeholderEmail && !bypassRequired && v.email.$dirty && !v.email.required">{{ t('vtramit', 'This field is required') }}</span>
							<span v-else-if="v.email.$dirty && !v.email.email">{{ t('vtramit', 'Email field is not valid') }}</span>
						</div>
					</div>
				</div>

				<div class="d-flex justify-end mb">
					<!-- <Actions>
						<ActionButton icon="icon-add" @click.stop="clickAddNewStakeholder">
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
						<input  data-test="save" type="button"
							class="primary"
							:value="t('vtramit', 'Save')"
							:disabled="!formReadyForSubmit"
							@click="clickCreate">
					</div>
				</div>
			</template>
		</div>
	</AppSidebar>
</template>

<script>
/**
 * @module components/procedures/ProceduresCreate
 *
 * Component to create one procedure
 *
 */
import moment from '@nextcloud/moment'
import TopicSelector from '../fields/TopicSelector.vue'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import MessageManager from '../../mixins/messageManager.mixin.js'
import { required, email } from 'vuelidate/lib/validators/index.js'
import { AppSidebar, DatetimePicker, Actions, ActionButton } from '@nextcloud/vue'

const phoneValid = (value) => !value || value.replace(/\D/g, '').length >= 7

export default {
	name: 'ProceduresCreate',
	components: {
		TopicSelector,
		AppSidebar,
		DatetimePicker,
		Actions,
		ActionButton,
	},
	mixins: [ComponentStatus, MessageManager],
	data: () => ({
		firstFormClick: true,

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

		editStakeholders: [],
		editIsMainStakeholder: null,
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
			if (this.selectedProcedureType.requireAssignedGroup && this.allowAssignGroup) {
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
		groups() {
			return this.$store.getters['procedureTypes/readAllowedGroupsAll']
		},
		users() {
			return this.$store.getters['procedureTypes/readAllowedUsersByGroupId'](this.editAssignedGroupId)
		},
		channels() {
			return this.$store.getters['procedureTypes/readAllowedChannelsAll']
		},
		allowCreate() {
			return this.$store.getters['procedureTypes/allowCreate']
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
		procedureTypesCreate() {
			return this.$store.getters['procedureTypes/readAllCreate']
		},
		selectedProcedureType() {
			return this.$store.getters['procedureTypes/readById'](this.editProcedureTypeId)
		},
	},
	watch: {
		groups(newValue) {
			if (newValue.length === 1) {
				this.editAssignedGroupId = newValue[0].id
			}
		},
		users(newValue) {
			if (newValue.length === 1) {
				this.editAssignedUserId = newValue[0].id
			}
		},
		channels(newValue) {
			if (newValue.length === 1) {
				this.editAttendanceChannelId = newValue[0].id
			}
		},
	},
	created() {
		// Adding 1 initial stakeholder
		const tempStakeholder = this.newStakeHolder()
		tempStakeholder.isMain = true
		this.editIsMainStakeholder = tempStakeholder.isMainId
		this.editStakeholders.push(tempStakeholder)
	},
	methods: {
		newStakeHolder() {
			return {
				isMainId: `${Date.now()}_${Math.floor(Math.random() * 1000)}`,
				personalId: null,
				name: null,
				phone: null,
				email: null,
				isMain: false,
			}
		},
		changedProcedureType() {
			this.$store
				.dispatch('procedureTypes/readProcedureTypeInfoById', this.editProcedureTypeId)
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
			this.editStakeholders.push(this.newStakeHolder())
		},
		clickRemoveStakeholder(index) {
			this.editStakeholders.splice(index, 1)
		},
		clickCreate() {
			if (this.firstFormClick) {
				this.firstFormClick = false
				this.$v.$touch()
			}
			this.$nextTick(() => {
				if (!this.$v.$invalid) {
					this.sendCreate()
				}
			})
		},
		sendCreate() {
			this.setStatusCreating()
			const payload = {
				procedureTypeId: this.editProcedureTypeId,
				externalId: this.editExternalId,
				assignedGroupId: this.editAssignedGroupId,
				assignedUserId: this.editAssignedUserId,
				attendanceChannel: this.editAttendanceChannelId,
				topic: this.editTopic,
				comments: this.editComments,
			}
			if (this.editAppointmentDate) {
				payload.appointmentDate = moment(this.editAppointmentDate).local().format('YYYY-MM-DD HH:mm:00')
			}
			if (this.editDeadlineDate) {
				payload.deadlineDate = moment(this.editDeadlineDate).local().format('YYYY-MM-DD HH:mm:00')
			}
			// As we cannot pass the procedure id after the Promise.all we store
			// a "global" reference here
			let newProcedureId = ''

			// We will first create the procedure itself and then all the
			// stakeholders with an array of api call promises
			this.$store
				.dispatch('procedures/create', payload)
				.then((result) => {
					this.manageMessage(result)

					if (result.result === 'OK' && result.entity.id) {
						newProcedureId = result.entity.id
						return result.entity.id
					}

					// Throw an exception to be ignored because we have already managed the messages to be shown from the server
					throw new Error('IGNORE')
				})
				.then((procedureId) => {
					const newStakeholders = []
					for (const element of this.editStakeholders) {
						const tempStakeholder = {
							procedureId,
							personalId: element.personalId,
							name: element.name,
							phone: element.phone,
							email: element.email,
						}
						if (this.editIsMainStakeholder === element.isMainId) {
							tempStakeholder.isMain = true
						}
						newStakeholders.push(tempStakeholder)
					}
					return this.$store.dispatch('stakeholders/createBatch', newStakeholders)
				})
				.then((result) => {
					// Clean current errors and manage new messages
					this.$v.$touch()
					this.manageMessage(result)

					this.$store.commit('appStatus/setForceProcedureListRefresh')
					this.$router.push({
						name: 'procedures-edit',
						params: {
							id: newProcedureId,
						},
					})
				})
				.catch((error) => {
					if (error.message !== 'IGNORE') {
						// Setting the appropriate error markers from the server response
						this.$v.$touch()
						this.manageException(error, t('vtramit', 'Could not create the procedure'))
					}
				})
		},
	},
}
</script>
