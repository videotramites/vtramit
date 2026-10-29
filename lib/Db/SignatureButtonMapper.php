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

class SignatureButtonMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::SIGNATURE_BUTTONS, SignatureButton::class, 'signature button type');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return SignatureType
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): SignatureButton {
        return parent::find($id);
    }

    /**
     * Obtain all groups allowed for given procedure type.
     *
     * @param int    $procedureTypeId  identifier of the procedure type
     * @return SignatureButton      configuration for the status and group
     * @throws EntityNotFoundException
     */
    public function findSignatureButtonByProcedureTypeId(int $procedureTypeId): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('procedure_type_id', $qb->createNamedParameter($procedureTypeId, IQueryBuilder::PARAM_INT)));

        return $entity = $this->findEntities($qb);
    }
}