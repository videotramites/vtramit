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

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;

use OCA\VTramit\Db\Tables;
use OCA\VTramit\Exception\EntityNotFoundException;

class ProcedureStakeholderMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::PROCEDURE_STAKEHOLDER, ProcedureStakeholder::class, 'procedure stakeholder');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return ProcedureStakeholder
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): ProcedureStakeholder {
        return parent::find($id);
    }

    /**
     * Obtain the stakeholders related to given procedure.
     *
     * @param int $procedure  procedure entity which belongs the stakeholders
     * @return array          list of stakeholder entities
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function findByProcedure(Procedure $procedure): array {
        return $this->findByProcedureId($procedure->getId());
    }

    /**
     * Obtain the stakeholders related to given procedure.
     *
     * @param int $procedureId  identifier of the procedure which belongs the stakeholders
     * @return array            list of stakeholder entities
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function findByProcedureId(int $procedureId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->andWhere($qb->expr()->eq('a.procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * Obtain the main entity for given procedure. This method will try to retrieve
     * the stakeholder marked as main; if none is marked, it will retrieve the first
     * one inserted in the database that belongs to the procedure.
     *
     * @param int $procedure  procedure entity to which belongs the stakeholder
     * @return ProcedureStakeholder
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function findMainByProcedure(Procedure $procedure): ProcedureStakeholder {
        return $this->findMainByProcedureId($procedure->getId());
    }

    /**
     * Obtain the main entity for given procedure. This method will try to retrieve
     * the stakeholder marked as main; if none is marked, it will retrieve the first
     * one inserted in the database that belongs to the procedure.
     *
     * @param int $procedureId  identifier of the procedure to which belongs the stakeholder
     * @return ProcedureStakeholder
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function findMainByProcedureId(int $procedureId): ProcedureStakeholder {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            //->andWhere('a.is_main = false')
            ->andWhere($qb->expr()->eq('a.procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->addOrderBy('a.is_main', 'DESC')
            ->addOrderBy('a.procedure_id', 'ASC')
            ->setMaxResults(1);

        return $this->findEntity($qb);
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
     * 'freeSearch'      search if external ID, personal ID and/or stakeholder
     *                   name starts with given string. When this parameter is given
     *                   all other parameters are ignored
     *
     * @param string $filter  filter parameters as array
     * @return array          list of procedures that accomplish given filters
     */
    public function findMainByFilter($filter): array {
        if (empty($filter)) {
            return [];
        }

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE, 'b', 'a.procedure_id = b.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE, 'c', 'b.procedure_type_id = c.id')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_GROUP, 'd', 'c.id = d.procedure_type_id')
            ->andWhere('a.is_main = true')
        ;

        if (!empty($filter['procedureTypeId'])) {
            $qb->andWhere($qb->expr()->eq('b.procedure_type_id', $qb->createNamedParameter($filter['procedureTypeId'], IQueryBuilder::PARAM_INT)));
        }

        if (!empty($filter['freeSearch']) && !empty(trim($filter['freeSearch']))) {
            $filter['freeSearch'] = trim($filter['freeSearch']);
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('b.external_id', $qb->createNamedParameter($filter['freeSearch']."%", IQueryBuilder::PARAM_STR)),
                    $qb->expr()->like('a.personal_id', $qb->createNamedParameter($filter['freeSearch']."%", IQueryBuilder::PARAM_STR)),
                    $qb->expr()->like('a.name', $qb->createNamedParameter($filter['freeSearch']."%", IQueryBuilder::PARAM_STR))
                )
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
                            $qb->expr()->isNull('b.assigned_group_id'),
                            $qb->expr()->eq('b.assigned_group_id', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR)),
                            $qb->expr()->in('b.assigned_group_id', $qb->createNamedParameter($filter['groups'], IQueryBuilder::PARAM_STR_ARRAY))
                        )
                    );
                }

                // assigned_group_id IS NULL OR assigned_to = ''
                else if ($unassigned) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->isNull('b.assigned_group_id'),
                            $qb->expr()->eq('b.assigned_group_id', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR))
                        )
                    );
                }

                // assigned_group_id IN (groups)
                else if (!empty($filter['groups'])) {
                    $qb->andWhere($qb->expr()->in('b.assigned_group_id', $qb->createNamedParameter($filter['groups'], IQueryBuilder::PARAM_STR_ARRAY)));
                }

                if (!empty($filter['groups'])) {
                    $qb->andWhere($qb->expr()->in('d.group', $qb->createNamedParameter($filter['groups'], IQueryBuilder::PARAM_STR_ARRAY)));
                }
            }

            // jitsi_room_code = jitsiRoomCode
            if (!empty($filter['jitsiRoomCode'])) {
                $qb->andWhere($qb->expr()->eq('b.jitsi_room_code', $qb->createNamedParameter($filter['jitsiRoomCode'], IQueryBuilder::PARAM_STR)));
            }

            // external_id = externalId
            if (!empty($filter['externalId'])) {
                $qb->andWhere($qb->expr()->eq('b.external_id', $qb->createNamedParameter($filter['externalId'], IQueryBuilder::PARAM_STR)));
            }

            // appointment_date >= minDate
            if (!empty($filter['minDate'])) {
                /*if (!is_numeric($filter['minDate'])) {
                    $filter['minDate'] = strtotime($filter['minDate']);
                }*/

                $qb->andWhere($qb->expr()->gte('b.appointment_date', $qb->createNamedParameter($filter['minDate'], IQueryBuilder::PARAM_DATE)));
            }

            // appointment_date <= maxDate
            if (!empty($filter['maxDate'])) {
                /*if (!is_numeric($filter['maxDate'])) {
                    $filter['maxDate'] = strtotime($filter['maxDate']);
                }*/

                $qb->andWhere($qb->expr()->lte('b.appointment_date', $qb->createNamedParameter($filter['maxDate'], IQueryBuilder::PARAM_DATE)));
            }

            // status_id IN (status)
            if (!empty($filter['status'])) {
                $qb->andWhere($qb->expr()->in('b.status_id', $qb->createNamedParameter($filter['status'], IQueryBuilder::PARAM_INT_ARRAY)));
            }

            if (!empty($filter['users'])) {
                $unassigned =  in_array('unassigned', $filter['users']);
                $filter['users'] = array_diff($filter['users'], ['unassigned']);

                // assigned_user_id IS NULL OR assigned_user_id = '' OR assigned_user_id IN (users)
                if ($unassigned && !empty($filter['users'])) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->isNull('b.assigned_user_id'),
                            $qb->expr()->eq('b.assigned_user_id', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR)),
                            $qb->expr()->in('b.assigned_user_id', $qb->createNamedParameter($filter['users'], IQueryBuilder::PARAM_STR_ARRAY))
                        )
                    );
                }

                // assigned_user_id IS NULL OR assigned_to = ''
                else if ($unassigned) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->isNull('b.assigned_user_id'),
                            $qb->expr()->eq('b.assigned_user_id', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR))
                        )
                    );
                }

                // assigned_user_id IN (users)
                else if (!empty($filter['users'])) {
                    $qb->andWhere($qb->expr()->in('b.assigned_user_id', $qb->createNamedParameter($filter['users'], IQueryBuilder::PARAM_STR_ARRAY)));
                }
            }

            // name like stakeholderName%
            if (!empty($filter['stakeholderName']) && !empty(trim($filter['stakeholderName']))) {
                $filter['stakeholderName'] = trim($filter['stakeholderName']);
                $qb->andWhere($qb->expr()->like('a.name', $qb->createNamedParameter($filter['stakeholderName']."%", IQueryBuilder::PARAM_STR)));
            }

            // personal_id like stakeholderCitizenId%
            if (!empty($filter['stakeholderCitizenId']) && !empty(trim($filter['stakeholderCitizenId']))) {
                $filter['stakeholderCitizenId'] = trim($filter['stakeholderCitizenId']);
                $qb->andWhere($qb->expr()->like('a.personal_id', $qb->createNamedParameter($filter['stakeholderCitizenId']."%", IQueryBuilder::PARAM_STR)));
            }

            // phone like stakeholderPhone%
            if (!empty($filter['stakeholderPhone']) && !empty(trim($filter['stakeholderPhone']))) {
                $filter['stakeholderPhone'] = trim($filter['stakeholderPhone']);
                $qb->andWhere($qb->expr()->like('a.phone', $qb->createNamedParameter($filter['stakeholderPhone']."%", IQueryBuilder::PARAM_STR)));
            }

            // email like stakeholderEmail%
            if (!empty($filter['stakeholderEmail']) && !empty(trim($filter['stakeholderEmail']))) {
                $filter['stakeholderEmail'] = trim($filter['stakeholderEmail']);
                $qb->andWhere($qb->expr()->like('a.email', $qb->createNamedParameter($filter['stakeholderEmail']."%", IQueryBuilder::PARAM_STR)));
            }
        }

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
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE, 'b', 'a.procedure_id = b.id')
            ->andWhere($qb->expr()->in('b.status', $qb->createNamedParameter($status, IQueryBuilder::PARAM_STR_ARRAY)))
            ->andWhere($qb->expr()->notLike('a.email', $qb->createNamedParameter('%@anonymous.com', IQueryBuilder::PARAM_STR)))
        ;

        return $this->findEntities($qb);
    }
}