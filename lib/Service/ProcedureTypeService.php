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

use OCA\VTramit\Db\ProcedureType;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Helper\BeHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Service\ConfigService;

class ProcedureTypeService extends EntityService {

    /** @var ProcedureTypeGroupMapper */
    private $procedureTypeGroupMapper;

    /** @var UserGroupHelper */
    private $userGroupHelper;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ProcedureTypeMapper $procedureTypeMapper,
        ProcedureTypeGroupMapper $procedureTypeGroupMapper,
        UserGroupHelper $userGroupHelper,
        ConfigService $configService
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $procedureTypeMapper);
        $this->procedureTypeGroupMapper = $procedureTypeGroupMapper;
        $this->userGroupHelper = $userGroupHelper;
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new ProcedureType();
    }


    //
    // Context
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the procedure context for the user.
     *
     * @param int $procedureTypeId  identifier of the procedure type for which the context is retrieved
     * @param int $userId           user identifier in the context
     * @return ResponseInfo  {result, groups[]}
     */
    public function getContext(int $procedureTypeId, string $userId): ResponseInfo {
        $procedureGroups = [];

        $procedureType = $this->entityMapper->find($procedureTypeId);
        $procedureGroupsToProcess = $this->procedureTypeGroupMapper->findGroupsByProcedureTypeId($procedureTypeId);

        $groupsByUser = $this->userGroupHelper->getAllowedGroupsForUser($userId, $procedureGroupsToProcess);
        if (!is_array($groupsByUser)) {
            $groupsByUser = [];
        }
        $groupsByProcedure = $this->procedureTypeGroupMapper->findGroupsByProcedureTypeId($procedureType->getId());

        $groups = array_intersect($groupsByUser, $groupsByProcedure);
        $procedureTypeGroupList = $this->getProcedureTypeGroupForUserGroups($procedureTypeId, $groups);
        $allowCreate = !empty($procedureTypeGroupList);
        $allowAssignGroup = false;
        $allowAssignUser = false;
        $bypassRequired = false;
        foreach($procedureTypeGroupList as $procedureTypeGroup) {
            if ($procedureTypeGroup->getAllowAssignGroup()) {
                $allowAssignGroup = true;
            }

            if ($procedureTypeGroup->getAllowAssignUser()) {
                $allowAssignUser = true;
            }

            if ($procedureTypeGroup->getBypassRequired()) {
                $bypassRequired = true;
            }
        }

        foreach($groups as $group) {
            $usersInGroup = $this->userGroupHelper->getAssignableUsersByGroup($group, $userId, $allowAssignUser);
            $procedureGroups[$group] = BeHelper::arrayToKeyValue($usersInGroup);
        }

        if (!empty($procedureGroups) && is_array($procedureGroups)) {
            ksort($procedureGroups, SORT_NATURAL | SORT_FLAG_CASE);
        }

        $formattedProcedureGroups = [];
        foreach($procedureGroups as $key => $value) {
            $formattedProcedureGroups[] = ["key" => $key, "value" => $key, "users" => $value];
        }

        $response = new ResponseInfo();
        $response->setInfo('groups', $formattedProcedureGroups);
        $response->setInfo('channels', $procedureType->getAllowedAttendanceChannelList());
        $response->setInfo('allowCreate', $allowCreate);
        $response->setInfo('allowAssignGroup', $allowAssignGroup);
        $response->setInfo('allowAssignUser', $allowAssignUser);
        $response->setInfo('bypassRequired', $bypassRequired);

        return $response;
    }

    /**
     * Get the procedure type groups that matches given groups.
     *
     * @param int   $procedureTypeId  identifier of the procedure type for which obtain matching groups
     * @param array $groups           list of groups to match
     * @return array                  list of procedure type groups matching given groups in the procedure type
     */
    private function getProcedureTypeGroupForUserGroups(int $procedureTypeId, array $groups) {
        $procedureTypeGroupList = $this->procedureTypeGroupMapper->findByProcedureTypeId($procedureTypeId);
        $intersection = [];

        foreach($procedureTypeGroupList as $procedureTypeGroup) {
            if (in_array($procedureTypeGroup->getGroup(), $groups)) {
                $intersection[] = $procedureTypeGroup;
            }
        }

        return $intersection;
    }


    //
    // Main actions
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get procedure type for given procedure ID.
     *
     * @param string $procedureId  the ID of the procedure
     * @return ResponseInfo        {result, entity}
     */
    public function findByProcedureId(int $procedureId): ResponseInfo {
        $procedureType = $this->entityMapper->findByProcedureId($procedureId);
        $response = new ResponseInfo();
        $response->setInfo('entity', $procedureType);

        return $response;
    }

    /**
     * Get filtered procedures.
     *
     * Valid values for $filter:
     * 'groups'           array of groups which belongs the procedures; if no
     *                    group is assigned, all the user groups will be used
     * 'allowCreate'      the user is in a group for which is allowed to create the procedure type
     * 'allowAssignGroup' the user is in a group for which is allowed to assign the group in the procedure type
     * 'allowAssignUser'  the user is in a group for which is allowed to assign the user in the procedure type
     *
     * Examples of usage:
     *
     * 1.- Obtain procedure types that can be created by given user
     * findByFilter({"filter": {"allowCreate": true}}, $userId)
     *
     *
     * @param array  $filter           filter parameters as array or JSON
     * @param string $userId           identifier of user executing the operation
     * @return ResponseInfo  {result, list[]}
     */
    public function findByFilter(array $filter, string $userId = null): ResponseInfo {
        $response = new ResponseInfo();
        $timezone = $this->configService->getDateTimezone();


        // Ensure to filter by user groups
        if (empty($filter['groups']) && !empty($userId)) {
            $allGroups = $this->procedureTypeGroupMapper->findAllGroups();
            $filter['groups'] = $this->userGroupHelper->getAllowedGroupsForUser($userId, $allGroups);
        }

        if (empty($filter['groups']) && !empty($userId)) {
            $response->setInfo('list', []);
            return $response;
        }

        $list = $this->entityMapper->findByFilter($filter);
        $response->setInfo('list', $list);
        return $response;
    }

    /**
     * Operations to apply before delete entity.
     *
     * @param Entity $entity  the entity to delete
     * @return bool           indicates wether the entity can be deleted or not
     */
    protected function beforeDelete(Entity $entity): ResponseInfo {
        $response = new ResponseInfo();
        $groups = $this->procedureTypeGroupMapper->findGroupsByProcedureTypeId($entity->getId());
        if (!empty($groups)) {
            $response->setError($this->translate->t('The entity cannot be deleted because there are related groups associated'));
        }

        return $response;
    }
}