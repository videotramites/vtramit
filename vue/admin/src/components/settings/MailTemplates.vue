<template>
	<div class="section" v-if="itemList && itemList.length" data-test="mailTemplates">
		<!-- Section header with expand button. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'E-mail templates') }}</h2>
			<Actions>
				<ActionLink icon="icon-add"
					href="#"
					:close-after-click="true"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new e-mail template') }}
				</ActionLink>
			</Actions>
		</div>

		<div class="help">
			<div class="block">
				<div class="ms-4">
					<p><strong>{{ t('vtramit', 'Feature deprecated!') }}</strong></p>
					<p>{{ t('vtramit', 'Migrate your e-mails to the message templates system. After you have migrated your e-mails to the message system, update your procedures and remove the following templates.') }}</p>
					<p>{{ t('vtramit', 'In future versions this feature will be removed!') }}</p>
				</div>
			</div>
		</div>

		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(mailTemplate, index) in itemList"
				:key="mailTemplate.id"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': mailTemplate.isExpanded
				}">
				<!-- Item title -->
				<div class="collapsible-header" @click="toggleItem(mailTemplate, $event)">
					<Actions>
						<ActionButton :icon="mailTemplate.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ mailTemplate.name }}
					</div>
				</div>
				<!-- Items form -->
				<div class="item-content" :hidden="!mailTemplate.isExpanded">
					<div class="field-item">
						<label :for="getElementId('name', index)">{{ t('vtramit', 'Name') }}</label>
						<input v-model="mailTemplate.name"
							:id="getElementId('name', index)"
							type="text"
							@input="mailTemplate.isChanged = true"
							@blur="mailTemplate.isChanged = true">
					</div>
					<div class="field-item">
						<label :for="getElementId('subject', index)">{{ t('vtramit', 'Subject') }}</label>
						<input v-model="mailTemplate.subject"
							:id="getElementId('subject', index)"
							type="text"
							@input="mailTemplate.isChanged = true"
							@blur="mailTemplate.isChanged = true">
					</div>
					<div class="field-item">
						<label :for="getElementId('template', index)">{{ t('vtramit', 'Template') }}</label>
						<textarea v-model="mailTemplate.template"
							:id="getElementId('template', index)"
							class="ckeditor"
							rows="10"
							@input="mailTemplate.isChanged = true"
							@blur="mailTemplate.isChanged = true" />
						<TokensPopup />
					</div>
					<!-- Individual item's CRUD buttons -->
					<div class="d-flex justify-end form-actions">
						<button v-if="!mailTemplate.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Delete'),
								autoHide: false,
								html: true
							}"
							class="btn-delete"
							@click="clickDeleteItem(mailTemplate)">
							<span class="icon icon-delete" />
							<span class="label">
								{{ t('vtramit', 'Delete') }}
							</span>
						</button>
						<button v-if="mailTemplate.isNew"
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
						<button v-if="mailTemplate.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Create'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !mailTemplate.isChanged"
							class="primary btn-save"
							@click="clickCreateItem(mailTemplate)">
							<span class="icon icon-upload" />
							<span class="label">
								{{ t('vtramit', 'Create') }}
							</span>
						</button>
						<button v-if="!mailTemplate.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Update'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !mailTemplate.isChanged"
							class="primary btn-save"
							@click="clickUpdateItem(mailTemplate)">
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
 * @module components/settings/MailTemplates
 *
 * Component to edit the mail templates config ... old style, without stores
 * or much of Vue's proper reactivity :'-(
 *
 */
import TokensPopup from '../ui/TokensPopup.vue'
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import { Actions, ActionButton, ActionLink } from '@nextcloud/vue'

export default {
	name: 'MailTemplates',
	components: {
		Actions,
		ActionButton,
		ActionLink,
		TokensPopup,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/mail-template',
		urlForInsert: 'apps/vtramit/web/2.0/mail-template',
		urlForUpdate: 'apps/vtramit/web/2.0/mail-template',
		urlForDelete: 'apps/vtramit/web/2.0/mail-template/%id',
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
			return !(this.status.readError);
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
			return 'mailTemplate_' + name + '_' + index;
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				id: null,
				name: '',
				subject: '',
				template: '',
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			const forApi = {
				name: item.name,
				subject: item.subject,
				template: item.template,
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
				subject: item.subject,
				template: item.template,
			}

			return forApi
		},
	},
}
</script>
