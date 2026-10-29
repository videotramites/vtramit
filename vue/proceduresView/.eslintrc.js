// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

module.exports = {
	extends: [
		'@nextcloud',
//		'plugin:cypress/recommended',
	],
	globals: {
		__webpack_public_path__: 'writable',
		__webpack_nonce__: 'writable',
	},
	rules: {
		'no-console': 0,
		'import/no-named-as-default-member': 0,
    quotes: [0, "double"],
    'indent': 'off',
  },
}
