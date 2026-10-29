<template>
	<div class="draggable-row" @dragover="onDragOver" @drop="onDrop">
		<div class="draggable-col">
			<img
				class="draggable-handle"
				:class="{ 'is-disabled': !draggable }"
				:src="icon"
				:alt="altText"
				:title="titleText"
				:draggable="draggable"
				@dragstart="onDragStart"
				@dragend="onDragEnd" />
		</div>
		<div class="draggable-body">
			<slot />
		</div>
	</div>
</template>

<script>
export default {
	name: 'DraggableRow',
	props: {
		icon: {
			type: String,
			required: true,
		},
		draggable: {
			type: Boolean,
			default: true,
		},
		altText: {
			type: String,
			default: 'Drag',
		},
		titleText: {
			type: String,
			default: 'Drag',
		},
	},
	methods: {
		onDragStart(event) {
			if (this.draggable && event?.dataTransfer && this.$el) {
				const rect = this.$el.getBoundingClientRect()
				const offsetX = event.clientX - rect.left
				const offsetY = event.clientY - rect.top
				event.dataTransfer.setDragImage(this.$el, offsetX, offsetY)
			}
			this.$emit('dragstart', event)
		},
		onDragEnd(event) {
			this.$emit('dragend', event)
		},
		onDragOver(event) {
			this.$emit('dragover', event)
		},
		onDrop(event) {
			this.$emit('drop', event)
		},
	},
}
</script>

<style lang="scss">
.vtramit .draggable-row {
	display: grid;
	grid-template-columns: 28px 1fr;
	column-gap: 10px;
	align-items: center;
}

.vtramit .draggable-col {
	display: flex;
	justify-content: center;
	align-items: center;
}

.vtramit .draggable-body {
	min-width: 0;
}

.vtramit .draggable-handle {
	width: 18px;
	height: 18px;
	cursor: grab;
	opacity: 0.65;
}

.vtramit .draggable-handle.is-disabled {
	cursor: not-allowed;
	opacity: 0.25;
}
</style>
