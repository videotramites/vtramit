<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\RgpdOption;

use PHPUnit\Framework\TestCase;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\RgpdOptionMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\RgpdOptionController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Service\RgpdOptionService;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\ProcedureTools;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Service\WebhookService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;
use OCA\VTramit\Test\Phpunit\TestHelpers;
use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\Service\ProcedureTokenService;
use OCA\VTramit\Service\TopicService;

/**
 * Tests for Rgpd option CRUD operations
 */
class RgpdOptionFindByJitsiRoomCodeTest extends CommonTestOperations
{

    private string $testFunction = "testFindByJitsiRoomCode";

    private int $procedureId = 0;

    /** @var RgpdOptionService */
    private RgpdOptionService $rgpdOptionService;

    /** @var RgpdOptionMapper */
    private RgpdOptionMapper $rgpdOptionMapper;

    /** @var ProcedureMapper */
    private ProcedureMapper $procedureMapper;

    /** @var ProcedureStakeholderMapper */
    private ProcedureStakeholderMapper $procedureStakeholderMapper;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /** @var ProcedureController */
    private ProcedureController $procedureController;

    /** @var ProcedureTypeController */
    private ProcedureTypeController $procedureTypeController;

    /** @var ProcedureService */
    private ProcedureService $procedureService;

    /** @var ProcedureTypeService */
    private ProcedureTypeService $procedureTypeService;

    /** @var ProcedureStakeholderService */
    private ProcedureStakeholderService $procedureStakeholderService;

    /** @var MailQueueService */
    private MailQueueService $mailQueueService;

    /** @var SignatureService */
    private SignatureService $signatureService;

    /** @var SignatureTypeService */
    private SignatureTypeService $signatureTypeService;

    /** @var SignatureButtonService */
    private SignatureButtonService $signatureButtonService;

    /** @var ProcedureTokenService */
    private ProcedureTokenService $procedureTokenService;

    /** @var ProcedureTypeGroupService */
    private ProcedureTypeGroupService $procedureTypeGroupService;

    /** @var WebhookService */
    private WebhookService $webhookService;

    /** @var MailTemplateMapper */
    private MailTemplateMapper $mailTemplateMapper;

    /** @var ProcedureTypeGroupMapper */
    private ProcedureTypeGroupMapper $procedureTypeGroupMapper;

    /** @var NextcloudHelper */
    private NextcloudHelper $nextCloudHelper;

    /** @var UserGroupHelper */
    private UserGroupHelper $userGroupHelper;

    /** @var DeckHelper */
    private DeckHelper $deckHelper;

    /** @var IRootFolder */
    private IRootFolder $rootFolder;

    /** @var IURLGenerator */
    private IURLGenerator $urlGenerator;

    /** @var TopicService */
    private TopicService $topicService;

    /**
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'rgpd-options-find-by-jitsi-room-code';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('rgpd option');

        // Inizializate mappers
        $this->procedureMapper = new ProcedureMapper($this->dbConnection);
        $this->procedureStakeholderMapper = new ProcedureStakeholderMapper($this->dbConnection);
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->mailTemplateMapper = $this->createMock(MailTemplateMapper::class);
        $this->procedureTypeGroupMapper = $this->createMock(ProcedureTypeGroupMapper::class);

        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);

        $this->nextCloudHelper = $this->createMock(NextcloudHelper::class);
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        $this->deckHelper = $this->createMock(DeckHelper::class);

        $this->mailQueueService = $this->createMock(MailQueueService::class);
        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureTypeService = $this->createMock(SignatureTypeService::class);
        $this->signatureButtonService = $this->createMock(SignatureButtonService::class);
        $this->procedureTypeGroupService = $this->createMock(ProcedureTypeGroupService::class);
        $this->webhookService = $this->createMock(WebhookService::class);

        $this->topicService = $this->createMock(TopicService::class);
        
        $this->procedureTokenService = new ProcedureTokenService(
            $this->configService,
            $this->topicService
        );
        // Create the service with mocked dependencies
        $this->procedureTypeService = new ProcedureTypeService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->procedureTypeMapper,
            $this->procedureTypeGroupMapper,
            $this->userGroupHelper,
            $this->configService
        );

        // Create the service with mocked dependencies
        $this->procedureTypeController = new ProcedureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeService,
            $this->userId
        );

        // Create the service with mocked dependencies
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
            $this->createMock(\OCA\VTramit\Service\ProcedureFileService::class),
            $this->createMock(\OCA\VTramit\Service\ProcedureVideoconferenceService::class),
            $this->createMock(\OCA\VTramit\Service\ProcedureMailService::class),
            $this->createMock(\OCA\VTramit\Service\ProcedureMessageService::class),
            $this->procedureTokenService,
            $this->procedureTypeService,
            $this->procedureTypeGroupService,
            $this->webhookService
        );
        $this->stakeholderService = $this->createMock(ProcedureStakeholderService::class);

        // Create the controller with mocked dependencies
        $this->procedureController = new ProcedureController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureService,
            $this->stakeholderService,
            $this->userId
        );
    }
    
    /**
     * 
     * Steps:
     *
     *  1.- Find rgpd option by itsi room code from procedure
     *  2.- Find rgpd option by itsi room code from cancelled procedure
     * 
     */
    public function testFindByJitsiRoomCode(): void {

        $this->createTestRequiredData();

        // Find rgpd option by itsi room code from procedure
        $this->findByJitsiRoomCode($this->entity, $this->procedureJitsiRoomCode);
        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];
        $procedureId = $this->createdEntities[get_class($this->procedureController)][0];

        // Find rgpd option by itsi room code from cancelled procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
        $procedureTestEntity["id"] = $procedureId;
        $procedureTestEntity["statusId"] = ProcedureStatus::CANCELLED;
        $responseProcedure = $this->update($this->procedureController, $procedureTestEntity);
        $this->findByJitsiRoomCode($this->entity, $this->procedureJitsiRoomCode);


    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Set mappers
        $this->rgpdOptionMapper = new RgpdOptionMapper($this->dbConnection);

        // Instantiate the service with mocks
        $this->rgpdOptionService = new RgpdOptionService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->rgpdOptionMapper,
            $this->procedureMapper,
            $this->procedureStakeholderMapper,
            $this->procedureTypeMapper
        );

        // Ensure controller is instantiated properly
        $this->controller = new RgpdOptionController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->rgpdOptionService,
            $this->userId
        );
    }

    /**
     * Checks rgpd option data against expected values
     *
     * @param array $expectedData Expected rgpd option data
     * @param array $actualData Actual rgpd option data from response
     */
    protected function checkEntityData(array $expectedData, array $actualData): void {
        // Check all received information
        $this->assertEquals(
            $expectedData['name'],
            $actualData['name'],
            'Name mismatch'
        );

        $this->assertEquals(
            $expectedData['type'],
            $actualData['type'],
            'Type mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['label']),
            json_encode($actualData['label']),
            'Label content mismatch'
        );

        $this->assertEquals(
            $expectedData['required'],
            $actualData['required'],
            'Required value mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Save rgpd option
        $rgpdOptionResponse = $this->create($this->controller, TestData::$rgpdOptionTestEntityData);
        $this->entity = $this->getEntity($rgpdOptionResponse);
        $this->logResponseData('RGPD OPTION', $rgpdOptionResponse);
        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["rgpdOptions"] = $this->createdEntities[get_class($this->controller)];
        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);
        $this->logResponseData('PROCEDURE TYPE', $responseProcedureType);
        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $responseProcedure = $this->create($this->procedureController, $procedureTestEntity);

        // Save jitsi room code
        $responseProcedureEntity = $this->getEntity($responseProcedure);
        $this->procedureJitsiRoomCode =  $responseProcedureEntity["jitsiRoomCode"];
        $this->logResponseData('PROCEDURE', $responseProcedureEntity);
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