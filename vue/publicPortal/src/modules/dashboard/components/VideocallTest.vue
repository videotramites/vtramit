<template>
  <div class="videocall-test">

    <!-- Help text (configurable from admin, per language) -->
    <div v-if="helpText" class="help-hint" v-html="helpText"></div>
    <p v-else class="help-hint">
      {{ t('vtramit', 'If you need help configuring your browser, go to the "Help" section.') }}
    </p>

    <!-- Start / repeat test button -->
    <v-btn
      color="primary"
      :disabled="testing"
      @click="startTest"
      class="test-button"
      large
    >
      <v-icon left>mdi-video-check</v-icon>
      {{ lastResult ? t('vtramit', 'Repeat test') : t('vtramit', 'Test videocall') }}
    </v-btn>

    <!-- Status while testing -->
    <div v-if="testing" class="test-status">
      <v-progress-circular indeterminate color="primary" size="20" width="2" />
      <span>{{ t('vtramit', 'Test in progress...') }}</span>
    </div>

    <!-- Last result -->
    <div v-if="lastResult && !testing" class="test-result">
      <div class="result-header">
        <v-icon :color="resultOk ? '#1976d2' : '#f44336'" class="mr-2">
          {{ resultOk ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline' }}
        </v-icon>
        <span>{{ resultTitle }}</span>
      </div>

      <div class="result-date">{{ resultDate }}</div>

      <div v-if="lastResult.result && lastResult.result.initialMedia" class="result-media">
        <div class="media-item" :class="lastResult.result.initialMedia.hasVideo ? 'media-ok' : 'media-fail'">
          <v-icon small :color="lastResult.result.initialMedia.hasVideo ? '#4caf50' : '#f44336'">
            {{ lastResult.result.initialMedia.hasVideo ? 'mdi-check' : 'mdi-close' }}
          </v-icon>
          {{ t('vtramit', 'Camera') }}
        </div>
        <div class="media-item" :class="lastResult.result.initialMedia.hasAudio ? 'media-ok' : 'media-fail'">
          <v-icon small :color="lastResult.result.initialMedia.hasAudio ? '#4caf50' : '#f44336'">
            {{ lastResult.result.initialMedia.hasAudio ? 'mdi-check' : 'mdi-close' }}
          </v-icon>
          {{ t('vtramit', 'Microphone') }}
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'VideocallTest',
  props: {
    config: { type: Object, required: true },
    stakeholder: { type: Object, required: true },
    appointment: { type: Object, required: true },
  },
  data () {
    return {
      testing: false,
      lastResult: null,
      pollTimer: null,
    }
  },
  computed: {
    helpText () {
      const texts = this.config.videocallTestHelpTexts || {}
      // Try current UI language, then 'es', then 'en', then first available
      const lang = this.$i18n?.locale || document.documentElement.lang || 'es'
      return texts[lang] || texts['es'] || texts['en'] || Object.values(texts)[0] || ''
    },
    resultOk () {
      return this.lastResult && this.lastResult.state === 'completed'
    },
    resultTitle () {
      if (!this.lastResult) return ''
      if (this.lastResult.state === 'completed') return this.t('vtramit', 'Test completed')
      if (this.lastResult.state === 'error') return this.t('vtramit', 'Test failed')
      return this.t('vtramit', 'Test') + ': ' + this.lastResult.state
    },
    resultDate () {
      if (!this.lastResult || !this.lastResult.completedAt) return ''
      return new Date(this.lastResult.completedAt * 1000).toLocaleString()
    },
  },
  mounted () {
    this.fetchLastResult()
  },
  beforeDestroy () {
    this.stopPolling()
  },
  methods: {
    async fetchLastResult () {
      try {
        const url = OC.generateUrl('/apps/vtramit/web/2.0/videocall-test/result/' + this.stakeholder.id)
        const { data } = await axios.get(url)
        this.lastResult = data.test || null
      } catch (e) {
        // No previous test
      }
    },

    async startTest () {
      this.testing = true
      try {
        const url = OC.generateUrl('/apps/vtramit/web/2.0/videocall-test/start')
        const { data } = await axios.post(url, {
          procedureStakeholderId: this.stakeholder.id,
          procedureId: this.appointment.id,
          language: this.$i18n?.locale || 'es',
        })

        const test = data.test
        if (test && test.joinUrl) {
          window.open(test.joinUrl, '_blank')
        }

        this.startPolling()
      } catch (e) {
        this.testing = false
        console.error('Failed to start videocall test', e)
      }
    },

    startPolling () {
      this.stopPolling()
      this.pollTimer = setInterval(async () => {
        await this.fetchLastResult()
        if (this.lastResult && this.lastResult.state !== 'pending') {
          this.testing = false
          this.stopPolling()
        }
      }, 3000)
    },

    stopPolling () {
      if (this.pollTimer) {
        clearInterval(this.pollTimer)
        this.pollTimer = null
      }
    },
  },
}
</script>

<style scoped>
.videocall-test {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  padding: 32px 24px;
  gap: 20px;
}

.help-hint {
  color: #555;
  font-size: 14px;
  text-align: left;
  max-width: 600px;
  width: 100%;
  line-height: 1.6;
  margin: 0;
}

.test-button {
  min-width: 240px;
}

.test-status {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #666;
  font-size: 14px;
}

.test-result {
  text-align: center;
  padding: 28px 40px;
  border-radius: 10px;
  border: 1px solid #1976d2;
  max-width: 500px;
  width: 100%;
}

.result-header {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 500;
  margin-bottom: 8px;
}

.result-date {
  font-size: 15px;
  color: #999;
  margin-bottom: 18px;
}

.result-media {
  display: flex;
  gap: 32px;
  justify-content: center;
}

.media-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 17px;
}

.media-ok {
  color: #4caf50;
}

.media-fail {
  color: #f44336;
}
</style>
