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

// ------------------------------------------------------------------------
// apiService.mixin.js
//
// Generic API service for consistent HTTP communication within Nextcloud.
// Automatically includes the request token and provides unified methods
// for GET, POST, PUT and DELETE requests.
// ------------------------------------------------------------------------

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'

export default {
	methods: {
		// ------------------------------------------------------------------------
		// Generic GET request.
		//
		// @param {string} url - The API endpoint (relative to /apps/...).
		// @param {Object} [params={}] - Optional query parameters.
		// @returns {Promise<Object>} The API response data.
		//
		async get(url, params = {}, showMessages = false) {
			try {
				const result = await axios.get(generateUrl(url), {
					params,
					headers: { requesttoken: OC.requestToken },
				})

        if (showMessages) {
				  showSuccess(t('vtramit', 'Action successful'))
        }

				return result.data
			} catch (error) {
				console.error('[apiService] GET error:', url, error)
				showError(t('vtramit', 'An internal error has been produced; please try it again later'))
				throw error
			}
		},

		// ------------------------------------------------------------------------
		// Generic POST request.
		//
		// @param {string} url - The API endpoint (relative to /apps/...).
		// @param {Object} [data={}] - Data payload to send.
		// @returns {Promise<Object>} The API response data.
		//
		async post(url, data = {}, showMessages = false) {
			try {
				const result = await axios.post(generateUrl(url), data, {
					headers: {
						requesttoken: OC.requestToken,
						'Content-Type': 'application/json',
					},
				})

        if (showMessages) {
				  showSuccess(t('vtramit', 'Action successful'))
        }

				return result.data
			} catch (error) {
				console.error('[apiService] POST error:', url, error)
				showError(t('vtramit', 'An internal error has been produced; please try it again later'))
				throw error
			}
		},

		// ------------------------------------------------------------------------
		// Generic PUT request.
		//
		// @param {string} url - The API endpoint (relative to /apps/...).
		// @param {Object} [data={}] - Data payload to send.
		// @returns {Promise<Object>} The API response data.
		//
		async put(url, data = {}, showMessages = false) {
			try {
				const result = await axios.put(generateUrl(url), data, {
					headers: {
						requesttoken: OC.requestToken,
						'Content-Type': 'application/json',
					},
				})

        if (showMessages) {
				  showSuccess(t('vtramit', 'Action successful'))
        }

				return result.data
			} catch (error) {
				console.error('[apiService] PUT error:', url, error)
				showError(t('vtramit', 'An internal error has been produced; please try it again later'))
				throw error
			}
		},

		// ------------------------------------------------------------------------
		// Generic DELETE request.
		//
		// @param {string} url - The API endpoint (relative to /apps/...).
		// @returns {Promise<Object>} The API response data.
		//
		async delete(url, showMessages = false) {
			try {
				const result = await axios.delete(generateUrl(url), {
					headers: { requesttoken: OC.requestToken },
				})

        if (showMessages) {
				  showSuccess(t('vtramit', 'Successfully deleted'))
        }

				return result.data
			} catch (error) {
				console.error('[apiService] DELETE error:', url, error)
				showError(t('vtramit', 'An internal error has been produced; please try it again later'))
				throw error
			}
		},
	},
}
