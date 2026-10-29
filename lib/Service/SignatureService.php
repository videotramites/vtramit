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

use Exception;
use OC;

use OCA\VTramit\Exception\EntityNotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;
use \OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCP\IL10N;

use OCA\VTramit\Constants;
use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\SignatureStatus;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ProcedureType;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\Signature;
use OCA\VTramit\Db\SignatureMapper;
use OCA\VTramit\Db\SignatureFile;
use OCA\VTramit\Db\SignatureFileMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\SignatureType;
use OCA\VTramit\Db\SignatureButton;
use OCA\VTramit\Enum\ActivityType;
use OCA\VTramit\Exception\SignatureNotFound;

use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\MailTemplateService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Helper\BeHelper;
use OCA\VTramit\Helper\CurlHelper;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;

class SignatureService {

    /** @var LoggerInterface */
    private $logger;

    /** @var IL10N */
    private $translate;

    /** @var ActivityManager */
    protected $activityManager;

    /** @var SignatureMapper */
    private $signatureMapper;

    /** @var SignatureFileMapper */
    private $signatureFileMapper;

    /** @var ConfigService */
    private $configService;

    /** @var MailQueueService */
    private $mailQueueService;

    /** @var MailTemplateService */
    private $mailTemplateService;

    /** @var ProcedureMapper */
    private $procedureMapper;

    /** @var ProcedureTypeGroupMapper */
    private $procedureTypeGroupMapper;

    /** @var ProcedureTypeService */
    private $procedureTypeService;

    /** @var ProcedureStakeholderMapper */
    private $procedureStakeholderMapper;

    /** @var ProcedureFileService */
    private $procedureFileService;

    /** @var ProcedureMailService */
    private $procedureMailService;

    /** @var ProcedureMessageService */
    private $procedureMessageService;

    /** @var CurlHelper */
    private $curlHelper;

    /** @var NextcloudHelper */
    private $nextcloudHelper;

    /** @var UserGroupHelper */
    private $userGroupHelper;

    /** @var SignatureTypeService */
    private $signatureTypeService;

    /** @var IRootFolder */
    private $rootFolder;

    /** @var IURLGenerator */
    private $urlGenerator;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        SignatureMapper $signatureMapper,
        SignatureFileMapper $signatureFileMapper,
        ConfigService $configService,
        MailQueueService $mailQueueService,
        MailTemplateService $mailTemplateService,
        ProcedureMapper $procedureMapper,
        ProcedureTypeGroupMapper $procedureTypeGroupMapper,
        ProcedureTypeService $procedureTypeService,
        ProcedureStakeholderMapper $procedureStakeholderMapper,
        ProcedureFileService $procedureFileService,
        ProcedureMailService $procedureMailService,
        ProcedureMessageService $procedureMessageService,
        CurlHelper $curlHelper,
        NextcloudHelper $nextcloudHelper,
        UserGroupHelper $userGroupHelper,
        SignatureTypeService $signatureTypeService,
        IRootFolder $rootFolder,
        IURLGenerator $urlGenerator
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->activityManager = $activityManager;
        $this->signatureMapper = $signatureMapper;
        $this->signatureFileMapper = $signatureFileMapper;
        $this->configService = $configService;
        $this->mailQueueService = $mailQueueService;
        $this->mailTemplateService = $mailTemplateService;
        $this->procedureMapper = $procedureMapper;
        $this->procedureTypeGroupMapper = $procedureTypeGroupMapper;
        $this->procedureTypeService = $procedureTypeService;
        $this->procedureStakeholderMapper = $procedureStakeholderMapper;
        $this->procedureFileService = $procedureFileService;
        $this->procedureMailService = $procedureMailService;
        $this->procedureMessageService = $procedureMessageService;
        $this->curlHelper = $curlHelper;
        $this->nextcloudHelper = $nextcloudHelper;
        $this->userGroupHelper = $userGroupHelper;
        $this->signatureTypeService = $signatureTypeService;
        $this->rootFolder = $rootFolder;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     *
     */
    private function handleException(\Exception $e): void {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new SignatureNotFound($e->getMessage());
        } else {
            throw $e;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function getConfigService(): ConfigService {
        return $this->configService;
    }

    /**
     *
     */
    public function find(int $signatureId): ?Signature {
        try {
            return $this->signatureMapper->find($signatureId);
        } catch (DoesNotExistException $e) {
            return null;
        } catch (EntityNotFoundException $e) {
            return null;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     *
     */
    public function findByHash(string $hash): ?Signature {
        try {
            return $this->signatureMapper->findByHash($hash);
        } catch (DoesNotExistException $e) {
            return null;
        } catch (EntityNotFoundException $e) {
            return null;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Get signature file info for a procedure, applying precedence by status.
     *
     * This method groups all signature-file associations by file_id and keeps
     * only the one with the most relevant signature status, according to
     * defined precedence.
     *
     * Precedence (from highest to lowest):
     *   SIGNED, WAITING_SIGNATURE, WAITING_MAIL, REQUEST_TO_START,
     *   INITIALIZING, EXPIRED, ERROR, CANCELLED
     *
     * @param Procedure|int $procedure
     *   The procedure entity or its ID.
     *
     * @return array
     *   Indexed by file_id => ['status' => int, 'status_date' => string].
     */
    public function findFileSignaturesByProcedure(Procedure|int $procedure): array {
        if (is_object($procedure)) {
            $procedureId = $procedure->getId();
        } else {
            $procedureId = (int) $procedure;
        }

        if (empty($procedureId)) {
            return [];
        }

        // Get all rows from mapper
        $rows = $this->signatureMapper->findFileSignaturesByProcedure($procedureId);
        if (empty($rows)) {
            return [];
        }

        // Define precedence order
        $precedence = [
            SignatureStatus::SIGNED,
            SignatureStatus::WAITING_SIGNATURE,
            SignatureStatus::WAITING_MAIL,
            SignatureStatus::REQUEST_TO_START,
            SignatureStatus::INITIALIZING,
            SignatureStatus::EXPIRED,
            SignatureStatus::ERROR,
            SignatureStatus::CANCELLED,
        ];

        $rank = array_flip($precedence);
        $filtered = [];

        foreach ($rows as $row) {
            $fid = (int) $row['file_id'];
            $status = (int) $row['status'];

            if (!isset($filtered[$fid])) {
                $filtered[$fid] = $row;
                continue;
            }

            // Keep the one with higher precedence (lower rank index)
            if ($rank[$status] < $rank[$filtered[$fid]['status']]) {
                $filtered[$fid] = $row;
            }
        }

        return $filtered;
    }

    /**
     * Find the signature requests for the given procedure.
     *
     * @param Procedure|int $procedure Procedure or procedure ID associated to the signatures to retrieve
     * @return Signature[]             Array of Signature objects (empty array if none)
     */
    public function findByProcedure(Procedure|int $procedure): array {
        if (empty($procedure)) {
            return [];
        }

        if (is_object($procedure)) {
            $procedure = $procedure->getId();
        }

        if (empty($procedure)) {
            return [];
        }

        try {
            return $this->signatureMapper->findByProcedureExcludeStatus($procedure, [SignatureStatus::ERROR]);
        } catch (DoesNotExistException $e) {
            return [];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     *
     */
    public function getInfoByHash(string $hash): array {
        $signature = $this->findByHash($hash);
        if (empty($signature)) {
            return [];
        }

        $signatureType = $this->signatureTypeService->find($signature->getSignatureTypeId())?->getInfo('entity');
        // PHPUnit Test: Impossible case due to function find return value is set to Signature entity, so will never be empty
        // @codeCoverageIgnoreStart
        if (empty($signatureType)) {
            return [];
        }
        // @codeCoverageIgnoreEnd
        $filesList = [];
        try {
            $files = $this->signatureFileMapper->findBySignature($signature->getId());
            foreach ($files as $file) {
                $filesList[] = $file->jsonSerialize();
            }
        } catch (\Exception $e) {
            return [];
        }

        $signatureInfo = $signature->jsonSerialize();
        $signatureInfo['files'] = $filesList;
        $signatureInfo['format'] = $signatureType->getFormat();
        $signatureInfo['caProvider'] = $signatureType->getCertifiedAuthority();

        $procedure = $this->procedureMapper->find($signature->getProcedureId());
        $procedureStakeholder = $this->procedureStakeholderMapper->findMainByProcedure($procedure);

        $signatureInfo['personalId'] = $procedureStakeholder->getPersonalId();
        $signatureInfo['name'] = $procedureStakeholder->getName();
        $signatureInfo['email'] = $procedureStakeholder->getEmail();
        $signatureInfo['phone'] = $procedureStakeholder->getPhone();
        $signatureInfo['topic'] = $procedure->getTopic();
        $signatureInfo['signatureReturn'] = $procedureStakeholder->getEntryPage() . '#/signature-finished';

        return $signatureInfo;
    }

    /**
     * Get procedure type id by signature.
     */
    private function getProcedureTypeIdBySignature(Signature $signature): ?int {
        try {
            $procedure = $this->procedureMapper->find($signature->getProcedureId());
            return $procedure->getProcedureTypeId();
        } catch (EntityNotFoundException $th) {
            return null;
        }
    }

    /**
     * Get the list of pending signatures for a given room.
     *
     * @param string $jitsiRoomCode Room code of the procedure for which pending files to sign must be retrieved.
     * @return array                List of pending files with signature data.
     */
    public function getPendingSignatureFilesByJitsiRoomCode(string $jitsiRoomCode): array {
        $procedures = $this->procedureMapper->findByJitsiRoomCode($jitsiRoomCode);

        $filesList = [];
        foreach ($procedures as $procedure) {
            $signatures = $this->signatureMapper->findByProcedure($procedure->getId());
            if (empty($signatures)) {
                continue;
            }

            foreach ($signatures as $signature) {
                $filesList = $this->addPendingSignature($signature, $filesList);
            }

            if (!empty($filesList)) {
                break;
            }
        }

        return $filesList;
    }

    /**
     * Add information of pending signature to given files list.
     *
     * @param Signature $signature
     * @param array     $filesList
     * @return array  The given file list updated.
     */
    private function addPendingSignature(Signature $signature, array $filesList): array {
        // Don't process signatures that are not waiting to be signed
        if ($signature->getStatus() != SignatureStatus::WAITING_SIGNATURE) {
            return $filesList;
        }

        $files = $this->signatureFileMapper->findBySignature($signature->getId());
        foreach ($files as $file) {
            $filesList[] = [
                'id'            => $file->getId(),
                'signatureUrl'  => $signature->getUrl(),
                'signatureHash' => $signature->getHash(),
                'sharedUrl'     => $file->getSharedUrl(),
            ];
        }

        return $filesList;
    }

    /**
     * Indicates whether the given procedure is valid for a new signature request.
     *
     * @param Procedure     $procedure     Procedure associated to the signatures to check
     * @param ProcedureType $procedureType Type of the procedure
     * @return bool|string                 true if valid; otherwise, translated error message
     */
    public function isProcedureValidForCreateSignature(Procedure $procedure, ProcedureType $procedureType): bool|string {
        // PHPUnit Test: Impossible case due to $procedure is stricted
        // @codeCoverageIgnoreStart
        if (empty($procedure)) {
            return $this->translate->t('No procedure provided!');
        }
        // @codeCoverageIgnoreEnd
        if (!$procedureType->getAllowSignature()) {
            return $this->translate->t('Signature system is disabled or you don\'t have permissions; please contact the system administrator');
        }

        if ($procedureType->getSignatureLimit() == 0) {
            return $this->translate->t('The procedure has reached the maximum signatures allowed');
        }

        $allowedForSignature = [ProcedureStatus::CREATED, ProcedureStatus::PENDANT, ProcedureStatus::ON_COURSE, ProcedureStatus::FINISHED];

        if (empty($procedure->getId())) {
            return $this->translate->t('The procedure must be saved before trying to attach a signature');
        }

        if (!in_array($procedure->getStatusId(), $allowedForSignature)) {
            return $this->translate->t('Current procedure status is not valid for a new signature');
        }

        $signatures = $this->findByProcedure($procedure->getId());

        if (!empty($signatures)
            && $procedureType->getSignatureLimit() > 0
            && count($signatures) >= $procedureType->getSignatureLimit()
        ) {
            return $this->translate->t('The procedure has reached the maximum signatures allowed');
        }

        return true;
    }

    /**
     * Get filtered signatures.
     *
     * @param array       $filter Filter parameters as array or JSON
     * @param string|null $userId Identifier of user executing the operation
     * @return ResponseInfo       {result, list[]}
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
                        $filter['minDate'] = BeHelper::getFromRelativeDate('-' . $this->configService->getFilterOverdueDays() . ' day', $timezone, 'today');
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

        $list = $this->signatureMapper->findByFilter($filter);
        $response->setInfo('list', $list);
        return $response;
    }

    /**
     * Get the list of procedures waiting to be attended.
     */
    public function findProceduresWaitingForSignature(): ResponseInfo {
        // The timeframe to sign depends on the procedure type configuration for each procedure,
        // so we need to check it one by one after finding a pending signature
        $signatures = $this->signatureMapper->findByStatus(SignatureStatus::WAITING_SIGNATURE);
        $waiting = [];
        foreach ($signatures as $signature) {
            $procedureTypeId = $this->getProcedureTypeIdBySignature($signature);
            if (!empty($procedureTypeId)) {
                $procedureType = $this->procedureTypeService->find($procedureTypeId)->getInfo('entity');
                $timeframeToSign = $procedureType->getSignatureTimeframeDownload(); // in hours, 0 is no limit
                if (!$signature->isTimeframeExpired($timeframeToSign)) {
                    $waiting[] = $signature;
                }
            }
        }

        $response = new ResponseInfo();
        $response->setInfo('list', $waiting);

        return $response;
    }

    /**
     * Indicates whether the given procedure has some signature waiting to be signed by
     * the user, within the time frame expected to be signed.
     *
     * @param Procedure  $procedure        Procedure associated to the signatures to check
     * @param ProcedureType $procedureType Procedure type which contains settings such as timeframes
     * @return bool                        true if there is any signature waiting to be signed
     *                                     and inside the expected interval; otherwise, false
     */
    public function isProcedureWaitingForSignature(Procedure $procedure, ProcedureType $procedureType): bool {
        if ($procedureType->getAllowSignature()) {
            $timeframeToSign = $procedureType->getSignatureTimeframeSign();
            $signatures = $this->findByProcedure($procedure);
            foreach ($signatures as $signature) {
                if ($signature->getStatus() == SignatureStatus::WAITING_SIGNATURE) {
                    if (!$signature->isTimeframeExpired($timeframeToSign)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Indicates whether the given procedure has some signature inside the downloadable timeframe.
     *
     * @param Procedure  $procedure         Procedure associated to the signatures to check
     * @param ProcedureType $procedureType  Procedure type which contains settings such as timeframes
     * @return bool                         true if there is any signature signed and inside
     *                                      the allowed interval for download; otherwise, false
     */
    public function procedureHasSignatureWaitingForDownload(Procedure $procedure, ProcedureType $procedureType): bool {
        if ($procedureType->getAllowSignature()) {
            $timeframeToDownload = $procedureType->getSignatureTimeframeDownload();
            $signatures = $this->findByProcedure($procedure);
            foreach ($signatures as $signature) {
                if ($signature->getStatus() == SignatureStatus::SIGNED) {
                    if (!$signature->isTimeframeExpired($timeframeToDownload)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Create a new signature entry for given procedure.
     *
     * @param Procedure            $procedure             Procedure associated to the signatures to check
     * @param ProcedureType        $procedureType         Type of procedure to sign
     * @param ProcedureStakeholder $procedureStakeholder  Stakeholder that has to sign
     * @param string               $userId                identifier of user executing the operation
     * @param string               $signatureMethods      Name of signature method allowed
     * @param mixed                $additionalInfo        Additional info (free structure)
     * @param SignatureType        $signatureType         Signature Type object associated with this signature
     * @param SignatureButton      $signatureButton       Signature Button object associated with this procedure and signature
     * @return Signature|string                           Signature object created; or translated error message
     */
    public function createSignatureForProcedure(
        Procedure $procedure,
        ProcedureType $procedureType,
        ProcedureStakeholder $procedureStakehoker,
        string $userId,
        string $signatureMethods,
        $additionalInfo,
        SignatureType $signatureType,
        SignatureButton $signatureButton
    ): Signature|string {

        // PHPUnit Test: Impossible case due to function find parameter value is set to Procedure entity, so will never be empty
        // @codeCoverageIgnoreStart
        if (empty($procedure)) {
            return $this->translate->t('No procedure provided!');
        }
        // @codeCoverageIgnoreEnd

        // Ensure signature process can be started
        $isProcedureValidForCreateSignature = $this->isProcedureValidForCreateSignature($procedure, $procedureType);
        if ($isProcedureValidForCreateSignature !== true) {
            return $isProcedureValidForCreateSignature;
        }

        $validationResult = $this->validateFilesForProcedureSignature($procedure, $procedureType, $userId, $signatureType);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $davpathUpload = $this->procedureFileService->getWebDAVToFolder($procedure, $procedureType->getSignatureUploadsFolder(), $userId);
        $davpathDownload = $this->procedureFileService->getWebDAVToFolder($procedure, $procedureType->getSignatureDownloadsFolder(), $userId);

        // Create the new signature process
        $signature = new Signature();
        $signature->setProcedureId($procedure->getId());
        $signature->setStatus(SignatureStatus::INITIALIZING);
        $signature->setHash(md5(uniqid(rand(), true)));
        $signature->setDavpathUpload($davpathUpload);
        $signature->setDavpathDownload($davpathDownload);
        $signature->setSignatureMethods($signatureMethods);
        $signature->setAdditionalInfo($additionalInfo);
        $signature->setSignatureTypeId($signatureType->getId());

        $signature = $this->signatureMapper->insert($signature);

        // Include all the files associated to the signature
        $files = $this->getFilesForSignatureInProcedure($procedure, $procedureType, $userId, $signatureType, true);
        $signatureFiles = [];
        foreach($files as $file) {
            $signatureFile = $this->addSignatureFile(
                $signature->getId(),
                $file,
                $davpathDownload,
                $davpathUpload,
                $procedureStakehoker->getEntryPassword($this->configService->getEncryptionKey())
            );
            $signatureFiles[] = $signatureFile->jsonSerialize();
        } 

        $signatureReport = $signature->jsonSerialize();
        $signatureReport['files'] = $signatureFiles;

        $this->activityManager->reportEntityActivity(
            $this->signatureMapper, 
            null, 
            ActivityType::CREATED, 
            $signatureReport, 
            $signature->getId()
        );

        // Request signature URL
        // If any error raises, mark signature as error
        if (!$this->requestExternalSignatureUrl($signature)) {
            $this->setSignatureError($signature);
            return $this->translate->t('Could not initialize the signature in the signing system');
        }

        // Prepare mail to user
        // If any error raises, mark signature as error
        if (!$this->preparePendantMail($procedure, $procedureType, $procedureStakehoker, $signature, $signatureButton)) {
            $this->setSignatureError($signature);
            return $this->translate->t('An error occurred while sending the message to the user.');
        }

        return $signature;
    }

    /**
     * Add a file to a signature process.
     *
     * @param int              $signatureId      Identifier of the signature group
     * @param \OCP\Files\File  $file             File object to sign
     * @param string           $davpathDownload  DAV path to obtain the original file to sign
     * @param string           $davpathUpload    DAV path to upload the signed file
     * @param string           $citizenPassword  Password for the shared link
     * @return SignatureFile                     New SignatureFile created
     */
    protected function addSignatureFile(
        int $signatureId,
        File $file,
        string $davpathDownload,
        string $davpathUpload,
        string $citizenPassword
    ): SignatureFile {

        try {
            // If already exists, return existing data
            return $this->signatureFileMapper->findBySignatureAndFile($signatureId, (int) $file->getId());
        } catch (\OCA\VTramit\Exception\EntityNotFoundException $e) {
            // Otherwise, continue to create it
        }

        $filepath = $file->getPath();
        $davpathUpload = $davpathUpload . '/' . $file->getName();
        $davpathDownload = $davpathDownload . '/' . $file->getName();
        $signatureFile = new SignatureFile();
        $signatureFile->setSignatureId($signatureId);
        $signatureFile->setFileId($file->getId());
        $signatureFile->setFilepath($filepath);
        $signatureFile->setDavpathUpload($davpathUpload);
        $signatureFile->setDavpathDownload($davpathDownload);
        $signatureFile->setSharedUrl($this->nextcloudHelper->shareFile($davpathUpload, $citizenPassword));

        $this->signatureFileMapper->insert($signatureFile);
        return $signatureFile;
    }

    /**
     * Generates a new share link protected by password for each file in any
     * signature related to given procedure stakeholder.
     *
     * @param ProcedureStakeholder $procedureStakehoker The procedure stakeholder
     */
    public function shareAllSignatureFilesInProcedureStakeholder(ProcedureStakeholder $procedureStakehoker): void {
        $signatures = $this->findByProcedure($procedureStakehoker->getProcedureId());
        $password = $procedureStakehoker->getEntryPassword($this->configService->getEncryptionKey());
        foreach ($signatures as $signature) {
            $this->shareAllSignatureFiles($signature, $password);
        }
    }

    /**
     * Generates a new share link protected by password for each file in given
     * signature.
     *
     * @param Signature $signature Signature object for which file's must be generated shared links
     * @param string    $password  The password to access to the the shared file
     */
    private function shareAllSignatureFiles(Signature $signature, string $password): void {
        $files = $this->signatureFileMapper->findBySignature($signature->getId());
        foreach ($files as $signatureFile) {
            if ($this->nextcloudHelper->fileExists($signatureFile->getFilepath())) {

                // The file to share is the davpath reference as it must be accessible by current user
                // so stored file path is not valid because it includes the owner user in the path
                $sharedUrl = $this->nextcloudHelper->shareFile($signatureFile->getDavpathUpload(), $password);
                $signatureFile->setSharedUrl($sharedUrl);
                $this->signatureFileMapper->update($signatureFile);
            }
        }
    }

    /**
     * Indicates if documents in procedure accomplish the conditions to be signed.
     * Those conditions are:
     *    - To have at least one file to sign
     *    - The file must have a valid extension from those defined in settings
     *    - The number of files is not greater than the limit defined in settings
     *
     * @return bool|string Returns true when validation succeeds or a translated error message otherwise.
     */
    public function validateFilesForProcedureSignature(
        Procedure $procedure,
        ProcedureType $procedureType,
        string $userId,
        SignatureType $signatureType
    ): bool|string {
        $file_to_sign = $this->getFilesForSignatureInProcedure($procedure, $procedureType, $userId, $signatureType, true);

        if (empty($file_to_sign)) {
            return $this->translate->t('Not enough files to start the signature process or files are already being processed');
        }

        $maxFiles = $signatureType->getFilesPerRequest();

        if ($maxFiles >= 0 && count($file_to_sign) > $maxFiles) {
            return $this->translate->t('Too many files selected for signature. Only %s file(s) are allowed per request.', [$maxFiles]);
        }

        return true;
    }

    /**
     * Obtain the list of files valid for signature in a procedure.
     *
     * @param Procedure     $procedure
     * @param ProcedureType $procedureType
     * @param string        $userId
     * @param SignatureType $signatureType
     * @param bool          $excludeProcessing Whether to exclude files already being processed
     *                                         in a non-final signature
     * @return \OCP\Files\File[]               List of files
     */
    protected function getFilesForSignatureInProcedure(
        Procedure $procedure,
        ProcedureType $procedureType,
        string $userId,
        SignatureType $signatureType,
        bool $excludeProcessing = false
    ): array {
        $origin = $procedureType->getSignatureUploadsFolder();
        $file_to_sign = [];

        if (empty($origin)) {
            return $file_to_sign;
        }

        $filepath = $this->procedureFileService->getLocalPathToFolder($procedure, $origin, $userId);
        if (!$this->rootFolder->nodeExists($filepath)) {
            return $file_to_sign;
        }

        // If exclusion is required, gather files already in non-final signatures
        $alreadyProcessingIds = [];
        if ($excludeProcessing) {
            $existing = $this->findByProcedure($procedure->getId());
            foreach ($existing as $signature) {
                $signatureFileList = $this->signatureFileMapper->findBySignature($signature->getId());
                foreach ($signatureFileList as $signatureFile) {
                    $alreadyProcessingIds[(int) $signatureFile->getFileId()] = true;
                }
            }
        }

        $node = $this->rootFolder->get($filepath);
        $items = $node->getDirectoryListing();

        foreach ($items as $item) {
            // Only process file-like nodes
            if (method_exists($item, "getExtension")) {
                $extList = explode(',', $signatureType->getFileType());
                $allowed = in_array('*', $extList) || in_array($item->getExtension(), $extList);

                if ($allowed) {
                    $fid = (int) $item->getId();
                    if (!$excludeProcessing || !isset($alreadyProcessingIds[$fid])) {
                        $file_to_sign[] = $item;
                    }
                }
            }
        }

        return $file_to_sign;
    }


    /**
     * Request the signature URL for given signature.
     *
     * @param Signature $signature Signature object for which request the URL for user signature
     * @return bool                true if URL has been properly requested and set; otherwise, false
     */
    protected function requestExternalSignatureUrl(Signature $signature): bool {
        // Call ca-proxy to inform about signature request
        $signatureType = $this->signatureTypeService->find($signature->getSignatureTypeId())?->getInfo('entity');
        // PHPUnit Test: Impossible case due to function find return value is set to SignatureType entity, so will never be empty
        // @codeCoverageIgnoreStart
        if (empty($signatureType)) {
            return false;
        }
        // @codeCoverageIgnoreEnd
        $url = $signatureType->getCertifiedAuthorityProxy();
        $call = $url . "sign/" . $signature->getHash();
        $info = $this->curlHelper->request($call);

        if (empty($info) || $info->Ok != true) {
            return false;
        }

        $url = $info->UserUrl;

        if (empty($url)) {
            return false;
        }

        return $this->setSignatureUrl($signature, $url);
    }

    /**
     * Set the url that must be used by the user to sign.
     *
     * @param Signature|int $signature Signature object or the ID of the signature object for which the URL must be set
     * @param string        $url       URL to set
     * @return bool                    true if URL has been properly set; otherwise, false
     */
    protected function setSignatureUrl(Signature|int $signature, string $url): bool {
        if (is_int($signature)) {
            $signature = $this->find($signature);
        }

        if (!empty($signature)) {
            $signature->setUrl($url);
            $signature->setStatus(SignatureStatus::WAITING_MAIL);
            $this->signatureMapper->update($signature);

            return true;
        }

        return false;
    }

    /**
     * Set the state of the signature to ERROR.
     *
     * @param Signature|int $signature Signature object or ID
     */
    protected function setSignatureError(Signature|int $signature): void {
        // PHPUnit Test: Impossible case due to function is never called passing int
        // @codeCoverageIgnoreStart
        if (is_int($signature)) {
            $signature = $this->find($signature);
        }
        // @codeCoverageIgnoreEnd
        if (!empty($signature)) {
            $signature->setStatus(SignatureStatus::ERROR);
            $this->signatureMapper->update($signature);
        }
    }

    /**
     * Prepare the mail for given signature and insert it into the mailing queue.
     *
     * @param Procedure            $procedure
     * @param ProcedureType        $procedureType
     * @param ProcedureStakeholder $procedureStakehoker
     * @param Signature            $signature
     * @param SignatureButton      $signatureButton
     * @return bool                true if mail is queued or mailing is disabled; otherwise, false
     */
    private function preparePendantMail(Procedure $procedure, ProcedureType $procedureType, ProcedureStakeholder $procedureStakehoker, Signature $signature, SignatureButton $signatureButton): bool {
        if (!$signatureButton->getMessageEnabled()) {
            $signature->setStatus(SignatureStatus::WAITING_SIGNATURE);
            $this->signatureMapper->update($signature);
            return true;
        }

        $procedureStakeholder = $this->procedureStakeholderMapper->findMainByProcedure($procedure);

        if (!empty($procedureStakeholder->getEmail())) {
            $response = $this->procedureMessageService->sendMessagesToStakeholder($procedureStakehoker, $procedure, $signatureButton);

            // DEPRECATED: Remove with EmailTemplates system
            if (!$response->isOk()) {
                $response = $this->procedureMailService->sendEmailToStakeholder($procedureStakeholder, $procedure, $signatureButton);
            }
            // END DEPRECATED

            $result = false;
            if ($response->getInfo('result') == 'OK') {
                $result = true;
                $signature->setStatus(SignatureStatus::WAITING_SIGNATURE);
                $this->signatureMapper->update($signature);
            }

            return $result;
        }

        return false;
    }

    /**
     * Cancels a specific file inside a signature process and marks the signature as cancelled when no files remain.
     *
     * @param int $signatureId
     * @param int $fileId
     * @return ResponseInfo
     */
    public function cancelFileSignature(int $signatureId, int $fileId): ResponseInfo {
        $response = new ResponseInfo();

        $signature = $this->signatureMapper->find($signatureId);
        if (!$signature) {
            throw new DataNotFoundException($this->translate->t('The requested signature was not found.'));
        }

        $signatureFile = $this->signatureFileMapper->findBySignatureAndFile($signatureId, $fileId);
        if (!$signatureFile) {
            throw new DataNotFoundException($this->translate->t('The requested signature file was not found.'));
        }

        if ($signatureFile->isDeleted()) {
            throw new DataNotFoundException($this->translate->t('The signature file is already cancelled.'));
        }

        $signatureFile->setDeleted(true);
        $this->signatureFileMapper->update($signatureFile);

		$remaining = $this->signatureFileMapper->findBySignature($signatureId, false);
        if (count($remaining) === 0) {
            $signature->setStatus(SignatureStatus::CANCELLED);
            $this->signatureMapper->update($signature);
        }

        $response->setInfo('result', 'OK');
        $response->setInfo('signatureFileId', $signatureFile->getId());
        $response->setInfo('signatureId', $signatureId);
        $response->setInfo('fileId', $fileId);
        $response->setInfo('remainingFiles', count($remaining));

        return $response;
    }

    /**
     * Notification indicating that signed files have been uploaded.
     *
     * @param string $hash   Hash associated to the completed signature
     * @param mixed  $files  List of files uploaded (format managed externally)
     * @return Signature|bool  The Signature object updated; otherwise, false
     */
    public function uploadCompletedByHash(string $hash, mixed $files): Signature|bool {
        // TODO: UTILIZAR $files
        $signature = $this->findByHash($hash);
        if (empty($signature)) {
            return false;
        }

        $allowedForCompletion = [SignatureStatus::WAITING_SIGNATURE];

        if (in_array($signature->getStatus(), $allowedForCompletion)) {
            $signature->setStatus(SignatureStatus::SIGNED);
            $signature = $this->signatureMapper->update($signature);
            return $signature;
        }

        return false;
    }

    /**
     * Cancel all the signatures related to a procedure.
     *
     * @param Procedure|int $procedure Procedure associated to the signatures to cancel
     */
    public function cancelSignaturesByProcedure(Procedure|int $procedure): void {
        $signatures = $this->findByProcedure($procedure);
        foreach($signatures as $signature) {
            $this->cancelSignature($signature);
        }
    }

    /**
     * Cancel a signature.
     *
     * @param Signature|int $signature Signature object or the ID of the signature object to cancel
     * @return Signature|null          Signature object updated
     */
    public function cancelSignature(Signature|int $signature): ?Signature {
        if (!empty($signature) && is_int($signature)) {
            $signature = $this->find($signature);
        }

        if (!empty($signature)) {
            $signature->setStatus(SignatureStatus::CANCELLED);
            $signature = $this->signatureMapper->update($signature);
        }

        return $signature;
    }

    /**
     * Cancel all the signatures that are out of time to be signed.
     */
    public function cancelAllOutOfTimeSignature(): void {
        $signatures = $this->signatureMapper->findByStatus(SignatureStatus::WAITING_SIGNATURE);
        foreach ($signatures as $signature) {
            $this->cancelOutOfTimeSignature($signature);
        }
    }

    /**
     * Cancel a signature because allowed time to sign is out.
     *
     * @param Signature|int $signature Signature object or the ID of the signature object to cancel
     * @return Signature|null          Signature object updated
     */
    public function cancelOutOfTimeSignature(Signature|int $signature): ?Signature {
        if (!empty($signature) && is_int($signature)) {
            $signature = $this->find($signature);
        }

        if (!empty($signature)) {
            $signature->setStatus(SignatureStatus::EXPIRED);
            $signature = $this->signatureMapper->update($signature);
        }

        return $signature;
    }
}
