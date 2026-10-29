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

class ProcedureTypeStatus extends Entity implements JsonSerializable {
    protected $procedureTypeId;
    protected $statusName;
    protected $nextStatus;
    protected $isInitialStatus;
    protected $isFinalStatus;
    protected $allowUpload;
    protected $allowDownload;
    protected $allowSignature;
    protected $allowVideo;
    protected $notifyStakeholder;
    protected $notifyStaff;
    protected $procedureAnonymizationPeriod;
    protected $filesDeletionPeriod;
    protected $procedureDeletionPeriod;


    public function __construct() {
        $this->addType('procedureTypeId', Types::INTEGER);
        $this->addType('statusName', Types::STRING);
        $this->addType('nextStatus', Types::INTEGER);
        $this->addType('isInitialStatus', Types::INTEGER);
        $this->addType('isFinalStatus', Types::STRING);
        $this->addType('allowUpload', Types::BOOLEAN);
        $this->addType('allowDownload', Types::BOOLEAN);
        $this->addType('allowSignature', Types::BOOLEAN);
        $this->addType('allowVideo', Types::BOOLEAN);
        $this->addType('notifyStakeholder', Types::BOOLEAN);
        $this->addType('notifyStaff', Types::BOOLEAN);
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
     *     schema="ProcedureTypeStatus",
     *     type="object",
     *     title="Procedure type status model",
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
     *         property="statusName",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="nextStatus",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="isInitialStatus",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="isFinalStatus",
     *         type="boolean"
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
            'procedureTypeId'              => (int) $this->procedureTypeId,
            'statusName'                   => $this->statusName,
            'nextStatus'                   => $this->nextStatus,
            'isInitialStatus'              => (bool) $this->isInitialStatus,
            'isFinalStatus'                => (bool) $this->isFinalStatus,
            'allowUpload'                  => (bool) $this->allowUpload,
            'allowDownload'                => (bool) $this->allowDownload,
            'allowSignature'               => (bool) $this->allowSignature,
            'allowVideo'                   => (bool) $this->allowVideo,
            'notifyStakeholder'            => (bool) $this->notifyStakeholder,
            'notifyStaff'                  => (bool) $this->notifyStaff,
            'procedureAnonymizationPeriod' => (int) $this->procedureAnonymizationPeriod,
            'filesDeletionPeriod'          => (int) $this->filesDeletionPeriod,
            'procedureDeletionPeriod'      => (int) $this->procedureDeletionPeriod,
        ];
    }

    /**
     * Period to wait until anonymize the procedures of this type after it
     * reaches the current status.
     *
     * Use the value '-1' to inherit the period from the general procedure
     * type configuration.
     *
     * If you don't want to anoymize the procedure while in this state, use
     * value '0'.
     *
     * @param int $value hours until the prodecure is anonymized
     */
    public function setProcedureAnonymizationPeriod($value) {
        $this->procedureAnonymizationPeriod = $value < -1 ? -1 : $value;
    }

    /**
     * Period to wait until files of the procedure are deleted for any procedure
     * of this type after it reaches the current status.
     *
     * Use the value '-1' to inherit the period from the general procedure
     * type configuration.
     *
     * If you don't want to remove the procedure files while in this state, use
     * value '0'.
     *
     * @param int $value hours until the prodecure files are deleted
     */
    public function setFilesDeleteionPeriod($value) {
        $this->filesDeletionPeriod = $value < -1 ? -1 : $value;
    }

    /**
     * Period to wait until the procedure of this type is deleted after it
     * reaches the current status.
     *
     * Use the value '-1' to inherit the period from the general procedure
     * type configuration.
     *
     * If you don't want to remove the procedure while in this state, use
     * value '0'.
     *
     * @param int $value hours until the prodecure is deleted
     */
    public function setProcedureDeletionPeriod($value) {
        $this->procedureDeletionPeriod = $value < -1 ? -1 : $value;
    }
}
