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

use OCA\VTramit\AttendanceChannel;
use OCA\VTramit\Constants;
use OCA\VTramit\Language;
use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\SignatureStatus;
use OCA\VTramit\Enum\MessageChannel;

use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Exception\InvalidIdException;

use OCA\VTramit\Helper\BeHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Service\ConfigService;

class ContextService {

    /** @var LoggerInterface */
    protected $logger;

    /** @var IL10N */
    protected $translate;

    /** @var ProcedureTypeGroupMapper */
    private $procedureTypeGroupMapper;

    /** @var UserGroupHelper */
    private $userGroupHelper;

    /** @var ConfigService */
    private $configService;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ProcedureTypeGroupMapper $procedureTypeGroupMapper,
        UserGroupHelper $userGroupHelper,
        ConfigService $configService
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->procedureTypeGroupMapper = $procedureTypeGroupMapper;
        $this->userGroupHelper = $userGroupHelper;
        $this->configService = $configService;
    }

    /**
     * Get the list of groups in the system.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function getGroupsList(): ResponseInfo {
        /*$groups = $this->procedureTypeGroupMapper->findAllGroups();

        $formattedGroups = [];
        foreach($groups as $key => $value) {
            $formattedGroups[] = ["key" => $value, "value" => $value];
        }

        $response = new ResponseInfo();
        $response->setInfo('list', $formattedGroups);

        return $response;*/

        $groups = $this->userGroupHelper->getAllGroups();

        $formattedGroups = [];
        foreach($groups as $key => $value) {
            $formattedGroups[] = ["key" => $value, "value" => $value];
        }

        $response = new ResponseInfo();
        $response->setInfo('list', $formattedGroups);

        return $response;
    }

    /**
     * Get the detailed list of available procedure status.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function getProcedureStatusList(): ResponseInfo {
        $status = ProcedureStatus::getDetails();

        $response = new ResponseInfo();
        $response->setInfo('list', $status);

        return $response;
    }

    /**
     * Get the detailed list of available signature status.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function getSignatureStatusList(): ResponseInfo {
        $status = SignatureStatus::getDetails($this->translate);

        $response = new ResponseInfo();
        $response->setInfo('list', $status);

        return $response;
    }

    /**
     * Get the detailed list of available channels.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function getAttendanceChannelList(): ResponseInfo {
        $status = AttendanceChannel::getDetails($this->translate);

        $response = new ResponseInfo();
        $response->setInfo('list', $status);

        return $response;
    }

        /**
     * Get the detailed list of available message channels.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function getMessageChannels(): ResponseInfo {
        $channels = MessageChannel::getDetails($this->translate);

        $response = new ResponseInfo();
        $response->setInfo('list', $channels);

        return $response;
    }

    /**
     * Get the detailed list of supported languages.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function getSupportedLanguageList(): ResponseInfo {
        $status = Language::getDetails($this->translate);

        $response = new ResponseInfo();
        $response->setInfo('list', $status);

        return $response;
    }

    /**
     * Get the app context for the user.
     *
     * @param int $userId    user identifier in the context
     * @return ResponseInfo  {result, groups[], users[], isAdmin}
     */
    public function getAppContext(string $userId): ResponseInfo {
        $appGroups = [];
        $appUsers = [];

        $allGroups = $this->procedureTypeGroupMapper->findAllGroups();
        $groupsByUser = $this->userGroupHelper->getAllowedGroupsForUser($userId, $allGroups);
        $groupsByProcedure = $this->procedureTypeGroupMapper->findAllGroups();
        $groups = array_intersect($groupsByUser, $groupsByProcedure);

        foreach($groups as $group) {
            $usersInGroup = $this->userGroupHelper->getAssignableUsersByGroup($group, $userId);
            $appUsers = $appUsers + $usersInGroup; // Avoid array_merge because we don't want to loss key index
            $appGroups[$group] = $group;
        }

        asort($appGroups, SORT_NATURAL | SORT_FLAG_CASE);
        asort($appUsers, SORT_NATURAL | SORT_FLAG_CASE);

        $response = new ResponseInfo();
        $response->setInfo('timezone', $this->configService->getDateTimezone()->getName());
        $response->setInfo('groups', BeHelper::arrayToKeyValue($appGroups));
        $response->setInfo('users', BeHelper::arrayToKeyValue($appUsers));
        $response->setInfo('isAdmin', $this->userGroupHelper->isAdmin($userId));
        $response->setInfo('phoneLink', $this->configService->getPhoneLink());
        $response->setInfo('mode', $this->configService->getMode());

        return $response;
    }
}