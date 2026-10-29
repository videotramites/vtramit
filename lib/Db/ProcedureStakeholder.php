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

use OCA\VTramit\Helper\BeHelper;

class ProcedureStakeholder extends Entity implements JsonSerializable {
    protected $procedureId;
    protected $personalId;
    protected $name;
    protected $phone;
    protected $email;
    protected $stakeholderUploadsUrl;
    protected $stakeholderDownloadsUrl;
    protected $jitsiRoomStakeholderUrl;
    protected $attendanceChannel;
    protected $entryPage;
    protected $entryPassword;
    protected $deadlineDate;
    protected $isMain;
    protected $deckCardId;

    /** Computed fields **/
    protected ?string $fullJitsiRoomStakeholderUrl = null;

    public function __construct() {
        $this->addType('procedureId', Types::INTEGER);
        $this->addType('personalId', Types::STRING);
        $this->addType('name', Types::STRING);
        $this->addType('phone', Types::STRING);
        $this->addType('email', Types::STRING);
        $this->addType('stakeholderUploadsUrl', Types::STRING);
        $this->addType('stakeholderDownloadsUrl', Types::STRING);
        $this->addType('jitsiRoomStakeholderUrl', Types::STRING);
        $this->addType('attendanceChannel', Types::INTEGER);
        $this->addType('entryPage', Types::STRING);
        $this->addType('entryPassword', Types::STRING);
        $this->addType('deadlineDate', Types::STRING); // Types::DATETIME
        $this->addType('isMain', Types::BOOLEAN);
        $this->addType('deckCardId', Types::INTEGER);
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
     *     schema="ProcedureStakeholder",
     *     type="object",
     *     title="Procedure stakeholder model",
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
     *         property="personalId",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="phone",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="stakeholderUploadsUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="stakeholderDownloadsUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="jitsiRoomStakeholderUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="fullJitsiRoomStakeholderUrl",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="attendanceChannel",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="entryPage",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="entryPassword",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="deadlineDate",
     *         type="string",
     *         format="date-time"
     *     ),
     *     @OA\Property(
     *         property="isMain",
     *         type="boolean"
     *     ),
     *     @OA\Property(
     *         property="deckCardId",
     *         type="string"
     *     )
     * )
     */
    public function jsonSerialize(): array {
        $data = [
            'id'                           => (int) $this->id,
            'procedureId'                  => (int) $this->procedureId,
            'personalId'                   => $this->personalId,
            'name'                         => $this->name,
            'phone'                        => $this->phone,
            'email'                        => $this->email,
            'stakeholderUploadsUrl'        => $this->stakeholderUploadsUrl,
            'stakeholderDownloadsUrl'      => $this->stakeholderDownloadsUrl,
            'jitsiRoomStakeholderUrl'      => $this->jitsiRoomStakeholderUrl,
            'attendanceChannel'            => (int) $this->attendanceChannel,
            'entryPage'                    => $this->entryPage,
            'entryPassword'                => $this->entryPassword,
            'deadlineDate'                 => $this->deadlineDate,
            'isMain'                       => (bool) $this->isMain,
            'deckCardId'                   => $this->deckCardId,
        ];

        // Append computed field if set
        if ($this->fullJitsiRoomStakeholderUrl !== null) {
            $data['fullJitsiRoomStakeholderUrl'] = $this->fullJitsiRoomStakeholderUrl;
        }

        return $data;
    }

    public function jsonSerializeIncludePassword($encryptionKey = null): array {
        $data = $this->jsonSerialize();
        $data['entryPassword'] = $this->getEntryPassword($encryptionKey);
        return $data;
    }

    /**
     * @Override
     *
     * Note: overriden to force boolean result
     */
    public function getIsMain(): bool {
        return (bool) parent::getIsMain();
    }

    /**
     * Obtain the stakeholder password. If a encryption key is given, the returned
     * password is the decrypted one; otherwise it returns an encrypted password.
     *
     * @param string|null $encryptionKey
     * @return string
     */
    public function getEntryPassword($encryptionKey = null) {
        $password = parent::getEntryPassword();
        return empty($encryptionKey) || empty($password) ? $password: BeHelper::decrypt($password, $encryptionKey);
    }

    /**
     * Sets the full Jitsi room URL for stakeholder (computed, not persisted to DB).
     *
     * @param string|null $value
     *   The full Jitsi room URL or null if not applicable.
     */
    public function setFullJitsiRoomStakeholderUrl(?string $value): void {
        $this->fullJitsiRoomStakeholderUrl = $value;
    }

    /**
     * Gets the full Jitsi room URL for stakeholder (computed).
     *
     * @return string|null
     *   The full Jitsi room URL or null if not set.
     */
    public function getFullJitsiRoomStakeholderUrl(): ?string {
        return $this->fullJitsiRoomStakeholderUrl;
    }
}
