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

class RgpdActivity extends Entity implements JsonSerializable {
    protected $procedureId;
    protected $procedureStakeholderId;
    protected $date;
    protected $labelList;
    protected $optionList;
    protected $userAgent;
    protected $ip;

    public function __construct() {
        $this->addType('procedureId', Types::INTEGER);
        $this->addType('procedureStakeholderId', Types::INTEGER);
        $this->addType('date', Types::DATETIME);
        $this->addType('labelList', Types::STRING);
        $this->addType('optionList', Types::STRING);
        $this->addType('userAgent', Types::STRING);
        $this->addType('ip', Types::STRING);
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
     *     schema="RgpdActivity",
     *     type="object",
     *     title="RGPD activity model",
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
     *         property="procedureStakeholderId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="date",
     *         type="string",
     *         format="date-time"
     *     ),
     *     @OA\Property(
     *         property="labelList",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="optionList",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="userAgent",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="ip",
     *         type="string"
     *     )
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                     => (int)$this->id,
            'procedureId'            => (int)$this->procedureId,
            'procedureStakeholderId' => (int)$this->procedureStakeholderId,
            'date'                   => $this->date,
            'labelList'              => empty($this->labelList) ? [] : json_decode($this->labelList),
            'optionList'             => empty($this->optionList) ? [] : json_decode($this->optionList),
            'userAgent'              => $this->userAgent,
            'ip'                     => $this->ip,
        ];
    }

    /**
     * Set the list of accepted labels.
     *
     * @param array $value  list of accepted labels
     */
    public function setLabelList($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        parent::setLabelList($value);
    }

    /**
     * Set the list of accepted options.
     *
     * @param array $value  list of accepted options
     */
    public function setOptionList($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        parent::setOptionList($value);
    }
}
