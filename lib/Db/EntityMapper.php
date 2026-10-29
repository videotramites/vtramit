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
use OCA\VTramit\Exception\EntityNotFoundException;

class EntityMapper extends QBMapper {
    protected $entityName;

    public function __construct(IDBConnection $db, string $tableName, string $entityClass, string $entityName) {
        parent::__construct($db, $tableName, $entityClass);
        $this->entityName = $entityName;
    }

    public function beginTransaction(): void {
        $this->db->beginTransaction();
    }

    public function commitTransaction(): void {
        $this->db->commit();
    }

    public function rollBackTransaction(): void {
        $this->db->rollBack();
    }

    public function getEntityName(): string {
        return $this->entityName;
    }

    /**
     * Returns an db result and throws exceptions when there are more or less
     * results
     *
     * @param IQueryBuilder $query
     * @return Entity the entity
     * @throws EntityNotFoundException if the item does not exist or more than one item exist
     */
    protected function findEntity(IQueryBuilder $query): Entity {
        try {
            return parent::findEntity($query);
        }
        catch (DoesNotExistException $e) {
            throw new EntityNotFoundException(sprintf('Requested %s not found', $this->entityName));
        }
        catch (MultipleObjectsReturnedException $e) {
            throw new EntityNotFoundException(sprintf('More than one %s found while expecting only one', $this->entityName));
        }
    }

    /**
     * Obtain all existing procedure types.
     *
     * @return array
     */
    public function findAll(): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName());

        return $this->findEntities($qb);
    }

    /**
     * Obtain the procedure type given its unique identifier.
     *
     * @param int $id
     * @return Entity
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): Entity {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * Delete all the procedure types by given identifiers.
     *
     * @param array|int  $id  a procedure type indentier or a list of
     *                        procedure types indentifiers to delete
     */
    public function deleteById($id) {
        if (!is_array($id)) {
            $id = array($id);
        }

        foreach($id as $_id) {
            $list = $this->find($_id);
            foreach($list as $element) {
                $this->delete($element);
            }
        }
    }

    /**
     * Delete all stored entities in database.
     */
    public function deleteAll() {
        $entities = $this->findAll();
        foreach($entities as $entity) {
            $this->delete($entity);
        }
    }
}
