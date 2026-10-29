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
use OCA\VTramit\Exception\EntityNotFoundException;
use OCA\VTramit\Helper\BeHelper;

class TopicMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::TOPIC, Topic::class, 'topic');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return Topic
     * @throws EntityNotFoundException
     */
    public function find(int $id): Topic {
        return parent::find($id);
    }

    /**
     * Obtain all procedures for the topic
     *
     * @param string $name   name of the topic
     * @return array
     */
    public function findByName(string $name): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();

        $qb->select('a.*')
            ->from($this->getTableName(), 'a')
            ->where(
                $qb->expr()->iLike(
                    'a.name',
                    $qb->createNamedParameter('%' . $name . '%', IQueryBuilder::PARAM_STR)
                )
            );

        return $this->findEntities($qb);
    }

    /**
     * Get filtered topcis.
     *
     * Valid values for $filter:
     * 'name'           topic name
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
        $qb->select('*')
            ->from($this->getTableName());

        if (!empty($filter['name'])) {
            $name = '%'.trim($filter['name']).'%';
            $qb->andWhere(
                $qb->expr()->iLike(
                    'name',
                    $qb->createNamedParameter($name, IQueryBuilder::PARAM_STR)
                )
            );
        }

        return $this->findEntities($qb);
    }
}
