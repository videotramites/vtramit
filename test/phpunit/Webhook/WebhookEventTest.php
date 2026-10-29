<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Webhook;

use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCA\VTramit\Db\WebhookMapper;
use OCA\VTramit\Controller\WebhookController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\WebhookService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Service\TopicService;
use OCA\VTramit\Test\Phpunit\Helpers\TestHelpers;
use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;

use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\Webhook;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\Procedure;

use OCA\VTramit\Controller\ProcedureTypeGroupController;
use OCA\VTramit\Controller\RgpdActivityController;
use OCA\VTramit\Controller\ProcedureStakeholderController;
use OCA\VTramit\Service\ActivityManager;
use OCA\VTramit\Service\RgpdActivityService;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Service\ProcedureTools;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureVideoconferenceService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureTokenService;
/**
 * PU069
 */
class WebhookEventTest extends CommonTestOperations
{

    private string $testFunction = "testEventTest";


    /** @var ProcedureTypeController */
    private ProcedureTypeController $procedureTypeController;

    /** @var ProcedureController */
    private ProcedureController $procedureController;

    /** @var ProcedureService */
    private ProcedureService $procedureService;

    /** @var ProcedureTypeService */
    private ProcedureTypeService $procedureTypeService;

    /** @var WebhookService Service for webhook operations */
    private WebhookService $webhookService;

    /** @var WebhookMapper Mapper for webhook database operations */
    private WebhookMapper $webhookMapper;

        /** @var ProcedureMapper */
    private ProcedureMapper $procedureMapper;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /** @var ProcedureTypeGroupMapper */
    private ProcedureTypeGroupMapper $procedureTypeGroupMapper;
    /** @var ProcedureVideoconferenceService */
    private ProcedureVideoconferenceService $procedureVideoconferenceService;

    /** @var ProcedureMailService */
    private ProcedureMailService $procedureMailService;

    /** @var ProcedureMessageService */
    private ProcedureMessageService $procedureMessageService;

    /** @var ProcedureTokenService */
    private ProcedureTokenService $procedureTokenService;

    /** @var ProcedureFileService */
    private ProcedureFileService $procedureFileService;

    /**
     * Constructor for the Webhook test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'webhook-event';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('webhook');

        $this->topicService = $this->createMock(TopicService::class);

        $this->procedureTokenService = $this->createMock(ProcedureTokenService::class);

        $this->procedureFileService = $this->createMock(ProcedureFileService::class);
        $this->procedureFileService->method('getWebDAVToFolder')->willReturn("test/");

        $this->procedureMailService = $this->createMock(ProcedureMailService::class);
        $this->procedureMailService->method('sendEmailToStakeholder')->willReturn(new ResponseInfo());
        $this->procedureMailService->method('sendEmailToStaff')->willReturn(new ResponseInfo());
        $this->procedureMailService->method('sendEmailToAllStakeholders')->willReturn(new ResponseInfo());

        $this->procedureMessageService = $this->createMock(ProcedureMessageService::class);
        $this->procedureMessageService->method('sendMessagesToStakeholder')->willReturn(new ResponseInfo());
        $this->procedureMessageService->method('sendMessagesToStaff')->willReturn(new ResponseInfo());
        $this->procedureMessageService->method('sendMessagesToAllStakeholders')->willReturn(new ResponseInfo());

        $this->procedureVideoconferenceService = $this->createMock(ProcedureVideoconferenceService::class);
        $this->procedureVideoconferenceService->method('getVideoconferenceConfirmationForm')->willReturn("");

        // Inizializate mappers
        $this->procedureMapper = new ProcedureMapper($this->dbConnection);
        $this->procedureStakeholderMapper = new ProcedureStakeholderMapper($this->dbConnection);
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = new ProcedureTypeGroupMapper($this->dbConnection);
        $this->mailTemplateMapper = $this->createMock(MailTemplateMapper::class);
        // Initialize mapper
        $this->webhookMapper = new WebhookMapper($this->dbConnection);

        // Mock classes
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);

        $this->nextCloudHelper = $this->createMock(NextcloudHelper::class);
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        $this->userGroupHelper->method('getAllowedGroupsForUser')->willReturn($this->allowedGroups);
        $this->userGroupHelper->method('getAssignableUsersByGroup')->willReturn([]);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->deckHelper = $this->createMock(DeckHelper::class);

        $this->mailQueueService = $this->createMock(MailQueueService::class);
        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureTypeService = $this->createMock(SignatureTypeService::class);
        $this->signatureButtonService = $this->createMock(SignatureButtonService::class);

        $this->procedureTypeGroupService = $this->createMock(ProcedureTypeGroupService::class);

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
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {


        // Create webhook service
        $this->webhookService = new WebhookService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->webhookMapper
        );

        // Create webhook controller
        $this->controller = new WebhookController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->webhookService,
            $this->userId
        );


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

        $this->procedureController = new ProcedureController(
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
     * 
     * Steps:
     *
     *  1.- Create a procedure
     *  2.- Update a procedure
     *  3.- Find topic in topic existing entities list (FIND ALL)
     *  4.- Update topic correctly (UPDATE)
     *  5.- Delete topic correctly (DELTE)
     * 
     */
    public function testEventTest(): void {
        $this->createTestRequiredData();

        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];

        // Create a procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
        $responseProcedure = $this->create($this->procedureController, $procedureTestEntity);
        $this->logResponseData("CREATE", $responseProcedure);
        $this->entity = $this->getEntity($responseProcedure);

        // Update a procedure
        $responseProcedure = $this->update($this->procedureController,$this->entity);
        $this->logResponseData("UPDATE", $responseProcedure);
        $this->entity = $this->getEntity($responseProcedure);

        // Sending a webhook without valid data
        $this->webhookService->event("", "", New Procedure());
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
            $expectedData['name'],
            $actualData['name'],
            'Name mismatch'
        );

        $this->assertEquals(
            $expectedData['endpoint'],
            $actualData['endpoint'],
            'Endpoint mismatch'
        );

        $this->assertEquals(
            $expectedData['authType'],
            $actualData['authType'],
            'Auth Type mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['authTypeData']),
            json_encode($actualData['authTypeData']),
            'Auth Type Data mismatch'
        );

        $this->assertEquals(
            $expectedData['event'],
            $actualData['event'],
            'Event mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        $webhookData = TestData::$webhookTestEntityData;
        $webhookData["event"] = [Webhook::CREATE_PROCEDURE, Webhook::UPDATE_PROCEDURE, Webhook::DELETE_PROCEDURE];
        $webhookData["authTypeData"] = ["user" => "TEST", "password"=> "TEST"];
        $webhookData["authType"] = Webhook::BASIC_AUTH;

        $this->create($this->controller, $webhookData);

        $webhookData = TestData::$webhookTestEntityData;
        $webhookData["event"] = [Webhook::CREATE_PROCEDURE, Webhook::UPDATE_PROCEDURE, Webhook::DELETE_PROCEDURE];
        $webhookData["authTypeData"] = ["token" => "2424424"];
        $webhookData["authType"] = Webhook::TOKEN;

        $this->create($this->controller, $webhookData);

        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);
        $this->logResponseData("CREATE", $responseProcedureType);
        $this->entity = $this->getEntity($responseProcedureType);

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
            case 'OCA\VTramit\Controller\ProcedureController':
                return $this->procedureController;
            default:
                return $this->controller;
        }
    }
}