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

class ProcedureTypeGroup extends Entity implements JsonSerializable {
    protected $procedureTypeId;
    protected $group;
    protected $notifyStakeholderTemplateId;
    protected $notifyStaffTemplateId;
    protected $notifyStakeholderTemplate;
    protected $notifyStaffTemplate;
    protected $vcConfirmationForm;
    protected $allowCreate;
    protected $allowAssignGroup;
    protected $allowAssignUser;
    protected $bypassRequired;
    protected $defaultViewDesktopLeft;
    protected $defaultViewDesktopRight;
    protected $defaultViewMobile;

    public function __construct() {
        $this->addType('procedureTypeId', Types::INTEGER);
        $this->addType('group', Types::STRING);
        $this->addType('notifyStakeholderTemplateId', Types::INTEGER);
        $this->addType('notifyStaffTemplateId', Types::INTEGER);
        $this->addType('notifyStakeholderTemplate', Types::STRING);
        $this->addType('notifyStaffTemplate', Types::STRING);
        $this->addType('vcConfirmationForm', Types::STRING);
        $this->addType('allowCreate', Types::BOOLEAN);
        $this->addType('defaultViewDesktopLeft', Types::STRING);
        $this->addType('defaultViewDesktopRight', Types::STRING);
        $this->addType('defaultViewMobile', Types::STRING);
        $this->addType('allowAssignGroup', Types::BOOLEAN);
        $this->addType('allowAssignUser', Types::BOOLEAN);
        $this->addType('bypassRequired', Types::BOOLEAN);
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
     *     schema="ProcedureTypeGroup",
     *     type="object",
     *     title="Procedure type group model",
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
     *         property="group",
     *         type="string"
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
     *         property="notifyStakeholderTemplate",
     *         type="array",
     *         @OA\Items(
     *           type="string"
     *         )
     *     ),
     *     @OA\Property(
     *         property="notifyStaffTemplate",
     *         type="array",
     *         @OA\Items(
     *           type="string"
     *         )
     *     ),
     *     @OA\Property(
     *         property="vcConfirmationForm",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="allowCreate",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="allowAssignGroup",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="allowAssignUser",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="bypassRequired",
     *         type="boolean"
     *     ),
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                              => (int) $this->id,
            'procedureTypeId'                 => (int) $this->procedureTypeId,
            'group'                           => $this->group,
            'notifyStakeholderTemplateId'     => (int) $this->notifyStakeholderTemplateId,
            'notifyStaffTemplateId'           => (int) $this->notifyStaffTemplateId,
            'notifyStakeholderTemplate'       => empty($this->notifyStakeholderTemplate) ? [] : array_map('intval', explode(',', $this->notifyStakeholderTemplate)),
            'notifyStaffTemplate'             => empty($this->notifyStaffTemplate) ? [] : array_map('intval', explode(',', $this->notifyStaffTemplate)),
            'vcConfirmationForm'              => $this->vcConfirmationForm,
            'allowCreate'                     => (bool) $this->allowCreate,
            'allowAssignGroup'                => (bool) $this->allowAssignGroup,
            'allowAssignUser'                 => (bool) $this->allowAssignUser,
            'bypassRequired'                  => (bool) $this->bypassRequired,
            'defaultViewDesktopLeft'          => $this->defaultViewDesktopLeft,
            'defaultViewDesktopRight'         => $this->defaultViewDesktopRight,
            'defaultViewMobile'               => $this->defaultViewMobile,
        ];
    }

    /**
     * Set the list of stakeholder templates.
     *
     * @param array $value  list of stakeholder templates
     */
    public function setNotifyStakeholderTemplate($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        parent::setNotifyStakeholderTemplate($value);
    }

    /**
     * Set the list of stakeholder templates.
     *
     * @param array $value  list of stakeholder templates
     */
    public function setNotifyStakeholderTemplateList($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $this->setNotifyStakeholderTemplate($value);
    }

    /**
     * Obtain the list of stakeholder templates.
     *
     * @return array $value  list of stakeholder templates
     */
    public function getNotifyStakeholderTemplateList(): array {
        if (!empty($this->getNotifyStakeholderTemplate())) {
            return array_map('intval', (explode(',', $this->getNotifyStakeholderTemplate())));
        }

        return [];
    }

    /**
     * Set the list of staff templates.
     *
     * @param array $value  list of staff templates
     */
    public function setNotifyStaffTemplate($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        parent::setNotifyStaffTemplate($value);
    }

    /**
     * Set the list of staff templates.
     *
     * @param array $value  list of staff templates
     */
    public function setNotifyStaffTemplateList($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $this->setNotifyStaffTemplate($value);
    }

    /**
     * Obtain the list of staff templates.
     *
     * @return array $value  list of staff templates
     */
    public function getNotifyStaffTemplateList(): array {
        if (!empty($this->getNotifyStaffTemplate())) {
            return array_map('intval', (explode(',', $this->getNotifyStaffTemplate())));
        }

        return [];
    }

}
