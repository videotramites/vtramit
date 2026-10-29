<template>
	<transition-group
		:name="transitionName"
		:tag="tag">
		<slot />
	</transition-group>
</template>

<script>
export default {
	name: 'DraggableList',
	props: {
		transitionName: {
			type: String,
			default: 'draggable-list',
		},
		tag: {
			type: String,
			default: 'div',
		},
	},
}
</script>

<style lang="scss">
.vtramit .draggable-list-move {
	transition: transform 0.18s ease;
}

.vtramit .draggable-list-enter-active,
.vtramit .draggable-list-leave-active {
	transition: opacity 0.12s ease;
}

.vtramit .draggable-list-enter-from,
.vtramit .draggable-list-leave-to {
	opacity: 0.4;
}
</style>
