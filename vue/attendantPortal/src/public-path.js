// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

// ------------------------------------------------------------------------
// Set webpack runtime public path for a Nextcloud app (must run first)
// ------------------------------------------------------------------------
/* eslint-disable no-global-assign, no-undef, no-unused-vars */
import { generateFilePath } from '@nextcloud/router'

if (window.OC && window.OC.requestToken) {
	// Keep CSP nonce aligned with Nextcloud
	__webpack_nonce__ = btoa(window.OC.requestToken)
}

// Ensure assets resolve to /apps/<appId>/js/...
__webpack_public_path__ = generateFilePath('vtramit', '', 'js/')
/* eslint-enable no-global-assign, no-undef, no-unused-vars */
