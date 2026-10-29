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
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

use OCA\VTramit\Db\Tables;
use OCA\VTramit\Exception\EntityNotFoundException;

class ProcedureTypeStatusGroupMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::PROCEDURE_TYPE_STATUS_GROUP, ProcedureTypeStatusGroup::class, 'procedure type for a status and group');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return ProcedureTypeStatusGroup
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): ProcedureTypeStatusGroup {
        return parent::find($id);
    }

    /**
     * Obtain the identifier of the template to apply to the stakeholder for given status and group.
     *
     * @param int    $procedureTypeStatusId  identifier of the type status of the configuration
     * @param string $group                  name of the group for which configuration is searched
     * @return int                           identifier of the template
     * @throws EntityNotFoundException
     */
    public function findNotifyStakeholderTemplateId(int $procedureTypeStatusId, string $group): int {
        try {
            $entity = $this->findByTypeStatusIdAndGroup($procedureTypeStatusId, $group);
        }
        catch (EntityNotFoundException $e) {
            // If no entity found for given type status id and group, then try to find the
            // entity for the general group (procedure_type_group_id = '0')
            $entity = $this->findByTypeStatusIdAndGeneralGroup($procedureTypeStatusId);
        }

        return $entity->getNotifyStakeholderTemplateId();
    }

    /**
     * Obtain the identifier of the template to apply to the worker for given status and group.
     *
     * @param int    $procedureTypeStatusId  identifier of the type status of the configuration
     * @param string $group                  name of the group for which configuration is searched
     * @return int                           identifier of the template
     * @throws EntityNotFoundException
     */
    public function findNotifyStaffTemplateId(int $procedureTypeStatusId, string $group): int {
        try {
            $entity = $this->findByTypeStatusIdAndGroup($procedureTypeStatusId, $group);
        }
        catch (EntityNotFoundException $e) {
            // If no entity found for given type status id and group, then try to find the
            // entity for the general group (procedure_type_group_id = '0')
            $entity = $this->findByTypeStatusIdAndGeneralGroup($procedureTypeStatusId);
        }

        return $entity->getNotifyStaffTemplateId();
    }

    /**
     * Obtain the configuration of procedure type for given type status and group.
     *
     * @param int    $procedureTypeStatusId  identifier of the type status of the configuration
     * @param string $group                  name of the group for which configuration is searched
     * @return ProcedureTypeStatusGroup      configuration for the status and group
     * @throws EntityNotFoundException
     */
    private function findByTypeStatusIdAndGroup(int $procedureTypeStatusId, string $group): ProcedureTypeStatusGroup {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName(), 'a')
            ->leftJoin('a', Tables::PROCEDURE_TYPE_GROUP, 'b', 'a.procedure_type_group_id = b.id')
            ->andWhere($qb->expr()->eq('a.procedure_type_status_id', $qb->createNamedParameter($procedureTypeStatusId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('b.group', $qb->createNamedParameter($group, IQueryBuilder::PARAM_STR)));

        return $entity = $this->findEntity($qb);
    }

    /**
     * Obtain the configuration of procedure type for given type status and general group
     * (procedure_type_group_id = '0').
     *
     * @param int    $procedureTypeStatusId  identifier of the type status of the configuration
     * @return ProcedureTypeStatusGroup      configuration for the status and group
     * @throws EntityNotFoundException
     */
    private function findByTypeStatusIdAndGeneralGroup(int $procedureTypeStatusId): ProcedureTypeStatusGroup {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName(), 'a')
            ->andWhere($qb->expr()->eq('a.procedure_type_status_id', $qb->createNamedParameter($procedureTypeStatusId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('a.procedure_type_group_id', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)));

        return $entity = $this->findEntity($qb);
    }
}