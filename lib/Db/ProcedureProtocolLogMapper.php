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

class ProcedureProtocolLogMapper extends EntityMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::PROCEDURE_PROTOCOL_LOG, ProcedureProtocolLog::class, 'procedure protocol log');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return ProcedureProtocolLog
     */
    public function find(int $id): ProcedureProtocolLog {
        return parent::find($id);
    }

    /**
     * Obtain all log entries for a given procedure.
     *
     * @param int $procedureId  identifier of the procedure
     * @return ProcedureProtocolLog[]
     */
    public function findByProcedureId(int $procedureId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->orderBy('created_at', 'ASC')
            ->addOrderBy('id', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * Check whether a step has already been completed for a given procedure.
     *
     * @param int $procedureId  identifier of the procedure
     * @param int $stepId       identifier of the protocol step
     * @return bool
     */
    public function existsByProcedureIdAndStepId(int $procedureId, int $stepId): bool {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('id')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('proc_type_protocol_step_id', $qb->createNamedParameter($stepId, IQueryBuilder::PARAM_INT)))
            ->setMaxResults(1);

        $result = $qb->executeQuery();
        $row = $result->fetch();
        $result->closeCursor();

        return $row !== false;
    }
}
