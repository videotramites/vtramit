<template>
	<div class="section" data-test="webhooks">
		<!-- Section header with expand button. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'Webhooks') }}</h2>
			<Actions>
				<ActionLink icon="icon-add"
					href="#"
					:close-after-click="true"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new webhook') }}
				</ActionLink>
			</Actions>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(webhook, index) in itemList"
				:key="webhook.id"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': webhook.isExpanded
				}">
				<!-- Item title -->
				<div class="collapsible-header" @click="toggleItem(webhook, $event)">
					<Actions>
						<ActionButton :icon="webhook.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ webhook.name }}
					</div>
				</div>
				<!-- Items form -->
				<div class="item-content" :hidden="!webhook.isExpanded">
					<!-- Name, Endpoint -->
					<div class="grid-2-columns">
						<div class="field-item">
							<label :for="getElementId('name', index)">{{ t('vtramit', 'Name') }}</label>
							<input v-model="webhook.name"
								:id="getElementId('name', index)"
								type="text"
								@input="webhook.isChanged = true"
								@blur="webhook.isChanged = true">
						</div>
						<div class="field-item">
							<label :for="getElementId('endpoint', index)">{{ t('vtramit', 'Endpoint') }}</label>
							<input v-model="webhook.endpoint"
								:id="getElementId('endpoint', index)"
								type="text"
								@input="webhook.isChanged = true"
								@blur="webhook.isChanged = true">
						</div>
					</div>
					<!-- Auth Type -->
					<div class="grid-2-columns">
						<div class="field-item">
							<label>{{ t('vtramit', 'Auth Type') }}</label>
							<select v-model="webhook.authType"
								:id="getElementId('authtype', index)"
								@change="webhook.isChanged = true"
								@blur="webhook.isChanged = true"
								required>
								<option v-for="option in authTypes" :key="option.id" :value="option.id" @click="webhook.isChanged = true">{{ option.name }}</option>
							</select>
						</div>
						<!-- Auth Type Data -->
						<div class="field-item" v-if="webhook.authType === 'basic-auth'">
							<label :for="getElementId('user', index)">{{ t('vtramit', 'User') }}</label>
							<input v-model="webhook.authTypeData.user"
								:id="getElementId('user', index)"
								type="text"
								@input="webhook.isChanged = true"
								@blur="webhook.isChanged = true"
								required>
							<label :for="getElementId('password', index)">{{ t('vtramit', 'Password') }}</label>
							<input v-model="webhook.authTypeData.password"
								:id="getElementId('password', index)"
								type="text"
								@input="webhook.isChanged = true"
								@blur="webhook.isChanged = true"
								required>
						</div>
						<div v-if="webhook.authType === 'token'" class="field-item">
							<label :for="getElementId('token', index)">{{ t('vtramit', 'Token') }}</label>
							<input v-model="webhook.authTypeData.token"
								:id="getElementId('token', index)"
								type="text"
								@input="webhook.isChanged = true"
								@blur="webhook.isChanged = true"
								required>
						</div>
					</div>
					<!-- Event -->
					<h3>{{ t('vtramit', 'Events') }}</h3>
					<div class="field-item">
						<select v-model="webhook.event" multiple class="multiselect">
							<option v-for="event in events" :key="event.id" :value="event.id" @click="webhook.isChanged = true">{{ event.name }}</option>
						</select>
					</div>

					<!-- Individual item's CRUD buttons -->
					<div class="d-flex justify-end form-actions">
						<button v-if="!webhook.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Delete'),
								autoHide: false,
								html: true
							}"
							class="btn-delete"
							@click="clickDeleteItem(webhook)">
							<span class="icon icon-delete" />
							<span class="label">
								{{ t('vtramit', 'Delete') }}
							</span>
						</button>
						<button v-if="webhook.isNew"
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
						<button v-if="webhook.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Create'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !webhook.isChanged"
							class="primary btn-save"
							@click="clickCreateItem(webhook)">
							<span class="icon icon-upload" />
							<span class="label">
								{{ t('vtramit', 'Create') }}
							</span>
						</button>
						<button v-if="!webhook.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Update'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !webhook.isChanged"
							class="primary btn-save"
							@click="clickUpdateItem(webhook)">
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
 * @module components/settings/Webhook
 *
 *
 */
import TwoColumnsSelector from '../ui/TwoColumnsSelector.vue'
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import { Actions, ActionButton, ActionLink } from '@nextcloud/vue'

export default {
	name: 'Webhook',
	components: {
		Actions,
		ActionButton,
		ActionLink,
		TwoColumnsSelector,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/webhook',
		urlForInsert: 'apps/vtramit/web/2.0/webhook',
		urlForUpdate: 'apps/vtramit/web/2.0/webhook',
		urlForDelete: 'apps/vtramit/web/2.0/webhook/%id',
	}),
	computed: {
		// ------------------------------------------------------------------------
		// Computed to control a bit when the CRUD actions are happening (with
		// the help of the ComponentStatus mixin!) so we can easily prevent people
		// clicking buttons twice while they are waiting for the server response.
		//
		authTypes() {
			return [
				{"id": "no-auth", "name": "No Auth"},
				{"id": "basic-auth", "name": "Basic Auth"},
				{"id": "token", "name": "Bearer Token"},
			]
		},
		events() {
			return [
				{"id": "create-procedure", "name": "Create Procedure"},
				{"id": "update-procedure", "name": "Update Procedure"},
				{"id": "delete-procedure", "name": "Delete Procedure"},
				{"id": "create-stakeholder", "name": "Create Stakeholder"},
				{"id": "update-stakeholder", "name": "Update Stakeholder"},
				{"id": "delete-stakeholder", "name": "Delete Stakeholder"},
			]
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
			return 'webhook_' + name + '_' + index;
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
				id: null,
				name: '',
				endpoint: '',
				authType: '',
				authTypeData: {},
				event: [],
			}
			return newItem
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			const forApi = {
				name: item.name,
				endpoint: item.endpoint,
				authType: item.authType,
				authTypeData: item.authTypeData,
				event: item.event,
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
				endpoint: item.endpoint,
				authType: item.authType,
				authTypeData: item.authTypeData,
				event: item.event,
			}

			return forApi
		},
	},
}
</script>
