<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

// Find the correct Nextcloud root path
$ncRoot = realpath(__DIR__ . '/../../../..');

// Ensure the path is correct before including
if (!file_exists("$ncRoot/lib/base.php")) {
    echo "Error: Nextcloud core file not found at $ncRoot/lib/base.php";
}

// ✅ Load Nextcloud Core
require_once "$ncRoot/lib/base.php";

// ✅ Ensure Nextcloud App Autoloading Works
OC_App::loadApp('vtramit');
