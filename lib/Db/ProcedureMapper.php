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

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\Db\Tables;
use OCA\VTramit\Exception\EntityNotFoundException;

class ProcedureMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::PROCEDURE, Procedure::class, 'procedure');
    }

    /**
     * Obtain the procedure type given its unique identifier.
     *
     * @param int $id
     * @return Procedure
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): Procedure {
        return parent::find($id);
    }

    /**
     * Get filtered procedures.
     *
     * Valid values for $filter:
     * 'minDate'         oldest date of procedures to retrieve; PHP time strings
     *                   are valid, like 'today', 'tomorrow', '+1 day', ...
     * 'maxDate'         newest date of procedures to retrieve; PHP time strings
     *                   are valid, like 'today', 'tomorrow', '+1 day', ...
     * 'groups'          array of groups which belongs the procedures; the special
     *                   user 'unassigned' can be used to retrieve also procedures
     *                   not yet assigned
     * 'status'          array of procedure statuses. If not set, any state will be
     *                   retrieved
     * 'users'           array of users assigned to the procedures; the special
     *                   user 'unassigned' can be used to retrieve also procedures
     *                   not yet assigned. If not set, procedures with any assignment
     *                   will be retrieved
     * 'externalId'      search for given external Id
     * 'jitsiRoomCode'   the jitsi room code
     * 'procedureTypeId' search for given external Id
     * 'topic'           exact match against the topic column (case-sensitive)
     * 'assignedGroupId' raw equality on assigned_group_id; unlike 'groups'
     *                   this does NOT require the procedure_type to be
     *                   registered against that group in oc_vtproc_type_group
     * 'freeSearch'      search if external ID, personal ID and/or stakeholder
     *                   name starts with given string. When this parameter is given
     *                   all other parameters are ignored
     *
     * @param string $filter  filter parameters as array
     * @return array          list of procedures that accomplish given filters
     */
    public function findByFilter($filter): array {
        if (empty($filter)) {
            return [];
        }

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_STAKEHOLDER, 'b', 'a.id = b.procedure_id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE, 'c', 'a.procedure_type_id = c.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_GROUP, 'd', 'c.id = d.procedure_type_id')
        ;

        if (!empty($filter['procedureTypeId'])) {
            $qb->andWhere($qb->expr()->eq('a.procedure_type_id', $qb->createNamedParameter($filter['procedureTypeId'], IQueryBuilder::PARAM_INT)));
        }

        if (!empty($filter['freeSearch']) && !empty(trim($filter['freeSearch']))) {
            $filter['freeSearch'] = trim($filter['freeSearch']); /** external_id appointment */
            $qb->andWhere(
                $qb->expr()->like('a.external_id', $qb->createNamedParameter($filter['freeSearch']."%", IQueryBuilder::PARAM_STR)),
            );
        }
        else {
            // assigned_group_id IN (groups)
            if (!empty($filter['groups'])) {
                $unassigned =  in_array('unassigned', $filter['groups']);
                $filter['groups'] = array_diff($filter['groups'], ['unassigned']);

                // assigned_group_id IS NULL OR assigned_group_id = '' OR assigned_group_id IN (groups)
                if ($unassigned && !empty($filter['groups'])) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->isNull('a.assigned_group_id'),
                            $qb->expr()->eq('a.assigned_group_id', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR)),
                            $qb->expr()->in('a.assigned_group_id', $qb->createNamedParameter($filter['groups'], IQueryBuilder::PARAM_STR_ARRAY))
                        )
                    );
                }

                // assigned_group_id IS NULL OR assigned_to = ''
                else if ($unassigned) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->isNull('a.assigned_group_id'),
                            $qb->expr()->eq('a.assigned_group_id', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR))
                        )
                    );
                }

                // assigned_group_id IN (groups)
                else if (!empty($filter['groups'])) {
                    $qb->andWhere($qb->expr()->in('a.assigned_group_id', $qb->createNamedParameter($filter['groups'], IQueryBuilder::PARAM_STR_ARRAY)));
                }

                if (!empty($filter['groups'])) {
                    $qb->andWhere($qb->expr()->in('d.group', $qb->createNamedParameter($filter['groups'], IQueryBuilder::PARAM_STR_ARRAY)));
                }
            }

            // jitsi_room_code = jitsiRoomCode
            if (!empty($filter['jitsiRoomCode'])) {
                $qb->andWhere($qb->expr()->eq('a.jitsi_room_code', $qb->createNamedParameter($filter['jitsiRoomCode'], IQueryBuilder::PARAM_STR)));
            }

            // external_id = externalId
            if (!empty($filter['externalId'])) {
                $qb->andWhere($qb->expr()->iLike('a.external_id', $qb->createNamedParameter($filter['externalId'], IQueryBuilder::PARAM_STR)));
            }

            // topic = topic (exact match, case-sensitive). Used by the
            // citizen-queue feature with scope=topic to count appointments
            // with the same topic ahead of self.
            if (!empty($filter['topic'])) {
                $qb->andWhere($qb->expr()->eq('a.topic', $qb->createNamedParameter($filter['topic'], IQueryBuilder::PARAM_STR)));
            }

            // assigned_group_id = assignedGroupId (raw equality, no JOIN
            // against procedure_type_group). The plural 'groups' filter
            // ALSO requires the procedure_type to be registered for that
            // group in oc_vtproc_type_group, which is the right thing for
            // listing/search but wrong for the citizen-queue feature
            // where we just want "same group_id ahead of me".
            if (!empty($filter['assignedGroupId'])) {
                $qb->andWhere($qb->expr()->eq('a.assigned_group_id', $qb->createNamedParameter($filter['assignedGroupId'], IQueryBuilder::PARAM_STR)));
            }

            // appointment_date >= minDate
            if (!empty($filter['minDate'])) {
                /*if (!is_numeric($filter['minDate'])) {
                    $filter['minDate'] = strtotime($filter['minDate']);
                }*/

                $qb->andWhere($qb->expr()->gte('a.appointment_date', $qb->createNamedParameter($filter['minDate'], IQueryBuilder::PARAM_DATE)));
            }

            // appointment_date <= maxDate
            if (!empty($filter['maxDate'])) {
                /*if (!is_numeric($filter['maxDate'])) {
                    $filter['maxDate'] = strtotime($filter['maxDate']);
                }*/

                $qb->andWhere($qb->expr()->lte('a.appointment_date', $qb->createNamedParameter($filter['maxDate'], IQueryBuilder::PARAM_DATE)));
            }

            // status_id IN (status)
            if (!empty($filter['status'])) {
                $qb->andWhere($qb->expr()->in('a.status_id', $qb->createNamedParameter($filter['status'], IQueryBuilder::PARAM_INT_ARRAY)));
            }

            if (!empty($filter['users'])) {
                $unassigned =  in_array('unassigned', $filter['users']);
                $filter['users'] = array_diff($filter['users'], ['unassigned']);

                // assigned_user_id IS NULL OR assigned_user_id = '' OR assigned_user_id IN (users)
                if ($unassigned && !empty($filter['users'])) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->isNull('a.assigned_user_id'),
                            $qb->expr()->eq('a.assigned_user_id', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR)),
                            $qb->expr()->in('a.assigned_user_id', $qb->createNamedParameter($filter['users'], IQueryBuilder::PARAM_STR_ARRAY))
                        )
                    );
                }

                // assigned_user_id IS NULL OR assigned_to = ''
                else if ($unassigned) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->isNull('a.assigned_user_id'),
                            $qb->expr()->eq('a.assigned_user_id', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR))
                        )
                    );
                }

                // assigned_user_id IN (users)
                else if (!empty($filter['users'])) {
                    $qb->andWhere($qb->expr()->in('a.assigned_user_id', $qb->createNamedParameter($filter['users'], IQueryBuilder::PARAM_STR_ARRAY)));
                }
            }

            // name like stakeholderName%
            if (!empty($filter['stakeholderName']) && !empty(trim($filter['stakeholderName']))) {
                $filter['stakeholderName'] = trim($filter['stakeholderName']);
                $qb->andWhere($qb->expr()->like('b.name', $qb->createNamedParameter($filter['stakeholderName']."%", IQueryBuilder::PARAM_STR)));
            }

            // personal_id like stakeholderCitizenId%
            if (!empty($filter['stakeholderCitizenId']) && !empty(trim($filter['stakeholderCitizenId']))) {
                $filter['stakeholderCitizenId'] = trim($filter['stakeholderCitizenId']);
                $qb->andWhere($qb->expr()->like('b.personal_id', $qb->createNamedParameter($filter['stakeholderCitizenId']."%", IQueryBuilder::PARAM_STR)));
            }

            // phone like stakeholderPhone%
            if (!empty($filter['stakeholderPhone']) && !empty(trim($filter['stakeholderPhone']))) {
                $filter['stakeholderPhone'] = trim($filter['stakeholderPhone']);
                $qb->andWhere($qb->expr()->like('b.phone', $qb->createNamedParameter($filter['stakeholderPhone']."%", IQueryBuilder::PARAM_STR)));
            }

            // email like stakeholderEmail%
            if (!empty($filter['stakeholderEmail']) && !empty(trim($filter['stakeholderEmail']))) {
                $filter['stakeholderEmail'] = trim($filter['stakeholderEmail']);
                $qb->andWhere($qb->expr()->like('b.email', $qb->createNamedParameter($filter['stakeholderEmail']."%", IQueryBuilder::PARAM_STR)));
            }
        }

        // ORDER BY appointment_date
        $qb->orderBy('a.appointment_date');

        return $this->findEntities($qb);
    }

    /**
     * @param string $jitsiRoomCode
     * @return array list of Entity|Appointment found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findByJitsiRoomCode(string $jitsiRoomCode): array {
        return $this->findByFilter(['jitsiRoomCode' => $jitsiRoomCode]);
    }

    /**
     * Obtain all pendant procedures.
     *
     * @return array
     */
    public function findPendant(): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_TYPE, 'b', 'a.procedure_type_id = b.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_STATUS, 'c', 'b.id = c.procedure_type_id AND a.procedure_type_status_id = c.id')
            ->where('c.is_final_status = false');

        return $this->findEntities($qb);
    }

    /**
     * NOTE: aquest mètode no es fa servir i és per a quan s'habiliti l'ús de VTRAMIT_PROCEDURE_TYPE_STATUS
     *
     * Obtain all pendant procedures that are allowed for a given group.
     *
     * @param string $group   name of the group that can manage procedures
     * @return array
     */
    public function findPendantForGroup(string $group): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_TYPE, 'b', 'a.procedure_type_id = b.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_STATUS, 'c', 'b.id = c.procedure_type_id AND a.procedure_type_status_id = c.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_GROUP, 'd', 'b.id = d.procedure_type_id')
            ->andWhere('c.is_final_status = false')
            ->andWhere($qb->expr()->eq('d.group', $qb->createNamedParameter($group, IQueryBuilder::PARAM_STR)));

        return $this->findEntities($qb);
    }

    /**
     * NOTE: aquest mètode no es fa servir i és per a quan s'habiliti l'ús de VTRAMIT_PROCEDURE_TYPE_STATUS
     *
     * Obtain all pendant procedures that are allowed for a given group.
     *
     * @param int    $procedureTypeId  identifier of the procedures type to search
     * @return array
     */
    public function findPendantByType(string $procedureTypeId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_TYPE, 'b', 'a.procedure_type_id = b.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_STATUS, 'c', 'b.id = c.procedure_type_id AND a.procedure_type_status_id = c.id')
            ->andWhere('c.is_final_status = false')
            ->andWhere($qb->expr()->eq('a.procedure_type_id', $qb->createNamedParameter($procedureTypeId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * NOTE: aquest mètode no es fa servir i és per a quan s'habiliti l'ús de VTRAMIT_PROCEDURE_TYPE_STATUS
     *
     * Obtain all pendant procedures that are allowed for a given group.
     *
     * @param int    $procedureTypeId  identifier of the procedures type to search
     * @param string $group            name of the group that can manage procedures
     * @return array
     */
    public function findPendantByTypeForGroup(string $procedureTypeId, string $group): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_TYPE, 'b', 'a.procedure_type_id = b.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_STATUS, 'c', 'b.id = c.procedure_type_id AND a.procedure_type_status_id = c.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_GROUP, 'd', 'b.id = d.procedure_type_id')
            ->andWhere('c.is_final_status = false')
            ->andWhere($qb->expr()->eq('d.group', $qb->createNamedParameter($group, IQueryBuilder::PARAM_STR)))
            ->andWhere($qb->expr()->eq('a.procedure_type_id', $qb->createNamedParameter($procedureTypeId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * Get  the procedures with the given external identifier. Optionally, a list
     * of procedures not to retrieve can be given.
     *
     * @param string    $externalId  external identifier of the duplicated procedures
     * @param array|int $excluded    list of procedures identifiers to avoid in the results
     * @return array                 entity list of cancelled procedures
     */
    public function findDuplicatedByExternalId(string $externalId, array $excludedProcedures = []): array {
        if (!empty($excludedProcedures) && !is_array($excludedProcedures)) {
            $excludedProcedures = array($excludedProcedures);
        }

        /* @var $qb IQueryBuilder */
        $finalStatus = ProcedureStatus::getFinalStatus();
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->andWhere($qb->expr()->eq('a.external_id', $qb->createNamedParameter($externalId, IQueryBuilder::PARAM_STR)))
            ->andWhere($qb->expr()->notIn('a.status_id', $qb->createNamedParameter($finalStatus, IQueryBuilder::PARAM_INT_ARRAY)));

        if (!empty($excludedProcedures)) {
            $qb->andWhere($qb->expr()->notIn('a.id', $qb->createNamedParameter($excludedProcedures, IQueryBuilder::PARAM_INT_ARRAY)));
        }

        /*
        // TODO: Following query can be executed when ProcedureTypeStatus is enabled to replace previous one
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_TYPE, 'b', 'a.procedure_type_id = b.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_STATUS, 'c', 'b.id = c.procedure_type_id AND a.procedure_type_status_id = c.id')
            ->andWhere('c.is_final_status = false')
            ->andWhere($qb->expr()->eq('a.external_id', $qb->createNamedParameter($externalId, IQueryBuilder::PARAM_STR)));

        if (!empty($excludedProcedures)) {
            $qb->andWhere($qb->expr()->notIn('a.id', $qb->createNamedParameter($excludedProcedures, IQueryBuilder::PARAM_INT_ARRAY)));
        }*/

        return $this->findEntities($qb);
    }

    /**
     * Get the list of procedures older than given age.
     *
     * @param int $procedureTypeId    type of the procedures to find
     * @param array $status           list of status of the procedures to search
     * @param int $age                number of hours since last state change
     * @param DateTimeZone $timezone  the relative timezone for the search
     * @return array                  list of not anonymized appointments found
     */
    public function findByStatusAndAge(int $procedureTypeId, array $status, int $age, \DateTimeZone $timezone): array {
        $date = new \DateTime("now", $timezone);
        $interval = "-$age hour";
        $date->modify($interval);

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_STAKEHOLDER, 'b', 'a.id = b.procedure_id')
            ->andWhere($qb->expr()->eq('a.procedure_type_id', $qb->createNamedParameter($procedureTypeId, IQueryBuilder::PARAM_STR_INT)))
            ->andWhere($qb->expr()->in('a.status', $qb->createNamedParameter($status, IQueryBuilder::PARAM_STR_ARRAY)))
            ->andWhere($qb->expr()->lte('a.modification_date', $qb->createNamedParameter($date, IQueryBuilder::PARAM_DATE)))
        ;

        return $this->findEntities($qb);
    }

    /**
     * Get the list of procedures not anonymized and older than given age.
     *
     * @param int $procedureTypeId    type of the procedures to find
     * @param array $status           list of status of the procedures to search
     * @param int $age                number of hours since last state change
     * @param DateTimeZone $timezone  the relative timezone for the search
     * @return array                  list of not anonymized appointments found
     */
    public function findNotAnonymizedByStatusAndAge(int $procedureTypeId, array $status, int $age, \DateTimeZone $timezone): array {
        $date = new \DateTime("now", $timezone);
        $interval = "-$age hour";
        $date->modify($interval);

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_STAKEHOLDER, 'b', 'a.id = b.procedure_id')
            ->andWhere($qb->expr()->eq('a.procedure_type_id', $qb->createNamedParameter($procedureTypeId)))
            ->andWhere($qb->expr()->in('a.status_id', $qb->createNamedParameter($status, IQueryBuilder::PARAM_STR_ARRAY)))
            ->andWhere($qb->expr()->lte('a.modification_date', $qb->createNamedParameter($date, IQueryBuilder::PARAM_DATE)))
            ->andWhere($qb->expr()->lte('a.appointment_date', $qb->createNamedParameter($date, IQueryBuilder::PARAM_DATE)))
            ->andWhere($qb->expr()->notLike('b.email', $qb->createNamedParameter('%@anonymous.com', IQueryBuilder::PARAM_STR)))
        ;

        return $this->findEntities($qb);
    }

    /**
     * Get the list of procedures not anonymized.
     *
     * @param int $status  list of status of the procedures to search
     * @return array       list of not anonymized appointments found
     */
    public function findNotAnonymizedByStatus(array $status): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_STAKEHOLDER, 'b', 'a.id = b.procedure_id')
            ->andWhere($qb->expr()->in('a.status_id', $qb->createNamedParameter($status, IQueryBuilder::PARAM_STR_ARRAY)))
            ->andWhere($qb->expr()->notLike('b.email', $qb->createNamedParameter('%@anonymous.com', IQueryBuilder::PARAM_STR)))
        ;

        return $this->findEntities($qb);
    }

    /**
     * Change the state of all appointments in a given state a date range.
     *
     * @param int       $fromStatus  status to change
     * @param int       $toStatus    the new status
     * @param \DateTime $minDate     mininum date of the appointments to change
     * @param \DateTime $maxDate     maximum date of the appointments to change
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function findByStatusDateRange(int $fromStatus, \DateTime $minDate, \DateTime $maxDate): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('*')
            ->from($this->getTableName())
            ->andWhere($qb->expr()->eq('status_id', $qb->createNamedParameter($fromStatus, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->gte('appointment_date', $qb->createNamedParameter($minDate, IQueryBuilder::PARAM_DATE)))
            ->andWhere($qb->expr()->lte('appointment_date', $qb->createNamedParameter($maxDate, IQueryBuilder::PARAM_DATE)));

            return $this->findEntities($qb);
    }

    /**
     * Change the state of all appointments in a given state a date range.
     *
     * @param int       $fromStatus  status to change
     * @param int       $toStatus    the new status
     * @param \DateTime $minDate     mininum date of the appointments to change
     * @param \DateTime $maxDate     maximum date of the appointments to change
     *
     * @return int
     */
    public function changeStatusByDateRange(int $fromStatus, int $toStatus, \DateTime $minDate, \DateTime $maxDate): int {
        $now = new \DateTime("now", $minDate->getTimezone());

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->getTableName())
            ->set('status_id', $qb->createNamedParameter($toStatus, IQueryBuilder::PARAM_INT))
            ->set('status_date', $qb->createNamedParameter($now, IQueryBuilder::PARAM_DATE))
            ->andWhere($qb->expr()->eq('status_id', $qb->createNamedParameter($fromStatus, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->gte('appointment_date', $qb->createNamedParameter($minDate, IQueryBuilder::PARAM_DATE)))
            ->andWhere($qb->expr()->lte('appointment_date', $qb->createNamedParameter($maxDate, IQueryBuilder::PARAM_DATE)));

        return $qb->executeStatement();
    }
}