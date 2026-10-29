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
	<div class="procedure">
		<div class="procedure__scroll">
			<!-- 1. Identity card -->
			<article v-if="mainStakeholder" class="card identity">
				<header class="identity__header">
					<div class="identity__header-left">
						<v-icon dark size="16">mdi-card-account-details-outline</v-icon>
						<span class="identity__eyebrow">{{ t('vtramit', 'Interested') }}</span>
						<span v-if="mainStakeholder.isMain" class="identity__principal">
							{{ t('vtramit', 'Primary') }}
						</span>
					</div>
					<span v-if="statusLabel !== '—'" class="identity__status">
						<span class="identity__status-dot" aria-hidden="true"></span>
						{{ t('vtramit', statusLabel) }}
					</span>
				</header>

				<div class="identity__body">
					<div class="identity__avatar" aria-hidden="true">{{ mainInitials }}</div>
					<div class="identity__info">
						<div class="eyebrow">{{ t('vtramit', 'Name') }}</div>
						<div class="identity__name">{{ mainStakeholder.name || '—' }}</div>
						<div class="eyebrow eyebrow--mt">{{ t('vtramit', 'Citizen ID') }}</div>
						<div class="identity__personal-id">{{ mainStakeholder.personalId || '—' }}</div>
					</div>
				</div>

				<!-- Read mode: clickable links + optional edit affordance -->
				<footer v-if="!editingContact" class="identity__contacts">
					<a v-if="mainStakeholder.phone"
						class="identity__contact"
						:href="`tel:${cleanPhone(mainStakeholder.phone)}`"
						:title="t('vtramit', 'Call') + ' ' + mainStakeholder.phone">
						<v-icon size="14" color="#005CD1">mdi-phone-outline</v-icon>
						<span class="identity__contact-text">{{ mainStakeholder.phone }}</span>
					</a>
					<span v-else class="identity__contact identity__contact--empty">
						<v-icon size="14" color="#9DA3A6">mdi-phone-outline</v-icon>
						<span class="identity__contact-text">—</span>
					</span>
					<a v-if="mainStakeholder.email"
						class="identity__contact"
						:href="`mailto:${mainStakeholder.email}`"
						:title="t('vtramit', 'Email') + ' ' + mainStakeholder.email">
						<v-icon size="14" color="#005CD1">mdi-email-outline</v-icon>
						<span class="identity__contact-text">{{ mainStakeholder.email }}</span>
					</a>
					<span v-else class="identity__contact identity__contact--empty">
						<v-icon size="14" color="#9DA3A6">mdi-email-outline</v-icon>
						<span class="identity__contact-text">—</span>
					</span>
					<button v-if="isEditableStatus"
						type="button"
						class="identity__edit-btn"
						:title="t('vtramit', 'Edit contact')"
						@click="startEditingContact">
						<v-icon size="13" color="#005CD1">mdi-pencil-outline</v-icon>
						{{ t('vtramit', 'Edit contact') }}
					</button>
				</footer>

				<!-- Edit mode: phone + email inputs + save -->
				<footer v-else class="identity__edit">
					<label class="identity__edit-field">
						<span class="eyebrow">{{ t('vtramit', 'Phone') }}</span>
						<input
							type="tel"
							class="identity__input"
							:placeholder="t('vtramit', 'Enter phone...')"
							:value="getStakeholderFieldValue(mainStakeholder.id, 'phone', mainStakeholder.phone)"
							@input="onStakeholderFieldChange(mainStakeholder.id, 'phone', $event.target.value)">
					</label>
					<label class="identity__edit-field">
						<span class="eyebrow">{{ t('vtramit', 'Email') }}</span>
						<input
							type="email"
							class="identity__input"
							:placeholder="t('vtramit', 'Enter email...')"
							:value="getStakeholderFieldValue(mainStakeholder.id, 'email', mainStakeholder.email)"
							@input="onStakeholderFieldChange(mainStakeholder.id, 'email', $event.target.value)">
					</label>
					<div class="identity__edit-actions">
						<button type="button"
							class="btn btn--tertiary"
							@click="cancelEditingContact">
							{{ t('vtramit', 'Cancel') }}
						</button>
						<button type="button"
							class="btn btn--primary btn--sm"
							:disabled="status.updating"
							@click="saveStakeholderContact(mainStakeholder)">
							{{ status.updating ? t('vtramit', 'Saving...') : t('vtramit', 'Save contact') }}
						</button>
					</div>
				</footer>
			</article>

			<!-- TODO: secondary stakeholders (stakeholders.length > 1) are not rendered;
				the redesign decided to show only the primary one. Consider an inline
				selector if the use case appears. -->

			<!-- 2. Appointment card -->
			<article class="card appointment">
				<div class="appointment__eyebrow">
					<span>{{ t('vtramit', '# appt') }} · </span>
					<span class="appointment__id">{{ procedure.externalId || '—' }}</span>
				</div>
				<div class="appointment__topic">{{ procedure.topic || '—' }}</div>
				<div class="appointment__when">
					<v-icon size="14" color="#656D72">mdi-clock-outline</v-icon>
					<span>{{ formatDateTime(procedure.appointmentDate) }}</span>
					<template v-if="procedure.deadlineDate">
						<span class="appointment__sep">·</span>
						<span>{{ t('vtramit', 'deadline') }} {{ formatDateTime(procedure.deadlineDate) }}</span>
					</template>
				</div>
			</article>

			<!-- 3. Comments card -->
			<article class="card comments">
				<header class="comments__header">
					<div class="eyebrow">{{ t('vtramit', 'Comments') }}</div>
					<button v-if="!editingComments && isEditableStatus"
						type="button"
						class="comments__edit-btn"
						@click="startEditingComments">
						<v-icon size="13" color="#005CD1">mdi-pencil-outline</v-icon>
						{{ procedure.comments ? t('vtramit', 'Edit') : t('vtramit', 'Add') }}
					</button>
				</header>

				<template v-if="!editingComments">
					<div v-if="procedure.comments" class="comments__text">{{ procedure.comments }}</div>
					<div v-else class="comments__empty">{{ t('vtramit', 'No comments') }}</div>
				</template>

				<template v-else>
					<textarea
						ref="commentsTextarea"
						v-model="localComments"
						class="comments__textarea"
						:placeholder="t('vtramit', 'Write a comment...')"></textarea>
					<div class="comments__actions">
						<button type="button"
							class="btn btn--tertiary"
							@click="cancelEditingComments">
							{{ t('vtramit', 'Cancel') }}
						</button>
						<button type="button"
							class="btn btn--primary btn--sm"
							:disabled="status.updating"
							@click="saveComments">
							{{ status.updating ? t('vtramit', 'Saving...') : t('vtramit', 'Save') }}
						</button>
					</div>
				</template>
			</article>
		</div>

		<!-- 4. Action bar (sticky) — drives the existing dynamic procedureStatuses transitions -->
		<footer v-if="procedureStatuses && procedureStatuses.length" class="action-bar">
			<button v-for="(s, idx) in procedureStatuses"
				:key="s.id"
				type="button"
				class="btn btn--lg"
				:class="actionButtonClass(idx, procedureStatuses.length)"
				:disabled="!formReadyForSubmit"
				@click="clickChangeStatus(s.id)">
				{{ t('vtramit', s.action) }}
			</button>
		</footer>
	</div>
</template>

<script>
import moment from '@nextcloud/moment'
import ComponentStatus from '../../../mixins/componentStatus.mixin.js'
import MessageManager from '../../../mixins/messageManager.mixin.js'

export default {
	name: 'Procedure',
	props: {
		procedureId: { type: [Number, String], required: true },
	},
	mixins: [ComponentStatus, MessageManager],
	data() {
		return {
			localComments: '',
			editingComments: false,
			editingContact: false,
			stakeholderEdits: {},
		}
	},
	computed: {
		procedure() {
			const getter = this.$store.getters['procedure/readById']
			return typeof getter === 'function' ? (getter(this.procedureId) || {}) : {}
		},
		stakeholders() {
			const getter = this.$store.getters['stakeholders/readByProcedureId']
			return typeof getter === 'function' ? (getter(this.procedureId) || []) : []
		},
		// Primary stakeholder: first one flagged isMain, otherwise the first available.
		mainStakeholder() {
			if (!this.stakeholders.length) return null
			return this.stakeholders.find(s => s.isMain) || this.stakeholders[0]
		},
		// Two-letter initials derived from the primary stakeholder's name.
		mainInitials() {
			const name = this.mainStakeholder?.name || ''
			const parts = name.trim().split(/\s+/).filter(Boolean)
			if (!parts.length) return '—'
			if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
			return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
		},
		procedureStatuses() {
			const getter = this.$store.getters['procedure/readAllowedStatusesAll']
			return Array.isArray(getter) ? getter : []
		},
		currentStatus() {
			const statusId = this.procedure?.statusId
			if (!statusId) return null
			const getter = this.$store.getters['procedureStatuses/readById']
			return (typeof getter === 'function') ? getter(statusId) : null
		},
		isEditableStatus() {
			return !!(this.currentStatus && this.currentStatus.editable)
		},
		statusLabel() {
			const status = this.currentStatus
			if (!status) return '—'
			return status.value || status.action || String(this.procedure.statusId)
		},
		formReadyForSubmit() {
			if (this.status?.loading) return false
			if (this.status?.readError) return false
			return !this.status?.updating
		},
	},
	watch: {
		'procedure.comments': {
			immediate: true,
			handler(newVal) {
				this.localComments = newVal || ''
			},
		},
		procedureId: {
			immediate: true,
			handler(id) {
				if (id) {
					this.$store.dispatch('procedure/readProcedureInfoById', id).catch(() => {})
				}
			},
		},
	},
	methods: {
		formatDateTime(val) {
			if (!val) return '—'
			try {
				return moment(val).format('DD-MM-YYYY HH:mm')
			} catch (e) {
				return String(val)
			}
		},
		// Strip whitespace, dashes and dots so the tel: link works regardless of how the phone is stored.
		// NOTE: the country prefix ("+34" etc.) is stored in the admin app config (see ConfigService::getPhonePrefix);
		// it isn't surfaced to the attendant portal yet, so the link uses the raw cleaned digits for now.
		cleanPhone(phone) {
			return String(phone || '').replace(/[\s\-.]/g, '')
		},
		// Comments edit lifecycle.
		startEditingComments() {
			this.localComments = this.procedure.comments || ''
			this.editingComments = true
			this.$nextTick(() => this.$refs.commentsTextarea?.focus())
		},
		cancelEditingComments() {
			this.localComments = this.procedure.comments || ''
			this.editingComments = false
		},
		saveComments() {
			this.updateProcedure({
				id: this.procedure?.id,
				comments: this.localComments,
			})
			this.editingComments = false
		},
		// Contact edit lifecycle.
		startEditingContact() {
			this.editingContact = true
		},
		cancelEditingContact() {
			if (this.mainStakeholder?.id) {
				this.$delete(this.stakeholderEdits, this.mainStakeholder.id)
			}
			this.editingContact = false
		},
		clickChangeStatus(newStatusId) {
			this.updateProcedure({
				id: this.procedure?.id,
				statusId: newStatusId,
			})
		},
		updateProcedure(payload) {
			if (!payload || !payload.id) {
				this.manageException(null, 'Missing procedure ID', true)
				return
			}

			this.setStatusUpdating()

			this.$store.dispatch('procedure/update', payload)
				.then((result) => {
					this.$v && this.$v.$touch && this.$v.$touch()
					this.manageMessage(result, true)

					if (result.result === 'OK' && result.entity?.id) {
						if (typeof result.redirect === 'string' && result.redirect) {
							window.open(result.redirect)
						}
						return Promise.all([
							this.$store.dispatch('procedure/readById', { id: this.procedureId }),
							this.$store.dispatch('procedure/readProcedureInfoById', this.procedureId),
						])
					}

					throw new Error('IGNORE')
				})
				.then((result) => {
					this.$v && this.$v.$touch && this.$v.$touch()
					this.manageMessage(result, true)
				})
				.catch((error) => {
					if (error?.message !== 'IGNORE') {
						this.$v && this.$v.$touch && this.$v.$touch()
						this.manageException(error, this.t('vtramit', 'Could not update the procedure'), true)
					}
				})
		},
		getStakeholderFieldValue(stakeholderId, field, fallback) {
			if (!stakeholderId) return fallback || ''
			const editsForStakeholder = this.stakeholderEdits[stakeholderId]
			if (editsForStakeholder && field in editsForStakeholder) {
				return editsForStakeholder[field] || ''
			}
			return fallback || ''
		},
		onStakeholderFieldChange(stakeholderId, field, value) {
			if (!stakeholderId || !field) return
			if (!this.stakeholderEdits[stakeholderId]) {
				this.$set(this.stakeholderEdits, stakeholderId, {})
			}
			this.$set(this.stakeholderEdits[stakeholderId], field, value)
		},
		saveStakeholderContact(stakeholder) {
			if (!stakeholder || !stakeholder.id) {
				this.manageException(null, 'Missing stakeholder ID', true)
				return
			}

			const edits = this.stakeholderEdits[stakeholder.id] || {}
			const payload = {
				...stakeholder,
				phone: edits.phone !== undefined ? edits.phone : stakeholder.phone,
				email: edits.email !== undefined ? edits.email : stakeholder.email,
			}

			this.setStatusUpdating()

			this.$store.dispatch('stakeholders/update', payload)
				.then((result) => {
					this.$v && this.$v.$touch && this.$v.$touch()
					this.manageMessage(result, true)
					this.editingContact = false
				})
				.catch((error) => {
					this.$v && this.$v.$touch && this.$v.$touch()
					this.manageException(error, this.t('vtramit', 'Could not update stakeholder data'), true)
				})
		},
		// Last button is primary, others are secondary. With a single button we still want it primary.
		actionButtonClass(idx, total) {
			return (idx === total - 1) ? 'btn--primary' : 'btn--secondary'
		},
	},
}
</script>

<style lang="scss" scoped>
$color-primary: #005CD1;
$color-primary-hover: #004DB0;
$color-primary-soft: #E0EEFF;
$color-ink: #1B2124;
$color-ink-muted: #656D72;
$color-hairline: #E5E6E8;
$color-surface: #F5F5F6;
$color-disabled: #CDD1D3;
$color-success-bg: #E4F4F2;
$color-success-fg: #0F6E65;
$color-amber-bg: rgba(255, 194, 74, .2);
$color-amber-fg: #FFC24A;
$color-placeholder: #9DA3A6;

.procedure {
	height: 100%;
	display: flex;
	flex-direction: column;
	background: $color-surface;
	color: $color-ink;
	font-family: Inter, system-ui, -apple-system, sans-serif;

	&__scroll {
		flex: 1;
		overflow-y: auto;
		padding: 20px;
	}
}

.card {
	background: #fff;
	border-radius: 16px;
	box-shadow: 0 1px 3px rgba(27, 33, 36, .06);
	margin-bottom: 14px;
}

.eyebrow {
	font-weight: 600;
	font-size: 11px;
	line-height: 1;
	color: $color-ink-muted;
	letter-spacing: .06em;
	text-transform: uppercase;

	&--mt { margin-top: 6px; }
}

/* ---------- IdentityCard ---------- */
.identity {
	border-radius: 20px;
	overflow: hidden;
	box-shadow: 0 4px 10px rgba(27, 33, 36, .08), 0 1px 3px rgba(27, 33, 36, .06);

	&__header {
		padding: 14px 18px;
		background: $color-ink;
		color: #fff;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
	}

	&__header-left {
		display: flex;
		align-items: center;
		gap: 10px;
		min-width: 0;
	}

	&__eyebrow {
		font-weight: 700;
		font-size: 11px;
		line-height: 1;
		letter-spacing: .12em;
		text-transform: uppercase;
		white-space: nowrap;
	}

	&__principal {
		font-weight: 600;
		font-size: 10px;
		line-height: 1;
		letter-spacing: .06em;
		padding: 2px 7px;
		border-radius: 9999px;
		background: $color-amber-bg;
		color: $color-amber-fg;
		text-transform: uppercase;
		white-space: nowrap;
	}

	&__status {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		background: $color-success-bg;
		color: $color-success-fg;
		padding: 3px 10px;
		border-radius: 9999px;
		font-weight: 600;
		font-size: 12px;
		line-height: 1;
		white-space: nowrap;
		max-width: 50%;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	&__status-dot {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background: currentColor;
		flex-shrink: 0;
	}

	&__body {
		padding: 20px 18px 16px;
		display: flex;
		gap: 16px;
		align-items: center;
	}

	&__avatar {
		width: 64px;
		height: 78px;
		border-radius: 8px;
		background: linear-gradient(135deg, $color-primary-soft, #C6DFFF);
		color: $color-primary;
		font-weight: 700;
		font-size: 26px;
		line-height: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	&__info { flex: 1; min-width: 0; }

	&__name {
		font-weight: 700;
		font-size: 18px;
		line-height: 1.2;
		letter-spacing: -.01em;
		color: $color-ink;
		word-break: break-word;
	}

	&__personal-id {
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
		font-size: 13px;
		line-height: 1.2;
		color: $color-ink;
		word-break: break-all;
	}

	&__contacts {
		padding: 10px 18px 14px;
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px;
		border-top: 1px dashed $color-hairline;
	}

	&__contact {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 13px;
		color: $color-ink !important; /* override Vuetify a:hover */
		text-decoration: none;
		padding: 6px 8px;
		border-radius: 8px;
		margin: 0 -8px;
		transition: background .15s;
		min-width: 0;

		&:hover {
			background: $color-surface;
			text-decoration: none;
		}

		&--empty {
			color: $color-placeholder !important;
			cursor: default;

			&:hover { background: transparent; }
		}
	}

	&__contact-text {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		min-width: 0;
	}

	&__edit-btn {
		grid-column: 1 / -1;
		justify-self: end;
		display: inline-flex;
		align-items: center;
		gap: 5px;
		border: none;
		background: transparent;
		color: $color-primary;
		font-weight: 500;
		font-size: 12px;
		line-height: 1;
		padding: 4px 8px;
		border-radius: 8px;
		cursor: pointer;
		transition: background .15s;

		&:hover { background: $color-primary-soft; }
	}

	&__edit {
		padding: 14px 18px 16px;
		border-top: 1px dashed $color-hairline;
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	&__edit-field {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	&__input {
		width: 100%;
		padding: 8px 10px;
		border: 1px solid $color-hairline;
		border-radius: 10px;
		font-size: 13px;
		line-height: 1.4;
		color: $color-ink;
		background: #fff;
		box-sizing: border-box;
		transition: border-color .15s, box-shadow .15s;

		&:focus {
			outline: none;
			border-color: $color-primary;
			box-shadow: 0 0 0 3px rgba(0, 92, 209, .25);
		}
	}

	&__edit-actions {
		display: flex;
		gap: 8px;
		justify-content: flex-end;
	}
}

/* ---------- AppointmentCard ---------- */
.appointment {
	padding: 16px 18px;

	&__eyebrow {
		font-weight: 700;
		font-size: 11px;
		line-height: 1;
		letter-spacing: .08em;
		text-transform: uppercase;
		color: $color-primary;
	}

	&__id {
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
		letter-spacing: 0;
		text-transform: none;
	}

	&__topic {
		font-weight: 700;
		font-size: 20px;
		line-height: 1.2;
		letter-spacing: -.01em;
		color: $color-ink;
		margin-top: 3px;
		word-break: break-word;
	}

	&__when {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-top: 6px;
		font-size: 13px;
		color: $color-ink-muted;
		flex-wrap: wrap;
	}

	&__sep { color: $color-disabled; }
}

/* ---------- CommentsCard ---------- */
.comments {
	padding: 14px 18px;

	&__header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 8px;
	}

	&__edit-btn {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		border: none;
		background: transparent;
		color: $color-primary;
		font-weight: 500;
		font-size: 12px;
		line-height: 1;
		padding: 4px 6px;
		border-radius: 8px;
		cursor: pointer;
		transition: background .15s;

		&:hover { background: $color-primary-soft; }
	}

	&__text {
		font-size: 14px;
		line-height: 1.5;
		color: $color-ink;
		white-space: pre-wrap;
		word-break: break-word;
	}

	&__empty {
		font-style: italic;
		font-size: 13px;
		line-height: 1.5;
		color: $color-placeholder;
	}

	&__textarea {
		width: 100%;
		min-height: 84px;
		padding: 10px 12px;
		border: 1px solid $color-hairline;
		border-radius: 10px;
		font-size: 14px;
		line-height: 1.5;
		font-family: inherit;
		color: $color-ink;
		background: #fff;
		resize: vertical;
		outline: none;
		transition: border-color .15s, box-shadow .15s;
		box-sizing: border-box;

		&:focus {
			border-color: $color-primary;
			box-shadow: 0 0 0 3px rgba(0, 92, 209, .25);
		}
	}

	&__actions {
		display: flex;
		gap: 8px;
		margin-top: 10px;
		justify-content: flex-end;
	}
}

/* ---------- Buttons ---------- */
.btn {
	height: 40px;
	padding: 0 16px;
	border-radius: 10px;
	font-weight: 500;
	font-size: 14px;
	line-height: 1;
	font-family: inherit;
	cursor: pointer;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	border: 1px solid transparent;
	transition: background .15s, border-color .15s;

	&:focus-visible {
		outline: none;
		box-shadow: 0 0 0 3px rgba(0, 92, 209, .35);
	}

	&--primary {
		background: $color-primary;
		color: #fff;
		border-color: $color-primary;

		&:hover:not([disabled]) {
			background: $color-primary-hover;
			border-color: $color-primary-hover;
		}
	}

	&--secondary {
		background: #fff;
		color: $color-ink;
		border-color: $color-ink;

		&:hover:not([disabled]) { background: $color-surface; }
	}

	&--tertiary {
		background: transparent;
		color: $color-primary;
		height: 36px;
		padding: 0 10px;
		font-size: 13px;
		border-color: transparent;

		&:hover:not([disabled]) { background: $color-primary-soft; }
	}

	&--sm {
		height: 36px;
		padding: 0 14px;
		font-size: 13px;
	}

	&--lg {
		height: 48px;
		font-size: 14px;
		padding: 0 12px;
		width: 100%;
	}

	&[disabled] {
		background: $color-disabled;
		color: #fff;
		border-color: $color-disabled;
		cursor: not-allowed;
		pointer-events: none;
	}
}

/* ---------- ActionBar ---------- */
.action-bar {
	padding: 12px 20px 16px;
	background: $color-surface;
	border-top: 1px solid $color-hairline;
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
	gap: 10px;
}
</style>
