<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Procedure;

use OCP\IURLGenerator;
use OCP\Files\IRootFolder;

use OCA\VTramit\Exception\DataNotFoundException;

use OCA\VTramit\ProcedureStatus;

use OCA\VTramit\Db\MailQueueMapper;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\MessageQueueMapper;
use OCA\VTramit\Db\MessageTemplateMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\RgpdActivityMapper;
use OCA\VTramit\Db\SignatureButtonMapper;
use OCA\VTramit\Db\SignatureTypeMapper;
use OCA\VTramit\Db\SignatureFileMapper;
use OCA\VTramit\Db\SignatureMapper;
use OCA\VTramit\Db\TopicMapper;
use OCA\VTramit\Db\WebhookMapper;

use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureStakeholderController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureTypeGroupController;

use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\MailTemplateService;
use OCA\VTramit\Service\MessageQueueService;
use OCA\VTramit\Service\MessageTemplateService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\ProcedureTokenService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureVideoconferenceService;
use OCA\VTramit\Service\RgpdActivityService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\TopicService;
use OCA\VTramit\Service\WebhookService;

use OCA\VTramit\Helper\CurlHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Helper\JwtHelper;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;


/**
 * Tests for Procedure CRUD operations (PU018)
 */
class ProcedureCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";

    private string $noProcedureExpectedMessage = "You must select a procedure type to continue";

    private string $noAssignedGroupExpectedMessage = "Please select a department";

    private string $invalidExternalIdErrorMessage = "The external procedure identifier can only contain letters, numbers, hyphens, and underscores";

    private string $noAppointmentDateExpectedMessage = "Please select a date";

    private string $noDeadlineDateExpectedMessage = "Please select a deadline date";

    private string $appointmentTooOldError = "The appointment date is too old";

    private string $noJitsiUrlError = "There is no base Jitsi URL defined to create the Jitsi room URL for the staff";

    private string $jitsiRoomCodeNoExternalIdError = "To create the Jitsi room code you have to declare first the external ID";

    /** @var MailQueueMapper */
    private MailQueueMapper $mailQueueMapper;

    /** @var MailQueueService */
    private MailQueueService $mailQueueService;

    /** @var MailTemplateMapper */
    private MailTemplateMapper $mailTemplateMapper;

    /** @var MailTemplateService */
    private MailTemplateService $mailTemplateService;

    /** @var MessageQueueMapper */
    private MessageQueueMapper $messageQueueMapper;

    /** @var MessageQueueService */
    private MessageQueueService $messageQueueService;

    /** @var MessageTemplateMapper */
    private MessageTemplateMapper $messageTemplateMapper;

    /** @var MessageTemplateService */
    private MessageTemplateService $messageTemplateService;

    /** @var ProcedureController */
    private ProcedureController $procedureController;

    /** @var ProcedureFileService */
    private ProcedureFileService $procedureFileService;

    /** @var ProcedureMailService */
    private ProcedureMailService $procedureMailService;

    /** @var ProcedureMapper */
    private ProcedureMapper $procedureMapper;

    /** @var ProcedureMessageService */
    private ProcedureMessageService $procedureMessageService;

    /** @var ProcedureService */
    private ProcedureService $procedureService;

    /** @var ProcedureStakeholderController */
    private ProcedureStakeholderController $procedureStakeholderController;

    /** @var ProcedureStakeholderMapper */
    private ProcedureStakeholderMapper $procedureStakeholderMapper;

    /** @var ProcedureStakeholderService */
    private ProcedureStakeholderService $procedureStakeholderService;

    /** @var ProcedureTokenService */
    private ProcedureTokenService $procedureTokenService;

    /** @var ProcedureTypeController */
    private ProcedureTypeController $procedureTypeController;

    /** @var ProcedureTypeGroupController */
    private ProcedureTypeGroupController $procedureTypeGroupController;
    
    /** @var ProcedureTypeGroupMapper */
    private ProcedureTypeGroupMapper $procedureTypeGroupMapper;

    /** @var ProcedureTypeProtocolStepMapper */
    private ProcedureTypeProtocolStepMapper $procedureTypeProtocolStepMapper;

    /** @var ProcedureTypeGroupService */
    private ProcedureTypeGroupService $procedureTypeGroupService;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /** @var ProcedureTypeService */
    private ProcedureTypeService $procedureTypeService;

    /** @var ProcedureVideoconferenceService */
    private ProcedureVideoconferenceService $procedureVideoconferenceService;

    /** @var RgpdActivityMapper */
    private RgpdActivityMapper $rgpdActivityMapper;

    /** @var RgpdActivityService */
    private RgpdActivityService $rgpdActivityService;

    /** @var SignatureButtonMapper */
    private SignatureButtonMapper $signatureButtonMapper;

    /** @var SignatureButtonService */
    private SignatureButtonService $signatureButtonService;

    /** @var SignatureFileMapper */
    private SignatureFileMapper $signatureFileMapper;

    /** @var SignatureMapper */
    private SignatureMapper $signatureMapper;

    /** @var SignatureService */
    private SignatureService $signatureService;

    /** @var SignatureTypeMapper */
    private SignatureTypeMapper $signatureTypeMapper;

    /** @var SignatureTypeService */
    private SignatureTypeService $signatureTypeService;

    /** @var TopicMapper */
    private TopicMapper $topicMapper;

    /** @var TopicService */
    private TopicService $topicService;

    /** @var WebhookMapper */
    private WebhookMapper $webhookMapper;

    /** @var WebhookService */
    private WebhookService $webhookService;

    /**
     * Constructor for the Procedure test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'procedure-crud';
        $this->setEntityTypeName('procedure');
        parent::__construct($testName, $this->testFunction);

        // Inizializate mappers
        $this->signatureButtonMapper = new SignatureButtonMapper($this->dbConnection);
        $this->procedureMapper = new ProcedureMapper($this->dbConnection);
        $this->procedureStakeholderMapper = new ProcedureStakeholderMapper($this->dbConnection);
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->mailTemplateMapper = new MailTemplateMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = new ProcedureTypeGroupMapper($this->dbConnection);
        $this->procedureTypeProtocolStepMapper = new ProcedureTypeProtocolStepMapper($this->dbConnection);
        $this->topicMapper = new TopicMapper($this->dbConnection);
        $this->mailQueueMapper = new MailQueueMapper($this->dbConnection);
        $this->messageTemplateMapper = new MessageTemplateMapper($this->dbConnection);
        $this->messageQueueMapper = new MessageQueueMapper($this->dbConnection);
        $this->signatureTypeMapper = new SignatureTypeMapper($this->dbConnection);
        $this->signatureFileMapper = new SignatureFileMapper($this->dbConnection);
        $this->signatureMapper = new SignatureMapper($this->dbConnection);
        $this->webhookMapper = new WebhookMapper($this->dbConnection);
        $this->curlHelper = $this->createMock(CurlHelper::class);

        // Mock classes
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);

        $this->nextCloudHelper = $this->createMock(NextcloudHelper::class);

        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->userGroupHelper->method('getAllowedGroupsForUser')->willReturn($this->allowedGroups);
        $this->userGroupHelper->method('getAssignableUsersByGroup')->willReturnCallback(function ($group, $userId, $allowAssignUser) {
            if ($group === "IMH") {
                return ["imh-01" => "IMH 01",
                        "imh-02" => "IMH 02",
                        "oac-imh-01" => "Supervisor OAC/IMH 01"];
            }
            else if ($group === "OAC") {
                return ["oac-01" => "OAC 01",
                        "oac-02" => "OAC 02",
                        "oac-imh-01" => "Supervisor OAC/IMH 01"];
            }
            else {
                return [];
            };
        });
        $this->deckHelper = $this->createMock(DeckHelper::class);

        $this->topicService = new TopicService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->topicMapper
        );

        $this->procedureTokenService = new ProcedureTokenService(
            $this->configService,
            $this->topicService
        );

        $this->procedureTypeGroupService = new ProcedureTypeGroupService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->procedureTypeGroupMapper,
            $this->procedureTypeProtocolStepMapper
        );

        $this->procedureTypeGroupController = new ProcedureTypeGroupController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeGroupService,
            $this->userId
        );

        $this->procedureVideoconferenceService = new ProcedureVideoconferenceService(
            $this->procedureTypeGroupService,
            $this->procedureTokenService
        );

        $this->mailTemplateService = new MailTemplateService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->mailTemplateMapper,
            $this->procedureTokenService
        );

        $this->messageQueueService = new MessageQueueService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->messageQueueMapper,
            $this->configService,
        );

        $this->messageTemplateService = new MessageTemplateService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->messageTemplateMapper,
            $this->procedureTokenService
        );

        $this->mailQueueService = new MailQueueService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->mailQueueMapper,
            $this->configService
        );

        $this->signatureTypeService = new SignatureTypeService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->signatureTypeMapper
        );

        // Create signature button service
        $this->signatureButtonService = new SignatureButtonService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->signatureButtonMapper
        );

        $this->webhookService = new WebhookService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->webhookMapper
        );

        $this->procedureTypeService = new ProcedureTypeService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->procedureTypeMapper,
            $this->procedureTypeGroupMapper,
            $this->userGroupHelper,
            $this->configService
        );

        $this->procedureTypeController = new ProcedureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeService,
            $this->userId
        );

        $this->procedureFileService = new ProcedureFileService(
            $this->config,
            $this->translate,
            $this->urlGenerator,
            $this->nextCloudHelper,
            $this->procedureMapper,
            $this->procedureStakeholderMapper,
            $this->procedureTypeService,
            $this->configService,
            $this->procedureVideoconferenceService
        );

        $this->procedureMailService = new ProcedureMailService(
            $this->translate,
            $this->mailTemplateService,
            $this->procedureTypeService,
            $this->procedureTypeGroupService,
            $this->procedureStakeholderMapper,
            $this->userGroupHelper,
            $this->configService,
            $this->mailQueueService,
            $this->procedureTokenService
        );
        
        $this->procedureMessageService = new ProcedureMessageService(
            $this->translate,
            $this->messageTemplateService,
            $this->procedureTypeService,
            $this->procedureTypeGroupService,
            $this->procedureStakeholderMapper,
            $this->userGroupHelper,
            $this->configService,
            $this->messageQueueService,
            $this->procedureTokenService
        );

        $this->signatureService = new SignatureService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->signatureMapper,
            $this->signatureFileMapper,
            $this->configService,
            $this->mailQueueService,
            $this->mailTemplateService,
            $this->procedureMapper,
            $this->procedureTypeGroupMapper,
            $this->procedureTypeService,
            $this->procedureStakeholderMapper,
            $this->procedureFileService,
            $this->procedureMailService,
            $this->procedureMessageService,
            $this->curlHelper,
            $this->nextCloudHelper,
            $this->userGroupHelper,
            $this->signatureTypeService,
            $this->rootFolder,
            $this->urlGenerator
        );

    }

    /**
     * 
     * Steps:
     *
     *  1.- Create procedure correctly (CREATE)
     *  2.- Find procedure correctly using ID (FIND)
     *  3.- Find procedure in procedure existing entities list (FIND ALL)
     *  4.- Update procedure correctly (UPDATE)
     *  5.- Delete procedure correctly (DELTE)
     *  6.- Create procedure without procedure type id
     *  7.- Create procedure with no externalId
     *  8.- Create procedure with invalid data
     *  9.- Create procedure with appointment data older than today
     *  10.- Verify that if the createFolders method of the TokenService procedure throws a DataNotFoundException, it handles it correctly
     *  11.- Create procedure without setting jitsiStaffUrl in its procedure type 
     *  12.- Create procedure setting jitsiStaffUrl in its procedure type 
     *  13.- Create procedure setting id previously
     */
    public function testCRUD(): void {

        $this->createTestRequiredData();

        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];
        $procedureTypeGroupId = $this->createdEntities[get_class($this->procedureTypeGroupController)][0];

        $initalData = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
        $updateData = $this->setAssociatedInfoToProcedure(TestData::$updatedProcedureTestEntityData, $procedureTypeId);// Crear procedure correctamente (CREATE)

        // Create procedure correctly (CREATE)
        // Find procedure correctly using ID (FIND)
        // Find procedure in procedure existing entities list (FIND ALL)
        // Update procedure correctly (UPDATE)
        // Delete procedure correctly (DELETE)
        $this->runCRUDTest($initalData, $updateData);

        $noExternalIdInitialData = $initalData;
        unset($noExternalIdInitialData["externalId"]);
        $noExternalIdInitialData["externalId"] = "";
        // Create procedure with no externalId required
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] = $procedureTypeId;
        $procedureTypeTestEntity["requireExternalId"] = true;
        $procedureTypeTestEntity["allowVideo"] = true;
        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);
        
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["id"] = $procedureTypeGroupId;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $procedureTypeId;
        $procedureTypeGroupTestEntity["bypassRequired"] = true;
        $procedureTypeGroupTestEntity["allowAssignUser"] = true;
        $procedureTypeGroupTestEntity["allowCreate"] = true;

        $responseProcedureTypeGroup = $this->update($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);
        // Create procedure with no externalId required
        $responseProcedure = $this->create($this->controller, $noExternalIdInitialData);
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["id"] = $procedureTypeGroupId;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $procedureTypeId;
        $procedureTypeGroupTestEntity["bypassRequired"] = false;

        $responseProcedureTypeGroup = $this->update($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);

        $responseProcedure = $this->create($this->controller, $noExternalIdInitialData);

        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["requireExternalId"] = false;
        $procedureTypeTestEntity["requireAssignedGroup"] = true;
        $procedureTypeTestEntity["requireAppointmentDate"] = true;
        $procedureTypeTestEntity["requireDeadlineDate"] = true;
        $procedureTypeTestEntity["requireAttendanceChannel"] = true;

        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["id"] = $procedureTypeGroupId;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $procedureTypeId;
        $procedureTypeGroupTestEntity["bypassRequired"] = false;
        $procedureTypeGroupTestEntity["allowAssignUser"] = true;

        $responseProcedureTypeGroup = $this->update($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);

        // Create procedure without procedure type id
        $initalData["procedureTypeId"] = "";
        $responseProcedure = $this->create($this->controller, $initalData);
        $this->logResponseData("ERROR NO PROCEDURE TYPE ID",  $responseProcedure);
        $this->assertEquals("ERROR", $responseProcedure["result"]);
        $this->assertEquals($this->noProcedureExpectedMessage, $responseProcedure["message"][0]);

        // Create procedure with invalid data
        $initalData["procedureTypeId"] = $procedureTypeId;
        $initalData["assignedGroupId"] ="";
        $initalData["appointmentDate"] ="";
        $initalData["attendanceChannel"] ="";
        $initalData["statusId"] = ProcedureStatus::FINISHED;
        $initalData["deadlineDate"] ="";
        $initalData["externalId"] ="$$$!$0=$%";
        $responseProcedure = $this->create($this->controller, $initalData);
        $this->logResponseData("ERROR INVALID VALUES",  $responseProcedure);
        $this->assertEquals("ERROR", $responseProcedure["result"]);
        $this->assertContains($this->noAssignedGroupExpectedMessage, $responseProcedure["message"]);
        $this->assertContains($this->noAppointmentDateExpectedMessage, $responseProcedure["message"]);
        $this->assertContains($this->invalidExternalIdErrorMessage, $responseProcedure["message"]);
        $this->assertContains($this->noDeadlineDateExpectedMessage, $responseProcedure["message"]);

        // Create procedure with appointment data older than today
        $now = new \DateTime('today',new \DateTimeZone('Europe/Berlin'));
        $lastWeek = (clone $now)->modify('-7 days');
        $initalData["appointmentDate"] =  $lastWeek->format('Y-m-d H:i:s');
         $responseProcedure = $this->create($this->controller, $initalData);
        $this->logResponseData("ERROR APPOINTMENT TOO OLD",  $responseProcedure);
        $this->assertContains($this->appointmentTooOldError, $responseProcedure["message"]);

        // Verify that if the createFolders method of the TokenService procedure throws a DataNotFoundException, it handles it correctly
        $procedureData = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
        $responseProcedure = $this->create($this->controller, $procedureData);
        $this->entity = $this->getEntity($responseProcedure);

        $this->procedureFileService = $this->createMock(ProcedureFileService::class);
        $this->procedureFileService->method('createFolders')->will($this->throwException(new DataNotFoundException()));

        $this->setUpController();

        $this->entity["assignedGroupId"] = "TOST";
        $this->entity["statusId"] = ProcedureStatus::CREATED;
        $responseProcedure = $this->update($this->controller, $this->entity);
        $this->logResponseData("EXCEPTION CREATEFOLDERS",  $responseProcedure);
        $this->assertEquals("ERROR", $responseProcedure["result"]);
        $this->assertContains("Folders could not be created", $responseProcedure["message"]);

        // Create procedure without setting jitsiStaffUrl in its procedure type 
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["notifyStakeholder"] = true;
        $procedureTypeTestEntity["allowUpload"] = true;
        $procedureTypeTestEntity["allowDownload"] = true;
        $procedureTypeTestEntity["allowVideo"] = true;
        $procedureTypeTestEntity["jitsiStaffUrl"] = "";

        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        $newData = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
        $responseProcedure = $this->create($this->controller, $newData);
        $this->logResponseData("EXCEPTION NO JITSI URL",  $responseProcedure);
        $this->assertEquals("EXCEPTION", $responseProcedure["result"]);
        // $this->assertEquals($this->noJitsiUrlError, $responseProcedure["message"]);
        $this->assertEquals($this->noJitsiUrlError, $responseProcedure["message"]);
        $jitsiStaffJwtPayload = '{
            "aud": "my_app_id",
            "iss": "my_app_id",
            "sub": "meet.mydomain.com",
            "room": "myMeetingRoom",
            "exp": 1736480400,
            "context": {
                "user": {
                "name": "Jane Doe",
                "email": "jane@example.com",
                "avatar": "https://example.com/avatar.jpg"
                }
            }
        }';

        // Create procedure setting jitsiStaffUrl in its procedure type 
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["notifyStakeholder"] = true;
        $procedureTypeTestEntity["allowUpload"] = true;
        $procedureTypeTestEntity["allowDownload"] = true;
        $procedureTypeTestEntity["allowVideo"] = true;
        $procedureTypeTestEntity["jitsiStaffJwtPayload"] = $jitsiStaffJwtPayload;
        $procedureTypeTestEntity["jitsiStaffJwtSecret"] ='my_super_secure_secret_key_12345!';

        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        $newData = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
        $responseProcedure = $this->create($this->controller, $newData);
        $this->logResponseData("JITSI URL",  $responseProcedure);

        $responseProcedure = $this->find($this->controller, $responseProcedure["entity"]->id);
        $this->logResponseData("COMPUTED FIELDS",  $responseProcedure);

        // Create procedure setting id previously
        $newData["id"] = "TOST";
        $responseProcedure = $this->create($this->controller, $newData);
        $this->logResponseData("ID ALREADY SET",  $responseProcedure);

    }

    protected function setUpController(): void {

        $this->procedureService = new ProcedureService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->rootFolder,
            $this->urlGenerator,
            $this->mailTemplateMapper,
            $this->procedureMapper,
            $this->procedureStakeholderMapper,
            $this->procedureTypeMapper,
            $this->procedureTypeGroupMapper,
            $this->nextCloudHelper,
            $this->userGroupHelper,
            $this->deckHelper,
            $this->configService,
            $this->mailQueueService,
            $this->signatureService,
            $this->signatureTypeService,
            $this->signatureButtonService,
            $this->procedureFileService,
            $this->procedureVideoconferenceService,
            $this->procedureMailService,
            $this->procedureMessageService,
            $this->procedureTokenService,
            $this->procedureTypeService,
            $this->procedureTypeGroupService,
            $this->webhookService
        );

        $this->procedureStakeholderService = new ProcedureStakeholderService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->rootFolder,
            $this->urlGenerator,
            $this->mailTemplateMapper,
            $this->procedureMapper,
            $this->procedureStakeholderMapper,
            $this->procedureTypeMapper,
            $this->procedureTypeGroupMapper,
            $this->nextCloudHelper,
            $this->userGroupHelper,
            $this->deckHelper,
            $this->configService,
            $this->procedureService,
            $this->mailQueueService,
            $this->signatureService,
            $this->procedureFileService,
            $this->procedureMailService,
            $this->procedureMessageService,
            $this->procedureTokenService,
            $this->procedureTypeService,
            $this->webhookService
        );

        $this->procedureStakeholderController = new ProcedureStakeholderController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureStakeholderService,
            $this->userId
        );


        $this->controller = new ProcedureController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureService,
            $this->procedureStakeholderService,
            $this->userId
        );
    }

    /**
     * Checks response data against expected values
     *
     * @param array $expectedData Expected data
     * @param array $actualData Actual data from response
     */
    protected function checkEntityData(array $expectedData, array $actualData): void {
        // Check all received information
        $this->assertEquals(
            $expectedData['externalId'],
            $actualData['externalId'],
            'External Id mismatch'
        );

        $this->assertEquals(
            $expectedData['procedureTypeId'],
            $actualData['procedureTypeId'],
            'Procedure Type Id mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['assignedGroupId']),
            json_encode($actualData['assignedGroupId']),
            'Assigned Group Id mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['comments']),
            json_encode($actualData['comments']),
            'Comments mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['topic']),
            json_encode($actualData['topic']),
            'Topic mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['attendanceChannel']),
            json_encode($actualData['attendanceChannel']),
            'Attendance Channel mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['appointmentDate']),
            json_encode($actualData['appointmentDate']),
            'Appointment Date mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["requireAssignedGroup"] = true;
        $procedureTypeTestEntity["requireAppointmentDate"] = true;
        $procedureTypeTestEntity["requireDeadlineDate"] = true;
        $procedureTypeTestEntity["requireAttendanceChannel"] = true;

        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);
        // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $responseProcedureType["entity"]->id;
        $procedureTypeGroupTestEntity["allowAssignUser"] = true;
        $procedureTypeGroupTestEntity["bypassRequired"] = true;

        $responseProcedureTypeGroup = $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);
    }

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     */
    protected function getControllerInstance($controllerClass = ''): EntityController {

        switch ($controllerClass) {
            case 'OCA\VTramit\Controller\ProcedureTypeController':
                return $this->procedureTypeController;
            case 'OCA\VTramit\Controller\ProcedureTypeGroupController':
                return $this->procedureTypeGroupController;
            default:
                return $this->controller;
        }
    }
}
