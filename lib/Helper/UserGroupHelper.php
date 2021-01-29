<?php
/**
 * @copyright Copyright (c) 2020-2021 Ajuntament de Barcelona
 * 
 * @author Daniel Tamajon <daniel@floss.cat>
 * @author Kenneth Peiruza <kenneth@floss.cat>
 * @author Letizia Benítez <letizia@floss.cat>
 * @author Marta González <gonzalez.marta@gmail.com>
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

namespace OCA\VTramit\Helper;

use OC\Accounts\AccountManager;
use OCA\Provisioning_API\Controller\UsersController;
use OCA\Provisioning_API\Controller\GroupsController;
use OCP\ILogger;
use OCP\IGroupManager;
use OCP\IUserManager;

use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCA\VTramit\Service\ConfigService;

class UserGroupHelper {

    /** @var ILogger */
    private $logger;

    /** @var UsersController */
    private $userController;

    /** @var IUserManager */
    private $userManager;

    /** @var GroupsController */
    private $groupsController;

    /** @var IGroupManager|\OC\Group\Manager */ // FIXME Requires a method that is not on the interface
    protected $groupManager;
    
    /** @var AccountManager */
    protected $accountManager;

    /** @var ConfigService */
    private $configService;

    public function __construct(
        ILogger $logger,
        UsersController $userController,
        IUserManager $userManager,
        GroupsController $groupsController,
        IGroupManager $groupManager,
        AccountManager $accountManager,
        ConfigService $configService
    ) {
        $this->logger = $logger;
        $this->userController = $userController;
        $this->userManager = $userManager;
        $this->groupsController = $groupsController;
        $this->groupManager = $groupManager;
        $this->accountManager = $accountManager;
        $this->configService = $configService;
    }

    /**
     * Get the list of users in a given group that can be viewed by given user.
     * Here 'deparment' is a synonymous of 'group'.
     *
     * @param string $group   group from which obtain users
     * @param string $userId  identifier of user for which info is retrieved
     * return array           list of users viewable in the group
     */
    public function getAssignableUsersByGroup(string $group, string $userId) {
        if ($this->canUserDoAssignments($userId)) {
            $users = $this->getUsersByGroup($group, true);
            if (!empty($users)) {
                return $users;
            }
        }

        $users = [];
        if ($this->isUserInGroup($userId, $group)) {
            $user = $this->userController->getUser($userId);
            $data = $user->getData();
            $users[$userId] = $data['displayname'];
        }

        return $users;
    }

    /**
     * Get the list of users in a given group. By default, a restriction check is done
     * so list of all users is returned only for users with admin permissions.
     *
     * @param string $group                   group from which obtain users
     * @param bool   $avoidAdminRestrictions  true to return all users, even if current user has
     *                                        no permissions to see them
     * return array                           list of users in the group
     */
    public function getUsersByGroup(string $group, bool $avoidAdminRestrictions = false) {
        $users = [];

        try {
            if ($avoidAdminRestrictions) {
                // When admin restrictions control must be avoided, we need to invoke 
                // custom functions. Nextcloud core requires a user control.
                $groupUsers = $this->getGroupUsersDetails($group);
                foreach($groupUsers as $id => $data) {
                    $users[$id] = $data['displayname'];
                }
            }
            else {
                // When admin restrictions control is required, we can invoke Nextcloud
                // functions.
                $groupUsersData = $this->groupsController->getGroupUsersDetails($group);
                if (!empty($groupUsersData) && !empty($groupUsersData->getData())) {
                    $groupUsers = $groupUsersData->getData();
                    foreach($groupUsers['users'] as $id => $data) {
                        if (array_key_exists('displayname', $data)) {
                            $users[$id] = $data['displayname'];
                        }
                        else {
                            $users[$id] = $id;
                        }
                    }
                }
            }
        }
        // Exceptions are ignored because are related to non-existance of the group
        // or because the user has no permissions to access to it. In both cases,
        // the list to return must be empty, with no errors, as the user cannot view
        // that information.
        catch(\OCP\AppFramework\OCS\OCSException $e) {}
        catch(Exception $e) {}

        asort($users, SORT_NATURAL | SORT_FLAG_CASE);
        return $users;
    }

    /**
     * Returns an array of users details in the specified group.
     * 
     * This is a customized function from Nextcloud core found in class
     * OCA\Provisioning_API\Controller\GroupsController which is defined in:
     * apps/provisioning_api/lib/Controller/GroupsController.php
     *
     * @NoAdminRequired
     *
     * @param string $groupId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @return DataResponse
     * @throws OCSException
     */
    private function getGroupUsersDetails(string $groupId, string $search = '', int $limit = null, int $offset = 0): array {
        $groupId = urldecode($groupId);

        // Check the group exists
        $group = $this->groupManager->get($groupId);
        if ($group === null) {
            throw new OCSException('The requested group could not be found', \OCP\API::RESPOND_NOT_FOUND);
        }

        $users = $group->searchUsers($search, $limit, $offset);

        // Extract required number
        $usersDetails = [];
        foreach ($users as $user) {
            try {
                /** @var IUser $user */
                $userId = (string)$user->getUID();
                $userData = $this->getUserData($userId);
                // Do not insert empty entry
                if (!empty($userData)) {
                    $usersDetails[$userId] = $userData;
                } else {
                    // Logged user does not have permissions to see this user
                    // only showing its id
                    $usersDetails[$userId] = ['id' => $userId];
                }
            } catch (OCSNotFoundException $e) {
                // continue if a users ceased to exist.
            }
        }
        return $usersDetails;
    }
    
    /**
     * Creates a array with all user data.
     * 
     * This is a customized function from Nextcloud core found in class
     * OCA\Provisioning_API\Controller\AUserData which is defined in:
     * apps/provisioning_api/lib/Controller/AUserData.php
     * 
     * This function is not returning all the information calculated by the 
     * original function as it is not required by the app.
     *
     * @param string $userId
     * @return array
     * @throws NotFoundException
     * @throws OCSException
     * @throws OCSNotFoundException
     */
    private function getUserData(string $userId): array {
        $data = [];

        // Check if the target user exists
        $targetUserObject = $this->userManager->get($userId);
        if ($targetUserObject === null) {
            throw new OCSNotFoundException('User does not exist');
        }

        // Get groups data
        $userAccount = $this->accountManager->getUser($targetUserObject);
        $groups = $this->groupManager->getUserGroups($targetUserObject);
        $gids = [];
        foreach ($groups as $group) {
            $gids[] = $group->getGID();
        }
        
        $data['id'] = $targetUserObject->getUID();
        $data['lastLogin'] = $targetUserObject->getLastLogin() * 1000;
        $data[AccountManager::PROPERTY_EMAIL] = $targetUserObject->getEMailAddress();
        $data[AccountManager::PROPERTY_DISPLAYNAME] = $targetUserObject->getDisplayName();
        $data[AccountManager::PROPERTY_PHONE] = $userAccount[AccountManager::PROPERTY_PHONE]['value'];
        $data[AccountManager::PROPERTY_ADDRESS] = $userAccount[AccountManager::PROPERTY_ADDRESS]['value'];
        $data[AccountManager::PROPERTY_WEBSITE] = $userAccount[AccountManager::PROPERTY_WEBSITE]['value'];
        $data[AccountManager::PROPERTY_TWITTER] = $userAccount[AccountManager::PROPERTY_TWITTER]['value'];
        $data['groups'] = $gids;

        return $data;
    }

    /**
     * Get the list of groups allowed for the user.
     *
     * @param string $userId  user identifier
     * return array           list of allowed groups
     */
    public function getAllowedGroupsForUser(string $userId) {
        $allowed = $this->configService->getGroups();
        $groups = $this->getUserGroups($userId);

        if (in_array('admin', $groups)) {
            return $allowed;
        }

        return array_intersect($allowed, $groups);
    }

    /**
     * Get the list of groups which belongs the given user.
     *
     * @param string $userId  user identifier
     * return array           list of groups
     */
    public function getUserGroups(string $userId) {
        $user = $this->userController->getUser($userId);
        $data = $user->getData();
        return $data['groups'];
    }

    /**
     * Indicates if given user is in given group.
     *
     * @param string $userId  user identifier
     * @param string $group   group
     * return boolean         true if user is in group; otherwise, false
     */
    public function isUserInGroup(string $userId, string $group) {
        $groups = $this->getUserGroups($userId);
        return in_array($group, $groups);
    }

    /**
     * Indicates if given user is admin.
     *
     * @param string $userId  user identifier
     * return boolean         true if user is admin; otherwise, false
     */
    public function isAdmin(string $userId) {
        $groups = $this->getUserGroups($userId);
        return in_array('admin', $groups);
    }

    /**
     * Check if user can do assignments of appointments to another users.
     *
     * @param string $userId  user identifier
     * return bool            true if user can do assignments; otherwise, false
     */
    public function canUserDoAssignments(string $userId) {
        $groups = $this->getUserGroups($userId);
        if (in_array('admin', $groups)) {
            return true;
        }

        $groupLimit = $this->configService->getGroupLimit();

        foreach($groupLimit as $limit) {
            foreach($groups as $group) {
                if ($limit['id'] == $group) {
                    return true;
                }
            }
        }
        return false;
    }


    //
    // Validations
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Indicates wether given email has a valid format.
     *
     * @param string $email  email to validate
     * @return bool          true if given email has a valid format; otherwise, false
     */
    public function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    //
    // Functions for random
    ////////////////////////////////////////////////////////////////////////////////

    public function getRandomPIN() {
        $pin = rand(10000000, 99999999);
        return $pin;
    }

    public function getRandomPhone() {
        $phone = rand(100000000, 999999999);
        return $phone;
    }

    /**
     * Obtains a random string
     *
     * @param int    $length_of_string      length of the string to return
     * @param string $additionalCharacters  additional characters to use (by default only numbers and letters are used)
     * @return string                       random string
     */
    public function random_string($length_of_string, $additionalCharacters = '') {
        // String of all alphanumeric character
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$additionalCharacters;

        // Shufle the $str_result and returns substring
        // of specified length
        return substr(str_shuffle($str_result),
                        0, $length_of_string);
    }
}