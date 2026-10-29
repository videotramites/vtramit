<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Procedure;

use OCA\VTramit\Service\ConfigService;
use OCP\IURLGenerator;
use OCP\Files\IRootFolder;

use OCA\VTramit\Exception\DataNotFoundException;
use OCA\VTramit\Exception\EntityNotFoundException;

use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\AttendanceChannel;

use OCA\VTramit\Db\MailQueueMapper;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\MessageQueue;
use OCA\VTramit\Db\MessageQueueMapper;
use OCA\VTramit\Db\MessageTemplateMapper;
use OCA\VTramit\Db\ProcedureMapper;
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
 * (PU027)
 */
class ProcedureSendMailAndMessagesTest extends CommonTestOperations
{

    private string $testFunction = "testSendEmailToStakeholder";
    private bool $configLoaded = false;
    private string $expectedResponseMessage = "Recorded message for sending";
    private string $expectedErrorOnStakeholderAdded = "Folders could not be created";
    private string $noEmailError = "There is no email to address the message";
    private string $noRecipientError = "There is no recipient to address the message";
    private string $noMailStakeholderTemplateFound = "There is no template defined to send the email";
    private string $noMessageTemplateFound = "There is no template defined to send the message";
    private string $noMailStaffTemplateFound = "There is no template defined for the staff to send the email";
    private string $noMessageStaffTemplateFound = "There is no template defined for the staff to send the message";

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
        $testName = 'procedure-send-mail-and-messages';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure');

        if (!$this->configLoaded) {
            $saved = null;
            $this->config->method('getAppValue')
                ->willReturnCallback(function ($app, $key, $default = '') use (&$saved) {
                    if ($key !== 'encryption_key') return $default;
                    static $first = true;
                    if ($first) { $first = false; return ''; }
                    return $saved ?? $default;
                });

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
        }
        $this->initializeConfigService();
        $this->configLoaded = true;

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
     *  1.- Try to send email to stakeholder when appointment date is changed and there is no assigned group id
     *  2.- Try to send emails
     *  3.- Try to send messages
     *  4.- Try to send email to stakeholder when procedure stakeholder is not found
     *  5.- Try to send email to stakeholder when status is not indicated
     *  6.-  Call a onStakeholderAdded simulating createFolders throws DataNotFoundException
     * 
     */
    public function testSendEmailToStakeholder(): void {

        $this->createTestRequiredData();

        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];

        // Try to send email to stakeholder when appointment date is changed and there is no assigned group id
        $appointmentDate = new \DateTime($this->entity["appointmentDate"]);
        $newAppointmentDate = TestHelpers::addDayToAppointmentDate($appointmentDate);
        $this->entity["appointmentDate"] = $newAppointmentDate;
        $this->entity["assignedGroupId"] = "";
        unset($this->entity["statusId"]);
        $responseProcedure = $this->update($this->controller, $this->entity);
        $this->entity = $this->getEntity($responseProcedure);
        $this->logResponseData('EMAIL SENT TO STAKEHOLDER ERROR', $responseProcedure);
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);

        // Try to send emails
        $this->procedureMailServiceTest($procedureTypeId);
        $this->__construct();
        $this->setUpController();

        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["notifyStakeholder"] = true;
        $procedureTypeTestEntity["notifyStaff"] = true;
        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        // Try to send messages
        $this->procedureMessageServiceTests($procedureTypeId);

        // Try to send email to stakeholder when procedure stakeholder is not found
        $this->procedureStakeholderMapper = $this->createMock(ProcedureStakeholderMapper::class);
        $this->procedureStakeholderMapper->method('findMainByProcedure')
        ->will($this->throwException(new EntityNotFoundException()));
        $this->procedureStakeholderMapper->method('findByProcedure')->willReturn([]);
        $this->procedureFileService = $this->createMock(ProcedureFileService::class);
        $this->procedureFileService->method('createFolders')->will($this->throwException(new DataNotFoundException()));

        $this->setUpController();
        
        $responseProcedureSendEmail = $this->procedureService->sendEmailToAllStakeholders($responseProcedure["entity"]->id)->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMailStakeholderTemplateFound,
            $responseProcedureSendEmail["message"]
        );
        
        // Try to send email to stakeholder when status is not indicated
        $appointmentDate = new \DateTime($this->entity["appointmentDate"]);
        $newAppointmentDate = TestHelpers::addDayToAppointmentDate($appointmentDate);
        $this->entity["appointmentDate"] = $newAppointmentDate;
        unset($this->entity["statusId"]);
        $responseProcedureError = $this->update($this->controller, $this->entity);
        $this->logResponseData('EMAIL SENT TO STAKEHOLDER ERROR', $responseProcedureError);

        // Call a onStakeholderAdded simulating createFolders throws DataNotFoundException
        $responseonStakeholderAdded = $this->procedureService->onStakeholderAdded(new  \OCA\VTramit\Db\Procedure(), new ProcedureStakeholder(), $this->userId)->getData();
        $this->logResponseData('onStakeholderAdded ERROR', $responseonStakeholderAdded);
        $this->assertEquals("ERROR", $responseonStakeholderAdded["result"]);

        $this->assertEquals($this->expectedErrorOnStakeholderAdded, $responseonStakeholderAdded["message"][0]);
    }
    private function procedureMessageServiceTests(int $procedureTypeId) {

        $this->procedureMessageServiceTestCalls($procedureTypeId, "email");

        $this->messageTemplateService = new MessageTemplateService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->messageTemplateMapper,
            $this->procedureTokenService
        );
        //$this->configService = $this->createMock(ConfigService::class);
        //$this->configService->method('getEncryptionKey')->willReturn("");
        //$this->configService->method('getTimezone')->willReturn("Europe/Berlin");
        //$this->configService->method('getDateTimezone')->willReturn(new \DateTimeZone("Europe/Berlin"));
        $this->setUpController();

        $messageTemplateId = $this->createdEntities[get_class($this->messageTemplateController)][0];
        $messageTemplateTestEntity = TestData::$messageTemplateEntityData;
        $messageTemplateTestEntity["id"] =  $messageTemplateId;
        $messageTemplateTestEntity["channel"] = "sms";
        $responseMesageTemplate = $this->update($this->messageTemplateController, $messageTemplateTestEntity);

        $this->procedureMessageServiceTestCalls($procedureTypeId, "sms");
        
        $this->messageTemplateService = new MessageTemplateService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->messageTemplateMapper,
            $this->procedureTokenService
        );
        $this->setUpController();

        $messageTemplateId = $this->createdEntities[get_class($this->messageTemplateController)][0];
        $messageTemplateTestEntity = TestData::$messageTemplateEntityData;
        $messageTemplateTestEntity["id"] =  $messageTemplateId;
        $messageTemplateTestEntity["channel"] = "command";
        $responseMesageTemplate = $this->update($this->messageTemplateController, $messageTemplateTestEntity);

        $this->procedureMessageServiceTestCalls($procedureTypeId, "command");
    }
    private function procedureMessageServiceTestCalls(int $procedureTypeId, string $recipientRequired) {
        
        // Enviar mail a todos los stakeholders al actualizar la fecha del procedure y indicando el assignedGroupId
        $appointmentDate = new \DateTime($this->entity["appointmentDate"]);
        $newAppointmentDate = TestHelpers::addDayToAppointmentDate($appointmentDate);
        $this->entity["appointmentDate"] = $newAppointmentDate;
        $this->entity["assignedGroupId"] = "TEST";
        $this->entity["assignedUserId"] = "TEST";
        unset($this->entity["statusId"]);
        $responseProcedure = $this->update($this->controller, $this->entity);
        
        if ($recipientRequired === "email" || $recipientRequired === "sms") {

            $phoneNumber = $this->procedureStakeholder->getPhone();
            $this->procedureStakeholder->setPhone("");
            $this->procedureStakeholder->setEmail("");
            $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
            $this->assertEquals(
                "ERROR",
                $responseProcedureSendEmail["result"]
            );
            $this->assertEquals(
                $this->noRecipientError,
                $responseProcedureSendEmail["message"]
            );
            $this->procedureStakeholder->setEmail($this->testEmail);
            $this->procedureStakeholder->setPhone($phoneNumber);
        }

        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToAllStakeholders($responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);
        $this->assertInstanceOf(MessageQueue::class, $responseProcedureSendEmail["entity"]);
        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);
        $this->assertInstanceOf(MessageQueue::class, $responseProcedureSendEmail["entity"]);

        if ($recipientRequired === "email" || $recipientRequired === "command") {
            $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
            $this->assertEquals("OK", $responseProcedureSendEmail["result"]);
            $this->assertInstanceOf(MessageQueue::class, $responseProcedureSendEmail["entity"]);
        } else {
            $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
            $this->assertEquals("ERROR", $responseProcedureSendEmail["result"]);
            $this->assertEquals($this->noRecipientError,$responseProcedureSendEmail["message"]);
        }

        $this->messageTemplateService = $this->createMock(MessageTemplateService::class);
        // Forzar que lance una excepción
        $this->messageTemplateService->method('findById')
            ->will($this->throwException(new EntityNotFoundException()));
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
        $this->setUpController();
        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToAllStakeholders($responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMessageTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMessageTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMessageStaffTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        // Crear un procedure con un procedure type sin jitsiStaffUrl indicado
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["notifyStakeholder"] = false;
        // Save procedure type
        $this->update($this->procedureTypeController, $procedureTypeTestEntity);
        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToAllStakeholders($responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);
        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);
        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);

        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->userGroupHelper->method('getUserEmail')->willReturn(null);

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

        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);

        // Enviar mail a todos los stakeholders al actualizar la fecha del procedure y indicando el assignedGroupId
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["notifyStakeholder"] = true;
        $procedureTypeTestEntity["notifyStaff"] = true;
        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);
        $this->entity["assignedGroupId"] = "";
        $this->entity["assignedUserId"] = "TEST";
        unset($this->entity["statusId"]);
        $responseProcedure = $this->update($this->controller, $this->entity);
        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToAllStakeholders($responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMessageTemplateFound,
            $responseProcedureSendEmail["message"]
        );
        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMessageTemplateFound,
            $responseProcedureSendEmail["message"]
        );
        $responseProcedureSendEmail = $this->procedureMessageService->sendMessagesToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMessageStaffTemplateFound,
            $responseProcedureSendEmail["message"]
        );
    }

    private function procedureMailServiceTest(int $procedureTypeId) {

        
        // Enviar mail a todos los stakeholders al actualizar la fecha del procedure y indicando el assignedGroupId
        $appointmentDate = new \DateTime($this->entity["appointmentDate"]);
        $newAppointmentDate = TestHelpers::addDayToAppointmentDate($appointmentDate);
        $this->entity["appointmentDate"] = $newAppointmentDate;
        $this->entity["assignedGroupId"] = "TEST";
        $this->entity["assignedUserId"] = "TEST";
        unset($this->entity["statusId"]);
        $responseProcedure = $this->update($this->controller, $this->entity);
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToAllStakeholders($responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "OK",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->expectedResponseMessage,
            $responseProcedureSendEmail["message"][0]
        );
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "OK",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->expectedResponseMessage,
            $responseProcedureSendEmail["message"]
        );
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals(
            "OK",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->expectedResponseMessage,
            $responseProcedureSendEmail["message"][0]
        );

        $this->procedureStakeholder->setEmail("");
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noEmailError,
            $responseProcedureSendEmail["message"]
        );
        $this->procedureStakeholder->setEmail($this->testEmail);
        $this->mailTemplateService = $this->createMock(MailTemplateService::class);
        // Forzar que lance una excepción
        $this->mailTemplateService->method('find')
            ->will($this->throwException(new EntityNotFoundException()));
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
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToAllStakeholders($responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMailStakeholderTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMailStakeholderTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMailStaffTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        $this->procedureTypeGroupService = $this->createMock(ProcedureTypeGroupService::class);
            // Forzar que lance una excepción
        $this->procedureTypeGroupService->method('findNotifyStakeholderTemplateId')
            ->will($this->throwException(new EntityNotFoundException()));
        $this->procedureTypeGroupService->method('findNotifyStaffTemplateId')
            ->will($this->throwException(new EntityNotFoundException()));

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

        
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMailStaffTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToAllStakeholders($responseProcedure["entity"])->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMailStakeholderTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStakeholder($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals(
            "ERROR",
            $responseProcedureSendEmail["result"]
        );
        $this->assertEquals(
            $this->noMailStakeholderTemplateFound,
            $responseProcedureSendEmail["message"]
        );

        $this->procedureStakeholderMapper = $this->createMock(ProcedureStakeholderMapper::class);
            // Forzar que lance una excepción
        $this->procedureStakeholderMapper->method('findMainByProcedure')
            ->will($this->throwException(new EntityNotFoundException()));

        $this->procedureFileService = $this->createMock(ProcedureFileService::class);
        $this->procedureFileService->method('createFolders')->will($this->throwException(new DataNotFoundException()));

        $this->setUpController();

        //  Enviar mail al stakeholder simunaldo que no se encuentra un main stakeholder
        $appointmentDate = new \DateTime($this->entity["appointmentDate"]);
        $newAppointmentDate = TestHelpers::addDayToAppointmentDate($appointmentDate);
        $this->entity["appointmentDate"] = $newAppointmentDate;
        unset($this->entity["statusId"]);
        $responseProcedureError = $this->update($this->controller, $this->entity);
        $this->logResponseData('EMAIL SENT TO STAKEHOLDER ERROR', $responseProcedureError);

        // Llamar a onStakeholderAdded simulando que createFolders lanza DataNotFoundException
        $responseonStakeholderAdded = $this->procedureService->onStakeholderAdded(new  \OCA\VTramit\Db\Procedure(), new ProcedureStakeholder(), $this->userId)->getData();
        $this->logResponseData('onStakeholderAdded ERROR', $responseonStakeholderAdded);
        $this->assertEquals("ERROR", $responseonStakeholderAdded["result"]);

        $this->assertEquals($this->expectedErrorOnStakeholderAdded, $responseonStakeholderAdded["message"][0]);

        // Crear un procedure con un procedure type sin jitsiStaffUrl indicado
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeId;
        $procedureTypeTestEntity["notifyStakeholder"] = false;
        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToAllStakeholders($responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStakeholder($this->procedureStakeholder, $responseProcedure["entity"], $this->signatureButton)->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);

        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->userGroupHelper->method('getUserEmail')->willReturn(null);
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
        $responseProcedureSendEmail = $this->procedureMailService->sendEmailToStaff($this->procedureStakeholder, $responseProcedure["entity"])->getData();
        $this->assertEquals("OK", $responseProcedureSendEmail["result"]);

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
     * @param array $expectedData Expected rgpd option data
     * @param array $actualData Actual rgpd option data from response
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

        $messageTemplateTestEntity = TestData::$messageTemplateEntityData;
        $messageTemplateTestEntity["channel"] = "email";
        $responseMesageTemplate = $this->create($this->messageTemplateController, $messageTemplateTestEntity);

        $responseMailTemplate = $this->create($this->mailTemplateController, TestData::$mailTemplateEntityData);
        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["notifyStakeholder"] = true;
        $procedureTypeTestEntity["notifyStaff"] = true;

        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);

        // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $responseProcedureType["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStakeholderTemplateId"] = $responseMailTemplate["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStaffTemplateId"] = $responseMailTemplate["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStakeholderTemplate"] = $responseMesageTemplate["entity"]->id;
        $procedureTypeGroupTestEntity["notifyStaffTemplate"] = $responseMesageTemplate["entity"]->id;

        $responseProcedureTypeGroup = $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);

        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $responseProcedure = $this->create($this->controller, $procedureTestEntity);

        $this->entity = $this->getEntity($responseProcedure);
        // Create procedure stakeholder
        $procedureStakeholderTestEntity = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $responseProcedure["entity"]->id);
        $procedureStakeholderTestEntity["email"] = $this->testEmail;
        $responseProcedureStakeholder = $this->create($this->procedureStakeholderController, $procedureStakeholderTestEntity);
        $this->procedureStakeholder = $responseProcedureStakeholder["entity"];

        // Save signature type response
        $responseSignatureType = $this->create($this->signatureTypeController, TestData::$signatureTypeTestEntityData);

        // Set main entity
        $signatureButtonTestEntity = $this->setAssociatedIdsToSignatureButtons(TestData::$signatureButtonTestEntityData, $responseProcedureType["entity"]->id, $responseSignatureType["entity"]->id, $responseProcedureTypeGroup["entity"]->getGroup());
        $signatureButtonTestEntity["mailTemplateId"] = $responseMailTemplate["entity"]->id;
        $signatureButtonTestEntity["messageTemplateId"] = $responseMesageTemplate["entity"]->id;
        $signatureButtonTestEntity["mailEnabled"] = true;
        $signatureButtonTestEntity["messageEnabled"] = true;
        $responseSignatureButton = $this->create($this->signatureButtonController, $signatureButtonTestEntity);

        $this->signatureButton = $responseSignatureButton["entity"];
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
            case 'OCA\VTramit\Controller\MailTemplateController':
                return $this->mailTemplateController;
            case 'OCA\VTramit\Controller\ProcedureStakeholderController':
                return $this->procedureStakeholderController;
            case 'OCA\VTramit\Controller\ProcedureTypeGroupController':
                return $this->procedureTypeGroupController;
            case 'OCA\VTramit\Controller\SignatureTypeController':
                return $this->signatureTypeController;
            case 'OCA\VTramit\Controller\SignatureButtonController':
                return $this->signatureButtonController;
            default:
                return $this->controller;
        }
    }
}
