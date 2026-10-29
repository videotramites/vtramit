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

class ProcedureTypeGroupMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::PROCEDURE_TYPE_GROUP, ProcedureTypeGroup::class, 'procedure type group');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return ProcedurProcedureTypeGroupeType
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): ProcedureTypeGroup {
        return parent::find($id);
    }

    /**
     * // TODO: Eliminar aquesta funció quan estigui operatiu ProcedureTypeStatusGroup
     *
     * Obtain the configuration of procedure type for given type and group.
     *
     * @param int    $procedureTypeId  identifier of the procedure type  of the configuration
     * @param string $group            name of the group for which configuration is searched
     * @return ProcedureTypeGroup      configuration for the group
     * @throws EntityNotFoundException
     */
    public function findByTypeIdAndGroup(int $procedureTypeId, string $group): ProcedureTypeGroup {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->andWhere($qb->expr()->eq('procedure_type_id', $qb->createNamedParameter($procedureTypeId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('group', $qb->createNamedParameter($group, IQueryBuilder::PARAM_STR)));

        return $entity = $this->findEntity($qb);
    }

    /**
     * // TODO: Eliminar aquesta funció quan estigui operatiu ProcedureTypeStatusGroup
     *
     * Obtain the configuration of procedure type for given type and general group
     * (group = '').
     *
     * @param int    $procedureTypeId  identifier of the procedure type
     * @return ProcedureTypeGroup      configuration for the group
     * @throws EntityNotFoundException
     */
    public function findByTypeIdAndGeneralGroup(int $procedureTypeId): ProcedureTypeGroup {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->andWhere($qb->expr()->eq('procedure_type_id', $qb->createNamedParameter($procedureTypeId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('group', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR)));

        return $entity = $this->findEntity($qb);
    }

    /**
     * Obtain all groups allowed for given procedure type.
     *
     * @param int    $procedureTypeId  identifier of the procedure type
     * @return ProcedureTypeGroup      configuration for the group
     * @throws EntityNotFoundException
     */
    public function findByProcedureTypeId(int $procedureTypeId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('procedure_type_id', $qb->createNamedParameter($procedureTypeId, IQueryBuilder::PARAM_INT)));

        return $entity = $this->findEntities($qb);
    }

    /**
     * Obtain the configuration of procedure type group by joining with procedure.
     *
     * @param int $procedureId     Identifier of the procedure.
     * @param string $group        Name of the group for which configuration is searched.
     * @return ProcedureTypeGroup  Configuration for the group.
     * @throws EntityNotFoundException
     */
    public function findByProcedureAndGroup(int $procedureId, string $group): ProcedureTypeGroup {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('ptg.*')
            ->from($this->getTableName(), 'ptg')
            ->innerJoin('ptg', Tables::PROCEDURE, 'p', $qb->expr()->eq('p.procedure_type_id', 'ptg.procedure_type_id'))
            ->andWhere($qb->expr()->eq('p.id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('ptg.group', $qb->createNamedParameter($group, IQueryBuilder::PARAM_STR)));

        return $this->findEntity($qb);
    }

    /**
     * Obtain the configuration of general procedure type group by joining with procedure.
     *
     * @param int $procedureId     Identifier of the procedure.
     * @return ProcedureTypeGroup  Configuration for the general group.
     * @throws EntityNotFoundException
     */
    public function findByProcedureAndGeneralGroup(int $procedureId): ProcedureTypeGroup {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('ptg.*')
            ->from($this->getTableName(), 'ptg')
            ->innerJoin('ptg', Tables::PROCEDURE, 'p', $qb->expr()->eq('p.procedure_type_id', 'ptg.procedure_type_id'))
            ->andWhere($qb->expr()->eq('p.id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('ptg.group', $qb->createNamedParameter('', IQueryBuilder::PARAM_STR)));

        return $this->findEntity($qb);
    }

    /**
     * // TODO: Eliminar aquesta funció quan estigui operatiu ProcedureTypeStatusGroup
     *
     * Obtain the identifier of the template to apply to the stakeholder for given procedure type and group.
     *
     * @param int    $procedureTypeId  identifier of the procedure type of the configuration
     * @param string $group            name of the group for which configuration is searched
     * @return int                     identifier of the template
     * @throws EntityNotFoundException
     */
    public function findNotifyStakeholderTemplateId(int $procedureTypeId, string $group): int {
        try {
            $entity = $this->findByTypeIdAndGroup($procedureTypeId, $group);
        }
        catch (EntityNotFoundException $e) {
            // If no entity found for given type status id and group, then try to find the
            // entity for the general group (group = '')
            $entity = $this->findByTypeIdAndGeneralGroup($procedureTypeId);
        }

        return $entity->getNotifyStakeholderTemplateId();
    }

    /**
     * // TODO: Eliminar aquesta funció quan estigui operatiu ProcedureTypeStatusGroup
     *
     * Obtain the identifier of the template to apply to the staff for given procedure type and group.
     *
     * @param int    $procedureTypeId  identifier of the procedure type of the configuration
     * @param string $group            name of the group for which configuration is searched
     * @return int                     identifier of the template
     * @throws EntityNotFoundException
     */
    public function findNotifyStaffTemplateId(int $procedureTypeId, string $group): int {
        try {
            $entity = $this->findByTypeIdAndGroup($procedureTypeId, $group);
        }
        catch (EntityNotFoundException $e) {
            // If no entity found for given type status id and group, then try to find the
            // entity for the general group (group = '')
            $entity = $this->findByTypeIdAndGeneralGroup($procedureTypeId);
        }

        return $entity->getNotifyStaffTemplateId();
    }

    /**
     * // TODO: Eliminar aquesta funció quan estigui operatiu ProcedureTypeStatusGroup
     *
     * Obtain the list of stakeholder message template identifiers for the given type/group.
     *
     * @param int    $procedureTypeId  The procedure type identifier.
     * @param string $group            The group identifier.
     * @return int[]                   The list of message template IDs (may be empty).
     * @throws EntityNotFoundException
     */
    public function findNotifyStakeholderMessageTemplateIds(int $procedureTypeId, string $group): array {
        try {
            $entity = $this->findByTypeIdAndGroup($procedureTypeId, $group);
        }
        catch (EntityNotFoundException $e) {
            // If no entity found for given type status id and group, then try to find the
            // entity for the general group (group = '')
            $entity = $this->findByTypeIdAndGeneralGroup($procedureTypeId);
        }

        return $entity->getNotifyStakeholderTemplateList();
    }

    /**
     * // TODO: Eliminar aquesta funció quan estigui operatiu ProcedureTypeStatusGroup
     *
     * Obtain the list of staff message template identifiers for the given type/group.
     *
     * @param int    $procedureTypeId  The procedure type identifier.
     * @param string $group            The group identifier.
     * @return int[]                   The list of message template IDs (may be empty).
     */
    public function findNotifyStaffMessageTemplateIds(int $procedureTypeId, string $group): array {
        try {
            $entity = $this->findByTypeIdAndGroup($procedureTypeId, $group);
        }
        catch (\OCA\VTramit\Exception\EntityNotFoundException $e) {
            // If no entity found for the given group, fallback to the general group ('').
            $entity = $this->findByTypeIdAndGeneralGroup($procedureTypeId);
        }

        // Uses the entity helper that parses the comm-separated list into an int[].
        return $entity->getNotifyStaffTemplateList();
    }

    /**
     * Obtain all groups allowed for given procedure type.
     *
     * @param int    $procedureTypeId  identifier of the procedure type
     * @return array      configuration for the status and group
     */
    public function findGroupsByProcedureTypeId(int $procedureTypeId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('group')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('procedure_type_id', $qb->createNamedParameter($procedureTypeId, IQueryBuilder::PARAM_INT)));

        $groups = [];
        $result = $qb->executeQuery();
        foreach($result->fetchAll() as $group) {
            $groups[] = $group['group'];
        }
        $result->closeCursor();

        return $groups;
    }

    /**
     * Obtain all groups allowed for given procedure type.
     *
     * @return array                   list of all defined groups
     */
    public function findAllGroups(): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->selectDistinct('group')
            ->from($this->getTableName());

        $groups = [];
        $result = $qb->executeQuery();
        foreach($result->fetchAll() as $group) {
            $groups[] = $group['group'];
        }
        $result->closeCursor();

        return $groups;
    }
}