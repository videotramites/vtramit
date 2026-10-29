<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\ProcedureStakeholder;

use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Test\Phpunit\ProcedureType\ProcedureTypeGetContextTest;
use OCP\IURLGenerator;
use OCP\Files\IRootFolder;

use OCA\VTramit\Exception\DataNotFoundException;
use OCA\VTramit\Exception\EntityNotFoundException;

use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\AttendanceChannel;

use OCA\VTramit\Db\MailQueueMapper;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\MessageQueueMapper;
use OCA\VTramit\Db\MessageTemplateMapper;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureTypeGroup;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\RgpdActivityMapper;
use OCA\VTramit\Db\SignatureButton;
use OCA\VTramit\Db\SignatureButtonMapper;
use OCA\VTramit\Db\SignatureTypeMapper;
use OCA\VTramit\Db\SignatureFileMapper;
use OCA\VTramit\Db\SignatureMapper;
use OCA\VTramit\Db\TopicMapper;
use OCA\VTramit\Db\WebhookMapper;

use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\MailTemplateController;
use OCA\VTramit\Controller\MessageTemplateController;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureStakeholderController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureTypeGroupController;
use OCA\VTramit\Controller\SignatureButtonController;
use OCA\VTramit\Controller\SignatureTypeController;

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

use OCA\VTramit\Helper\BeHelper;
use OCA\VTramit\Helper\CurlHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Helper\JwtHelper;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;
use OCA\VTramit\Test\Phpunit\Helpers\TestHelpers;

/**
 * PU036
 */
class ProcedureStakeholderCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";
    private string $noProcedureAssignedError = "The stakeholder must belong to a procedure";
    private string $noMessageStaffTemplateFound = "There is no template defined for the staff to send the message";
    private string $noMessageTemplateFound = "There is no template defined to send the message";
    private string $noEmailError = "Email is not valid";
    private string $noStakeholderNameError = "Please indicate the stakeholder name";
    private string $noStakeholderPhoneError = "Please indicate the stakeholder phone";
    private string $noFoldersCreatedError = "Folders could not be created";
    private string $noJitsiRoomCodeError = "To create the Jitsi room URL for the stakeholders you have to generate first a Jitsi room code";
    private Procedure $procedureTestEntity;
    private ProcedureTypeGroup $procedureTypeGroup;

    /** @var SignatureButton */
    private SignatureButton $signatureButton;

    /** @var ProcedureStakeholder */
    private ProcedureStakeholder $procedureStakeholder;

    /** @var MailQueueMapper */
    private MailQueueMapper $mailQueueMapper;

    /** @var MailQueueService */
    private MailQueueService $mailQueueService;

    /** @var MailTemplateController */
    private MailTemplateController $mailTemplateController;

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
    
    /** @var MessageTemplateController */
    private MessageTemplateController $messageTemplateController;

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

    /** @var SignatureButtonController */
    private SignatureButtonController $signatureButtonController;

    /** @var SignatureButtonMapper */
    private SignatureButtonMapper $signatureButtonMapper;

    /** @var SignatureButtonService */
    private SignatureButtonService $signatureButtonService;

    /** @var SignatureFileMapper */
    private SignatureFileMapper $signatureFileMapper;

    /** @var SignatureMapper */
    private SignatureMapper $signatureMapper;

    /** @var SignatureTypeController */
    private SignatureTypeController $signatureTypeController;

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
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = '/procedure-stakeholder-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure stakeholder');

        $saved = null;

        // 1) getAppValue: 1ª vez '' (fuerza generación), siguientes devuelven lo guardado
        // $this->config->method('getAppValue')
        //     ->willReturnCallback(function ($app, $key, $default = '') use (&$saved) {
        //         if ($key !== 'encryption_key') return $default;
        //         static $first = true;
        //         if ($first) { $first = false; return ''; }   // primera llamada
        //         return $saved ?? $default;                   // siguientes
        //     });

                // 2) setAppValue: aceptar cualquier string de 64 chars y CAPTURARLA
        $this->config->expects($this->atLeastOnce())
            ->method('setAppValue')
            ->with(
                $this->anything(),                       // o $this->equalTo('vtramit')
                $this->equalTo('encryption_key'),
                $this->callback(function ($value) use (&$saved) {
                    $this->assertIsString($value);
                    $this->assertSame(64, strlen($value));
                    $saved = $value;                     // 👈 guardamos lo que realmente genera BeHelper
                    return true;
                })
            );
        $this->initializeConfigService();

        // Inizializate mappers
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

        // Create signature button service
        $this->signatureTypeService = new SignatureTypeService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->signatureTypeMapper
        );

        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->userGroupHelper->method('getUserEmail')->willReturn($this->testEmail);
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

        $this->mailTemplateController = new MailTemplateController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->mailTemplateService,
            $this->userId
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

        
        // Initialize mapper
        $this->signatureButtonMapper = new SignatureButtonMapper($this->dbConnection);

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

        $this->procedureTypeController = new ProcedureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeService,
            $this->userId
        );

        // Create signature type controller
        $this->signatureTypeController = new SignatureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->signatureTypeService,
            $this->userId
        );

        $this->signatureButtonController = new SignatureButtonController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->signatureButtonService,
            $this->userId
        );

        $this->procedureTypeGroupController = new ProcedureTypeGroupController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeGroupService,
            $this->userId
        );

        $this->messageTemplateController = new MessageTemplateController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->messageTemplateService,
            $this->userId
        );

    }

    /**
     * 
     * Steps:
     *
     *  1.- Create stakeholder without setting it main
     *  2.- Updating stakeholder without setting it main
     *  3.- Create procedure stakeholder correctly (CREATE)
     *  4.- Find procedure stakeholder correctly using ID (FIND)
     *  5.- Find procedure stakeholder in procedure existing entities list (FIND ALL)
     *  6.- Update procedure stakeholder correctly (UPDATE)
     *  7.- Delete procedure stakeholder correctly (DELTE)
     *  8.- Create stakeholder without indicating message/mail templates
     *  9.- Create procedure stakeholder without procedure id
     *  10.- Create procedure stakeholder without indicating email, name or phone
     *  11.- Create procedure stakeholder checking if its main
     *  12.- Create procedure stakeholder and delete it using its entity
     *  13.- Create procedure stakeholder forcing createFolders to fail
     *  14.- Create procedure stakeholder when main is not found
     *  15.- Create procedure stakeholder assigned to a procedure without jitsi room code
     */
    public function testCRUD(): void {

        $this->createTestRequiredData();

        $procedureId = $this->createdEntities[get_class($this->procedureController)][0];
        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];
        // Create procedure stakeholder
        $initialData = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);
        $updateData = $this->setAssociatedInfoToProcedureStakeholder(TestData::$updatedProcedureStakeholderTestEntityData, $procedureId);

        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->setUpController();

        // Create stakeholder without setting it main
        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->assertEquals("OK", $responseCreateStakeholder["result"]);
        $responseCreateStakeholder["entity"]->setIsMain(false);

        $responseUpdateStakeholder =  $this->create($this->controller, $updateData);
        $this->assertEquals("OK", $responseCreateStakeholder["result"]);
        $responseUpdateStakeholder["entity"]->setIsMain(false);
        $this->entity = $this->getEntity($responseUpdateStakeholder);

        $this->procedureStakeholderMapper = $this->createMock(ProcedureStakeholderMapper::class);
        $this->procedureStakeholderMapper->method('findMainByProcedure')->willReturn($responseCreateStakeholder["entity"]);
        $this->procedureStakeholderMapper->method('find')->willReturn($responseUpdateStakeholder["entity"]);
        $this->setUpController();


        $responseCreateStakeholder =  $this->create($this->controller, $updateData);
        $this->logResponseData('CREATE MAIN FALSE', $responseCreateStakeholder);
        $this->assertEquals("EXCEPTION", $responseCreateStakeholder["result"]);

        // Updating stakeholder without setting it main
        $responseCreateStakeholder =  $this->update($this->controller, $this->entity);
        $this->logResponseData('UPDATE MAIN FALSE', $responseCreateStakeholder);
        $this->assertEquals("EXCEPTION", $responseCreateStakeholder["result"]);

        $this->procedureStakeholderMapper = new ProcedureStakeholderMapper($this->dbConnection);
        $this->setUpController();

        // Create procedure stakeholder correctly (CREATE)
        // Find procedure stakeholder correctly using ID (FIND)
        // Find procedure stakeholder in procedure existing entities list (FIND ALL)
        // Update procedure stakeholder correctly (UPDATE)
        // Delete procedure stakeholder correctly (DELTE)
        $this->runCRUDTest($initialData, $updateData);

        // Create stakeholder without indicating message/mail templates
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["id"] =  $this->procedureTypeGroup->id;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $procedureTypeId;
        $procedureTypeGroupTestEntity["notifyStakeholderTemplateId"] = $this->procedureTypeGroup->getNotifyStakeholderTemplateId();
        $procedureTypeGroupTestEntity["notifyStaffTemplateId"] = $this->procedureTypeGroup->getNotifyStaffTemplateId();
        $procedureTypeGroupTestEntity["notifyStakeholderTemplate"] = 0;
        $procedureTypeGroupTestEntity["notifyStaffTemplate"] = 0;
        $procedureTypeGroupTestEntity["allowAssignUser"] = true;
        $procedureTypeGroupTestEntity["bypassRequired"] = false;

        $responseProcedureTypeGroup = $this->update($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);
        $initialData["isMain"] = true;
        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->logResponseData('NO MAIL TEMPLATES ERROR', $responseCreateStakeholder);
        $this->assertEquals("ERROR", $responseCreateStakeholder["result"]);
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noMessageStaffTemplateFound), false)
        );
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noMessageTemplateFound), false)
        );

        // Create procedure stakeholder without procedure id
        $responseCreateStakeholder =  $this->create($this->controller, TestData::$procedureStakeholderTestEntityData);
        $this->logResponseData('NO PROCEDURE ID', $responseCreateStakeholder);
        $this->assertEquals("ERROR", $responseCreateStakeholder["result"]);
        $this->assertTrue(
            array_reduce($responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noProcedureAssignedError), false)
        );

        // Create procedure stakeholder without indicating email, name or phone
        $initialData = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);
        $initialData["email"] = "";
        $initialData["name"] = "";
        $initialData["phone"] = "";

        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->logResponseData('NO DATA STAKEHOLDER', $responseCreateStakeholder);
        $this->assertEquals("ERROR", $responseCreateStakeholder["result"]);
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noEmailError), false)
        );
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noStakeholderNameError), false)
        );
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noStakeholderPhoneError), false)
        );

        // Create procedure stakeholder checking if its main
        $initialData = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);
        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->logResponseData('CREATE ISMAIN', $responseCreateStakeholder);
        $this->assertEquals("ERROR", $responseCreateStakeholder["result"]);
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noMessageTemplateFound), false)
        );
        $responseCreateStakeholderEntity = $this->getEntity($responseCreateStakeholder);
        
        $responseCreateStakeholder =  $this->update($this->controller,$responseCreateStakeholderEntity);
        $this->assertEquals("OK", $responseCreateStakeholder["result"]);
        $initialData = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);
        $initialData["isMain"] = true;
        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->assertEquals("ERROR", $responseCreateStakeholder["result"]);
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noMessageStaffTemplateFound), false)
        );
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noMessageTemplateFound), false)
        );
        $responseCreateStakeholder =  $this->update($this->controller,$responseCreateStakeholderEntity);
        $this->assertEquals("OK", $responseCreateStakeholder["result"]);
        $this->entity = $this->getEntity($responseCreateStakeholder);

        $responseCreateStakeholder =  $this->update($this->controller,$this->entity);
        $this->assertEquals("OK", $responseCreateStakeholder["result"]);
        $responseCreateStakeholderEntity["isMain"] = true;
        $responseCreateStakeholder =  $this->update($this->controller,$responseCreateStakeholderEntity);
        $this->assertEquals("OK", $responseCreateStakeholder["result"]);

        // Create procedure stakeholder and delete it using its entity
        $initialData = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);

        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->assertNotEmpty($responseCreateStakeholder["entity"]);

        $responseDeleteStakeholder = $this->procedureStakeholderService->delete($responseCreateStakeholder["entity"])->getData();
        $this->assertEquals("OK", $responseDeleteStakeholder["result"]);

        // Create procedure stakeholder forcing createFolders to fail
        $this->procedureFileService = $this->createMock(ProcedureFileService::class);
        $this->procedureFileService->method('createFolders')->will($this->throwException(new DataNotFoundException("")));

        $this->setUpController();

        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->entity = $this->getEntity($responseCreateStakeholder);
        $this->logResponseData('ERRORES RESULT EXCEPTION', $responseCreateStakeholder);
        $this->assertEquals("EXCEPTION", $responseCreateStakeholder["result"]);
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noMessageTemplateFound), false)
        );
        $this->assertTrue(
            array_reduce( $responseCreateStakeholder["message"], fn ($carry, $v) => $carry || str_contains($v, $this->noFoldersCreatedError), false)
        );

        // Create procedure stakeholder when main is not found
        $testEntity = $responseCreateStakeholder["entity"];
        $this->procedureStakeholderMapper = $this->createMock(ProcedureStakeholderMapper::class);
        $this->procedureStakeholderMapper->method('findMainByProcedure')
                   ->will($this->throwException(new EntityNotFoundException()));
        $this->procedureStakeholderMapper->method('find')
                   ->willReturn($testEntity);
        $this->setUpController();

        $responseCreateStakeholder =  $this->update($this->controller, $this->entity);
        $this->logResponseData('ERROR findMainByProcedure ', $responseCreateStakeholder);
        $this->assertEquals("EXCEPTION", $responseCreateStakeholder["result"]);

        $testEntity->setIsMain(true);
        $testEntity->setDeckCardId(1);
        $this->procedureStakeholderMapper = $this->createMock(ProcedureStakeholderMapper::class);
        $this->procedureStakeholderMapper->method('findMainByProcedure')
                   ->willReturn($testEntity);

        $this->procedureStakeholderMapper->method('find')
                   ->willReturn( $testEntity);

        $this->setUpController();

        $responseCreateStakeholder =  $this->delete($this->controller, $testEntity->id);
        $this->assertEquals("OK", $responseCreateStakeholder["result"]);

        // Create procedure stakeholder assigned to a procedure without jitsi room code
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["notifyStakeholder"] = true;
        $procedureTypeTestEntity["allowUpload"] = true;
        $procedureTypeTestEntity["allowDownload"] = true;
        $procedureTypeTestEntity["allowVideo"] = true;

        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        
        $this->procedureTestEntity->setJitsiRoomCode("");

        $this->procedureMapper = $this->createMock(ProcedureMapper::class);
        $this->procedureMapper->method('find')->willReturn($this->procedureTestEntity);
        $this->setUpController();
        $initialData = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);

        $responseCreateStakeholder =  $this->create($this->controller, $initialData);
        $this->logResponseData('NO JITSI ROOM CODE', $responseCreateStakeholder);
        $this->assertEquals("EXCEPTION", $responseCreateStakeholder["result"]);
        $this->assertEquals($this->noJitsiRoomCodeError, $responseCreateStakeholder["message"]);

    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {

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

        $this->controller = new ProcedureStakeholderController(
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
        $messageTemplateTestEntity = TestData::$messageTemplateEntityData;
        $messageTemplateTestEntity["channel"] = "email";
        $responseMesageTemplate = $this->create($this->messageTemplateController, $messageTemplateTestEntity);

        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["notifyStakeholder"] = true;
        $procedureTypeTestEntity["notifyStaff"] = true;
        $procedureTypeTestEntity["requireStakeholderEmail"] = true;
        $procedureTypeTestEntity["requireStakeholderPhone"] = true;
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
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);


        // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $responseProcedureType["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStakeholderTemplateId"] = $responseMailTemplate["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStaffTemplateId"] = $responseMailTemplate["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStakeholderTemplate"] = $responseMesageTemplate["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStaffTemplate"] = $responseMesageTemplate["entity"]->id;
        $procedureTypeGroupTestEntity["allowAssignUser"] = true;
        $procedureTypeGroupTestEntity["bypassRequired"] = false;

        $responseProcedureTypeGroup = $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);
        $this->procedureTypeGroup = $responseProcedureTypeGroup["entity"];
        // Save procedure type group response
        //$this->entity = $this->getEntity($responseProcedureTypeGroup);
        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $procedureTestEntity["assignedUserId"] = "TEST";
        $responseProcedure = $this->create($this->procedureController, $procedureTestEntity);
        $this->procedureTestEntity = $responseProcedure["entity"];
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
