<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\ProcedureStakeholder;

use OCA\VTramit\Service\ConfigService;
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
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Db\RgpdActivityMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureStakeholderController;
use OCA\VTramit\Controller\MailTemplateController;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\MailTemplateService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Service\WebhookService;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Test\Phpunit\Helpers\TestHelpers;
use OCA\VTramit\Controller\ProcedureTypeGroupController;
use OCA\VTramit\Db\MailQueueMapper;
use OCA\VTramit\Service\TopicService;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureVideoconferenceService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureTokenService;
/**
 * PU034
 */
class ProcedureStakeholderCreateJistsiRoomCodeTest extends CommonTestOperations
{

    private string $testFunction = "testCreateJitsiRoomCode";
    private string $noJitsiUrlDefinedError = "There is no base Jitsi URL defined to create the Jitsi room URL for the staff";
    /** @var TopicService */
    private TopicService $topicService;

    /** @var ProcedureController */
    private ProcedureController $procedureController;

    /** @var ProcedureTypeController */
    private ProcedureTypeController $procedureTypeController;

     /** @var ProcedureTypeGroupController */
    private ProcedureTypeGroupController $procedureTypeGroupController;

    /** @var ProcedureStakeholderController */
    private ProcedureStakeholderController $procedureStakeholderController;

  /** @var MailTemplateController */
    private MailTemplateController $mailTemplateController;

    /** @var ProcedureService */
    private ProcedureService $procedureService;

    /** @var ProcedureTypeService */
    private ProcedureTypeService $procedureTypeService;

    /** @var MailTemplateService */
    private MailTemplateService $mailTemplateService;

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

    /** @var ProcedureTypeProtocolStepMapper */
    private ProcedureTypeProtocolStepMapper $procedureTypeProtocolStepMapper;

    /** @var MailQueueMapper */
    private MailQueueMapper $mailQueueMapper;

    /**
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = '/procedure-stakeholder-crate-jitsi-room-code';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure stakeholder');

        // Set mock config service functions their return value

        // $this->procedureTools = $this->createMock(ProcedureTools::class);
        // $this->procedureTools->method('sendEmailToStakeholder')->willReturn(new ResponseInfo());
        // $this->procedureTools->method('sendEmailToStaff')->willReturn(new ResponseInfo());
        // 1) Forzar que no exista clave

        $fakeKey = 'FAKE_KEY_' . str_repeat('X', 54);

        // // 2) Verificar que se guarda exactamente una vez y capturar el valor
        // $this->configService = $this->getMockBuilder(ConfigService::class)
        //     ->setConstructorArgs([$this->config, $this->groupManager])
        //     ->onlyMethods(['generateRandomKey'])
        //     ->getMock();

        // $this->configService->method('generateRandomKey')
        //     ->willReturn($fakeKey);

        //    $this->config->method('getAppValue')
        //     ->with('vtramit', 'encryption_key', '')
        //     ->willReturn('');
        $this->topicService = $this->createMock(TopicService::class);

        $this->procedureTokenService = new ProcedureTokenService(
            $this->configService,
            $this->topicService
        );

        $this->procedureFileService = $this->createMock(ProcedureFileService::class);
        $this->procedureFileService->method('getWebPathToFolder')->willReturn("");

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
        $this->mailTemplateMapper = new MailTemplateMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = new ProcedureTypeGroupMapper($this->dbConnection);
        $this->procedureTypeProtocolStepMapper = new ProcedureTypeProtocolStepMapper($this->dbConnection);
        $this->mailQueueMapper = new MailQueueMapper($this->dbConnection);

        // Mock classes
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);

        $this->nextCloudHelper = $this->createMock(NextcloudHelper::class);
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->deckHelper = $this->createMock(DeckHelper::class);


        // Initialize services
        $this->mailQueueService = new MailQueueService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->mailQueueMapper,
            $this->configService
        );
        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureTypeService = $this->createMock(SignatureTypeService::class);
        $this->signatureButtonService = $this->createMock(SignatureButtonService::class);

        //$this->procedureTypeGroupService = $this->createMock(ProcedureTypeGroupService::class);
        $this->webhookService = $this->createMock(WebhookService::class);
        // Create procedure type group service
        $this->procedureTypeGroupService = new ProcedureTypeGroupService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->procedureTypeGroupMapper,
            $this->procedureTypeProtocolStepMapper
        );

        // Create procedure type group controller
        $this->procedureTypeGroupController = new ProcedureTypeGroupController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeGroupService,
            $this->userId
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


        $this->mailTemplateService = new MailTemplateService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->mailTemplateMapper,
            $this->createMock(ProcedureTokenService::class)
        );

        $this->mailTemplateController = new MailTemplateController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->mailTemplateService,
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
    }

    /**
     * 
     * Steps:
     *
     *  1.- Create stakeholder with a procedure type with jisti stakeholder url filled
     *  2.- Create stakeholder with a procedure type without jisti stakeholder url filled
     *  3.- Get full jitsi room stakeholder url
     *  4.- Get jitsi room stakeholder jwt
     * 
     * 
     * 
     */
    public function testCreateJitsiRoomCode(): void {

        $this->createTestRequiredData();

        $procedureId = $this->createdEntities[get_class($this->procedureController)][0];
        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];
        // Create procedure stakeholder
        $initialData = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);

        // Create stakeholder with a procedure type with jisti stakeholder url filled
        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->logResponseData('CreateStakeholder', $responseCreateStakeholder);
        $this->assertEquals("OK", $responseCreateStakeholder["result"]);

        // Create stakeholder with a procedure type without jisti stakeholder url filled
        $this->entity = $this->getEntity($responseCreateStakeholder);
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["allowVideo"] = true;
        $procedureTypeTestEntity["jitsiStakeholderUrl"] = "";
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["jitsiStaffJwtPayload"] = '{
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
        $procedureTypeTestEntity["jitsiStaffJwtSecret"] ='my_super_secure_secret_key_12345!';
        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);
        $this->procedureTypeEntity = $responseProcedureType["entity"];

        $responseCreateStakeholderError =  $this->create($this->controller, $initialData);
        $this->logResponseData('CreateStakeholderError', $responseCreateStakeholderError);
        $this->assertEquals("EXCEPTION", $responseCreateStakeholderError["result"]);
        $this->assertEquals($this->noJitsiUrlDefinedError, $responseCreateStakeholderError["message"]);

        // Get full jitsi room stakeholder url
        $responseGetFullJitsiRoom =$this->procedureStakeholderService->getFullJitsiRoomStakeholderUrl($responseCreateStakeholder["entity"]);
        $this->assertStringStartsWith(
            'https://demovideollamada.vtramit.com/',
            $responseGetFullJitsiRoom
        );

        parse_str(parse_url($responseGetFullJitsiRoom, PHP_URL_QUERY), $params);

        $jwt = $params['jwt'] ?? null;
    
        // Get jitsi room stakeholder jwt
        $responseGetJitsiRoomJwt =$this->procedureStakeholderService->getJitsiRoomStakeholderJwt($responseCreateStakeholder["entity"]);

        $this->assertEquals($responseGetJitsiRoomJwt, $jwt);
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {

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

        $this->controller = new ProcedureStakeholderController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureStakeholderService,
            $this->userId
        );
    }

    /**
     * Checks response data against expected values
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
            TestHelpers::normalizePhoneNumber($expectedData['phone']),
            $actualData['phone'],
            'Phone mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['email']),
            json_encode($actualData['email']),
            'Email mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        $responseMailTemplate = $this->create($this->mailTemplateController,TestData::$mailTemplateEntityData);
        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["allowVideo"] = true;
        $procedureTypeTestEntity["jitsiStakeholderJwtPayload"] = '{"key1":"value1","key2":"value2"}';
        $procedureTypeTestEntity["jitsiStakeholderJwtSecret"] = 'TEST';

        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);

        // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $responseProcedureType["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStakeholderTemplateId"] = $responseMailTemplate["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStaffTemplateId"] = $responseMailTemplate["entity"]->id;

        $responseProcedureTypeGroup = $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);


        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $responseProcedure = $this->create($this->procedureController, $procedureTestEntity);
        $this->logResponseData('responseProcedure', $responseProcedure);

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
            case 'OCA\VTramit\Controller\ProcedureTypeGroupController':
                return $this->procedureTypeGroupController;
            case 'OCA\VTramit\Controller\MailTemplateController':
                return $this->mailTemplateController;
            default:
                return $this->controller;
        }
    }
}
