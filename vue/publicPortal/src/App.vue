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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 -->

<template>
	<router-view />
</template>

<script>

/* Hack to remove the new css in NC25, which breaks our UI */
const cssNode = document.querySelector('link[href^="/core/css/server.css"')
cssNode && cssNode.parentNode.removeChild(cssNode)

export default {
	name: 'App',
	data() {
		return {}
  },
}
</script>

<style lang="scss">
	html,
	body,
	.app-public,
	.v-application {
		height: 100%;
	}

	input[type='checkbox'] {
		cursor: pointer;
	}

	.vtramit-info {
		margin-top: 10px;
		font-weight: bold;
		text-align: center;
	}

	.vtramit-error {
		margin-top: 10px;
		font-weight: bold;
		color: red;
		text-align: center;
	}

	.pt-40 {
		padding-top: 40px;
	}

	.layout-base .v-application--wrap {
		min-height: 100%;
	};

	div.disabled {
		pointer-events: none;
		opacity: 0.4;
	}

	/**
	 * Login page: down to bottom arrow
	 */
	.down-to-bottom-arrow {
	 	position: fixed;
    bottom: 0;
		left: 50%;
    transform: translateX(-50%);
		> a {
			background-image: url('data:image/svg+xml;base64,PHN2ZyBmaWxsPSIlMjMwMDAwMDAiIGhlaWdodD0iODAwcHgiIHdpZHRoPSI4MDBweCIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgNDkwLjQgNDkwLjQiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxnPjxwYXRoIGQ9Ik00OTAuNCwyNDUuMkM0OTAuNCwxMTAsMzgwLjQsMCwyNDUuMiwwUzAsMTEwLDAsMjQ1LjJzMTEwLDI0NS4yLDI0NS4yLDI0NS4yUzQ5MC40LDM4MC40LDQ5MC40LDI0NS4yeiBNMjQuNSwyNDUuMmMwLTEyMS43LDk5LTIyMC43LDIyMC43LTIyMC43czIyMC43LDk5LDIyMC43LDIyMC43cy05OSwyMjAuNy0yMjAuNywyMjAuN1MyNC41LDM2Ni45LDI0LjUsMjQ1LjJ6Ii8+PHBhdGggZD0iTTI1My45LDM2MC40bDY4LjktNjguOWM0LjgtNC44LDQuOC0xMi41LDAtMTcuM3MtMTIuNS00LjgtMTcuMywwbC00OCw0OFYxMzguN2MwLTYuOC01LjUtMTIuMy0xMi4zLTEyLjNzLTEyLjMsNS41LTEyLjMsMTIuM3YxODMuNGwtNDgtNDhjLTQuOC00LjgtMTIuNS00LjgtMTcuMywwcy00LjgsMTIuNSwwLDE3LjNsNjguOSw2OC45YzIuNCwyLjQsNS41LDMuNiw4LjcsMy42UzI1MS41LDM2Mi44LDI1My45LDM2MC40eiIvPjwvZz48L2c+PC9zdmc+');
			display: inline-block;
			width: 30px;
			height: 30px;
			background-size: cover;
			background-repeat: no-repeat;
			text-decoration: none;
			text-align: center;
			line-height: 30px;
			background-color: white;
    	border-radius: 20px;
		}
	}

	.logo {
		margin-right: auto;
		padding-left: 10px;
	}

	img.logoLogin {
		margin-right: auto;
		padding-left: 10px;
		vertical-align: middle;
		max-height: 60px;
	}

	.simulate-expansion-panel {
		flex: 1 0 100%;
		max-width: 100%;
		position: relative;
	}
</style>
