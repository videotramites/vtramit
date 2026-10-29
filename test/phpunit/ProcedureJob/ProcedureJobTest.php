<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\ProcedureJob;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCA\VTramit\Exception\DataNotFoundException;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\RgpdActivityMapper;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureJobController;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Service\ProcedureTools;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureJobService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Service\WebhookService;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\ProcedureStakeholderController;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureVideoconferenceService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureTokenService;

/**
 * Tests for Procedure Job (PU032)
 */
class ProcedureJobTest extends CommonTestOperations
{

    private string $testFunction = "testProcedureJob";

    /** @var Procedure */
    private Procedure $procedure;

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

    /** @var ProcedureTypeGroupService */
    private ProcedureTypeGroupService $procedureTypeGroupService;

    /** @var WebhookService */
    private WebhookService $webhookService;

    /** @var ProcedureJobService */
    private ProcedureJobService $procedureJobService;

       /** @var ProcedureJobController */
    private ProcedureJobController $procedureJobController;

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

    /** @var ProcedureController */
    private ProcedureController $procedureController;


    /** @var ProcedureStakeholderController */
    private ProcedureStakeholderController $procedureStakeholderController;

    /**
     * Constructor for the Procedure test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'procedure-job-test';
        $this->setEntityTypeName('procedure job');
        parent::__construct($testName, $this->testFunction);

        $yesterday = new \DateTime('yesterday', new \DateTimeZone('Europe/Berlin'));

        $this->procedureTokenService = $this->createMock(ProcedureTokenService::class);

        $this->procedureFileService = $this->createMock(ProcedureFileService::class);

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
        $this->signatureService->method('isProcedureWaitingForSignature')->willReturn(true);
        $this->signatureService->method('procedureHasSignatureWaitingForDownload')->willReturn(false);
        $this->signatureTypeService = $this->createMock(SignatureTypeService::class);
        $this->signatureButtonService = $this->createMock(SignatureButtonService::class);

        $this->procedureTypeGroupService = $this->createMock(ProcedureTypeGroupService::class);
        $this->webhookService = $this->createMock(WebhookService::class);

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
     * 1.- Call findWorkForTodayToPrepare(), prepareWorkToday() and purgeData() with a procedure with a signature pending of downloading
     * 2.- Call findWorkForTodayToPrepare(), prepareWorkToday() and purgeData() with a procedure with a signature pending
     * 3.- Call findWorkForTodayToPrepare(), prepareWorkToday() and purgeData() with a procedure with a signature pending of downloading
     * 
     */
    public function testProcedureJob(): void {

        $this->createTestRequiredData();

        // Call findWorkForTodayToPrepare(), prepareWorkToday() and purgeData() with a procedure un procedure with a signature pending of downloading
        $responseProcedureJobPrepare = $this->procedureJobController->findWorkForTodayToPrepare()->getData();
        $this->logResponseData('FIND WORK FOR TODAY TO PREPARE', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $responseProcedureJobPrepare = $this->procedureJobController->prepareWorkToday()->getData();
        $this->logResponseData('PREPARE WORK TODAY', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $responseProcedureJobPrepare = $this->procedureJobController->purgeData()->getData();
        $this->logResponseData('PURGE DATA', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureService->method('isProcedureWaitingForSignature')->willReturn(false);
        $this->signatureService->method('procedureHasSignatureWaitingForDownload')->willReturn(true);

        $this->setUpController();

        // Call findWorkForTodayToPrepare(), prepareWorkToday() and purgeData() with a procedure with a signature pending
        $responseProcedureJobPrepare = $this->procedureJobController->findWorkForTodayToPrepare()->getData();
        $this->logResponseData('FIND WORK FOR TODAY TO PREPARE', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $responseProcedureJobPrepare = $this->procedureJobController->prepareWorkToday()->getData();
        $this->logResponseData('PREPARE WORK TODAY', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $responseProcedureJobPrepare = $this->procedureJobController->purgeData()->getData();
        $this->logResponseData('PURGE DATA', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureService->method('isProcedureWaitingForSignature')->willReturn(false);
        $this->signatureService->method('procedureHasSignatureWaitingForDownload')->willReturn(false);

        $this->setUpController();

        // Call findWorkForTodayToPrepare(), prepareWorkToday() and purgeData() with a procedure with a signature pending
        $responseProcedureJobPrepare = $this->procedureJobController->findWorkForTodayToPrepare()->getData();
        $this->logResponseData('FIND WORK FOR TODAY TO PREPARE', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $responseProcedureJobPrepare = $this->procedureJobController->prepareWorkToday()->getData();
        $this->logResponseData('PREPARE WORK TODAY', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $responseProcedureJobPrepare = $this->procedureJobController->purgeData()->getData();
        $this->logResponseData('PURGE DATA', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $this->procedureFileService = $this->createMock(ProcedureFileService::class);
        $this->procedureFileService->method('getLocalPathToProcedure')->will($this->throwException(new DataNotFoundException()));
        $this->procedureMapper = $this->createMock(ProcedureMapper::class);
        $this->procedureMapper->method('findNotAnonymizedByStatus')->willReturn([]);
        $this->procedureMapper->method('findAll')->willReturn([]);
        $this->procedureMapper->method('findNotAnonymizedByStatusAndAge')->willReturn([$this->procedure]);

        $this->setUpController();

        // Call findWorkForTodayToPrepare(), prepareWorkToday() and purgeData() with a procedure 
        $responseProcedureJobPrepare = $this->procedureJobController->findWorkForTodayToPrepare()->getData();
        $this->logResponseData('FIND WORK FOR TODAY TO PREPARE', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $responseProcedureJobPrepare = $this->procedureJobController->prepareWorkToday()->getData();
        $this->logResponseData('PREPARE WORK TODAY', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

        $responseProcedureJobPrepare = $this->procedureJobController->purgeData()->getData();
        $this->logResponseData('PURGE DATA', $responseProcedureJobPrepare);
        $this->assertEquals("OK", $responseProcedureJobPrepare["result"]);

    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {

        $this->procedureJobService = new ProcedureJobService(
            $this->logger,
            $this->translate,
            $this->rootFolder,
            $this->urlGenerator,
            $this->procedureMapper,
            $this->procedureService,
            $this->procedureFileService,
            $this->procedureStakeholderMapper,
            $this->procedureStakeholderService,
            $this->procedureTypeMapper,
            $this->nextCloudHelper,
            $this->configService,
            $this->signatureService
        );

        $this->procedureJobController = new ProcedureJobController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureJobService,
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
        $procedureTypeTestEntity["procedureAnonymizationPeriod"] = 0;

        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);

        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["procedureAnonymizationPeriod"] = 7;

        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);

        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);


        $date = new \DateTime("today", new \DateTimeZone('Europe/Berlin'));
        $formattedDate = $date->format('Y-m-d H:i:s');
        $procedureTestEntity["appointmentDate"] = $formattedDate;

        $responseProcedure = $this->create($this->procedureController, $procedureTestEntity);
        $this->procedure = $responseProcedure["entity"];

		$this->procedureMapper = $this->createMock(ProcedureMapper::class);
        $this->procedureMapper->method('findNotAnonymizedByStatusAndAge')
                ->willReturn([$this->procedure]);
        $this->procedureMapper->method('findNotAnonymizedByStatus')
                ->willReturn([$this->procedure]);
        $this->setUpController();
        $this->entity = $this->getEntity($responseProcedure);

        // Create procedure stakeholder
        $procedureStakeholderTestEntity = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $responseProcedure["entity"]->id);
        $procedureStakeholderTestEntity["email"] = $this->testEmail;
        $responseProcedureStakeholder = $this->create($this->procedureStakeholderController, $procedureStakeholderTestEntity);
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