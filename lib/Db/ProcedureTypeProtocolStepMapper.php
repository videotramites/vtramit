<?php
/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia BenÃ­tez <letizia@videoatencion.com>
 * @author Daniel Tamajon <dtamajon@litesolutions.es>
 * @author Marta GonzÃ¡lez <marta.gonzalez@litesolutions.es>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan ReynÃ© <ivanreyne@gmail.com>
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

class ProcedureTypeProtocolStepMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::PROCEDURE_TYPE_PROTOCOL_STEP, ProcedureTypeProtocolStep::class, 'procedure type protocol step');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return ProcedureTypeProtocolStep
     */
    public function find(int $id): ProcedureTypeProtocolStep {
        return parent::find($id);
    }

    /**
     * Obtain all steps for given procedure type group.
     *
     * @param int $procedureTypeGroupId  identifier of the procedure type group
     * @return ProcedureTypeProtocolStep[]
     */
    public function findByProcedureTypeGroupId(int $procedureTypeGroupId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('procedure_type_group_id', $qb->createNamedParameter($procedureTypeGroupId, IQueryBuilder::PARAM_INT)))
            ->orderBy('order', 'ASC')
            ->addOrderBy('id', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * Delete all steps for given procedure type group.
     *
     * @param int $procedureTypeGroupId  identifier of the procedure type group
     * @return int  number of affected rows
     */
    public function deleteByProcedureTypeGroupId(int $procedureTypeGroupId): int {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())
            ->where($qb->expr()->eq('procedure_type_group_id', $qb->createNamedParameter($procedureTypeGroupId, IQueryBuilder::PARAM_INT)));

        return $qb->executeStatement();
    }
}
