<?php
/**
 * @copyright Copyright (c) 2020-2021 Ajuntament de Barcelona
 * 
 * @author Daniel Tamajon <daniel@floss.cat>
 * @author Kenneth Peiruza <kenneth@floss.cat>
 * @author Letizia Benítez <letizia@floss.cat>
 * @author Marta González <gonzalez.marta@gmail.com>
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

class Videoconference extends Entity implements JsonSerializable {
    protected $appointmentId;
    protected $firstConnection;
    protected $lastConnection;

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
            'id'              => $this->id,
            'appointmentId'   => $this->appointmentId,
            'firstConnection' => $this->firstConnection,
            'lastConnection'  => $this->lastConnection
        ];
    }

    /**
     * Indicates whether the non-moderator user is waiting for the moderator.
     * It is considered that a user is waiting if there is less than 1 minute since last
     * alive notification.
     *
     * @return bool  true if the non-moderator is waiting for the moderator; otherwise, false
     */
    public function isConnected(): bool {
        if (empty($this->getLastConnection())) {
            return false;
        }

        return ($this->getLastConnection() > strtotime("-10 seconds"));
    }
}
