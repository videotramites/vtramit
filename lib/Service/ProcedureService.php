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

use DateTimeZone;
use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\AppFramework\Db\Entity;
use OCP\Files\IRootFolder;
use OCP\Share\IShare;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DataDownloadResponse;

use OCA\VTramit\AttendanceChannel;
use OCA\VTramit\Constants;
use OCA\VTramit\ProcedureStatus;

use OCA\VTramit\Db\MailTemplate;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureType;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ProcedureTypeGroup;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Exception\DataNotFoundException;
use OCA\VTramit\Exception\EntityNotFoundException;
use OCA\VTramit\Exception\DeleteNotAllowedException;
use OCA\VTramit\Exception\InvalidStatusTransitionException;

use OCA\VTramit\Helper\BeHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Helper\JwtHelper;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureVideoconferenceService;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureTokenService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\WebhookService;

use OCA\VTramit\Csv\File\Json as JsonCsvConverter;

class ProcedureService extends EntityService {

    /** @var IRootFolder */
    private $rootFolder;

    /** @var IURLGenerator */
    private $urlGenerator;

    /** @var MailTemplateMapper */
    private $mailTemplateMapper;

    /** @var ProcedureMapper */
    private $procedureMapper;

    /** @var ProcedureStakeholderMapper */
    private $procedureStakeholderMapper;

    /** @var ProcedureTypeMapper */
    private $procedureTypeMapper;

    /** @var ProcedureTypeGroupMapper */
    private $procedureTypeGroupMapper;

    /** @var NextcloudHelper */
    private $nextcloudHelper;

    /** @var UserGroupHelper */
    private $userGroupHelper;

    /** @var DeckHelper */
    private $deckHelper;

    /** @var MailQueueService */
    private $mailQueueService;

    /** @var SignatureService */
    private $signatureService;

    /** @var SignatureTypeService */
    private $signatureTypeService;

    /** @var SignatureButtonService */
    private $signatureButtonService;

    /** @var ProcedureFileService */
    private $procedureFileService;

    /** @var ProcedureVideoconferenceService */
    private $procedureVideoconferenceService;

    /** @var ProcedureMailService */
    private $procedureMailService;

    /** @var ProcedureMessageService */
    private $procedureMessageService;

    /** @var ProcedureTokenService */
    private $procedureTokenService;

    /** @var ProcedureTypeService */
    private $procedureTypeService;

    /** @var ProcedureTypeGroupService */
    private $procedureTypeGroupService;

    /** @var WebhookService */
    private $webhookService;


    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        IRootFolder $rootFolder,
        IURLGenerator $urlGenerator,
        MailTemplateMapper $mailTemplateMapper,
        ProcedureMapper $procedureMapper,
        ProcedureStakeholderMapper $procedureStakeholderMapper,
        ProcedureTypeMapper $procedureTypeMapper,
        ProcedureTypeGroupMapper $procedureTypeGroupMapper,
        NextcloudHelper $nextcloudHelper,
        UserGroupHelper $userGroupHelper,
        DeckHelper $deckHelper,
        ConfigService $configService,
        MailQueueService $mailQueueService,
        SignatureService $signatureService,
        SignatureTypeService $signatureTypeService,
        SignatureButtonService $signatureButtonService,
        ProcedureFileService $procedureFileService,
        ProcedureVideoconferenceService $procedureVideoconferenceService,
        ProcedureMailService $procedureMailService,
        ProcedureMessageService $procedureMessageService,
        ProcedureTokenService $procedureTokenService,
        ProcedureTypeService $procedureTypeService,
        ProcedureTypeGroupService $procedureTypeGroupService,
        WebhookService $webhookService
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $procedureMapper, $webhookService);

        $this->rootFolder = $rootFolder;
        $this->urlGenerator = $urlGenerator;
        $this->mailTemplateMapper = $mailTemplateMapper;
        $this->procedureStakeholderMapper = $procedureStakeholderMapper;
        $this->procedureMapper = $procedureMapper;
        $this->procedureTypeMapper = $procedureTypeMapper;
        $this->procedureTypeGroupMapper = $procedureTypeGroupMapper;
        $this->nextcloudHelper = $nextcloudHelper;
        $this->userGroupHelper = $userGroupHelper;
        $this->deckHelper = $deckHelper;
        $this->mailQueueService = $mailQueueService;
        $this->signatureService = $signatureService;
        $this->signatureTypeService = $signatureTypeService;
        $this->signatureButtonService = $signatureButtonService;
        $this->procedureFileService = $procedureFileService;
        $this->procedureVideoconferenceService = $procedureVideoconferenceService;
        $this->procedureMailService = $procedureMailService;
        $this->procedureMessageService = $procedureMessageService;
        $this->procedureTokenService = $procedureTokenService;
        $this->procedureTypeService = $procedureTypeService;
        $this->procedureTypeGroupService = $procedureTypeGroupService;
        $this->webhookService = $webhookService;
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new Procedure();
    }

    /**
     * Indicates wether the activity is allowed to be reported or not.
     *
     * @return bool  true if activity is allowed to be reported; othersise, false
     */
    protected function allowReportActivity(): bool {
        return true;
    }

    /**
     * Get the list of fields that should be ignored when receiving new data.
     *
     * @return array  list of fields to ignore on update
     */
    protected function fieldsToIgnoreOnUpdate(): array {
        return ['procedureTypeId', 'staffUploadsUrl', 'staffDownloadsUrl', 'staffSignatureUploadsUrl', 'staffSignatureDownloadsUrl', 'jitsiRoomCode', 'jitsiRoomStaffUrl', 'statusDate', 'foldersOwner', 'modificationDate', 'modificationUser'];
    }

    /**
     * Obtain the procedure type related to given procedure.
     *
     * @param int $procedure  the procedure
     * @return ProcedureType
     */
    public function getProcedureType(Procedure $procedure): ProcedureType {
        return $this->procedureTypeMapper->find($procedure->getProcedureTypeId());
    }

    /**
     * Obtain the procedure type related to given procedure.
     *
     * @param int $procedure  the procedure
     * @return ProcedureType
     */
    protected function getProcedureTypeByProcedureId(int $procedureId): ProcedureType {
        $procedure = $this->entityMapper->find($procedureId);
        return $this->procedureTypeMapper->find($procedure->getProcedureTypeId());
    }

    // TODO: following code can be used when ProcedureTypeStatus is enabled
    /**
     * Obtain the procedure type status related to its given id.
     *
     * @param int $procedureTypeStatusId  the identifier of the procedure type status
     * @return ProcedureTypeStatus
     */
    /*protected function getProcedureTypeStatus(int $procedureTypeStatusId): ProcedureTypeStatus {
        return $this->procedureTypeStatusMapper->find($procedureTypeStatusId);
    }*/

    /**
     * Obtain the main stakeholder for the given procedure.
     *
     * @param Procedure $entity  procedure entity to which belongs the stakeholder
     * @return ProcedureStakeholder|null
     */
    protected function getMainStakeholder(Procedure $entity): ?ProcedureStakeholder {
        try {
            return $this->procedureStakeholderMapper->findMainByProcedure($entity);
        }
        catch (EntityNotFoundException $e) {
            return null;
        }
    }

    /**
     * Modify the entity status with NO storage in database.
     *
     * @param Procedure $entity  the entity to change status
     * @param int $statusId      the new status to set
     * @return Procedure
     * @throws InvalidStatusTransitionException
     */
    private function setProcedureStatus(Procedure $entity, int $statusId): Procedure {
        if ($entity->getStatusId() != $statusId) {
            if (ProcedureStatus::isValidTransition($entity->getStatusId(), $statusId)) {
                $now = $this->configService->getDateTime();
                $entity->setStatusDate($now);
                $entity->setStatusId($statusId);
            }
            else {
                throw new InvalidStatusTransitionException($this->translate->t('The procedure cannot transit to the new given status'));
            }
        }

        return $entity;
    }

    /**
     * Inform the entity as modified with NO storage in database.
     *
     * @param Procedure $entity  the entity to inform as modified
     * @param string $userId     the user that has done the modification
     * @return Procedure
     */
    private function setProcedureModified(Procedure $entity, string $userId): Procedure {
        $now = $this->configService->getDateTime();
        $entity->setModificationDate($now);
        $entity->setModificationUser(empty($userId) ? 'anonymous' : $userId);

        return $entity;
    }

    /**
     * Builds and returns the staff portal URL for a given procedure.
     *
     * @param int $procedureId
     *   The ID of the procedure to retrieve the staff portal URL for.
     *
     * @return \ResponseInfo
     *   The response object containing the procedure information and the
     *   generated staff portal URL under the 'url' key if available.
     */
    public function getStaffPortalUrlById(int $procedureId): string {
        $response = $this->find($procedureId);
        $procedure = $response->getInfo('entity');

        if (!empty($procedure)) {
            return $this->getStaffPortalUrl($procedure);
        }
        // PHPUnit Test: Impossible case due to function find return value is set to Procedure entity, so will never be empty
        // @codeCoverageIgnoreStart
        return '';
        // @codeCoverageIgnoreEnd
    }

    /**
     * Builds and returns the staff portal URL for a given procedure.
     *
     * @param int $procedureId
     *   The ID of the procedure to retrieve the staff portal URL for.
     *
     * @return \ResponseInfo
     *   The response object containing the procedure information and the
     *   generated staff portal URL under the 'url' key if available.
     */
    public function getStaffPortalUrl(Procedure $procedure): string {
        $url = $this->procedureFileService->getWebPathToVtramit() . 'attendant/' . $procedure->getJitsiRoomCode();
        return $url;
    }


    //
    // Context
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the procedure context for the user.
     *
     * @param int $procedureId  identifier of the procedure for which the context is retrieved
     * @param int $userId       user identifier in the context
     * @return ResponseInfo  {result, groups[]}
     */
    public function getContext(int $procedureId, string $userId): ResponseInfo {
        $response = new ResponseInfo();

        $procedure = $this->entityMapper->find($procedureId);
        $procedureType = $this->getProcedureTypeByProcedureId($procedureId);
        $response->setInfo('channels', $procedureType->getAllowedAttendanceChannelList());

        $nextStatuses = ProcedureStatus::getAllowedActions($procedure->getStatusId());
        $response->setInfo('transitions', $nextStatuses);

        $signatureButtons = [];
        $procedureAssignedGroup = $procedure->getAssignedGroupId();
        if ($procedureType->getAllowSignature()) {
            $buttons = $this->signatureButtonService->findByProcedureTypeId($procedureType->id);
            $procedureTypeGroup = $this->procedureTypeGroupService->findByProcedureTypeId($procedureType->id);
            // Send only the buttons for the group which the procedure has been assigned to, since there could be buttons assigned
            // to other groups for the same procedure type
            foreach ($buttons->getData()['list'] as $button) {
                foreach ($procedureTypeGroup->getData()['list'] as $procTypeGroup){
                    if ($procedureAssignedGroup == $procTypeGroup->getGroup()) {
                        if ($procedureAssignedGroup == $button->getGroup()) {
                            $signatureButtons[] = $button;
                        }
                    }
                }
            }
        }

        $response->setInfo('signatureButtons', $signatureButtons);

        return $response;
    }


    //
    // Main actions
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Obtain entity given its identifier.
     *
     * @return ResponseInfo  {result, entity}
     */
    public function find(int $id): ResponseInfo {
        $response = parent::find($id);
        $entity = $response->getInfo('entity');

        if (!empty($entity)) {
            $entity = $this->prepareComputedFields($entity);
        }

        $response->setInfo('entity', $entity);

        return $response;
    }

    /**
     * Prepares and sets computed fields for a given Procedure entity.
     *
     * This method calculates dynamic values (like the full Jitsi staff room URL
     * and the staff portal URL) and injects them into the Procedure instance
     * before it is returned.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     *   The Procedure entity to enrich with computed fields.
     *
     * @return \OCA\VTramit\Db\Procedure
     *   The same Procedure entity with computed fields populated.
     */
    private function prepareComputedFields(Procedure $procedure): Procedure {
        $jitsiUrl = $this->prepareFullJitsiRoomStaffUrl($procedure);
        $procedure->setFullJitsiRoomStaffUrl($jitsiUrl);

        $portalUrl = $this->getStaffPortalUrl($procedure);
        $procedure->setStaffPortalUrl($portalUrl);

        return $procedure;
    }

    /**
     * Get filtered procedures.
     *
     * Valid keys in $filter:
     * 'dueDate'         overdue / dueToday / dueWeek / dueMonth
     * 'minDate'         oldest date of procedures to retrieve; PHP time strings
     *                   are valid, like 'today', 'tomorrow', '+1 day', ...
     *                   This filter is overridden by 'dueDate' parameter
     * 'maxDate'         newest date of procedures to retrieve; PHP time strings
     *                   are valid, like 'today', 'tomorrow', '+1 day', ...
     *                   This filter is overridden by 'dueDate' parameter
     * 'groups'          array of groups which belongs the procedures; the special
     *                   user 'unassigned' can be used to retrieve also procedures
     *                   not yet assigned; if no group is assigned, all the user
     *                   groups will be used
     * 'status'          array of procedures statuses. If not set, any status will be
     *                   retrieved
     * 'users'           array of users assigned to the procedures; the special
     *                   user 'unassigned' can be used to retrieve also procedures
     *                   not yet assigned. If not set, procedures with any assignment
     *                   will be retrieved
     * 'externalId'      search for given external Id
     * 'jitsiRoomCode'   the jitsi room code
     * 'procedureTypeId' search for given external Id
     * 'freeSearch'      search if external ID, personal ID and/or stakeholder
     *                   name starts with given string. When this parameter is given
     *                   all other parameters are ignored
     *
     * @param array  $filter                 Associative array with filter parameters
     * @param string $userId                 identifier of user executing the operation
     * @param bool   $prepareComputedFields  whether to compute and populate dynamic fields (default: false).
     * @return ResponseInfo  {result, list[]}
     */
    public function findByFilter(array $filter, string $userId = null, bool $prepareComputedFields = false): ResponseInfo {
        $response = new ResponseInfo();
        $timezone = $this->configService->getDateTimezone();

        // Ensure to filter by user groups
        if (empty($filter['groups']) && !empty($userId)) {
            $allGroups = $this->procedureTypeGroupMapper->findAllGroups();
            $filter['groups'] = $this->userGroupHelper->getAllowedGroupsForUser($userId, $allGroups);

            // If user has access to any group, then include also the unassigned
            if (!empty($filter['groups'])) {
                $filter['groups'][] = 'unassigned';
            }
        }

        if (empty($filter['groups']) && !empty($userId)) {
            $response->setInfo('list', []);
            return $response;
        }

        // 'dueDate' is not managed by mapper, so it must be managed now
        if (!empty($filter['dueDate'])) {
            switch ($filter['dueDate']) {
                case "overdue":
                    if ($this->configService->getFilterOverdueDays() > -1) {
                        $filter['minDate'] = BeHelper::getFromRelativeDate('-'.$this->configService->getFilterOverdueDays().' day', $timezone, 'today');
                    }
                    $filter['maxDate'] = new \DateTime('today', $timezone);
                    break;

                case "dueToday":
                    $filter['minDate'] = new \DateTime('today', $timezone);
                    $filter['maxDate'] = new \DateTime('tomorrow', $timezone);
                    break;

                case "dueWeek":
                    $filter['minDate'] = new \DateTime('today', $timezone);
                    $filter['maxDate'] = BeHelper::getFromRelativeDate('+1 week', $timezone, 'today');
                    break;

                case "dueMonth":
                    $filter['minDate'] = new \DateTime('today', $timezone);
                    $filter['maxDate'] = BeHelper::getFromRelativeDate('+1 month', $timezone, 'today');
                    break;

                case "dueNow":
                    if (count($filter['groups']) == 1) {
                        $group = $filter['groups'][0];
                        $nowFilter = $this->configService->getGroupFilterNow($group);
                    }
                    else {
                        $nowFilter = $this->configService->getGroupFilterNow('default');
                    }

                    if (!empty($nowFilter)) {
                        $filter['minDate'] = BeHelper::getFromRelativeDate($nowFilter['interval-before'], $timezone, 'auto');
                        $filter['maxDate'] = BeHelper::getFromRelativeDate($nowFilter['interval-after'], $timezone, 'auto');
                    }

                    // If 'now' not defined, set default values
                    else {
                        $filter['minDate'] = BeHelper::getFromRelativeDate('-1 hour', $timezone, 'now');
                        $filter['maxDate'] = BeHelper::getFromRelativeDate('+4 hour', $timezone, 'now');
                    }
                    break;
            }
            unset($filter['dueDate']);
        }
        /** 'dueDateRange' is not managed by mapper, so it must be managed now. Since the previous filter and this
        *   are mutually exclusive, if the next filter is set, the minDate and maxDate will be overriden and previous
        *   filter 'dueDate won't have any effect.
        *   We receive Zulu times from the Vue DateTimePicker components.
        */
        if (!empty($filter['dueDateFrom'])) {
            $filter['minDate'] = new \DateTime($filter['dueDateFrom'], new \DateTimeZone('UTC'));
            $filter['minDate']->setTimezone($timezone);
        }
        if (!empty($filter['dueDateTo'])) {
            $filter['maxDate'] = new \DateTime($filter['dueDateTo'], new \DateTimeZone('UTC'));
            $filter['maxDate']->setTimezone($timezone);
            // If dueDateFrom filter is empty, set today as 'from' reference automatically
            if (empty($filter['dueDateFrom'])) {
                $filter['minDate'] = new \DateTime('today', $timezone);
            }
        }

        if (!empty($filter['minDate']) && is_string($filter['minDate'])) {
            $filter['minDate'] = BeHelper::getFromRelativeDate($filter['minDate'], $timezone, 'auto');
        }

        if (!empty($filter['maxDate']) && is_string($filter['maxDate'])) {
            $filter['maxDate'] = BeHelper::getFromRelativeDate($filter['maxDate'], $timezone, 'auto');
        }

        // Check dates
        if (empty($filter['minDate']) && empty($filter['maxDate'])) {
            $filter['minDate'] = new \DateTime('today', $timezone);
            $filter['maxDate'] = new \DateTime('tomorrow', $timezone);
        }

        // Normalize phone
        if (!empty($filter['stakeholderPhone']) && trim($filter['stakeholderPhone'])[0] != '+') {
            $phone = BeHelper::normalizePhone(
                trim($filter['stakeholderPhone']),
                $this->configService->getPhoneDefaultCountryCode(),
                $this->configService->getPhonePrefix()
            );
            $filter['stakeholderPhone'] = $phone;
        }

        $list = $this->entityMapper->findByFilter($filter);

        if ($prepareComputedFields && !empty($list)) {
            foreach ($list as $index => $procedure) {
                $list[$index] = $this->prepareComputedFields($procedure);
            }
        }

        $response->setInfo('list', $list);
        return $response;
    }

    /**
     * Get all non-cancelled procedures using given external ID.
     *
     * @param string $externalId  the external ID to filter
     * @return ResponseInfo  {result, list[]}
     */
    public function findByExternalId(string $externalId): ResponseInfo {
        $filter['externalId'] = $externalId;
        $filter['status'] = ProcedureStatus::getNonFinalStatus();
        $list = $this->entityMapper->findByFilter($filter);

        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Get the procedure associated to the given Jitsi room code.
     *
     * @param string $jitsiRoomCode  The Jitsi room code associated to the procedure.
     *
     * @return ResponseInfo  {result, ?entity}
     */
    public function findByJitsiRoomCode(string $jitsiRoomCode): ResponseInfo {
        $filter = ['jitsiRoomCode' => $jitsiRoomCode];
        $list = $this->entityMapper->findByFilter($filter);

        $response = new ResponseInfo();
        if (!empty($list)) {
            $response->setInfo('entity', reset($list));
        }

        return $response;
    }

    /**
     * Updates given entity or create a new one if not existing.
     *
     * @param array  $data    entity data in JSON format
     * @param string $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, ?message, ?entity}
     */
    public function createOrUpdateByExternalId(array $data, string $userId): ResponseInfo {
        if (!empty($data['externalId'])) {
            $response = $this->findByExternalId($data['externalId']);
            $list = $response->getInfo('list');

            foreach($list as $entity) {
                if ($entity->getStatusId() == ProcedureStatus::COMPLETED) {
                    $response = new ResponseInfo();
                    $response->setError($this->translate->t('Can\'t proceed because the procedure with this external ID is already finished.'));
                    return $response;
                }

                $data['id'] = $entity->getId();
                break;
            }
        }

        return $this->createOrUpdate($data, $userId);
    }

    /**
     * Validates entity information being created.
     *
     * @override
     * @param Entity|ProcedureStakeholder $entity  the entity object to validate
     * @param string                      $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, ?message}
     */
    protected function validateEntityToCreate(Entity $entity, string $userId): ResponseInfo {
        $response = $this->validateEntity($entity, null, $userId);

        $duplications = $this->entityMapper->findDuplicatedByExternalId($entity->getExternalId());
        foreach($duplications as $duplicated) {
            if ($duplicated->getStatusId() == ProcedureStatus::FINISHED) {
                $response->addError($this->translate->t('The procdure cannot be created because a finished procedure with same external ID is awaiting to be completed'));
            }
        }

        return $response;
    }

    /**
     * Operations to apply before create entity.
     *
     * @override
     * @param Entity|Procedure $entity  the entity object being created
     * @param string           $userId  identifier of user executing the operation
     * @return Entity|Procedure         the entity object being created with any applied modification
     */
    protected function beforeCreate(Entity $entity, string $userId): Entity {
        // Manage change of status and information modification
        $entity = $this->setProcedureStatus($entity, ProcedureStatus::INITIALIZING);
        $entity = $this->setProcedureModified($entity, $userId);

        $group = $entity->getAssignedGroupId();
        $entity->setJitsiRoomCode($this->createJitsiRoomCode($entity, $userId));
        $entity->setJitsiRoomStaffUrl($this->createJitsiRoomForStaff($entity));

        $entity->setFoldersOwner($userId);

        return $entity;
    }

    /**
     * Operations to apply after create entity.
     *
     * @override
     * @param Entity|Procedure $entity  the entity object that has been created
     * @param string           $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, list}
     */
    protected function afterCreate(Entity $entity, string $userId): ResponseInfo {
        // Notify the webhooks
        $this->webhookService->event("create-procedure", "vtproc", $entity);

        return $this->cancelDuplicatedProcedures($entity, $userId);
    }

    /**
     * Execute operations after a new stakeholder is added.
     *
     * @param Procedure            $procedure             the procedure to which the stakeholder is added
     * @param ProcedureStakeholder $procedureStakeholder  the added stakeholder
     * @param string               $userId                the user that has done the modification
     * @return ResponseInfo  {result, ?message}
     */
    public function onStakeholderAdded(Procedure $procedure, ProcedureStakeholder $procedureStakeholder, string $userId): ResponseInfo {
        $response = new ResponseInfo();

        try {
            $this->procedureFileService->createFolders($procedure, $userId);
        }
        catch(DataNotFoundException $e) {
            $response->addError($this->translate->t('Folders could not be created'));
        }

        // DISABLED: Not working in some environments
        /*try {
            $this->createReadme($procedure, $userId);
        }
        catch(DataNotFoundException $e) {
            $response->addError($this->translate->t('Could not create the Readme.md file'));
        }*/

        $this->autoprocessStatus($procedure, $userId);

        return $response;
    }

    /**
     * Validates entity information being updated.
     *
     * @override
     * @param Entity|ProcedureStakeholder $newEntity  the entity object to validate
     * @param Entity|ProcedureStakeholder $oldEntity  the previous existing entity object
     * @param array                       $diff       the different elements between the new and old entities
     * @param string                      $userId     identifier of user executing the operation
     * @return ResponseInfo  {result, ?message}
     */
    protected function validateEntityToUpdate(Entity $newEntity, Entity $oldEntity, array $diff, string $userId): ResponseInfo {
        $response = $this->validateEntity($newEntity, null, $userId);

        if (!empty($diff['statusId'])) {
            $transitions = ProcedureStatus::getAllowedTransition($oldEntity->getStatusId());
            if (!in_array($diff['statusId'], $transitions)) {
                $response->addError($this->translate->t('The new procedure status cannot be set as it doesn\'t follow the expected flow'));
            }
            else if (in_array($diff['statusId'], [ProcedureStatus::FINISHED, ProcedureStatus::COMPLETED])
                && !$this->isAppointmentDateToday($newEntity)
            ) {
                $response->addError($this->translate->t('The procedure cannot be finished because today is not the appointment day'));
            }
        }

        return $response;
    }

    /**
     * Operations to apply before update entity.
     *
     * @override
     * @param Entity|Procedure $newEntity  the entity with the new data; that is the object that should be modified
     * @param Entity|Procedure $oldEntity  the existing entity object
     * @param array            $diff       the different elements between the new and old entities
     * @param string           $userId     identifier of user executing the operation
     * @return Entity|Procedure            the entity object being updated with any applied modification
     * @throws InvalidStatusTransitionException
     */
    protected function beforeUpdate(Entity $newEntity, Entity $oldEntity, array $diff, string $userId): Entity {
        // Auto complete attendance channel when procedure is being finished or completed and only
        // one channel is allowed
        if ((in_array($newEntity->getStatusId(), [ProcedureStatus::FINISHED, ProcedureStatus::COMPLETED]))
            && empty($newEntity->getAttendanceChannel())
        ) {
            $procedureType = $this->getProcedureType($newEntity);
            if ($procedureType->getRequireAttendanceChannel()
                && count($procedureType->getAllowedAttendanceChannelList()) == 1
            ) {
                $attendanceChannel = reset($procedureType->getAllowedAttendanceChannelList());
                $newEntity->setAttendanceChannel($attendanceChannel);
            }
        }

        if (!empty($diff['statusId'])) {
            switch($diff['statusId']) {
                case ProcedureStatus::CANCELLED:
                case ProcedureStatus::DUPLICATED:
                case ProcedureStatus::EXPIRED:
                    // Manage cancellation
                    $response = $this->cancelProcedure($newEntity, $userId, $diff['statusId'], false);
                    if (!empty($response->getInfo('entity'))) {
                        $newEntity = $response->getInfo('entity');
                    }
                    break;

                case ProcedureStatus::ON_COURSE:
                case ProcedureStatus::FINISHED:
                case ProcedureStatus::COMPLETED:
                    if (empty($newEntity->getAssignedUserId())) {
                        $newEntity->setAssignedUserId($userId);
                    }
            }
        }

        // Manage information modification
        $this->setProcedureModified($newEntity, $userId);

        return $newEntity;
    }

    /**
     * Operations to apply after update entity.
     *
     * @override
     * @param Entity|Procedure $entity  the entity object that has been updated
     * @param array            $diff    the different elements between the new and old entities
     * @param string           $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, ?message, ?list}
     */
    protected function afterUpdate(Entity $entity, array $diff, string $userId): ResponseInfo {
        $response = new ResponseInfo();

        // If external ID has been modified then cancel other possible procedures
        // with new external ID
        if (!empty($diff['externalId'])) {
            $newResponse = $this->cancelDuplicatedProcedures($entity, $userId);
            $response->mergeResponseInfo($newResponse);
        }

        // If status has changed to completed, then try to open the confirmation form
        if (!empty($diff['statusId']) && ($diff['statusId'] == ProcedureStatus::FINISHED || $diff['statusId'] == ProcedureStatus::COMPLETED)) {
            $stakeholder = $this->getMainStakeholder($entity);
            $form = $this->procedureVideoconferenceService->getVideoconferenceConfirmationForm($entity, $stakeholder);
            if (!empty($form)) {
                $response->setInfo('redirect', $form);
            }
        }

        // If group assignation has changed, be sure the sharing folders has been created
        // (could be previously there was no assigned group, so no folders exists) and
        // the procedure status is correct
        if (!empty($diff['assignedGroupId'])) {
            $this->autoprocessStatus($entity, $userId);

            try {
                $this->procedureFileService->createFolders($entity, $userId);
            }
            catch(DataNotFoundException $e) {
                $response->addError($this->translate->t('Folders could not be created'));
            }
        }

        // If appointment date or group assignation has changed, send a new e-mail to
        // the stakeholders and staff
        if (!empty($diff['appointmentDate']) || !empty($diff['assignedGroupId'])) {
            $messagetoStakeholdersResponse = $this->procedureMessageService->sendMessagesToAllStakeholders($entity);
            $response->mergeResponseInfo($messagetoStakeholdersResponse);

            // DEPRECATED: Remove with EmailTemplates system
            if (!$messagetoStakeholdersResponse->isOk()) {
                $mailtoStakeholdersResponse = $this->procedureMailService->sendEmailToAllStakeholders($entity);
                $response->mergeResponseInfo($mailtoStakeholdersResponse);
            }
            // END DEPRECATED
        }

        // If appointment date or group assignation has changed, send a new e-mail to
        // the stakeholders and staff
        if (!empty($diff['appointmentDate']) || !empty($diff['assignedUserId'])) {
            $mainStakeholder = empty($stakeholder) ? $this->getMainStakeholder($entity) : $stakeholder;

            $messageToStaffResponse = $this->procedureMessageService->sendMessagesToStaff($mainStakeholder, $entity);
            $response->mergeResponseInfo($messageToStaffResponse);

            // DEPRECATED: Remove with EmailTemplates system
            if (!$messageToStaffResponse->isOk()) {
                $mailToStaffResponse = $this->procedureMailService->sendEmailToStaff($mainStakeholder, $entity);
                $response->mergeResponseInfo($mailToStaffResponse);
            }
            // END DEPRECATED
        }

        // Notify the webhooks
        if ($response->isOk()) {
            $this->webhookService->event("update-procedure", "vtproc", $entity);
        }

        return $response;
    }

    /**
     * Validates procedure entity information.
     *
     * @param Procedure      $entity         the procedure object to validate
     * @param ProcedureType  $procedureType  the procedure type of the object to validate
     * @param string         $userId         identifier of user executing the operation
     * @return ResponseInfo  {result, message}
     */
    protected function validateEntity(Procedure $entity, ProcedureType $procedureType = null, string $userId): ResponseInfo {
        $response = new ResponseInfo();

        if (empty($entity->getProcedureTypeId())) {
            // If no procedure type defined, do not continue
            $response->addError($this->translate->t('You must select a procedure type to continue'));
            return $response;
        }

        if ($procedureType == null) {
            $procedureType = $this->getProcedureType($entity);
        }

        $context = $this->procedureTypeService->getContext($procedureType->getId(), $userId);
        $bypassRequired = $context->getInfo('bypassRequired');

        if (!$bypassRequired && $procedureType->getRequireAssignedGroup() && empty($entity->getAssignedGroupId())) {
            $response->addError($this->translate->t('Please select a department'));
        }

        if (!$entity->isValidExternalId($bypassRequired || !$procedureType->getRequireExternalId())) {
            $response->addError($this->translate->t('The external procedure identifier can only contain letters, numbers, hyphens, and underscores'));
        }

        if (!$bypassRequired && $procedureType->getRequireAppointmentDate() && empty($entity->getAppointmentDate())) {
            $response->addError($this->translate->t('Please select a date'));
        }
        else {
            if ($entity->isAppointmentDateOlderThan('-1 day', $this->configService->getDateTimezone())) {
                $response->addError($this->translate->t('The appointment date is too old'));
            }
        }

        if (!$bypassRequired && $procedureType->getRequireDeadlineDate() && empty($entity->getDeadlineDate())) {
            $response->addError($this->translate->t('Please select a deadline date'));
        }

        if (!$bypassRequired
            && $procedureType->getRequireAttendanceChannel()
            && (in_array($entity->getStatusId(), [ProcedureStatus::FINISHED, ProcedureStatus::COMPLETED]))
            && empty($entity->getAttendanceChannel())
            && count($procedureType->getAllowedAttendanceChannelList()) > 1
        ) {
            $response->addError($this->translate->t('Please select the attendance channel to proceed'));
        }

        return $response;
    }

    /**
     * Check if the procedure status needs to be changed automatically, in which
     * case, it is changed.
     *
     * @param Procedure $procedure  the procedure to auto process status
     * @param string    $userId     identifier of user executing the operation
     */
    private function autoprocessStatus($procedure, $userId) {
        if ($procedure->getStatusId() == ProcedureStatus::INITIALIZING) {
            $newStatus = $this->isAppointmentDateToday($procedure) ? ProcedureStatus::PENDANT : ProcedureStatus::CREATED;
            $procedure = $this->setProcedureStatus($procedure, $newStatus);
            $procedure = $this->setProcedureModified($procedure, $userId);
            $this->entityMapper->update($procedure);
        }

        else if ($procedure->getStatusId() == ProcedureStatus::CREATED) {
            if ($this->isAppointmentDateToday($procedure)) {
                $procedure = $this->setProcedureStatus($procedure, ProcedureStatus::PENDANT);
                $procedure = $this->setProcedureModified($procedure, $userId);
                $this->entityMapper->update($procedure);
            }
        }
    }

    /**
     * Delete entity by given id.
     *
     * @param int $id  identifier of the entity to delete
     * @return ResponseInfo  {result}
     */
    public function delete(int $id): ResponseInfo {
        $response = $this->find($id);
        $procedure = $response->getInfo('entity');
        $stakeholders = $this->procedureStakeholderMapper->findByProcedure($procedure);

        if (!empty($stakeholders)) {
            throw new DeleteNotAllowedException($this->translate->t('To delete the procedure you must delete first the related stakeholders'));
        }

        // NOTE: it is not necessary to manage deck integration on deletion
        //       because first the stakeholders must be removed
        $response = parent::delete($id);

        // Notify the webhooks
        if ($response->isOk()) {
            $this->webhookService->event("delete-procedure", "vtproc", $response->getInfo('entity'));
        }

        return $response;
    }

    /**
     * Prepare and send an e-mail to all stakeholders in given procedure.
     *
     * @param int $procedureId  the identifier of the procedure with all the recipient stakeholders
     * @return ResponseInfo  {result, message}
     */
    public function sendEmailToAllStakeholders(int $procedureId): ResponseInfo {
        $entity = $this->entityMapper->find($procedureId);
        $response = $this->procedureMessageService->sendMessagesToAllStakeholders($entity);

        // DEPRECATED: Remove with EmailTemplates system
        if (!$response->isOk()) {
            $response = $this->procedureMailService->sendEmailToAllStakeholders($entity);
        }
        // END DEPRECATED

        return $response;
    }

    /**
     * Receive notification of start of videoconference and change the related
     * procedure status to on course.
     *
     * The operation is only successful if the procedure is scheduled for the
     * date in which this function is called and the state is "pendant".
     *
     * If there is a form defined on 'vtramit.group.forms' for the group at which
     * belongs the procedure, and the procedure is updated successfully, the
     * response contains a redirection to the form.
     *
     * @param string $jitsiRoomCode videoconference room
     * @param string $userId        identifier of user executing the operation
     * @return array                information about result status and message status
     * @return ResponseInfo  {result, message, entity, ?redirect}
     */
    public function videoconferenceStarted(string $jitsiRoomCode, string $userId): ResponseInfo {
        $filter['jitsiRoomCode'] = $jitsiRoomCode;
        $filter['status'] = [ProcedureStatus::PENDANT, ProcedureStatus::ON_COURSE];
        $list = $this->entityMapper->findByFilter($filter);

        foreach($list as $entity) {
            if ($this->isAppointmentDateToday($entity)) {
                // If already on course, just send the entity
                // This workflow will allow to reconnect in case the user has
                // closed the Jitsi window accidentally
                if ($entity->getStatusId() == ProcedureStatus::ON_COURSE) {
                    $response = new ResponseInfo();
                    $response->setInfo('entity', $entity);
                }
                else {
                    $entity->setStatusId(ProcedureStatus::ON_COURSE);
                    $entity->setAttendanceChannel(AttendanceChannel::VIDEOCONFERENCE);
                    $response = $this->update($entity->jsonSerialize(), $userId);
                }

                if ($response->isOk()) {
                    $response->setMessage($this->translate->t('Appointment started'));
                }

                return $response;
            }
        }

        $response = new ResponseInfo();
        $response->setError($this->translate->t('No appointments found today and in progress for the notified video conference'));
        return $response;
    }

    /**
     * Receive notification of finished videoconference and change the related
     * procedure status to finished.
     *
     * The operation is only successful if the procedure is scheduled for the
     * date in which this function is calld and the state is "pendant" or "on course".
     *
     * If there is a form defined on 'vtramit.group.forms' for the group at which
     * belongs the procedure, and the procedure is updated successfully, the
     * response contains a redirection to the form.
     *
     * @param string $jitsiRoomCode videoconference room
     * @param string $userId        identifier of user executing the operation
     * @return array                information about result status and message status
     * @return ResponseInfo  {result, message, entity, ?redirect}
     */
    public function videoconferenceFinished(string $jitsiRoomCode, string $userId): ResponseInfo {
        $filter['jitsiRoomCode'] = $jitsiRoomCode;
        $filter['status'] = [ProcedureStatus::PENDANT, ProcedureStatus::ON_COURSE];
        $list = $this->entityMapper->findByFilter($filter);


        foreach($list as $entity) {
            if ($this->isAppointmentDateToday($entity)) {
                $entity->setStatusId(ProcedureStatus::FINISHED);
                $entity->setAttendanceChannel(AttendanceChannel::VIDEOCONFERENCE);

                $response = $this->update($entity->jsonSerialize(), $userId);
                if ($response->isOk()) {
                    $response->setMessage($this->translate->t('Appointment finished'));
                }

                return $response;
            }
        }

        $response = new ResponseInfo();
        $response->setError($this->translate->t('No appointments found today and in progress for the notified video conference'));
        return $response;
    }

    /**
     * Initialize the signature process for given procedure.
     *
     * @param string $procedureId  procedure identifier
     * @return array               information about result status and message status
     */
    public function initializeSignature(int $procedureId, string $userId, $signatureButtonId): ResponseInfo {
        $response = new ResponseInfo();
        $procedure = $this->entityMapper->find($procedureId);
        $procedureType = $this->getProcedureType($procedure);
        $procedureStakeholder = $this->getMainStakeholder($procedure);

        $signatureButton = $this->signatureButtonService->find($signatureButtonId)->getInfo('entity');
        $signatureType = $this->signatureTypeService->find($signatureButton->getSignatureTypeId())->getInfo('entity');
        $signatureMethods = $signatureType->getMethod();
        $additionalInfo = $signatureType->getAdditionalInfo();
        $signature = $this->signatureService->createSignatureForProcedure(
            $procedure,
            $procedureType,
            $procedureStakeholder,
            $userId,
            $signatureMethods,
            $additionalInfo,
            $signatureType,
            $signatureButton
        );

        // PHPUnit Test: Impossible case due to function find return value of function createSignatureForProcedure is set to Signature|string, so will never be empty
        // @codeCoverageIgnoreStart
        if (empty($signature) || is_string($signature)) {
            if (is_string($signature)) {
                $response->setError($signature);
            }
            else {
                $response->setError($this->translate->t('Signature process could not be initialized. Please, check if there are valid files to be signed.'));
            }
        }
        else {
            $response->setMessage($this->translate->t('Signature process has been initialized successfully'));
        }
        // @codeCoverageIgnoreEnd

        $response->setInfo('entity', $procedure);
        return $response;
    }


    //
    // Cancellation processes
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Search for duplicated procedures and remove them.
     *
     * Search query
     * SELECT * FROM `oc_filecache` WHERE name = '{cita_id}' AND path NOT like 'files/{document_ciutada}/{cita_id}'
     *
     * @param Procedure $entity  the procedure for which its duplicates must be cancelled
     * @param string    $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, list}
     */
    protected function cancelDuplicatedProcedures(Procedure $entity, string $userId): ResponseInfo {
        $response = new ResponseInfo();

        $duplications = $this->entityMapper->findDuplicatedByExternalId($entity->getExternalId(), [$entity->getId()]);
        foreach($duplications as $duplicated) {
            $newResponse = $this->cancelProcedure($duplicated, $userId, ProcedureStatus::DUPLICATED);

            // Move the returned entity to a list of entities
            $response->addInfo('list', $newResponse->getInfo('entity'));
            $newResponse->deleteInfo('entity');

            // Merge any other information from the response
            $response->mergeResponseInfo($newResponse);
        }

        return $response;
    }

    /**
     * Cancel and anonymize a procedure. Cancellation is done if current
     * state is not a final state.
     *
     * If any mail is in queue to be sent, it is removed from the queue.
     *
     * It is compatible with procedures not registered in database, but that
     * have folders either created. Folders removal are always done, independently
     * of the current procedure state.
     *
     * @param Procedure $entity    procedure to cancel
     * @param string    $userId    identifier of user executing the operation
     * @param int       $newState  the new status; should be ProcedureStatus::CANCELLED,
     *                             ProcedureStatus::DUPLICATED or ProcedureStatus::EXPIRED
     * @param bool      $save      indicates if entity should be updated after change status or not
     * @return ResponseInfo  {result, entity}
     */
    public function cancelProcedure(Procedure $entity, string $userId, int $newStatus = ProcedureStatus::CANCELLED, bool $save = true): ResponseInfo {
        $response = new ResponseInfo();

        // We need to keep cancellable state from the beginning as after first
        // step the state will be changed and anoymization would never be produced
        $cancellable = ProcedureStatus::isValidTransition($entity->getStatusId(), $newStatus);

        // TODO: following code can be used when ProcedureTypeStatus is enabled
        //$procedureTypeStatus = $this->getProcedureTypeStatus($entity->getStatusId());
        //$cancellable = !$procedureTypeStatus->isFinalStatus();


        if ($cancellable) {
            // Cancel signatures
            $procedureType = $this->getProcedureType($entity);
            if ($procedureType->getAllowSignature()) {

                if ($this->signatureService->isProcedureWaitingForSignature($entity, $procedureType)) {
                    $response->setError($this->translate->t('The procedure cannot be cancelled because is waiting for signatures'));
                    return $response;
                }

                if ($this->signatureService->procedureHasSignatureWaitingForDownload($entity, $procedureType)) {
                    $response->setError($this->translate->t('The procedure cannot be cancelled because it has signatures waiting for download'));
                    return $response;
                }

                $this->signatureService->cancelSignaturesByProcedure($entity);
            }

            // Integration with the deck module
            $this->deleteDeckFromProcedure($entity);

            // Clean mail queue
            if (!empty($entity->getId())) {
                $this->mailQueueService->deleteByProcedureId($entity->getId());
            }

            // Clean local files
            try {
                $this->nextcloudHelper->deletePath($this->procedureFileService->getLocalPathToProcedure($entity, $userId));
            }
            // Ignore if data not found
            catch(DataNotFoundException $e) {}

            // Update entity to new status
            $entity = $this->setProcedureStatus($entity, $newStatus);
            $entity = $this->setProcedureModified($entity, $userId);
            if ($save) {
                $this->entityMapper->update($entity);
            }

            // TODO: l'anomització sempre s'hauria de fer amb un job... repensar si té sentit aqui
            //$this->anonymizeProcedure($entity);
        }

        $response->setInfo('entity', $entity);
        return $response;
    }


    //
    // Anonymize
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Anonymize given procedure.
     *
     * @param Procedure $procedure  procedure to anonymize
     * @return Procedure
     */
    public function anonymizeProcedure(Procedure $procedure): Procedure {
        $stakeholders = $this->procedureStakeholderMapper->findByProcedure($procedure);
        foreach($stakeholders as $procedureStakeholder) {
            $this->anonymizeProcedureStakeholder($procedureStakeholder);
        }

        $comments = BeHelper::random_string(200, ' ');
        $procedure->setComments($comments);

        return $this->entityMapper->update($procedure);
    }

    /**
     * Anonymize given procedure stakeholder.
     *
     * @param ProcedureStakeholder $procedureStakeholder  procedure stakeholder to anonymize
     * @return ProcedureStakeholder
     */
    protected function anonymizeProcedureStakeholder(ProcedureStakeholder $procedureStakeholder): ProcedureStakeholder {
        $personalId = BeHelper::random_string(12);
        $name = BeHelper::random_string(100, ' ');
        $phone = BeHelper::getRandomPhone($this->configService->getPhoneDefaultCountryCode(), $this->configService->getPhonePrefix());
        $email = BeHelper::random_string(15).'@anonymous.com';
        $comments = BeHelper::random_string(200, ' ');

        $procedureStakeholder->setPersonalId($personalId);
        $procedureStakeholder->setName($name);
        $procedureStakeholder->setPhone($phone);
        $procedureStakeholder->setEmail($email);

        return $this->procedureStakeholderMapper->update($procedureStakeholder);
    }


    //
    // Files management
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Return first-level files from the configured uploads folder. Returns an
     * empty array if uploads are not allowed or the folder is not configured.
     *
     * @param int $procedureId     The unique identifier of the procedure to which belongs the folder.
     * @param string|null $userId  The user that has done the request.
     *
     * @return ResponseInfo  {result, list:array<int,array{name:string,path:string,size:int,mtime:int,mimetype:string}>}
     */
    public function getUploadsFolderFiles(int $procedureId, string $userId = null): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $list = $this->procedureFileService->getUploadsFolderFiles($procedure, $userId);

        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Return first-level files from the configured downloads folder. Returns an
     * empty array if downloads are not allowed or the folder is not configured.
     *
     * @param int $procedureId     The unique identifier of the procedure to which belongs the folder.
     * @param string|null $userId  The user that has done the request.
     *
     * @return ResponseInfo  {result, list:array<int,array{name:string,path:string,size:int,mtime:int,mimetype:string}>}
     */
    public function getDownloadsFolderFiles(int $procedureId, string $userId = null): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $list = $this->procedureFileService->getDownloadsFolderFiles($procedure, $userId);

        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Return first-level files from the configured signature uploads folder. Returns
     * an empty array if signature is not allowed or the folder is not configured.
     *
     * @param int $procedureId     The unique identifier of the procedure to which belongs the folder.
     * @param string|null $userId  The user that has done the request.
     *
     * @return ResponseInfo  {result, list:array<int,array{name:string,path:string,size:int,mtime:int,mimetype:string}>}
     */
    public function getSignatureUploadsFolderFiles(int $procedureId, string $userId = null): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $list = $this->procedureFileService->getSignatureUploadsFolderFiles($procedure, $userId);

        // Add signature status info to files when applicable
        $signaturesByFile = $this->signatureService->findFileSignaturesByProcedure($procedure);

        foreach ($list as &$file) {
            $fileId = (int) ($file['id'] ?? 0);
            if (isset($signaturesByFile[$fileId])) {
                $file['status']            = (int) $signaturesByFile[$fileId]['status'];
                $file['status_date']       = $signaturesByFile[$fileId]['status_date'];
                $file['signature_type']    = $signaturesByFile[$fileId]['name'];
                $file['signature_file_id'] = $signaturesByFile[$fileId]['id'];
                $file['signature_id']      = $signaturesByFile[$fileId]['signature_id'];
                $file['file_id']           = $signaturesByFile[$fileId]['file_id'];
            }
        }
        unset($file); // Clean reference

        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Return first-level files from the configured signature downloads folder. Returns
     * an empty array if signature is not allowed or the folder is not configured.
     *
     * @param int $procedureId     The unique identifier of the procedure to which belongs the folder.
     * @param string|null $userId  The user that has done the request.
     *
     * @return ResponseInfo  {result, list:array<int,array{name:string,path:string,size:int,mtime:int,mimetype:string}>}
     */
    public function getSignatureDownloadsFolderFiles(int $procedureId, string $userId = null): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $list = $this->procedureFileService->getSignatureDownloadsFolderFiles($procedure, $userId);

        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Uploads a base64-encoded data URL to the uploads folder of a procedure.
     *
     * @param int $procedureId  The unique identifier of the procedure to which the file will be uploaded.
     * @param string $userId    The identifier of the user performing the upload.
     * @param string $filename  The filename.
     * @param string $data      The data URL to be uploaded (for example, an image encoded in base64).
     * @return ResponseInfo     {result, file}
     */
    public function uploadToUploadsFolder(int $procedureId, string $userId, string $filename, string $data): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $file = $this->procedureFileService->uploadToUploadsFolder($procedure, $userId, $filename, $data);

        $response = new ResponseInfo();
        $response->setInfo('file', $file);

        return $response;
    }

    /**
     * Uploads a base64-encoded data URL to the downloads folder of a procedure.
     *
     * @param int $procedureId  The unique identifier of the procedure to which the file will be uploaded.
     * @param string $userId    The identifier of the user performing the upload.
     * @param string $filename  The filename.
     * @param string $data      The data URL to be uploaded (for example, an image encoded in base64).
     * @return ResponseInfo     {result, file}
     */
    public function uploadToDownloadsFolder(int $procedureId, string $userId, string $filename, string $data): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $file = $this->procedureFileService->uploadToDownloadsFolder($procedure, $userId, $filename, $data);

        $response = new ResponseInfo();
        $response->setInfo('file', $file);

        return $response;
    }

    /**
     * Deletes a file from the uploads folder.
     *
     * @param int $procedureId
     * @param string $userId
     * @param string $filename
     * @return ResponseInfo
     */
    public function deleteFromUploadsFolder(int $procedureId, string $userId, string $filename): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $this->procedureFileService->deleteFromUploadsFolder($procedure, $userId, $filename);

        $response = new ResponseInfo();
        $response->setInfo('filename', $filename);
        $response->setInfo('deleted', true);

        return $response;
    }

    /**
     * Deletes a file from the downloads folder.
     *
     * @param int $procedureId
     * @param string $userId
     * @param string $filename
     * @return ResponseInfo
     */
    public function deleteFromDownloadsFolder(int $procedureId, string $userId, string $filename): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $this->procedureFileService->deleteFromDownloadsFolder($procedure, $userId, $filename);

        $response = new ResponseInfo();
        $response->setInfo('filename', $filename);
        $response->setInfo('deleted', true);

        return $response;
    }

    /**
     * Deletes a file from the signature uploads folder.
     *
     * @param int $procedureId
     * @param string $userId
     * @param string $filename
     * @return ResponseInfo
     */
    public function deleteFromSignatureUploadsFolder(int $procedureId, string $userId, string $filename): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $this->procedureFileService->deleteFromSignatureUploadsFolder($procedure, $userId, $filename);

        $response = new ResponseInfo();
        $response->setInfo('filename', $filename);
        $response->setInfo('deleted', true);

        return $response;
    }

    /**
     * Deletes a file from the signature downloads folder.
     *
     * @param int $procedureId
     * @param string $userId
     * @param string $filename
     * @return ResponseInfo
     */
    public function deleteFromSignatureDownloadsFolder(int $procedureId, string $userId, string $filename): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $this->procedureFileService->deleteFromSignatureDownloadsFolder($procedure, $userId, $filename);

        $response = new ResponseInfo();
        $response->setInfo('filename', $filename);
        $response->setInfo('deleted', true);

        return $response;
    }

    /**
     * Uploads a base64-encoded data URL to the uploads folder of a procedure.
     *
     * @param int $procedureId  The unique identifier of the procedure to which the file will be uploaded.
     * @param string $userId    The identifier of the user performing the upload.
     * @param string $filename  The filename.
     * @param string $data      The data URL to be uploaded (for example, an image encoded in base64).
     * @return ResponseInfo     {result, file}
     */
    public function uploadToSignatureUploadsFolder(int $procedureId, string $userId, string $filename, string $data): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $file = $this->procedureFileService->uploadToSignatureUploadsFolder($procedure, $userId, $filename, $data);

        $response = new ResponseInfo();
        $response->setInfo('file', $file);

        return $response;
    }

    /**
     * Uploads a base64-encoded data URL to the downloads folder of a procedure.
     *
     * @param int $procedureId  The unique identifier of the procedure to which the file will be uploaded.
     * @param string $userId    The identifier of the user performing the upload.
     * @param string $filename  The filename.
     * @param string $data      The data URL to be uploaded (for example, an image encoded in base64).
     * @return ResponseInfo     {result, file}
     */
    public function uploadToSignatureDownloadsFolder(int $procedureId, string $userId, string $filename, string $data): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');
        $file = $this->procedureFileService->uploadToSignatureDownloadsFolder($procedure, $userId, $filename, $data);

        $response = new ResponseInfo();
        $response->setInfo('file', $file);

        return $response;
    }

    /**
     * Get the content of a specific file from the uploads folder as a data URL payload.
     *
     * @param int         $procedureId  The procedure ID.
     * @param string      $filename     The file name to fetch.
     * @param string|null $userId       The requesting user (optional).
     *
     * @return ResponseInfo {result, filename, mimetype, data}
     */
    public function getUploadsFolderFileContent(int $procedureId, string $filename, string $userId = null): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');

        // Delegamos en ProcedureFileService (implementación esperada).
        $file = $this->procedureFileService->getUploadsFolderFileContent($procedure, $filename, $userId);

        $response = new ResponseInfo();
        $response->setInfo('filename', $file['filename'] ?? $filename);
        $response->setInfo('mimetype', $file['mimetype'] ?? 'application/octet-stream');
        $response->setInfo('data', $file['data'] ?? ''); // data URL (e.g. "data:...;base64,...")
        return $response;
    }

    /**
     * Get the content of a specific file from the downloads folder as a data URL payload.
     *
     * @param int         $procedureId  The procedure ID.
     * @param string      $filename     The file name to fetch.
     * @param string|null $userId       The requesting user (optional).
     *
     * @return ResponseInfo {result, filename, mimetype, data}
     */
    public function getDownloadsFolderFileContent(int $procedureId, string $filename, string $userId = null): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');

        $file = $this->procedureFileService->getDownloadsFolderFileContent($procedure, $filename, $userId);

        $response = new ResponseInfo();
        $response->setInfo('filename', $file['filename'] ?? $filename);
        $response->setInfo('mimetype', $file['mimetype'] ?? 'application/octet-stream');
        $response->setInfo('data', $file['data'] ?? '');
        return $response;
    }

    /**
     * Get the content of a specific file from the signature uploads folder as a data URL payload.
     *
     * @param int         $procedureId  The procedure ID.
     * @param string      $filename     The file name to fetch.
     * @param string|null $userId       The requesting user (optional).
     *
     * @return ResponseInfo {result, filename, mimetype, data}
     */
    public function getSignatureUploadsFolderFileContent(int $procedureId, string $filename, string $userId = null): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');

        $file = $this->procedureFileService->getSignatureUploadsFolderFileContent($procedure, $filename, $userId);

        $response = new ResponseInfo();
        $response->setInfo('filename', $file['filename'] ?? $filename);
        $response->setInfo('mimetype', $file['mimetype'] ?? 'application/octet-stream');
        $response->setInfo('data', $file['data'] ?? '');
        return $response;
    }

    /**
     * Get the content of a specific file from the signature downloads folder as a data URL payload.
     *
     * @param int         $procedureId  The procedure ID.
     * @param string      $filename     The file name to fetch.
     * @param string|null $userId       The requesting user (optional).
     *
     * @return ResponseInfo {result, filename, mimetype, data}
     */
    public function getSignatureDownloadsFolderFileContent(int $procedureId, string $filename, string $userId = null): ResponseInfo {
        $procedure = $this->find($procedureId)->getInfo('entity');

        $file = $this->procedureFileService->getSignatureDownloadsFolderFileContent($procedure, $filename, $userId);

        $response = new ResponseInfo();
        $response->setInfo('filename', $file['filename'] ?? $filename);
        $response->setInfo('mimetype', $file['mimetype'] ?? 'application/octet-stream');
        $response->setInfo('data', $file['data'] ?? '');
        return $response;
    }

    /**
     * Create the Readme file in the procedure folder.
     *
     * @param Procedure $procedure  the procedure for which readme must be created
     * @codeCoverageIgnore
     */
    protected function createReadme(Procedure $procedure) {
        $procedureStakeholder = $this->getMainStakeholder($procedure);
        $content = $this->procedureFileService->generateReadmeContent($procedure, $procedureStakeholder);

        $fullpath = $this->procedureFileService->getLocalPathToReadme($procedure);
        $folder = $this->nextcloudHelper->createPath(dirname($fullpath));

        $readme = $folder->newFile(basename($fullpath));
        $readme->putContent($content);
    }


    //
    // Deck integration
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Remove the deck cards for given procedure
     *
     * @param Procedure $procedure  the procedure for which remove all deck cards
     */
    public function deleteDeckFromProcedure(Procedure $procedure) {
        $procedureType = $this->getProcedureType($procedure);
        if (!$procedureType->getDeckIntegration()) {
            return;
        }

        $stakeholders = $this->procedureStakeholderMapper->findByProcedure($procedure);
        foreach($stakeholders as $stakeholder) {
            if (!empty($stakeholder->getDeckCardId())) {
                $this->deckHelper->deleteFromProcedureStakeholder($stakeholder);
                $stakeholder->setDeckCardId(null);
                $this->procedureStakeholderMapper->update($stakeholder);
            }
        }
    }


    //
    // Pagination
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Determine the page for each procedure based on the list obtained by the getter, and the
     * max number of items per page.
     *
     * @param DataResponse $response the data obtained from a GET call to obtain procedures
     * @return DataResponse with added data: total (number of procedures), pages (number of necessary pages),
     * resultsPerPage(max number of results visible at once per page)
     */
    public function paginateProcedures(DataResponse $response) : DataResponse {
        $resultsPerPage = $this->configService->getResultsPerPage();
        $data = $response->getData();
        $totalProcedures = count($response->getData()['list']);
        $data['total'] = $totalProcedures;
        $pages = ceil($totalProcedures / $resultsPerPage);
        $data['pages'] = $pages;
        $data['resultsPerPage'] = $resultsPerPage;

        $response->setData($data);
        return $response;
    }

    /**
     * Trim the list of procedures to only include the ones contained in the selected page.
     *
     * @param DataResponse $response the list of all resulting procedures with added data about pagination
     * @param int $selectedPage number of the page we want to retrieve
     * @return DataResponse with the list trimmed to only show the procedures contained in the selected page
     */
    public function producePage(DataResponse $response, int $selectedPage) : DataResponse {
        $data = $response->getData();
        $resultsPerPage = $data['resultsPerPage'];
        $list = $data['list'];

        $newList = [];
        $firstResult = (($selectedPage * $resultsPerPage) - (-1 + $resultsPerPage)-1) < $resultsPerPage ? 0 : ($selectedPage * $resultsPerPage)-(-1 + $resultsPerPage)-1;
        for ($i = 0; $i < $resultsPerPage; $i++) {
            if (!empty($list[$firstResult])) {
                $newList[] = $list[$firstResult];
            }
            $firstResult++;
        }
        $data['list'] = $newList;
        $data['page'] = $selectedPage;

        $response->setData($data);
        return $response;
    }

    /**
     * Check whether the pagination is active in the app.
     * @return bool true if active, false otherwise
     */
    public function isPaginationActive() {
        return $this->configService->getResultsPerPage() > 0;
    }


    //
    // CSV Download
    ////////////////////////////////////////////////////////////////////////////////
    /**
     * Force the download of CSV file with the data provided.
     * @param string $data   json encoded data to convert to csv string
     * @param string $encoding optional encoding, i.e. 'utf8_encoding'
     * @return DataDownloadResponse
     */
    public function downloadCsv(string $data, ?string $encoding = null) : DataDownloadResponse {
        $jsonLibrary = new JsonCsvConverter(null);
        $jsonLibrary->fromString($data);
        // To set a conversion option
        if ($encoding != null) {
            $jsonLibrary->setConversionKey($encoding, true);
        }
        // To convert JSON to CSV and force download on browser
        return $jsonLibrary->convertAndDownload('export_'.date('Ymd_Hi').'.csv');
    }

    /**
     * Aggregate the data that is visible on the procedures list in the front
     */
    public function collateDataForCsv($procedures, $stakeholders) {
        $result = [];

        $statusesDetails = ProcedureStatus::getDetails();
        $statuses = array_column($statusesDetails, 'value', 'key');

        foreach ($procedures as $procedure) {
            $procedureStakeholder = null;
            foreach ($stakeholders as $stakeholder) {
                if ($stakeholder->getProcedureId() == $procedure->id) {
                    $procedureStakeholder = $stakeholder;
                    break;
                }
            }
            $item = [
                'externalId' => $procedure->getExternalId(),
                'assignedGroupId' => $procedure->getAssignedGroupId(),
                'assignedUserId' => $procedure->getAssignedUserId(),
                'status' => $statuses[$procedure->getStatusId()],
                'topic' => $procedure->getTopic(),
                'appointmentDate' => $procedure->getAppointmentDate(),
                'stakeholderName' => '',
                'stakeholderCitizenId' => '',
                'stakeholderPhone' => '',
            ];
            if ($procedureStakeholder) {
                $item['stakeholderName'] = $procedureStakeholder->getName();
                $item['stakeholderCitizenId'] = $procedureStakeholder->getPersonalId();
                $item['stakeholderPhone'] = $procedureStakeholder->getPhone();
            }
            $result[] = $item;
        }
        $response = new ResponseInfo();
        $response->setInfo('list', $result);
        return $response;
    }


    //
    // Helpers
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Create a new Jitsi room code.
     *
     * @param Procedure $entity  the procedure object for which generate the code
     * @return string            the Jitsi room code
     * @throws DataNotFoundException
     */
    protected function createJitsiRoomCode(Procedure $entity, string $userId): string {
        $externalId = $entity->getExternalId();

        $procedureType = $this->getProcedureType($entity);
        $requireExternalId = $procedureType->getRequireExternalId();

        $context = $this->procedureTypeService->getContext($procedureType->getId(), $userId);
        $bypassRequired = $context->getInfo('bypassRequired');

        if (empty($externalId)) {
            if ($requireExternalId && $bypassRequired) {
                return '';
            }
            // NOTE PHPUnit Test: Impossible case due to condition returns error in validateEntity function, which is called earlier in the code (line 938)
            // @codeCoverageIgnoreStart
            if ($requireExternalId && !($bypassRequired)) {
                throw new \OCA\VTramit\Exception\DataNotFoundException(
                    $this->translate->t('To create the Jitsi room code you have to declare first the external ID')
                );
            }
            // @codeCoverageIgnoreEnd
        }

        //$url = empty($externalId) ? BeHelper::random_string(16) : $externalId;
        $template = $this->configService->getJitsiRoomPattern();
        $url = $this->procedureTokenService->applyReplacements($entity, null, $template);

        return urlencode(strtolower($url));
    }

    /**
     * Create a new Jitsi room URL for the staff.
     *
     * @param Procedure $entity  the procedure object for which generate the URL
     * @return string            the Jitsi room URL
     * @throws DataNotFoundException
     */
    protected function createJitsiRoomForStaff(Procedure $entity): string {
        $procedureType = $this->getProcedureType($entity);
        if (!$procedureType->getAllowVideo()) {
            return '';
        }

        if (empty($entity->getJitsiRoomCode())) {
            throw new DataNotFoundException($this->translate->t('To create the Jitsi room URL for the staff you have to generate first a Jitsi room code'));
        }

        if (empty($procedureType->getJitsiStaffUrl())) {
            throw new DataNotFoundException($this->translate->t('There is no base Jitsi URL defined to create the Jitsi room URL for the staff'));
        }

        $baseUrl = rtrim($procedureType->getJitsiStaffUrl(), '/');
        $url = $baseUrl . '/' . $entity->getJitsiRoomCode();

        return $url;
    }

    /**
     * Indicates if appointment date for the procedure is today. If no appointment
     * date is required and no appointment date is set, then each day is the appointment
     * date.
     *
     * @param Procedure $entity             the procedure to check if appointment date is today
     * @param ProcedureType $procedureType  the associated procedure type
     * @return bool                         true if appointment date is today; otherwise, false
     */
    protected function isAppointmentDateToday(Procedure $entity, ProcedureType $procedureType = null): bool {
        if (empty($procedureType)) {
            $procedureType = $this->getProcedureType($entity);
        }

        return (!$procedureType->getRequireAppointmentDate() && empty($entity->getAppointmentDate()))
            || $entity->isAppointmentDateToday($this->configService->getDateTimezone())
        ;
    }

    /**
     * Get the Jitsi Room URL for Staff users for the given procedure procedure.
     *
     * @param \Procedure $procedure
     *   The procedure ID or a Procedure instance.
     *
     * @return ResponseInfo
     *   The response containing the full Jitsi room URL.
     */
    protected function prepareFullJitsiRoomStaffUrl(Procedure $procedure): string {
        $url = $procedure->getJitsiRoomStaffUrl();

        $jwt = $this->prepareJitsiRoomStaffJwt($procedure);
        if ($jwt !== '') {
            $url .= '?jwt=' . $jwt;
        }

        return $url;
    }

    /**
     * Get the Jitsi Room JWT data for Staff users for the given procedure.
     *
     * @param \Procedure $procedure
     *   The procedure ID or a Procedure instance.
     *
     * @return string
     *   The generated JWT or an empty string if no payload is defined.
     */
    protected function prepareJitsiRoomStaffJwt(Procedure $procedure): string {
        $jwt = '';

        $procedureType = $this->getProcedureType($procedure);
        if (!empty($procedureType->getJitsiStaffJwtPayload())) {
            $template = $procedureType->getJitsiStaffJwtPayload();
            $secret = $procedureType->getJitsiStaffJwtSecret();

            $stakeholder = $this->getMainStakeholder($procedure);
            $payload = $this->procedureTokenService->applyReplacements($procedure, $stakeholder, $template);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // TODO: Manange the error, maybe throw an exception
                //       Also add a validation on the admin zone before save the JSON payload
            }

            $jwtHelper = new JwtHelper();
            $jwt = $jwtHelper->generateJitsiJWT(json_decode($payload, true), $secret);
        }

        return $jwt;
    }
}
