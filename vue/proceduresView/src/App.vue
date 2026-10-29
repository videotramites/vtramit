<!--
/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia Benítez <letizia@videoatencion.com>
 * @author Daniel Tamajon <dtamajon@litesolutions.es>
 * @author Marta González <marta.gonzalez@litesolutions.es>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan Reyné <ivanreyne@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 -->

<template>
	<Content app-name="appVTramitProcedures" :class="{ 'nav-hidden': !showNavigation, 'sidebar-hidden': !showSidebar }">
		<!-- <ProceduresNavigation v-show="showNavigation" /> -->
		<AppContent>
			<router-view />
			<!-- <StatusSnackbar /> -->
		</AppContent>
		<router-view name="sidebar" :visible="showSidebar" />
	</Content>
</template>

<script>
import { Content, AppContent } from '@nextcloud/vue'
// import ProceduresNavigation from './components/procedures/ProceduresNavigation.vue'

export default {
	name: 'App',
	components: {
		Content,
		AppContent,
		// ProceduresNavigation,
	},
	data: () => ({
	}),
	computed: {
		showNavigation() {
			return this.$store.getters['appStatus/getShowNavigation']
		},
		showSidebar() {
			return this.$store.getters['appStatus/getShowSidebar']
		},
	},
	created() {
		this.$store
			.dispatch('statuses/readAll')
			.then((/* result */) => {
				return this.$store.dispatch('channels/readAll')
			})
			.then((/* result */) => {
				return this.$store.dispatch('context/readContext')
			})
			.then((/* result */) => {
				return this.$store.dispatch('procedureTypes/readAll')
			})
			.then((/* result */) => {
				// console.log('kkk', this.$store.getters['procedureTypes/readAll'])
			})
			.catch((/* error */) => {
				// console.log('error1', error)
			})
	},
}
</script>

<style lang="scss" scoped>
	#content-vue {
		#app-content {
			transition: margin-left 100ms ease;
			position: relative;
			overflow-x: hidden;
			align-items: stretch;
		}
		#app-sidebar {
			transition: max-width 100ms ease;
		}
		&.nav-hidden {
			#app-content {
				margin-left: 0;
			}
		}
		&.sidebar-hidden {
			#app-sidebar {
				max-width: 0;
				min-width: 0;
			}
		}
	}
</style>

<style lang="scss">

	.app-sidebar .app-sidebar-header__desc .app-sidebar-header__title-container .app-sidebar-header__maintitle-container .app-sidebar-header__menu {

		button:not(.button-vue,[class^=vs__]):not(.app-navigation-entry-button) {
			margin-top: 0px;
		}
	}

	input[type='text'] {
		width: 100%;
	}

	.field-required {
		color: #000099;
	}

	.mx-datepicker {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}

	select {
		width: 100%;
	}

	.error_message {
		color: red;
		font-size: .75rem;
		position: relative;
		top: -38px;
		left: 13px;
		height: 0;
		pointer-events: none;
	}

	.board-list {

		.board-list-row {
			align-items: center;
			border-bottom: 1px solid #ededed;
			display: flex;
		}

		.board-list-row:not(.board-list-header-row):hover {
			transition: background-color 0.3s ease;
			background-color: var(--color-background-dark);
		}

		.board-list-header-row {
			color: var(--color-text-lighter);
		}

		.board-list-bullet-cell,
		.board-list-avatars-cell {
			padding: 6px 15px;
		}

		.board-list-avatars-cell {
			flex: 0 0 50px;
		}

		.board-list-avatar,
		.board-list-bullet {
			height: 32px;
			width: 32px;
		}

		.board-list-title-cell {
			flex: 1 0 auto;
			padding: 15px;
		}

		.board-list-actions-cell {
			// placeholder
			flex: 0 0 50px;
		}
	}

	.d-flex {
		display: flex;
	}

	.justify-end {
		justify-content: end;
	}

	.justify-space-between {
		justify-content: space-between;
	}

	.align-center {
		align-items: center;
	}

	.flex-grow-1,
	.flex-spacer {
		flex-grow: 1;
	}

	.ma {
		margin: 1em;
	}

	.mt {
		margin-top: 1em;
	}

	.mb {
		margin-bottom: 1em;
	}

	.ml {
		margin-left: 1em;
	}

	.mr {
		margin-right: 1em;
	}

	.fixed-bottom {
		position: sticky;
		bottom: 0px;
		background-color: #fff;
		border-top: 1px solid;
		padding-top: 6px;
		padding-bottom: 6px;
	}

	// .fixed-bottom {
	// position: fixed;
	// bottom: 0;
	// width: 27vw;
	// min-width: 300px;
	// max-width: 500px;
	// border-top: 1px solid #000;
	// background-color: #fff;
	// padding-top: 1em;
	// }

	.controls {
		display: flex;

		.board-title {
			display: flex;
			align-items: center;

			h2 {
				margin: 0;
				margin-right: 10px;
			}

			.board-bullet {
				display: inline-block;
				width: 20px;
				height: 20px;
				border: none;
				border-radius: 50%;
				background-color: #aaa;
				margin: 12px;
				margin-left: -4px;
			}
		}

		#stack-add form {
			display: flex;
		}
	}

	#app-navigation-toggle-custom {
		position: static;
		width: 44px;
		height: 44px;
		cursor: pointer;
		opacity: 1;
		display: inline-block !important;
	}

	.board-actions {
		order: 100;
		display: flex;
		justify-content: flex-end;
	}

	.board-action-buttons {
		display: flex;
		button {
			border: 0;
			width: 44px;
			margin: 0 0 0 -1px;
			background-color: transparent;
		}
	}

	.uploads-state {
		border-radius: 50%;
	}

	.uploads-state.loaded {
		background-color: #87CEEB;
	}

	.signature-status {
		border-radius: 50%;
	}

	.signature-status.initializing,
	.signature-status.request-to-start,
	.signature-status.waiting-mailing,
	.signature-status.waiting-signature {
		background-color: #FFFACD;
	}

	.signature-status.signed {
		background-color: #A4FCB4;
	}

	.signature-status.cancelled,
	.signature-status.cancelled-out-of-time,
	.signature-status.error {
		background-color: #FF4500;
	}

	#filter-panel {
		height: auto !important;
		background-color: #e5e9e9;

		.filter {
			position: relative;
			padding: 1em;
			width: 100%;
			/* max-height: 80vh; */
			overflow: visible; /* Required for the datetimepicker component */
			display: flex;

			h3 {
				margin-top: 0px;
				margin-bottom: 5px;
			}

			.two-col-grid {
				display: grid;
				grid-template-columns: 1fr 1fr;
				grid-gap: 10px;
				padding: 0 1rem;
			}

			&--group {
				margin-right: 10px;

				&.department {
					width: 300px;

				}

				&--datetime {
					width: 280px;

					.mx-datepicker-main .mx-calendar {
						width: 280px;
					}
				}
			}

			&--item {

				&--checkbox {
					text-wrap: nowrap;
				}

				&--radio {
					text-wrap: nowrap;
				}

				input + label {
					display: block;
					padding: 6px 0;

					.avatardiv {
						vertical-align: middle;
						margin-bottom: 2px;
						margin-right: 3px;
					}

					.label {
						padding: 5px;
						border-radius: 3px;
					}
				}
			}

			input {
				border-width: 1px;
			}

			.mx-datepicker {
				input.mx-input {
					background-color: #FFFFFF;
    			background-clip: border-box;
					border-color: #7d7d7d;
				}
			}

			&--control {
				&--checkbox-group {
					background-color: #FFFFFF;
					overflow: auto;
					border: 1px solid #7d7d7d;
					max-width: 150px;
					max-height: 180px;
					width: 150px;
					padding-right: 2px;

					&.department {
						max-width: 100%;
						width: 100%;
					}
				}
			}

			&-actions {
				padding: 1em;
			}

		}
	}


	.tooltip-inner.popover-inner {
		text-align: left;
	}

	table {
		border-collapse: collapse;
		width: 100%;

		tr {
			border-bottom: 1px solid #ededed;
		}

		tr:hover {
			transition: background-color 0.3s ease;
			background-color: var(--color-background-dark);
		}

		th, td {
			text-align: left;
			padding: 8px;
			padding: 15px;
		}

		th {
			&>a.procedure-list-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				i {
					margin-left: 8px;
				}
			}
		}
	}

	.paginator {
		&-row {
			display: flex;
			align-items: center;
			flex-wrap: nowrap;
			flex-direction: row;
			justify-content: center;
			margin: 1rem;
		}
		&-item {
			padding: 0.5rem;
    	border: white 1px solid;
			/* color: var(--color-primary-element-text); */
			width: 38px;
			text-align: center;
			&:hover {
				background-color: var(--color-primary-element-hover);
				color: #FFFFFF;
			}
			&.active {
				background-color: var(--color-primary-element);
				color: #FFFFFF;
				font-weight: bold;
			}
		}
	}

	div#filter-panel+div.procedures-table {
		overflow: scroll;
		height: calc(100% - 44px) !important;
	}

	#app-settings-content {
		p {
			margin-top: 20px;
			margin-bottom: 20px;
			color: var(--color-text-light);
		}
	}

	.video_waiting {
		background-color: #a4fcb4 !important;
		border-radius: 50% !important;
	}

	.signature_waiting {
		background-color: #a4fcb4 !important;
		border-radius: 50% !important;
	}

	.unsaved-changes {
		color: #FF8C00;
		margin-left: 10px;
	}

	.toastify.dialogs {
			.toast-undo-button, .toast-close {
				&.toast-close::before {
					background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzODQgNTEyIj48IS0tIUZvbnQgQXdlc29tZSBGcmVlIDYuNi4wIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlL2ZyZWUgQ29weXJpZ2h0IDIwMjQgRm9udGljb25zLCBJbmMuLS0+PHBhdGggZD0iTTM0Mi42IDE1MC42YzEyLjUtMTIuNSAxMi41LTMyLjggMC00NS4zcy0zMi44LTEyLjUtNDUuMyAwTDE5MiAyMTAuNyA4Ni42IDEwNS40Yy0xMi41LTEyLjUtMzIuOC0xMi41LTQ1LjMgMHMtMTIuNSAzMi44IDAgNDUuM0wxNDYuNyAyNTYgNDEuNCAzNjEuNGMtMTIuNSAxMi41LTEyLjUgMzIuOCAwIDQ1LjNzMzIuOCAxMi41IDQ1LjMgMEwxOTIgMzAxLjMgMjk3LjQgNDA2LjZjMTIuNSAxMi41IDMyLjggMTIuNSA0NS4zIDBzMTIuNS0zMi44IDAtNDUuM0wyMzcuMyAyNTYgMzQyLjYgMTUwLjZ6Ii8+PC9zdmc+") !important;
					content: "X" !important;
					filter: var(--background-invert-if-dark) !important;
					display: inline-block;
					width: 16px;
					height: 16px;
					background-repeat: no-repeat;
				}
			}
		}

	.theme--dark {
		.toastify.dialogs {
			.toast-close {
				&.toast-close::before {
					background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzODQgNTEyIj48IS0tIUZvbnQgQXdlc29tZSBGcmVlIDYuNi4wIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlL2ZyZWUgQ29weXJpZ2h0IDIwMjQgRm9udGljb25zLCBJbmMuLS0+PHBhdGggZD0iTTM0Mi42IDE1MC42YzEyLjUtMTIuNSAxMi41LTMyLjggMC00NS4zcy0zMi44LTEyLjUtNDUuMyAwTDE5MiAyMTAuNyA4Ni42IDEwNS40Yy0xMi41LTEyLjUtMzIuOC0xMi41LTQ1LjMgMHMtMTIuNSAzMi44IDAgNDUuM0wxNDYuNyAyNTYgNDEuNCAzNjEuNGMtMTIuNSAxMi41LTEyLjUgMzIuOCAwIDQ1LjNzMzIuOCAxMi41IDQ1LjMgMEwxOTIgMzAxLjMgMjk3LjQgNDA2LjZjMTIuNSAxMi41IDMyLjggMTIuNSA0NS4zIDBzMTIuNS0zMi44IDAtNDUuM0wyMzcuMyAyNTYgMzQyLjYgMTUwLjZ6Ii8+PC9zdmc+") !important;
					content: "X" !important;
					filter: var(--background-invert-if-dark) !important;
					display: inline-block;
					width: 16px;
					height: 16px;
					background-repeat: no-repeat;
				}
			}
		}
	}
</style>
