<template>
  <div class="popup">
    <span v-if="!$slots.trigger" :style="floatStyle">
      <a
        href="#"
        class="popup-trigger"
        @click.prevent="open()"
        aria-haspopup="dialog"
        :aria-expanded="String(isOpen)"
        :aria-label="icon ? computedLabel : null"
      >
        <template v-if="icon">
          <img :src="icon" alt="" class="popup-icon" />
        </template>
        <template v-else>
          {{ computedLabel }}
        </template>
      </a>
    </span>

    <span v-else @click="open" class="popup-trigger-slot" :style="floatStyle">
      <slot name="trigger" />
    </span>

    <transition name="fade">
      <div
        v-if="isOpen"
        class="modal-overlay"
        role="dialog"
        aria-modal="true"
        @click.self="handleOverlayClick"
      >
        <div class="modal-content" ref="dialog" tabindex="-1">
          <div class="modal-header" v-if="$slots.header || title">
            <slot name="header">
              <h3 class="modal-title">{{ title }}</h3>
            </slot>
            <button class="modal-close" @click="close" :aria-label="computedCloseLabel">×</button>
          </div>

          <div class="modal-body">
            <slot />
          </div>

          <div class="modal-actions" v-if="$slots.footer">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'Popup',
  props: {
    value: { type: Boolean, default: undefined },
    label: { type: String, default: '' },
    title: { type: String, default: '' },
    closeLabel: { type: String, default: '' },
    closeOnEsc: { type: Boolean, default: true },
    closeOnOverlay: { type: Boolean, default: true },
    defaultOpen: { type: Boolean, default: false },
    float: { type: String, default: '' },
    icon: { type: String, default: '' },
  },
  data() {
    return {
      localOpen: this.defaultOpen,
    };
  },
  computed: {
    isControlled() {
      return this.$options.propsData && Object.prototype.hasOwnProperty.call(this.$options.propsData, 'value');
    },
    isOpen: {
      get() {
        return this.isControlled ? !!this.value : this.localOpen;
      },
      set(v) {
        if (this.isControlled) {
          this.$emit('input', v);
        } else {
          this.localOpen = v;
        }
      },
    },
    computedLabel() {
      return (this.label) ? this.label : t('vtramit', 'Show help');
    },
    computedCloseLabel() {
      return (this.closeLabel) ? this.closeLabel : t('vtramit', 'Close');
    },
    floatStyle() {
      return this.float ? { float: this.float } : {};
    },
  },
  watch: {
    isOpen(open) {
      if (open) this.$nextTick(() => this.$refs.dialog && this.$refs.dialog.focus());
      this.$emit(open ? 'open' : 'close');
    },
  },
  mounted() { window.addEventListener('keydown', this.onKeydown); },
  beforeDestroy() { window.removeEventListener('keydown', this.onKeydown); },
  methods: {
    open() { this.isOpen = true; },
    close() { this.isOpen = false; },
    toggle() { this.isOpen = !this.isOpen; },
    handleOverlayClick() { if (this.closeOnOverlay) this.close(); },
    onKeydown(e) {
      if (!this.isOpen) return;
      if (this.closeOnEsc && (e.key === 'Escape' || e.key === 'Esc')) this.close();
    },
  },
};
</script>

<style scoped>
.popup-trigger{cursor:pointer;padding:.5rem .75rem;border:none;background:#fff; color:rgb(5, 80, 230);font-weight:normal;text-decoration:underline;text-decoration-style:dotted}
.popup-trigger:hover{-webkit-text-decoration: none;text-decoration: none;}
.popup-icon {display:inline-block;height:1em;width:auto;vertical-align:middle;}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;z-index:9990}
.modal-content{background:#fff;width:min(720px,92vw);max-height:85vh;overflow:auto;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.2);padding:1rem;outline:none}
.modal-header{display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.5rem}
.modal-title{font-size:1.05rem;margin:0}
.modal-close{border:none;background:transparent;font-size:1.5rem;line-height:1;cursor:pointer}
.fade-enter-active,.fade-leave-active{transition:opacity .15s ease}
.fade-enter,.fade-leave-to{opacity:0}
</style>
