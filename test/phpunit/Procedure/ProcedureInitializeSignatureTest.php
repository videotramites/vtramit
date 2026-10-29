<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Procedure;

use OCP\IURLGenerator;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\Files\File;

use OCA\VTramit\Exception\DataNotFoundException;

use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\AttendanceChannel;

use OCA\VTramit\Db\MailQueueMapper;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\MessageQueueMapper;
use OCA\VTramit\Db\MessageTemplateMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureType;
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

use OCA\VTramit\Helper\CurlHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Helper\JwtHelper;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * Tests for Initializate Signature Test (PU025)
 */
class ProcedureInitializeSignatureTest extends CommonTestOperations
{

    private string $testFunction = "testInitializateProcedure";
    private string $expectedResponseMessage = "Signature process has been initialized successfully";
    private string $errorSendingMessage = "An error occurred while sending the message to the user.";
    private string $errorInizializingSignatureMessage = "Could not initialize the signature in the signing system";
    private string $notEnoughFilesError = "Not enough files to start the signature process or files are already being processed";
    private string $expectedResponseMessageError = "Signature system is disabled or you don't have permissions; please contact the system administrator";
    private string $simulatedErrorMessage = "ERROR TEST";

    private string $testFileName = "test_file.txt";

    /** @var ProcedureType */
    private ProcedureType $procedureType;

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
        $testName = 'procedure-inizialite-signature';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure');

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

        $filepath = '/vtramit/proc/123/uploads/firma';
        $this->rootFolder = $this->createMock(IRootFolder::class);

        // Mock IRootFolder behavior
        $this->rootFolder->method('nodeExists')->willReturn(true);

        $folderMock = $this->createMock(Folder::class);

        $fileMock1 = $this->createMock(File::class);
        $fileMock1->method('getExtension')->willReturn('pdf');
        $fileMock2 = $this->createMock(File::class);
        $fileMock2->method('getExtension')->willReturn('docx');

        $folderMock->method('getDirectoryListing')->willReturn([$fileMock1,$fileMock1]);

        $this->rootFolder->method('get')->willReturn($folderMock);

        $this->urlGenerator = $this->createMock(IURLGenerator::class);

        $this->curlHelper = $this->createMock(CurlHelper::class);
        $this->curlHelper->method('request')
        ->willReturn((object)[
                'Ok' => true,
                'UserUrl' => 'https://example.com/signature/12345'
        ]);

        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->deckHelper = $this->createMock(DeckHelper::class);
        $this->nextCloudHelper = $this->createMock(NextcloudHelper::class);

        // Mock listing of folders in file in NextcloudHelper
        $file1 = [
            'name' => $this->testFileName,
            'path' => 'path/to/test/file',
            'size' => 999,
            'mtime' => 99999,
            'mimetype' => 'docx',
        ];
        $file2 = [
            'name' => $this->testFileName,
            'path' => 'path/to/test/file',
            'size' => 999,
            'mtime' => 99999,
            'mimetype' => 'docx',
        ];
        $listFiles = [$file1, $file2];
        $this->nextCloudHelper->method('listFilesInFolder')->willReturn($listFiles);
        
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

    }


    /**
     * 
     * Steps:
     *
     *  1.- Initialize signature normally
     *  2.- Initialize signature simulatig it return error string
     *  3.- Initialize signature allowing message/mail in signature button without allowing notify stakeholder
     *  4.- Initialize signature allowing message/mail in signature button allowing notify stakeholder
     *  5.- Initialize signature allowing message/mail in signature button but without indicating procedure stakeholder email
     *  6.- Initialize signature mocking curlHelper request() method returns Ok = false
     *  7.- Initialize signature mocking curlHelper request() method returns Ok = true
     *  8.- Initialize signature mocking rootFolder nodeExists() method returns false
     * 
     */
    public function testInitializateProcedure(): void {

        $this->createTestRequiredData();

        $procedureId = $this->createdEntities[get_class($this->controller)][0];
        $signatureTypeTestId = $this->createdEntities[get_class($this->signatureTypeController)][0];
        $procedureTypeTestId = $this->createdEntities[get_class($this->procedureTypeController)][0];
        $procedureTypeGroupTestId = $this->createdEntities[get_class($this->procedureTypeGroupController)][0];
        $signatureButtonId = $this->createdEntities[get_class($this->signatureButtonController)][0];
        $procedureStakeholderId = $this->createdEntities[get_class($this->procedureStakeholderController)][0];

        // Initialize signature normally
        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeSignature);

        $this->assertEquals("OK", $responseInitializeSignature["result"]);
        $this->assertEquals($this->expectedResponseMessage, $responseInitializeSignature["message"]);
        $this->checkEntityData($this->entity, $this->getEntity($responseInitializeSignature));

        // Initialize signature simulatig it return error string
        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureService->method('createSignatureForProcedure')->willReturn($this->simulatedErrorMessage);

        $this->setUpController();

        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE ERROR', $responseInitializeSignature);

        $this->assertEquals("ERROR", $responseInitializeSignature["result"]);
        $this->assertEquals($this->simulatedErrorMessage, $responseInitializeSignature["message"]);
        $this->checkEntityData($this->entity, $this->getEntity($responseInitializeSignature));

        // Initialize signature allowing message/mail in signature button without allowing notify stakeholder
        $this->signatureFileMapper = $this->createMock(SignatureFileMapper::class);
        $this->signatureFileMapper->method("findBySignature")->willReturn([]);
        $this->setUpSignatureService();

        $signatureButtonTest = $this->setAssociatedIdsToSignatureButtons(TestData::$signatureButtonTestEntityData, $procedureTypeTestId, $signatureTypeTestId,  $procedureTypeGroupTestId);
        $signatureButtonTest["id"] = $signatureButtonId;
        $signatureButtonTest["mailEnabled"] = true;
        $signatureButtonTest["messageEnabled"] = true;
        $signatureButtonTest["messageTemplateId"] = 0;
        $responseSignatureButton = $this->update($this->signatureButtonController, $signatureButtonTest);

        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeSignature);

        $this->assertEquals("OK", $responseInitializeSignature["result"]);
        $this->assertEquals($this->expectedResponseMessage, $responseInitializeSignature["message"]);
        $this->checkEntityData($this->entity, $this->getEntity($responseInitializeSignature));
        
        // Initialize signature allowing message/mail in signature button allowing notify stakeholder
        $signatureButtonTest = $this->setAssociatedIdsToSignatureButtons(TestData::$signatureButtonTestEntityData, $procedureTypeTestId, $signatureTypeTestId,  $procedureTypeGroupTestId);
        $signatureButtonTest["id"] = $signatureButtonId;
        $signatureButtonTest["mailEnabled"] = true;
        $signatureButtonTest["messageEnabled"] = true;
        $responseSignatureButton = $this->update($this->signatureButtonController, $signatureButtonTest);

        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] = $procedureTypeTestId;
        $procedureTypeTestEntity["allowSignature"] = true;
        $procedureTypeTestEntity["allowUpload"] = true;
        $procedureTypeTestEntity["allowDownload"] = true;
        $procedureTypeTestEntity["notifyStakeholder"] = true;

        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeSignature);
        
        $this->assertEquals("ERROR", $responseInitializeSignature["result"]);
        $this->assertEquals($this->errorSendingMessage, $responseInitializeSignature["message"]);
        $this->checkEntityData($this->entity, $this->getEntity($responseInitializeSignature));

        // Initialize signature allowing message/mail in signature button but without indicating procedure stakeholder email
        $procedureStakeholderTestEntity = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);
        $procedureStakeholderTestEntity["id"] = $procedureStakeholderId;
        $procedureStakeholderTestEntity["email"] = "";

        $responseProcedureStakeholder = $this->update($this->procedureStakeholderController, $procedureStakeholderTestEntity);

        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeSignature);

        $this->assertEquals("ERROR", $responseInitializeSignature["result"]);
        $this->assertEquals($this->errorSendingMessage, $responseInitializeSignature["message"]);
        $this->checkEntityData($this->entity, $this->getEntity($responseInitializeSignature));

        // Restore procedure stakeholder
        $procedureStakeholderTestEntity = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $procedureId);
        $procedureStakeholderTestEntity["id"] = $procedureStakeholderId;
        $procedureStakeholderTestEntity["email"] = $this->testName;
        $procedureStakeholderTestEntity["isMain"] = true;

        $responseProcedureStakeholder = $this->update($this->procedureStakeholderController, $procedureStakeholderTestEntity);

        // Initialize signature mocking curlHelper request() method returns Ok = false
        $this->curlHelper = $this->createMock(CurlHelper::class);
        $this->curlHelper->method('request')
        ->willReturn((object)[
            'Ok' => false,
            'UserUrl' => 'https://example.com/signature/12345'
        ]);

        $this->setUpSignatureService();

        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeSignature);

        $this->assertEquals("ERROR", $responseInitializeSignature["result"]);
        $this->assertEquals($this->errorInizializingSignatureMessage, $responseInitializeSignature["message"]);
        $this->checkEntityData($this->entity, $this->getEntity($responseInitializeSignature));

        // Initialize signature mocking curlHelper request() method returns Ok = true
        $this->curlHelper = $this->createMock(CurlHelper::class);
        $this->curlHelper->method('request')->willReturn((object)[
            'Ok' => true
        ]);
        $this->setUpSignatureService();

        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeSignature);

        $this->assertEquals("ERROR", $responseInitializeSignature["result"]);
        $this->assertEquals($this->errorInizializingSignatureMessage, $responseInitializeSignature["message"]);
        $this->checkEntityData($this->entity, $this->getEntity($responseInitializeSignature));

        // Initialize signature mocking rootFolder nodeExists() method returns false
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->rootFolder->method('nodeExists')->willReturn(false);
        $this->setUpSignatureService();

        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeSignature);

        $this->assertEquals("ERROR", $responseInitializeSignature["result"]);
        $this->assertEquals($this->notEnoughFilesError, $responseInitializeSignature["message"]);
        $this->checkEntityData($this->entity, $this->getEntity($responseInitializeSignature));

    }

    protected function setUpSignatureService(): void {

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
        $this->setUpController();
    }
    /**
     * Set up the controller to test
     */
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


        // Add rgpd options to procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["allowSignature"] = true;
        $procedureTypeTestEntity["allowUpload"] = true;
        $procedureTypeTestEntity["allowDownload"] = true;

        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);
        $this->procedureType= $responseProcedureType["entity"];
        // Add rgpd options to procedure type
        $signatureTypeTestEntity = TestData::$signatureTypeTestEntityData;
        $signatureTypeTestEntity["filesPerRequest"] = -1;
        // Save signature type response
        $responseSignatureType = $this->create($this->signatureTypeController, $signatureTypeTestEntity);


        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $procedureTestEntity["assignedGroupId"] = "TEST";
        $responseProcedure = $this->create($this->controller, $procedureTestEntity);

        $this->entity = $this->getEntity($responseProcedure);


        // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $responseProcedureType["entity"]->id;

        $responseProcedureTypeGroup = $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);

        $signatureButtonTest = $this->setAssociatedIdsToSignatureButtons(TestData::$signatureButtonTestEntityData, $responseProcedureType["entity"]->id, $responseSignatureType["entity"]->id,  $procedureTypeGroupTestEntity["group"]);

        $responseSignatureButton = $this->create($this->signatureButtonController, $signatureButtonTest);

        // Create procedure stakeholder
        $procedureStakeholderTestEntity = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $responseProcedure["entity"]->id);
        $procedureStakeholderTestEntity["email"] = $this->testEmail;
        $procedureStakeholderTestEntity["isMain"] = true;

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
