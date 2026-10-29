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

class MailQueueMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::MAIL_QUEUE, MailQueue::class, 'mail queue');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return Entity|MailQueue
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): MailQueue {
        return parent::find($id);
    }

    /**
     * Find all the mails in mail queue for given procedure identifier.
     *
     * @param int $procedureId    procedure indentifier to filter for
     * @return array              list of found entities
     */
    public function findByProcedureId(int $procedureId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::MAIL_QUEUE)
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * Obtain the next email in the queue to be sent.
     *
     * @return ?MailQueue
     */
    public function getNext(): ?MailQueue {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::MAIL_QUEUE);
        $qb->setMaxResults(1);

        $results = $this->findEntities($qb);
        return empty($results) ? null : $results[0];
    }
}
