<template>
  <div
    class="selector-box"
    :class="[
      { 'disabled': disabled },
      layoutClass,
      columnsClass
    ]"
    :style="layout === 'column'
      ? { width: (typeof width === 'number' ? width + 'px' : width),
          height: (typeof height === 'number' ? height + 'px' : height) }
      : null"
  >
    <label
      v-for="opt in options"
      :key="opt.key"
      class="selector-box--item"
    >
      <input
        :id="idFor(opt)"
        type="checkbox"
        :value="opt.key"
        :checked="model.includes(opt.key)"
        @change="onToggle($event, opt.key)"
      >
      <span class="selector-box--text">{{ opt.value || opt.key }}</span>
    </label>
  </div>
</template>

<script>
export default {
  name: 'MultipleSelector',

  // ------------------------------------------------------------------------
  // Props
  // - layout: "row" (full-bleed grid, no box) | "column" (classic boxed list)
  // - columns: number of grid columns for the "row" layout (1..12)
  // - width/height: only applied for "column" layout; ignored in "row"
  //
  props: {
    options:  { type: Array, required: true },
    value:    { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
    layout:   { type: String,  default: 'row' }, // 'row' | 'column'
    width:    { type: [Number, String], default: 240 },
    height:   { type: [Number, String], default: 160 },
    columns:  { type: Number, default: 3 },
  },

  computed: {
    // ------------------------------------------------------------------------
    // v-model support with uniqueness guard
    //
    model: {
      get() { return Array.isArray(this.value) ? this.value : [] },
      set(v) { this.$emit('input', Array.from(new Set(v))) },
    },

    // ------------------------------------------------------------------------
    // Layout classes
    //
    layoutClass() {
      return this.layout === 'row' ? 'layout-row' : 'layout-column'
    },
    columnsClass() {
      if (this.layout !== 'row') return null
      const n = Math.min(Math.max(parseInt(this.columns, 10) || 1, 1), 12)
      return `cols-${n}`
    },
  },

  methods: {
    // ------------------------------------------------------------------------
    // Generate predictable ids for inputs
    //
    idFor(opt) {
      return `ms-${this._uid}-${String(opt.key).replace(/\s+/g, '_')}`
    },

    // ------------------------------------------------------------------------
    // Toggle selection and emit changes
    //
    onToggle(e, key) {
      const next = new Set(this.model)
      e.target.checked ? next.add(key) : next.delete(key)
      this.model = [...next]
      this.$emit('change', this.model)
    },
  },
}
</script>

<style lang="scss" scoped>
// ------------------------------------------------------------------------
// MultipleSelector styles
// - layout-column: classic boxed list
// - layout-row: full-bleed grid (no box), column count via .cols-N class
// - Disabled: grey overlay (no opacity on content), blocks interaction
//
.selector-box {
  position: relative;

  // ----------------------- Disabled overlay -----------------------
  &.disabled {
    // Keep content colors; apply veil above content
    &::after {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(230, 230, 230, 0.65);
      pointer-events: auto; // block clicks/scroll on content
      z-index: 1;
    }
  }

  // ----------------------- Error state -----------------------
  &.error {
    border: 1px solid red !important;
  }

  // ----------------------- Vertical (boxed) -----------------------
  &.layout-column {
    display: flex;
    flex-direction: column;
    gap: 4px; // tighter vertical spacing

    overflow: auto;
    overflow-x: auto;
    overflow-y: auto;

    border: var(--border-width-input) solid var(--color-border-maxcontrast);
    border-radius: 8px;
    padding: 8px;
    background: #fff;

    .selector-box--item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      width: auto;
    }
  }

  // ----------------------- Horizontal (full-bleed grid) -----------------------
  &.layout-row {
    width: 100%;
    height: 100%;
    display: grid;
    column-gap: 16px;
    row-gap: 4px;
    align-content: start;

    // No box visuals in horizontal layout
    border: none;
    padding: 0;
    background: transparent;

    // Column count variants (1..12)
    @for $i from 1 through 12 {
      &.cols-#{$i} {
        grid-template-columns: repeat(#{$i}, 1fr);
      }
    }

    .selector-box--item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      width: 100%; // fill grid cell for homogeneous distribution
    }
  }

  // ----------------------- Item internals -----------------------
  &--text {
    display: inline-block;
    width: auto;
    margin: 0;
  }

  input {
    min-height: 24px;
    height: 24px;
    margin: 0;
    padding: 0;
  }
}
</style>
