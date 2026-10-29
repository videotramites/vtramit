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

use OC;
use OCP\IL10N;
use OCP\IConfig;
use OCP\IGroupManager;

use OCA\VTramit\Constants;
use OCA\VTramit\Language;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Helper\BeHelper;

class ConfigService {

    // Constants
    public const DIRECTORY_SEPARATOR = '/';

    private $config;

    /** @var IL10N */
    private $translate;

    private $groupManager;

    private $dateTimeZone = null;

    /**
     * PHPUnit Test: Ignore _construct coverage as the class is initialized with mocks
     * @codeCoverageIgnore
     */
    public function __construct(
        IConfig $config,
        IL10N $translate,
        IGroupManager $groupManager
    ) {
        $this->config = $config;
        $this->translate = $translate;
        $this->groupManager = $groupManager;
    }

    private function getAppName() {
        return 'vtramit';
    }

    private function toBoolean(mixed $value): bool {
        if (!is_bool($value)) {
            return in_array($value, ['1', 'true', 'yes']);
        }

        return $value;
    }

    private function toInt(mixed $value, int $default = 0): int {
        return filter_var($value, FILTER_VALIDATE_INT) !== false
            ? (int) $value
            : $default;
    }

    /**
     * Obtain all configurable options
     */
    public function findAll(): ResponseInfo {
        $data = [];

        // Tab -General-
        $data['languages'] = $this->getAllowedLanguages();
        $data['resultsPerPage'] = $this->getResultsPerPage();
        $data['filterOption'] = $this->getFilterOptions();
        $data['filterOverdueDays'] = $this->getFilterOverdueDays();
        $data['dateFormat'] = $this->getDateFormat();
        $data['timeFormat'] = $this->getTimeFormat();
        $data['datetimeFormat'] = $this->getDatetimeFormat();
        $data['admin'] = $this->getAdminUser();
        $data['mailsAllowed'] = $this->isAllowSendEmails();
        $data['citizenQueueInfo'] = $this->isCitizenQueueInfoEnabled();
        $data['citizenQueueScope'] = $this->getCitizenQueueScope();
        $data['groupLimit'] = $this->getManagerGroups();
        $data['phoneLink'] = $this->getPhoneLink();
        $data['phonePrefix'] = $this->getPhonePrefix();
        $data['phoneDefaultCountryCode'] = $this->getPhoneDefaultCountryCode();

        // Tab -Public-
        $data['publicDocumentationUrl'] = $this->getPublicDocumentationUrl();
        $data['publicLogoUrl'] = $this->getPublicLogoUrl();
        $data['publicLogoLogin'] = $this->getPublicLogoLogin();
        $data['jitsiRoomPattern'] = $this->getJitsiRoomPattern();
        $data['publicTextExpired'] = $this->getPublicTextExpired();
        $data['publicTextFuture'] = $this->getPublicTextFuture();
        $data['publicPasswordHide'] = $this->getPublicPasswordHide();
        $data['publicDownloadsHide'] = $this->getPublicDownloadsHide();
        $data['publicUploadsHide'] = $this->getPublicUploadsHide();
        $data['publicSignatureHide'] = $this->getPublicSignatureHide();
        $data['publicOutputAutodownload'] = $this->getPublicOutputAutodownload();

        // Tab -Public- Robot videocall test (per-language help text)
        $languages = $this->getAllowedLanguages();
        foreach ($languages as $lang) {
            $data['videocallTestHelpText_' . $lang] = $this->getVideocallTestHelpText($lang);
        }
        $data['robotTestUrl'] = $this->getRobotTestUrl();
        $data['robotTestToken'] = $this->getRobotTestToken();

        $response = new ResponseInfo();
        $response->setInfo('configuration', $data);
        // Sibling to 'configuration' (never persisted back on update): the list
        // of Nextcloud groups the admin can pick as "manager groups".
        $response->setInfo('availableGroups', $this->getAllGroupsForSelector());
        return $response;
    }

    /**
     * Update or create configurations using given data.
     *
     * @param array  $data  list of configuration keys and values to store
     */
    public function update($data): ResponseInfo {
        foreach($data as $key => $value) {
            if (is_array($value)) {
                $this->config->setAppValue($this->getAppName(), $key, serialize($value));
            }
            else {
                $this->config->setAppValue($this->getAppName(), $key, $value);
            }
        }

        $response = new ResponseInfo();
        return $response;
    }

    /**
     * Obtain the operation mode. Allowed values are:
     *   - production (default)
     *   - test
     *
     * Be careful: Operations in test mode can result in lost data
     *
     * That option should be configured in the vtramit.config.php file
     *
     * @return string  operation mode
     */
    public function getMode(): string {
        return $this->config->getSystemValueString('vtramit.mode', 'production');
    }

    /**
     * Indicates wether the time of methods should be logged.
     *
     * That option should be configured in the vtramit.config.php file
     *
     * @return bool  true if method's time should be logged; otherwise, false
     */
    public function getLogTimer(): bool {
        return $this->config->getSystemValueBool('vtramit.log.timer', false);
    }

    /**
     * Obtain the encryption key generated for the current instance. If no key
     * exists, a new one is created and stored in database.
     *
     * @return string  encryption key
     */
    public function getEncryptionKey(): string {
        $value = $this->config->getAppValue($this->getAppName(), 'encryption_key', '');
        if (empty($value)) {
            $value = BeHelper::random_string_with_special_chars(64);
            $this->config->setAppValue($this->getAppName(), 'encryption_key', $value);
        }

        return $value;
    }

    /**
     * Obtain the allowed languages for the app.
     *
     * This configuration is stored in database.
     *
     * @return array  list of allowed languages
     */
    public function getAllowedLanguages(): array {
        $languages =  $this->config->getAppValue($this->getAppName(), 'languages', []);
        return is_array($languages) ? $languages : unserialize($languages);

        /*
        // TODO: merge getAllowedLanguages and getDetailedAllowedLanguages
        $languages =  $this->config->getAppValue($this->getAppName(), 'languages', []);
        $languages = is_array($languages) ? $languages : unserialize($languages);

        $supported = Language::getDetails($this->translate);
        $supportedMap = array_column($supported, 'value', 'key');
        $detailed = array_intersect_key($supportedMap, array_flip($languages));

        return $detailed;
        */
    }

    /**
     * Get the list of allowed languages with their details.
     *
     * @return array<string,string> Associative array of allowed languages,
     *   where the key is the language code and the value is the translated label.
     */
    public function getDetailedAllowedLanguages(): array {
        $supported = Language::getDetails($this->translate);
        $allowed = $this->getAllowedLanguages();

        $supportedMap = array_column($supported, 'value', 'key');
        $detailed = array_intersect_key($supportedMap, array_flip($allowed));

        return $detailed;
    }

    /**
     * Obtain the procedure max results per page setting for the app.
     *
     * This configuration is stored in database.
     *
     * @return int  the number of max results. 0 or less means pagination is deactivated
     */
    public function getResultsPerPage(): int {
        return $this->toInt($this->config->getAppValue($this->getAppName(), 'resultsPerPage', 0), 0);
    }

    /**
     * Obtain the filter options for the procedures list and their status.
     *
     * This configuration is stored in database.
     *
     * @return array  list of filters
     */
    public function getFilterOptions(): array {
        $filterOption =  $this->config->getAppValue($this->getAppName(), 'filterOption', []);
        return is_array($filterOption) ? $filterOption : unserialize($filterOption);
    }

    /**
     *
     */
    public function getTimezone(): string {
        return $this->config->getSystemValueString('logtimezone', 'Europe/Berlin');
    }

    /**
     *
     */
    public function getDateTimezone() {
        if (empty($this->dateTimeZone)) {
            $this->dateTimeZone = new \DateTimeZone($this->getTimezone());
        }

        return $this->dateTimeZone;
    }

    /**
     *
     */
    public function getDateTime($value = "now") {
        return new \DateTime($value, $this->getDateTimezone());
    }

    /**
     *
     */
    public function getDateFormat(): string {
        return $this->config->getAppValue($this->getAppName(), 'dateFormat', Constants::DEFAULT_DATE_FORMAT);
    }

    /**
     *
     */
    public function getTimeFormat(): string {
        return $this->config->getAppValue($this->getAppName(), 'timeFormat', Constants::DEFAULT_TIME_FORMAT);
    }

    /**
     *
     */
    public function getDatetimeFormat(): string {
        return $this->config->getAppValue($this->getAppName(), 'datetimeFormat', Constants::DEFAULT_DATETIME_FORMAT);
    }

    /**
     *
     */
    public function getAdminUser(): string {
        return $this->config->getAppValue($this->getAppName(), 'admin', 'admin');
    }

    /**
     *
     */
    public function getAdminRoot(): string {
        return $this->getAdminUser().self::DIRECTORY_SEPARATOR.'files'.self::DIRECTORY_SEPARATOR;
    }

    /**
     *
     */
    public function getTrashRoot(): string {
        return 'files_trashbin'.self::DIRECTORY_SEPARATOR;
    }

    /**
     *
     */
    public function isAllowSendEmails(): bool {
        return $this->toBoolean($this->config->getAppValue($this->getAppName(), 'mailsAllowed', true));
    }


    /**
     * Whether to surface "ahead-in-queue" info to the citizen on the
     * waiting-room ping response. When OFF (default), the ping response
     * stays the way it has always been so the production Jitsi-meet
     * forks that don't read the body keep working. When ON, the response
     * gains a `queue.ahead` count which a companion script (or a
     * patched fork) can render as "Té N persones davant".
     *
     * Never exposes the attendant's identity — count only.
     */
    public function isCitizenQueueInfoEnabled(): bool {
        return $this->toBoolean($this->config->getAppValue($this->getAppName(), 'citizenQueueInfo', false));
    }

    /**
     * Scope that defines what "ahead in queue" means for the citizen.
     * Drives which column(s) of `oc_vtproc` the ping uses to filter
     * other appointments to count. Always returns the actual scope name
     * (never falls back silently): unknown values resolve to 'assigned_user'.
     *
     *  assigned_user           — only count appointments with the same
     *                            assigned_user_id as the citizen's procedure
     *  assigned_group          — same assigned_group_id (e.g. "OAC")
     *  procedure_type_and_group — same procedure_type_id AND assigned_group_id
     *                            (the most specific group-level scope: same
     *                            type of procedure handled by the same
     *                            department)
     *  topic                   — same topic string verbatim
     *
     * @return string one of the values above
     */
    public function getCitizenQueueScope(): string {
        $known = ['assigned_user', 'assigned_group', 'procedure_type_and_group', 'topic'];
        $value = (string) $this->config->getAppValue($this->getAppName(), 'citizenQueueScope', 'assigned_user');
        return in_array($value, $known, true) ? $value : 'assigned_user';
    }

    /**
     * Manager ("gestor") groups. Members of any of these groups (and global
     * admins) may see every user of the groups they belong to — which drives
     * both the "assigned to" column and the user search filter in the
     * procedures list. Stored under the legacy 'groupLimit' app-config key.
     *
     * Robust to both storage formats: a PHP-serialized array (written by the
     * settings UI) and the historical comma-separated string of group ids.
     *
     * @return string[]  list of Nextcloud group ids
     */
    public function getManagerGroups(): array {
        $value = $this->config->getAppValue($this->getAppName(), 'groupLimit', '');
        if ($value === '' || $value === null) {
            return [];
        }

        $groups = @unserialize($value);
        if (!is_array($groups)) {
            $groups = explode(',', (string) $value);
        }

        // Normalise: trim, drop empties, reindex.
        $groups = array_values(array_filter(array_map('trim', $groups), function ($g) {
            return $g !== '';
        }));

        return $groups;
    }

    /**
     * The full list of Nextcloud groups, formatted for the settings group
     * picker as [{id, name}]. Not a stored config value.
     *
     * @return array
     */
    private function getAllGroupsForSelector(): array {
        $result = [];
        foreach ($this->groupManager->search('') as $group) {
            $result[] = [
                'id' => $group->getGID(),
                'name' => $group->getDisplayName(),
            ];
        }
        return $result;
    }


    /**
     * Phone settings
     */
    public function getPhoneLink() {
        return $this->config->getAppValue($this->getAppName(), 'phoneLink', 'ciscotel');
    }

    /**
     *
     */
    public function getPhonePrefix() {
        return $this->config->getAppValue($this->getAppName(), 'phonePrefix', '');
    }

    /**
     *
     */
    public function getPhoneDefaultCountryCode() {
        return $this->config->getAppValue($this->getAppName(), 'phoneDefaultCountryCode', '');
    }

    /**
     * Filter option for 'Overdue'
     */
    public function getFilterOverdueDays() {
        return $this->toInt($this->config->getAppValue($this->getAppName(), 'filterOverdueDays', 2), 2);
    }

    /**
     * Group filters
     */
    protected function getGroupFilterSettings($group) {
        $data = $this->config->getSystemValueArray('vtramit.group.filter', []);
        $filterInfo = array_key_exists($group, $data) ? $data[$group] : [];
        $default = array_key_exists('default', $data) ? $data['default'] : [];

        return array_merge($default, $filterInfo);
    }

    protected function getGroupFilter($group, $filterName, $default = '') {
        $filter = $this->getGroupFilterSettings($group);
        if (!empty($filter) && !empty($filter[$filterName])) {
            return $filter[$filterName];
        }

        return $default;
    }

    public function getGroupFilterNow($group) {
        return $this->getGroupFilter($group, 'now');
    }


    /**
     * Public settings
     */
    public function getPublicDocumentationUrl() {
        return $this->config->getAppValue($this->getAppName(), 'publicDocumentationUrl', '');
    }

    public function getPublicLogoUrl() {
        return $this->config->getAppValue($this->getAppName(), 'publicLogoUrl', '');
    }

    public function getPublicLogoLogin() {
        return $this->config->getAppValue($this->getAppName(), 'publicLogoLogin', '');
    }

    public function getJitsiRoomPattern() {
        return $this->config->getAppValue($this->getAppName(), 'jitsiRoomPattern', '%externalId%-%random:16%');
    }

    public function getPublicTextExpired() {
        return $this->config->getAppValue($this->getAppName(), 'publicTextExpired', '');
    }

    public function getPublicTextFuture() {
        return $this->config->getAppValue($this->getAppName(), 'publicTextFuture', '');
    }

    public function getPublicPasswordHide() {
        return $this->config->getAppValue($this->getAppName(), 'publicPasswordHide', false);
    }

    public function getPublicDownloadsHide() {
        return $this->config->getAppValue($this->getAppName(), 'publicDownloadsHide', false);
    }

    public function getPublicUploadsHide() {
        return $this->config->getAppValue($this->getAppName(), 'publicUploadsHide', false);
    }

    public function getPublicSignatureHide() {
        return $this->config->getAppValue($this->getAppName(), 'publicSignatureHide', false);
    }

    public function getPublicOutputAutodownload() {
        return $this->toBoolean($this->config->getAppValue($this->getAppName(), 'publicOutputAutodownload', false));
    }

    public function getVideocallTestHelpText(string $lang = ''): string {
        $key = 'videocallTestHelpText' . ($lang ? '_' . $lang : '');
        return $this->config->getAppValue($this->getAppName(), $key, '');
    }

    public function getRobotTestUrl(): string {
        return $this->config->getAppValue($this->getAppName(), 'robotTestUrl', '');
    }

    public function getRobotTestToken(): string {
        return $this->config->getAppValue($this->getAppName(), 'robotTestToken', '');
    }
}
