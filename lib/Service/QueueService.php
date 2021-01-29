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

namespace OCA\VTramit\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use \OCP\ILogger;

use OCA\VTramit\Db\Queue;
use OCA\VTramit\Db\QueueMapper;
class QueueService {

    /** @var QueueMapper */
    private $mapper;
    private $logger;
    private $db;

    private $dbConnection = null;

    public function __construct(
        ILogger $logger,
        QueueMapper $mapper,
        IDBConnection $db
    ) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->db = $db;
    }

    public function findAll(): array {
        return $this->mapper->findAll();
    }

    /**
     * Find all the mails in mail queue for given appointment identifier.
     *
     * @param int $appointmentId  appointment indentifier to filter for
     * @return array              list of found entities
     */
    public function findByAppointmentId(int $appointmentId): Appointment {
        return $this->mapper->findByAppointmentId($appointmentId);
    }

    public function getNext(): array {
        return $this->mapper->getNext();
    }

    private function handleException(Exception $e): void {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new QueueNotFound($e->getMessage());
        } else {
            throw $e;
        }
    }

    public function find($id) {
        try {
            return $this->mapper->find($id);

            // in order to be able to plug in different storage backends like files
            // for instance it is a good idea to turn storage related exceptions
            // into service related exceptions so controllers and service users
            // have to deal with only one type of exception
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function create($appointmentId, $mailTo, $mailCc, $mailCco, $subject, $body) {
        $queue = new Queue();
        $queue->setAppointmentId($appointmentId);
        $queue->setMailTo($mailTo);
        $queue->setMailCc($mailCc);
        $queue->setMailCco($mailCco);
        $queue->setSubject($subject);
        $queue->setBody($body);
        return $this->mapper->insert($queue);
    }

    public function update($id, $appointmentId, $mailTo, $mailCc, $mailCco, $subject, $body) {
        try {
            $queue = $this->mapper->find($id);
            $queue->setAppointmentId($appointmentId);
            $queue->setMailTo($mailTo);
            $queue->setMailCc($mailCc);
            $queue->setMailCco($mailCco);
            $queue->setSubject($subject);
            $queue->setBody($body);
            return $this->mapper->update($queue);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function delete($id) {
        try {
            $queue = $this->mapper->find($id);
            $this->mapper->delete($queue);
            return $queue;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function deleteByAppointmentId($appointmentId) {
        try {
            $this->mapper->deleteByAppointmentId($appointmentId);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}
