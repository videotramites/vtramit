<template>
	<div class="section" data-test="procedures">
		<!-- Section header with expand button. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'Procedure types') }}</h2>
			<Actions>
				<ActionLink icon="icon-add"
					href="#"
					:close-after-click="true"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new procedure type') }}
				</ActionLink>
			</Actions>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(procedureType, index) in itemList"
				:key="procedureType.id"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': procedureType.isExpanded
				}">
				<!-- Item title -->
				<div class="collapsible-header" @click="toggleItem(procedureType, $event)">
					<Actions>
						<ActionButton :icon="procedureType.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ procedureType.name }}
					</div>
				</div>
				<!-- Items form -->
				<div class="item-content" :hidden="!procedureType.isExpanded">
					<div class="field-item">
						<label :for="getElementId('name', index)">{{ t('vtramit', 'Name') }}</label>
						<input v-model="procedureType.name"
							:id="getElementId('name', index)"
							type="text"
							@input="procedureType.isChanged = true"
							@blur="procedureType.isChanged = true">
					</div>
					<!-- Rgpd Options -->
					<div class="field-item">
						<label>{{ t('vtramit', 'RGPD Options') }}</label>
						<TwoColumnsSelector :optionsList="rgpds"
							v-model="procedureType.rgpdOptions"
							wrapperClass="rgpd-selector"
							:sort="true"
							@changed="procedureType.isChanged = true" />
					</div>
					<!-- Attendance Channels -->
					<div class="field-item">
						<label>{{ t('vtramit', 'Allowed Attendance Channels') }}</label>
						<TwoColumnsSelector :optionsList="acceptanceChannels"
							v-model="procedureType.allowedAttendanceChannels"
							wrapperClass="attendance-channels-selector"
							@changed="procedureType.isChanged = true" />
					</div>
					<!-- Uploads -->
					<fieldset :disabled="!procedureType.allowUpload">
						<legend>
							<div class="field-item field-checkbox">
								<label :for="getElementId('allowUpload', index)">{{ t('vtramit', 'Allow Upload') }}</label>
								<input v-model="procedureType.allowUpload"
									:id="getElementId('allowUpload', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</legend>
						<div class="help">
							<p>
								{{ t('vtramit', 'Define the folder name to be created in the procedure to allow the stakeholder to upload files.') }}
							</p>
							<p>
								{{ t('vtramit', 'If you want to use the same folder for uploads and downloads, set the same folder name in both settings.') }}
							</p>
						</div>
						<div class="field-item">
							<label :for="getElementId('uploadsFolder', index)">{{ t('vtramit', 'Uploads Folder') }}</label>
							<input v-model="procedureType.uploadsFolder"
								:id="getElementId('uploadsFolder', index)"
								type="text"
								@input="procedureType.isChanged = true"
								@blur="procedureType.isChanged = true">
						</div>
					</fieldset>
					<!-- Downloads -->
					<fieldset :disabled="!procedureType.allowDownload">
						<legend>
							<div class="field-item field-checkbox">
								<label :for="getElementId('allowDownload', index)">{{ t('vtramit', 'Allow Download') }}</label>
								<input v-model="procedureType.allowDownload"
									:id="getElementId('allowDownload', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</legend>
						<div class="help">
							<p>
								{{ t('vtramit', 'Define the folder name to be created in the procedure to allow the stakeholder to download files.') }}
							</p>
							<p>
								{{ t('vtramit', 'If you want to use the same folder for uploads and downloads, set the same folder name in both settings.') }}
							</p>
						</div>
						<div class="field-item">
							<label :for="getElementId('downloadsFolder', index)">{{ t('vtramit', 'Downloads Folder') }}</label>
							<input v-model="procedureType.downloadsFolder"
								:id="getElementId('downloadsFolder', index)"
								type="text"
								@input="procedureType.isChanged = true"
								@blur="procedureType.isChanged = true">
						</div>
					</fieldset>
					<!-- Notifications -->
					<fieldset>
						<legend>{{ t('vtramit', 'Notifications') }}</legend>
						<div class="grid-2-columns">
							<div class="field-item field-checkbox">
								<label :for="getElementId('notifyStakeholder', index)">{{ t('vtramit', 'Notify Stakeholder') }}</label>
								<input v-model="procedureType.notifyStakeholder"
									:id="getElementId('notifyStakeholder', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item field-checkbox">
								<label :for="getElementId('notifyStaff', index)">{{ t('vtramit', 'Notify Staff') }}</label>
								<input v-model="procedureType.notifyStaff"
									:id="getElementId('notifyStaff', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</div>
					</fieldset>
					<!-- Signature -->
					<fieldset :disabled="!procedureType.allowSignature">
						<legend>
							<div class="field-item field-checkbox">
								<label :for="getElementId('allowSignature', index)">{{ t('vtramit', 'Allow Signature') }}</label>
								<input v-model="procedureType.allowSignature"
									:id="getElementId('allowSignature', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</legend>
						<div class="help">
							<p>
								{{ t('vtramit', 'Define the signature folders names to be created in the procedure to allow the staff to upload documents to be signed and the stakeholder to view and sign the documents.') }}
								<em>{{ t('vtramit', 'Signature folders are required.') }}</em>
							</p>
							<p>
								{{ t('vtramit', 'Signature Limit can have the following values') }}:
							</p>
							<ul>
								<li>
									{{ t('vtramit', '-1 (or any negative value)') }}: {{ t('vtramit', 'Unlimited signatures') }}
								</li>
								<li>
									{{ t('vtramit', '0 (zero)') }}: {{ t('vtramit', 'No signatures allowed; it has the same effect as leaving the "Allow Signature" option disabled') }}
								</li>
								<li>
									{{ t('vtramit', 'Any positive value') }}: {{ t('vtramit', 'The maximum number of signatures allowed in a file') }}
								</li>
							</ul>
						</div>
						<div class="item-list grid-2-columns">
							<div class="field-item">
								<label :for="getElementId('signatureUploadsFolder', index)">{{ t('vtramit', 'Signature Uploads Folder') }}</label>
								<input v-model="procedureType.signatureUploadsFolder"
									:id="getElementId('signatureUploadsFolder', index)"
									type="text"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
								<div class="help-inline">{{ t('vtramit', 'Folder where the user will upload their files. You can define multilevel folders like "Input/Signature".') }}</div>
							</div>
							<div class="field-item">
								<label :for="getElementId('signatureDownloadsFolder', index)">{{ t('vtramit', 'Signature Downloads Folder') }}</label>
								<input v-model="procedureType.signatureDownloadsFolder"
									:id="getElementId('signatureDownloadsFolder', index)"
									type="text"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
								<div class="help-inline">{{ t('vtramit', 'Folder where the user will get their files. You can define multilevel folders like "Output/Signature".') }}</div>
							</div>
							<div class="field-item">
								<label :for="getElementId('signatureLimit', index)">{{ t('vtramit', 'Signature Limit') }}</label>
								<input v-model="procedureType.signatureLimit"
									:id="getElementId('signatureLimit', index)"
									type="number"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</div>
						<!-- Time Frame -->
						<div class="grid-2-columns">
							<div class="field-item">
								<label :for="getElementId('signatureTimeframeSign', index)">{{ t('vtramit', 'Time Frame to Sign') }}</label>
								<input v-model="procedureType.signatureTimeframeSign"
									:id="getElementId('signatureTimeframeSign', index)"
									type="number"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
								<div class="help-inline">{{ t('vtramit', 'In hours; use 0 for no expiry') }}</div>
							</div>
							<div class="field-item">
								<label :for="getElementId('signatureTimeframeDownload', index)">{{ t('vtramit', 'Time Frame to Download') }}</label>
								<input v-model="procedureType.signatureTimeframeDownload"
									:id="getElementId('signatureTimeframeDownload', index)"
									type="number"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
								<div class="help-inline">{{ t('vtramit', 'In hours; use 0 for no expiry') }}</div>
							</div>
						</div>
					</fieldset>
					<!-- Videocall -->
					<fieldset :disabled="!procedureType.allowVideo">
						<legend>
							<div class="field-item field-checkbox">
								<label :for="getElementId('allowVideo', index)">{{ t('vtramit', 'Allow Video') }}</label>
								<input v-model="procedureType.allowVideo"
									:id="getElementId('allowVideo', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</legend>
						<div class="grid-2-columns">
							<div class="field-item">
								<label :for="getElementId('jitsiStakeholderUrl', index)">{{ t('vtramit', 'Jitsi Stakeholder Url') }}</label>
								<input v-model="procedureType.jitsiStakeholderUrl"
									:id="getElementId('jitsiStakeholderUrl', index)"
									type="text"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item span-2-rows">
								<TokensPopup float="right" :icon=true />
								<label :for="getElementId('jitsiStakeholderJwtPayload', index)">{{ t('vtramit', 'Jitsi Stakeholder Jwt Payload') }}</label>
								<textarea v-model="procedureType.jitsiStakeholderJwtPayload"
									:id="getElementId('jitsiStakeholderJwtPayload', index)"
									style="min-height: 110px;"
									class="json-validate"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true" />
							</div>
							<div class="field-item">
								<label :for="getElementId('jitsiStakeholderJwtSecret', index)">{{ t('vtramit', 'Jitsi Stakeholder Jwt Secret') }}</label>
								<input v-model="procedureType.jitsiStakeholderJwtSecret"
									:id="getElementId('jitsiStakeholderJwtSecret', index)"
									type="text"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item">
								<label :for="getElementId('jitsiStaffUrl', index)">{{ t('vtramit', 'Jitsi Staff Url') }}</label>
								<input v-model="procedureType.jitsiStaffUrl"
									:id="getElementId('jitsiStaffUrl', index)"
									type="text"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item span-2-rows">
								<TokensPopup float="right" :icon=true />
								<label :for="getElementId('jitsiStaffJwtPayload', index)">{{ t('vtramit', 'Jitsi Staff Jwt Payload') }}</label>
								<textarea v-model="procedureType.jitsiStaffJwtPayload"
									:id="getElementId('jitsiStaffJwtPayload', index)"
									style="min-height: 110px;"
									class="json-validate"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true" />
							</div>
							<div class="field-item">
								<label :for="getElementId('jitsiStaffJwtSecret', index)">{{ t('vtramit', 'Jitsi Staff Jwt Secret') }}</label>
								<input v-model="procedureType.jitsiStaffJwtSecret"
									:id="getElementId('jitsiStaffJwtSecret', index)"
									type="text"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</div>
					</fieldset>
					<!-- Deck -->
					<fieldset :disabled="!procedureType.deckIntegration">
						<legend>
							<div class="field-item field-checkbox">
								<label :for="getElementId('deckIntegration', index)">{{ t('vtramit', 'Deck Integration') }}</label>
								<input v-model="procedureType.deckIntegration"
									:id="getElementId('deckIntegration', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</legend>
						<div class="grid-2-columns">
							<div class="field-item">
								<label :for="getElementId('deckBoard', index)">{{ t('vtramit', 'Deck Board') }}</label>
								<input v-model="procedureType.deckBoard"
									:id="getElementId('deckBoard', index)"
									type="number"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item">
								<label :for="getElementId('deckStack', index)">{{ t('vtramit', 'Deck Stack') }}</label>
								<input v-model="procedureType.deckStack"
									:id="getElementId('deckStack', index)"
									type="number"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</div>
					</fieldset>
					<!-- Required fields -->
					<fieldset>
						<legend>{{ t('vtramit', 'Required fields') }}</legend>
						<div  class="grid-3-columns">
							<div class="field-item field-checkbox">
								<label :for="getElementId('requireAssignedGroup', index)">{{ t('vtramit', 'Require Assigned Group') }}</label>
								<input v-model="procedureType.requireAssignedGroup"
									:id="getElementId('requireAssignedGroup', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item field-checkbox">
								<label :for="getElementId('requireExternalId', index)">{{ t('vtramit', 'Require External Id') }}</label>
								<input v-model="procedureType.requireExternalId"
									:id="getElementId('requireExternalId', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item field-checkbox">
								<label :for="getElementId('requireAppointmentDate', index)">{{ t('vtramit', 'Require Appointment Date') }}</label>
								<input v-model="procedureType.requireAppointmentDate"
									:id="getElementId('requireAppointmentDate', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item field-checkbox">
								<label :for="getElementId('requireDeadlineDate', index)">{{ t('vtramit', 'Require Deadline Date') }}</label>
								<input v-model="procedureType.requireDeadlineDate"
									:id="getElementId('requireDeadlineDate', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item field-checkbox">
								<label :for="getElementId('requireStakeholderPid', index)">{{ t('vtramit', 'Require Stakeholder Personal ID') }}</label>
								<input v-model="procedureType.requireStakeholderPid"
									:id="getElementId('requireStakeholderPid', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item field-checkbox">
								<label :for="getElementId('requireStakeholderPhone', index)">{{ t('vtramit', 'Require Stakeholder Phone') }}</label>
								<input v-model="procedureType.requireStakeholderPhone"
									:id="getElementId('requireStakeholderPhone', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item field-checkbox">
								<label :for="getElementId('requireStakeholderEmail', index)">{{ t('vtramit', 'Require Stakeholder Email') }}</label>
								<input v-model="procedureType.requireStakeholderEmail"
									:id="getElementId('requireStakeholderEmail', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
							<div class="field-item field-checkbox">
								<label :for="getElementId('requireAttendanceChannel', index)">{{ t('vtramit', 'Require Attendance Channel') }}</label>
								<input v-model="procedureType.requireAttendanceChannel"
									:id="getElementId('requireAttendanceChannel', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
							</div>
						</div>
					</fieldset>
					<!-- Attendant portal -->
					<fieldset>
						<legend>{{ t('vtramit', 'Attendant portal') }}</legend>
						<div class="grid-2-columns">
							<div class="field-item field-checkbox">
								<label :for="getElementId('allowRemoteCommands', index)">{{ t('vtramit', 'Allow remote commands') }}</label>
								<input v-model="procedureType.attendantPortalProperties.allowRemoteCommands"
									:id="getElementId('allowRemoteCommands', index)"
									type="checkbox"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
								<div class="help">
									{{ t('vtramit', 'If enabled, the public portal hides the right panel for downloads/uploads/signature management.') }}
								</div>
							</div>
						</div>
					</fieldset>
					<!-- Maintenances tasks -->
					<fieldset>
						<legend>{{ t('vtramit', 'Maintenances tasks') }}</legend>
						<div class="help">
							<p>
								{{ t('vtramit', 'Define the time elapsed in hours to proceed with each maintenance task.') }}
							</p>
							<div class="block">
								<div><strong>{{ t('vtramit', 'Procedure Anonymization') }}</strong></div>
								<div class="ms-4">
									<p>{{ t('vtramit', 'This procedures takes 2 steps:') }}</p>
									<ul>
										<li>
											{{ t('vtramit', 'On the first step, it set status to "Expired" for any procedure in status "Initializing", "Created" or "Pendant"which appointment date and modification date are higher than the time defined in "Procedure Anonymization Period".') }}
										</li>
										<li>
											{{ t('vtramit', 'On a second step, anonymizes any procedures non-anonymized procedure with status "Expired", "Duplicated" or "Cancelled".') }}
										</li>
									</ul>
									<p>{{ t('vtramit', 'Procedures can be distinguished for the stakeholders\' emails which has the "%@anonymous.com" format.') }}</p>
								</div>
							</div>
						</div>
						<div class="item-list grid-3-columns">
							<div class="field-item">
								<label :for="getElementId('procedureAnonymizationPeriod', index)">{{ t('vtramit', 'Procedure Anonymization Period') }}</label>
								<input v-model="procedureType.procedureAnonymizationPeriod"
									:id="getElementId('procedureAnonymizationPeriod', index)"
									type="number"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
								<div class="help-inline">{{ t('vtramit', 'Use 0 for no anonymization.') }}</div>
							</div>
							<div class="field-item">
								<label :for="getElementId('filesDeletionPeriod', index)">{{ t('vtramit', 'Files Deletion Period') }}</label>
								<input v-model="procedureType.filesDeletionPeriod"
									:id="getElementId('filesDeletionPeriod', index)"
									type="number"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
								<div class="help-inline">{{ t('vtramit', 'Use 0 to avoid files deletion.') }}</div>
							</div>
							<div class="field-item">
								<label :for="getElementId('procedureDeletionPeriod', index)">{{ t('vtramit', 'Procedure Deletion Period') }}</label>
								<input v-model="procedureType.procedureDeletionPeriod"
									:id="getElementId('procedureDeletionPeriod', index)"
									type="number"
									@input="procedureType.isChanged = true"
									@blur="procedureType.isChanged = true">
								<div class="help-inline">{{ t('vtramit', 'Use 0 to avoid procedure deletion.') }}</div>
							</div>
						</div>
					</fieldset>
					<!-- Groups -->
					<fieldset v-if="!procedureType.isNew" class="section-no-borders hide-legend">
						<ProcedureTypeGroups :procedure-type-id="procedureType.id" />
					</fieldset>
					<!-- Signature Buttons -->
					<fieldset v-if="!procedureType.isNew" class="section-no-borders hide-legend">
						<SignatureButtons :procedure-type-id="procedureType.id" />
					</fieldset>
					<!-- Individual item's CRUD buttons -->
					<div class="d-flex justify-end form-actions">
						<button v-if="!procedureType.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Delete'),
								autoHide: false,
								html: true
							}"
							class="btn-delete"
							@click="clickDeleteItem(procedureType)">
							<span class="icon icon-delete" />
							<span class="label">
								{{ t('vtramit', 'Delete') }}
							</span>
						</button>
						<button v-if="procedureType.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Cancel'),
								autoHide: false,
								html: true
							}"
							class="btn-delete"
							@click="clickCancelItem(index)">
							<span class="icon icon-delete" />
							<span class="label">
								{{ t('vtramit', 'Cancel') }}
							</span>
						</button>
						<div class="flex-grow-1" />
						<button v-if="procedureType.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Create'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !procedureType.isChanged"
							class="primary btn-save"
							@click="clickCreateItem(procedureType)">
							<span class="icon icon-upload" />
							<span class="label">
								{{ t('vtramit', 'Create') }}
							</span>
						</button>
						<button v-if="!procedureType.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Update'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !procedureType.isChanged"
							class="primary btn-save"
							@click="clickUpdateItem(procedureType)">
							<span class="icon icon-upload-white" />
							<span class="label">
								{{ t('vtramit', 'Update') }}
							</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * @module components/settings/ProcedureTypes
 */
import TwoColumnsSelector from '../ui/TwoColumnsSelector.vue'
import TokensPopup from '../ui/TokensPopup.vue'
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import ProcedureTypeGroups from './ProcedureTypeGroups.vue'
import SignatureButtons from './SignatureButtons.vue'
import { Actions, ActionButton, ActionLink } from '@nextcloud/vue'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'ProcedureTypes',
	components: {
		TwoColumnsSelector,
		TokensPopup,
		Actions,
		ActionButton,
		ActionLink,
		ProcedureTypeGroups,
		SignatureButtons,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/procedure-type',
		urlForInsert: 'apps/vtramit/web/2.0/procedure-type',
		urlForUpdate: 'apps/vtramit/web/2.0/procedure-type',
		urlForDelete: 'apps/vtramit/web/2.0/procedure-type/%id',
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
		acceptanceChannels() {
			return this.$store.getters['acceptanceChannels/readAll']
		},
		rgpds() {
			return this.$store.getters['rgpds/readAll']
		},
	},
	created() {
		this.loadFormData()
			.then(() => {
				this.normalizeItems()
			})
			.then(() => {
				return this.$store.dispatch('acceptanceChannels/readAll')
			})
			.then(() => {
				return this.$store.dispatch('rgpds/readAll')
			})
	},
	methods: {
		// ------------------------------------------------------------------------
		// Generate a unique element identifier given the name of the element and
		// the index offset in the array of elements
		//
		getElementId(name, index) {
			return 'procedureType_' + name + '_' + index;
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				id: null,
				name: '',
				rgpdOptions: [],
				allowUpload: false,
				uploadsFolder: '',
				allowDownload: false,
				downloadsFolder: '',
				allowSignature: false,
				signatureUploadsFolder: '',
				signatureDownloadsFolder: '',
				signatureTimeframeSign: 0,
				signatureTimeframeDownload: 0,
				signatureLimit: 0,
				allowVideo: false,
				jitsiStakeholderUrl: '',
				jitsiStakeholderJwtSecret: '',
				jitsiStakeholderJwtPayload: '',
				jitsiStaffUrl: '',
				jitsiStaffJwtSecret: '',
				jitsiStaffJwtPayload: '',
				allowedAttendanceChannels: [],
				notifyStakeholder: false,
				notifyStaff: false,
				requireAssignedGroup: false,
				requireExternalId: false,
				requireAppointmentDate: false,
				requireDeadlineDate: false,
				requireStakeholderPid: false,
				requireStakeholderPhone: false,
				requireStakeholderEmail: false,
				requireAttendanceChannel: false,
				attendantPortalProperties: { allowRemoteCommands: false },
				deckIntegration: false,
				deckBoard: '',
				deckStack: '',
				procedureAnonymizationPeriod: 0,
				filesDeletionPeriod: 0,
				procedureDeletionPeriod: 0,
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			const forApi = {
				name: item.name,
				allowUpload: item.allowUpload,
				uploadsFolder: item.uploadsFolder,
				allowDownload: item.allowDownload,
				downloadsFolder: item.downloadsFolder,
				allowSignature: item.allowSignature,
				signatureLimit: item.signatureLimit,
				signatureUploadsFolder: item.signatureUploadsFolder,
				signatureDownloadsFolder: item.signatureDownloadsFolder,
				allowVideo: item.allowVideo,
				jitsiStakeholderUrl: item.jitsiStakeholderUrl,
				jitsiStakeholderJwtSecret: item.jitsiStakeholderJwtSecret,
				jitsiStakeholderJwtPayload: item.jitsiStakeholderJwtPayload,
				jitsiStaffUrl: item.jitsiStaffUrl,
				jitsiStaffJwtSecret: item.jitsiStaffJwtSecret,
				jitsiStaffJwtPayload: item.jitsiStaffJwtPayload,
				allowedAttendanceChannels: item.allowedAttendanceChannels,
				notifyStakeholder: item.notifyStakeholder,
				notifyStaff: item.notifyStaff,
				requireAssignedGroup: item.requireAssignedGroup,
				requireExternalId: item.requireExternalId,
				requireAppointmentDate: item.requireAppointmentDate,
				requireDeadlineDate: item.requireDeadlineDate,
				requireStakeholderPid: item.requireStakeholderPid,
				requireStakeholderPhone: item.requireStakeholderPhone,
				requireStakeholderEmail: item.requireStakeholderEmail,
				requireAttendanceChannel: item.requireAttendanceChannel,
				attendantPortalProperties: { ...(item.attendantPortalProperties || {}) },
				deckIntegration: item.deckIntegration,
				deckBoard: item.deckBoard,
				deckStack: item.deckStack,
				procedureAnonymizationPeriod: item.procedureAnonymizationPeriod,
				filesDeletionPeriod: item.filesDeletionPeriod,
				procedureDeletionPeriod: item.procedureDeletionPeriod,
				rgpdOptions: item.rgpdOptions,
			}

			return forApi
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to update the item.
		//
		prepareItemForUpdate(item) {
			const forApi = {
				id: item.id,
				name: item.name,
				allowUpload: item.allowUpload,
				uploadsFolder: item.uploadsFolder,
				allowDownload: item.allowDownload,
				downloadsFolder: item.downloadsFolder,
				allowSignature: item.allowSignature,
				signatureLimit: item.signatureLimit,
				signatureUploadsFolder: item.signatureUploadsFolder,
				signatureDownloadsFolder: item.signatureDownloadsFolder,
				allowVideo: item.allowVideo,
				jitsiStakeholderUrl: item.jitsiStakeholderUrl,
				jitsiStakeholderJwtSecret: item.jitsiStakeholderJwtSecret,
				jitsiStakeholderJwtPayload: item.jitsiStakeholderJwtPayload,
				jitsiStaffUrl: item.jitsiStaffUrl,
				jitsiStaffJwtSecret: item.jitsiStaffJwtSecret,
				jitsiStaffJwtPayload: item.jitsiStaffJwtPayload,
				allowedAttendanceChannels: item.allowedAttendanceChannels,
				notifyStakeholder: item.notifyStakeholder,
				notifyStaff: item.notifyStaff,
				requireAssignedGroup: item.requireAssignedGroup,
				requireExternalId: item.requireExternalId,
				requireAppointmentDate: item.requireAppointmentDate,
				requireDeadlineDate: item.requireDeadlineDate,
				requireStakeholderPid: item.requireStakeholderPid,
				requireStakeholderPhone: item.requireStakeholderPhone,
				requireStakeholderEmail: item.requireStakeholderEmail,
				requireAttendanceChannel: item.requireAttendanceChannel,
				attendantPortalProperties: { ...(item.attendantPortalProperties || {}) },
				deckIntegration: item.deckIntegration,
				deckBoard: item.deckBoard,
				deckStack: item.deckStack,
				procedureAnonymizationPeriod: item.procedureAnonymizationPeriod,
				filesDeletionPeriod: item.filesDeletionPeriod,
				procedureDeletionPeriod: item.procedureDeletionPeriod,
				rgpdOptions: item.rgpdOptions,
			}

			return forApi
		},
		normalizeItems() {
			if (!Array.isArray(this.itemList)) return
			this.itemList = this.itemList.map(it => {
				if (!it.attendantPortalProperties || typeof it.attendantPortalProperties !== 'object' || Array.isArray(it.attendantPortalProperties)) {
					this.$set(it, 'attendantPortalProperties', {})
				}
				if (typeof it.attendantPortalProperties.allowRemoteCommands === 'undefined') {
					this.$set(it.attendantPortalProperties, 'allowRemoteCommands', false)
				}
				return it
			})
		},
		// ------------------------------------------------------------------------
		// Indicates wether the data is right for creation.
		//
		validateBeforeCreate(item) {
			return this.validate(item)
		},
		// ------------------------------------------------------------------------
		// Indicates wether the data is right for update.
		//
		validateBeforeUpdate(item) {
			return this.validate(item)
		},
		// ------------------------------------------------------------------------
		// Validates form.
		//
		validate(item) {
			if (!item) {
				return false
			}

			if (!this.isItemJsonValid(item, 'jitsiStakeholderJwtPayload')) {
				showError(t('vtramit', 'Jitsi Stakeholder Jwt Payload has no valid JSON'))
				return false
			}

			if (!this.isItemJsonValid(item, 'jitsiStaffJwtPayload')) {
				showError(t('vtramit', 'Jitsi Staff Jwt Payload has no valid JSON'))
				return false
			}

			return true
		},
		// ------------------------------------------------------------------------
		// Checks whether the textarea bound to the given field has valid JSON.
		//
		isItemJsonValid(item, elName) {
			if (!this.$el || !Array.isArray(this.itemList)) {
				return true
			}

			const index = this.itemList.indexOf(item)
			if (index === -1) {
				return true
			}

			const elementId = this.getElementId(elName, index)
			const escapedId = typeof CSS !== 'undefined' && typeof CSS.escape === 'function'
				? CSS.escape(elementId)
				: elementId.replace(/([ #;?%&,.+*~':"!^$[\]()=>|/@])/g, '\\$1')

			const el = typeof this.$el.querySelector === 'function'
				? this.$el.querySelector(`#${escapedId}`)
				: null

			if (!el || !el.dataset || typeof el.dataset.jsonValid === 'undefined') {
				return true
			}

			return el.dataset.jsonValid !== 'false'
		},
	},
}
</script>
