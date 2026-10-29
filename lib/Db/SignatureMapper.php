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

class SignatureMapper extends EntityMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::SIGNATURE, Signature::class, 'signature');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return Signature
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): Signature {
        return parent::find($id);
    }

    /**
     * @param int $procedureId
     * @return array
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findByProcedure(int $procedureId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::SIGNATURE)
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
        ;

        return $this->findEntities($qb);
    }

    /**
     * @param int $procedureId
     * @param array $statusToExclude
     * @return array
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findByProcedureExcludeStatus(int $procedureId, array $statusToExclude): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::SIGNATURE)
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->notIn('status', $qb->createNamedParameter($statusToExclude, IQueryBuilder::PARAM_INT_ARRAY)))
        ;

        return $this->findEntities($qb);
    }

    /**
     * @param int $jitsiRoomCode
     * @return array
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findByJitsiRoomCode(int $jitsiRoomCode): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();$qb->select('s.*')
            ->from(Tables::SIGNATURE, 's')
            ->leftJoin('s', Tables::PROCEDURE, 'p', 's.procedure_id = p.id')
            ->where($qb->expr()->eq('a.jitsi_room_code', $qb->createNamedParameter($jitsi_room_code, IQueryBuilder::PARAM_STR)));

        return $this->findEntities($qb);
    }

    /**
     * @param int $status
     * @return array
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findByStatus(int $status): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::SIGNATURE)
            ->where($qb->expr()->eq('status', $qb->createNamedParameter($status, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * @param int $hash
     * @return array
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findByHash(string $hash): Signature {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::SIGNATURE)
            ->where($qb->expr()->eq('hash', $qb->createNamedParameter($hash, IQueryBuilder::PARAM_STR)));

        return $this->findEntity($qb);
    }

    /**
     * Get all signature files for a given procedure.
     *
     * @param int $procedureId  The procedure identifier.
     * @return array            List of associative arrays with keys: file_id, status, status_date.
     */
    public function findFileSignaturesByProcedure(int $procedureId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('sf.id', 'sf.signature_id', 'sf.file_id', 'st.name', 's.status', 's.status_date')
            ->from(Tables::SIGNATURE_FILE, 'sf')
            ->innerJoin('sf', Tables::SIGNATURE, 's', 'sf.signature_id = s.id')
            ->innerJoin('s', Tables::SIGNATURE_TYPE, 'st', 's.signature_type_id = st.id')
            ->where($qb->expr()->eq('s.procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)));

        $cursor = $qb->executeQuery();
        $rows = $cursor->fetchAll();
        $cursor->closeCursor();

        return $rows ?: [];
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
     * 'status'          array of procedure statuses. If not set, any status will be
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
    public function findByFilter($filter): array {
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
            ->leftJoin('a', Tables::PROCEDURE_STAKEHOLDER, 'e', 'e.procedure_id = b.id')
        ;

        if (!empty($filter['procedureTypeId'])) {
            $qb->andWhere($qb->expr()->eq('b.procedure_type_id', $qb->createNamedParameter($filter['procedureTypeId'], IQueryBuilder::PARAM_INT)));
        }

        if (!empty($filter['freeSearch']) && !empty(trim($filter['freeSearch']))) {
            $filter['freeSearch'] = trim($filter['freeSearch']); /** external_id appointment (procedure) */
            $qb->andWhere(
                $qb->expr()->like('b.external_id', $qb->createNamedParameter($filter['freeSearch']."%", IQueryBuilder::PARAM_STR)),
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
                $qb->andWhere($qb->expr()->like('e.name', $qb->createNamedParameter($filter['stakeholderName']."%", IQueryBuilder::PARAM_STR)));
            }

            // personal_id like stakeholderCitizenId%
            if (!empty($filter['stakeholderCitizenId']) && !empty(trim($filter['stakeholderCitizenId']))) {
                $filter['stakeholderCitizenId'] = trim($filter['stakeholderCitizenId']);
                $qb->andWhere($qb->expr()->like('e.personal_id', $qb->createNamedParameter($filter['stakeholderCitizenId']."%", IQueryBuilder::PARAM_STR)));
            }

            // phone like stakeholderPhone%
            if (!empty($filter['stakeholderPhone']) && !empty(trim($filter['stakeholderPhone']))) {
                $filter['stakeholderPhone'] = trim($filter['stakeholderPhone']);
                $qb->andWhere($qb->expr()->like('e.phone', $qb->createNamedParameter($filter['stakeholderPhone']."%", IQueryBuilder::PARAM_STR)));
            }

            // email like stakeholderEmail%
            if (!empty($filter['stakeholderEmail']) && !empty(trim($filter['stakeholderEmail']))) {
                $filter['stakeholderEmail'] = trim($filter['stakeholderEmail']);
                $qb->andWhere($qb->expr()->like('e.email', $qb->createNamedParameter($filter['stakeholderEmail']."%", IQueryBuilder::PARAM_STR)));
            }
        }

        return $this->findEntities($qb);
    }
}