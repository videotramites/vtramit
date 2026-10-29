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
	<div class="protocol-steps">
		<div v-if="loading" class="protocol-steps--loading d-flex justify-center align-center pa-4">
			<v-progress-circular indeterminate color="primary" />
		</div>

		<div v-else-if="steps.length === 0" class="protocol-steps--empty pa-4 text-center text--disabled">
			{{ t('vtramit', 'No protocol steps defined') }}
		</div>

		<v-list v-else dense class="protocol-steps--list">
			<v-list-item v-for="(step, index) in orderedSteps"
				:key="step.id"
				class="protocol-steps--item">
				<v-list-item-content>
					<div class="d-flex align-center">
						<!-- Step number + completion indicator -->
						<v-avatar size="28"
							class="mr-3 flex-shrink-0"
							:class="{ 'protocol-steps--avatar-clickable': isStepCompleted(step) }"
							:color="isStepCompleted(step) ? 'success' : (isStepActive(index) ? 'primary' : 'grey lighten-2')"
							@click.stop="onReturnToStep(step)">
							<v-icon v-if="isStepCompleted(step)"
								small
								color="white"
								:title="t('vtramit', 'Do you want to return to this step?')">
								mdi-check
							</v-icon>
							<span v-else class="caption white--text">{{ index + 1 }}</span>
						</v-avatar>

						<!-- Step name -->
						<div class="flex-grow-1">
							<div class="body-2"
								:class="{ 'text--disabled': !isStepCompleted(step) && !isStepActive(index) }">
								{{ step.name }}
							</div>
							<div v-if="isStepCompleted(step)" class="caption text--disabled">
								{{ formatCompletedAt(step.completedAt) }}
								<span v-if="step.completedBy"> &mdash; {{ step.completedBy }}</span>
							</div>
						</div>

						<!-- Complete button -->
						<v-btn v-if="!isStepCompleted(step) && isStepActive(index)"
							x-small
							color="primary"
							:loading="completing === step.id"
							class="ml-2"
							@click="onCompleteStep(step)">
							{{ t('vtramit', 'Done') }}
						</v-btn>

						<!-- Help button (only if description exists) -->
						<v-btn v-if="step.description"
							icon
							x-small
							:title="t('vtramit', 'Help')"
							class="ml-1"
							@click="openHelp(step)">
							<v-icon small>mdi-help-circle-outline</v-icon>
						</v-btn>
					</div>
				</v-list-item-content>
			</v-list-item>
		</v-list>

		<!-- Help dialog -->
		<v-dialog v-model="helpDialog" max-width="480">
			<v-card v-if="helpStep">
				<v-toolbar flat dense>
					<v-toolbar-title>{{ helpStep.name }}</v-toolbar-title>
					<v-spacer />
					<v-btn icon @click="helpDialog = false">
						<v-icon>mdi-close</v-icon>
					</v-btn>
				</v-toolbar>
				<v-card-text>
					<!-- eslint-disable-next-line vue/no-v-html -->
					<div v-html="helpStep.description" />
				</v-card-text>
			</v-card>
		</v-dialog>
	</div>
</template>

<script>
export default {
	name: 'ProtocolSteps',
	props: {
		procedureId: { type: Number, default: null },
	},
	data() {
		return {
			completing: null,
			helpDialog: false,
			helpStep: null,
			returnToOrder: null,
		}
	},
	computed: {
		steps() {
			return this.$store.getters['protocolSteps/steps']
		},
		loading() {
			return this.$store.getters['protocolSteps/loading']
		},
		orderedSteps() {
			return [...this.steps].sort((a, b) => a.order - b.order)
		},
	},
	watch: {
		procedureId: {
			immediate: true,
			handler(id) {
				this.returnToOrder = null
				if (!id) return
				if (this.loading || this.steps.length > 0) return
				this.$store.dispatch('protocolSteps/fetchSteps', id).catch(() => {})
			},
		},
	},
	methods: {
		isStepCompleted(step) {
			if (!step) return false
			if (this.returnToOrder === null) {
				return !!step.completed
			}
			return step.order < this.returnToOrder
		},
		isStepActive(index) {
			const step = this.orderedSteps[index]
			if (!step) return false

			if (this.returnToOrder !== null) {
				return step.order === this.returnToOrder
			}

			// A step is active if all previous steps are completed
			if (index === 0) return true
			return this.isStepCompleted(this.orderedSteps[index - 1])
		},
		openHelp(step) {
			this.helpStep = step
			this.helpDialog = true
		},
		onReturnToStep(step) {
			if (!step || !this.isStepCompleted(step)) return
			const confirmed = window.confirm(this.t('vtramit', 'Do you want to return to this step?'))
			if (!confirmed) return
			this.returnToOrder = step.order
		},
		async onCompleteStep(step) {
			this.completing = step.id
			try {
				await this.$store.dispatch('protocolSteps/completeStep', {
					procedureId: this.procedureId,
					stepId: step.id,
				})
				this.returnToOrder = null
			} catch (e) {
				// ignore
			} finally {
				this.completing = null
			}
		},
		formatCompletedAt(value) {
			if (!value) return ''

			let rawValue = value
			if (typeof value === 'object' && value.date) {
				const rawDate = String(value.date).trim()
				const withIsoSeparator = rawDate.includes('T') ? rawDate : rawDate.replace(' ', 'T')
				const withoutMicros = withIsoSeparator.replace(/\.(\d{3})\d+$/, '.$1')
				const hasTimezoneSuffix = /([zZ]|[+-]\d{2}:\d{2})$/.test(withoutMicros)
				const timezone = typeof value.timezone === 'string' ? value.timezone.toUpperCase() : ''

				// Backend usually sends UTC datetime + explicit timezone metadata.
				rawValue = !hasTimezoneSuffix && timezone === 'UTC'
					? `${withoutMicros}Z`
					: withoutMicros
			}

			const date = new Date(rawValue)
			if (Number.isNaN(date.getTime())) {
				if (typeof value === 'object' && value.date) return String(value.date)
				return String(value)
			}

			// Render in browser locale/timezone (user timezone).
			return date.toLocaleString()
		},
	},
}
</script>

<style lang="scss" scoped>
	.protocol-steps {
		height: 100%;
		overflow-y: auto;
	}

	.protocol-steps--item {
		border-bottom: 1px solid rgba(0, 0, 0, 0.06);

		&:last-child {
			border-bottom: none;
		}
	}

	.protocol-steps--avatar-clickable {
		cursor: pointer;
	}

	.v-dialog > .v-card > .v-card__text {
		padding: 16px;
	}
</style>
