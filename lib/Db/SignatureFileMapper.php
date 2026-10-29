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

class SignatureFileMapper extends EntityMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::SIGNATURE_FILE, SignatureFile::class, 'signature file');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return SignatureFile
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): SignatureFile {
        return parent::find($id);
    }

    /**
     * @param int $signatureId
     * @return array
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    public function findBySignature(int $signatureId, bool $includeDeleted = false): array {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::SIGNATURE_FILE)
            ->where($qb->expr()->eq('signature_id', $qb->createNamedParameter($signatureId, IQueryBuilder::PARAM_INT)));

        if (!$includeDeleted) {
            $qb->andWhere($qb->expr()->eq('deleted', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)));
        }

        return $this->findEntities($qb);
    }

    /**
     * Find signature file by signature and file ids.
     *
     * @param int $signatureId
     * @param int $fileId
     * @return SignatureFile
     * @throws \OCA\VTramit\Exception\EntityNotFoundException
     */
    public function findBySignatureAndFile(int $signatureId, int $fileId, bool $includeDeleted = false): SignatureFile {
        /* @var $qb IQueryBuilder */
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from(Tables::SIGNATURE_FILE)
            ->where($qb->expr()->eq('signature_id', $qb->createNamedParameter($signatureId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('file_id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)))
            ->setMaxResults(1);

        if (!$includeDeleted) {
            $qb->andWhere($qb->expr()->eq('deleted', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)));
        }

        return $this->findEntity($qb);
    }
}
