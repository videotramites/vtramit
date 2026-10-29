<template>
  <div class="section">
    <!-- Section header. -->
    <div class="d-flex align-center section-header">
      <h2>{{ t('vtramit', 'General configuration') }}</h2>
    </div>
    <!-- Section body -->
    <div class="grid-2-columns" data-test="publicConfig">
      <!-- Documentation URL -->
      <div class="field-item span-2-columns">
        <label for="publicDocumentationUrl">{{ t('vtramit', 'Documentation URL')
          }}</label>
        <input v-model="configuration.publicDocumentationUrl"
          id="publicDocumentationUrl" type="text"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- Logo URL  -->
      <div class="field-item">
        <label for="publicLogoUrl">{{ t('vtramit', 'Logo URL') }}</label>
        <input v-model="configuration.publicLogoUrl" id="publicLogoUrl" type="text"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- Logo Login  -->
      <div class="field-item">
        <label for="publicLogoLogin">{{ t('vtramit', 'Logo Login') }}</label>
        <input v-model="configuration.publicLogoLogin" id="publicLogoLogin"
          type="text"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- JitsiRoom pattern  -->
      <div class="field-item">
        <TokensPopup :show-stakeholder-tokens="false" float="right" :icon=true />
        <label for="jitsiRoomPattern">{{ t('vtramit', 'Jitsi Room pattern') }}</label>
        <input v-model="configuration.jitsiRoomPattern" id="jitsiRoomPattern"
          type="text"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- Empty slot -->
      <div class="field-item">
      </div>

      <!-- Text Expired  -->
      <div class="field-item">
        <label for="publicTextExpired">{{ t('vtramit', 'Text for expired appointment') }}</label>
        <textarea v-model="configuration.publicTextExpired"
          id="publicTextExpired"
          class="ckeditor"
          rows="10"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true" />
      </div>

      <!-- Text Future -->
      <div class="field-item">
        <label for="publicTextFuture">{{ t('vtramit', 'Text for future appointment') }}</label>
        <textarea v-model="configuration.publicTextFuture"
          id="publicTextFuture"
          class="ckeditor"
          rows="10"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true" />
      </div>

      <!-- Password Hide  -->
      <div class="field-item field-checkbox">
        <label for="publicPasswordHide">{{ t('vtramit', 'Hide Password')
          }}</label>
        <input v-model="configuration.publicPasswordHide" id="publicPasswordHide"
          type="checkbox"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- Downloads Hide  -->
      <div class="field-item field-checkbox">
        <label for="publicDownloadsHide">{{ t('vtramit', 'Hide Downloads')
          }}</label>
        <input v-model="configuration.publicDownloadsHide" id="publicDownloadsHide"
          type="checkbox"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- Uploads Hide  -->
      <div class="field-item field-checkbox">
        <label for="publicUploadsHide">{{ t('vtramit', 'Hide Uploads')
          }}</label>
        <input v-model="configuration.publicUploadsHide" id="publicUploadsHide"
          type="checkbox"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- Signature Hide  -->
      <div class="field-item field-checkbox">
        <label for="publicSignatureHide">{{ t('vtramit', 'Hide Signature')
          }}</label>
        <input v-model="configuration.publicSignatureHide" id="publicSignatureHide"
          type="checkbox"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- Signature Hide  -->
      <div class="field-item field-checkbox">
        <label for="publicOutputAutodownload">{{ t('vtramit', 'Download output files automatically') }}</label>
        <input v-model="configuration.publicOutputAutodownload"
          id="publicOutputAutodownload" type="checkbox"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true">
      </div>

      <!-- Videocall Test Help Text (per language) -->
      <div v-for="lang in availableLanguages" :key="'vcht_'+lang" class="field-item">
        <label :for="'videocallTestHelpText_'+lang">{{ t('vtramit', 'Videocall test help text') }} ({{ lang.toUpperCase() }})</label>
        <textarea v-model="configuration['videocallTestHelpText_'+lang]"
          :id="'videocallTestHelpText_'+lang"
          class="ckeditor"
          rows="6"
          @input="configuration.isChanged = true"
          @blur="configuration.isChanged = true" />
      </div>

      <!-- Robot Videocall Test -->
      <div class="field-item">
        <label for="robotTestUrl">{{ t('vtramit', 'Robot Test URL') }}</label>
        <input v-model="configuration.robotTestUrl" id="robotTestUrl"
          type="text" placeholder="https://video.vtram.it/robot-proves-videotrucada"
          @input="configuration.isChanged = true">
      </div>

      <div class="field-item">
        <label for="robotTestToken">{{ t('vtramit', 'Robot Test Token') }}</label>
        <input v-model="configuration.robotTestToken" id="robotTestToken"
          type="password" placeholder="Token d'autenticació del robot"
          @input="configuration.isChanged = true">
      </div>

    </div>

    <!-- Individual item's CRUD buttons -->
    <div class="d-flex justify-end form-actions">
      <button
        v-tooltip="{
          placement: 'auto',
          trigger: 'hover',
          content: t('vtramit', 'Update'),
          autoHide: false,
          html: true
        }"
        :disabled="!formReadyForSubmit || !configuration.isChanged"
        class="primary btn-save"
        @click="clickUpdateItem(configuration)">
        <span class="icon icon-upload-white" />
        <span class="label">
          {{ t('vtramit', 'Update') }}
        </span>
      </button>
    </div>
  </div>
</template>

<script>
/**
 * @module components/settings/PublicGeneral
 */
import ComponentCRUD from '../../mixins/componentCRUD.mixin.js'
import ComponentStatus from '../../mixins/componentStatus.mixin.js'
import ComponentToggle from '../../mixins/componentToggle.mixin.js'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { Actions } from '@nextcloud/vue'
import TokensPopup from '../ui/TokensPopup.vue'

export default {
	name: 'PublicGeneral',
	components: {
		Actions,
		TokensPopup,
	},
	mixins: [ComponentCRUD, ComponentStatus, ComponentToggle],
	data: () => ({
    formIsChanged: false,
		configuration: {
			publicDocumentationUrl: '',
			publicLogoUrl: '',
			publicLogoLogin: '',
			jitsiRoomPattern: '',
			publicTextExpired: '',
			publicTextFuture: '',
			publicPasswordHide: false,
      publicDownloadsHide: false,
			publicUploadsHide: false,
			publicSignatureHide: false,
      publicOutputAutodownload: false,
      robotTestUrl: '',
      robotTestToken: ''
		},

		urlForLoad: 'apps/vtramit/web/2.0/configuration',
		urlForUpdate: 'apps/vtramit/web/2.0/configuration',
	}),
	computed: {
		formReadyForSubmit() {
			if (this.status.loading) return false
			if (this.status.readError) return false
			return true
		},
		availableLanguages() {
			// Get languages from config (loaded from server)
			if (this.configuration.languages && Array.isArray(this.configuration.languages)) {
				return this.configuration.languages
			}
			return ['ca', 'es', 'en']
		},
	},
	created() {
		// Once the component is created we load the data needed for the form.
		this.loadFormData()
	},
	methods: {
    // ------------------------------------------------------------------------
		// Loads the data from the backend API.
		//
		loadFormData() {
			// Setting the proper "ComponentStatus" so we can enable/disable things
			// whilst we are waiting for the API calls.
			this.setStatusReading()
			return axios({
				method: 'GET',
				url: generateUrl(this.urlForLoad),
			}).then((result) => {
				if (result.data?.result && result.data.result === 'OK') {
					// Assign new results
					this.configuration = this.newItem(result.data.configuration)
					this.setStatusReadSuccess()
					return true
				}
				return false
			}).catch((error) => {
				this.setStatusReadError()
				this.manageException(error, t('vtramit', 'Error loading public general configuration data'))
				throw error
			})
		},
		// ------------------------------------------------------------------------
		// Creates a new item with all the needed fields.
		//
		prepareNewItem() {
			const newItem = {
				// Data from server.
        publicDocumentationUrl: '',
        publicLogoUrl: '',
        publicLogoLogin: '',
        jitsiRoomPattern: '',
        publicTextExpired: '',
        publicTextFuture: '',
        publicPasswordHide: false,
        publicDownloadsHide: false,
        publicUploadsHide: false,
        publicSignatureHide: false,
        publicOutputAutodownload: false,
        robotTestUrl: '',
        robotTestToken: ''
			}
			return newItem
		},
		prepareItemForUpdate(item) {
			const forApi = {
				publicDocumentationUrl: item.publicDocumentationUrl,
        publicLogoUrl: item.publicLogoUrl,
        publicLogoLogin: item.publicLogoLogin,
        jitsiRoomPattern: item.jitsiRoomPattern,
        publicTextExpired: item.publicTextExpired,
        publicTextFuture: item.publicTextFuture,
        publicPasswordHide: item.publicPasswordHide,
        publicDownloadsHide: item.publicDownloadsHide,
        publicUploadsHide: item.publicUploadsHide,
        publicSignatureHide: item.publicSignatureHide,
        publicOutputAutodownload: item.publicOutputAutodownload,
        robotTestUrl: item.robotTestUrl,
        robotTestToken: item.robotTestToken
			}
			// Include per-language videocall test help texts
			for (const key of Object.keys(item)) {
				if (key.startsWith('videocallTestHelpText_')) {
					forApi[key] = item[key]
				}
			}
			return forApi
		},
	},
}
</script>
