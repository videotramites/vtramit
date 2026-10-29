<template>
	<div class="action-split" @click.stop>
		<button
			class="button primary action-split--main"
			:title="primaryAction.label"
			:disabled="primaryAction.disabled"
			@click="runPrimaryAction">
			<span class="icon" :class="primaryAction.icon || 'icon-upload'" aria-hidden="true" />
		</button>
		<button
			class="button action-split--toggle"
			:disabled="menuActions.length <= 1"
			@click="toggleMenu">
			...
		</button>
		<div v-if="isMenuOpen" class="action-split--menu">
			<button v-for="action in menuActions"
				:key="action.key"
				class="button action-split--menu--item"
				:disabled="action.disabled"
				@click="runAction(action)">
				<span class="icon" :class="action.icon" aria-hidden="true" />
				{{ action.label }}
			</button>
		</div>
	</div>
</template>

<script>
export default {
	name: 'ActionSplit',
	props: {
		actions: {
			type: Array,
			default: () => [],
		},
	},
	data: () => ({
		isMenuOpen: false,
	}),
	computed: {
		menuActions() {
			return this.actions.filter(action => action && action.show !== false)
		},
		primaryAction() {
			if (this.menuActions.length) {
				return this.menuActions[0]
			}
			return {
				key: 'none',
				label: this.fallbackLabel(),
				icon: 'icon-upload',
				disabled: true,
				handler: () => {},
			}
		},
	},
	mounted() {
		document.addEventListener('click', this.onDocumentClick)
	},
	beforeUnmount() {
		document.removeEventListener('click', this.onDocumentClick)
	},
	methods: {
		fallbackLabel() {
			if (typeof t === 'function') {
				return t('vtramit', 'Actions')
			}
			return 'Actions'
		},
		onDocumentClick() {
			this.isMenuOpen = false
		},
		toggleMenu() {
			this.isMenuOpen = !this.isMenuOpen
		},
		runPrimaryAction() {
			this.isMenuOpen = false
			if (!this.primaryAction.disabled && typeof this.primaryAction.handler === 'function') {
				this.primaryAction.handler()
			}
		},
		runAction(action) {
			this.isMenuOpen = false
			if (!action.disabled && typeof action.handler === 'function') {
				action.handler()
			}
		},
	},
}
</script>

<style lang="scss">
.vtramit .action-split {
	display: inline-flex;
	align-items: stretch;
	position: relative;
	flex-shrink: 0;
	align-self: start;
	width: 100px;
	margin-top: 3px;
}

.vtramit .action-split--main {
	border-top-right-radius: 0;
	border-bottom-right-radius: 0;
	height: 36px;
	line-height: 34px;
	padding-top: 0;
	margin-left: 20px !important;
	padding-bottom: 0;
	flex: 1 1 auto;
	min-width: 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

.vtramit .action-split--toggle {
	border-top-left-radius: 0;
	border-bottom-left-radius: 0;
	min-width: 32px;
	height: 36px;
	line-height: 34px;
	padding: 0 8px;
}

.vtramit .action-split--menu {
	position: absolute;
	right: 0;
	top: calc(100% + 2px);
	z-index: 10;
	display: flex;
	flex-direction: column;
	min-width: 160px;
	background: var(--color-main-background);
	border: none;
	border-radius: var(--border-radius-element);
	box-shadow: var(--shadow-card);
	overflow: hidden;
}

.vtramit .action-split--menu button.action-split--menu--item {
	width: 100%;
	text-align: left;
	border-radius: 0;
	height: 36px;
	line-height: 34px;
	margin: 0px;
	display: inline-flex;
	align-items: center;
	gap: 8px;
}

.vtramit .action-split--menu button.action-split--menu--item .icon {
	flex: 0 0 16px;
}

.vtramit .action-split--menu button.action-split--menu--item:not(:disabled):hover {
	background-color: var(--color-primary-element-dark, var(--color-primary-element));
	color: var(--color-primary-text);
}

.vtramit .action-split--main:not(:disabled):hover {
	background-color: var(--color-primary-element-dark, var(--color-primary-element));
	border-color: var(--color-primary-element-dark, var(--color-primary-element));
}
</style>
