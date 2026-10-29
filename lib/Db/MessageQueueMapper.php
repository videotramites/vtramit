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

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCA\VTramit\Db\Tables;

class MessageQueueMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::MESSAGE_QUEUE, MessageQueue::class, 'message queue');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return Entity|MessageQueue
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): MessageQueue {
        return parent::find($id);
    }

    /**
     * Find all the messages in message queue for given procedure identifier.
     *
     * @param int $procedureId    procedure indentifier to filter for
     * @return array              list of found entities
     */
    public function findByProcedureId(int $procedureId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::MESSAGE_QUEUE)
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * Obtain the next message in the queue to be sent.
     *
     * @param string $channel                 the channel which belongs the message to obtain
     *
     * @return ?MessageQueue
     */
    public function getNext(string $channel): ?MessageQueue {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::MESSAGE_QUEUE)
            ->where($qb->expr()->eq('channel', $qb->createNamedParameter($channel, IQueryBuilder::PARAM_STR)));

        $qb->addOrderBy('id', 'ASC');
        $qb->setMaxResults(1);

        $results = $this->findEntities($qb);
        return empty($results) ? null : $results[0];
    }

    /**
     * Obtain the next message in the queue to be sent.
     *
     * @param string $channel                 the channel which belongs the message to obtain
     * @param int    $procedureId             procedure indentifier to which the message belongs
     * @param ?int   $procedureStakeholderId  procedure stakeholder indentifier associated to the message to obtain
     *
     * @return ?MessageQueue
     */
    public function getNextByProcedure(string $channel, int $procedureId, ?int $procedureStakeholderId = null): ?MessageQueue {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::MESSAGE_QUEUE)
            ->where($qb->expr()->eq('channel', $qb->createNamedParameter($channel, IQueryBuilder::PARAM_STR)))
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)));

        if (!empty($procedureStakeholderId)) {
            $qb->andWhere($qb->expr()->eq('procedure_stakeholder_id', $qb->createNamedParameter($procedureStakeholderId, IQueryBuilder::PARAM_INT)));
        }

        $qb->addOrderBy('id', 'ASC');
        $qb->setMaxResults(1);

        $results = $this->findEntities($qb);
        return empty($results) ? null : $results[0];
    }
}
