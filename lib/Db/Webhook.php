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


class Webhook extends Entity implements JsonSerializable {
    protected $name;
    protected $endpoint;
    protected $authType;
    protected $authTypeData;
    protected $event;

    /**
    *   Operations
    */
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const CREATE_PROCEDURE = 'create-procedure';
    public const UPDATE_PROCEDURE = 'update-procedure';
    public const DELETE_PROCEDURE = 'delete-procedure';
    public const CREATE_STAKEHOLDER = 'create-stakeholder';
    public const UPDATE_STAKEHOLDER = 'update-stakeholder';
    public const DELETE_STAKEHOLDER = 'delete-stakeholder';

    /**
     * Auth Types
     */
    public const NO_AUTH = 'no-auth';
    public const BASIC_AUTH = 'basic-auth';
    public const TOKEN = 'token';

  public function __construct() {
      $this->addType('name', Types::STRING);
      $this->addType('endpoint', Types::STRING);
      $this->addType('authType', Types::STRING);
      $this->addType('authTypeData', Types::STRING);
      $this->addType('event', Types::STRING);
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
     *     schema="Webhook",
     *     type="object",
     *     title="Webhook model",
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
     *         property="endpoint",
     *         type="string",
     *     ),
     *     @OA\Property(
     *         property="auth_type",
     *         type="string",
     *     ),
     *     @OA\Property(
     *         property="auth_type_data",
     *         type="array",
     *         @OA\Items(
     *           type="string"
     *         )
     *     ),
     *     @OA\Property(
     *         property="event",
     *         type="array",
     *         @OA\Items(
     *           type="string"
     *         )
     *     ),
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                        => (int) $this->id,
            'name'                      => $this->name,
            'endpoint'                  => $this->endpoint,
            'authType'                  => $this->authType,
            'authTypeData'              => empty($this->authTypeData) ? [] : json_decode($this->authTypeData, true),
            'event'                     => empty($this->event) ? [] : array_map('strval', explode(',', $this->event)),
        ];
    }

    /**
     * Set the list of auth data.
     *
     * @param array $value  list of auth data
     */
    public function setAuthTypeData($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        parent::setAuthTypeData($value);
    }

    /**
     * Set the list of auth data.
     *
     * @param array $value  list of auth data
     */
    public function setAuthTypeDataList($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $this->setAuthTypeData($value);
    }

    /**
     * Obtain the list of auth data.
     *
     * @return array  list of auth data
     */
    public function getAuthTypeDataList(): array {
        if (!empty($this->getAuthTypeData())) {
            return empty($this->getAuthTypeData()) ? [] : json_decode($this->getAuthTypeData(), true);
        }

        return [];
    }

    /**
     * Set the events.
     *
     * @param array $value  events
     */
    public function setEvent($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        parent::setEvent($value);
    }

}
