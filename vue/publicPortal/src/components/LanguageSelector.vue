<!--
/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia Benítez <letizia@videoatencion.com>
 * @author Daniel Tamajon <dtamajon@litesolutions.es>
 * @author Marta González <marta.gonzalez@litesolutions.es>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan Reyné <ivanreyne@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
-->

<!--
USAGE
- Purpose: drop-in language switcher for Nextcloud Vue modules. It registers the l10n catalog,
  updates <html lang>, persists the selection (localStorage: 'nc_lang') and emits 'changed'.
- Runtime loading: catalogs are fetched directly from /apps/<APP_ID>/l10n/<code>.json (preferred)
  or /apps/<APP_ID>/l10n/<code>.js as a fallback (without using Webpack imports).
- This avoids bundling/copying l10n files into JS chunks.

Props
- appId (String, required)
  Nextcloud public app id used to build the l10n path (/apps/<APP_ID>/l10n).
- domain (String, optional, default: 'vtramit')
  Translation domain used with Nextcloud l10n.
- allowedLanguages (Array|Object, required)
  Allowed language list. Either:
    • Array of codes: ['en','es','ca']
    • Array of objects: [{ value: 'es', label: 'Español' }, { value: 'en', label: 'English' }]
    • Object map: { en: 'English', es: 'Español' }
  NOTE: At least one language should be provided; labels are optional (falls back to UPPERCASE code).
- icon (String, optional, default: 'mdi-web')
  Activator button icon (Material Design Icons name).

Events
- changed(languageCode: string)
  Emitted after a successful language change so the parent can react if needed.

Example of usage:
  <template>
    <div>
      <LanguageSelector
        :appId="'my_app_id'"
        :domain="'vtramit'"
        :allowedLanguages="['en','es','ca']"
        :icon="'mdi-web'"
        @changed="onLanguageChanged"
      />
    </div>
  </template>

  <script>
  import LanguageSelector from '@/components/LanguageSelector.vue'
  export default {
    components: { LanguageSelector },
    methods: {
      onLanguageChanged(lang) {
        // Optional: react to changes (analytics, logs, etc.).
      },
    },
  }
  </script>
-->

<template>
	<v-menu v-model="open" offset-y transition="slide-y-transition">
		<template v-slot:activator="slotProps">
			<slot name="activator" v-bind="slotProps" :selected="selected" :label="currentLabel">
				<v-btn
					icon
					v-bind="slotProps.attrs"
					v-on="slotProps.on"
					:title="t(domain,'Select language')"
					class="language-selector-button">
					<v-icon>{{ icon }}</v-icon>
				</v-btn>
			</slot>
		</template>

		<v-list dense>
			<v-list-item
				v-for="opt in options"
				:key="opt.value"
				@click="selectLanguage(opt.value)"
				:class="{ 'is-active': opt.value === selected }">
				<v-list-item-content>
					<v-list-item-title>{{ opt.label }}</v-list-item-title>
				</v-list-item-content>
				<v-list-item-icon>
					<v-icon v-if="opt.value === selected">mdi-check</v-icon>
				</v-list-item-icon>
			</v-list-item>
		</v-list>
	</v-menu>
</template>

<script>
import Vue from 'vue'
import { register, unregister, getLocale as _getLocale } from '@nextcloud/l10n'

// ------------------------------------------------------------------------
// Global i18n hub and mixin (installed at module load, before app mounts)
// Forces reactive refresh across the app when catalogs change.
//
const I18N_HUB = Vue.observable({ locale: 'en', bump: 0 })
if (!Vue.prototype.$i18n) Vue.prototype.$i18n = I18N_HUB

if (!Vue.__i18nBumpMixinInstalled) {
	Vue.mixin({
		created() {
			if (this.$i18n && typeof this.$i18n.bump !== 'undefined') {
				this.__unwatchI18nBump = this.$watch(() => this.$i18n.bump, () => this.$forceUpdate())
			}
		},
		beforeDestroy() {
			if (this.__unwatchI18nBump) this.__unwatchI18nBump()
		},
	})
	Vue.__i18nBumpMixinInstalled = true
}

export default {
	name: 'LanguageSelector',
	props: {
		appId:   { type: String, default: 'vtramit' },
		domain:  { type: String, default: 'vtramit' },
		// Can be: object { code: "Label" } or array of strings / { value, label }
		allowedLanguages: { type: [Object, Array], default: () => ({}) },
		icon:    { type: String, default: 'mdi-web' },
	},
	data() {
		return { selected: '', open: false, loading: false }
	},
	computed: {
		// --------------------------------------------------------------------
		// Build normalized options from `allowedLanguages`
		//
		options() {
			if (Array.isArray(this.allowedLanguages)) {
				return this.allowedLanguages.map(it => {
					if (typeof it === 'string') {
						const code = this.normalizeString(it)
						return { value: code, label: code.toUpperCase() }
					}
					const code = this.normalizeString(it.value || it.code || it.id || it.lang)
					return { value: code, label: it.label || code.toUpperCase() }
				}).filter(o => !!o.value)
			}
			return Object.entries(this.allowedLanguages || {}).map(([code, label]) => {
				const c = this.normalizeString(code)
				return { value: c, label: label || c.toUpperCase() }
			})
		},
		// --------------------------------------------------------------------
		// Normalized list of allowed codes
		//
		allowedCodes() {
			return this.options.map(o => o.value)
		},
		// --------------------------------------------------------------------
		// Current selected label
		//
		currentLabel() {
			const m = this.options.find(o => o.value === this.selected)
			return m ? m.label : ''
		},
	},
	watch: {
		// --------------------------------------------------------------------
		// Keep `selected` consistent with allowedLanguages set and apply catalog
		//
		allowedLanguages: {
			deep: true, immediate: true,
			async handler () {
				if (!this.allowedCodes.length) return
				if (!this.allowedCodes.includes(this.selected)) {
					const next = this.detectCurrentLanguage(this.allowedCodes) || this.allowedCodes[0]
					this.selected = next
					await this.applyCatalog(next)
					this.applyLanguage(next)
				}
			},
		},
	},
	async created() {
		const init = this.allowedCodes.length ? this.allowedCodes : ['en']
		const detected = this.detectCurrentLanguage(init) || init[0] || 'en'
		this.selected = detected

		await this.applyCatalog(detected)
		I18N_HUB.locale = detected

		document.documentElement.setAttribute('lang', detected)
	},
	methods: {
		// ------------------------------------------------------------------------
		// Normalize a language code (e.g. 'es-ES' -> 'es')
		//
		normalizeString(v) {
			return String(v || '').toLowerCase().split('-')[0]
		},
		// ------------------------------------------------------------------------
		// Return the code if it is in the allowed list of codes, else null
		//
		matchLanguage(x, codes) {
			return x && codes.includes(x) ? x : null
		},
		// --------------------------------------------------------------------
		// Detect the best language candidate among provided codes:
		// 1) localStorage, 2) <html lang>, 3) Nextcloud getLocale(), 4) browser
		//
		detectCurrentLanguage(codes) {
			const fromStorage = this.matchLanguage(this.normalizeString(localStorage.getItem('nc_lang')), codes)
			const fromHtml    = this.matchLanguage(this.normalizeString(document.documentElement.getAttribute('lang')), codes)
			const fromOc      = this.matchLanguage(this.normalizeString(_getLocale ? _getLocale() : ''), codes)
			const fromBrowser = (() => {
				const list = (navigator.languages || [navigator.language]).filter(Boolean)
				for (const tag of list) {
					const base = this.normalizeString(tag)
					if (codes.includes(base)) return base
				}
				return null
			})()
			return fromStorage || fromHtml || fromOc || fromBrowser
		},
		// --------------------------------------------------------------------
		// Fetch JSON catalog from /apps/<APP_ID>/l10n/<lang>.json
		//
		async loadJsonCatalog(lang) {
			const url = `/apps/${this.appId}/l10n/${lang}.json`
			const res = await fetch(url, { cache: 'no-store', credentials: 'same-origin' })
			if (!res.ok) throw new Error(`Cannot load JSON locale: ${lang}`)
			const payload = await res.json()
			// JSON is expected to be either { translations, pluralForm } or a flat map
			const translations = payload.translations || payload || {}
			const pluralForm = payload.pluralForm || 'nplurals=2; plural=(n != 1);'
			return { translations, pluralForm }
		},
		// --------------------------------------------------------------------
		// Load JS catalog by injecting a <script> (fallback):
		// The JS file should call OC.L10N.register(domain, translations, pluralForm).
		//
		loadScriptCatalog(lang) {
			return new Promise((resolve, reject) => {
				const url = `/apps/${this.appId}/l10n/${lang}.js`
				const s = document.createElement('script')
				s.src = url
				s.defer = true
				s.onload = () => resolve(true)
				s.onerror = () => reject(new Error(`Cannot load JS locale: ${lang}`))
				document.head.appendChild(s)
			})
		},
		// --------------------------------------------------------------------
		// Apply translation catalog for `lang` and trigger global re-render:
		// - Unregister previous domain catalog (if any)
		// - Try JSON first; if not available, try JS fallback
		// - Always register something (empty map) to clear stale mappings
		//
		async applyCatalog(language) {
			try { unregister(this.domain) } catch {}

			let registered = false

			// Try JSON catalog
			try {
				const { translations, pluralForm } = await this.loadJsonCatalog(language)
				register(this.domain, translations, pluralForm)
				registered = true
			} catch (_) {
				// Try JS fallback (OC.L10N.register inside the script)
				try {
					await this.loadScriptCatalog(language)
					// If the JS file has registered for a different domain by mistake,
					// ensure our domain has at least an empty catalog to avoid stale entries.
					if (!registered) {
						register(this.domain, {}, 'nplurals=2; plural=(n != 1);')
					}
					registered = true
				} catch (__){}
			}

			// Ensure domain is always registered (even for 'en' or missing files)
			if (!registered) {
				register(this.domain, {}, 'nplurals=2; plural=(n != 1);')
			}

			I18N_HUB.locale = language
			I18N_HUB.bump++

			// Force a render from the root down
			if (this.$root && this.$root.$forceUpdate) this.$root.$forceUpdate()
		},
		// --------------------------------------------------------------------
		// Apply language selection
		//
		applyLanguage(language) {
			localStorage.setItem('nc_lang', language)
			document.documentElement.setAttribute('lang', language)
			this.$emit('changed', language)
		},
		// --------------------------------------------------------------------
		// Handle user picking a language from the menu
		//
		async selectLanguage(language) {
			if (language === this.selected) { this.open = false; return }
			this.selected = language
			await this.applyCatalog(language)
			this.applyLanguage(language)
			this.open = false
		},
	},
}
</script>

<style scoped>
.is-active .v-list-item-title {
	font-weight: 600;
}

.language-selector-button {
	width: 44px;
	height: 44px;
}
</style>
