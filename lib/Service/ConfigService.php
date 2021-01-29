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

namespace OCA\VTramit\Service;

use OCP\IConfig;
use OCP\IGroupManager;

class ConfigService {

    // Constants
    public const DIRECTORY_SEPARATOR = '/';

    private $groupManager; 
    private $config;

    public function __construct(
        IConfig $config,
        IGroupManager $groupManager
    ) {
        $this->config = $config;
        $this->groupManager = $groupManager;
    }

    public function get() {
        $data = [
            'groupLimit' => $this->getGroupLimit(),
        ];
        return $data;
    }

    public function setValue($key, $value) {
        switch ($key) {
            case 'groupLimit':
                return $this->setGroupLimit($value);
                break;
        }

        return null;
    }

    private function getAppName() {
        return 'vtramit';
    }

    public function getDataDirectory() {
        return $this->config->getSystemValue('datadirectory', \OC::$SERVERROOT.'/data');
    }

    private function setGroupLimit($value) {
        $groups = [];
        foreach ($value as $group) {
            $groups[] = $group['id'];
        }
        $data = implode(',', $groups);
        $this->config->setAppValue($this->getAppName(), 'groupLimit', $data);
        return $groups;
    }

    private function getGroupLimitList() {
        $value = $this->config->getAppValue($this->getAppName(), 'groupLimit', '');
        $groups = explode(',', $value);
        if ($value === '') {
            return [];
        }
        return $groups;
    }

    public function getGroupLimit() {
        $groups = $this->getGroupLimitList($this->getAppName());
        $groups = array_map(function ($groupId) {
            /** @var IGroup $groups */
            $group = $this->groupManager->get($groupId);
            if ($group === null) {
                return null;
            }
            return [
                'id' => $group->getGID(),
                'displayname' => $group->getDisplayName(),
            ];
        }, $groups);
        return array_filter($groups);
    }

    

    public function getDateFormat() {
        return $this->config->getSystemValue('vtramit.general.dateFormat', 'd/m/Y');
    }

    public function getTimeFormat() {
        return $this->config->getSystemValue('vtramit.general.timeFormat', 'H:i');
    }

    public function getAdminUser() {
        return $this->config->getSystemValue('vtramit.admin', 'admin');
    }

    public function getAdminRoot() {
        return $this->getAdminUser().self::DIRECTORY_SEPARATOR.'files'.self::DIRECTORY_SEPARATOR;
    }

    public function getTrashRoot() {
        return 'files_trashbin'.self::DIRECTORY_SEPARATOR;
    }

    public function getGroups() {
        return $this->config->getSystemValue('vtramit.groups', []);
    }

    public function getFolderUploadsName() {
        return $this->config->getSystemValue('vtramit.folder.upload', 'Entrada');
    }

    public function getFolderDownloadsName() {
        return $this->config->getSystemValue('vtramit.folder.download', 'Sortida');
    }

    public function isAllowSendEmails() {
        return $this->config->getSystemValue('vtramit.mails.allowed', true);
    }

    /**
     * Url without trailing "/"
     */
    public function getJitsiCitizenUrl() {
        return $this->config->getSystemValue('vtramit.jitsi.citizen.url', '');
    }

    /**
     * Url without trailing "/"
     */
    public function getJitsiStaffUrl() {
        return $this->config->getSystemValue('vtramit.jitsi.staff.url', '');
    }

    /**
     * Phone settings
     */
    public function getPhoneLink() {
        return $this->config->getSystemValue('vtramit.phone.link', 'ciscotel');
    }

    public function getPhonePrefix() {
        return $this->config->getSystemValue('vtramit.phone.prefix', '+34');
    }

    

    /**
     * History
     */
    public function getHistoryDays() {
        $days = $this->config->getSystemValue('vtramit.history.days', 2);
        return is_int($days) ? $days : 2;
    }



    /**
     * Group settings
     */
    protected function getGroupSettings($group) {
        $data = $this->config->getSystemValue('vtramit.group.settings', []);
        $settings = array_key_exists($group, $data) ? $data[$group] : [];
        $default = array_key_exists('default', $data) ? $data['default'] : [];

        return array_merge($default, $settings);
    }

    protected function getGroupAttribute($group, $attribute, $default) {
        $settings = $this->getGroupSettings($group);
        if (!empty($settings) && !empty($settings[$attribute])) {
            return $settings[$attribute];
        }

        return $default;
    }

    public function getGroupFullname($group) {
        return $this->getGroupAttribute($group, 'fullname', "");
    }

    public function getGroupAddress($group) {
        return $this->getGroupAttribute($group, 'address', "");
    }

    public function getGroupZip($group) {
        return $this->getGroupAttribute($group, 'cp', "");
    }

    public function getGroupPhone($group) {
        return $this->getGroupAttribute($group, 'phone', "");
    }



    /**
     * Group forms
     */
    protected function getGroupForms($group) {
        $data = $this->config->getSystemValue('vtramit.group.forms', []);
        $forms = array_key_exists($group, $data) ? $data[$group] : [];
        $default = array_key_exists('default', $data) ? $data['default'] : [];

        return array_merge($default, $forms);
    }

    protected function getGroupForm($group, $form) {
        $settings = $this->getGroupForms($group);
        if (!empty($settings) && !empty($settings[$form])) {
            return $settings[$form];
        }

        return "";
    }

    public function existsGroupVideoconferenceConfirmationForm($group) {
        return !empty($this->getGroupForm($group, 'vc_confirmation'));
    }

    public function getGroupVideoconferenceConfirmationForm($group) {
        return $this->getGroupForm($group, 'vc_confirmation');
    }



    /**
     * Group mail settings
     */
    protected function getGroupMailSettings($group) {
        $data = $this->config->getSystemValue('vtramit.group.mailSettings', []);
        $mailInfo = array_key_exists($group, $data) ? $data[$group] : [];
        $default = array_key_exists('default', $data) ? $data['default'] : [];

        return array_merge($default, $mailInfo);
    }

    protected function getGroupMailAttribute($group, $attribute, $default) {
        $settings = $this->getGroupMailSettings($group);
        if (!empty($settings) && !empty($settings[$attribute])) {
            return $settings[$attribute];
        }

        return $default;
    }

    public function getGroupMailSubject($group) {
        return $this->getGroupMailAttribute($group, 'subject', '');
    }



    /**
     * Group filters
     */
    protected function getGroupFilterSettings($group) {
        $data = $this->config->getSystemValue('vtramit.group.filter', []);
        $mailInfo = array_key_exists($group, $data) ? $data[$group] : [];
        $default = array_key_exists('default', $data) ? $data['default'] : [];

        return array_merge($default, $mailInfo);
    }

    protected function getGroupFilter($group, $filterName, $default = '') {
        $filter = $this->getGroupFilterSettings($group);
        if (!empty($filter) && !empty($settings[$filterName])) {
            return $filter[$filterName];
        }

        return $default;
    }

    public function getGroupFilterNow($group) {
        return $this->getGroupFilter($group, 'now');
    }


    
    /**
     * Deck
     */
    protected function getDeckSettings($group) {
        return $this->config->getSystemValue('vtramit.group.deck', []);
    }


    

    /**
     * File Paths
     */
    public function getLocalPathToAppointment($appointment, $userId = null) {
        if (empty($userId)) {
            return $appointment->getUserId()
                .self::DIRECTORY_SEPARATOR.'files'
                .self::DIRECTORY_SEPARATOR.$appointment->getDepartment()
                .self::DIRECTORY_SEPARATOR.$appointment->getCitizenId()
                .self::DIRECTORY_SEPARATOR.$appointment->getExternalId();
        }

        return $userId
            .self::DIRECTORY_SEPARATOR.'files'
            .self::DIRECTORY_SEPARATOR.$appointment->getDepartment()
            .self::DIRECTORY_SEPARATOR.$appointment->getCitizenId()
            .self::DIRECTORY_SEPARATOR.$appointment->getExternalId();
    }

    public function getLocalPathToDocuments($appointment, $subfolder, $userId = null) {
        return $this->getLocalPathToAppointment($appointment, $userId)
            .self::DIRECTORY_SEPARATOR.$subfolder;
    }

    public function getLocalPathToReadme($appointment, $userId = null) {
        return $this->getLocalPathToAppointment($appointment, $userId)
            .self::DIRECTORY_SEPARATOR.'Readme.md';
    }


    
    /**
     * Web Paths
     */
    public function getWebPathToAppointment($appointment) {
        return '/apps/files/?dir='
            .self::DIRECTORY_SEPARATOR.$appointment->getDepartment()
            .self::DIRECTORY_SEPARATOR.$appointment->getCitizenId()
            .self::DIRECTORY_SEPARATOR.$appointment->getExternalId();
    }

    public function getWebPathToDocuments($appointment, $subfolder) {
        return $this->getWebPathToAppointment($appointment)
            .self::DIRECTORY_SEPARATOR.$subfolder;
    }

    public function getWebPathToReadme($appointment) {
        return $this->getWebPathToAppointment($appointment)
            .self::DIRECTORY_SEPARATOR.'Readme.md';
    }
    

    /**
     * Example: '/var/www/data/my-admin/files/my-deparment/{citizen_id}/{appointment_id}/{subfolder}'
     */
    public function getUserRelativePathToDocuments($appointment, $subfolder) {
        // The folder to share must not include the username
        return $appointment->getDepartment()
            .self::DIRECTORY_SEPARATOR.$appointment->getCitizenId()
            .self::DIRECTORY_SEPARATOR.$appointment->getExternalId()
            .self::DIRECTORY_SEPARATOR.$subfolder;
    }
}
