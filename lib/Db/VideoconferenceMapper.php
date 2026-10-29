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

use DateTimeImmutable;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

use OCA\VTramit\Db\Tables;

class VideoconferenceMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::VIDEOCONFERENCE, Videoconference::class);
    }

    /**
     * Get the videoconference notification entity for the given id.
     *
     * @param int $id                  videoconference id
     * @return Entity|Videoconference  entity found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function find(int $id): Videoconference {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTablename())
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
            ->from($this->getTablename());

        return $this->findEntities($qb);
    }

    /**
     * Get all the videoconference notifications related to given procedure.
     *
     * @param int $procedureId  identifier of the related procedure
     * @return array            list of entities found
     */
    public function findByProcedureId(int $procedureId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTablename())
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * Get the last videoconference notification related to given procedure.
     *
     * @param int $procedureId         identifier of the related procedure
     * @return Entity|Videoconference  entity found
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findLastByProcedureId(int $procedureId): Videoconference {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTablename())
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->orderBy('last_connection', 'DESC')
            ->setMaxResults(1);

        return $this->findEntity($qb);
    }

    /**
     * Get videoconferences connected for procedures.
     *
     * @return array  entities found
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findConnectedProcedures($timezone): array {
        $now = new \DateTime("now", $timezone);
        $now->modify("-10 second");

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTablename())
            ->andWhere($qb->expr()->isNotNull('procedure_id'))
            ->andWhere($qb->expr()->gte('last_connection', $qb->createNamedParameter($now->getTimestamp(), IQueryBuilder::PARAM_INT)))
        ;

        return $this->findEntities($qb);
    }
}
