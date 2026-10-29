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
 */

namespace OCA\VTramit\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;
use OCP\DB\Types;

use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\Constants;
use OCA\VTramit\Service\ConfigService;

class Procedure extends Entity implements JsonSerializable {
    protected $procedureTypeId;
    protected $externalId;
    protected $assignedGroupId;
    protected $assignedUserId;
    protected $topic;
    protected $comments;
    protected $staffUploadsUrl;
    protected $staffDownloadsUrl;
    protected $uploadsQty;
    protected $downloadsQty;
    protected $staffSignatureUploadsUrl;
    protected $staffSignatureDownloadsUrl;
    protected $jitsiRoomCode;
    protected $jitsiRoomStaffUrl;
    protected $statusId;
    protected $statusDate;
    protected $attendanceChannel;
    protected $appointmentDate;
    protected $deadlineDate;
    protected $foldersOwner;
    protected $modificationDate;
    protected $modificationUser;

    /** Computed fields **/
    protected ?string $fullJitsiRoomStaffUrl = null;
    protected ?string $staffPortalUrl = null;

    public function __construct() {
        $this->addType('procedureTypeId', Types::INTEGER);
        $this->addType('externalId', Types::STRING);
        $this->addType('assignedGroupId', Types::STRING);
        $this->addType('assignedUserId', Types::STRING);
        $this->addType('topic', Types::STRING);
        $this->addType('comments', Types::STRING);
        $this->addType('staffUploadsUrl', Types::STRING);
        $this->addType('staffDownloadsUrl', Types::STRING);
        $this->addType('uploadsQty', Types::INTEGER);
        $this->addType('downloadsQty', Types::INTEGER);
        $this->addType('staffSignatureUploadsUrl', Types::STRING);
        $this->addType('staffSignatureDownloadsUrl', Types::STRING);
        $this->addType('jitsiRoomCode', Types::STRING);
        $this->addType('jitsiRoomStaffUrl', Types::STRING);
        $this->addType('statusId', Types::INTEGER);
        $this->addType('statusDate', Types::STRING); // Types::DATETIME
        $this->addType('attendanceChannel', Types::INTEGER);
        $this->addType('appointmentDate', Types::STRING); // Types::DATETIME
        $this->addType('deadlineDate', Types::STRING); // Types::DATETIME
        $this->addType('foldersOwner', Types::STRING);
        $this->addType('modificationDate', Types::STRING); // Types::DATETIME
        $this->addType('modificationUser', Types::STRING);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     *
     * @OA\Schema(
     *     schema="Procedure",
     *     type="object",
     *     title="Procedure model",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="procedureTypeId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="externalId",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="assignedGroupId",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="assignedUserId",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="topic",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="comments",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="staffUploadsUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="staffDownloadsUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="uploadsQty",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="downloadsQty",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="staffSignatureUploadsUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="staffSignatureDownloadsUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="jitsiRoomCode",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="jitsiRoomStaffUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="fullJitsiRoomStaffUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="staffPortalUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="statusId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="statusDate",
     *         type="string",
     *         format="date-time"
     *     ),
     *     @OA\Property(
     *         property="attendanceChannel",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="appointmentDate",
     *         type="string",
     *         format="date-time"
     *     ),
     *     @OA\Property(
     *         property="deadlineDate",
     *         type="string",
     *         format="date-time"
     *     ),
     *     @OA\Property(
     *         property="foldersOwner",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="modificationDate",
     *         type="string",
     *         format="date-time"
     *     ),
     *     @OA\Property(
     *         property="modificationUser",
     *         type="string"
     *     )
     * )
     */
    public function jsonSerialize(): array {
        $data = [
            'id'                         => (int) $this->id,
            'procedureTypeId'            => (int) $this->procedureTypeId,
            'externalId'                 => $this->externalId,
            'assignedGroupId'            => $this->assignedGroupId,
            'assignedUserId'             => $this->assignedUserId,
            'topic'                      => $this->topic,
            'comments'                   => $this->comments,
            'staffUploadsUrl'            => $this->staffUploadsUrl,
            'staffDownloadsUrl'          => $this->staffDownloadsUrl,
            'uploadsQty'                 => $this->uploadsQty,
            'downloadsQty'               => $this->downloadsQty,
            'staffSignatureUploadsUrl'   => $this->staffSignatureUploadsUrl,
            'staffSignatureDownloadsUrl' => $this->staffSignatureDownloadsUrl,
            'jitsiRoomCode'              => $this->jitsiRoomCode,
            'jitsiRoomStaffUrl'          => $this->jitsiRoomStaffUrl,
            'statusId'                   => (int) $this->statusId,
            'statusDate'                 => $this->statusDate,
            'attendanceChannel'          => (int) $this->attendanceChannel,
            'appointmentDate'            => $this->appointmentDate,
            'deadlineDate'               => $this->deadlineDate,
            'foldersOwner'               => $this->foldersOwner,
            'modificationDate'           => $this->modificationDate,
            'modificationUser'           => $this->modificationUser,
        ];

        // Append computed fields if set
        if ($this->fullJitsiRoomStaffUrl !== null) {
            $data['fullJitsiRoomStaffUrl'] = $this->fullJitsiRoomStaffUrl;
        }
        if ($this->staffPortalUrl !== null) {
            $data['staffPortalUrl'] = $this->staffPortalUrl;
        }

        return $data;
    }

    public function setStatusDate($value) {
        if ($value instanceof \DateTime) {
            $value = $value->format(Constants::MYSQL_DATETIME_FORMAT);
        }

        parent::setStatusDate($value);
    }

    public function setAppointmentDate($value) {
        if ($value instanceof \DateTime) {
            $value = $value->format(Constants::MYSQL_DATETIME_FORMAT);
        }

        parent::setAppointmentDate($value);
    }

    public function getAppointmentDateTimestamp(\DateTimeZone $timezone) {
        if (empty($this->getAppointmentDate())) {
            return null;
        }

        $appointmentDate = new \DateTime($this->getAppointmentDate(), $timezone);
        return $appointmentDate->getTimestamp();
    }

    /**
     * Get the appointment date formatted as string.
     *
     * @param \DateTimeZone $timezone  the timezone to compare
     * @param string $format           the string to format the date
     * @return string                  the appointment date formatted
     */
    public function getFormattedAppointmentDate(\DateTimeZone $timezone, string $format = Constants::DEFAULT_DATETIME_FORMAT) {
        if (empty($this->getAppointmentDate())) {
            return '';
        }

        $appointmentDate = new \DateTime($this->getAppointmentDate(), $timezone);
        return $appointmentDate->format($format);
    }

    /**
     * Get the expiration timestamp of the appointment (24 hours later).
     *
     * @param \DateTimeZone $timezone
     *   The timezone to apply.
     *
     * @return int
     *   The expiration timestamp (Unix epoch). If the appointment date is not
     *   set or invalid, it defaults to now + 24 hours.
     */
    public function getAppointmentExpiryTimestamp(\DateTimeZone $timezone): int {
        if (!empty($this->getAppointmentDate())) {
            try {
                $appointmentDate = new \DateTime($this->getAppointmentDate(), $timezone);
                $expiryDate = $appointmentDate->modify('+24 hours');
                return $expiryDate->getTimestamp();

            } catch (\Exception $e) {
                // Fall back to now + 24 hours if the appointment date is invalid
            }
        }

        // Default fallback: now + 24 hours
        $now = new \DateTime('now', $timezone);
        $now->modify('+24 hours');
        return $now->getTimestamp();
    }

    public function setDeadlineDate($value) {
        if ($value instanceof \DateTime) {
            $value = $value->format(Constants::MYSQL_DATETIME_FORMAT);
        }

        parent::setDeadlineDate($value);
    }

    public function setModificationDate($value) {
        if ($value instanceof \DateTime) {
            $value = $value->format(Constants::MYSQL_DATETIME_FORMAT);
        }

        parent::setModificationDate($value);
    }

    /**
     * Indicates wether external ID is valid or not.
     *
     * @return bool  true if external ID has a valid format; otherwise, false
     */
    public function isValidExternalId(bool $emptyAllowed = true): bool {
        $externalId = $this->getExternalId();

        if ($emptyAllowed && empty($externalId)) {
            return true;
        }

        return preg_match("/^[A-Za-z0-9-_]+$/", $externalId) === 1;
    }

    /**
     * Indicates appointment date is older than today relative to given interval.
     *
     * See also:
     * https://www.php.net/manual/es/class.dateinterval.php
     *
     * @param string $interval   a relative time to now with which compare; for example,
     *                           if you set '-2 day', it will compare if it is older
     *                           than 2 days; set '0' to compare with now
     * @param \DateTimeZone $timezone  the timezone to compare
     * @return bool  true if appointment is older than relative interval from now; otherwise, false
     */
    public function isAppointmentDateOlderThan(string $interval, \DateTimeZone $timezone) {
        if (empty($this->getAppointmentDate())) {
            return false;
        }

        $appointmentDate = new \DateTime($this->getAppointmentDate(), $timezone);

        $now = new \DateTime("now", $timezone);
        $now->modify($interval);
        return $appointmentDate < $now;
    }

    /**
     * Indicates if the appointment date is set to today
     *
     * @return bool  true if appointment is expected to be done today; otherwise, false
     */
    public function isAppointmentDateToday(\DateTimeZone $timezone) {
        if (empty($this->getAppointmentDate())) {
            return false;
        }

        $appointmentDate = new \DateTime($this->getAppointmentDate(), $timezone);
        $now = new \DateTime("now", $timezone);
        $interval = $now->diff($appointmentDate);

        return $interval->days == 0;
    }

    /**
     * Sets the full Jitsi room URL for staff (computed, not persisted to DB).
     *
     * @param string|null $value
     *   The full Jitsi room URL or null if not applicable.
     */
    public function setFullJitsiRoomStaffUrl(?string $value): void {
        $this->fullJitsiRoomStaffUrl = $value;
    }

    /**
     * Gets the full Jitsi room URL for staff (computed).
     *
     * @return string|null
     *   The full Jitsi room URL or null if not set.
     */
    public function getFullJitsiRoomStaffUrl(): ?string {
        return $this->fullJitsiRoomStaffUrl;
    }

    /**
     * Sets the full portal URL for staff (computed, not persisted to DB).
     *
     * @param string|null $value
     *   The full portal URL or null if not applicable.
     */
    public function setStaffPortalUrl(?string $value): void {
        $this->staffPortalUrl = $value;
    }

    /**
     * Gets the full portal URL for staff (computed).
     *
     * @return string|null
     *   The full portal URL or null if not set.
     */
    public function getStaffPortalUrl(): ?string {
        return $this->staffPortalUrl;
    }
}
