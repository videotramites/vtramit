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

class Signature extends Entity implements JsonSerializable {

    protected $signatureTypeId;
    protected $procedureId;
    protected $url;
    protected $hash;
    protected $status;
    protected $statusDate;
    protected $davpathUpload;
    protected $davpathDownload;
    protected $signatureMethods;
    protected $additionalInfo;

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        $json = [
            'id'                    => (int) $this->id,
            'signatureTypeId'       => (int) $this->signatureTypeId,
            'procedureId'           => (int) $this->procedureId,
            'url'                   => $this->url,
            'hash'                  => $this->hash,
            'status'                => (int) $this->status,
            'statusDate'            => $this->statusDate,
            'davpathUpload'         => $this->davpathUpload,
            'davpathDownload'       => $this->davpathDownload,
            'signatureMethod'       => empty($this->signatureMethods) ? '' : json_decode($this->signatureMethods, true),
        ];

        if (!empty($this->additionalInfo)) {
            $additionalInfo = json_decode($this->additionalInfo, true);
            $json = array_merge($json, $additionalInfo);
        }

        return $json;
    }

    public function setStatus($status) {
        if ($status != $this->status) {
            parent::setStatus($status);

            $dateStatus = new \DateTime();
            $this->setStatusDate($dateStatus->getTimestamp());
        }
    }

    /**
     * Allowed signature methods in JSON format. If array given, it is
     * encoded to JSON.
     */
    public function setSignatureMethods($signatureMethods) {
        if (is_array($signatureMethods)) {
            parent::setSignatureMethods(json_encode($signatureMethods));
        }
        else {
            parent::setSignatureMethods($signatureMethods);
        }
    }

    /**
     * Additional information reuired for the signature process. If array given, it is
     * encoded to JSON.
     */
    public function setAdditionalInfo($additionalInfo) {
        if (is_array($additionalInfo)) {
            parent::setAdditionalInfo(json_encode($additionalInfo));
        }
        else {
            parent::setAdditionalInfo($additionalInfo);
        }
    }

    public function isTimeframeExpired($timeframe) {
        // If set to 0, there is no expiry set
        if ($timeframe == 0) {
            return false;
        }

        $oldLimit = strtotime("-$timeframe hours");

        return ($this->getStatusDate() < $oldLimit);
    }
}
