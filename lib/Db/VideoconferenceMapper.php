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

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class VideoconferenceMapper extends QBMapper {
    private const TABLE = 'vtramit_videoconference';

    public function __construct(IDBConnection $db) {
        parent::__construct($db, self::TABLE, Videoconference::class);
    }

    /**
     * Get the videoconference notification entity for the given id.
     *
     * @param int $id                  videoconference id
     * @return Entity|Videoconference  entity found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws DoesNotExistException
     */
    public function find(int $id): Videoconference {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(self::TABLE)
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * Get all the videoconference notification entities.
     *
     * @return array  list of entities found
     */
    public function findAll(): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(self::TABLE);

        return $this->findEntities($qb);
    }

    /**
     * Get all the videoconference notifications related to given appointment.
     *
     * @param int $appointmentId  identifier of the related appointment
     * @return array              list of entities found
     */
    public function findByAppointmentId(int $appointmentId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(self::TABLE)
            ->where($qb->expr()->eq('appointment_id', $qb->createNamedParameter($appointmentId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * Get the last videoconference notification related to given appointment.
     *
     * @param int $appointmentId       identifier of the related appointment
     * @return Entity|Videoconference  entity found
     * @throws DoesNotExistException
     */
    public function findLastByAppointmentId(int $appointmentId): Videoconference {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(self::TABLE)
            ->where($qb->expr()->eq('appointment_id', $qb->createNamedParameter($appointmentId, IQueryBuilder::PARAM_INT)))
            ->orderBy('last_connection', 'DESC')
            ->setMaxResults(1);

        return $this->findEntity($qb);
    }
}
