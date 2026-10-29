<template>
	<div class="section" >
		<!-- Section header -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'Signatures') }}</h2>
			<Actions>
				<ActionButton icon="icon-add"
					href="#"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new signature') }}
				</ActionButton>
			</Actions>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(signatureButtons, index) in itemList"
				:key="signatureButtons.id"
				class="item-wrapper item-separator">
				<!-- Items form -->
				<div class="item-content"
					:class="{ 'pending-changes': hasPendingChanges(signatureButtons) }">
					<div class="grid-2-columns-with-actions">
						<div class="grid-4-columns">
							<div class="field-item">
								<label :for="getElementId('signatureButtons', index)">{{ t('vtramit', 'Group') }}</label>
								<select v-model="signatureButtons.group"
									:id="getElementId('signatureButtons', index)"
									class="select"
									:class="{ error: !isGroupValid(signatureButtons) }"
									@change="signatureButtons.isChanged = true">
									<option v-for="item in procedureTypeGroupOptions"
										:key="item.id"
										:value="item.id"
										:class="{ missing: item.missing }">
									{{ item.name  }}
									</option>
								</select>
							</div>
							<div class="field-item">
								<label :for="getElementId('signatureTypeId', index)">{{ t('vtramit', 'Signature') }}</label>
								<select v-model="signatureButtons.signatureTypeId"
									:id="getElementId('signatureTypeId', index)"
									class="select"
									:class="{ error: !isSignatureTypeValid(signatureButtons) }"
									@change="signatureButtons.isChanged = true">
									<option v-for="signatureType in signatureTypeOptions"
										:key="signatureType.id"
										:value="signatureType.id"
										:class="{ missing: signatureType.missing }">
										{{ signatureType.name }}
									</option>
								</select>
							</div>
							<div class="field-item"
								v-if="showMailLegacy(signatureButtons)">
								<label :for="getElementId('mailTemplateId', index)">{{ t('vtramit', 'Mail template') }}</label>
								<select v-model="signatureButtons.mailTemplateId"
									:id="getElementId('mailTemplateId', index)"
									class="select"
									:class="{ error: !isMailTemplateValid(signatureButtons) }"
									@change="signatureButtons.isChanged = true">
									<option value="0">--- Do not send e-mail ---</option>
									<option v-for="template in mailTemplateOptions"
										:key="template.id"
										:value="template.id"
										:class="{ missing: template.missing }">
										{{ template.name }}
									</option>
								</select>
							</div>
							<div class="field-item">
								<label :for="getElementId('messageTemplateId', index)">{{ t('vtramit', 'Message template') }}</label>
								<select v-model="signatureButtons.messageTemplateId"
									:id="getElementId('messageTemplateId', index)"
									class="select"
									:class="{ error: !isMessageTemplateValid(signatureButtons) }"
									@change="signatureButtons.isChanged = true">
									<option value="0">--- Do not send message ---</option>
									<option v-for="template in messageTemplateOptionsForStakeholder"
										:key="template.key"
										:value="template.key"
										:class="{ missing: template.missing }">
										{{ template.value }}
									</option>
								</select>
							</div>
							<div v-for="language in allowedLanguages"
								:key="language"
								class="field-item">
								<label :for="getElementId('label_' + language, index)">{{ t('vtramit', 'Label') }} ({{ language }})</label>
								<input v-model="signatureButtons.label[language]"
									:id="getElementId('label_' + language, index)"
									type="text"
									@input="signatureButtons.isChanged = true">
							</div>
						</div>
						<!-- Individual item's CRUD buttons -->
						<div class="grid-2-columns form-actions">
							<ActionSplit :actions="actionItems(signatureButtons, index)" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * @module components/settings/ProcedureTypeGroups
 */
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import { showError } from '@nextcloud/dialogs'
import { Actions, ActionButton } from '@nextcloud/vue'
import ActionSplit from '../ui/ActionSplit.vue'

export default {
	name: 'SignatureButtons',
	components: {
		Actions,
		ActionButton,
		ActionSplit,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	props: {
		procedureTypeId: {
			type: Number,
			default: 0,
		},
	},
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/procedure-type/%id/signature-buttons',
		urlForInsert: 'apps/vtramit/web/2.0/signature-buttons',
		urlForUpdate: 'apps/vtramit/web/2.0/signature-buttons',
		urlForDelete: 'apps/vtramit/web/2.0/signature-buttons/%id',
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
		// ------------------------------------------------------------------------
		//
		// DEPRECATED: Remove with EmailTemplates system
		//
		mailTemplates() {
			return this.$store.getters['mailTemplates/readAll']
		},
		messageTemplates() {
			return this.$store.getters['messageTemplates/readAll']
		},
		messageChannels() {
			return this.$store.getters['messageChannels/readAll']
		},
		signatureTypes() {
			return this.$store.getters['signatureTypes/readAll']
		},
		allowedLanguages() {
			return this.$store.getters['configuration/getLanguages']
		},
		procedureTypeGroups() {
			return this.$store.getters['procedureTypeGroups/readByProcedureType'](this.procedureTypeId)
		},
		// ------------------------------------------------------------------------
		// Build the group options including any "orphan" (missing) selection found
		// on current items, so users can see and fix them.
		//
		procedureTypeGroupOptions() {
			const map = new Map(
				this.procedureTypeGroups.map(it => [it.group, { id: it.group, name: it.group, missing: false }])
			)

			for (const it of this.itemList) {
				const id = it.group
				if (id && !map.has(id)) {
					map.set(id, {
						id,
						name: `${id} (${t('vtramit', 'missing!')})`,
						missing: true,
					})
				}
			}

			return Array.from(map.values())
		},
		// ------------------------------------------------------------------------
		// Build signature type options including any ids referenced by items
		// that no longer exist (marked as missing).
		//
		signatureTypeOptions() {
			const map = new Map(this.signatureTypes.map(st => [st.id, { ...st, missing: false }]))

			for (const it of this.itemList) {
				const id = it.signatureTypeId
				if (id && !map.has(id)) {
					map.set(id, {
						id,
						name: `${id} (${t('vtramit', 'missing!')})`,
						missing: true,
					})
				}
			}
			return Array.from(map.values())
		},
		// ------------------------------------------------------------------------
		//
		// DEPRECATED: Remove with EmailTemplates system
		//
		// Build the mail template options including any "missing" ids currently
		// selected by items, so users can see and fix them.
		// Note: we do NOT include the special "0" option here.
		//
		mailTemplateOptions() {
			const map = new Map(this.mailTemplates.map(t => [t.id, {
				id: t.id,
				name: t.name,
				missing: false,
			}]))

			for (const it of this.itemList) {
				const id = it.mailTemplateId
				if (id && id !== 0 && id !== '0' && !map.has(id)) {
					map.set(id, {
						id,
						name: `${id} (${t('vtramit', 'missing!')})`,
						missing: true,
					})
				}
			}

			return Array.from(map.values())
		},
		// ------------------------------------------------------------------------
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
		// Build the message template options for stakeholder, including "missing".
		//
		messageTemplateOptionsForStakeholder() {
			const map = new Map()

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
				const id = it?.messageTemplateId
				if (id && id !== 0 && id !== '0') ensureMissing(id)
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
		this.$store.dispatch('signatureTypes/readAll')
		this.$store.dispatch('procedureTypeGroups/readAll')
		this.$store.dispatch('messageChannels/readAll')
		this.$store.dispatch('messageTemplates/readAll')

	},
	methods: {
		actionItems(item, index) {
			const actions = [
				{
					key: 'create',
					label: t('vtramit', 'Create'),
					icon: 'icon-upload',
					show: item.isNew,
					disabled: !this.formReadyForSubmit || !item.isChanged,
					handler: () => this.clickCreateProcedureTypeSignature(item),
				},
				{
					key: 'update',
					label: t('vtramit', 'Update'),
					icon: 'icon-upload',
					show: !item.isNew,
					disabled: !this.formReadyForSubmit || !item.isChanged,
					handler: () => this.clickUpdateProcedureTypeSignature(item),
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
					handler: () => this.clickDeleteProcedureTypeSignature(item),
				},
			]

			return actions.filter(a => a.show)
		},
		// ------------------------------------------------------------------------
		// Generate a unique element identifier given the name of the element and
		// the index offset in the array of elements
		//
		getElementId(name, index) {
			return 'signatureButtons_' + this.procedureTypeId + '_' + name + '_' + index;
		},
		// ------------------------------------------------------------------------
		// Returns whether the item has pending changes (new or modified).
		hasPendingChanges(item) {
			return !!(item.isNew || item.isChanged)
		},
		// ------------------------------------------------------------------------
		// Check if the selected group is valid (not empty and not missing).
		//
		isGroupValid(procedureTypeGroup) {
			if (!procedureTypeGroup || !procedureTypeGroup.group) {
				return false
			}
			const selected = this.procedureTypeGroupOptions.find(g => g.id === procedureTypeGroup.group)
			if (!selected) {
				return false
			}
			return !selected.missing
		},
		// ------------------------------------------------------------------------
		// Check whether the selected signature type is valid (exists and not missing).
		//
		// @param {Object} item
		//   The signatureButtons item.
		// @returns {boolean}
		//   TRUE if an existing, non-missing type is selected; FALSE otherwise.
		//
		isSignatureTypeValid(item) {
			const id = item?.signatureTypeId
			if (!id) {
				return false
			}
			const selected = this.signatureTypeOptions.find(st => st.id === id)
			return !!(selected && !selected.missing)
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
		isMailTemplateValid(item) {
			if (!item) {
				return false
			}
			const id = item.mailTemplateId
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
		//
		// DEPRECATED: Remove with EmailTemplates system
		//
		// Mail legacy visibility: use persisted value, not in-form edits.
		//
		showMailLegacy(item) {
			if (!item || item.isNew) return false
			if (item._persistedMailTemplateId === undefined) {
				item._persistedMailTemplateId = Number(item.mailTemplateId || 0)
			}
			return item._persistedMailTemplateId !== 0
		},
		// ------------------------------------------------------------------------
		// Check whether the selected message template is valid.
		// "0" (Do not send message) is considered a valid choice.
		//
		// @param {Object} item
		//   The signatureButtons item being edited.
		// @returns {boolean}
		//   TRUE if '0' is selected or an existing, non-missing template is selected.
		//
		isMessageTemplateValid(item) {
			if (!item) {
				return false
			}
			const id = item.messageTemplateId
			if (id === 0 || id === '0') {
				return true
			}
			const selected = this.messageTemplateOptionsForStakeholder.find(t => t.key === id)
			if (!selected) {
			 return false
			}
			return !selected.missing
		},
		// ------------------------------------------------------------------------
		// Build payload for label: keep only non-empty strings per allowedLanguages.
		//
		// @param {Object} label
		// @returns {Object}
		//
		prepareLabelPayload(label) {
			const out = {}
			for (const lang of this.allowedLanguages) {
				const v = label && label[lang]
				if (typeof v === 'string') {
					const t = v.trim()
					if (t !== '') out[lang] = t
				}
			}
			return out
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				id: null,
				procedureTypeId: 0,
				group: 0,
				signatureTypeId: 0,
				mailEnabled: false,
				mailTemplateId: 0,
				messageTemplateId: 0,
				messageEnabled: false,
				label: {},
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			if (item.mailTemplateId == "0") {
				item.mailEnabled = false

			} else {
				item.mailEnabled = true

			}
			if (item.messageTemplateId == "0") {
				item.messageEnabled = false

			} else {
				item.messageEnabled = true

			}
			const forApi = {
				procedureTypeId: this.procedureTypeId,
				group: item.group,
				signatureTypeId: item.signatureTypeId,
				mailEnabled:  item.mailEnabled,
				mailTemplateId: item.mailTemplateId,
				messageEnabled: item.messageEnabled,
				messageTemplateId: item.messageTemplateId,
				label: this.prepareLabelPayload(item.label),
			}

			return forApi
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to update the item.
		//
		prepareItemForUpdate(item) {
			if (item.mailTemplateId == "0") {
				item.mailEnabled = false

			} else {
				item.mailEnabled = true

			}
			if (item.messageTemplateId == "0") {
				item.messageEnabled = false

			} else {
				item.messageEnabled = true

			}
			const forApi = {
				id: item.id,
				procedureTypeId: this.procedureTypeId,
				group: item.group,
				signatureTypeId: item.signatureTypeId,
				mailEnabled:  item.mailEnabled,
				mailTemplateId: item.mailTemplateId,
				messageEnabled: item.messageEnabled,
				messageTemplateId: item.messageTemplateId,
				label: this.prepareLabelPayload(item.label),
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

			if (!this.isSignatureTypeValid(item)) {
				showError(t('vtramit', 'The signature is not valid! Please choose a valid signature type'))
				return false
			}

			if (!this.isMailTemplateValid(item)) {
				showError(t('vtramit', 'The mail template is not valid! Please choose a different template'))
				return false
			}

			if (!this.isMessageTemplateValid(item)) {
				showError(t('vtramit', 'The message template is not valid! Please choose a different template'))
				return false
			}

			return true
		},
		// ------------------------------------------------------------------------
		// Delete item click handler.
		//
		clickDeleteProcedureTypeSignature(item) {
			this.clickDeleteItem(item, { id: item.procedureTypeId }).then(() => {
				this.loadFormData(null, { id: this.procedureTypeId })
			})
		},
		// ------------------------------------------------------------------------
		// Create item click handler.
		//
		clickCreateProcedureTypeSignature(item) {
			this.clickCreateItem(item, { id: this.procedureTypeId }).then(() => {
				this.loadFormData(null, { id: this.procedureTypeId })
			})
		},
		// ------------------------------------------------------------------------
		// Update item click handler.
		//
		clickUpdateProcedureTypeSignature(item) {
			this.clickUpdateItem(item, { id: item.procedureTypeId }).then(() => {
				this.loadFormData(null, { id: this.procedureTypeId })
			})
		},
	},
}
</script>
