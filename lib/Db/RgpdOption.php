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

class RgpdOption extends Entity implements JsonSerializable {
    protected $name;
    protected $type;
    protected $label;
    protected $required;

    public function __construct() {
        $this->addType('name', Types::STRING);
        $this->addType('type', Types::STRING);
        $this->addType('label', Types::STRING);
        $this->addType('required', Types::BOOLEAN);
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
     *     schema="RgpdOption",
     *     type="object",
     *     title="RGPD option model",
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
     *         property="type",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="label",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="required",
     *         type="boolean"
     *     ),
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'       => (int) $this->id,
            'name'     => $this->name,
            'type'     => $this -> type,
            'label'    => empty($this->label) ? [] : json_decode($this->label),
            'required' => (bool) $this->required,
        ];
    }

    /**
     * Set the option type. It must be one of the following types or 'markup' is
     * selected instead:
     *   - checkbox
     *   - markup
     *
     * @param string $value  option type
     */
    public function setType($value) {
        $value = strtolower($value);
        if (!in_array($value, ['checkbox', 'markup'])) {
            $value = 'markup';
        }

        parent::setType($value);
    }

    /**
     * Set the list of allowed attendace channels.
     *
     * @param array $value  list of allowed attendace channels
     */
    public function setLabel($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        parent::setLabel($value);
    }

    /**
     * Set the list of allowed attendace channels.
     *
     * @param array $value  list of allowed attendace channels
     */
    public function setLabelList($value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $this->setLabel($value);
    }

    /**
     * Obtain the list of allowed attendace channels.
     *
     * @return array  list of allowed attendace channels
     */
    public function getLabelList(): array {
        if (!empty($this->getLabel())) {
            return empty($this->getLabel()) ? [] : json_decode($this->getLabel());
        }

        return [];
    }
}
