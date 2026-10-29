<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\MailQueue;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;

use OCA\VTramit\Db\MailQueueMapper;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\MailQueue;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\ProcedureStakeholderMapper;

use OCA\VTramit\Controller\MailQueueController;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureTypeController;

use OCA\VTramit\Service\ActivityManager;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureTools;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Service\WebhookService;

use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Helper\DeckHelper;

use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;
use OCA\VTramit\Controller\EntityController;

/**
 * (PU004)
 */
class MailQueueByProcedureIdCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testByProcedureIdCRUD";

    /** @var ProcedureController */
    private ProcedureController $procedureController;

    /** @var ProcedureTypeController */
    private ProcedureTypeController $procedureTypeController;

    /** @var MailQueueService */
    private MailQueueService $mailQueueService;

    /** @var ProcedureService */
    private ProcedureService $procedureService;

    /** @var ProcedureTypeService */
    private ProcedureTypeService $procedureTypeService;

    /** @var MailQueueMapper */
    private MailQueueMapper $mailQueueMapper;

    /** @var ProcedureMapper */
    private ProcedureMapper $procedureMapper;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /**
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'mail-queue-by-procedure-id-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('mail queue');

        // Initialize mappers
        $this->mailQueueMapper = new MailQueueMapper($this->dbConnection);
        $this->procedureMapper = new ProcedureMapper($this->dbConnection);
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);

        // Mock classes
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);
        $this->nextCloudHelper = $this->createMock(NextcloudHelper::class);
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->deckHelper = $this->createMock(DeckHelper::class);

        // Initialize services and controllers
        $this->procedureTypeService = new ProcedureTypeService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->procedureTypeMapper,
            $this->createMock(\OCA\VTramit\Db\ProcedureTypeGroupMapper::class),
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
     * Steps:
     *
     *  1.- Create a Mail queue entity
     *  2.- Search the created Mail Queue by procedure id
     *  3.- Delete the created Mail Queue by procedure id
     * 
     */
    public function testByProcedureIdCRUD(): void {

        $this->createTestRequiredData();

        $procedureId = $this->createdEntities[get_class($this->procedureController)][0];

        // Create a Mail Queue
        $initalData = TestData::$mailQueueTestEntityData;
        $initalData['procedureId'] = $procedureId;
        $responseMailQueue = $this->create($this->controller, $initalData);

        // Search Mail Queue by procedure id
        $procedureFindByIdResponse = $this->mailQueueService->findByProcedureId($procedureId);
        $this->logResponseData('FIND BY PROCEDURE ID', $procedureFindByIdResponse);
        $this->checkEntityData($this->getEntity($responseMailQueue), json_decode(json_encode($procedureFindByIdResponse[0], JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR));
        
        // Delete Mail Queue by procedure id
        $procedureDeleteByIdResponse = $this->mailQueueService->deleteByProcedureId($procedureId)->getData();
        $this->logResponseData('DELETE BY PROCEDURE ID', $procedureDeleteByIdResponse);
        $this->assertEquals("OK",  $procedureDeleteByIdResponse["result"]);
    }


    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Set mapper
        $this->mailQueueMapper = new MailQueueMapper($this->dbConnection);

        // Initialize service
        $this->mailQueueService = new MailQueueService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->mailQueueMapper,
            $this->configService
        );

        // Initialize controller
        $this->controller = new MailQueueController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->mailQueueService,
            $this->userId
        );

        // Create the procedure service
        $this->procedureService = new ProcedureService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->rootFolder,
            $this->urlGenerator,
            $this->createMock(MailTemplateMapper::class),
            $this->procedureMapper,
            $this->createMock(ProcedureStakeholderMapper::class),
            $this->procedureTypeMapper,
            $this->createMock(\OCA\VTramit\Db\ProcedureTypeGroupMapper::class),
            $this->nextCloudHelper,
            $this->userGroupHelper,
            $this->deckHelper,
            $this->configService,
            $this->mailQueueService,
            $this->createMock(SignatureService::class),
            $this->createMock(SignatureTypeService::class),
            $this->createMock(SignatureButtonService::class),
            $this->createMock(\OCA\VTramit\Service\ProcedureFileService::class),
            $this->createMock(\OCA\VTramit\Service\ProcedureVideoconferenceService::class),
            $this->createMock(\OCA\VTramit\Service\ProcedureMailService::class),
            $this->createMock(\OCA\VTramit\Service\ProcedureMessageService::class),
            $this->createMock(\OCA\VTramit\Service\ProcedureTokenService::class),
            $this->procedureTypeService,
            $this->createMock(ProcedureTypeGroupService::class),
            $this->createMock(WebhookService::class)
        );
        $this->stakeholderService = $this->createMock(ProcedureStakeholderService::class);

        // Create the procedure controller
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
     * Checks response data against expected values
     *
     * @param array $expectedData Expected data
     * @param array $actualData Actual data from response
     */
    protected function checkEntityData(array $expectedData, array $actualData): void {
        // Check all received information
        $this->assertEquals(
            $expectedData['procedureId'],
            $actualData['procedureId'],
            'Procedure Id mismatch'
        );

        $this->assertEquals(
            $expectedData['subject'],
            $actualData['subject'],
            'Subject mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['body']),
            json_encode($actualData['body']),
            'Body mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['citizenPassword']),
            json_encode($actualData['citizenPassword']),
            'Citizen Password mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['mailTo']),
            json_encode($actualData['mailTo']),
            'Mail To mismatch'
        );

        if (isset($expectedData['mailCc']) && isset($actualData['mailCc'])) {

            $this->assertEquals(
                json_encode($expectedData['mailCc']),
                json_encode($actualData['mailCc']),
                'Mail Cc mismatch'
            );

        }

        if (isset($expectedData['mailCco']) && isset($actualData['mailCco'])) {

            $this->assertEquals(
                json_encode($expectedData['mailCco']),
                json_encode($actualData['mailCco']),
                'Mail Cco mismatch'
            );
        }

    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Create procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $responseProcedureType = $this->create($this->procedureTypeController, TestData::$procedureTypeTestEntityData);

        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $responseProcedure = $this->create($this->procedureController, $procedureTestEntity);

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