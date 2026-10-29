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

use OCA\VTramit\Db\Tables;
use OCA\VTramit\Exception\EntityNotFoundException;

class ProcedureTypeMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::PROCEDURE_TYPE, ProcedureType::class, 'procedure type');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return ProcedureType
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): ProcedureType {
        return parent::find($id);
    }

    /**
     * Get procedure type for given procedure ID.
     *
     * @param string $procedureId  the ID of the procedure
     * @return ProcedureType       procedure type associated to the given procedure
     */
    public function findByProcedureId(int $procedureId): ProcedureType {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('a.*')
            ->from($this->getTableName(), 'a')
            ->innerJoin('a', Tables::PROCEDURE, 'b', 'a.id = b.procedure_type_id')
            ->where($qb->expr()->eq('b.id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }


    /**
     * Get filtered procedure types.
     *
     * Valid values for $filter:
     * 'groups'           array of groups which belongs the procedure types; if no
     *                    group is assigned, all the user groups will be used
     * 'allowCreate'      the user is in a group for which is allowed to create the procedure type
     * 'allowAssignGroup' the user is in a group for which is allowed to assign the group in the procedure type
     * 'allowAssignUser'  the user is in a group for which is allowed to assign the user in the procedure type
     *
     * @param string $filter  filter parameters as array
     * @return array          list of procedure types that accomplish given filters
     */
    public function findByFilter($filter): array {
        if (empty($filter)) {
            return [];
        }

        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('a.*')
            ->from($this->getTableName(), 'a')
            ->innerJoin('a', Tables::PROCEDURE_TYPE_GROUP, 'b', 'a.id = b.procedure_type_id');

        // assigned_group_id IN (groups)
        if (!empty($filter['groups'])) {
            $qb->andWhere($qb->expr()->in('b.group', $qb->createNamedParameter($filter['groups'], IQueryBuilder::PARAM_STR_ARRAY)));
        }

        // allow_create = allowCreate
        if (isset($filter['allowCreate'])) {
            $qb->andWhere($qb->expr()->eq('b.allow_create', $qb->createNamedParameter($filter['allowCreate'], IQueryBuilder::PARAM_BOOL)));
        }

        // allow_assign_group = allowAssignGroup
        if (isset($filter['allowAssignGroup'])) {
            $qb->andWhere($qb->expr()->eq('b.allow_assign_group', $qb->createNamedParameter($filter['allowAssignGroup'], IQueryBuilder::PARAM_BOOL)));
        }

        // allow_assign_user = allowAssignUser
        if (isset($filter['allowAssignUser'])) {
            $qb->andWhere($qb->expr()->eq('b.allow_assign_user', $qb->createNamedParameter($filter['allowAssignUser'], IQueryBuilder::PARAM_BOOL)));
        }

        return $this->findEntities($qb);
    }
}