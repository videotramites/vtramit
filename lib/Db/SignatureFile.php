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

class SignatureFile extends Entity implements JsonSerializable {

    protected $signatureId;
    protected $fileId;
    protected $filepath;
    protected $davpathUpload;
    protected $davpathDownload;
    protected $sharedUrl;
    protected $deleted = 0;

    public function __construct() {
        $this->addType('signatureId', Types::INTEGER);
        $this->addType('fileId', Types::INTEGER);
        $this->addType('filepath', Types::STRING);
        $this->addType('davpathUpload', Types::STRING);
        $this->addType('davpathDownload', Types::STRING);
        $this->addType('sharedUrl', Types::STRING);
        $this->addType('deleted', Types::INTEGER);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        return [
            'id'                    => $this->id,
            'signatureId'           => $this->signatureId,
            'fileId'                => $this->fileId,
            'filepath'              => $this->filepath,
            'davpathUpload'         => $this->davpathUpload,
            'davpathDownload'       => $this->davpathDownload,
            'sharedUrl'             => $this->sharedUrl,
            'deleted'               => (int) $this->deleted,
        ];
    }

    public function isDeleted(): bool {
        return (bool) $this->deleted;
    }

    public function setDeleted(bool $deleted): void {
        parent::setDeleted($deleted ? 1 : 0);
    }

}
