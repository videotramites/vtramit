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
use OCP\IURLGenerator;
use OCP\AppFramework\Db\Entity;
use OCP\Files\IRootFolder;
use OCP\Share\IShare;

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

use OCA\VTramit\Helper\BeHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Helper\JwtHelper;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureTokenService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\WebhookService;

class ProcedureStakeholderService extends EntityService {

    /** @var IRootFolder */
    private $rootFolder;

    /** @var IURLGenerator */
    private $urlGenerator;

    /** @var MailTemplateMapper */
    private $mailTemplateMapper;

    /** @var ProcedureMapper */
    private $procedureMapper;

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

    /** @var ProcedureService */
    private $procedureService;

    /** @var MailQueueService */
    private $mailQueueService;

    /** @var SignatureService */
    private $signatureService;

    /** @var ProcedureFileService */
    private $procedureFileService;

    /** @var ProcedureMailService */
    private $procedureMailService;

    /** @var ProcedureMessageService */
    private $procedureMessageService;

    /** @var ProcedureTokenService */
    private $procedureTokenService;

    /** @var ProcedureTypeService */
    private $procedureTypeService;

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
        ProcedureService $procedureService,
        MailQueueService $mailQueueService,
        SignatureService $signatureService,
        ProcedureFileService $procedureFileService,
        ProcedureMailService $procedureMailService,
        ProcedureMessageService $procedureMessageService,
        ProcedureTokenService $procedureTokenService,
        ProcedureTypeService $procedureTypeService,
        WebhookService $webhookService
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $procedureStakeholderMapper, $webhookService);

        $this->rootFolder = $rootFolder;
        $this->urlGenerator = $urlGenerator;
        $this->mailTemplateMapper = $mailTemplateMapper;
        $this->procedureMapper = $procedureMapper;
        $this->procedureTypeMapper = $procedureTypeMapper;
        $this->procedureTypeGroupMapper = $procedureTypeGroupMapper;
        $this->nextcloudHelper = $nextcloudHelper;
        $this->userGroupHelper = $userGroupHelper;
        $this->deckHelper = $deckHelper;
        $this->procedureService = $procedureService;
        $this->mailQueueService = $mailQueueService;
        $this->signatureService = $signatureService;
        $this->procedureFileService = $procedureFileService;
        $this->procedureMailService = $procedureMailService;
        $this->procedureMessageService = $procedureMessageService;
        $this->procedureTokenService = $procedureTokenService;
        $this->procedureTypeService = $procedureTypeService;
        $this->webhookService = $webhookService;
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new ProcedureStakeholder();
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
        return ['procedureId', 'stakeholderUploadsUrl', 'stakeholderDownloadsUrl', 'jitsiRoomStakeholderUrl', 'entryPage', 'entryPassword'];
    }

    /**
     * Obtain the procedure which belong the given procedure stakeholder.
     *
     * @param ProcedureStakeholder $entity  the stakeholder
     * @return Procedure
     */
    protected function getProcedure(ProcedureStakeholder $entity): Procedure {
        return $this->procedureMapper->find($entity->getProcedureId());
    }

    /**
     * Obtain the procedure type related to given procedure.
     *
     * @param Procedure $entity  the procedure
     * @return ProcedureType
     */
    protected function getProcedureType(Procedure $entity): ProcedureType {
        return $this->procedureTypeMapper->find($entity->getProcedureTypeId());
    }


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
            return $this->entityMapper->findMainByProcedure($entity);
        }
        catch (EntityNotFoundException $e) {
            return null;
        }
    }

    /**
     * Builds the full public entry page URL for a given stakeholder.
     *
     * @param \App\Entity\ProcedureStakeholder|int $entity
     *   The stakeholder entity or its numeric ID.
     *
     * @return string
     *   The full public entry page URL, including the access key if available.
     */
    public function getFullEntryPage(ProcedureStakeholder|int $entity) {
        if (is_int($entity)) {
            $response = $this->find($entity);
            $entity = $response->getInfo('entity');
        }

        $entryPage = $entity->getEntryPage();

        $entryPassword = '';
        if (!empty($entity->getEntryPassword())) {
            $entryPassword = $entity->getEntryPassword($this->configService->getEncryptionKey());
        }

        $fullEntryPage = !empty($entryPassword)
            ? $entryPage . '?ak=' . $entryPassword
            : $entryPage;

        $response = new ResponseInfo();
        $response->setInfo('fullEntryPage', $fullEntryPage);
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

        $url = $this->getFullJitsiRoomStakeholderUrl($entity);
        $entity->setFullJitsiRoomStakeholderUrl($url);
        $response->setInfo('entity', $entity);

        return $response;
    }

    /**
     * Get filtered procedures stakeholders.
     *
     * Valid values for $filter:
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

        $list = $this->entityMapper->findMainByFilter($filter);
        $response->setInfo('list', $list);
        return $response;
    }

    /**
     * Validates entity information being created.
     *
     * @override
     * @param Entity|ProcedureStakeholder $entity  the entity object to validate
     * @param string                      $userId  identifier of user executing the operation
     * @return ResponseInfo  {result}
     */
    protected function validateEntityToCreate(Entity $entity, string $userId): ResponseInfo {
        return $this->validateEntity($entity, $userId);
    }

    /**
     * Operations to apply before create entity.
     *
     * @override
     * @param Entity|ProcedureStakeholder $entity  the entity object being created
     * @param string                      $userId  identifier of user executing the operation
     * @return Entity|ProcedureStakeholder         the entity object being created with any applied modification
     */
    protected function beforeCreate(Entity $entity, string $userId): Entity {
        $procedure = $this->getProcedure($entity);
        $entity->setJitsiRoomStakeholderUrl($this->createJitsiRoomForStakeholder($procedure));
        $entity->setEntryPage($this->getUrlToPublicPage($procedure));

        // Ensure the password is created
        $entity = $this->createPassword($entity, false);

        // Set de main stakeholder
        $mainStakeholder = $this->getMainStakeholder($procedure);
        if (empty($mainStakeholder)) {
            $entity->setIsMain(true);
        }
        else if ($entity->getIsMain() == true && $mainStakeholder->getIsMain() == true) {
            $mainStakeholder->setIsMain(false);
            $this->entityMapper->update($mainStakeholder);
        }
        else if ($mainStakeholder->getIsMain() == false) {
            $entity->setIsMain(true);
        }

        $this->normalizePhone($entity);

        return $entity;
    }

    /**
     * Operations to apply after create entity.
     *
     * @override
     * @param Entity|ProcedureStakeholder $entity  the entity object that has been created
     * @param string                      $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, ?message}
     */
    protected function afterCreate(Entity $entity, string $userId): ResponseInfo {
        $response = new ResponseInfo();
        $procedure = $this->getProcedure($entity);

        try {
            $procedureStakeholder = $this->shareFolderWithStakeholder($entity, $procedure, false, $userId);
        }
        catch(DataNotFoundException $e) {
            $response->setException($e->getMessage());
        }

        $newResponse = $this->procedureService->onStakeholderAdded($procedure, $entity, $userId);
        $response->mergeResponseInfo($newResponse);

        $messagetoStakeholdersResponse = $this->procedureMessageService->sendMessagesToStakeholder($entity, $procedure);
        $response->mergeResponseInfo($messagetoStakeholdersResponse);

        // DEPRECATED: Remove with EmailTemplates system
        if (!$messagetoStakeholdersResponse->isOk()) {
            $mailtoStakeholdersResponse = $this->procedureMailService->sendEmailToStakeholder($entity, $procedure);
            $response->mergeResponseInfo($mailtoStakeholdersResponse);
        }
        // END DEPRECATED

        if ($entity->getIsMain() == true) {
            $messageToStaffResponse = $this->procedureMessageService->sendMessagesToStaff($entity, $procedure);
            $response->mergeResponseInfo($messageToStaffResponse);

            // DEPRECATED: Remove with EmailTemplates system
            if (!$messageToStaffResponse->isOk()) {
                $mailToStaffResponse = $this->procedureMailService->sendEmailToStaff($entity, $procedure);
                $response->mergeResponseInfo($mailToStaffResponse);
            }
            // END DEPRECATED
        }

        // Integration with the 'deck' module
        $this->syncWithDeck($entity);

        // Notify the webhooks
        if ($response->isOk()) {
            $this->webhookService->event("create-stakeholder", "vtproc_stakeholder", $entity);
        }

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
        return $this->validateEntity($newEntity, $userId);
    }

    /**
     * Operations to apply before update entity.
     *
     * @param Entity|ProcedureStakeholder $newEntity  the entity with the new data; that is the object that should be modified
     * @param Entity|ProcedureStakeholder $oldEntity  the existing entity object
     * @param array                       $diff       the different elements between the new and old entities
     * @param string                      $userId     identifier of user executing the operation
     * @return Entity|ProcedureStakeholder            the entity object being updated with any applied modification
     */
    protected function beforeUpdate(Entity $newEntity, Entity $oldEntity, array $diff, string $userId): Entity {
        // Password should never travel as it is managed internally
        // Ignore new password and hold the one created internally
        //$newEntity->setEntryPassword($oldEntity->getEntryPassword());

        // Set the main stakeholder
        $procedure = $this->getProcedure($oldEntity);
        $mainStakeholder = $this->getMainStakeholder($procedure);
        if (empty($mainStakeholder)) {
            $newEntity->setIsMain(true);
        }
        else if ($newEntity->getIsMain() == true && $newEntity->getId() != $mainStakeholder->getId()) {
            $mainStakeholder->setIsMain(false);
            $this->entityMapper->update($mainStakeholder);
        }
        else if ($newEntity->getId() == $mainStakeholder->getId()) {
            $newEntity->setIsMain(true);
        }
        else if ($mainStakeholder->getIsMain() == false) {
            $newEntity->setIsMain(true);
        }

        $this->normalizePhone($newEntity);

        return $newEntity;
    }

    /**
     * Operations to apply after update entity.
     *
     * @override
     * @param Entity|ProcedureStakeholder $entity  the entity object that has been updated
     * @param array                       $diff    the different elements between the new and old entities
     * @param string                      $userId  identifier of user executing the operation
     * @return ResponseInfo  {result}
     */
    protected function afterUpdate(Entity $entity, array $diff, string $userId): ResponseInfo {
        $procedure = $this->getProcedure($entity);
        $entity = $this->shareFolderWithStakeholder($entity, $procedure, false, $userId);

        // Integration with the 'deck' module
        $this->syncWithDeck($entity);

        // Notify the webhooks
        $this->webhookService->event("update-stakeholder", "vtproc_stakeholder", $entity);

        return new ResponseInfo();
    }

    /**
     * Validates procedure stakeholder entity information.
     *
     * @param ProcedureStakeholder $entity  the procedure stakeholder object to validate
     * @param string               $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, ?message}
     */
    protected function validateEntity(ProcedureStakeholder $entity, string $userId): ResponseInfo {
        $response = new ResponseInfo();

        if (empty($entity->getProcedureId())) {
            // If no procedure defined, do not continue
            $response->addError($this->translate->t('The stakeholder must belong to a procedure'));
            return $response;
        }

        $procedure = $this->getProcedure($entity);
        $procedureType = $this->getProcedureType($procedure);

        $context = $this->procedureTypeService->getContext($procedureType->getId(), $userId);
        $bypassRequired = $context->getInfo("bypassRequired");

        if (!$bypassRequired && $procedureType->getRequireStakeholderEmail() && empty($entity->getEmail())
            || !$this->userGroupHelper->isValidEmail($entity->getEmail())
        ) {
            $response->addError($this->translate->t('Email is not valid'));
        }

        if (!$bypassRequired && empty($entity->getName())) {
            $response->addError($this->translate->t('Please indicate the stakeholder name'));
        }

        if (!$bypassRequired && $procedureType->getRequireStakeholderPhone() && empty($entity->getPhone())) {
            $response->addError($this->translate->t('Please indicate the stakeholder phone'));
        }

        if (!empty($entity->getPhone()) && !BeHelper::isValidPhone($entity->getPhone())) {
            $response->addError($this->translate->t('Phone number is not valid'));
        }

        return $response;
    }

    /**
     * Delete entity by given id.
     *
     * @param int|ProcedureStakeholder $id  identifier of the entity or the entity itself to delete
     * @return ResponseInfo  {result}
     */
    public function delete($id): ResponseInfo {
        if (is_int($id)) {
            $entity = $this->entityMapper->find($id);
        }
        else {
            $entity = $id;
        }

        // Unlink deck card
        if (!empty($entity->getDeckCardId())) {
            $this->deckHelper->deleteFromProcedureStakeholder($entity);
        }

        // Delete entity
        $this->entityMapper->delete($entity);

        // Manage main stakeholder
        if ($entity->getIsMain()) {

            // Set the main stakeholder
            $procedure = $this->getProcedure($entity);
            $mainStakeholder = $this->getMainStakeholder($procedure);
            if ($mainStakeholder != null) {
                $mainStakeholder->setIsMain(true);
                $this->entityMapper->update($mainStakeholder);
            }
        }

        // Notify the webhooks
        $this->webhookService->event("delete-stakeholder", "vtproc_stakeholder", $entity);

        $response = new ResponseInfo();
        return $response;
    }

    /**
     * Delete all the stakeholders related to given procedure.
     *
     * @param int $id  identifier of the procedure which belongs the stakeholders to delete
     * @return ResponseInfo  {result}
     */
    public function deleteByProcedureId(int $procedureId): ResponseInfo {
        $response = new ResponseInfo();

        $procedureStakeholderList = $this->entityMapper->findByProcedureId($procedureId);
        foreach($procedureStakeholderList as $procedureStakeholder) {
            $deleteResponse = $this->delete($procedureStakeholder->getId());
            $response->mergeResponseInfo($deleteResponse);
        }

        return $response;
    }

    /**
     * Force a password change.
     *
     * @param int $id  identifier of the stakeholder to change the password
     * @return ResponseInfo  {result, ?message}
     */
    public function changePassword(int $id, string $userId): ResponseInfo {
        $response = new ResponseInfo();
        $entity = $this->entityMapper->find($id);
        $procedure = $this->getProcedure($entity);

        $this->createPassword($entity, true);
        $response->addMessage($this->translate->t('Password changed'));

        try {
            $this->shareFolderWithStakeholder($entity, $procedure, true, $userId);
        }
        catch(\Throwable $e) {
            $response->addError($this->translate->t('Folders could not be shared using the new password'));
        }

        try {
            $messagetoStakeholdersResponse = $this->procedureMessageService->sendMessagesToStakeholder($entity, $procedure);
            $response->mergeResponseInfo($messagetoStakeholdersResponse);

            // DEPRECATED: Remove with EmailTemplates system
            if (!$messagetoStakeholdersResponse->isOk()) {
                $mailtoStakeholdersResponse = $this->procedureMailService->sendEmailToStakeholder($entity, $procedure);
                $response->mergeResponseInfo($mailtoStakeholdersResponse);
            }
            // END DEPRECATED
        }
        catch(\Throwable $e) {
            $response->addError($this->translate->t('The email could not be sent'));
        }

        return $response;
    }

    /**
     * TODO: Rename to sendMessagesToStakeholder when EmailTemplates system is removed
     *
     * Prepare and send an e-mail to given stakeholder recipient.
     *
     * @param int $id  identifier of the stakeholder to send the e-mail
     * @return ResponseInfo  {result, ?message}
     */
    public function sendEmailToStakeholder(int $id): ResponseInfo {
        $procedureStakeholder = $this->entityMapper->find($id);
        $procedure = $this->getProcedure($procedureStakeholder);

        $response = $this->procedureMessageService->sendMessagesToStakeholder($procedureStakeholder, $procedure);

        // DEPRECATED: Remove with EmailTemplates system
        if (!$response->isOk()) {
            $response = $this->procedureMailService->sendEmailToStakeholder($procedureStakeholder, $procedure);
        }
        // END DEPRECATED

        return $response;
    }


    //
    // Main actions by procedure ID
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the procedure stakeholders that belongs to given procedure.
     *
     * @param int $procedureId  identifier of the procedure which belongs the stakeholders to retrieve
     * @return ResponseInfo  {result, list[]}
     */
    public function findByProcedureId(int $procedureId): ResponseInfo {
        $list = $this->entityMapper->findByProcedureId($procedureId);
        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Get the main procedure stakeholder that belongs to given procedure.
     *
     * @param int $procedureId  identifier of the procedure which belongs the main stakeholder to retrieve
     * @return ResponseInfo  {result, list[]}
     */
    public function findMainByProcedureId(int $procedureId): ResponseInfo {
        $entity = $this->entityMapper->findMainByProcedureId($procedureId);
        $response = new ResponseInfo();
        $response->setInfo('entity', $entity);

        return $response;
    }


    //
    // Files management
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Share folders with given stakeholder. It returns the entity updated.
     *
     * @param ProcedureStakeholder $procedureStakeholder  the stakeholder for which sharing must be created
     * @param Procedure            $procedure             the procedure which belongs the stakeholder; if null, the sytem try to find the
     *                                                    procedure related with given stakeholder
     * @param bool                 $force                 indicates wether to share the folders even if already shared
     * @param string               $userId                identifier of user executing the operation
     * @return ProcedureStakeholder                       updated stakeholder with the links to shared folders
     */
    public function shareFolderWithStakeholder(ProcedureStakeholder $procedureStakeholder, Procedure $procedure, $force, string $userId): ProcedureStakeholder {
        $password = $this->getStakeholderPassword($procedureStakeholder);
        $procedureType = $this->getProcedureType($procedure);
        $procedureUpdated = false;
        $stakeholderUpdated = false;

        $this->procedureFileService->createFolders($procedure, $userId);

        // Staff upload url
        if ($procedureType->getAllowUpload() && empty($procedure->getStaffUploadsUrl())) {
            $webpath = $this->procedureFileService->getWebPathToFolder($procedure, $procedureType->getUploadsFolder());
            $staffUrlToShare = $this->urlGenerator->getAbsoluteURL($webpath);
            $procedure->setStaffUploadsUrl($staffUrlToShare);
            $procedureUpdated = true;
        }

        // Staff download url
        if ($procedureType->getAllowDownload() && empty($procedure->getStaffDownloadsUrl())) {
            $webpath = $this->procedureFileService->getWebPathToFolder($procedure, $procedureType->getDownloadsFolder());
            $staffUrlToShare = $this->urlGenerator->getAbsoluteURL($webpath);
            $procedure->setStaffDownloadsUrl($staffUrlToShare);
            $procedureUpdated = true;
        }

        if ($procedureUpdated) {
           $this->procedureMapper->update($procedure);
        }

        // Shared link for upload/download folder
        // The generated link is always the one of the main stakeholder (see 'getUserRelativePathToFolder')
        if ($procedureType->getAllowUpload()
            && $procedureType->getAllowDownload()
            && $procedureType->getUploadsFolder() == $procedureType->getDownloadsFolder()
            && (empty($procedureStakeholder->getStakeholderUploadsUrl()) || empty($procedureStakeholder->getStakeholderDownloadsUrl()))
        ) {
            $url = $this->procedureFileService->getUserRelativePathToFolder($procedure, $procedureType->getUploadsFolder());
            $stakeholderUrlToShare = $this->nextcloudHelper->shareFolder($url, $password, true, true);
            $procedureStakeholder->setStakeholderUploadsUrl($stakeholderUrlToShare);
            $procedureStakeholder->setStakeholderDownloadsUrl($stakeholderUrlToShare);
            $stakeholderUpdated = true;
        }
        else {

            // Shared link for upload folder
            // The generated link is always the one of the main stakeholder (see 'getUserRelativePathToFolder')
            if ($procedureType->getAllowUpload() && empty($procedureStakeholder->getStakeholderUploadsUrl())) {
                $url = $this->procedureFileService->getUserRelativePathToFolder($procedure, $procedureType->getUploadsFolder());
                $stakeholderUrlToShare = $this->nextcloudHelper->shareFolder($url, $password, true, false);
                $procedureStakeholder->setStakeholderUploadsUrl($stakeholderUrlToShare);
                $stakeholderUpdated = true;
            }

            // Shared link for download folder
            // The generated link is always the one of the main stakeholder (see 'getUserRelativePathToFolder')
            if ($procedureType->getAllowDownload() && empty($procedureStakeholder->getStakeholderDownloadsUrl())) {
                if (!$procedureType->getAllowUpload() || $procedureType->getUploadsFolder() != $procedureType->getDownloadsFolder()) {
                    $url = $this->procedureFileService->getUserRelativePathToFolder($procedure, $procedureType->getDownloadsFolder());
                    $stakeholderUrlToShare = $this->nextcloudHelper->shareFolder($url, $password, false, true);
                }
                $procedureStakeholder->setStakeholderDownloadsUrl($stakeholderUrlToShare);
                $stakeholderUpdated = true;
            }
        }

        if ($stakeholderUpdated) {
            $procedureStakeholder = $this->entityMapper->update($procedureStakeholder);
        }

        return $procedureStakeholder;
    }


    //
    // Deck integration
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Update the deck information
     *
     * @param ProcedureStakeholder $entity  the stakeholder for which update the deck card
     */
    protected function syncWithDeck(ProcedureStakeholder $entity) {
        $procedure = $this->getProcedure($entity);
        $procedureType = $this->getProcedureType($procedure);
        if ($procedureType->getDeckIntegration()) {
            if (empty($procedureType->getDeckStack())) {
                throw new DataNotFoundException($this->translate->t('The deck integration is enabled but not configured'));
            }
            $content = $this->procedureFileService->generateReadmeContent($procedure, $entity);
            if (empty($entity->getDeckCardId())) {
                $deckCardId = $this->deckHelper->createFromProcedure($procedureType->getDeckStack(), $procedure, $entity, $content);
                if ($deckCardId) {
                    $entity->setDeckCardId($deckCardId);
                    $this->entityMapper->update($entity);
                }
            }
            else {
                $this->deckHelper->updateFromProcedure($procedureType->getDeckStack(), $procedure, $entity, $content);
            }
        }
    }


    //
    // Helpers
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Create a new Jitsi room URL for the staff.
     *
     * @param Procedure $entity  the procedure object for which generate the URL
     * @return string            the Jitsi room URL
     * @throws DataNotFoundException
     */
    protected function createJitsiRoomForStakeholder(Procedure $entity): string {
        $procedureType = $this->getProcedureType($entity);
        if (!$procedureType->getAllowVideo()) {
            return '';
        }

        if (empty($entity->getJitsiRoomCode())) {
            throw new DataNotFoundException($this->translate->t('To create the Jitsi room URL for the stakeholders you have to generate first a Jitsi room code'));
        }

        if (empty($procedureType->getJitsiStakeholderUrl())) {
            throw new DataNotFoundException($this->translate->t('There is no base Jitsi URL defined to create the Jitsi room URL for the staff'));
        }

        $url = $procedureType->getJitsiStakeholderUrl()
            . '/'
            . $entity->getJitsiRoomCode()
        ;

        return $url;
    }

    /**
     * Get the URL to the public page
     *
     * @param Procedure $procedure  the procedure which will interact with the public page
     * @return string               the URL
     */
    protected function getUrlToPublicPage(Procedure $procedure): string {
        return $this->urlGenerator->getAbsoluteURL($this->procedureFileService->getWebPathToPublicPage($procedure));
    }

    /**
     * Generates a new password and is set in given procedure stakeholder object.
     * If the procedure stakeholder has any file for signature, a new link will be
     * generated to be used with the new password.
     *
     * @param ProcedureStakeholder $entity  stakeholder for which generate the new password
     * @param bool                 $force   whether force to create a new password even if one exists
     * @return ProcedureStakeholder         the updated entity wich includes the password
     */
    protected function createPassword(ProcedureStakeholder $entity, bool $force = false): ProcedureStakeholder {
        $isPasswordChanged = false;
        if (!empty($entity->getEntryPassword())) {
            if (!$force) {
                return $entity;
            }

            $isPasswordChanged = true;
        }

        $password = BeHelper::random_string(10);
        $encryptionKey = $this->configService->getEncryptionKey();
        $cryptedPassword = BeHelper::encrypt($password, $encryptionKey);

        $entity->setEntryPassword($cryptedPassword);

        // Update entity only if already registered in database
        if (!empty($entity->getId())) {
            $entity = $this->entityMapper->update($entity);
        }

        if ($isPasswordChanged) {
            $this->signatureService->shareAllSignatureFilesInProcedureStakeholder($entity);
        }

        return $entity;
    }

    /**
     * Obtain the stakeholder decrypted password. If no password exists, a new one is created.
     *
     * @param ProcedureStakeholder $entity  stakeholder for which retrieve the password
     * @return string                       the decrypted password
     */
    protected function getStakeholderPassword(ProcedureStakeholder $entity): string {
        $entity = $this->createPassword($entity, false);
        return $entity->getEntryPassword($this->configService->getEncryptionKey());
    }

    /**
     * Normalizes the phone number of given entity.
     *
     * @param ProcedureStakeholder $entity  the procedure stakeholder object for which normalize phone number
     * @return ProcedureStakeholder         the entity updated
     */
    protected function normalizePhone(ProcedureStakeholder $entity) {
        if (!empty($entity->getPhone())) {
            $phone = BeHelper::normalizePhone(
                $entity->getPhone(),
                $this->configService->getPhoneDefaultCountryCode(),
                $this->configService->getPhonePrefix()
            );

            $entity->setPhone($phone);
        }

        return $entity;
    }

    /**
     * Get the Jitsi Room URL for stakeholder users for the given procedure procedure.
     *
     * @param ProcedureStakeholder $stakeholder
     *   The stakeholder ID or a ProcedureStakeholder instance.
     *
     * @return ResponseInfo
     *   The response containing the full Jitsi room URL.
     */
    public function getFullJitsiRoomStakeholderUrl(ProcedureStakeholder $stakeholder): string {
        $url = $stakeholder->getJitsiRoomStakeholderUrl();
        $jwt = $this->getJitsiRoomStakeholderJwt($stakeholder);
        if ($jwt !== '') {
            $url .= '?jwt=' . $jwt;
        }

        return $url;
    }

    /**
     * Get the Jitsi Room JWT data for stakeholder users for the given procedure.
     *
     * @param ProcedureStakeholder $stakeholder
     *   The stakeholder ID or a ProcedureStakeholder instance.
     *
     * @return string
     *   The generated JWT or an empty string if no payload is defined.
     */
    public function getJitsiRoomStakeholderJwt(ProcedureStakeholder $stakeholder): string {
        $jwt = '';

        $procedure = $this->getProcedure($stakeholder);
        $procedureType = $this->getProcedureType($procedure);
        if (!empty($procedureType->getJitsiStakeholderJwtPayload())) {
            $template = $procedureType->getJitsiStakeholderJwtPayload();
            $secret = $procedureType->getJitsiStakeholderJwtSecret();

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
