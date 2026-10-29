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

class ProcedureProtocolLog extends Entity implements JsonSerializable {
    protected $procedureId;
    protected $procTypeProtocolStepId;
    protected $procedureTypeGroupId;
    protected $userId;
    protected $createdAt;

    public function __construct() {
        $this->addType('procedureId', Types::INTEGER);
        $this->addType('procTypeProtocolStepId', Types::INTEGER);
        $this->addType('procedureTypeGroupId', Types::INTEGER);
        $this->addType('userId', Types::STRING);
        $this->addType('createdAt', Types::DATETIME);
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
     *     schema="ProcedureProtocolLog",
     *     type="object",
     *     title="Procedure protocol log model",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="procedureId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="procTypeProtocolStepId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="procedureTypeGroupId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="userId",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="createdAt",
     *         type="string",
     *         format="date-time"
     *     )
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                     => (int) $this->id,
            'procedureId'            => (int) $this->procedureId,
            'procTypeProtocolStepId' => (int) $this->procTypeProtocolStepId,
            'procedureTypeGroupId'   => (int) $this->procedureTypeGroupId,
            'userId'                 => $this->userId,
            'createdAt'              => $this->createdAt,
        ];
    }
}