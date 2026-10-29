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
use OCP\DB\QueryBuilder\IQueryBuilder;

use OCA\VTramit\Db\Tables;
use OCA\VTramit\Exception\EntityNotFoundException;

class WebhookMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::WEBHOOK, Webhook::class, 'webhook');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return Webhook
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): Webhook {
        return parent::find($id);
    }

    /**
     * Obtain all procedures for the relevant operation
     *
     * @param string $operation   name of the operation
     * @return array
     */
    public function findByOperation(string $operation): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->where('a.event LIKE (\'%'. $operation.'%\')');

        return $this->findEntities($qb);
    }
}