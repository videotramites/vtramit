<template>
  <Popup
    :label="t('vtramit','Show available tokens')"
    :title="t('vtramit','You can use following variables in your template')"
    :float="float"
    :icon="icon ? helpIcon : ''"
  >
    <TokensPanel :show-stakeholder-tokens="showStakeholderTokens" />
  </Popup>
</template>

<script>
import Popup from './Popup.vue';
import TokensPanel from './TokensPanel.vue';
import helpIcon from '../../images/icon_help.svg'

export default {
  name: 'TokensHelp',
  components: { Popup, TokensPanel },
  props: {
    float: { type: String, default: '' },
    icon: { type: Boolean, default: false },
    showStakeholderTokens: { type: Boolean, default: true },
  },
  data() {
    return {
      helpIcon,
    }
  },
};
</script>
