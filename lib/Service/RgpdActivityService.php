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

namespace OCA\VTramit\Service;

use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\AppFramework\Db\Entity;

use OCA\VTramit\Db\RgpdActivity;
use OCA\VTramit\Db\RgpdActivityMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Service\ConfigService;

class RgpdActivityService extends EntityService {

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        RgpdActivityMapper $rgpdActivityMapper,
        ConfigService $configService
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $rgpdActivityMapper);
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new RgpdActivity();
    }

    /**
     * Operations to apply before create entity.
     *
     * @param Entity|RgpdActivity $entity  the entity object being created
     * @param string              $userId  identifier of user executing the operation
     * @return Entity|RgpdActivity         the entity object being created with any applied modification
     */
    protected function beforeCreate(Entity $entity, string $userId): Entity {
        $now = $this->configService->getDateTime();
        $entity->setDate($now);
        $entity->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        $entity->setIp($_SERVER['REMOTE_ADDR']);

        return $entity;
    }

    /**
     * Update existing entity using JSON data.
     *
     * @param array  $newData  entity data in JSON format
     * @param string $userId   identifier of user executing the operation
     * @return ResponseInfo  {result, message, ?entity}
     */
    public function update(array $newData, string $userId): ResponseInfo {
        $response = new ResponseInfo();
        $response->setError($this->translate->t('RGPD activity is not allowed to be updated'));

        return $response;
    }

    /**
     * Obtain the last activity for a given stakeholder in a procedure. If none
     * is found, a null entity is returned.
     *
     * @param int $procedureId             the procedure for which the operation is taken
     * @param int $procedureStakeholderId  the stakeholder doing the operations
     * @return Entity|null                 the last activity, if any
     */
    public function findLastByProcedureStakeholder(int $procedureId, int $procedureStakeholderId): ResponseInfo {
        $response = new ResponseInfo();

        $this->timeLogger->startTime('findLastByProcedureStakeholder');
        $entity = $this->entityMapper->findLastByProcedureStakeholder($procedureId, $procedureStakeholderId);
        $this->timeLogger->logTime('findLastByProcedureStakeholder', 'Find last RGPD by procedure and stakeholder');

        if (!empty($entity)) {
            $response->setInfo('entity', $entity);
        }

        return $response;
    }
}