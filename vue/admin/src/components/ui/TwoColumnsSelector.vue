<template>
	<div :class="[
		'selector-box',
		wrapperClass,
  	sort ? 'selector-box--sortable' : 'selector-box--static'
	]">
		<div class="options-box ts-column ">
			<ul class="list-container">
				<li class="column-title">
					{{ t('vtramit', 'Available Options') }}
				</li>
				<li v-for="item in unselectedOptions"
					:key="item.id"
					:class="['list-item', {'selected-option': optionsToSelect.includes(item.id)}]"
					@click="prepareOptionToSelect(item.id)">
					{{ item.name }}
				</li>
			</ul>
		</div>
		<div class="actions-column ts-column">
			<div class="actions-wrapper">
				<button v-tooltip="{
						placement: 'middle',
						trigger: 'hover',
						content: t('vtramit', 'Add'),
						autoHide: false,
						html: true
					}"
					class="primary block-display center"
					@click="addItemsToSelection">
					<span class="label">
						{{ t('vtramit', 'Add') }}
					</span>
				</button>
				<button v-tooltip="{
						placement: 'middle',
						trigger: 'hover',
						content: t('vtramit', 'Remove'),
						autoHide: false,
						html: true
					}"
					class="primary block-display center"
					@click="removeItemsFromSelection">
					<span class="label">
						{{ t('vtramit', 'Remove') }}
					</span>
				</button>
			</div>
		</div>
		<div class="options-box ts-column ">
			<ul class="list-container">
				<li class="column-title">
					{{ t('vtramit', 'Selected Options') }}
				</li>
				<li v-for="item in selectedOptions"
					:key="item.id"
					:class="['list-item', {'selected-option': optionToUnselect === item.id}]"
					@click="prepareOptionToUnselect(item.id)">
					{{ item.name }}
				</li>
			</ul>
		</div>
		<div class="actions-column ts-column" v-if="sort">
			<div class="actions-wrapper">
				<button v-tooltip="{
						placement: 'middle',
						trigger: 'hover',
						content: t('vtramit', 'Up'),
						autoHide: false,
						html: true
					}"
					class="primary block-display"
					@click="moveSelection('up')">
					<span class="label">
						{{ t('vtramit', 'Up') }}
					</span>
				</button>
				<button v-tooltip="{
						placement: 'middle',
						trigger: 'hover',
						content: t('vtramit', 'Down'),
						autoHide: false,
						html: true
					}"
					class="primary block-display"
					@click="moveSelection('down')">
					<span class="label">
						{{ t('vtramit', 'Down') }}
					</span>
				</button>
			</div>
		</div>
	</div>
</template>

<script>

export default {
	name: 'TwoColumnsSelector',
	components: {},
	props: {
		optionsList: {
			Type: Array,
			default: [{ id: 0, name: '' }],
		},
		value: {
			Type: Array,
			default: [],
		},
		wrapperClass: {
			Type: String,
			default: '',
		},
		sort: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			optionToUnselect: '', // This is a strign because we can remove or move one option at a time NO MORE.
			optionsToSelect: [], // Being an Array this allows to add multiple options to the selected options at once.
			selectedOptions: [],
			unselectedOptions: [],
			_optionsList: [],
		}
	},
	watch: {
		optionsList(newOptionsList, oldOptionsList) {
			this.prepareInternalOptionsList()
			if (newOptionsList.length !== 0) {
				this.updateSelections()
			}
		},
		value(newValue) {
			this.updateSelections()
		}
	},
	created() {
		// ------------------------------------------------------------------------
		// Dynamically set the selected and unselected options
		this.prepareInternalOptionsList()
		this.updateSelections()
	},
	methods: {
		prepareInternalOptionsList() {
			let hasObjects = this.optionsList.some(value => { return typeof value == "object" });
			if (hasObjects === true ) {
				this._optionsList = this.optionsList
			} else {
				this._optionsList = this.optionsList.map( item => {
					return {id: item, name: item}
				})
			}
		},
		// ------------------------------------------------------------------------
		// Prepare the selected (by click) items to add to the Selected options
		prepareOptionToSelect(id) {
			if (this.optionsToSelect.includes(id)) {
				this.optionsToSelect.splice(this.optionsToSelect.indexOf(id), 1)
			} else {
				this.optionsToSelect.push(id)
			}
		},
		// ------------------------------------------------------------------------
		// Prepare the selected (by click) items to remove from the Selected options
		prepareOptionToUnselect(id) {
			if (this.optionToUnselect === id) {
				this.optionToUnselect = ''
			} else {
				this.optionToUnselect = id
			}
		},
		// ------------------------------------------------------------------------
		// Modify the Selected options by removing the ones selected
		removeItemsFromSelection() {
			this.value.splice(this.value.indexOf(this.optionToUnselect), 1)
			this.resetSelection()
			this.updateSelections()
			this.$emit('changed')
			this.$emit('input', this.value)
		},
		// ------------------------------------------------------------------------
		// Modify the Selected options by adding the ones selected
		addItemsToSelection() {
			this.optionsToSelect.map(id => this.value.push(id))
			this.resetSelection()
			this.updateSelections()
			this.$emit('changed')
			this.$emit('input', this.value)
		},
		// ------------------------------------------------------------------------
		// Set the elements selected by clicking to none
		resetSelection() {
			this.optionsToSelect = []
			this.optionToUnselect = ''
		},
		// ------------------------------------------------------------------------
		// Move the Selected options up or down
		moveSelection(direction = '') {
			if (!this.optionToUnselect) {
				return false
			}
			const originalIndex = this.value.indexOf(this.optionToUnselect)
			let newIndex
			switch (direction) {
			case 'up':
				if (originalIndex === 0) {
					return false
				}
				newIndex = originalIndex - 1
				break
			case 'down':
				if (originalIndex === this.value.length - 1) {
					return false
				}
				newIndex = originalIndex + 1
				break
			default:
				console.error('Not a valid direction, check your code in TwoColumnsSelector.vue ~ moveSelection')
			}
			this.value.splice(originalIndex, 1)
			this.value.splice(newIndex, 0, this.optionToUnselect)
			this.updateSelections()
			this.$emit('changed')
			this.$emit('input', this.value)
		},
		updateSelections() {
			// We dont' need to check for the length of the selected option since map of an empty list will return an empty list.
			// Since the Id is unique we know the filter will return an Array with only one element so we use [0] to retrieve it.
			if (this._optionsList !== undefined && this._optionsList.length !== 0) {
				this.selectedOptions = this.value.map(option => this._optionsList.filter(element => element.id === option)[0])
				this.unselectedOptions = this._optionsList.filter(option => !this.value.includes(option.id))
			}
		},
	},
}
</script>

<style lang="scss" scoped>
	.selector-box {
		width: 100%;
		height: 150px;
		/*padding: 10px 15px 10px 15px;
		border: 1px solid #CDCDCD;
		border-radius: 1%;*/
	}

	.selector-box .block-display {
		display: block;
	}

	.selector-box .center {
		margin: .2rem auto !important;
	}

	.selector-box .ts-column {
		display: inline-block;
		vertical-align: middle;
		height: 100%;
	}

	.selector-box .list-item {
		list-style: none;
		margin: 0rem;
		padding: 0 0 0 4px;
	}

	.selector-box .list-container {
		text-align: start;
		padding: .25rem;
		margin: auto;
		overflow: scroll;
		height: inherit;
	}

	.selector-box--sortable .options-box {
		width: calc(50% - 160px);
		border: 1px solid #CDCDCD;
	}

	.selector-box--static .options-box {
		width: calc(50% - 80px);
		border: 1px solid #CDCDCD;
	}

	.selector-box .actions-column {
		width: 150px;
		margin: 0;
		text-align: center;
	}

	.selector-box .actions-column .actions-wrapper {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		height: 100%;
	}

	.selector-box .actions-column button {
		width: 130px;
	}

	.selector-box .column-title {
		font-weight: bold;
		background-color: #e6e9f9;
		font-size: .90rem;
		padding-left: 4px;
    padding-bottom: 2px;
	}

	.selector-box .actions-buttons {
		display:block;
		margin: auto;
	}

	.selector-box .selected-option {
		background-color: #008CBA;
		color: #ffffff;
	}

	.vtramit .grid-2-columns .selector-box--sortable .options-box {
		width: calc(50% - 130px);
	}

	.vtramit .grid-2-columns .selector-box--static .options-box {
		width: calc(50% - 65px);
	}

	.vtramit .grid-2-columns .selector-box .actions-column {
		width: 120px;
	}

	.vtramit .grid-2-columns .selector-box .actions-column button {
		width: 120px;
	}
</style>
