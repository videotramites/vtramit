<template>
	<AppNavigation>
		<template slot="list">
			<AppNavigationItem :title="t('vtramit', 'Appointments list')" icon="icon-home" />
		</template>
		<template slot="footer">
			<AppNavigationSettings v-if="navSettingsShown">
				<div>
					<Multiselect v-model="groupLimit"
						class="icon-loading-small"
						open-direction="bottom"
						:options="groups"
						:multiple="true"
						:placeholder="t('vtramit', 'Group assignation')"
						label="displayname"
						track-by="id"
						@input="updateConfig" />
					<p>{{ t('vtramit', 'Select groups that have permission to assign appointments to other users. Every user in the groups selected will be capable to do it.') }}</p>
				</div>
			</AppNavigationSettings>
		</template>
	</AppNavigation>
</template>

<script>
/**
 * @module components/procedures/ProceduresNavigation
 *
 * Component to handle the list filter options.
 *
 */
import { AppNavigation, AppNavigationItem, AppNavigationSettings, Multiselect } from '@nextcloud/vue'

export default {
	name: 'ProceduresNavigation',
	components: {
		AppNavigation,
		AppNavigationItem,
		AppNavigationSettings,
		Multiselect,
	},
	data: () => ({
		// TODO: check the old app for how to init
		navSettingsShown: true,
		groups: [],
		groupLimit: '',
	}),
	computed: {
	},
	methods: {
		updateConfig() {

		},
	},
}
</script>
