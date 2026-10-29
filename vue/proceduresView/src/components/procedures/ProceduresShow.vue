<template>
	<!-- TODO: The sidebar itself ideally should not be part of this component -->
	<AppSidebar :title="t('vtramit', 'Add new appointment')"
		@close="clickClose">
		<AppSidebarTab id="create" :name="t('vtramit', 'Appointment')" icon="icon-calendar">
			<div>
				<label>{{ t('vtramit', 'Appointment ID') }}</label>
				<span>{{ procedure.externalId }}</span>
			</div>

			<div>
				<label>{{ t('vtramit', 'Department') }}</label>
				<span>{{ procedure.assignedGroupId }}</span>
			</div>

			<div>
				<label>{{ t('vtramit', 'Assigned to') }}</label>
				<span>{{ procedure.assignedUserId }}</span>
			</div>

			<div>
				<label>{{ t('vtramit', 'Topic') }}</label>
				<span>{{ procedure.topic }}</span>
			</div>

			<div>
				<label>{{ t('vtramit', 'Comments') }}</label>
				<span>{{ procedure.comments }}</span>
			</div>

			<input type="button"
				class="primary"
				:value="t('vtramit', 'Edit')"
				@click="clickEdit">
		</AppSidebarTab>
	</AppSidebar>
</template>

<script>
/**
 * @module components/procedures/ProceduresShow
 *
 * Component to show one procedure details.
 *
 */
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import MessageManager from '../../mixins/messageManager.mixin.js'
import { AppSidebar, AppSidebarTab } from '@nextcloud/vue'

export default {
	name: 'ProceduresShow',
	components: {
		AppSidebar,
		AppSidebarTab,
		// DatetimePicker,
	},
	mixins: [ComponentStatus, MessageManager],
	data: () => ({}),
	computed: {
		objectId() {
			let objectId = ''
			if (this.$route.params.id) {
				objectId = this.$route.params.id.toString()
			}
			return objectId
		},
		// When the procedure id changes we use the store getter to "get" the
		// object that has (or the temporary one that will have) the procedure data.
		// Note that this is different than the store action used to read the
		// actual data from the backend, they both work "in parallel".
		procedure() {
			return this.$store.getters['procedures/readById'](this.objectId)
		},
	},
	watch: {
		// If the procedure id changes and we have not edit any field we trigger
		// the store action to load the data. Note that this is different than
		// the getter used to display the data, they both work "in parallel".
		objectId: {
			immediate: true,
			handler() {
				this.setStatusReading()
				this.$store
					.dispatch('procedures/readById', this.objectId)
					.then((/* result */) => {
						this.setStatusReadSuccess()
					})
					.catch((error) => {
						this.setStatusReadError()
						this.manageException(error, t('vtramit', 'Could not load the procedure data'))
					})
			},
		},
	},
	methods: {
		clickClose() {
			this.$router.push({ name: 'dashboard' })
		},
		clickEdit() {
			this.$router.push({ name: 'procedures-edit', params: { id: this.objectId } })
		},
	},
}
</script>
