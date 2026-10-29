<template>
  <div class="help-inline">
    <div class="grid-3-columns">
      <div class="field-item">%procedureId%</div>
      <div class="field-item">%group%</div>
      <div class="field-item">%date%</div>
      <div class="field-item">%time%</div>
      <div class="field-item">%externalId%</div>
      <div class="field-item">%topic%</div>
      <div class="field-item">%topicHelp%</div>
      <div class="field-item">%topicHelpUrl%</div>
      <div class="field-item">%topicMinFiles%</div>
      <div class="field-item">%urlStaffVideocall%</div>
      <template v-if="showStakeholderTokens">
        <div class="field-item">%urlStakeholderVideocall%</div>
        <div class="field-item">%userPersonalId%</div>
        <div class="field-item">%userName%</div>
        <div class="field-item">%userPhone%</div>
        <div class="field-item">%userEmail%</div>
        <div class="field-item">%uploadsUrl%</div>
        <div class="field-item">%downloadsUrl%</div>
        <div class="field-item">%userEntryPageUrl%</div>
        <div class="field-item">%password%</div>
      </template>
      <div class="field-item">%jwtExpiryTimestamp%</div>
      <div class="field-item">%random:?%</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HelpInline',
  props: {
    showStakeholderTokens: {
      type: Boolean,
      default: true
    }
  }
}
</script>
