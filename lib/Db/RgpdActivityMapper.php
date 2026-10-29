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
use OCP\AppFramework\Db\Entity;
use OCP\DB\QueryBuilder\IQueryBuilder;

use OCA\VTramit\Db\Tables;
use OCA\VTramit\Exception\EntityNotFoundException;
use OCA\VTramit\Exception\InvalidOperation;

class RgpdActivityMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::RGPD_ACTIVITY, RgpdActivity::class, 'RGPD activity');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return RgpdActivity
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): RgpdActivity {
        return parent::find($id);
    }

    /**
     * Overrides the entity update to avoid any updates on the RGPD entities.
     */
    public function update(Entity $entity): Entity {
        throw new InvalidOperation("RGPD activity is not allowed to be updated");
    }

    /**
     * Obtain the last activity for a given stakeholder in a procedure. If none
     * is found, a null entity is returned.
     *
     * @param int $procedureId             the procedure for which the operation is taken
     * @param int $procedureStakeholderId  the stakeholder doing the operations
     * @return Entity|null                 the last activity, if any
     */
    public function findLastByProcedureStakeholder(int $procedureId, int $procedureStakeholderId): ?Entity {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->andWhere($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('procedure_stakeholder_id', $qb->createNamedParameter($procedureStakeholderId, IQueryBuilder::PARAM_INT)))
            ->orderBy('date', 'DESC')
            ->setMaxResults(1)
        ;

        try {
            return $this->findEntity($qb);
        }
        catch (EntityNotFoundException $e) {
            return null;
        }
    }
}