<template>
	<div class="section" data-test="messageTemplates">
		<!-- Section header with expand button. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'Message templates') }}</h2>
			<Actions>
				<ActionLink icon="icon-add"
					href="#"
					:close-after-click="true"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new message template') }}
				</ActionLink>
			</Actions>
		</div>
		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(messageTemplate, index) in itemList"
				:key="messageTemplate.id"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': messageTemplate.isExpanded
				}">
				<!-- Item title -->
				<div class="collapsible-header" @click="toggleItem(messageTemplate, $event)">
					<Actions>
						<ActionButton :icon="messageTemplate.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ messageTemplate.name }}
					</div>
				</div>
				<!-- Items form -->
				<div class="item-content" :hidden="!messageTemplate.isExpanded">
					<div class="field-item">
						<label :for="getElementId('name', index)">{{ t('vtramit', 'Name') }}</label>
						<input v-model="messageTemplate.name"
							:id="getElementId('name', index)"
							type="text"
							@input="messageTemplate.isChanged = true"
							@blur="messageTemplate.isChanged = true">
					</div>
					<div class="field-item">
						<label :for="getElementId('channel', index)">{{ t('vtramit', 'Channel') }}</label>
						<select
							class="channel-select"
							v-model="messageTemplate.channel"
							:id="getElementId('channel', index)"
							@change="onChannelChange(messageTemplate, index)">
							<option v-for="opt in channelOptions" :key="opt.value" :value="opt.key">
								{{ opt.value }}
							</option>
						</select>
					</div>
					<div class="field-item">
						<label :for="getElementId('subject', index)">{{ t('vtramit', 'Subject') }}</label>
						<input v-model="messageTemplate.subject"
							:id="getElementId('subject', index)"
							type="text"
							@input="messageTemplate.isChanged = true"
							@blur="messageTemplate.isChanged = true">
					</div>
					<div class="field-item">
						<label :for="getElementId('template', index)">{{ t('vtramit', 'Template') }}</label>
						<textarea v-if="isTextarea(messageTemplate.channel) || isTextareaRich(messageTemplate.channel)"
							v-model="messageTemplate.template"
							:id="getElementId('template', index)"
							:key="editorKey(index)"
							:class="{ 'ckeditor': isTextareaRich(messageTemplate.channel) }"
							rows="10"
							@input="messageTemplate.isChanged = true"
							@blur="messageTemplate.isChanged = true" />
						<TokensPopup />
					</div>
					<!-- Individual item's CRUD buttons -->
					<div class="d-flex justify-end form-actions">
						<button v-if="!messageTemplate.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Delete'),
								autoHide: false,
								html: true
							}"
							class="btn-delete"
							@click="clickDeleteItem(messageTemplate)">
							<span class="icon icon-delete" />
							<span class="label">
								{{ t('vtramit', 'Delete') }}
							</span>
						</button>
						<button v-if="messageTemplate.isNew"
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
						<button v-if="messageTemplate.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Create'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !messageTemplate.isChanged"
							class="primary btn-save"
							@click="clickCreateItem(messageTemplate)">
							<span class="icon icon-upload" />
							<span class="label">
								{{ t('vtramit', 'Create') }}
							</span>
						</button>
						<button v-if="!messageTemplate.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Update'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !messageTemplate.isChanged"
							class="primary btn-save"
							@click="clickUpdateItem(messageTemplate)">
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
 * @module components/settings/MessageTemplates
 *
 * Component to edit the message templates config ... old style, without stores
 * or much of Vue's proper reactivity :'-(
 *
 */
import TokensPopup from '../ui/TokensPopup.vue'
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import MultipleSelector from '../ui/MultipleSelector.vue'
import { Actions, ActionButton, ActionLink } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'MessageTemplates',
	components: {
		Actions,
		ActionButton,
		ActionLink,
		TokensPopup,
		MultipleSelector,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/message-template',
		urlForInsert: 'apps/vtramit/web/2.0/message-template',
		urlForUpdate: 'apps/vtramit/web/2.0/message-template',
		urlForDelete: 'apps/vtramit/web/2.0/message-template/%id',
		urlForChannels: 'apps/vtramit/web/2.0/context/message-channel',
		channelOptions: [],
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
		channelMap() {
			return this.channelOptions.reduce((m, c) => ((m[c.key] = c), m), {})
		},
	},
	created() {
		// Once the component is created we load the data needed for the form.
		this.loadFormData()
		this.loadChannels()
	},
	beforeDestroy() {
		// Ensure all CKEditor instances are properly destroyed
		this.cleanupCKEditors(this.$el)
	},
	methods: {
		async loadChannels() {
			const urlForApi = generateUrl(this.urlForChannels)

			return axios({
				method: 'GET',
				url: urlForApi,
			}).then((result) => {
				if (result.data?.result === 'OK' && Array.isArray(result.data.list)) {
					this.channelOptions = result.data.list
						.filter(row => {
							const v = row?.availableForStaff || row?.availableForStakeholder
							return v === true || v === 'true' || v === 1
						})
						.map((row) => ({
							key: row.key ?? '',
							value: row.value ?? '',
							editor: String(row.editor ?? 'plain').toLowerCase(),
						}))
						.filter(o => o.value)

					return true
				}

				showError(t('vtramit', 'Error loading channels'))
				return false
			}).catch((error) => {
				showError(t('vtramit', 'Error loading channels'))
				throw error
			})
		},
		// ------------------------------------------------------------------------
		// Returns the channel option object that matches the provided key.
		// If no channel is found, it returns undefined.
		// This helper centralizes channel lookups to keep rendering logic simple.
		//
		getChannelByKey(key) {
			return this.channelOptions.find(c => c.key === key)
		},
		// ------------------------------------------------------------------------
		// Checks whether the given channel should be rendered with a plain <textarea>.
		// It returns true when the channel configuration specifies editor === 'plain'.
		// Used by the template v-if to decide the editor type.
		//
		isTextarea(channelKey) {
			return this.channelMap[channelKey]?.editor === 'plain'
		},
		// ------------------------------------------------------------------------
		// Checks whether the given channel should be rendered with a rich text editor.
		// It returns true when the channel configuration specifies editor === 'rich'.
		// When true, the <textarea> receives the 'ckeditor' class and is initialized.
		//
		isTextareaRich(channelKey) {
			return this.channelMap[channelKey]?.editor === 'rich'
		},
		// ------------------------------------------------------------------------
		// Returns the Vue key used by the editor field to control its re-rendering.
		// The key is deterministic per row (tpl-<index>) so the textarea remains
		// stable across edits. The optional second parameter is reserved for future
		// use in case we need to force remount on channel changes.
		//
		editorKey(index, channelKey) {
			return `tpl-${index}`
		},
		// ------------------------------------------------------------------------
		// Handle channel change: ensure CKEditor is destroyed/initialized as needed
		//
		// 1) Wait for DOM/class changes (nextTick)
		// 2) Destroy obsolete CKEditor instances (plain)
		// 3) Initialize new CKEditor instances (rich)
		//
		onChannelChange(item, index) {
			item.isChanged = true
			this.$nextTick(() => {
				this.cleanupCKEditors(this.$el)
				this.initializeCkeditor(this.$el)
			})
		},
		// ------------------------------------------------------------------------
		// Generate a unique element identifier given the name of the element and
		// the index offset in the array of elements
		//
		getElementId(name, index) {
			return 'messageTemplate_' + name + '_' + index;
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
				channel: this.channelOptions?.[0]?.key || '',
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
				channel: item.channel,
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
				channel: item.channel,
			}

			return forApi
		},
	},
}
</script>
<style>
.field-item .channel-select {
  width: auto;
  max-width: 360px;
  min-width: 220px;
  display: inline-block;
}
</style>
