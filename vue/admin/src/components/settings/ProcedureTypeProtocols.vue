<template>
	<div class="section">
		<!-- Section header. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'Protocols') }}</h2>
			<Actions>
				<ActionButton icon="icon-add"
					href="#"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new protocol') }}
				</ActionButton>
			</Actions>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(procedureTypeProtocol, index) in itemList"
				:key="procedureTypeProtocol.id"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': procedureTypeProtocol.isExpanded
				}">
				<!-- Item title -->
				<div class="collapsible-header" @click="toggleItem(procedureTypeProtocol, $event)">
					<Actions>
						<ActionButton :icon="procedureTypeProtocol.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ procedureTypeProtocol.name }}
					</div>
				</div>

				<!-- Items form -->
				<div class="item-content"
					:hidden="!procedureTypeProtocol.isExpanded"
					:class="{ 'pending-changes': hasPendingChanges(procedureTypeProtocol) }">
					<div class="grid-2-columns-with-actions">
						<div class="grid-2-columns">
							<div class="field-item">
								<label :for="getElementId('procedureTypeGroupId', index)">{{ t('vtramit', 'Group') }}</label>
								<select v-model="procedureTypeProtocol.procedureTypeGroupId"
									:id="getElementId('procedureTypeGroupId', index)"
									class="select"
									@change="procedureTypeProtocol.isChanged = true">
									<option :value="0">{{ t('vtramit', 'General') }}</option>
									<option v-for="item in procedureTypeGroupOptions"
										:key="item.id"
										:value="item.id"
										:class="{ missing: item.missing }">
										{{ item.name }}
									</option>
								</select>
							</div>
							<div class="field-item">
								<label :for="getElementId('name', index)">{{ t('vtramit', 'Name') }}</label>
								<input v-model="procedureTypeProtocol.name"
									:id="getElementId('name', index)"
									type="text"
									@input="procedureTypeProtocol.isChanged = true">
							</div>
						</div>
						<!-- Individual item's CRUD buttons -->
						<div class="grid-2-columns form-actions">
							<ActionSplit :actions="actionItems(procedureTypeProtocol, index)" />
						</div>
					</div>
					<div v-if="!procedureTypeProtocol.isNew" class="item-children">
						<ProcedureTypeProtocolSteps
							:procedure-type-id="procedureTypeId"
							:procedure-type-group-id="procedureTypeProtocol.procedureTypeGroupId" />
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * @module components/settings/ProcedureTypeProtocols
 */
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import ProcedureTypeProtocolSteps from './ProcedureTypeProtocolSteps.vue'
import { Actions, ActionButton } from '@nextcloud/vue'
import { showError } from '@nextcloud/dialogs'
import ActionSplit from '../ui/ActionSplit.vue'

export default {
	name: 'ProcedureTypeProtocols',
	components: {
		Actions,
		ActionButton,
		ActionSplit,
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
		urlForLoad: 'apps/vtramit/web/2.0/procedure-type/%id/protocols',
		urlForInsert: 'apps/vtramit/web/2.0/procedure-type-protocol',
		urlForUpdate: 'apps/vtramit/web/2.0/procedure-type-protocol',
		urlForDelete: 'apps/vtramit/web/2.0/procedure-type-protocol/%id',
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
		procedureTypeGroups() {
			return this.$store.getters['procedureTypeGroups/readByProcedureType'](this.procedureTypeId)
		},
		// ------------------------------------------------------------------------
		// Build group options including any "orphan" (missing) selection found
		// on current items, so users can see and fix them.
		//
		procedureTypeGroupOptions() {
			const map = new Map(
				this.procedureTypeGroups.map(it => [it.id, { id: it.id, name: it.group, missing: false }])
			)

			for (const it of this.itemList) {
				const id = it.procedureTypeGroupId
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
	},
	created() {
		const urlTokens = {
			id: this.procedureTypeId,
		}
		// Once the component is created we load the data needed for the form.
		if (this.procedureTypeId) {
			this.loadFormData(null, urlTokens)
		}
		this.$store.dispatch('procedureTypeGroups/readAll')
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
					handler: () => this.clickCreateProcedureTypeProtocol(item),
				},
				{
					key: 'update',
					label: t('vtramit', 'Update'),
					icon: 'icon-upload',
					show: !item.isNew,
					disabled: !this.formReadyForSubmit || !item.isChanged,
					handler: () => this.clickUpdateProcedureTypeProtocol(item),
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
					handler: () => this.clickDeleteProcedureTypeProtocol(item),
				},
			]

			return actions.filter(a => a.show)
		},
		// ------------------------------------------------------------------------
		// Validate name (required and not only spaces).
		//
		validateName(item) {
			const name = (item?.name ?? '').trim()
			if (!name) {
				showError(t('vtramit', 'Please provide a protocol name'), true)
				this.focusField('name', item)
				return false
			}
			if (name !== item.name) {
				item.name = name
			}
			return true
		},
		// ------------------------------------------------------------------------
		// Indicates whether the data is right for creation.
		//
		validateBeforeCreate(item) {
			return this.validateName(item)
		},
		// ------------------------------------------------------------------------
		// Indicates whether the data is right for update.
		//
		validateBeforeUpdate(item) {
			return this.validateName(item)
		},
		// ------------------------------------------------------------------------
		// Generate a unique element identifier given the name of the element and
		// the index offset in the array of elements
		//
		getElementId(name, index) {
			return 'procedureTypeProtocol_' + this.procedureTypeId + '_' + name + '_' + index;
		},
		// ------------------------------------------------------------------------
		// Returns whether the item has pending changes (new or modified).
		//
		hasPendingChanges(item) {
			return !!(item.isNew || item.isChanged)
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				id: null,
				procedureTypeId: 0,
				procedureTypeGroupId: 0,
				name: '',
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			const forApi = {
				procedureTypeId: this.procedureTypeId,
				procedureTypeGroupId: item.procedureTypeGroupId || null,
				name: item.name,
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
				procedureTypeGroupId: item.procedureTypeGroupId || null,
				name: item.name,
			}

			return forApi
		},
		// ------------------------------------------------------------------------
		// Delete item click handler.
		//
		clickDeleteProcedureTypeProtocol(item) {
			this.clickDeleteItem(item, { id: item.procedureTypeId }).then(() => {
				this.loadFormData(null, { id: this.procedureTypeId })
			})
		},
		// ------------------------------------------------------------------------
		// Create item click handler.
		//
		clickCreateProcedureTypeProtocol(item) {
			this.clickCreateItem(item, { id: this.procedureTypeId }).then(() => {
				this.loadFormData(null, { id: this.procedureTypeId })
			})
		},
		// ------------------------------------------------------------------------
		// Update item click handler.
		//
		clickUpdateProcedureTypeProtocol(item) {
			this.clickUpdateItem(item, { id: item.procedureTypeId }).then(() => {
				this.loadFormData(null, { id: this.procedureTypeId })
			})
		},
	},
}
</script>
