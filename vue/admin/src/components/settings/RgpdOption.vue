<template>
	<div class="section" data-test="rgpd">
		<!-- Section header with expand button. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'RGPD Options') }}</h2>
			<Actions>
				<ActionButton icon="icon-add"
					href="#"
					:close-after-click="true"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new RGPD option') }}
				</ActionButton>
			</Actions>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(rgpdOption, index) in itemList"
				:key="rgpdOption.id"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': rgpdOption.isExpanded
				}">
				<!-- Item title -->
				<div class="collapsible-header" @click="toggleItem(rgpdOption, $event)">
					<Actions>
						<ActionButton :icon="rgpdOption.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ rgpdOption.name }}
					</div>
				</div>
				<!-- Items form -->
				<div class="item-content" :hidden="!rgpdOption.isExpanded">
					<div class="field-item">
						<label :for="getElementId('name', index)">{{ t('vtramit', 'Name') }}</label>
						<input :id="getElementId('name', index)"
							v-model="rgpdOption.name"
							type="text"
							@input="rgpdOption.isChanged = true"
							@blur="rgpdOption.isChanged = true">
					</div>
					<div class="grid-2-columns">
						<div class="field-item">
							<label :for="getElementId('type', index)">{{ t('vtramit', 'Type') }}</label>
							<select :id="getElementId('type', index)"
								v-model="rgpdOption.type"
								@change="rgpdOption.isChanged = true"
								@blur="rgpdOption.isChanged = true">
								<option value="checkbox">
									checkbox
								</option>
								<option value="markup">
									markup
								</option>
							</select>
						</div>
						<div class="field-item field-radio">
							<label>{{ t('vtramit', 'Required') }}</label>
							<div class="field-radio--options">
								<input
									:id="getElementId('required-yes', index)"
									type="radio"
									:value=true
									v-model="rgpdOption.required"
									@input="rgpdOption.isChanged = true"
									@blur="rgpdOption.isChanged = true"
								>
								<label :for="getElementId('required-yes', index)">
									{{ t('vtramit', 'Yes') }}
								</label>
								<input
									:id="getElementId('required-no', index)"
									type="radio"
									:value=false
									v-model="rgpdOption.required"
									@input="rgpdOption.isChanged = true"
									@blur="rgpdOption.isChanged = true"
								>
								<label :for="getElementId('required-no', index)">
									{{ t('vtramit', 'No') }}
								</label>
							</div>
						</div>
					</div>
					<div v-for="language in allowedLanguages"
						:key="language"
						class="field-item">
						<label :for="getElementId('label_' + language, index)">{{ t('vtramit', 'Label') }} ({{ language }})</label>
						<textarea :id="getElementId('label_' + language, index)"
							v-model="rgpdOption.label[language]"
							class="ckeditor"
							rows="10"
							@input="rgpdOption.isChanged = true"
							@blur="rgpdOption.isChanged = true" />
					</div>
					<!-- Individual item's CRUD buttons -->
					<div class="d-flex justify-end form-actions">
						<button v-if="!rgpdOption.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Delete'),
								autoHide: false,
								html: true
							}"
							class="btn-delete"
							@click="clickDeleteItem(rgpdOption)">
							<span class="icon icon-delete" />
							<span class="label">
								{{ t('vtramit', 'Delete') }}
							</span>
						</button>
						<button v-if="rgpdOption.isNew"
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
						<button v-if="rgpdOption.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Create'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !rgpdOption.isChanged"
							class="primary btn-save"
							@click="clickCreateItem(rgpdOption)">
							<span class="icon icon-upload" />
							<span class="label">
								{{ t('vtramit', 'Create') }}
							</span>
						</button>
						<button v-if="!rgpdOption.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Update'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !rgpdOption.isChanged"
							class="primary btn-save"
							@click="clickUpdateItem(rgpdOption)">
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
 * @module components/settings/RgpdOption
 */
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import { Actions, ActionButton } from '@nextcloud/vue'

export default {
	name: 'RgpdOption',
	components: {
		Actions,
		ActionButton,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/rgpd-option',
		urlForInsert: 'apps/vtramit/web/2.0/rgpd-option',
		urlForUpdate: 'apps/vtramit/web/2.0/rgpd-option',
		urlForDelete: 'apps/vtramit/web/2.0/rgpd-option/%id',
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
		allowedLanguages() {
			return this.$store.getters['configuration/getLanguages']
		},
	},
	created() {
		// Once the component is created we load the data needed for the form.
		this.loadFormData()
	},
	methods: {
		// ------------------------------------------------------------------------
		// Generate a unique element identifier given the name of the element and
		// the index offset in the array of elements
		//
		getElementId(name, index) {
			return 'rgpdOption_' + name + '_' + index;
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				id: null,
				name: '',
				type: '',
				label: {},
				required: '',
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			const newLabel = []
			const labelKeys = Object.keys(item.label)

			labelKeys.forEach((key) => {
				newLabel.push({ key, value: item.label[key] })
			})
			const forApi = {
				id: item.id,
				name: item.name,
				type: item.type,
				label: newLabel,
				required: item.required,
			}

			return forApi
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to update the item.
		//
		prepareItemForUpdate(item) {
			const newLabel = []
			const labelKeys = Object.keys(item.label)

			labelKeys.forEach((key) => {
				newLabel.push({ key, value: item.label[key] })
			})
			const forApi = {
				id: item.id,
				name: item.name,
				type: item.type,
				label: newLabel,
				required: item.required,
			}

			return forApi
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields and proper initial
		// values.
		//
		newItem(data = {}) {
			const newItem = this.prepareNewItem()
			// Data needed to control the state of the item.
			newItem.isNew = true
			newItem.isExpanded = true
			newItem.isChanged = false
			newItem.id = data.id
			newItem.name = data.name
			newItem.type = data.type
			newItem.required = data.required
			newItem.label = {}

			if (data.label) {
				data.label.forEach((el) => {
					newItem.label[el.key] = el.value
				})
			}

			return newItem
		},
	},
}
</script>
