<template>
	<div class="section" data-test="signatures">
		<!-- Section header with expand button. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'Signature types') }}</h2>
			<Actions>
				<ActionLink icon="icon-add"
					href="#"
					:close-after-click="true"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new signature type') }}
				</ActionLink>
			</Actions>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(signatureType, index) in itemList"
				:key="signatureType.id"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': signatureType.isExpanded
				}">
				<!-- Item title -->
				<div class="collapsible-header" @click="toggleItem(signatureType, $event)">
					<Actions>
						<ActionButton :icon="signatureType.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ signatureType.name }}
					</div>
				</div>
				<!-- Items form -->
				<div class="item-content" :hidden="!signatureType.isExpanded">
					<div class="field-item">
						<label :for="getElementId('name', index)">{{ t('vtramit', 'Name') }}</label>
						<input v-model="signatureType.name"
							:id="getElementId('name', index)"
							type="text"
							@input="signatureType.isChanged = true"
							@blur="signatureType.isChanged = true">
					</div>
					<!-- Request -->
					<div class="grid-2-columns">
						<div class="field-item">
							<label :for="getElementId('requestLimit', index)">{{ t('vtramit', 'Request Limit') }}</label>
							<input v-model="signatureType.requestLimit"
								:id="getElementId('requestLimit', index)"
								type="number"
								@input="signatureType.isChanged = true"
								@blur="signatureType.isChanged = true">
							<div class="help-inline">{{ t('vtramit', 'Use -1 for unlimited') }}</div>
						</div>
						<div class="field-item">
							<label :for="getElementId('filesPerRequest', index)">{{ t('vtramit', 'Files per request') }}</label>
							<input v-model="signatureType.filesPerRequest"
								:id="getElementId('filesPerRequest', index)"
								type="number"
								@input="signatureType.isChanged = true"
								@blur="signatureType.isChanged = true">
							<div class="help-inline">{{ t('vtramit', 'Use -1 for unlimited') }}</div>
						</div>
					</div>
					<!-- Certified Authority -->
					<div class="grid-2-columns">
						<div class="field-item">
							<label :for="getElementId('certifiedAuthority', index)">{{ t('vtramit', 'Certified Authority') }}</label>
							<input v-model="signatureType.certifiedAuthority"
								:id="getElementId('certifiedAuthority', index)"
								type="text"
								@input="signatureType.isChanged = true"
								@blur="signatureType.isChanged = true">
						</div>
						<div class="field-item">
							<label :for="getElementId('certifiedAuthorityProxy', index)">{{ t('vtramit', 'Certified Authority Proxy') }}</label>
							<input v-model="signatureType.certifiedAuthorityProxy"
								:id="getElementId('certifiedAuthorityProxy', index)"
								type="text"
								@input="signatureType.isChanged = true"
								@blur="signatureType.isChanged = true">
							<div class="help-inline">{{ t('vtramit', 'Use trailing "/"') }}</div>
						</div>
					</div>
					<!-- Format | Method -->
					<div class="grid-2-columns">
						<div class="field-item">
							<label :for="getElementId('format', index)">{{ t('vtramit', 'Format') }}</label>
							<input v-model="signatureType.format"
								:id="getElementId('format', index)"
								type="text"
								@input="signatureType.isChanged = true"
								@blur="signatureType.isChanged = true">
							<div class="help-inline">{{ t('vtramit', 'Check valid values with the CA Proxy. Some valid values are: Cms, Xmldsig, Xades, Cades.') }}</div>
						</div>
						<div class="field-item">
							<label :for="getElementId('method', index)">{{ t('vtramit', 'Method') }}</label>
							<input v-model="signatureType.method"
								:id="getElementId('method', index)"
								type="text"
								@input="signatureType.isChanged = true"
								@blur="signatureType.isChanged = true">
							<div class="help-inline">{{ t('vtramit', 'Check valid values with the CA Proxy. Leave blank to use default method defined in the CA Proxy. Some valid values ar: otp, certificate.') }}</div>
						</div>
					</div>
					<!-- File type | Additional Info -->
					<div class="grid-2-columns">
						<div class="field-item">
							<div class="field-label">{{ t('vtramit', 'File Type') }}</div>
							<TwoColumnsSelector v-model="signatureType.fileType"
								:optionsList="fileTypes"
								wrapperClass="file-type-selector"
								@changed="signatureType.isChanged = true" />
						</div>
						<div class="field-item">
							<label :for="getElementId('additionalInfo', index)">{{ t('vtramit', 'Additional Info') }}</label>
							<textarea :id="getElementId('additionalInfo', index)"
								v-model="signatureType.additionalInfo"
								class="json-validate"
								rows="5"
								@input="signatureType.isChanged = true"
								@blur="signatureType.isChanged = true" />
						</div>
					</div>
					<!-- Phone required -->
					<div class="field-item field-checkbox">
						<label :for="getElementId('phoneRequired', index)">{{ t('vtramit', 'Phone required') }}</label>
						<input :id="getElementId('phoneRequired', index)"
							v-model="signatureType.phoneRequired"
							type="checkbox"
							@input="signatureType.isChanged = true"
							@blur="signatureType.isChanged = true">
					</div>
					<!-- Individual item's CRUD buttons -->
					<div class="d-flex justify-end form-actions">
						<button v-if="!signatureType.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Delete'),
								autoHide: false,
								html: true
							}"
							class="btn-delete"
							@click="clickDeleteItem(signatureType)">
							<span class="icon icon-delete" />
							<span class="label">
								{{ t('vtramit', 'Delete') }}
							</span>
						</button>
						<button v-if="signatureType.isNew"
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
						<button v-if="signatureType.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Create'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !signatureType.isChanged"
							class="primary btn-save"
							@click="clickCreateItem(signatureType)">
							<span class="icon icon-upload" />
							<span class="label">
								{{ t('vtramit', 'Create') }}
							</span>
						</button>
						<button v-if="!signatureType.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Update'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !signatureType.isChanged"
							class="primary btn-save"
							@click="clickUpdateItem(signatureType)">
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
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import { Actions, ActionButton, ActionLink } from '@nextcloud/vue'

export default {
	name: 'SignatureType',
	components: {
		Actions,
		ActionButton,
		ActionLink,
		TwoColumnsSelector,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/signature-type',
		urlForInsert: 'apps/vtramit/web/2.0/signature-type',
		urlForUpdate: 'apps/vtramit/web/2.0/signature-type',
		urlForDelete: 'apps/vtramit/web/2.0/signature-type/%id',
	}),
	computed: {
		// ------------------------------------------------------------------------
		// Computed to control a bit when the CRUD actions are happening (with
		// the help of the ComponentStatus mixin!) so we can easily prevent people
		// clicking buttons twice while they are waiting for the server response.
		//
		pdf() {

			return "pdf"
		},
		docx() {

			return "docx"
		},
		fileTypes() {
			return [
				{ id: "pdf", name: "pdf" },
				{ id: "docx", name: "docx" },
				{ id: "*", name: "any file" }
			]
		},
		selectedFileTypes() {
			this.signatureType.fileType.map(item => {
				return { id: item, name: item }
			})

			return this.signatureType.fileType
		},
		formReadyForSubmit() {
			if (this.status.loading) {
				return false
			}
			if (this.status.readError) {
				return false
			}
			return true
		},
	},
	created() {
		this.loadFormData()
	},
	methods: {
		// ------------------------------------------------------------------------
		// Generate a unique element identifier given the name of the element and
		// the index offset in the array of elements
		//
		getElementId(name, index) {
			return 'signatureType_' + name + '_' + index
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				id: null,
				name: '',
				upload: '',
				download: '',
				requestLimit: 0,
				filesPerRequest: 0,
				fileType: [],
				format: '',
				method: '',
				additionalInfo: '',
				certifiedAuthority: '',
				certifiedAuthorityProxy: '',
				phoneRequired: false,

			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			const forApi = {
				name: item.name,
				requestLimit: item.requestLimit,
				filesPerRequest: item.filesPerRequest,
				fileType: item.fileType,
				format: item.format,
				method: item.method,
				additionalInfo: item.additionalInfo,
				certifiedAuthority: item.certifiedAuthority,
				certifiedAuthorityProxy: item.certifiedAuthorityProxy,
				phoneRequired: item.phoneRequired,
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
				requestLimit: item.requestLimit,
				filesPerRequest: item.filesPerRequest,
				fileType: item.fileType,
				format: item.format,
				method: item.method,
				additionalInfo: item.additionalInfo,
				certifiedAuthority: item.certifiedAuthority,
				certifiedAuthorityProxy: item.certifiedAuthorityProxy,
				phoneRequired: item.phoneRequired,
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
			if (!this.isFieldJsonValid('additionalInfo', item, true)) {
				this.focusField('additionalInfo', item)
				return false
			}

			return true
		},
	},
}
</script>
