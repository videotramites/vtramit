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

namespace OCA\VTramit\Service;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\ILogger;

use OCA\VTramit\Db\Appointment;
use OCA\VTramit\Db\AppointmentMapper;
use OCA\VTramit\Db\Videoconference;
use OCA\VTramit\Db\VideoconferenceMapper;

class VideoconferenceService {

    /** @var ILogger */
    private $logger;

    /** @var VideoconferenceMapper */
    private $videoconferenceMapper;

    /** @var AppointmentMapper */
    private $appointmentMapper;

    public function __construct(
        ILogger $logger,
        VideoconferenceMapper $videoconferenceMapper,
        AppointmentMapper $appointmentMapper
    ) {
        $this->logger = $logger;
        $this->videoconferenceMapper = $videoconferenceMapper;
        $this->appointmentMapper = $appointmentMapper;
    }

    /**
     * Create a new videoconference notification for given appointment, setting
     * first and last connection to current time.
     *
     * @param string $appointment_id   identifier of the appointment notified
     * @return Entity|Videoconference  new videoconference notification entity
     */
    protected function create(int $appointment_id) {
        $now = new \DateTime();
        $timestamp = $now->getTimestamp();

        $videoconference = new Videoconference();
        $videoconference->setAppointmentId($appointment_id);
        $videoconference->setFirstConnection($timestamp);
        $videoconference->setLastConnection($timestamp);

        return $this->videoconferenceMapper->insert($videoconference);
    }

    /**
     * Update the videoconference notification for given appointment, setting
     * last connection to current time.
     *
     * @param string $appointment_id   identifier of the appointment notified
     * @return Entity|Videoconference  updated videoconference notification entity
     * @throws DoesNotExistException
     */
    protected function update(int $appointment_id) {
        $now = new \DateTime();
        $timestamp = $now->getTimestamp();

        $videoconference = $this->videoconferenceMapper->findLastByAppointmentId($appointment_id);
        $videoconference->setLastConnection($timestamp);

        return $this->videoconferenceMapper->update($videoconference);
    }

    /**
     * Update the given videoconference notification, setting
     * last connection to current time.
     *
     * @param string $videoconference  videoconference notification to update
     * @return Entity|Videoconference  updated videoconference notification entity
     * @throws DoesNotExistException
     */
    protected function updateVideoconference(Videoconference $videoconference) {
        $now = new \DateTime();
        $timestamp = $now->getTimestamp();

        $videoconference->setLastConnection($timestamp);

        return $this->videoconferenceMapper->update($videoconference);
    }

    /**
     * Main point entry for a videoconference notification. If there exists an
     * active connection for given room code, it is updated. Otherwise, a new
     * videoconference notification is created.
     *
     * @param string $roomCode         room code of the videoconference that initiated the notification
     * @return Entity|Videoconference  active videoconference notification entity for the given room
     */
    public function connectionNotified(string $roomCode) {
        $appointments = $this->appointmentMapper->findByJitsiRoomCodeAndState($roomCode, array(Appointment::STATE_PENDANT, Appointment::STATE_ON_COURSE));
        if (!empty($appointments)) {
            foreach($appointments as $appointment) {
                if ($appointment->isDateToday()) {
                    try {
                        $videoconference = $this->videoconferenceMapper->findLastByAppointmentId($appointment->getId());

                        // If videoconference is not currently connected we DON'T update it,
                        // instead we must force to create a new videoconference notification
                        if (!empty($videoconference) && !$videoconference->isConnected()) {
                            $videoconference = null;
                        }
                    }
                    catch(DoesNotExistException $e) {}

                    if (!empty($videoconference)) {
                        $videoconference = $this->updateVideoconference($videoconference);
                    }
                    else {
                        $videoconference = $this->create($appointment->getId());
                    }

                    return ['result' => 'OK'];
                }
            }
        }

        return ['result' => 'KO'];
    }

    /**
     * Indicates whether exists a connection waiting to be answered by a moderator.
     *
     * @param Appointment $appointment  appointment for which check an existing waiting connection
     * @return bool       true if there is a connection waiting for the appointment; otherwise, false
     */
    public function isWaitingForModerator(Appointment $appointment): bool {
        if ($appointment->isDateToday()) {
            try {
                $videoconference = $this->videoconferenceMapper->findLastByAppointmentId($appointment->getId());
                if (!empty($videoconference)) {
                    return $videoconference->isConnected();
                }
            }
            catch(DoesNotExistException $e) {}
        }

        return false;
    }
}