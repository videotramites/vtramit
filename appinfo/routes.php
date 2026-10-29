<?php
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
 * @license GNU AGPL version 3 or any later version
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
 *
 * See:
 * https://docs.nextcloud.com/server/latest/developer_manual/basics/routing.html
 */

return [
    'resources' => [
        'appointment'     => ['url' => '/appointments'],
        'appointment_api' => ['url' => '/api/0.1/appointments'],
        'queue'           => ['url' => '/queue'],
        'queue_api'       => ['url' => '/api/0.1/queue'],
    ],
    'routes' => [
        ////////////////////// Entry points & settings
        //////////////////////

        // Page
        ['name' => 'page#index',          'url' => '/',                          'verb' => 'GET'],
        ['name' => 'page#proceduresView', 'url' => '/procedures/',               'verb' => 'GET'],
        ['name' => 'page#attendant',      'url' => '/attendant/{jitsiRoomCode}', 'verb' => 'GET'],
        ['name' => 'page#public',         'url' => '/public/{jitsiRoomCode}',    'verb' => 'GET'],        ////////////////////// v0.1 routing
        //////////////////////

        // Videoconference
        // Replaced by POST /api/2.0/procedure/videoconference/{jitsiRoomCode}/waiting
        //@Deprecated
        //['name' => 'videoconference_api#waitingForVC', 'url' => '/api/0.1/waitingforvc/{roomCode}', 'verb' => 'GET'],



        ////////////////////// v2.0 routing
        //////////////////////

        // Configuration
        ['name' => 'configuration#index',  'url' => '/web/2.0/configuration',      'verb' => 'GET'],
        ['name' => 'configuration#update', 'url' => '/web/2.0/configuration',      'verb' => 'POST'],
        ['name' => 'configuration#update', 'url' => '/web/2.0/configuration',      'verb' => 'PUT'],


        // Configuration API
        ['name' => 'configurationApi#index',  'url' => '/api/2.0/configuration',      'verb' => 'GET'],
        ['name' => 'configurationApi#update', 'url' => '/api/2.0/configuration',      'verb' => 'POST'],
        ['name' => 'configurationApi#update', 'url' => '/api/2.0/configuration',      'verb' => 'PUT'],


        // Context
        ['name' => 'context#getAppContext',          'url' => '/web/2.0/context/app',                 'verb' => 'GET'],
        ['name' => 'context#getProcedureStatuses',   'url' => '/web/2.0/context/procedure/status',    'verb' => 'GET'],
        ['name' => 'context#getSignatureStatuses',   'url' => '/web/2.0/context/signature/status',    'verb' => 'GET'],
        ['name' => 'context#getChannels',            'url' => '/web/2.0/context/attendance-channel',  'verb' => 'GET'],
        ['name' => 'context#getLanguages',           'url' => '/web/2.0/context/language',            'verb' => 'GET'],
        ['name' => 'context#getGroups',              'url' => '/web/2.0/context/groups',              'verb' => 'GET'],
        ['name' => 'context#getMessageChannels',     'url' => '/web/2.0/context/message-channel',     'verb' => 'GET'],
        ['name' => 'procedure#getContext',           'url' => '/web/2.0/context/procedure/{id}',      'verb' => 'GET'],
        ['name' => 'procedureType#getContext',       'url' => '/web/2.0/context/procedure-type/{id}', 'verb' => 'GET'],
        ['name' => 'mailTemplate#indexVariables',    'url' => '/web/2.0/context/mail/variables',      'verb' => 'GET'],
        ['name' => 'messageTemplate#indexVariables', 'url' => '/web/2.0/context/message/variables',   'verb' => 'GET'],


        // Context API
        ['name' => 'contextApi#getAppContext',          'url' => '/api/2.0/context/app',                 'verb' => 'GET'],
        ['name' => 'contextApi#getProcedureStatuses',   'url' => '/api/2.0/context/procedure/status',    'verb' => 'GET'],
        ['name' => 'contextApi#getSignatureStatuses',   'url' => '/api/2.0/context/signature/status',    'verb' => 'GET'],
        ['name' => 'contextApi#getChannels',            'url' => '/api/2.0/context/attendance-channel',  'verb' => 'GET'],
        ['name' => 'contextApi#getLanguages',           'url' => '/api/2.0/context/language',            'verb' => 'GET'],
        ['name' => 'contextApi#getGroups',              'url' => '/api/2.0/context/groups',              'verb' => 'GET'],
        ['name' => 'contextApi#getMessageChannels',     'url' => '/api/2.0/context/message-channel',     'verb' => 'GET'],
        ['name' => 'procedureApi#getContext',           'url' => '/api/2.0/context/procedure/{id}',      'verb' => 'GET'],
        ['name' => 'procedureTypeApi#getContext',       'url' => '/api/2.0/context/procedure-type/{id}', 'verb' => 'GET'],
        ['name' => 'mailTemplateApi#indexVariables',    'url' => '/api/2.0/context/mail/variables',      'verb' => 'GET'],
        ['name' => 'messageTemplateApi#indexVariables', 'url' => '/api/2.0/context/message/variables',   'verb' => 'GET'],


        // Procedure
        ['name' => 'procedure#index',  'url' => '/web/2.0/procedure',      'verb' => 'GET'],
        ['name' => 'procedure#find',   'url' => '/web/2.0/procedure/{id}', 'verb' => 'GET'],
        ['name' => 'procedure#create', 'url' => '/web/2.0/procedure',      'verb' => 'POST'],
        ['name' => 'procedure#update', 'url' => '/web/2.0/procedure',      'verb' => 'PUT'],
        ['name' => 'procedure#delete', 'url' => '/web/2.0/procedure/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedure#updateByExternalId',      'url' => '/web/2.0/procedure/externalId',                 'verb' => 'POST'],
        ['name' => 'procedure#findByExternalId',        'url' => '/web/2.0/procedure/externalId/{externalId}',    'verb' => 'GET'],
        ['name' => 'procedure#findByFilter',            'url' => '/web/2.0/procedure/search/filter',              'verb' => 'POST'],
        ['name' => 'procedure#sendEmail',               'url' => '/web/2.0/procedure/{id}/stakeholder/op/email',  'verb' => 'POST'],

        ['name' => 'procedure#initializeSignature',                'url' => '/web/2.0/procedure/{id}/signature/initialize/{signatureButtonId}', 'verb' => 'POST'],
        ['name' => 'procedure#getSignatureUploadsFolderFiles',     'url' => '/web/2.0/procedure/{id}/signature/upload-folder',    'verb' => 'GET'],
        ['name' => 'procedure#uploadToSignatureUploadsFolder',     'url' => '/web/2.0/procedure/{id}/signature/upload-folder',    'verb' => 'POST'],
        ['name' => 'procedure#deleteFromSignatureUploadsFolder',   'url' => '/web/2.0/procedure/{id}/signature/upload-folder',    'verb' => 'DELETE'],
        ['name' => 'procedure#getSignatureDownloadsFolderFiles',   'url' => '/web/2.0/procedure/{id}/signature/download-folder',  'verb' => 'GET'],
        ['name' => 'procedure#uploadToSignatureDownloadsFolder',   'url' => '/web/2.0/procedure/{id}/signature/download-folder',  'verb' => 'POST'],
        ['name' => 'procedure#deleteFromSignatureDownloadsFolder', 'url' => '/web/2.0/procedure/{id}/signature/download-folder',  'verb' => 'DELETE'],

        ['name' => 'procedure#getUploadsFolderFiles',     'url' => '/web/2.0/procedure/{id}/file/upload-folder',    'verb' => 'GET'],
        ['name' => 'procedure#uploadToUploadsFolder',     'url' => '/web/2.0/procedure/{id}/file/upload-folder',    'verb' => 'POST'],
        ['name' => 'procedure#deleteFromUploadsFolder',   'url' => '/web/2.0/procedure/{id}/file/upload-folder',    'verb' => 'DELETE'],
        ['name' => 'procedure#getDownloadsFolderFiles',   'url' => '/web/2.0/procedure/{id}/file/download-folder',  'verb' => 'GET'],
        ['name' => 'procedure#uploadToDownloadsFolder',   'url' => '/web/2.0/procedure/{id}/file/download-folder',  'verb' => 'POST'],
        ['name' => 'procedure#deleteFromDownloadsFolder', 'url' => '/web/2.0/procedure/{id}/file/download-folder',  'verb' => 'DELETE'],
        //['name' => 'procedure#getUploadFiles', 'url' => '/web/2.0/procedure/{id}/file/upload', 'verb' => 'GET'],

        ['name' => 'videoconference#waitingProcedures', 'url' => '/web/2.0/procedure/videoconference/op/waiting',               'verb' => 'GET'],
        ['name' => 'videoconference#procedureWaiting',  'url' => '/web/2.0/procedure/videoconference/{jitsiRoomCode}/waiting',  'verb' => 'POST'],
        ['name' => 'procedure#videoconferenceStarted',  'url' => '/web/2.0/procedure/videoconference/{jitsiRoomCode}/started',  'verb' => 'POST'],
        ['name' => 'procedure#videoconferenceFinished', 'url' => '/web/2.0/procedure/videoconference/{jitsiRoomCode}/finished', 'verb' => 'POST'],

        // Videocall Test (web — public portal)
        ['name' => 'videocallTest#startTest', 'url' => '/web/2.0/videocall-test/start',                     'verb' => 'POST'],
        ['name' => 'videocallTest#getResult', 'url' => '/web/2.0/videocall-test/result/{stakeholderId}',     'verb' => 'GET'],

        // Videocall Test (API — robot callback + mirrors)
        ['name' => 'videocallTestApi#callback',  'url' => '/api/2.0/videocall-test/callback',                'verb' => 'POST'],
        ['name' => 'videocallTestApi#startTest', 'url' => '/api/2.0/videocall-test/start',                   'verb' => 'POST'],
        ['name' => 'videocallTestApi#getResult', 'url' => '/api/2.0/videocall-test/result/{stakeholderId}',  'verb' => 'GET'],

        ['name' => 'signature#waitingProcedures', 'url' => '/web/2.0/procedure/signature/op/waiting',               'verb' => 'GET'],

        ['name' => 'messageQueue#createByVideoconference',        'url' => '/web/2.0/videoconference/{jitsiRoomCode}/message/{channel}',      'verb' => 'POST'],
        ['name' => 'messageQueue#nextByVideoconference',          'url' => '/web/2.0/videoconference/{jitsiRoomCode}/message/{channel}/next', 'verb' => 'GET'],
        ['name' => 'messageQueue#deleteByVideoconference',        'url' => '/web/2.0/videoconference/{jitsiRoomCode}/message/{channel}/{id}', 'verb' => 'DELETE'],
        ['name' => 'messageQueue#createCommandByVideoconference', 'url' => '/web/2.0/videoconference/{jitsiRoomCode}/command',      'verb' => 'POST'],
        ['name' => 'messageQueue#nextCommandByVideoconference',   'url' => '/web/2.0/videoconference/{jitsiRoomCode}/command/next', 'verb' => 'GET'],
        ['name' => 'messageQueue#deleteCommandByVideoconference', 'url' => '/web/2.0/videoconference/{jitsiRoomCode}/command/{id}', 'verb' => 'DELETE'],


        // Procedure API
        ['name' => 'procedureApi#index',  'url' => '/api/2.0/procedure',      'verb' => 'GET'],
        ['name' => 'procedureApi#find',   'url' => '/api/2.0/procedure/{id}', 'verb' => 'GET'],
        ['name' => 'procedureApi#create', 'url' => '/api/2.0/procedure',      'verb' => 'POST'],
        ['name' => 'procedureApi#update', 'url' => '/api/2.0/procedure',      'verb' => 'PUT'],
        ['name' => 'procedureApi#delete', 'url' => '/api/2.0/procedure/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureApi#updateByExternalId',      'url' => '/api/2.0/procedure/externalId',                 'verb' => 'POST'],
        ['name' => 'procedureApi#findByExternalId',        'url' => '/api/2.0/procedure/externalId/{externalId}',    'verb' => 'GET'],
        ['name' => 'procedureApi#findByFilter',            'url' => '/api/2.0/procedure/search/filter',              'verb' => 'POST'],
        ['name' => 'procedureApi#sendEmail',               'url' => '/api/2.0/procedure/{id}/stakeholder/op/email',  'verb' => 'POST'],

        ['name' => 'procedureApi#initializeSignature',                'url' => '/api/2.0/procedure/{id}/signature/initialize/{signatureButtonId}', 'verb' => 'POST'],
        ['name' => 'procedureApi#getSignatureUploadsFolderFiles',     'url' => '/api/2.0/procedure/{id}/signature/upload-folder',    'verb' => 'GET'],
        ['name' => 'procedureApi#uploadToSignatureUploadsFolder',     'url' => '/api/2.0/procedure/{id}/signature/upload-folder',    'verb' => 'POST'],
        ['name' => 'procedureApi#deleteFromSignatureUploadsFolder',   'url' => '/api/2.0/procedure/{id}/signature/upload-folder',    'verb' => 'DELETE'],
        ['name' => 'procedureApi#getSignatureDownloadsFolderFiles',   'url' => '/api/2.0/procedure/{id}/signature/download-folder',  'verb' => 'GET'],
        ['name' => 'procedureApi#uploadToSignatureDownloadsFolder',   'url' => '/api/2.0/procedure/{id}/signature/download-folder',  'verb' => 'POST'],
        ['name' => 'procedureApi#deleteFromSignatureDownloadsFolder', 'url' => '/api/2.0/procedure/{id}/signature/download-folder',  'verb' => 'DELETE'],

        ['name' => 'procedureApi#getUploadsFolderFiles',     'url' => '/api/2.0/procedure/{id}/file/upload-folder',    'verb' => 'GET'],
        ['name' => 'procedureApi#uploadToUploadsFolder',     'url' => '/api/2.0/procedure/{id}/file/upload-folder',    'verb' => 'POST'],
        ['name' => 'procedureApi#deleteFromUploadsFolder',   'url' => '/api/2.0/procedure/{id}/file/upload-folder',    'verb' => 'DELETE'],
        ['name' => 'procedureApi#getDownloadsFolderFiles',   'url' => '/api/2.0/procedure/{id}/file/download-folder',  'verb' => 'GET'],
        ['name' => 'procedureApi#uploadToDownloadsFolder',   'url' => '/api/2.0/procedure/{id}/file/download-folder',  'verb' => 'POST'],
        ['name' => 'procedureApi#deleteFromDownloadsFolder', 'url' => '/api/2.0/procedure/{id}/file/download-folder',  'verb' => 'DELETE'],

        ['name' => 'videoconferenceApi#waitingProcedures', 'url' => '/api/2.0/procedure/videoconference/op/waiting',               'verb' => 'GET'],
        ['name' => 'videoconferenceApi#procedureWaiting',  'url' => '/api/2.0/procedure/videoconference/{jitsiRoomCode}/waiting',  'verb' => 'POST'],
        ['name' => 'procedureApi#videoconferenceStarted',  'url' => '/api/2.0/procedure/videoconference/{jitsiRoomCode}/started',  'verb' => 'POST'],
        ['name' => 'procedureApi#videoconferenceFinished', 'url' => '/api/2.0/procedure/videoconference/{jitsiRoomCode}/finished', 'verb' => 'POST'],

        ['name' => 'signatureApi#waitingProcedures',       'url' => '/api/2.0/procedure/signature/op/waiting',               'verb' => 'GET'],

        ['name' => 'messageQueueApi#createByVideoconference',        'url' => '/api/2.0/videoconference/{jitsiRoomCode}/message/{channel}',      'verb' => 'POST'],
        ['name' => 'messageQueueApi#nextByVideoconference',          'url' => '/api/2.0/videoconference/{jitsiRoomCode}/message/{channel}/next', 'verb' => 'GET'],
        ['name' => 'messageQueueApi#deleteByVideoconference',        'url' => '/api/2.0/videoconference/{jitsiRoomCode}/message/{channel}/{id}', 'verb' => 'DELETE'],
        ['name' => 'messageQueueApi#createCommandByVideoconference', 'url' => '/api/2.0/videoconference/{jitsiRoomCode}/command',      'verb' => 'POST'],
        ['name' => 'messageQueueApi#nextCommandByVideoconference',   'url' => '/api/2.0/videoconference/{jitsiRoomCode}/command/next', 'verb' => 'GET'],
        ['name' => 'messageQueueApi#deleteCommandByVideoconference', 'url' => '/api/2.0/videoconference/{jitsiRoomCode}/command/{id}', 'verb' => 'DELETE'],


        // Procedure Protocol Log
        ['name' => 'procedureProtocolLog#getProtocolSteps', 'url' => '/web/2.0/procedure/{id}/protocol-step',           'verb' => 'GET'],
        ['name' => 'procedureProtocolLog#completeStep',     'url' => '/web/2.0/procedure/{id}/protocol-step/{stepId}',  'verb' => 'POST'],


        // Procedure Protocol Log API
        ['name' => 'procedureProtocolLogApi#getProtocolSteps', 'url' => '/api/2.0/procedure/{id}/protocol-step',           'verb' => 'GET'],
        ['name' => 'procedureProtocolLogApi#completeStep',     'url' => '/api/2.0/procedure/{id}/protocol-step/{stepId}',  'verb' => 'POST'],


        // Procedure stakeholder
        ['name' => 'procedureStakeholder#index',  'url' => '/web/2.0/procedure-stakeholder',      'verb' => 'GET'],
        ['name' => 'procedureStakeholder#find',   'url' => '/web/2.0/procedure-stakeholder/{id}', 'verb' => 'GET'],
        ['name' => 'procedureStakeholder#create', 'url' => '/web/2.0/procedure-stakeholder',      'verb' => 'POST'],
        ['name' => 'procedureStakeholder#update', 'url' => '/web/2.0/procedure-stakeholder',      'verb' => 'PUT'],
        ['name' => 'procedureStakeholder#delete', 'url' => '/web/2.0/procedure-stakeholder/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureStakeholder#changePassword',   'url' => '/web/2.0/procedure-stakeholder/{id}/password',   'verb' => 'PUT'],
        ['name' => 'procedureStakeholder#findByFilter',     'url' => '/web/2.0/procedure-stakeholder/search/filter',   'verb' => 'POST'],
        ['name' => 'procedureStakeholder#getEntryPage',     'url' => '/web/2.0/procedure-stakeholder/{id}/entry-page', 'verb' => 'GET'],

        ['name' => 'procedureStakeholder#indexByProcedureId',     'url' => '/web/2.0/procedure/{procedureId}/stakeholder',      'verb' => 'GET'],
        ['name' => 'procedureStakeholder#findByProcedureId',      'url' => '/web/2.0/procedure/{procedureId}/stakeholder/{id}', 'verb' => 'GET'],
        ['name' => 'procedureStakeholder#createByProcedureId',    'url' => '/web/2.0/procedure/{procedureId}/stakeholder',      'verb' => 'POST'],
        ['name' => 'procedureStakeholder#updateByProcedureId',    'url' => '/web/2.0/procedure/{procedureId}/stakeholder',      'verb' => 'PUT'],
        ['name' => 'procedureStakeholder#deleteAllByProcedureId', 'url' => '/web/2.0/procedure/{procedureId}/stakeholder',      'verb' => 'DELETE'],
        ['name' => 'procedureStakeholder#deleteByProcedureId',    'url' => '/web/2.0/procedure/{procedureId}/stakeholder/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureStakeholder#sendEmail',   'url' => '/web/2.0/procedure/{procedureId}/stakeholder/{id}/op/email', 'verb' => 'POST'],


        // Procedure stakeholder API
        ['name' => 'procedureStakeholderApi#index',  'url' => '/api/2.0/procedure-stakeholder',      'verb' => 'GET'],
        ['name' => 'procedureStakeholderApi#find',   'url' => '/api/2.0/procedure-stakeholder/{id}', 'verb' => 'GET'],
        ['name' => 'procedureStakeholderApi#create', 'url' => '/api/2.0/procedure-stakeholder',      'verb' => 'POST'],
        ['name' => 'procedureStakeholderApi#update', 'url' => '/api/2.0/procedure-stakeholder',      'verb' => 'PUT'],
        ['name' => 'procedureStakeholderApi#delete', 'url' => '/api/2.0/procedure-stakeholder/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureStakeholderApi#changePassword', 'url' => '/api/2.0/procedure-stakeholder/{id}/password',   'verb' => 'PUT'],
        ['name' => 'procedureStakeholderApi#findByFilter',   'url' => '/api/2.0/procedure-stakeholder/search/filter',   'verb' => 'POST'],
        ['name' => 'procedureStakeholderApi#getEntryPage',   'url' => '/api/2.0/procedure-stakeholder/{id}/entry-page', 'verb' => 'GET'],

        ['name' => 'procedureStakeholderApi#indexByProcedureId',     'url' => '/api/2.0/procedure/{procedureId}/stakeholder',      'verb' => 'GET'],
        ['name' => 'procedureStakeholderApi#findByProcedureId',      'url' => '/api/2.0/procedure/{procedureId}/stakeholder/{id}', 'verb' => 'GET'],
        ['name' => 'procedureStakeholderApi#createByProcedureId',    'url' => '/api/2.0/procedure/{procedureId}/stakeholder',      'verb' => 'POST'],
        ['name' => 'procedureStakeholderApi#updateByProcedureId',    'url' => '/api/2.0/procedure/{procedureId}/stakeholder',      'verb' => 'PUT'],
        ['name' => 'procedureStakeholderApi#deleteAllByProcedureId', 'url' => '/api/2.0/procedure/{procedureId}/stakeholder',      'verb' => 'DELETE'],
        ['name' => 'procedureStakeholderApi#deleteByProcedureId',    'url' => '/api/2.0/procedure/{procedureId}/stakeholder/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureStakeholderApi#sendEmail',              'url' => '/api/2.0/procedure/{procedureId}/stakeholder/{id}/op/email', 'verb' => 'POST'],


        // Signature
        ['name' => 'signature#getInfoByHash',                           'url' => '/web/2.0/signature/info/{hash}',                  'verb' => 'GET'],
        ['name' => 'signature#uploadCompletedByHash',                   'url' => '/web/2.0/signature/uploadcompleted/{hash}',       'verb' => 'POST'],
        ['name' => 'signature#getPendingSignatureFilesByJitsiRoomCode', 'url' => '/web/2.0/signature/pendingfiles/{jitsiRoomCode}', 'verb' => 'GET'],
        ['name' => 'signature#findByFilter',                            'url' => '/web/2.0/signature/search/filter',                'verb' => 'POST'],
        ['name' => 'signature#cancelFileSignature',                     'url' => '/web/2.0/signature/{id}/file/{fileId}/cancel',    'verb' => 'POST'],


        // Signature API
        ['name' => 'signature_api#getInfoByHash',                           'url' => '/api/2.0/signature/info/{hash}',                  'verb' => 'GET'],
        ['name' => 'signature_api#uploadCompletedByHash',                   'url' => '/api/2.0/signature/uploadcompleted/{hash}',       'verb' => 'POST'],
        ['name' => 'signature_api#getPendingSignatureFilesByJitsiRoomCode', 'url' => '/api/2.0/signature/pendingfiles/{jitsiRoomCode}', 'verb' => 'GET'],
        ['name' => 'signature_api#findByFilter',                            'url' => '/api/2.0/signature/search/filter',                'verb' => 'POST'],
        ['name' => 'signature_api#cancelFileSignature',                     'url' => '/api/2.0/signature/{id}/file/{fileId}/cancel',    'verb' => 'POST'],


        // Mail template
        ['name' => 'mailTemplate#index',  'url' => '/web/2.0/mail-template',      'verb' => 'GET'],
        ['name' => 'mailTemplate#find',   'url' => '/web/2.0/mail-template/{id}', 'verb' => 'GET'],
        ['name' => 'mailTemplate#create', 'url' => '/web/2.0/mail-template',      'verb' => 'POST'],
        ['name' => 'mailTemplate#update', 'url' => '/web/2.0/mail-template',      'verb' => 'PUT'],
        ['name' => 'mailTemplate#delete', 'url' => '/web/2.0/mail-template/{id}', 'verb' => 'DELETE'],


        // Mail template API
        ['name' => 'mailTemplateApi#index',  'url' => '/api/2.0/mail-template',      'verb' => 'GET'],
        ['name' => 'mailTemplateApi#find',   'url' => '/api/2.0/mail-template/{id}', 'verb' => 'GET'],
        ['name' => 'mailTemplateApi#create', 'url' => '/api/2.0/mail-template',      'verb' => 'POST'],
        ['name' => 'mailTemplateApi#update', 'url' => '/api/2.0/mail-template',      'verb' => 'PUT'],
        ['name' => 'mailTemplateApi#delete', 'url' => '/api/2.0/mail-template/{id}', 'verb' => 'DELETE'],


        // Mail queue template
        ['name' => 'mailQueue#index',  'url' => '/web/2.0/mail-queue',      'verb' => 'GET'],
        ['name' => 'mailQueue#next',   'url' => '/web/2.0/mail-queue/next', 'verb' => 'GET'],
        ['name' => 'mailQueue#find',   'url' => '/web/2.0/mail-queue/{id}', 'verb' => 'GET'],
        ['name' => 'mailQueue#create', 'url' => '/web/2.0/mail-queue',      'verb' => 'POST'],
        ['name' => 'mailQueue#update', 'url' => '/web/2.0/mail-queue',      'verb' => 'PUT'],
        ['name' => 'mailQueue#delete', 'url' => '/web/2.0/mail-queue/{id}', 'verb' => 'DELETE'],


        // Mail queue template API
        ['name' => 'mailQueueApi#index',  'url' => '/api/2.0/mail-queue',      'verb' => 'GET'],
        ['name' => 'mailQueueApi#next',   'url' => '/api/2.0/mail-queue/next', 'verb' => 'GET'],
        ['name' => 'mailQueueApi#find',   'url' => '/api/2.0/mail-queue/{id}', 'verb' => 'GET'],
        ['name' => 'mailQueueApi#create', 'url' => '/api/2.0/mail-queue',      'verb' => 'POST'],
        ['name' => 'mailQueueApi#update', 'url' => '/api/2.0/mail-queue',      'verb' => 'PUT'],
        ['name' => 'mailQueueApi#delete', 'url' => '/api/2.0/mail-queue/{id}', 'verb' => 'DELETE'],


        // Message template
        ['name' => 'messageTemplate#index',  'url' => '/web/2.0/message-template',      'verb' => 'GET'],
        ['name' => 'messageTemplate#find',   'url' => '/web/2.0/message-template/{id}', 'verb' => 'GET'],
        ['name' => 'messageTemplate#create', 'url' => '/web/2.0/message-template',      'verb' => 'POST'],
        ['name' => 'messageTemplate#update', 'url' => '/web/2.0/message-template',      'verb' => 'PUT'],
        ['name' => 'messageTemplate#delete', 'url' => '/web/2.0/message-template/{id}', 'verb' => 'DELETE'],


        // Message template API
        ['name' => 'messageTemplateApi#index',  'url' => '/api/2.0/message-template',      'verb' => 'GET'],
        ['name' => 'messageTemplateApi#find',   'url' => '/api/2.0/message-template/{id}', 'verb' => 'GET'],
        ['name' => 'messageTemplateApi#create', 'url' => '/api/2.0/message-template',      'verb' => 'POST'],
        ['name' => 'messageTemplateApi#update', 'url' => '/api/2.0/message-template',      'verb' => 'PUT'],
        ['name' => 'messageTemplateApi#delete', 'url' => '/api/2.0/message-template/{id}', 'verb' => 'DELETE'],


        // Message queue template
        ['name' => 'messageQueue#index',  'url' => '/web/2.0/message-queue',                'verb' => 'GET'],
        ['name' => 'messageQueue#next',   'url' => '/web/2.0/message-queue/{channel}/next', 'verb' => 'GET'],
        ['name' => 'messageQueue#find',   'url' => '/web/2.0/message-queue/{id}',           'verb' => 'GET'],
        ['name' => 'messageQueue#create', 'url' => '/web/2.0/message-queue',                'verb' => 'POST'],
        ['name' => 'messageQueue#update', 'url' => '/web/2.0/message-queue',                'verb' => 'PUT'],
        ['name' => 'messageQueue#delete', 'url' => '/web/2.0/message-queue/{id}',           'verb' => 'DELETE'],


        // Message queue template API
        ['name' => 'messageQueueApi#index',  'url' => '/api/2.0/message-queue',                'verb' => 'GET'],
        ['name' => 'messageQueueApi#next',   'url' => '/api/2.0/message-queue/{channel}/next', 'verb' => 'GET'],
        ['name' => 'messageQueueApi#find',   'url' => '/api/2.0/message-queue/{id}',           'verb' => 'GET'],
        ['name' => 'messageQueueApi#create', 'url' => '/api/2.0/message-queue',                'verb' => 'POST'],
        ['name' => 'messageQueueApi#update', 'url' => '/api/2.0/message-queue',                'verb' => 'PUT'],
        ['name' => 'messageQueueApi#delete', 'url' => '/api/2.0/message-queue/{id}',           'verb' => 'DELETE'],


        // SignatureType
        ['name' => 'signatureType#index',  'url' => '/web/2.0/signature-type',      'verb' => 'GET'],
        ['name' => 'signatureType#find',   'url' => '/web/2.0/signature-type/{id}', 'verb' => 'GET'],
        ['name' => 'signatureType#create', 'url' => '/web/2.0/signature-type',      'verb' => 'POST'],
        ['name' => 'signatureType#update', 'url' => '/web/2.0/signature-type',      'verb' => 'PUT'],
        ['name' => 'signatureType#delete', 'url' => '/web/2.0/signature-type/{id}',  'verb' => 'DELETE'],


        // SignatureType API
        ['name' => 'signatureTypeApi#index',  'url' => '/api/2.0/signature-type',      'verb' => 'GET'],
        ['name' => 'signatureTypeApi#find',   'url' => '/api/2.0/signature-type/{id}', 'verb' => 'GET'],
        ['name' => 'signatureTypeApi#create', 'url' => '/api/2.0/signature-type',      'verb' => 'POST'],
        ['name' => 'signatureTypeApi#update', 'url' => '/api/2.0/signature-type',      'verb' => 'PUT'],
        ['name' => 'signatureTypeApi#delete', 'url' => '/api/2.0/signature-type/{id}', 'verb' => 'DELETE'],


        // SignatureButtons
        ['name' => 'signatureButton#index',   'url' => '/web/2.0/signature-buttons',      'verb' => 'GET'],
        ['name' => 'signatureButton#find',    'url' => '/web/2.0/signature-buttons/{id}', 'verb' => 'GET'],
        ['name' => 'signatureButton#create',  'url' => '/web/2.0/signature-buttons',      'verb' => 'POST'],
        ['name' => 'signatureButton#update',  'url' => '/web/2.0/signature-buttons',       'verb' => 'PUT'],
        ['name' => 'signatureButton#delete',  'url' => '/web/2.0/signature-buttons/{id}',  'verb' => 'DELETE'],

        ['name' => 'signatureButton#findByProcedureTypeId',  'url' => '/web/2.0/procedure-type/{id}/signature-buttons',      'verb' => 'GET'],


        // SignatureButtons API
        ['name' => 'signatureButtonApi#index',  'url' => '/api/2.0/signature-buttons',      'verb' => 'GET'],
        ['name' => 'signatureButtonApi#find',   'url' => '/api/2.0/signature-buttons/{id}', 'verb' => 'GET'],
        ['name' => 'signatureButtonApi#create', 'url' => '/api/2.0/signature-buttons',      'verb' => 'POST'],
        ['name' => 'signatureButtonApi#update', 'url' => '/api/2.0/signature-buttons',      'verb' => 'PUT'],
        ['name' => 'signatureButtonApi#delete', 'url' => '/api/2.0/signature-buttons/{id}', 'verb' => 'DELETE'],

        ['name' => 'signatureButtonApi#findByProcedureTypeId',  'url' => '/api/2.0/procedure-type/{id}/signature-buttons',      'verb' => 'GET'],


        // ProcedureType
        ['name' => 'procedureType#index',  'url' => '/web/2.0/procedure-type',      'verb' => 'GET'],
        ['name' => 'procedureType#find',   'url' => '/web/2.0/procedure-type/{id}', 'verb' => 'GET'],
        ['name' => 'procedureType#create', 'url' => '/web/2.0/procedure-type',      'verb' => 'POST'],
        ['name' => 'procedureType#update', 'url' => '/web/2.0/procedure-type',      'verb' => 'PUT'],
        ['name' => 'procedureType#delete', 'url' => '/web/2.0/procedure-type/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureType#findByFilter', 'url' => '/web/2.0/procedure-type/search/filter', 'verb' => 'POST'],


        // ProcedureType API
        ['name' => 'procedureTypeApi#index',  'url' => '/api/2.0/procedure-type',      'verb' => 'GET'],
        ['name' => 'procedureTypeApi#find',   'url' => '/api/2.0/procedure-type/{id}', 'verb' => 'GET'],
        ['name' => 'procedureTypeApi#create', 'url' => '/api/2.0/procedure-type',      'verb' => 'POST'],
        ['name' => 'procedureTypeApi#update', 'url' => '/api/2.0/procedure-type',      'verb' => 'PUT'],
        ['name' => 'procedureTypeApi#delete', 'url' => '/api/2.0/procedure-type/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureTypeApi#findByFilter', 'url' => '/api/2.0/procedure-type/search/filter', 'verb' => 'POST'],


        // ProcedureTypeGroup
        ['name' => 'procedureTypeGroup#index',  'url' => '/web/2.0/procedure-type-group',      'verb' => 'GET'],
        ['name' => 'procedureTypeGroup#find',   'url' => '/web/2.0/procedure-type-group/{id}', 'verb' => 'GET'],
        ['name' => 'procedureTypeGroup#create', 'url' => '/web/2.0/procedure-type-group',      'verb' => 'POST'],
        ['name' => 'procedureTypeGroup#update', 'url' => '/web/2.0/procedure-type-group',      'verb' => 'PUT'],
        ['name' => 'procedureTypeGroup#delete', 'url' => '/web/2.0/procedure-type-group/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureTypeGroup#findByProcedureTypeId',  'url' => '/web/2.0/procedure-type/{id}/groups',      'verb' => 'GET'],


        // ProcedureTypeGroup API
        ['name' => 'procedureTypeGroupApi#index',  'url' => '/api/2.0/procedure-type-group',      'verb' => 'GET'],
        ['name' => 'procedureTypeGroupApi#find',   'url' => '/api/2.0/procedure-type-group/{id}', 'verb' => 'GET'],
        ['name' => 'procedureTypeGroupApi#create', 'url' => '/api/2.0/procedure-type-group',      'verb' => 'POST'],
        ['name' => 'procedureTypeGroupApi#update', 'url' => '/api/2.0/procedure-type-group',      'verb' => 'PUT'],
        ['name' => 'procedureTypeGroupApi#delete', 'url' => '/api/2.0/procedure-type-group/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureTypeGroupApi#findByProcedureTypeId',  'url' => '/api/2.0/procedure-type/{id}/groups',      'verb' => 'GET'],


        // ProcedureTypeProtocolStep
        ['name' => 'procedureTypeProtocolStep#index',  'url' => '/web/2.0/procedure-type-protocol-step',      'verb' => 'GET'],
        ['name' => 'procedureTypeProtocolStep#find',   'url' => '/web/2.0/procedure-type-protocol-step/{id}', 'verb' => 'GET'],
        ['name' => 'procedureTypeProtocolStep#create', 'url' => '/web/2.0/procedure-type-protocol-step',      'verb' => 'POST'],
        ['name' => 'procedureTypeProtocolStep#update', 'url' => '/web/2.0/procedure-type-protocol-step',      'verb' => 'PUT'],
        ['name' => 'procedureTypeProtocolStep#delete', 'url' => '/web/2.0/procedure-type-protocol-step/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureTypeProtocolStep#findByProcedureTypeGroupId', 'url' => '/web/2.0/procedure-type-group/{id}/steps', 'verb' => 'GET'],


        // ProcedureTypeProtocolStep API
        ['name' => 'procedureTypeProtocolStepApi#index',  'url' => '/api/2.0/procedure-type-protocol-step',      'verb' => 'GET'],
        ['name' => 'procedureTypeProtocolStepApi#find',   'url' => '/api/2.0/procedure-type-protocol-step/{id}', 'verb' => 'GET'],
        ['name' => 'procedureTypeProtocolStepApi#create', 'url' => '/api/2.0/procedure-type-protocol-step',      'verb' => 'POST'],
        ['name' => 'procedureTypeProtocolStepApi#update', 'url' => '/api/2.0/procedure-type-protocol-step',      'verb' => 'PUT'],
        ['name' => 'procedureTypeProtocolStepApi#delete', 'url' => '/api/2.0/procedure-type-protocol-step/{id}', 'verb' => 'DELETE'],

        ['name' => 'procedureTypeProtocolStepApi#findByProcedureTypeGroupId', 'url' => '/api/2.0/procedure-type-group/{id}/steps', 'verb' => 'GET'],


        // Topic
        ['name' => 'topic#index',  'url' => '/web/2.0/topic',      'verb' => 'GET'],
        ['name' => 'topic#find',   'url' => '/web/2.0/topic/{id}', 'verb' => 'GET'],
        ['name' => 'topic#create', 'url' => '/web/2.0/topic',      'verb' => 'POST'],
        ['name' => 'topic#update', 'url' => '/web/2.0/topic',      'verb' => 'PUT'],
        ['name' => 'topic#delete', 'url' => '/web/2.0/topic/{id}', 'verb' => 'DELETE'],

        ['name' => 'topic#findByFilter', 'url' => '/web/2.0/topic/search/filter', 'verb' => 'POST'],


        // Topic API
        ['name' => 'topicApi#index',  'url' => '/api/2.0/topic',      'verb' => 'GET'],
        ['name' => 'topicApi#find',   'url' => '/api/2.0/topic/{id}', 'verb' => 'GET'],
        ['name' => 'topicApi#create', 'url' => '/api/2.0/topic',      'verb' => 'POST'],
        ['name' => 'topicApi#update', 'url' => '/api/2.0/topic',      'verb' => 'PUT'],
        ['name' => 'topicApi#delete', 'url' => '/api/2.0/topic/{id}', 'verb' => 'DELETE'],

        ['name' => 'topicApi#findByFilter', 'url' => '/api/2.0/topic/search/filter', 'verb' => 'POST'],


        // RgpdOption
        ['name' => 'rgpdOption#index',  'url' => '/web/2.0/rgpd-option',      'verb' => 'GET'],
        ['name' => 'rgpdOption#find',   'url' => '/web/2.0/rgpd-option/{id}', 'verb' => 'GET'],
        ['name' => 'rgpdOption#create', 'url' => '/web/2.0/rgpd-option',      'verb' => 'POST'],
        ['name' => 'rgpdOption#update', 'url' => '/web/2.0/rgpd-option',      'verb' => 'PUT'],
        ['name' => 'rgpdOption#delete', 'url' => '/web/2.0/rgpd-option/{id}', 'verb' => 'DELETE'],

        ['name' => 'rgpdOption#findByJitsiRoomCode', 'url' => '/web/2.0/rgpd-option/jitsiRoomCode/{jitsiRoomCode}',    'verb' => 'GET'],


        // RgpdOption API
        ['name' => 'rgpdOptionApi#index',  'url' => '/api/2.0/rgpd-option',      'verb' => 'GET'],
        ['name' => 'rgpdOptionApi#find',   'url' => '/api/2.0/rgpd-option/{id}', 'verb' => 'GET'],
        ['name' => 'rgpdOptionApi#create', 'url' => '/api/2.0/rgpd-option',      'verb' => 'POST'],
        ['name' => 'rgpdOptionApi#update', 'url' => '/api/2.0/rgpd-option',      'verb' => 'PUT'],
        ['name' => 'rgpdOptionApi#delete', 'url' => '/api/2.0/rgpd-option/{id}', 'verb' => 'DELETE'],

        ['name' => 'rgpdOptionApi#findByJitsiRoomCode', 'url' => '/api/2.0/rgpd-option/jitsiRoomCode/{jitsiRoomCode}',    'verb' => 'GET'],


        // RgpdActivity
        ['name' => 'rgpdActivity#index',  'url' => '/web/2.0/rgpd-activity',      'verb' => 'GET'],
        ['name' => 'rgpdActivity#find',   'url' => '/web/2.0/rgpd-activity/{id}', 'verb' => 'GET'],
        ['name' => 'rgpdActivity#create', 'url' => '/web/2.0/rgpd-activity',      'verb' => 'POST'],
        ['name' => 'rgpdActivity#update', 'url' => '/web/2.0/rgpd-activity',      'verb' => 'PUT'],
        ['name' => 'rgpdActivity#delete', 'url' => '/web/2.0/rgpd-activity/{id}', 'verb' => 'DELETE'],

        ['name' => 'rgpdActivity#findLastByProcedureStakeholder', 'url' => '/web/2.0/procedure/{procedureId}/stakeholder/{procedureStakeholderId}/rgpd-activity/last', 'verb' => 'GET'],


        // RgpdActivity API
        ['name' => 'rgpdActivityApi#index',  'url' => '/api/2.0/rgpd-activity',      'verb' => 'GET'],
        ['name' => 'rgpdActivityApi#find',   'url' => '/api/2.0/rgpd-activity/{id}', 'verb' => 'GET'],
        ['name' => 'rgpdActivityApi#create', 'url' => '/api/2.0/rgpd-activity',      'verb' => 'POST'],
        ['name' => 'rgpdActivityApi#update', 'url' => '/api/2.0/rgpd-activity',      'verb' => 'PUT'],
        ['name' => 'rgpdActivityApi#delete', 'url' => '/api/2.0/rgpd-activity/{id}', 'verb' => 'DELETE'],

        ['name' => 'rgpdActivityApi#findLastByProcedureStakeholder', 'url' => '/api/2.0/procedure/{procedureId}/stakeholder/{procedureStakeholderId}/rgpd-activity/last', 'verb' => 'GET'],


        // Jobs
        ['name' => 'procedureJob#findWorkForTodayToPrepare', 'url' => '/web/2.0/job/procedure/prepareWork/find', 'verb' => 'GET'],
        ['name' => 'procedureJob#prepareWorkToday',          'url' => '/web/2.0/job/procedure/prepareWork',      'verb' => 'POST'],
        ['name' => 'procedureJob#purgeData',                 'url' => '/web/2.0/job/procedure/purge',            'verb' => 'POST'],


        // Jobs API
        ['name' => 'procedureJobApi#findWorkForTodayToPrepare', 'url' => '/api/2.0/job/procedure/prepareWork/find', 'verb' => 'GET'],
        ['name' => 'procedureJobApi#prepareWorkToday',          'url' => '/api/2.0/job/procedure/prepareWork',      'verb' => 'POST'],
        ['name' => 'procedureJobApi#purgeData',                 'url' => '/api/2.0/job/procedure/purge',            'verb' => 'POST'],

        // Webhook
        ['name' => 'webhook#index',  'url' => '/web/2.0/webhook',      'verb' => 'GET'],
        ['name' => 'webhook#find',   'url' => '/web/2.0/webhook/{id}', 'verb' => 'GET'],
        ['name' => 'webhook#create', 'url' => '/web/2.0/webhook',      'verb' => 'POST'],
        ['name' => 'webhook#update', 'url' => '/web/2.0/webhook',      'verb' => 'PUT'],
        ['name' => 'webhook#delete', 'url' => '/web/2.0/webhook/{id}', 'verb' => 'DELETE'],


        // Webhook API
        ['name' => 'webhookApi#index',  'url' => '/api/2.0/webhook',      'verb' => 'GET'],
        ['name' => 'webhookApi#find',   'url' => '/api/2.0/webhook/{id}', 'verb' => 'GET'],
        ['name' => 'webhookApi#create', 'url' => '/api/2.0/webhook',      'verb' => 'POST'],
        ['name' => 'webhookApi#update', 'url' => '/api/2.0/webhook',      'verb' => 'PUT'],
        ['name' => 'webhookApi#delete', 'url' => '/api/2.0/webhook/{id}', 'verb' => 'DELETE'],


        // Test
        //['name' => 'testApi#procedureTypeInitAll',   'url' => '/api/2.0/test/procedure-type/all',    'verb' => 'POST'],
        ['name' => 'testApi#createConfiguration',    'url' => '/api/2.0/test/procedure/configuration/create', 'verb' => 'POST'],
        ['name' => 'testApi#cleanConfiguration',     'url' => '/api/2.0/test/procedure/configuration/clean',  'verb' => 'POST'],
        ['name' => 'testApi#cleanProcedures',        'url' => '/api/2.0/test/procedure/clean',                'verb' => 'POST'],
        ['name' => 'testApi#procedureEntityTest',    'url' => '/api/2.0/test/procedure/test',                 'verb' => 'POST'],
    ]
];
