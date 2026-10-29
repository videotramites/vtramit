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

class ProcedureType extends Entity implements JsonSerializable {
    protected $name;
    protected $rgpdOptions;
    protected $allowUpload;
    protected $uploadsFolder;
    protected $allowDownload;
    protected $downloadsFolder;
    protected $allowSignature;
    protected $signatureUploadsFolder;
    protected $signatureDownloadsFolder;
    protected $signatureTimeframeSign;
    protected $signatureTimeframeDownload;
    protected $signatureLimit;
    protected $allowVideo;
    protected $jitsiStakeholderUrl;
    //protected $jitsiStakeholderJwt;
    protected $jitsiStakeholderJwtSecret;
    protected $jitsiStakeholderJwtPayload;
    protected $jitsiStaffUrl;
    //protected $jitsiStaffJwt;
    protected $jitsiStaffJwtSecret;
    protected $jitsiStaffJwtPayload;
    protected $allowedAttendanceChannels;
    protected $notifyStakeholder;
    protected $notifyStaff;
    protected $requireAssignedGroup;
    protected $requireExternalId;
    protected $requireAppointmentDate;
    protected $requireDeadlineDate;
    protected $requireStakeholderPid;
    protected $requireStakeholderPhone;
    protected $requireStakeholderEmail;
    protected $requireAttendanceChannel;
    protected $attendantPortalProperties;
    protected $deckIntegration;
    protected $deckBoard;
    protected $deckStack;
    protected $procedureAnonymizationPeriod;
    protected $filesDeletionPeriod;
    protected $procedureDeletionPeriod;

    public function __construct() {
        $this->addType('name', Types::STRING);
        $this->addType('uploadsFolder', Types::STRING);
        $this->addType('downloadsFolder', Types::STRING);
        $this->addType('signatureUploadsFolder', Types::STRING);
        $this->addType('signatureDownloadsFolder', Types::STRING);
        $this->addType('signatureTimeframeSign', Types::INTEGER);
        $this->addType('signatureTimeframeDownload', Types::INTEGER);
        $this->addType('jitsiStakeholderUrl', Types::STRING);
        $this->addType('jitsiStakeholderJwtSecret', Types::STRING);
        $this->addType('jitsiStakeholderJwtPayload', Types::STRING);
        $this->addType('jitsiStaffUrl', Types::STRING);
        $this->addType('jitsiStaffJwtSecret', Types::STRING);
        $this->addType('jitsiStaffJwtPayload', Types::STRING);
        $this->addType('allowedAttendanceChannels', Types::STRING);
        $this->addType('rgpdOptions', Types::STRING);
        $this->addType('allowUpload', Types::BOOLEAN);
        $this->addType('allowDownload', Types::BOOLEAN);
        $this->addType('allowSignature', Types::BOOLEAN);
        $this->addType('allowVideo', Types::BOOLEAN);
        $this->addType('signatureLimit', Types::INTEGER);
        $this->addType('notifyStakeholder', Types::BOOLEAN);
        $this->addType('notifyStaff', Types::BOOLEAN);
        $this->addType('requireAssignedGroup', Types::BOOLEAN);
        $this->addType('requireExternalId', Types::BOOLEAN);
        $this->addType('requireAppointmentDate', Types::BOOLEAN);
        $this->addType('requireDeadlineDate', Types::BOOLEAN);
        $this->addType('requireStakeholderPid', Types::BOOLEAN);
        $this->addType('requireStakeholderPhone', Types::BOOLEAN);
        $this->addType('requireStakeholderEmail', Types::BOOLEAN);
        $this->addType('requireStakeholderEmail', Types::BOOLEAN);
        $this->addType('requireAttendanceChannel', Types::BOOLEAN);
        $this->addType('attendantPortalProperties', Types::JSON);
        $this->addType('deckIntegration', Types::BOOLEAN);
        $this->addType('deckBoard', Types::INTEGER);
        $this->addType('deckStack', Types::INTEGER);
        $this->addType('procedureAnonymizationPeriod', Types::INTEGER);
        $this->addType('filesDeletionPeriod', Types::INTEGER);
        $this->addType('procedureDeletionPeriod', Types::INTEGER);
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
     *     schema="ProcedureType",
     *     type="object",
     *     title="Procedure type model",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="uploadsFolder",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="downloadsFolder",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="signatureUploadsFolder",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="signatureDownloadsFolder",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="signatureTimeframeSign",
     *         type="int32"
     *     ),
     *     @OA\Property(
     *         property="signatureTimeframeDownload",
     *         type="int32"
     *     ),
     *     @OA\Property(
     *         property="jitsiStakeholderUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="jitsiStakeholderJwtSecret",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="jitsiStakeholderJwtPayload",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="jitsiStaffUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="jitsiStaffJwtSecret",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="jitsiStaffJwtPayload",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="allowedAttendanceChannels",
     *         type="array",
     *         @OA\Items(
     *           type="string"
     *         )
     *     ),
     *     @OA\Property(
     *         property="rgpdOptions",
     *         type="array",
     *         @OA\Items(
     *           type="string"
     *         )
     *     ),
     *     @OA\Property(
     *         property="allowUpload",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="allowDownload",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="allowSignature",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="signatureLimit",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="allowVideo",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="notifyStakeholder",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="notifyStaff",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="requireAssignedGroup",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="requireExternalId",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="requireAppointmentDate",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="requireDeadlineDate",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="requireStakeholderPid",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="requireStakeholderPhone",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="requireStakeholderEmail",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="requireAttendanceChannel",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="attendantPortalProperties",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="deckIntegration",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="deckBoard",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="deckStack",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="procedureAnonymizationPeriod",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="filesDeletionPeriod",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="procedureDeletionPeriod",
     *         type="integer",
     *         format="int32"
     *     )
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                           => (int) $this->id,
            'name'                         => $this->name,
            'uploadsFolder'                => $this->uploadsFolder,
            'downloadsFolder'              => $this->downloadsFolder,
            'signatureUploadsFolder'       => $this->signatureUploadsFolder,
            'signatureDownloadsFolder'     => $this->signatureDownloadsFolder,
            'signatureTimeframeSign'       => $this->signatureTimeframeSign,
            'signatureTimeframeDownload'   => $this->signatureTimeframeDownload,
            'jitsiStakeholderUrl'          => $this->jitsiStakeholderUrl,
            'jitsiStakeholderJwtSecret'    => $this->jitsiStakeholderJwtSecret,
            'jitsiStakeholderJwtPayload'   => $this->jitsiStakeholderJwtPayload,
            'jitsiStaffUrl'                => $this->jitsiStaffUrl,
            'jitsiStaffJwtSecret'          => $this->jitsiStaffJwtSecret,
            'jitsiStaffJwtPayload'         => $this->jitsiStaffJwtPayload,
            'allowedAttendanceChannels'    => empty($this->allowedAttendanceChannels) ? [] : array_map('intval', explode(',', $this->allowedAttendanceChannels)),
            'rgpdOptions'                  => empty($this->rgpdOptions) ? [] : array_map('intval', explode(',', $this->rgpdOptions)),
            'allowUpload'                  => (bool) $this->allowUpload,
            'allowDownload'                => (bool) $this->allowDownload,
            'allowSignature'               => (bool) $this->allowSignature,
            'signatureLimit'               => (int) $this->signatureLimit,
            'allowVideo'                   => (bool) $this->allowVideo,
            'notifyStakeholder'            => (bool) $this->notifyStakeholder,
            'notifyStaff'                  => (bool) $this->notifyStaff,
            'requireAssignedGroup'         => (bool) $this->requireAssignedGroup,
            'requireExternalId'            => (bool) $this->requireExternalId,
            'requireAppointmentDate'       => (bool) $this->requireAppointmentDate,
            'requireDeadlineDate'          => (bool) $this->requireDeadlineDate,
            'requireStakeholderPid'        => (bool) $this->requireStakeholderPid,
            'requireStakeholderPhone'      => (bool) $this->requireStakeholderPhone,
            'requireStakeholderEmail'      => (bool) $this->requireStakeholderEmail,
            'requireAttendanceChannel'     => (bool) $this->requireAttendanceChannel,
            'attendantPortalProperties'    => $this->attendantPortalProperties ?? [],
            'deckIntegration'              => (bool) $this->deckIntegration,
            'deckBoard'                    => (int) $this->deckBoard,
            'deckStack'                    => (int) $this->deckStack,
            'procedureAnonymizationPeriod' => (int) $this->procedureAnonymizationPeriod,
            'filesDeletionPeriod'          => (int) $this->filesDeletionPeriod,
            'procedureDeletionPeriod'      => (int) $this->procedureDeletionPeriod,
        ];
    }

    /**
     * Set the list of attendant portal properties.
     *
     * @param array $value  list of allowed attendace channels
     */
    public function setAllowedAttendanceChannels($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        parent::setAllowedAttendanceChannels($value);
    }

    /**
     * Set the list of allowed attendace channels.
     *
     * @param array $value  list of allowed attendace channels
     */
    public function setAllowedAttendanceChannelList($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $this->setAllowedAttendanceChannels($value);
    }

    /**
     * Obtain the list of allowed attendace channels.
     *
     * @return array  list of allowed attendace channels
     */
    public function getAllowedAttendanceChannelList(): array {
        if (!empty($this->getAllowedAttendanceChannels())) {
            return array_map('intval', (explode(',', $this->getAllowedAttendanceChannels())));
        }

        return [];
    }

    /**
     * Set the list of RGPD options.
     *
     * @param array $value  list of RGPD options
     */
    public function setRgpdOptions($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        parent::setRgpdOptions($value);
    }

    /**
     * Set the list of RGPD options.
     *
     * @param array $value  list of RGPD options
     */
    public function setRgpdOptionsList($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $this->setRgpdOptions($value);
    }

    /**
     * Obtain the list of RGPD options.
     *
     * @return array  list of RGPD options
     */
    public function getRgpdOptionsList(): array {
        if (!empty($this->getRgpdOptions())) {
            return array_map('intval', (explode(',', $this->getRgpdOptions())));
        }

        return [];
    }

    /**
     * Period to wait until anonymize the procedures of this type.
     *
     * If you don't want to anoymize the procedure, use value '0'.
     *
     * @param int $value hours until the prodecure is anonymized
     */
    public function setProcedureAnonymizationPeriod($value) {
        parent::setProcedureAnonymizationPeriod($value < 0 ? 0 : $value);
    }

    /**
     * Period to wait until files of the procedure are deleted for any procedure
     * of this type.
     *
     * If you don't want to remove the procedure files, use value '0'.
     *
     * @param int $value hours until the prodecure files are deleted
     */
    public function setFilesDeletionPeriod($value) {
        parent::setFilesDeletionPeriod($value < 0 ? 0 : $value);
    }

    /**
     * Period to wait until the procedure of this type is deleted.
     *
     * If you don't want to remove the procedure, use value '0'.
     *
     * @param int $value hours until the prodecure is deleted
     */
    public function setProcedureDeletionPeriod($value) {
        parent::setProcedureDeletionPeriod($value < 0 ? 0 : $value);
    }
}
