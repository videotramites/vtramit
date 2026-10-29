<template>
	<div class="topic-selector">
		<input
			:id="inputId"
			:placeholder="placeholder"
			type="text"
			v-model="inputValue"
			autocomplete="off"
			@input="onInput"
			@keydown.down.prevent="move(1)"
			@keydown.up.prevent="move(-1)"
			@keydown.enter.prevent="pickHighlighted"
			@keydown.esc.prevent="close"
			@focus="onFocus"
			@change="onChange"
		/>
		<!-- Dropdown with suggestions -->
		<div v-if="open && suggestions.length" class="dropdown">
			<div
				v-for="(s, i) in suggestions"
				:key="s.id ?? s.name ?? i"
				class="option"
				:class="{ highlighted: i === highlighted }"
				@mousedown.prevent.stop="onOptionMouseDown(s, i)"
				@mousemove="highlighted = i"
			>
				<div class="option__name">{{ s.name }}</div>
			</div>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'TopicSelector',
	model: { prop: 'modelValue', event: 'input' },
	props: {
		modelValue: { type: String, default: '' },
		placeholder: { type: String, default: '' },
		minChars: { type: Number, default: 2 },
		debounceMs: { type: Number, default: 250 },
		maxItems: { type: Number, default: 10 },
		inputId: { type: String, default: 'topic-selector-' + Math.random().toString(36).slice(2) },
	},
	data: () => ({
		// Internal mirror to avoid flicker/clearing while parent updates.
		inputValue: '',
		open: false,
		focused: false,
		suggestions: [],
		highlighted: -1,
		// Helpers for debounce and cancellation
		_debounce: null,
		_abort: null,
	}),
	watch: {
		// ------------------------------------------------------------------------
		// Keep internal value in sync with external v-model
		modelValue: {
			immediate: true,
			handler(v) { this.inputValue = v ?? '' },
		},
	},
	methods: {
		// ------------------------------------------------------------------------
		// Emit value changes
		emitValue(val, item = null) {
			this.$emit('input', val)
			this.$emit('update:modelValue', val)
			if (item) this.$emit('select', item)
		},
		// ------------------------------------------------------------------------
		// Emit change event
		onChange() {
			setTimeout(() => this.close(), 120)
			this.focused = false
			this.$emit('change', this.inputValue)
		},
		// ------------------------------------------------------------------------
		// Handle user typing: update v-model and debounce search.
		onInput() {
			this.emitValue(this.inputValue)
			if (this._debounce) clearTimeout(this._debounce)
			const q = (this.inputValue || '').trim()
			if (q.length < this.minChars) {
				this.suggestions = []
				this.highlighted = -1
				this.open = this.focused && this.suggestions.length > 0
				return
			}
			this._debounce = setTimeout(() => this.search(q), this.debounceMs)
		},
		// ------------------------------------------------------------------------
		// Remote search with cancellation.
		async search(q) {
			// cancel previous
			if (this._abort) { this._abort('cancel'); this._abort = null }
			const controller = new AbortController()
			this._abort = (reason) => controller.abort(reason)
			try {
				const url = generateUrl('/apps/vtramit/api/2.0/topic/search/filter')
				const { data } = await axios.post(url, { filter: { name: q } }, { signal: controller.signal })
				const list = Array.isArray(data?.list) ? data.list : []
				this.suggestions = list.slice(0, this.maxItems)
				this.highlighted = this.suggestions.length ? 0 : -1
				this.open = this.focused && this.suggestions.length > 0
			} catch (e) {
				if (e?.name !== 'AbortError') {
					this.suggestions = []
					this.highlighted = -1
					this.open = false
				}
			} finally {
				this._abort = null
			}
		},
		// ------------------------------------------------------------------------
		// Keyboard navigation.
		move(dir) {
			if (!this.open || !this.suggestions.length) return
			if (this.highlighted < 0) this.highlighted = 0
			else this.highlighted = (this.highlighted + dir + this.suggestions.length) % this.suggestions.length
		},
		// ------------------------------------------------------------------------
		// Pick current highlighted with Enter.
		pickHighlighted() {
			if (this.highlighted >= 0) this.pick(this.suggestions[this.highlighted])
			else this.close()
		},
		// ------------------------------------------------------------------------
		// Mouse selection handler executed on mousedown (before input blur).
		onOptionMouseDown(item, index) {
			this.highlighted = index
			this.pick(item)
		},
		// ------------------------------------------------------------------------
		// Emit selection and close dropdown. Keep free text by writing name.
		pick(item) {
			this.inputValue = item.name
			this.emitValue(item.name, item)
			this.$emit('change', this.inputValue)
			this.close()
		},
		// ------------------------------------------------------------------------
		// Focus/blur handlers
		onFocus() {
			this.focused = true
			this.open = this.suggestions.length > 0
		},
		// ------------------------------------------------------------------------
		// Close dropdown
		close() {
			this.open = false
			this.highlighted = -1
		},
	},
}
</script>

<style scoped>
.topic-selector { position: relative; width: 100%; }
.topic-selector > input { width: 100%; }
.topic-selector .dropdown {
	position: absolute;
	top: 100%;
	left: 0;
	right: 0;
	z-index: 999; /* ensure above labels/panels */
	border: 1px solid var(--color-border, #ddd);
	background: var(--color-main-background, #fff);
	max-height: 240px;
	overflow-y: auto;
	border-radius: 6px;
	box-shadow: 0 0 0 2px inset var(--color-primary-element);
}
.topic-selector .option {
	padding: 8px 10px;
	cursor: pointer;
	user-select: none;
}
.topic-selector .option.highlighted {
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
}
.topic-selector .option__name {
	cursor: pointer;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
</style>
