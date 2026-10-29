<template>
	<div class="section-no-borders">
		<!-- Section header. -->
		<div class="d-flex align-center section-header">
			<h3>{{ t('vtramit', 'Protocol steps') }}</h3>
			<Actions>
				<ActionButton icon="icon-add"
					href="#"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new step') }}
				</ActionButton>
			</Actions>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<DraggableList>
				<div v-for="(protocolStep, index) in itemList"
					:key="protocolStep.id || protocolStep.tmpId"
					:class="{
						'item-wrapper': true,
						'item-separator': true
					}">
				<!-- Items form -->
				<div class="item-content item-content-draggable"
					:class="{ 'pending-changes': hasPendingChanges(protocolStep) }">
					<DraggableRow
						:icon="dragIcon"
						:draggable="isReorderEnabled"
						:alt-text="t('vtramit', 'Reorder')"
						:title-text="isReorderEnabled ? t('vtramit', 'Drag to reorder') : t('vtramit', 'Save changes to reorder')"
						@dragstart="onDragStart(index, $event)"
						@dragover="onDragOver(index, $event)"
						@drop="onDrop(index, $event)"
						@dragend="onDragEnd">
						<div class="grid-2-columns-with-actions">
							<div class="grid-2-columns">
								<div class="field-item">
									<label :for="getElementId('name', index)">{{ t('vtramit', 'Title') }}</label>
									<input v-model="protocolStep.name"
									:id="getElementId('name', index)"
									type="text"
									@input="protocolStep.isChanged = true">
							</div>
							<div class="field-item">
								<label :for="getElementId('description', index)">{{ t('vtramit', 'Description') }}</label>
								<textarea v-model="protocolStep.description"
									:id="getElementId('description', index)"
									class="ckeditor"
									rows="3"
									@input="protocolStep.isChanged = true" />
							</div>
						</div>
						<!-- Individual item's CRUD buttons -->
						<div class="grid-2-columns form-actions">
							<ActionSplit :actions="actionItems(protocolStep, index)" />
						</div>
					</div>
					</DraggableRow>
				</div>
			</div>
			</DraggableList>
		</div>
	</div>
</template>

<script>
/**
 * @module components/settings/ProcedureTypeProtocolSteps
 */
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import { showError } from '@nextcloud/dialogs'
import { Actions, ActionButton } from '@nextcloud/vue'
import ActionSplit from '../ui/ActionSplit.vue'
import DraggableList from '../ui/DraggableList.vue'
import DraggableRow from '../ui/DraggableRow.vue'
import dragIcon from '../../assets/images/drag-item.svg'

export default {
	name: 'ProcedureTypeProtocolSteps',
	components: {
		Actions,
		ActionButton,
		ActionSplit,
		DraggableList,
		DraggableRow,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	props: {
		procedureTypeId: {
			type: Number,
			default: 0,
		},
		procedureTypeGroupId: {
			type: Number,
			default: 0,
		},
	},
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/procedure-type-group/%id/steps',
		urlForInsert: 'apps/vtramit/web/2.0/procedure-type-protocol-step',
		urlForUpdate: 'apps/vtramit/web/2.0/procedure-type-protocol-step',
		urlForDelete: 'apps/vtramit/web/2.0/procedure-type-protocol-step/%id',
		// Counter to build unique tmpId values for new (unsaved) rows.
		nextTmpId: 1,
		dragIndex: null,
		reorderWorkerRunning: false,
		pendingReorder: false,
		pendingOrderSnapshot: [],
		reorderDirty: false,
		dragIcon,
	}),
	computed: {
		formReadyForSubmit() {
			if (this.status.loading) {
				return false
			}
			if (this.status.readError) {
				return false
			}
			return true
		},
		isReorderEnabled() {
			return this.itemList.every(item => !this.hasPendingChanges(item))
		},
	},
	watch: {
		procedureTypeId() {
			this.loadForGroup()
		},
		procedureTypeGroupId() {
			this.loadForGroup()
		},
	},
	created() {
		this.loadForGroup()
	},
	methods: {
		onDragStart(index, event) {
			if (!this.isReorderEnabled) {
				event.preventDefault()
				return
			}
			this.dragIndex = index
			if (event?.dataTransfer) {
				event.dataTransfer.effectAllowed = 'move'
				event.dataTransfer.setData('text/plain', String(index))
			}
		},
		onDrop(index, event) {
			if (!this.isReorderEnabled) {
				event.preventDefault()
				return
			}
			this.applyDragReorder(index)
			this.finalizeReorder()
		},
		onDragOver(index, event) {
			if (!this.isReorderEnabled) {
				return
			}
			event.preventDefault()
			if (event?.dataTransfer) {
				event.dataTransfer.dropEffect = 'move'
			}
			this.applyDragReorder(index, true)
		},
		onDragEnd() {
			this.finalizeReorder()
		},
		applyDragReorder(index, duringDrag = false) {
			const fromIndex = this.dragIndex
			if (fromIndex === null || fromIndex === index) {
				return
			}
			const item = this.itemList.splice(fromIndex, 1)[0]
			this.itemList.splice(index, 0, item)
			this.dragIndex = index
			this.itemList.forEach((step, idx) => {
				step.order = idx
			})
			if (duringDrag) {
				this.reorderDirty = true
			}
		},
		finalizeReorder() {
			if (!this.reorderDirty) {
				this.dragIndex = null
				return
			}
			this.reorderDirty = false
			this.dragIndex = null
			this.enqueueReorder()
		},
		enqueueReorder() {
			this.pendingReorder = true
			this.pendingOrderSnapshot = this.itemList.map((step, idx) => ({
				id: step.id,
				order: idx,
				item: step,
			}))
			this.processReorderQueue()
		},
		processReorderQueue() {
			if (this.reorderWorkerRunning) {
				return
			}
			this.reorderWorkerRunning = true
			const run = () => {
				if (!this.pendingReorder) {
					this.reorderWorkerRunning = false
					return Promise.resolve()
				}
				this.pendingReorder = false
				const snapshot = this.pendingOrderSnapshot
				return this.persistReorderSnapshot(snapshot)
					.then(run)
					.catch(() => run())
			}
			run()
		},
		persistReorderSnapshot(snapshot) {
			let chain = Promise.resolve()
			snapshot.forEach((entry) => {
				if (!entry?.item || !entry.item.id) {
					return
				}
				if (entry.item._snapshot && entry.item._snapshot.order === entry.order) {
					return
				}
				entry.item.order = entry.order
				chain = chain.then(() => {
					return this.updateItem(entry.item, { id: entry.item.procedureTypeGroupId }).then(() => {
						if (entry.item._snapshot) {
							entry.item._snapshot.order = entry.order
						}
					})
				})
			})

			return chain
		},
		actionItems(item, index) {
			const actions = [
				{
					key: 'create',
					label: t('vtramit', 'Create'),
					icon: 'icon-add',
					show: item.isNew,
					disabled: !this.formReadyForSubmit || !item.isChanged,
					handler: () => this.clickCreateProcedureTypeProtocolStep(item),
				},
				{
					key: 'update',
					label: t('vtramit', 'Update'),
					icon: 'icon-upload',
					show: !item.isNew,
					disabled: !this.formReadyForSubmit || !item.isChanged,
					handler: () => this.clickUpdateProcedureTypeProtocolStep(item),
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
					handler: () => this.clickDeleteProcedureTypeProtocolStep(item),
				},
			]

			return actions.filter(a => a.show)
		},
		loadForGroup() {
			if (this.procedureTypeGroupId) {
				this.loadFormData(null, { id: this.procedureTypeGroupId })
			}
		},
		// ------------------------------------------------------------------------
		// Generate a unique element identifier given the name of the element and
		// the index offset in the array of elements
		//
		getElementId(name, index) {
			return 'procedureTypeProtocolStep_' + this.procedureTypeGroupId + '_' + name + '_' + index;
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
			const nextOrder = this.itemList.length
			const newItem = {
				// Data from server.
				id: null,
				// Temporary client-only id to keep v-for keys stable until save.
				tmpId: `tmp-${this.nextTmpId++}`,
				procedureTypeId: 0,
				procedureTypeGroupId: 0,
				name: '',
				description: '',
				order: nextOrder,
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			const forApi = {
				procedureTypeId: this.procedureTypeId,
				procedureTypeGroupId: this.procedureTypeGroupId,
				name: item.name,
				description: item.description,
				order: item.order,
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
				procedureTypeGroupId: this.procedureTypeGroupId,
				name: item.name,
				description: item.description,
				order: item.order,
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
			const name = (item?.name ?? '').trim()
			if (!name) {
				showError(t('vtramit', 'Please provide a step name'), true)
				this.focusField('name', item)
				return false
			}
			if (name !== item.name) {
				item.name = name
			}
			return true
		},
		// ------------------------------------------------------------------------
		// Delete item click handler.
		//
		clickDeleteProcedureTypeProtocolStep(item) {
			this.clickDeleteItem(item, { id: item.procedureTypeGroupId })
		},
		// ------------------------------------------------------------------------
		// Create item click handler.
		//
		clickCreateProcedureTypeProtocolStep(item) {
			this.clickCreateItem(item, { id: this.procedureTypeGroupId }).then(() => {
				this.loadFormData(null, { id: this.procedureTypeGroupId })
			})
		},
		// ------------------------------------------------------------------------
		// Update item click handler.
		//
		clickUpdateProcedureTypeProtocolStep(item) {
			this.clickUpdateItem(item, { id: item.procedureTypeGroupId })
		},
	},
}
</script>

<style lang="scss" scoped>
.vtramit .section-header {
	margin-bottom: 0px;
}
</style>
