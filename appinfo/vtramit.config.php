<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later
// Configuration file example.
//
// Copy this file to /config directory and setup your installation configuration.
//
// This file only contains configurations regarding to v-Tramit module.
//
$CONFIG = array (
  'vtramit.mode' => 'test', // 'production', 'test'
  'vtramit.log.timer' => false,

  'vtramit.vc.hideWaiting' => false, // MISSING FUNCTIONALITY > restore from v1.x

  'vtramit.group.filter' => array(
    'default' => array(
      'now' => array('interval-before' => '-1 hour', 'interval-after' => '+1 hour'),
    ),
    'GROUP 1' => array(
      'now' => array('interval-before' => '-1 hour', 'interval-after' => '+1 hour'),
    )
  ),

  // 'vtramit.public.settings' => array (
  //   'documentation.url'  => 'https://howto.vtramit.com/',
  //   'files.output.autodownload' => false,
  //   'logo.url'     => '',
  //   'text.future'  => '',
  //   'text.expired' => '',
  // ),

);
