<template>
	<div class="section">
		<!-- Section header. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'Groups') }}</h2>
			<Actions>
				<ActionButton icon="icon-add"
					href="#"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new group template') }}
				</ActionButton>
			</Actions>
		</div>
		<div class="help">
			<p>
				{{ t('vtramit', 'Define all groups that can manage this procedure.') }}
			</p>
			<p>
				{{ t('vtramit', 'If you leave an empty group name, the "Stakeholder template" and "Staff template" will be used for the groups which has not defined their own templates.') }}
			</p>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(procedureTypeGroup, index) in itemList"
				:key="procedureTypeGroup.id"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': procedureTypeGroup.isExpanded
				}">
				<!-- Item header -->
				<div class="collapsible-header" @click="toggleItem(procedureTypeGroup, $event)">
					<Actions>
						<ActionButton :icon="procedureTypeGroup.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ getGroupLabel(procedureTypeGroup) }}
					</div>
				</div>
				<!-- Items form -->
				<div class="item-content"
					:hidden="!procedureTypeGroup.isExpanded"
					:class="{ 'pending-changes': hasPendingChanges(procedureTypeGroup) }">
					<div class="grid-2-columns-with-actions">
						<div class="grid-2-columns">

							<div class="grid-2-columns">
								<div class="field-item">
									<label :for="getElementId('group', index)">{{ t('vtramit', 'Group') }}</label>
									<select v-model="procedureTypeGroup.group"
										:id="getElementId('group', index)"
										class="select"
										:class="{ error: !isGroupValid(procedureTypeGroup) }"
										@change="procedureTypeGroup.isChanged = true">
										<option v-for="group in groupsOptions"
											:key="group.id"
											:value="group.id"
											:class="{ 'missing': group.missing }">
											{{ group.name }}
										</option>
									</select>
								</div>
								<div class="field-item">
									<label :for="getElementId('vcConfirmationForm', index)">{{ t('vtramit', 'Confirmation Form') }}</label>
									<input v-model="procedureTypeGroup.vcConfirmationForm"
										:id="getElementId('vcConfirmationForm', index)"
										type="text"
										@input="procedureTypeGroup.isChanged = true">
								</div>
								<!-- DEPRECATED: Remove with EmailTemplates system -->
								<div class="field-item"
									v-if="showStakeholderLegacy(procedureTypeGroup)">
									<label :for="getElementId('notifyStakeholderTemplateId', index)">{{ t('vtramit', 'Stakeholder Template') }}</label>
									<select v-model="procedureTypeGroup.notifyStakeholderTemplateId"
										:id="getElementId('notifyStakeholderTemplateId', index)"
										class="select"
										:class="{ error: !isNotifyStakeholderTemplateId(procedureTypeGroup) }"
										@change="procedureTypeGroup.isChanged = true">
										<option value="0">--- Do not send e-mail ---</option>
										<option v-for="template in mailTemplateOptions"
											:key="template.id"
											:value="template.id"
											:class="{ missing: template.missing }">
											{{ template.name }}
										</option>
									</select>
								</div>
								<div class="field-item"
									v-if="showStaffLegacy(procedureTypeGroup)">
									<label :for="getElementId('notifyStaffTemplateId', index)">{{ t('vtramit', 'Staff Template') }}</label>
									<select v-model="procedureTypeGroup.notifyStaffTemplateId"
										:id="getElementId('notifyStaffTemplateId', index)"
										class="select"
										:class="{ error: !isNotifyStaffTemplateId(procedureTypeGroup) }"
										@change="procedureTypeGroup.isChanged = true">
										<option value="0">--- Do not send e-mail ---</option>
										<option v-for="template in mailTemplateOptions"
											:key="template.id"
											:value="template.id"
											:class="{ missing: template.missing }">
											{{ template.name }}
										</option>
									</select>
								</div>
								<!-- END DEPRECATED -->
								<div class="field-item field-checkbox">
									<label :for="getElementId('allowCreate', index)">{{ t('vtramit', 'Allow Create') }}</label>
									<input v-model="procedureTypeGroup.allowCreate"
										:id="getElementId('allowCreate', index)"
										type="checkbox"
										@change="procedureTypeGroup.isChanged = true">
								</div>
								<div class="field-item field-checkbox">
									<label :for="getElementId('allowAssignGroup', index)">{{ t('vtramit', 'Allow Assign Group') }}</label>
									<input v-model="procedureTypeGroup.allowAssignGroup"
										:id="getElementId('allowAssignGroup', index)"
										type="checkbox"
										@change="procedureTypeGroup.isChanged = true">
								</div>
								<div class="field-item field-checkbox">
									<label :for="getElementId('allowAssignUser', index)">{{ t('vtramit', 'Allow Assign User') }}</label>
									<input v-model="procedureTypeGroup.allowAssignUser"
										:id="getElementId('allowAssignUser', index)"
										type="checkbox"
										@change="procedureTypeGroup.isChanged = true">
								</div>
								<div class="field-item field-checkbox">
									<label :for="getElementId('bypassRequired', index)">{{ t('vtramit', 'Bypass Required') }}</label>
									<input v-model="procedureTypeGroup.bypassRequired"
										:id="getElementId('bypassRequired', index)"
										type="checkbox"
										@change="procedureTypeGroup.isChanged = true">

									<div class="help-inline">
										{{ t('vtramit', 'Assign carefully. All fields become optional for this group.') }}
									</div>
								</div>
							</div>

							<!-- Default view configuration -->
							<div class="grid-2-columns">
								<div class="field-item">
									<label :for="getElementId('defaultViewDesktopLeft', index)">{{ t('vtramit', 'Default view Desktop (Left)') }}</label>
									<select v-model="procedureTypeGroup.defaultViewDesktopLeft"
										:id="getElementId('defaultViewDesktopLeft', index)"
										@change="procedureTypeGroup.isChanged = true">
										<option value="">{{ t('vtramit', 'Auto') }}</option>
										<option value="videocall">{{ t('vtramit', 'Videocall') }}</option>
										<option value="documentation">{{ t('vtramit', 'Documentation') }}</option>
										<option value="rgpd">{{ t('vtramit', 'RGPD') }}</option>
										<option value="videocall-test">{{ t('vtramit', 'Videocall Test') }}</option>
									</select>
								</div>
								<div class="field-item">
									<label :for="getElementId('defaultViewDesktopRight', index)">{{ t('vtramit', 'Default view Desktop (Right)') }}</label>
									<select v-model="procedureTypeGroup.defaultViewDesktopRight"
										:id="getElementId('defaultViewDesktopRight', index)"
										@change="procedureTypeGroup.isChanged = true">
										<option value="">{{ t('vtramit', 'Auto') }}</option>
										<option value="uploads">{{ t('vtramit', 'Uploads') }}</option>
										<option value="downloads">{{ t('vtramit', 'Downloads') }}</option>
										<option value="signature">{{ t('vtramit', 'Signature') }}</option>
									</select>
								</div>
								<div class="field-item">
									<label :for="getElementId('defaultViewMobile', index)">{{ t('vtramit', 'Default view Mobile') }}</label>
									<select v-model="procedureTypeGroup.defaultViewMobile"
										:id="getElementId('defaultViewMobile', index)"
										@change="procedureTypeGroup.isChanged = true">
										<option value="">{{ t('vtramit', 'Auto') }}</option>
										<option value="videocall">{{ t('vtramit', 'Videocall') }}</option>
										<option value="videocall-test">{{ t('vtramit', 'Videocall Test') }}</option>
										<option value="documentation">{{ t('vtramit', 'Documentation') }}</option>
										<option value="uploads">{{ t('vtramit', 'Uploads') }}</option>
										<option value="downloads">{{ t('vtramit', 'Downloads') }}</option>
									</select>
								</div>
							</div>

							<div class="grid-2-columns">
								<div class="field-item multiple-selector">
									<label :for="getElementId('channel', index)">{{ t('vtramit', 'Stakeholder Message Templates') }}</label>
									<MultipleSelector
										:options="messageTemplateOptionsForStakeholder"
										layout="column"
										v-model="procedureTypeGroup.notifyStakeholderTemplate"
										:class="{ error: !isNotifyStakeholderTemplates(procedureTypeGroup) }"
										width="100%"
										@change="procedureTypeGroup.isChanged = true"
									/>
								</div>
								<div class="field-item multiple-selector">
									<label :for="getElementId('channel', index)">{{ t('vtramit', 'Staff Message Templates') }}</label>
									<MultipleSelector
										:options="messageTemplateOptionsForStaff"
										layout="column"
										v-model="procedureTypeGroup.notifyStaffTemplate"
										:class="{ error: !isNotifyStaffTemplates(procedureTypeGroup) }"
										width="100%"
										@change="procedureTypeGroup.isChanged = true"
									/>
								</div>
							</div>
						</div>
						<!-- Individual item's CRUD buttons -->
						<div class="grid-2-columns form-actions">
							<ActionSplit :actions="actionItems(procedureTypeGroup, index)" />
						</div>
					</div>
					<div v-if="!procedureTypeGroup.isNew" class="item-children">
						<ProcedureTypeProtocolSteps
							:procedure-type-id="procedureTypeId"
							:procedure-type-group-id="procedureTypeGroup.id" />
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * @module components/settings/ProcedureTypeGroups
 *
 * Component to edit the mail templates config ... old style, without stores
 * or much of Vue's proper reactivity :'-(
 *
 */
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import MultipleSelector from '../ui/MultipleSelector.vue'
import ProcedureTypeProtocolSteps from './ProcedureTypeProtocolSteps.vue'
import { showError } from '@nextcloud/dialogs'
import { Actions, ActionButton } from '@nextcloud/vue'
import ActionSplit from '../ui/ActionSplit.vue'

export default {
	name: 'ProcedureTypeGroups',
	components: {
		Actions,
		ActionButton,
		ActionSplit,
		MultipleSelector,
		ProcedureTypeProtocolSteps,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	props: {
		procedureTypeId: {
			type: Number,
			default: 0,
		},
	},
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/procedure-type/%id/groups',
		urlForInsert: 'apps/vtramit/web/2.0/procedure-type-group',
		urlForUpdate: 'apps/vtramit/web/2.0/procedure-type-group',
		urlForDelete: 'apps/vtramit/web/2.0/procedure-type-group/%id',
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
		groups() {
			return this.$store.getters['groups/readAll']
		},
		// ------------------------------------------------------------------------
		// Build the group options including any "orphan" (missing) selection found
		// on current items, so users can see and fix them.
		//
		groupsOptions() {
			const map = new Map(this.groups.map(g => [g.id, { ...g, missing: false }]))

			for (const it of this.itemList) {
				const id = it.group
				if (id && !map.has(id)) {
					map.set(id, {
						id,
						name: `${id} (${t('vtramit', 'missing!')})`,
						missing: true
					})
				}
			}

			return Array.from(map.values())
		},
		// ------------------------------------------------------------------------
		//
		messageTemplates() {
			return this.$store.getters['messageTemplates/readAll']
		},
		// ------------------------------------------------------------------------
		// Message channels from store to filter message templates by channel availability.
		//
		messageChannels() {
			return this.$store.getters['messageChannels/readAll']
		},
		// ------------------------------------------------------------------------
		// Build a quick lookup for message channels by id/key.
		//
		messageChannelMap() {
			const map = {}
			for (const c of this.messageChannels) {
				if (c?.id)  map[c.id]  = c
				if (c?.key) map[c.key] = c
			}
			return map
		},
		// ------------------------------------------------------------------------
		// Build the message template options for stakehloder from store and
		// include any "missing" ids currently selected by items, so users can see
		// and fix them.
		//
		// Each option is:
		//   { key: Number, value: String, missing: Boolean }
		//
		messageTemplateOptionsForStakeholder() {
			const map = new Map()

			// Base map from store (existing templates, filtered by availability)
			for (const template of this.messageTemplates) {
				const key = Number(template.id)
				if (!Number.isFinite(key)) continue

				const ch = this.messageChannelMap[template.channel]
				const allowed = ch?.availableForStakeholder === true
					|| ch?.availableForStakeholder === 'true'
					|| ch?.availableForStakeholder === 1

				if (allowed) {
					map.set(key, {
						key,
						value: `[${template.channel}] ${template.name}`,
						missing: false,
					})
				}
			}

			// Add "missing" entries found in current Stakeholder selections
			const ensureMissing = (id) => {
				const key = Number(id)
				if (!Number.isFinite(key)) return
				if (!map.has(key)) {
					map.set(key, {
						key,
						value: `${key} (${t('vtramit', 'missing!')})`,
						missing: true,
					})
				}
			}

			for (const it of this.itemList) {
				const a = Array.isArray(it.notifyStakeholderTemplate) ? it.notifyStakeholderTemplate : []
				for (const id of a) ensureMissing(id)
			}

			return Array.from(map.values())
		},

		// ------------------------------------------------------------------------
		// Build the message template options for staff from store and
		// include any "missing" ids currently selected by items, so users can see
		// and fix them.
		//
		// Each option is:
		//   { key: Number, value: String, missing: Boolean }
		//
		messageTemplateOptionsForStaff() {
			const map = new Map()

			// Base map from store (existing templates, filtered by availability)
			for (const template of this.messageTemplates) {
				const key = Number(template.id)
				if (!Number.isFinite(key)) continue

				const ch = this.messageChannelMap[template.channel]
				const allowed = ch?.availableForStaff === true
					|| ch?.availableForStaff === 'true'
					|| ch?.availableForStaff === 1

				if (allowed) {
					map.set(key, {
						key,
						value: `[${template.channel}] ${template.name}`,
						missing: false,
					})
				}
			}

			// Add "missing" entries found in current Staff selections
			const ensureMissing = (id) => {
				const key = Number(id)
				if (!Number.isFinite(key)) return
				if (!map.has(key)) {
					map.set(key, {
						key,
						value: `${key} (${t('vtramit', 'missing!')})`,
						missing: true,
					})
				}
			}

			for (const it of this.itemList) {
				const b = Array.isArray(it.notifyStaffTemplate) ? it.notifyStaffTemplate : []
				for (const id of b) ensureMissing(id)
			}

			return Array.from(map.values())
		},

		// ------------------------------------------------------------------------
		// DEPRECATED: Remove with EmailTemplates system
		//
		mailTemplates() {
			return this.$store.getters['mailTemplates/readAll']
		},
		// ------------------------------------------------------------------------
		// DEPRECATED: Remove with EmailTemplates system
		//
		// Build the mail template options including any "missing" ids currently
		// selected by items, so users can see and fix them.
		// Note: we do NOT include the special "0" option here.
		//
		mailTemplateOptions() {
			const map = new Map(this.mailTemplates.map(t => [t.id, { ...t, missing: false }]))

			for (const it of this.itemList) {
				const addIfMissing = (id) => {
					if (id && !map.has(id)) {
						map.set(id, {
							id,
							name: `${id} (${t('vtramit', 'missing!')})`,
							missing: true,
						})
					}
				}
				addIfMissing(it.notifyStakeholderTemplateId)
				addIfMissing(it.notifyStaffTemplateId)
			}

			return Array.from(map.values())
		},
	},
	created() {
		const urlTokens = {
			id: this.procedureTypeId,
		}
		// Once the component is created we load the data needed for the form.
		if (this.procedureTypeId) {
			this.loadFormData(null, urlTokens)
		}
		this.$store.dispatch('groups/readAll')
		this.$store.dispatch('mailTemplates/readAll')
		this.$store.dispatch('messageTemplates/readAll')
		this.$store.dispatch('messageChannels/readAll')
	},
	methods: {
		clickAddNewItem() {
			if (this.itemList.some(it => it.isNew)) {
				showError(t('vtramit', 'Please save the group being edited before adding a new group'))
				return
			}
			this.addNewItem()
		},
		getGroupLabel(item) {
			if (!item || !item.group) {
				const base = t('vtramit', 'Group')
				return item?.isNew ? `${base} *` : base
			}
			const selected = this.groupsOptions.find(g => g.id === item.group)
			const label = selected ? selected.name : item.group
			return (item?.isNew || item?.isChanged) ? `${label} *` : label
		},
		actionItems(item, index) {
			const actions = [
				{
					key: 'create',
					label: t('vtramit', 'Create'),
					icon: 'icon-upload',
					show: item.isNew,
					disabled: !this.formReadyForSubmit || !item.isChanged,
					handler: () => this.clickCreateProcedureTypeGroup(item),
				},
				{
					key: 'update',
					label: t('vtramit', 'Update'),
					icon: 'icon-upload',
					show: !item.isNew,
					disabled: !this.formReadyForSubmit || !item.isChanged,
					handler: () => this.clickUpdateProcedureTypeGroup(item),
				},
				{
					key: 'cancel',
					label: t('vtramit', 'Cancel'),
					icon: 'icon-close',
					show: true,
					disabled: item.isNew ? false : (!this.formReadyForSubmit || !item.isChanged),
					handler: () => this.clickCancelItem(index),
				},
				{
					key: 'delete',
					label: t('vtramit', 'Delete'),
					icon: 'icon-delete',
					show: !item.isNew,
					disabled: false,
					handler: () => this.clickDeleteProcedureTypeGroup(item),
				},
			]

			return actions.filter(a => a.show)
		},
		// ------------------------------------------------------------------------
		// Generate a unique element identifier given the name of the element and
		// the index offset in the array of elements
		//
		getElementId(name, index) {
			return 'procedureTypeGroup_' + this.procedureTypeId + '_' + name + '_' + index;
		},
		// ------------------------------------------------------------------------
		// Returns whether the item has pending changes (new or modified).
		//
		hasPendingChanges(item) {
			return !!(item.isNew || item.isChanged)
		},
		// ------------------------------------------------------------------------
		//
		// DEPRECATED: Remove with EmailTemplates system
		//
		// Show legacy select only if persisted value is not 0,
		// or if the item has unsaved changes (isChanged = true).
		//
		showStakeholderLegacy(item) {
			if (!item || item.isNew) return false
			if (item._persistedNotifyStakeholderTemplateId === undefined) {
				item._persistedNotifyStakeholderTemplateId = Number(item.notifyStakeholderTemplateId || 0)
			}
			return item._persistedNotifyStakeholderTemplateId !== 0
		},
		// ------------------------------------------------------------------------
		//
		// DEPRECATED: Remove with EmailTemplates system
		//
		// Show legacy select only if persisted value is not 0,
		// or if the item has unsaved changes (isChanged = true).
		//
		showStaffLegacy(item) {
			if (!item || item.isNew) return false
			if (item._persistedNotifyStaffTemplateId === undefined) {
				item._persistedNotifyStaffTemplateId = Number(item.notifyStaffTemplateId || 0)
			}
			return item._persistedNotifyStaffTemplateId !== 0
		},
		// ------------------------------------------------------------------------
		// Check if the selected group is valid (not empty and not missing).
		//
		isGroupValid(procedureTypeGroup) {
			if (!procedureTypeGroup.group) {
				return false
			}
			const selected = this.groupsOptions.find(g => g.id === procedureTypeGroup.group)
			if (!selected) {
				return false
			}
			return !selected.missing
		},
		// ------------------------------------------------------------------------
		// Check whether the selected stakeholder message templates are valid.
		// An empty array is considered a valid choice.
		//
		// @param {Object} item
		//   The procedureTypeGroup item being edited.
		// @returns {boolean}
		//   TRUE if the array is empty or all selected templates exist and are not missing.
		//
		isNotifyStakeholderTemplates(item) {
			if (!item) {
				return false
			}
			const ids = item.notifyStakeholderTemplate || []
			if (!Array.isArray(ids) || ids.length === 0) {
				return true
			}
			return ids.every(id => {
				const selected = this.messageTemplateOptionsForStakeholder.find(t => t.key === id)
				return !!selected && !selected.missing
			})
		},
		// ------------------------------------------------------------------------
		//
		// DEPRECATED: Remove with EmailTemplates system
		//
		// Check whether the selected staff message templates are valid.
		// An empty array is considered a valid choice.
		//
		// @param {Object} item
		//   The procedureTypeGroup item being edited.
		// @returns {boolean}
		//   TRUE if the array is empty or all selected templates exist and are not missing.
		//
		isNotifyStaffTemplates(item) {
			if (!item) {
				return false
			}
			const ids = item.notifyStaffTemplate || []
			if (!Array.isArray(ids) || ids.length === 0) {
				return true
			}
			return ids.every(id => {
				const selected = this.messageTemplateOptionsForStaff.find(t => t.key === id)
				return !!selected && !selected.missing
			})
		},
		// ------------------------------------------------------------------------
		//
		// DEPRECATED: Remove with EmailTemplates system
		//
		// Check whether the selected mail template is valid.
		// "0" (Do not send e-mail) is considered a valid choice.
		//
		// @param {Object} item
		//   The signatureButtons item being edited.
		// @returns {boolean}
		//   TRUE if '0' is selected or an existing, non-missing template is selected.
		//
		isNotifyStakeholderTemplateId(item) {
			if (!item) {
				return false
			}
			const id = item.notifyStakeholderTemplateId
			if (id === 0 || id === '0') {
				return true
			}
			const selected = this.mailTemplateOptions.find(t => t.id === id)
			if (!selected) {
				return false
			}
			return !selected.missing
		},
		// ------------------------------------------------------------------------
		// Check whether the selected mail template is valid.
		// "0" (Do not send e-mail) is considered a valid choice.
		//
		// @param {Object} item
		//   The signatureButtons item being edited.
		// @returns {boolean}
		//   TRUE if '0' is selected or an existing, non-missing template is selected.
		//
		isNotifyStaffTemplateId(item) {
			if (!item) {
				return false
			}
			const id = item.notifyStaffTemplateId
			if (id === 0 || id === '0') {
				return true
			}
			const selected = this.mailTemplateOptions.find(t => t.id === id)
			if (!selected) {
				return false
			}
			return !selected.missing
		},
		// ------------------------------------------------------------------------
		// Check if the selected group is unique among all items (excluding itself).
		//
		isGroupUnique(item) {
			if (!item.group) {
				return true
			}
			return !this.itemList.some(it => it !== item && it.group === item.group)
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				id: null,
				procedureTypeId: null,
				group: '',
				notifyStakeholderTemplateId: 0,
				notifyStaffTemplateId: 0,
				vcConfirmationForm: '',
				allowCreate: '',
				allowAssignGroup: '',
				allowAssignUser: '',
				bypassRequired: '',
				defaultViewDesktopLeft: '',
				defaultViewDesktopRight: '',
				defaultViewMobile: '',
				notifyStakeholderTemplate: [],
				notifyStaffTemplate: [],
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			const forApi = {
				procedureTypeId: this.procedureTypeId,
				group: item.group,
				notifyStakeholderTemplateId: item.notifyStakeholderTemplateId,
				notifyStaffTemplateId: item.notifyStaffTemplateId,
				vcConfirmationForm: item.vcConfirmationForm,
				allowCreate: item.allowCreate,
				allowAssignGroup: item.allowAssignGroup,
				allowAssignUser: item.allowAssignUser,
				bypassRequired: item.bypassRequired,
				defaultViewDesktopLeft: item.defaultViewDesktopLeft || '',
				defaultViewDesktopRight: item.defaultViewDesktopRight || '',
				defaultViewMobile: item.defaultViewMobile || '',
				notifyStakeholderTemplate: item.notifyStakeholderTemplate,
				notifyStaffTemplate: item.notifyStaffTemplate,
			}

			return forApi
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to update the item.
		//
		prepareItemForUpdate(item) {
			const forApi = {
				id: item.id,
				procedureTypeId: this.procedureTypeId,
				group: item.group,
				notifyStakeholderTemplateId: item.notifyStakeholderTemplateId,
				notifyStaffTemplateId: item.notifyStaffTemplateId,
				vcConfirmationForm: item.vcConfirmationForm,
				allowCreate: item.allowCreate,
				allowAssignGroup: item.allowAssignGroup,
				allowAssignUser: item.allowAssignUser,
				bypassRequired: item.bypassRequired,
				defaultViewDesktopLeft: item.defaultViewDesktopLeft || '',
				defaultViewDesktopRight: item.defaultViewDesktopRight || '',
				defaultViewMobile: item.defaultViewMobile || '',
				notifyStakeholderTemplate: item.notifyStakeholderTemplate,
				notifyStaffTemplate: item.notifyStaffTemplate,
			}

			return forApi
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
			if (!this.isGroupValid(item)) {
				showError(t('vtramit', 'The group is not valid! Please choose a valid group'))
				return false
			}

			if (!this.isGroupUnique(item)) {
				showError(t('vtramit', 'This group is already assigned! Please choose a different group'))
				return false
			}

			if (!this.isNotifyStakeholderTemplates(item)) {
				showError(t('vtramit', 'The stakeholder message templates selection is not valid! Please choose different templates'))
				return false
			}

			if (!this.isNotifyStaffTemplates(item)) {
				showError(t('vtramit', 'The staff message templates selection is not valid! Please choose different templates'))
				return false
			}

			// DEPRECATED: Remove with EmailTemplates system
			if (!this.isNotifyStakeholderTemplateId(item)) {
				showError(t('vtramit', 'The mail template to notify the stakeholder is not valid! Please choose a different template'))
				return false
			}

			if (!this.isNotifyStaffTemplateId(item)) {
				showError(t('vtramit', 'The mail template to notify the staff is not valid! Please choose a different template'))
				return false
			}
			// END DEPRECATED

			return true
		},
		// ------------------------------------------------------------------------
		// Delete item click handler.
		//
		clickDeleteProcedureTypeGroup(item) {
			this.clickDeleteItem(item, { id: item.procedureTypeId }).then((result) => {
				this.$store.dispatch('procedureTypeGroups/readAll')
			})
		},
		// ------------------------------------------------------------------------
		// Create item click handler.
		//
		clickCreateProcedureTypeGroup(item) {
			this.clickCreateItem(item, { id: this.procedureTypeId }).then((result) => {
				this.loadFormData(null, { id: this.procedureTypeId })
				this.$store.dispatch('procedureTypeGroups/readAll')
			})
		},
		// ------------------------------------------------------------------------
		// Update item click handler.
		//
		clickUpdateProcedureTypeGroup(item) {
			this.clickUpdateItem(item, { id: item.procedureTypeId }).then((result) => {
				this.$store.dispatch('procedureTypeGroups/readAll')
			})
		},
	},
}
</script>

<style>
/* Multiple Selector styles */
.grid-4-columns > .field-item.multiple-selector:has(.ms-group.is-row) {
  grid-column: 1 / -1 !important;
  grid-row: auto !important;
  margin-top: 8px;
}

.grid-4-columns > .field-item.multiple-selector:has(.ms-group.is-column) {
  grid-column: span 2 !important;
  margin-top: 0;
}

@media (max-width: 720px) {
  .grid-4-columns > .field-item.multiple-selector:has(.ms-group.is-column) {
    grid-column: 1 / -1 !important;
  }
}
</style>
