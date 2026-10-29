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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 -->

 <template>
	<v-app v-if="showLogin" style="width:100%;">
		<v-app-bar v-if="hasLogoLogin" app color="#fff">
			<div class="flex-grow-1">
				<img v-if="hasLogoLogin" :src="config.logoLogin" class="logoLogin">
			</div>
		</v-app-bar>
		<v-main>
			<v-container fluid class="d-flex flex-column align-stretch pa-0" style="height:100%;">
				<v-sheet tile ref="containerSheet" color="#fff" class="fill-height" style="scroll-behavior: smooth;">
					<div class="fill-height d-flex flex-column align-center justify-center pa-2">
						<v-sheet max-width="100%" width="100%">
							<div v-if="hasAppointment" class="d-flex flex-column align-center">
								<v-text-field
									v-if="!config.passwordHide"
									v-model="editPassword"
									class="w-200"
									:label="t('vtramit', 'Password')"
									hide-details
									outlined
									type="password" />
								<RGPD ref="rpgd"
									:appointment="appointment"
									:stakeholder="stakeholder"
									:rgpd-options="rgpdOptions"
									@onError="errorFromRgpd" />
								<div v-if="error" class="vtramit-error">
									{{ errorMessage }}
								</div>
								<div class="d-flex flex-column align-center">
									<div class="mt-4 w-200">
										<v-btn color="primary" block @click="clickLogIn">
											{{ t('vtramit', 'Login') }}
										</v-btn>
									</div>
								</div>
							</div>
							<div v-else class="text-h6 text-center">
								{{ t('vtramit', 'This link is not valid. Please check your mailbox for the last email we sent you for the correct link.') }}
							</div>
						</v-sheet>
					</div>
					<div v-show="!isAtBottom" class="down-to-bottom-arrow">
						<a v-if="isHeightOverflowing" href="#bottom" title="Go to bottom"></a>
					</div>
					<div id="bottom"></div>
				</v-sheet>
			</v-container>
			<!-- <v-footer app color="black" class="white--text">
				<br>
			</v-footer> -->
		</v-main>
	</v-app>
</template>

<script>
import RGPD from '../../../components/RGPD.vue'

export default {
	name: 'LoginPage',
	components: {
		RGPD,
	},
	data: () => ({
		// Local data
		editPassword: '',
		error: false,
		errorMessage: '',

		// Data from the server
		appointment: {},
		stakeholder: {},
		rgpdOptions: [],
		config: {},

		// Control the height of the sheet
		containerSheetHeight: 0,
		viewportHeight: 0,
		notAtBottom: true,
	}),
	computed: {
		hasLogo() {
			if ('logoUrl' in this.config && this.config.logoUrl && this.config.logoUrl !== '') {
				return true
			}
			return false
		},
		hasLogoLogin() {
			if ('logoLogin' in this.config && this.config.logoLogin && this.config.logoLogin !== '') {
				return true
			}
			return false
		},
		hasAppointment() {
			return 'id' in this.appointment
		},
		hasUploads() {
			if ('sharedUrlUploads' in this.appointment && this.appointment.sharedUrlUploads !== null) {
				return true
			}
			return false
		},
		hasDownloads() {
			if ('sharedUrlDownloads' in this.appointment && this.appointment.sharedUrlDownloads !== null) {
				return true
			}
			return false
		},
		hasSignature() {
			if ('isSignatureEnabled' in this.config && this.config.isSignatureEnabled !== null && this.config.isSignatureEnabled === true) {
				return true
			}
			return false
		},
		hasRgpdOptions() {
			return this.$store.getters['index/hasRgpdOptions']
		},
		isHeightOverflowing() {
			if (this.containerSheetHeight > this.viewportHeight) {
				return true
			}
			return false
		},
		isAtBottom() {
			if (this.notAtBottom) {
				return false
			}
			return true
		},
		showLogin() {
			if (this.hasRgpdOptions || this.hasUploads === true || this.hasDownloads === true || this.hasSignature === true) {
				return true
			}
			this.$store.commit('logIn')
			// eslint-disable-next-line vue/no-side-effects-in-computed-properties
			this.$router.push({ name: 'dashboard' })
			return false
		},
	},
	created() {
		const serverDataJson = document.getElementsByName('server-data')[0].innerHTML
		const serverData = JSON.parse(serverDataJson)
		if ('appointment' in serverData && serverData.appointment && 'id' in serverData.appointment) {
			this.$set(this, 'appointment', serverData.appointment)
		}
		if ('procedureStakeholder' in serverData && serverData.procedureStakeholder && 'id' in serverData.procedureStakeholder) {
			this.$set(this, 'stakeholder', serverData.procedureStakeholder)
		}
		if ('rgpdOptions' in serverData && serverData.rgpdOptions && 'list' in serverData.rgpdOptions) {
			this.$set(this, 'rgpdOptions', serverData.rgpdOptions.list)
			if (serverData.rgpdOptions.list.length > 0) {
				this.$store.commit('setRgpdOptions', true)
			}
		}
		if ('config' in serverData && serverData.config && 'documentationUrl' in serverData.config) {
			this.$set(this, 'config', serverData.config)
		}

		const params = new Proxy(new URLSearchParams(window.location.search), {
			get: (searchParams, prop) => searchParams.get(prop),
		})

		// Obtain password from the param 'ak'
		const mypassword = params.ak
		if (!this.$store.state.loggedIn && mypassword && mypassword.length > 4) {
			this.$store.commit('setPassword', mypassword)
			this.editPassword = mypassword

			// If no RGPD options, then autologin
			if (this.rgpdOptions.length === 0 || !this.$store.state.rgpdOptions) {
				this.$store.commit('logIn')
				this.$router.push({ name: 'dashboard' })
			}
		}
	},
	mounted() {
		this.$nextTick(() => {
			this.initializeSheetHeight()
		});
	},
	beforeDestroy() {
		if (this.resizeObserver) {
			this.resizeObserver.disconnect();
		}
		window.removeEventListener('scroll', this.checkIfEndIsVisible)
	},
	methods: {
		clickLogIn() {
			this.error = false

			if (this.editPassword === '' && this.config.passwordHide != true) {
				this.error = true
				this.errorMessage = 'Please complete all required fields'
				return
			}

			this.$refs.rpgd.validateAndSave().then((result) => {
				if (result === true) {
					this.$store.commit('setPassword', this.editPassword)
					this.$store.commit('logIn')
					this.$router.push({ name: 'dashboard' })
				}
			})
		},
		errorFromRgpd(message) {
			this.error = true
			this.errorMessage = message
		},
		checkIfEndIsVisible() {
			if (this.$refs.containerSheet && this.$refs.containerSheet.$el) {
				const sheet = this.$refs.containerSheet.$el
				const sheetBottom = sheet.getBoundingClientRect().bottom
				const viewportHeight = window.innerHeight

				if (sheetBottom <= viewportHeight) {
				// End of sheet is visible, we are at bottom
					this.notAtBottom = false
				} else {
					this.notAtBottom = true
				}
			} else {
				console.warn('containerSheet or its $el is not defined for checking visibility.');
			}
		},
		initializeSheetHeight() {
			// Ensure the DOM is fully loaded and the element exists
			if (this.$refs.containerSheet) {
				this.updateSheetHeight() // Initial height check

				// Create a ResizeObserver to watch for changes in height and width
				this.resizeObserver = new ResizeObserver(() => {
					this.updateSheetHeight()
					this.updateViewportHeight()
				});

				// Start observing the v-sheet's DOM element
				if (this.$refs.containerSheet && this.$refs.containerSheet.$el) {
					this.resizeObserver.observe(this.$refs.containerSheet.$el)
				} else {
					console.warn('Container sheet or its element is not available for observing.');
				}
				// Check if we are at the end
				window.addEventListener('scroll', this.checkIfEndIsVisible)
			} else {
				console.warn('containerSheet is not defined in $refs.');
			}
		},
		updateSheetHeight() {
			if (this.$refs.containerSheet && this.$refs.containerSheet.$el) {
				this.containerSheetHeight = this.$refs.containerSheet.$el.offsetHeight
				this.checkIfEndIsVisible()
			} else {
				console.warn('containerSheet or its $el is not defined.');
			}
		},
		updateViewportHeight() {
			this.viewportHeight = window.innerHeight
			this.checkIfEndIsVisible()
		}
	}
}
</script>

<style>
	.v-input input:not([type='range']),
	.v-input input:not([type='range']):hover,
	.v-input input:not([type='range']):focus {
		border: 1px none transparent;
	}

	.v-input.w-200 {
		width: 200px;
	}

	.v-input.w-200 .v-input__control {
		width: 200px;
	}
</style>
