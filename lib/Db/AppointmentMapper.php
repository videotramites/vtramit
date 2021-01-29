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

class AppointmentMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'vtramit_appointment', Appointment::class);
    }

    /**
     * @param int $id
     * @param string $userId
     * @return Entity|Appointment
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws DoesNotExistException
     */
    public function find(int $id): Appointment {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_appointment')
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
            ->from('vtramit_appointment');

        return $this->findEntities($qb);
    }

    /**
     * @param int $id
     * @return Entity|Appointment
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws DoesNotExistException
     */
    public function findById(int $id): Appointment {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_appointment')
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * @param string $externalId
     * @return Entity|Appointment
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws DoesNotExistException
     */
    public function findByExternalId(string $externalId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_appointment')
            ->where($qb->expr()->eq('external_id', $qb->createNamedParameter($externalId, IQueryBuilder::PARAM_STR)));

        return $this->findEntities($qb);
    }

    /**
     * @param string $jitsiRoomCode
     * @return Entity|Appointment
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws DoesNotExistException
     */
    public function findByJitsiRoomCode(string $jitsiRoomCode): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_appointment')
            ->where($qb->expr()->eq('jitsi_room_code', $qb->createNamedParameter($jitsiRoomCode, IQueryBuilder::PARAM_STR)));

        return $this->findEntities($qb);
    }

    /**
     * @param string $jitsiRoomCode
     * @param array $state
     * @return Entity|Appointment
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws DoesNotExistException
     */
    public function findByJitsiRoomCodeAndState(string $jitsiRoomCode, array $state): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_appointment')
            ->where($qb->expr()->eq('jitsi_room_code', $qb->createNamedParameter($jitsiRoomCode, IQueryBuilder::PARAM_STR)))
            ->andWhere($qb->expr()->in('state', $qb->createNamedParameter($state, IQueryBuilder::PARAM_INT_ARRAY)));

        return $this->findEntities($qb);
    }

    /**
     * Get the list of appointments filtered for given departments and date range.
     *
     * @param array $departments list of departments to get
     * @param int   $minDate     mininum date of the appointments to retrieve
     * @param int   $maxDate     maximum date of the appointments to retrieve
     * @return array             list of found entities
     */
    public function findByDepartmentAndDate(
        array $departments, 
        string $id = '', 
        int $minDate = null, 
        int $maxDate = null, 
        array $states = [], 
        array $users = [], 
        bool $unassigned = false
    ): array {
        if (empty($departments)) {
            return [];
        }

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_appointment')
            ->where($qb->expr()->in('department', $qb->createNamedParameter($departments, IQueryBuilder::PARAM_STR_ARRAY)));

        if (!empty($id) && !empty(trim($id))) { 
            $qb->andWhere($qb->expr()->like('external_id', $qb->createNamedParameter(trim($id)."%", IQueryBuilder::PARAM_STR)));
        }
        else {
            if (!empty($minDate)) {
                $qb->andWhere($qb->expr()->gte('date', $qb->createNamedParameter($minDate, IQueryBuilder::PARAM_INT)));
            }

            if (!empty($maxDate)) {
                $qb->andWhere($qb->expr()->lte('date', $qb->createNamedParameter($maxDate, IQueryBuilder::PARAM_INT)));
            }

            if (!empty($states)) {
                $qb->andWhere($qb->expr()->in('state', $qb->createNamedParameter($states, IQueryBuilder::PARAM_STR_ARRAY)));
            }

            //TODO: Fer una expressió per les 2 últimes condiccions

            if (!empty($users)) {
                $qb->andWhere($qb->expr()->in('assigned_to', $qb->createNamedParameter($users, IQueryBuilder::PARAM_STR_ARRAY)));
            }
            elseif ($unassigned) {
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNull('assigned_to'),
                        $qb->expr()->eq('assigned_to', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR))
                    )
                );
            }
        }

        $qb->orderBy('date');

        return $this->findEntities($qb);
    }

    /**
     * Get the list of appointments not anonymized and with given state.
     *
     * @param int $state  state of the appointments to search
     * @param int $age    number of hours since last state change.
     * @return array      list of not anonymized appointments found
     */
    public function findNotAnonymizedByAge(int $age): array {
        $now = new \DateTime();
        $old = $now->getTimestamp() - ($age * 3600); // convert hours to seconds and calculate what is considered an aged register

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_appointment')
            ->andWhere($qb->expr()->lte('date', $qb->createNamedParameter($old, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->notLike('email', $qb->createNamedParameter('%@anonymous.com', IQueryBuilder::PARAM_STR)));

        return $this->findEntities($qb);
    }

    /**
     * Get the list of appointments not anonymized and with given state.
     *
     * @param int $state  state of the appointments to search
     * @param int $age    number of hours since last state change.
     * @return array      list of not anonymized appointments found
     */
    public function findNotAnonymizedByStateAndAge(int $state, int $age): array {
        $now = new \DateTime();
        $old = $now->getTimestamp() - ($age * 3600); // convert hours to seconds and calculate what is considered an aged register

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('vtramit_appointment')
            ->where($qb->expr()->eq('state', $qb->createNamedParameter($state, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->lte('date', $qb->createNamedParameter($old, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->notLike('email', $qb->createNamedParameter('%@anonymous.com', IQueryBuilder::PARAM_STR)));

        return $this->findEntities($qb);
    }

    /**
     * Delete all the appointments with the given external identifier. Optionally, a list
     * of appointments not to delete can be given.
     *
     * @param string    $externalId    external identifier of the appointments to cancel
     * @param array|int $excludedAppointments identifier of the appointments to avoid cancel
     */
    public function deleteByExternalId(string $externalId, array $excludedAppointments = []) {
        if (!empty($excludedAppointments) && !is_array($excludedAppointments)) {
            $excludedAppointments = array($excludedAppointments);
        }

        $found_appointments = $this->findByExternalId($externalId);
        foreach($found_appointments as $appointment) {
            if (empty($excludedAppointment) || !in_array($appointment->getId(), $excludedAppointments)) {
                $this->delete($appointment);
                // TODO: Eliminar mail de la cua per appointment->getId()
            }
        }
    }

    /**
     * Cancel all the appointments with the given external identifier. Optionally, a list
     * of appointments not to cancel can be given.
     *
     * @param string    $externalId    external identifier of the appointments to cancel
     * @param array|int $excludedAppointments identifier of the appointments to avoid cancel
     * @return array                   entity list of cancelled appointments
     */
    public function cancelByExternalId(string $externalId, array $excludedAppointments = []): array {
        if (!empty($excludedAppointments) && !is_array($excludedAppointments)) {
            $excludedAppointments = array($excludedAppointments);
        }

        $cancelled = [];
        $found_appointments = $this->findByExternalId($externalId);
        foreach($found_appointments as $appointment) {
            if (empty($excludedAppointments) || !in_array($appointment->getId(), $excludedAppointments) ) {
                if ($appointment->getState() != Appointment::STATE_CANCELLED) {
                    $appointment->setState(Appointment::STATE_CANCELLED);
                    $this->update($appointment);
                    $cancelled[] = $appointment;
                }
            }
        }

        return $cancelled;
    }

    /**
     * Change the state of all appointments in a given state a date range.
     *
     * @param int $fromState  state to change
     * @param int $toState       the new state
     * @param int $minDate    mininum date of the appointments to change
     * @param int $maxDate    maximum date of the appointments to change
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function changeStateByDateRange(int $fromState, int $toState, int $minDate, int $maxDate) {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->update('vtramit_appointment')
            ->set('state', $qb->createNamedParameter($toState, IQueryBuilder::PARAM_INT))
            ->where($qb->expr()->eq('state', $qb->createNamedParameter($fromState, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->gte('date', $qb->createNamedParameter($minDate, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->lte('date', $qb->createNamedParameter($maxDate, IQueryBuilder::PARAM_INT)));

        return $qb->execute();
    }
}
