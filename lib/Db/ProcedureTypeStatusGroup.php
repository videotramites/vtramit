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

class ProcedureTypeStatusGroup extends Entity implements JsonSerializable {
    protected $procedureTypeStatusId;
    protected $procedureTypeGroupId;
    protected $notifyStakeholderTemplateId;
    protected $notifyStaffTemplateId;
    protected $vcConfirmationForm;

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     *
     * @OA\Schema(
     *     schema="ProcedureTypeStatusGroup",
     *     type="object",
     *     title="Model of procedure type configuration by status and group",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="procedureTypeStatusId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="procedureTypeGroupId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="notifyStakeholderTemplateId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="notifyStaffTemplateId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="vcConfirmationForm",
     *         type="string"
     *     )
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                              => (int) $this->id,
            'procedureTypeStatusId'           => (int) $this->procedureTypeStatusId,
            'procedureTypeGroupId'            => (int) $this->procedureTypeGroupId,
            'notifyStakeholderTemplateId'     => (int) $this->notifyStakeholderTemplateId,
            'notifyStaffTemplateId'           => (int) $this->notifyStaffTemplateId,
            'vcConfirmationForm'              => $this->vcConfirmationForm,
        ];
    }

    /**
     * Set the procedure type group to which applies the configuration of this
     * entity.
     *
     * Use value '0' to apply to all groups.
     *
     * @param int $value  the identifier of the procedure type group that applies
     *                    to this entity
     */
    public function setProcedureTypeGroupId(int $value) {
        parent::setProcedureTypeGroupId($value);
    }
}
