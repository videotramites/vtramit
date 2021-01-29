<?php
// Configuration file example
//
// This file only contains configurations regarding to this module.
// Nextcloud configuration has been removed in that example, so please use it
// carefully.
//
$CONFIG = array (
  'vtramit.admin'         => 'admin',
  'vtramit.history.days'  => 2,
  'vtramit.mails.allowed' => true,
  'vtramit.phone.link'    => 'ciscotel',
  'vtramit.phone.prefix'  => '+34',
  'vtramit.folder.upload'     => 'Inbox',
  'vtramit.folder.download'   => 'Outbox',
  'vtramit.jitsi.citizen.url' => 'https://citizenvideocall.mydomain.com',
  'vtramit.jitsi.staff.url'   => 'https://staffvideocall.mydomain.com',
  'vtramit.groups' => array(
    'GROUP 1',
    'GROUP 2',
    'GROUP 3',
  ),
  'vtramit.group.settings' => array(
    'GROUP 1' => array(
      'fullname' => "Sample Group 1",
      'address'  => "Group 1 office address",
      'cp'       => "Group 1 office zip code",
      'phone'    => "Group 1 office phone",
    ),
    'GROUP 2' => array(
      'fullname' => "Sample Group 2",
      'address'  => "Group 2 office address",
      'cp'       => "Group 2 office zip code",
      'phone'    => "Group 2 office phone",
    ),
    'GROUP 3' => array(
      'fullname' => "Sample Group 3",
      'address'  => "Group 3 office address",
      'cp'       => "Group 3 office zip code",
      'phone'    => "Group 3 office phone",
    ),
  ),
  'vtramit.group.mailSettings' => array(
    'default' => array(
      'subject' => "Videocall appointment",
    ),
    'GROUP 1' => array(
      'subject' => "Videocall appointment with Group 1",
    ),
    'GROUP 2' => array(
      'subject' => "Videocall appointment with Group 2",
    ),
  ),
  'vtramit.group.filter' => array(
    'default' => array(
      'now' => array('interval-before' => '-1 hour', 'interval-after' => '+1 hour'),
    ),
    'GROUP 1' => array(
      'now' => array('interval-before' => '-1 hour', 'interval-after' => '+1 hour'),
    )
  ),
  'vtramit.group.forms' => array(
    'default' => array(
      'vc_confirmation' => ""
    ),
    'GROUP 1' => array(
      'vc_confirmation' => "https://citizenvideocall.mydomain.com/apps/forms/3GCU6Lr5akgFq4uO",
    ),
  ),
  'vtramit.group.deck' => array (
    'GROUP 1' => array ('board' => 2, 'stack' => 4),
    'GROUP 2' => array ('board' => 3, 'stack' => 5),
  ),
);