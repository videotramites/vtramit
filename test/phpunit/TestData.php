<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later
namespace OCA\VTramit\Test\Phpunit;

use OCA\VTramit\Db\Webhook;
use OCA\VTramit\AttendanceChannel;

class TestData
{
    public static array $procedureTypeTestEntityData = [
        "name" => "PHPUnit Test Create",
        "uploadsFolder" => "Upload",
        "downloadsFolder" => "Download",
        "signatureUploadsFolder" => "Upload/Signature",
        "signatureDownloadsFolder" => "Download",
        "jitsiStakeholderUrl" => "https://demovideollamada.vtramit.com",
        "jitsiStaffUrl" => "https://demovideocita.vtramit.com",
        "allowUpload" => false,
        "allowDownload" => false,
        "allowSignature" => false,
        "signatureLimit" => 5,
        "allowVideo" => false,
        "notifyStakeholder" => false,
        "notifyStaff" => false,
        "requireAssignedGroup" => false,
        "requireExternalId" => false,
        "requireAppointmentDate" => false,
        "requireDeadlineDate" => false,
        "requireStakeholderPid" => false,
        "requireStakeholderPhone" => false,
        "requireStakeholderEmail" => false,
        "requireAttendanceChannel" => false,
        "procedureAnonymizationPeriod" => 0,
        "filesDeletionPeriod" => 0,
        "procedureDeletionPeriod" => 360,
        "allowedAttendanceChannels" => [AttendanceChannel::VIDEOCONFERENCE, AttendanceChannel::PHONE, AttendanceChannel::CHAT]
    ];

    public static array $updatedProcedureTypeTestEntityData = [
        // "id" => $this->procedureTypeId,
        "name" => "PHPUnit Test Update",
        "uploadsFolder" => "Uploades",
        "downloadsFolder" => "Downloades",
        "signatureUploadsFolder" => "Signature",
        "signatureDownloadsFolder" => "Downloades",
        "jitsiStakeholderUrl" => "https://videotrucada.barcelona.cat",
        "jitsiStaffUrl" => "https://informadorvideotrucada.bcn.cat",
        "allowUpload" => true,
        "allowDownload" => true,
        "allowSignature" => true,
        "signatureLimit" => 2,
        "allowVideo" => true,
        "notifyStakeholder" => true,
        "notifyStaff" => true,
        "requireAssignedGroup" => true,
        "requireExternalId" => true,
        "requireAppointmentDate" => true,
        "requireDeadlineDate" => true,
        "requireStakeholderPid" => true,
        "requireStakeholderPhone" => true,
        "requireStakeholderEmail" => true,
        "requireAttendanceChannel" => true,
        "procedureAnonymizationPeriod" => 120,
        "filesDeletionPeriod" => 360,
        "procedureDeletionPeriod" => 240,

        // "rgpdOptions" => [$this->rgpdOptionId]
    ];

    public static array $procedureTypeGroupTestEntityData = [
        // "procedureTypeId" => $this->procedureTypeId,
        "group" => "TEST",
        "notifyStakeholderTemplateId" => 0,
        "notifyStaffTemplateId" => 0,
        "vcConfirmationForm" => "https://dev.vtramit.com/index.php/apps/forms/KKS64KkwapoJp8Dk/submit?procedure_id=%procedureId%",
        "allowCreate" => false,
        "allowAssignGroup" => true,
        "allowAssignUser" => false,
        "bypassRequired" => false,
    ];

    public static array $updatedProcedureTypeGroupTestEntityData = [
        // "id" => $this->procedureTypeGroupId,
        // "procedureTypeId" => $this->procedureTypeId,
        "group" => "TEST",
        "notifyStakeholderTemplateId" => 0,
        "notifyStaffTemplateId" => 0,
        "vcConfirmationForm" => "https://demo.vtramit.com/",
        "allowCreate" => true,
        "allowAssignGroup" => false,
        "allowAssignUser" => true
    ];

    public static array $signatureTypeTestEntityData = [
        "name" => "PHP Test Create",
        "requestLimit" => 10,
        "filesPerRequest" => 10,
        "fileType" => ["pdf","docx"],
        "format" => "XADEs",
        "method" => "certificate",
        "certifiedAuthority" => "aoc-identitats",
        "certifiedAuthorityProxy" => "https://demo.vtramit.com/caproxy/v1/vtramit/",
        "additionalInfo" => "",
        "phoneRequired" => false,

    ];

    public static array  $updatedSignatureTypeTestEntityData = [
        "name" => "PHP Test",
        "requestLimit" => 5,
        "filesPerRequest" => 5,
        "fileType" => ["*"],
        "format" => "Cms",
        "method" => "otp",
        "certifiedAuthority" => "validatedid-vidsigner",
        "certifiedAuthorityProxy" => "https://demo.vtramit.com/",
        "additionalInfo" => '
        {
            "pdfSignature": {
                "visible": true
            }
        }',
        "phoneRequired" => true,

    ];

    public static array $signatureButtonTestEntityData = [
        // "procedureTypeId" => $this->procedureTypeId ,
        // "group" => "TEST",
        // "signatureTypeId" => $this->signatureTypeId,
        "mailEnabled" => false,
        "mailTemplateId" => 0,
        "label" => '{"ca":"Signatura des d\'OAC"}',
    ];

    public static array $updatedSignatureButtonTestEntityData = [
        // "id" => $this->signatureButtonId,
        // "procedureTypeId" => $this->procedureTypeId ,
        // "group" => "TEST",
        // "signatureTypeId" => $this->signatureTypeId,
        "mailEnabled" => true,
        "mailTemplateId" => 0,
        "label" => '{"ca":"Signatura des d\'OAC","es":"Firma"}',
    ];

    public static array $rgpdOptionTestEntityData = [
        "name" => "PHPUnit Test Create",
        "type" => "markup",
        "label" => [
            ["key" => "en", "value" => "Option 1"]
        ],
        "required" => true
    ];

    public static array $updatedRgpdOptionTestEntityData = [
        "name" => "PHPUnit Test Update",
        "type" => "checkbox",
        "label" => [
            ["key" => "en", "value" => "aaa"],
            ["key" => "es", "value" => "bbb"],
            ["key" => "gl", "value" => "ccc"]
        ],
        "required" => false
    ];

    public static array $rgpdActivityTestEntityData = [
        // "procedureId" => $this->procedureId,
        // "procedureStakeholderId" => $this->procedureStakeholderId,
        "labelList" => ["aaa", "bbbb"],
    ];

   public static array $updatedRgpdActivityTestEntityData = [
        // "id" => $this->rgpdActivityTestId,
        // "procedureId" => $this->procedureId,
        // "procedureStakeholderId" => $this->procedureStakeholderId,
        "labelList" => ["ccc", "ddddd"],
    ];


    public static array $procedureTestEntityData = [
        // "externalId" => $this->generateExternalId(5),
        // "procedureTypeId" => $this->procedureTypeId,
        "assignedGroupId" => "TEST",
        "comments" => "Testing, do not use this test created",
        "topic" => "Testing API 1674581866",
        "attendanceChannel" => AttendanceChannel::VIDEOCONFERENCE,
        // "appointmentDate" => $formattedDate
    ];

    public static array $updatedProcedureTestEntityData = [
        // "externalId" => $this->generateExternalId(5),
        // "procedureTypeId" => $this->procedureTypeId,
        "assignedGroupId" => "TIES",
        "comments" => "Updated test, do not use",
        "topic" => "Updated Testing API",
        "attendanceChannel" => AttendanceChannel::FACE_TO_FACE,
        // "appointmentDate" => $formattedDate
    ];

    public static array $procedureStakeholderTestEntityData = [
        // "procedureId" => $this->procedureId,
        // "personalId" => $this->generateExternalId(5),
        "name" => "Pere Ponç Càtara",
        "phone" => "666 777 888",
        "email" => "pere@gmail.com",
    ];

        public static array $updatedProcedureStakeholderTestEntityData = [
        // "procedureId" => $this->procedureId,
        // "personalId" => $this->generateExternalId(5),
        "name" => "Joan Jimenez Fons",
        "phone" => "666 123 456",
        "email" => "joan@gmail.com",
    ];

    
    // Create the mail queue test entity
    public static array $messageQueueTestEntityData = [
        // "id" => $this->mailQueueTestId,
        // "procedureId" => $this->procedureId,
        // "procedureStakeholderId" => $this->procedureStakeholderId,
        // "channel" => "sms",
        //"recipient" => "+34666777888",
        "subject" => "Test subject",
        "body" => "This is a message queue test entity",
        // "citizenPassword" => "UpdatedTestPass123",
    ];

    public static array $updatedMessageQueueTestEntityData = [
        // "id" => $this->mailQueueTestId,
        // "procedureId" => $this->procedureId,
        // "procedureStakeholderId" => $this->procedureStakeholderId,
        // "channel" => "sms",
        //"recipient" => "+34666777888",
        "subject" => "Test Subject Updated",
        "body" => "This is a updated message queue test entity",
        // "citizenPassword" => "UpdatedTestPass123",
    ];
    // Create the mail queue test entity
    public static array $mailQueueTestEntityData = [
        // "procedureId" => $this->procedureId,
        "subject" => "Test Subject",
        "body" => "Test body content",
        "citizenPassword" => "TestPass123",
        "mailTo" => "test@example.com",
        "mailCc" => "cc@example.com",
        "mailCco" => "bcc@example.com"
    ];

    public static array $updatedMailQueueTestEntityData = [
        // "id" => $this->mailQueueTestId,
        // "procedureId" => $this->procedureId,
        "subject" => "Updated Test Subject",
        "body" => "Updated test body content",
        "citizenPassword" => "TestPass123",
        "mailTo" => "updated@example.com",
        "mailCc" => "ccupdated@example.com",
        "mailCco" => "bccupdated@example.com"
    ];
    public static array $messageTemplateEntityData = [
        "name" => "PhphUnit TEST Template for stakeholders",
        // "channel" => "email",
        "subject" => "New appointment!!",
        "template" => "Dear %name%, link to %urlVideocall%"
    ];

    public static array $updatedMessageTemplateEntityData = [
        "name" => "PhphUnit TEST Template for stakeholders",
        // "channel" => "email",
        "subject" => "New appointment!!",
        "template" => "Dear %name%, link to %urlVideocall%"
    ];
    public static array $topicEntityData = [
        "name" => "phphunit test topic",
        "minRequiredDocs" => 2,
        "videoPreservationTime" => "34553",
        "helpUrl" => "https://www.litesolutions.es",
        "helpTarget" => "",
    ];
    public static array $updatedTopicEntityData = [
        "name" => "PhphUnit TEST Topic 2",
        "minRequiredDocs" => 1,
        "videoPreservationTime" => "0",
        "helpUrl" => "https://www.litesolutions.es",
        "helpTarget" => "_blank",
    ];

    public static array $mailTemplateEntityData = [
        "name" => "PhphUnit TEST Template for stakeholders",
        "subject" => "New appointment!!",
        "template" => "Dear %name%, link to %urlVideocall%"
    ];
    public static array $updatedMailTemplateEntityData = [
        "name" => "PhphUnit TEST 2",
        "subject" => "New appointment!!!!",
        "template" => "Hello %name%, link to is %urlVideocall%"
    ];

    public static array $webhookTestEntityData = [
        "name" => "Test Webhook",
        "endpoint" => "https://webhook.site/01abcfa0-37a7-41b1-b871-4417b27f5352",
        "authType" => Webhook::NO_AUTH,
        "authTypeData" => [],
        "event" => [Webhook::CREATE_PROCEDURE, Webhook::UPDATE_PROCEDURE, Webhook::DELETE_PROCEDURE ]
    ];

    public static array $updatedWebhookTestEntityData = [
        "name" => "Updated Basic Auth Webhook",
        "endpoint" => "https://webhook.site/01abcfa0-37a7-41b1-b871-4417b27f5352",
        "authType" => Webhook::BASIC_AUTH,
        "authTypeData" => ["user" => "updateduser", "password" => "updatedpass"],
        "event" => [Webhook::CREATE_STAKEHOLDER, Webhook::UPDATE_STAKEHOLDER, Webhook::DELETE_STAKEHOLDER]
    ];
}