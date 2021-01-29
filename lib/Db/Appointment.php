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

namespace OCA\VTramit\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Appointment extends Entity implements JsonSerializable {
    protected $externalId;  // Appointment ID
    protected $citizenId;   // Citizen ID number
    protected $department;
    protected $date;        // Appointment date and time (old)
    protected $comments;
    protected $name;
    protected $phone;
    protected $email;
    protected $topic;
    protected $assignedTo;
    protected $state;
    protected $stateDesc;
    protected $stateDate;
    protected $userId;

    protected $urlUploads;
    protected $urlDownloads;

    protected $sharedUrlUploads;
    protected $sharedUrlDownloads;
    protected $jitsiRoomCode;       // Ús intern
    protected $jitsiRoomInformador; // Al front és una icona per obrir l'enllaç
    protected $jitsiRoomCiutada;    // Ús intern

    protected $isWaitingForModerator = false;

    // Calculated properties for frontend
    protected $allowSendEmail = false;

    public const STATE_INITIALIZING = 0;
    public const STATE_CREATED      = 1;
    public const STATE_PENDANT         = 2;
    public const STATE_ON_COURSE    = 3;
    public const STATE_FINISHED     = 4;
    public const STATE_COMPLETED    = 5;
    public const STATE_CANCELLED    = 6;

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return [
            'id'                    => $this->id,
            'externalId'            => $this->externalId,
            'date'                  => $this->date,
            'citizenId'             => $this->citizenId,
            'department'            => $this->department,
            'comments'              => $this->comments,
            'name'                  => $this->name,
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'topic'                 => $this->topic,
            'assignedTo'            => $this->assignedTo,
            'state'                 => $this->state,
            'stateDesc'             => $this->stateDesc,
            'stateDate'             => $this->stateDate,
            'userId'                => $this->userId,
            'urlUploads'            => $this->urlUploads,
            'urlDownloads'          => $this->urlDownloads,
            'sharedUrlUploads'      => $this->sharedUrlUploads,
            'sharedUrlDownloads'    => $this->sharedUrlDownloads,
            'jitsiRoomCode'         => $this->jitsiRoomCode,
            'jitsiRoomInformador'   => $this->jitsiRoomInformador,
            'jitsiRoomCiutada'      => $this->jitsiRoomCiutada,
            'allowedForConference'  => $this->allowedForConference(),
            'allowStatePendant'     => $this->allowStatePendant(),
            'allowStateCompleted'   => $this->allowStateCompleted(),
            'allowStateFinished'    => $this->allowStateFinished(),
            'allowStateCancelled'   => $this->allowStateCancelled(),
            'allowSendEmail'        => $this->allowSendEmail,
            'isWaitingForModerator' => $this->isWaitingForModerator
        ];
    }

    public function getDateAsString($format = "d/m/Y H:i") {
        $dt = new \DateTime();
        $tz = new \DateTimeZone('Europe/Madrid');
        $dt->setTimestamp((int)$this->getDate());
        $dt->setTimezone($tz);
        return $dt->format($format);
    }

    public function setState($state) {
        if ($state != $this->state) {
            parent::setState($state);

            $dateState = new \DateTime();
            $this->setStateDate($dateState->getTimestamp());
        }
    }

    public function allowedForConference() {
        $allowedStateForConference = [self::STATE_CREATED, self::STATE_PENDANT, self::STATE_ON_COURSE, self::STATE_FINISHED, self::STATE_COMPLETED];
        return (!empty($this->jitsiRoomCode)
            && !empty($this->jitsiRoomInformador)
            && !empty($this->jitsiRoomCiutada)
            && in_array($this->getState(), $allowedStateForConference));
    }

    public function allowStateInitializing() {
        return empty($this->getState());
    }

    public function allowStateCreated() {
        $allowedForCreated = [self::STATE_INITIALIZING];
        return !empty($this->getId()) && in_array($this->getState(), $allowedForCreated);
    }

    public function allowStatePendant() {
        $allowedForPendant = [self::STATE_CREATED];
        return !empty($this->getId()) && in_array($this->getState(), $allowedForPendant) && $this->isDateToday();
    }

    public function allowStateOnCourse() {
        $allowedForOncourse = [self::STATE_PENDANT];
        return !empty($this->getId()) && in_array($this->getState(), $allowedForOncourse);
    }

    public function allowStateFinished() {
        $allowedForFinished = [self::STATE_PENDANT, self::STATE_ON_COURSE];
        return !empty($this->getId()) && in_array($this->getState(), $allowedForFinished);
    }

    public function allowStateCompleted() {
        $allowedForComplete = [self::STATE_PENDANT, self::STATE_ON_COURSE, self::STATE_FINISHED];
        return !empty($this->getId()) && in_array($this->getState(), $allowedForComplete);
    }

    public function allowStateCancelled() {
        $allowedForCancelled = [self::STATE_INITIALIZING, self::STATE_CREATED, self::STATE_PENDANT, self::STATE_ON_COURSE, self::STATE_FINISHED];
        return !empty($this->getId()) && in_array($this->getState(), $allowedForCancelled);
    }

    /**
     * Indicates if the appointment date is set to today
     *
     * @return bool  true if appointment is expected to be done today; otherwise, false
     */
    public function isDateToday() {
        if (empty($this->getDate())) {
            return false;
        }

        $date = new \DateTime();
        $match_date = new \DateTime();
        $match_date->setTimestamp($this->getDate());
        $interval = $date->diff($match_date);

        return $interval->days == 0;
    }
}
