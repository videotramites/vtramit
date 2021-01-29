<?php
/**
 * @copyright Copyright (c) 2020-2021 Ajuntament de Barcelona
 * 
 * @author Daniel Tamajon <daniel@floss.cat>
 * @author Kenneth Peiruza <kenneth@floss.cat>
 * @author Letizia Benítez <letizia@floss.cat>
 * @author Marta González <gonzalez.marta@gmail.com>
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
 */

return [
    'resources' => [
        'appointment' => ['url' => '/appointments'],
        'appointment_api' => ['url' => '/api/0.1/appointments'],
        'queue' => ['url' => '/queue'],
        'queue_api' => ['url' => '/api/0.1/queue'],
    ],
    'routes' => [
        // Page
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'page#public', 'url' => '/public/{jitsiRoomCode}', 'verb' => 'GET'],

        // Settings
        ['name' => 'Config#get', 'url' => '/config', 'verb' => 'GET'],
        ['name' => 'Config#setValue', 'url' => '/config/{key}', 'verb' => 'POST'],

        // Appointments
        ['name' => 'appointment#getAppointments', 'url' => '/getappointments', 'verb' => 'POST'],
        ['name' => 'appointment#filterAppointments', 'url' => '/filterappointments', 'verb' => 'GET'],
        ['name' => 'appointment#getUserDepartments', 'url' => '/getdepartments', 'verb' => 'GET'],
        ['name' => 'appointment#sendMailByAppointmentId', 'url' => '/sendmailbyappointmentid/{appointmentId}', 'verb' => 'POST'],
        ['name' => 'appointment#videoconferenceFinished', 'url' => '/videoconferencefinished/{jitsiRoomCode}', 'verb' => 'POST'],

        // Appointments API
        ['name' => 'appointment_api#preflighted_cors', 'url' => '/api/0.1/{path}', 'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
        ['name' => 'appointment_api#videoconferenceFinished', 'url' => '/api/0.1/videoconferencefinished', 'verb' => 'POST'],
        ['name' => 'appointment_api#createOrUpdateAppointment', 'url' => '/api/0.1/createorupdateappointment', 'verb' => 'POST'],
        ['name' => 'appointment_api#updateStateToCancelledByExternalId', 'url' => '/api/0.1/cancelappointment', 'verb' => 'POST'],

        // Appointment state update
        ['name' => 'appointment#updateStateToOnCourse', 'url' => '/updatestatetooncourse/{appointmentId}', 'verb' => 'POST'],
        ['name' => 'appointment#updateStateToPendant', 'url' => '/updatestatetopendant/{appointmentId}', 'verb' => 'POST'],
        ['name' => 'appointment#updateStateToFinished', 'url' => '/updatestatetofinished/{appointmentId}', 'verb' => 'POST'],
        ['name' => 'appointment#updateStateToCompleted', 'url' => '/updatestatetocompleted/{appointmentId}', 'verb' => 'POST'],
        ['name' => 'appointment#updateStateToCancelled', 'url' => '/updatestatetocancelled/{appointmentId}', 'verb' => 'POST'],

        // User
        ['name' => 'appointment#getUserContext', 'url' => '/getusercontext', 'verb' => 'GET'],

        //Videoconference
        ['name' => 'videoconference_api#waitingForVC', 'url' => '/api/0.1/waitingforvc/{roomCode}', 'verb' => 'GET'],
    ]
];
