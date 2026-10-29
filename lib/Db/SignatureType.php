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


class SignatureType extends Entity implements JsonSerializable {
    protected $name;
    protected $requestLimit;
    protected $filesPerRequest;
    protected $fileType;
    protected $format;
    protected $method;
    protected $certifiedAuthority;
    protected $certifiedAuthorityProxy;
    protected $additionalInfo;
    protected $phoneRequired;
    protected $mailEnabled;     // DEPRECATED
    protected $mailTemplateId;  // DEPRECATED

    public function __construct() {
      $this->addType('name', Types::STRING);
      $this->addType('requestLimit', Types::INTEGER);
      $this->addType('filesPerRequest', Types::INTEGER);
      $this->addType('fileType', Types::STRING);
      $this->addType('format', Types::STRING);
      $this->addType('method', Types::STRING);
      $this->addType('certifiedAuthority', Types::STRING);
      $this->addType('certifiedAuthorityProxy', Types::STRING);
      $this->addType('additionalInfo', Types::STRING);
      $this->addType('phoneRequired', Types::BOOLEAN);
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
     *     schema="SignatureType",
     *     type="object",
     *     title="Signature Type model",
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
     *         property="requestLimit",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="filesPerRequest",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="fileType",
     *         type="array",
     *         @OA\Items(
     *           type="string"
     *         )
     *     ),
     *     @OA\Property(
     *         property="format",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="method",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="certifiedAuthority",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="certifiedAuthorityProxy",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="additionalInfo",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="phoneRequired",
     *         type="boolean"
     *     )
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                      => (int) $this->id,
            'name'                    => $this->name,
            'requestLimit'            => (int) $this->requestLimit,
            'filesPerRequest'         => (int) $this->filesPerRequest,
            'fileType'                => empty($this->fileType) ? [] : array_map('strval', explode(',', $this->fileType)),
            'format'                  => $this->format,
            'method'                  => $this->method,
            'certifiedAuthority'      => $this->certifiedAuthority,
            'certifiedAuthorityProxy' => $this->certifiedAuthorityProxy,
            'certifiedAuthorityProxy' => $this->certifiedAuthorityProxy,
            'additionalInfo'          => $this->additionalInfo,
            'phoneRequired'           => (bool) $this->phoneRequired,
        ];
    }

    /**
     * Set the list of file types.
     *
     * @param array $value  list of file types
     */
    public function setFileType($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        parent::setFileType($value);
    }

    /**
     * Set the list of file types.
     *
     * @param array $value  list of file types
     */
    public function setAllowedAttendanceChannelList($value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $this->setFileType($value);
    }

    /**
     * Obtain the list of file types.
     *
     * @return array  list of file types
     */
    public function getAllowedAttendanceChannelList(): array {
        if (!empty($this->getLabel())) {
            return array_map('intval', (explode(',', $this->getFileType())));
        }

        return [];
    }
}
