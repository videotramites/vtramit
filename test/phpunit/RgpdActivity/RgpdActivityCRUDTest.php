<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\RgpdActivity;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\RgpdActivityMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\RgpdActivityController;
use OCA\VTramit\Controller\ProcedureStakeholderController;
use OCA\VTramit\Service\ActivityManager;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\RgpdActivityService;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureTypeService;
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
 * PU045
 */
class RgpdActivityCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";

    /** @var ProcedureController */
    private ProcedureController $procedureController;

    /** @var ProcedureTypeController */
    private ProcedureTypeController $procedureTypeController;

    /** @var ProcedureStakeholderController */
    private ProcedureStakeholderController $procedureStakeholderController;

    /** @var RgpdActivityService */
    private RgpdActivityService $rgpdActivityService;

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

    /** @var ProcedureMailService */
    private ProcedureMailService $procedureMailService;

    /** @var ProcedureMessageService */
    private ProcedureMessageService $procedureMessageService;

    /** @var ProcedureTypeGroupService */
    private ProcedureTypeGroupService $procedureTypeGroupService;

    /** @var WebhookService */
    private WebhookService $webhookService;

    /** @var RgpdActivityMapper */
    private RgpdActivityMapper $rgpdActivityMapper;

    /** @var ProcedureMapper */
    private ProcedureMapper $procedureMapper;

    /** @var ProcedureStakeholderMapper */
    private ProcedureStakeholderMapper $procedureStakeholderMapper;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /** @var MailTemplateMapper */
    private MailTemplateMapper $mailTemplateMapper;

    /** @var ProcedureTypeGroupMapper */
    private ProcedureTypeGroupMapper $procedureTypeGroupMapper;

    /**
     * Constructor for the Rgpd Activity test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'rgpd-activity-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('rgpd activity');

        $fakeKey = 'FAKE_KEY_' . str_repeat('X', 54); // 64 chars

        $saved = null;

        $this->config->expects($this->atLeastOnce())
            ->method('setAppValue')
            ->with(
                $this->anything(),               
                $this->equalTo('encryption_key'),
                $this->callback(function ($value) use (&$saved) {
                    $this->assertIsString($value);
                    $this->assertSame(64, strlen($value));
                    $saved = $value;       
                    return true;
                })
            );
        $this->initializeConfigService();

        // Inizializate mappers
        $this->procedureMapper = new ProcedureMapper($this->dbConnection);
        $this->procedureStakeholderMapper = new ProcedureStakeholderMapper($this->dbConnection);
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->mailTemplateMapper = $this->createMock(MailTemplateMapper::class);
        $this->procedureTypeGroupMapper = $this->createMock(ProcedureTypeGroupMapper::class);

        // Mock classes
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);

        $this->nextCloudHelper = $this->createMock(NextcloudHelper::class);
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->deckHelper = $this->createMock(DeckHelper::class);

        $this->mailQueueService = $this->createMock(MailQueueService::class);
        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureTypeService = $this->createMock(SignatureTypeService::class);
        $this->signatureButtonService = $this->createMock(SignatureButtonService::class);

        $this->procedureTypeGroupService = $this->createMock(ProcedureTypeGroupService::class);
        $this->webhookService = $this->createMock(WebhookService::class);

        $this->procedureMailService = $this->createMock(ProcedureMailService::class);
        $this->procedureMailService->method('sendEmailToStakeholder')->willReturn(new ResponseInfo());
        $this->procedureMailService->method('sendEmailToStaff')->willReturn(new ResponseInfo());

        $this->procedureMessageService = $this->createMock(ProcedureMessageService::class);
        $this->procedureMessageService->method('sendMessagesToStakeholder')->willReturn(new ResponseInfo());
        $this->procedureMessageService->method('sendMessagesToStaff')->willReturn(new ResponseInfo());

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
            $this->procedureMailService,
             $this->procedureMessageService,
            $this->createMock(\OCA\VTramit\Service\ProcedureTokenService::class),
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
            $this->createMock(\OCA\VTramit\Service\ProcedureFileService::class),
            $this->procedureMailService,
            $this->procedureMessageService,
            $this->createMock(\OCA\VTramit\Service\ProcedureTokenService::class),
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

        $this->procedureStakeholderController = new ProcedureStakeholderController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureStakeholderService,
            $this->userId
        );

    }

    /**
     * 
     * Steps:
     *
     *  1.- Create rgpd activity correctly (CREATE)
     *  2.- Find rgpd activity correctly using ID (FIND)
     *  3.- Find rgpd activity in rgpd activity existing entities list (FIND ALL)
     *  4.- Update rgpd activity correctly (UPDATE)
     *  5.- Delete rgpd activity correctly (DELTE)
     * 
     */
    public function testCRUD(): void {

        $this->createTestRequiredData();

        $procedureId = $this->createdEntities[get_class($this->procedureController)][0];
        $procedureStakeholderId = $this->createdEntities[get_class($this->procedureStakeholderController)][0];

        $initalData = $this->setAssociatedInfoToRgpdActivity(TestData::$rgpdActivityTestEntityData, $procedureId, $procedureStakeholderId);
        $updateData = $this->setAssociatedInfoToRgpdActivity(TestData::$updatedRgpdActivityTestEntityData, $procedureId, $procedureStakeholderId);

        // Create rgpd activity correctly (CREATE)
        // Find rgpd activity correctly using ID (FIND)
        // Find rgpd activity in rgpd activity existing entities list (FIND ALL)
        // Update rgpd activity correctly (UPDATE)
        // Delete rgpd activity correctly (DELTE)
        $this->runCRUDTest($initalData, $updateData);
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Set mappers
        $this->rgpdActivityMapper = new RgpdActivityMapper($this->dbConnection);

        $this->rgpdActivityService = new RgpdActivityService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->rgpdActivityMapper,
            $this->configService
        );

        $this->controller = new RgpdActivityController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->rgpdActivityService,
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
            $expectedData['procedureStakeholderId'],
            $actualData['procedureStakeholderId'],
            'Procedure Stakeholder Id mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['label']),
            json_encode($actualData['label']),
            'Label content mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);

        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $responseProcedure = $this->create($this->procedureController, $procedureTestEntity);

        // Create procedure stakeholder
        $procedureStakeholderTestEntity = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $responseProcedure["entity"]->id);
        $procedureStakeholderTestResponse = $this->create($this->procedureStakeholderController, $procedureStakeholderTestEntity);
        $this->logResponseData('procedureStakeholderTestResponse', $procedureStakeholderTestResponse);

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
            case 'OCA\VTramit\Controller\ProcedureStakeholderController':
                return $this->procedureStakeholderController;
            default:
                return $this->controller;
        }
    }
}