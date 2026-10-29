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


class SignatureButton extends Entity implements JsonSerializable {
    protected $procedureTypeId;
    protected $group;
    protected $signatureTypeId;
    protected $mailEnabled;
    protected $mailTemplateId;
    protected $messageEnabled;
    protected $messageTemplateId;
    protected $label;

    public function __construct() {
      $this->addType('procedureTypeId', Types::INTEGER);
      $this->addType('group', Types::STRING);
      $this->addType('signatureTypeId', Types::INTEGER);
      $this->addType('mailEnabled', Types::BOOLEAN);
      $this->addType('mailTemplateId', Types::INTEGER);
      $this->addType('messageEnabled', Types::BOOLEAN);
      $this->addType('messageTemplateId', Types::INTEGER);
      $this->addType('label', Types::STRING);
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
     *     schema="SignatureButton",
     *     type="object",
     *     title="Signature Button model",
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
     *         type="string",
     *     ),
     *     @OA\Property(
     *         property="signatureTypeId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="mailEnabled",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="mailTemplateId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="messageEnabled",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="messageTemplateId",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="label",
     *         type="string"
     *     ),
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                   => (int) $this->id,
            'procedureTypeId'      => (int) $this->procedureTypeId,
            'group'                => $this->group,
            'signatureTypeId'      => (int) $this->signatureTypeId,
            'mailEnabled'          => (bool) $this->mailEnabled,
            'mailTemplateId'       => (int) $this->mailTemplateId,
            'messageEnabled'       => (bool) $this->messageEnabled,
            'messageTemplateId'    => (int) $this->messageTemplateId,
            'label'                => empty($this->label) ? [] : json_decode($this->label, true),
        ];
    }

   /**
     * Set the list of button labels.
     *
     * @param array $value  list of button labels
     */
    public function setLabel($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        parent::setLabel($value);
    }

    /**
     * Set the list of button labels.
     *
     * @param array $value  list of button labels
     */
    public function setLabelList($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $this->setLabel($value);
    }

    /**
     * Obtain the list of button labels.
     *
     * @return array  list of button labels
     */
    public function getLabelList(): array {
        return empty($this->getLabel())
            ? []
            : json_decode($this->getLabel());
    }
}
