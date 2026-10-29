<template>
	<nav class="vtramit-tabs">
		<a	
			data-test="general"
			:href="generateLink('/settings/admin/vtramit')"
			class="tab"
			:class="{ active: activeTab === 'general' }">
			{{ t('vtramit', 'General') }}
		</a>
		<a
			data-test="signature"
			:href="generateLink('/settings/admin/vtramit?tab=signatures')"
			class="tab"
			:class="{ active: activeTab === 'signatures' }">
			{{ t('vtramit', 'Signatures') }}
		</a>
		<a
			data-test="procedure"
			:href="generateLink('/settings/admin/vtramit?tab=procedures')"
			class="tab"
			:class="{ active: activeTab === 'procedures' }">
			{{ t('vtramit', 'Procedures') }}
		</a>
		<a
			data-test="public"
			:href="generateLink('/settings/admin/vtramit?tab=public')"
			class="tab"
			:class="{ active: activeTab === 'public' }">
			{{ t('vtramit', 'Public') }}
		</a>
		<a
			data-test="integrations"
			:href="generateLink('/settings/admin/vtramit?tab=integrations')"
			class="tab"
			:class="{ active: activeTab === 'integrations' }">
			{{ t('vtramit', 'Integrations') }}
		</a>
	</nav>
</template>

<script>
export default {
	name: 'NavigationTabs',
	props: {
		activeTab: {
			type: String,
			required: true,
		},
	},
	methods: {
		/**
		 * Generate a proper URL using Nextcloud's OC.generateUrl
		 * so it works with or without index.php in the path.
		 */
		generateLink(path) {
			if (this.OC && typeof this.OC.generateUrl === 'function') {
				return this.OC.generateUrl(path)
			}
			return path
		},
	},
}
</script>

<style scoped lang="scss">
.vtramit-tabs {
	display: flex;
	gap: 1rem;
	margin-bottom: 1rem;

	.tab {
		text-decoration: none;
		color: var(--color-main-text);
		padding: 0.5rem 1rem;
		border-bottom: 2px solid transparent;
		transition: border-color 0.2s;

		&:hover {
			border-bottom-color: var(--color-border);
		}

		&.active {
			font-weight: bold;
			border-bottom-color: var(--color-primary);
		}
	}
}
</style>
