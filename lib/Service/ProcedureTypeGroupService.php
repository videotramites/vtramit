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

use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureTypeGroup;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Service\ConfigService;

class ProcedureTypeGroupService extends EntityService {

    /** @var ProcedureTypeGroupMapper */
    private $procedureTypeGroupMapper;

    /** @var ProcedureTypeProtocolStepMapper */
    private $procedureTypeProtocolStepMapper;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        ProcedureTypeGroupMapper $procedureTypeGroupMapper,
        ProcedureTypeProtocolStepMapper $procedureTypeProtocolStepMapper
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $procedureTypeGroupMapper);
        $this->procedureTypeProtocolStepMapper = $procedureTypeProtocolStepMapper;
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new ProcedureTypeGroup();
    }

    /**
     * Obtain the configuration of procedure type for given type and group.
     *
     * @param int    $procedureTypeId  Identifier of the procedure type of the configuration.
     * @param string $group            Name of the group for which configuration is searched.
     * @return ResponseInfo            {result, entity}
     */
    public function findByTypeIdAndGroup(int $procedureTypeId, string $group): ResponseInfo {
        $response = new ResponseInfo();
        $entity = $this->entityMapper->findByTypeIdAndGroup($procedureTypeId, $group);
        $response->setInfo('entity', $entity);

        return $response;
    }

    /**
     * Obtain the configuration of procedure type for given type and general group (group = '').
     *
     * @param int $procedureTypeId  Identifier of the procedure type.
     * @return ResponseInfo         {result, entity}
     */
    public function findByTypeIdAndGeneralGroup(int $procedureTypeId): ResponseInfo {
        $response = new ResponseInfo();
        $entity = $this->entityMapper->findByTypeIdAndGeneralGroup($procedureTypeId);
        $response->setInfo('entity', $entity);

        return $response;
    }

    /**
     * Obtain the list of groups for given procedure type.
     *
     * @param int $procedureTypeId  the procedure type identifier for which retrieve the groups
     * @return ResponseInfo  {result, list[]}
     */
    public function findByProcedureTypeId(int $procedureTypeId): ResponseInfo {
        $list = $this->entityMapper->findByProcedureTypeId($procedureTypeId);
        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Obtain the configuration of procedure type group by joining with procedure.
     *
     * @param int|Procedure $procedure  Procedure entity or identifier.
     * @param string        $group      Name of the group for which configuration is searched.
     * @return ResponseInfo             {result, entity}
     */
    public function findByProcedureAndGroup(int|Procedure $procedure, string $group): ResponseInfo {
        $response = new ResponseInfo();
        $procedureId = is_object($procedure) ? $procedure->getId() : $procedure;

        $entity = $this->entityMapper->findByProcedureAndGroup($procedureId, $group);
        $response->setInfo('entity', $entity);

        return $response;
    }

    /**
     * Obtain the configuration of general procedure type group by joining with procedure.
     *
     * @param int|Procedure $procedure  Procedure entity or identifier.
     * @return ResponseInfo             {result, entity}
     */
    public function findByProcedureAndGeneralGroup(int|Procedure $procedure): ResponseInfo {
        $response = new ResponseInfo();
        $procedureId = is_object($procedure) ? $procedure->getId() : $procedure;

        $entity = $this->entityMapper->findByProcedureAndGeneralGroup($procedureId);
        $response->setInfo('entity', $entity);

        return $response;
    }

    /**
     * Obtain the identifier of the template to apply to the stakeholder for given procedure type and group.
     *
     * @param int    $procedureTypeId  Identifier of the procedure type of the configuration.
     * @param string $group            Name of the group for which configuration is searched.
     * @return int                     templateId
     */
    public function findNotifyStakeholderTemplateId(int $procedureTypeId, string $group): int {
        return $this->entityMapper->findNotifyStakeholderTemplateId($procedureTypeId, $group);
    }

    /**
     * Obtain the identifier of the template to apply to the staff for given procedure type and group.
     *
     * @param int    $procedureTypeId  Identifier of the procedure type of the configuration.
     * @param string $group            Name of the group for which configuration is searched.
     * @return int                     templateId
     */
    public function findNotifyStaffTemplateId(int $procedureTypeId, string $group): int {
        return $this->entityMapper->findNotifyStaffTemplateId($procedureTypeId, $group);
    }

    /**
     * Obtain the list of stakeholder message template identifiers for the given type/group.
     *
     * @param int    $procedureTypeId  The procedure type identifier.
     * @param string $group            The group identifier.
     * @return ResponseInfo            {result, list[]}
     */
    public function findNotifyStakeholderMessageTemplateIds(int $procedureTypeId, string $group): ResponseInfo {
        $response = new ResponseInfo();
        $list = $this->entityMapper->findNotifyStakeholderMessageTemplateIds($procedureTypeId, $group);
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Obtain the list of staff message template identifiers for the given type/group.
     *
     * @param int    $procedureTypeId  The procedure type identifier.
     * @param string $group            The group identifier.
     * @return ResponseInfo            {result, list[]}
     */
    public function findNotifyStaffMessageTemplateIds(int $procedureTypeId, string $group): ResponseInfo {
        $response = new ResponseInfo();
        $list = $this->entityMapper->findNotifyStaffMessageTemplateIds($procedureTypeId, $group);
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Obtain all groups allowed for given procedure type.
     *
     * @param int $procedureTypeId  Identifier of the procedure type.
     * @return ResponseInfo         {result, list[]}
     */
    public function findGroupsByProcedureTypeId(int $procedureTypeId): ResponseInfo {
        $response = new ResponseInfo();
        $list = $this->entityMapper->findGroupsByProcedureTypeId($procedureTypeId);
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Obtain all groups defined across all procedure types.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function findAllGroups(): ResponseInfo {
        $response = new ResponseInfo();
        $list = $this->entityMapper->findAllGroups();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Delete steps associated with the group before removing it.
     *
     * @param Entity $entity
     * @return ResponseInfo
     */
    protected function beforeDelete(\OCP\AppFramework\Db\Entity $entity): ResponseInfo {
        $response = new ResponseInfo();
        $groupId = (int) $entity->getId();
        if ($groupId > 0) {
            $this->procedureTypeProtocolStepMapper->deleteByProcedureTypeGroupId($groupId);
        }

        return $response;
    }


}
