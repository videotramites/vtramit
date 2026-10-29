<template>
	<div class="section" data-test="topics">
		<!-- Section header with expand button. -->
		<div class="d-flex align-center section-header">
			<h2>{{ t('vtramit', 'Topics') }}</h2>
			<Actions>
				<ActionLink icon="icon-add"
					href="#"
					:close-after-click="true"
					@click="clickAddNewItem">
					{{ t('vtramit', 'Add new topic') }}
				</ActionLink>
			</Actions>
		</div>

		<!-- Section body -->
		<div class="item-list">
			<!-- Form that contains a list of items -->
			<div v-for="(topic, index) in itemList"
				:key="topic.id ?? ('new_' + index)"
				:class="{
					'item-wrapper': true,
					'collapsible-item': true,
					'expanded': topic.isExpanded
				}">
				<!-- Item title -->
				<div class="collapsible-header" @click="toggleItem(topic, $event)">
					<Actions>
						<ActionButton :icon="topic.isExpanded ? 'icon-triangle-s' : 'icon-triangle-e'">
							{{ t('vtramit', 'Expand') }}
						</ActionButton>
					</Actions>
					<div class="collapsible-title">
						{{ topic.name  }}
					</div>
				</div>

				<!-- Item form -->
				<div class="item-content" :hidden="!topic.isExpanded">
					<div class="field-item">
						<label :for="getElementId('name', index)">{{ t('vtramit', 'Name') }}</label>
						<input v-model="topic.name"
							:id="getElementId('name', index)"
							type="text"
							@input="topic.isChanged = true"
							@blur="topic.isChanged = true">
					</div>

					<div class="vtramit grid-2-columns">
						<div class="field-item">
							<label :for="getElementId('minRequiredDocs', index)">{{ t('vtramit', 'Minimum required documents') }}</label>
							<input v-model.number="topic.minRequiredDocs"
								:id="getElementId('minRequiredDocs', index)"
								type="number"
								min="0"
								step="1"
								@input="topic.isChanged = true"
								@blur="topic.isChanged = true">
						</div>

						<div class="field-item">
							<label :for="getElementId('videoPreservationTime', index)">
								{{ t('vtramit', 'Video preservation time (days)') }}
							</label>
							<input v-model.number="topic.videoPreservationTime"
								:id="getElementId('videoPreservationTime', index)"
								type="number"
								min="0"
								step="1"
								@input="topic.isChanged = true"
								@blur="topic.isChanged = true">
							<small class="hint">
								{{ t('vtramit', 'Use 0 for never') }}
							</small>
						</div>

						<div class="field-item">
							<label :for="getElementId('helpUrl', index)">{{ t('vtramit', 'Help URL') }}</label>
							<input v-model="topic.helpUrl"
								:id="getElementId('helpUrl', index)"
								type="url"
								@input="topic.isChanged = true"
								@blur="topic.isChanged = true">
						</div>

						<div class="field-item">
							<label :for="getElementId('helpTarget', index)">{{ t('vtramit', 'Help target') }}</label>
							<select v-model="topic.helpTarget"
								:id="getElementId('helpTarget', index)"
								class="select"
								@change="topic.isChanged = true">
								<option key="" value="">
									{{ t('vtramit', 'Default') }}
								</option>
								<option key="_blank" value="_blank">
									_blank
								</option>
							</select>
						</div>
					</div>


					<!-- Individual item's CRUD buttons -->
					<div class="d-flex justify-end form-actions">
						<button v-if="!topic.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Delete'),
								autoHide: false,
								html: true
							}"
							class="btn-delete"
							@click="clickDeleteItem(topic)">
							<span class="icon icon-delete" />
							<span class="label">
								{{ t('vtramit', 'Delete') }}
							</span>
						</button>

						<button v-if="topic.isNew"
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

						<button v-if="topic.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Create'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !topic.isChanged"
							class="primary btn-save"
							@click="clickCreateItem(topic)">
							<span class="icon icon-upload" />
							<span class="label">
								{{ t('vtramit', 'Create') }}
							</span>
						</button>

						<button v-if="!topic.isNew"
							v-tooltip="{
								placement: 'auto',
								trigger: 'hover',
								content: t('vtramit', 'Update'),
								autoHide: false,
								html: true
							}"
							:disabled="!formReadyForSubmit || !topic.isChanged"
							class="primary btn-save"
							@click="clickUpdateItem(topic)">
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
 * @module components/settings/Topics
 *
 * Component to edit Topics configuration
 */
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import { Actions, ActionButton, ActionLink } from '@nextcloud/vue'

export default {
	name: 'Topics',
	components: {
		Actions,
		ActionButton,
		ActionLink,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	data: () => ({
		urlForLoad: 'apps/vtramit/web/2.0/topic',
		urlForInsert: 'apps/vtramit/web/2.0/topic',
		urlForUpdate: 'apps/vtramit/web/2.0/topic',
		urlForDelete: 'apps/vtramit/web/2.0/topic/%id',
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
			return !(this.status.readError)
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
			return 'topic_' + name + '_' + index
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			return {
				id: null,
				name: '',
				minRequiredDocs: 0,
				videoPreservationTime: 0,
				helpUrl: '',
				helpTarget: '',
			}
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to create the item.
		//
		prepareItemForCreate(item) {
			return {
				name: item.name ?? '',
				minRequiredDocs: Number(item.minRequiredDocs) || 0,
				videoPreservationTime: Number(item.videoPreservationTime) || 0,
				helpUrl: item.helpUrl ?? '',
				helpTarget: item.helpTarget ?? '',
			}
		},
		// ------------------------------------------------------------------------
		// Prepare the data to be sent to the server to update the item.
		//
		prepareItemForUpdate(item) {
			return {
				id: item.id,
				name: item.name ?? '',
				minRequiredDocs: Number(item.minRequiredDocs) || 0,
				videoPreservationTime: Number(item.videoPreservationTime) || 0,
				helpUrl: item.helpUrl ?? '',
				helpTarget: item.helpTarget ?? '',
			}
		},
	},
}
</script>
