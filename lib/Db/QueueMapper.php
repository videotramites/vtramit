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

class QueueMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'vtramit_mail_queue', Queue::class);
    }

    /**
     * @param int $id
     * @return Entity|Queue
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws DoesNotExistException
     */
    public function find(int $id): Queue {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_mail_queue')
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * @return array
     */
    public function findAll(): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_mail_queue');

        return $this->findEntities($qb);
    }

    /**
     * Find all the mails in mail queue for given appointment identifier.
     *
     * @param int $appointmentId  appointment indentifier to filter for
     * @return array              list of found entities
     */
    public function findByAppointmentId(int $appointmentId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_mail_queue')
            ->where($qb->expr()->eq('appointment_id', $qb->createNamedParameter($appointmentId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * @return array
     */
    public function getNext(): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_mail_queue');
        $qb->setMaxResults(1);
        return $this->findEntities($qb);
    }

    /**
     * Delete all the mails in mail queue for given appointment identifiers.
     *
     * @param array|int  $appointmentId  an appointment indentier or a list of
     *                                   appointment indentifiers to delete
     */
    public function deleteByAppointmentId($appointmentId) {
        if (!is_array($appointmentId)) {
            $appointmentId = array($appointmentId);
        }

        foreach($appointmentId as $id) {
            $mails = $this->findByAppointmentId($id);
            foreach($mails as $mail) {
                $this->delete($mail);
            }
        }
    }
}
