<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Procedure;

use OCP\IRequest;
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
 * Tests for Initializate Signature Test (PU031)
 */
class ProcedureGetFilesTest extends CommonTestOperations
{

    private string $testFunction = "testGetFiles";
    private string $expectedResponseMessage = "Signature system is disabled or you don't have permissions; please contact the system administrator";
    private string $expectedResponseMessageUploadsFolder = "Uploads folder is not available for this procedure.";
    private string $expectedResponseMessageDownloadsFolder = "Downloads folder is not available for this procedure.";
    private string $expectedResponseMessageSignatureUploadsFolder = "Signature uploads folder is not available for this procedure.";
    private string $expectedResponseMessageSignatureDownloadsFolder = "Signature downloads folder is not available for this procedure.";

    private array $file1 = [
        'name' => 'test_file.docx',
        'path' => 'path/to/test/file',
        'size' => 999,
        'mtime' => 99999,
        'mimetype' => 'docx',
    ];
    
    private array $file2 = [
        'name' => 'test_file.pdf',
        'path' => 'path/to/test/file',
        'size' => 999,
        'mtime' => 99999,
        'mimetype' => 'pdf',
    ];
    private array $listFiles = [];

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

        $this->listFiles = [$this->file1, $this->file2];
        // Mock listing of folders in file in NextcloudHelper
        $this->nextCloudHelper->method('listFilesInFolder')->willReturn($this->listFiles);
        $this->nextCloudHelper->method('saveDataUrl')->willReturn($this->file1["name"]);

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
     *  2.- Initialize signature normally and cancel it
     *  3.- Get uploads folder files without indicating 'filename'
     *  4.- Get downloads folder files without indicating 'filename'
     *  5.- Get signature uploads folder files without indicating 'filename'
     *  6.- Get signature downloads folder files without indicating 'filename'
     *  7.- Upload uploads folder
     *  8.- Upload to downloads folder
     *  9.- Force getParam to return test_filename in case it is requested
     *  10.- Get uploads folder files indicating 'filename' without existing files
     *  11.- Get downloads folder files indicating 'filename' without existing files
     *  12.- Get signature uploads folder files indicating 'filename' without existing files
     *  13.- Get signature downloads folder files indicating 'filename' without existing files
     *  14.- Mock creation of upload and download folder and files
     *  15.- Get uploads folder files indicating 'filename' with existing files
     *  16.- Get downloads folder files indicating 'filename' with existing files
     *  17.- Get signature uploads folder files indicating 'filename' with existing files
     *  18.- Get signature downloads folder files indicating 'filename' with existing files
     *  19.- Get uploads folder files without allowing uploads in assigned procedure type
     *  20.- Get downloads folder files without allowing downloads in assigned procedure type
     *  21.- Get signature uploads folder without allowing signature in assigned procedure type
     *  22.- Get signature downloads without allowing signature in assigned procedure type
     *  23.- Get uploads folder files without assigning uploads folder
     *  24.- Get downloads folder files without assigning downloads folder
     *  25.- Get signature uploads folder without assigning signature uploads folder
     *  26.- Get signature downloads without assigning signature downloads folder
     *  27.- Get uploads folder files without allowing uploads in assigned procedure type and without indicating a filename
     *  28.- Get downloads folder files without allowing downloads in assigned procedure type and without indicating a filename
     *  29.- Get signature uploads folder without allowing signature in assigned procedure type and without indicating a filename
     *  30.- Get signature downloads without allowing signature in assigned procedure type and without indicating a filename
     *  31.- Get uploads folder files without assigning uploads folder and without indicating a filename and without indicating a filename
     *  32.- Get downloads folder files without assigning downloads folder and without indicating a filename and without indicating a filename
     *  33.- Get signature uploads folder without assigning signature uploads folder and without indicating a filename and without indicating a filename
     *  34.- Get signature downloads without assigning signature downloads folder and without indicating a filename and without indicating a filename
     * 
     */
    public function testGetFiles(): void {

        $this->createTestRequiredData();

        $procedureId = $this->createdEntities[get_class($this->controller)][0];
        $signatureTypeTestId = $this->createdEntities[get_class($this->signatureTypeController)][0];
        $procedureTypeTestId = $this->createdEntities[get_class($this->procedureTypeController)][0];
        $procedureTypeGroupTestId = $this->createdEntities[get_class($this->procedureTypeGroupController)][0];
        $signatureButtonId = $this->createdEntities[get_class($this->signatureButtonController)][0];
        $procedureStakeholderId = $this->createdEntities[get_class($this->procedureStakeholderController)][0];

        // Initialize signature normally and cancel it
        $responseInitializeCancelSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeCancelSignature);

        $findSignature = $this->signatureService->findByProcedure($responseInitializeCancelSignature["entity"]);

        $cancel = $this->signatureService->cancelSignature($findSignature[0]);

        // Initialize signature normally
        $responseInitializeSignature = $this->controller->initializeSignature($procedureId, $signatureButtonId)->getData();
        $this->logResponseData('START SIGNATURE', $responseInitializeSignature);

        // Get uploads folder files without indicating 'filename'
        $response = $this->controller->getUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->listFiles, $response["list"]);
        // Get downloads folder files without indicating 'filename'
        $response = $this->controller->getDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->listFiles, $response["list"]);
        // Get signature uploads folder files without indicating 'filename'
        $response = $this->controller->getSignatureUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->checkEntityData($this->file1, $response["list"][0]);
        $this->checkEntityData($this->file2, $response["list"][1]);

        // Get signature downloads folder files without indicating 'filename'
        $response = $this->controller->getSignatureDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->checkEntityData($this->file1, $response["list"][0]);
        $this->checkEntityData($this->file2, $response["list"][1]);

        // Upload uploads folder
        $response = $this->controller->uploadToUploadsFolder($responseInitializeSignature["entity"]->id, "test_upload", "test upload data")->getData();
        $this->assertEquals($this->file1["name"], $response["file"]);

        // Upload to downloads folder
        $response = $this->controller->uploadToDownloadsFolder($responseInitializeSignature["entity"]->id, "test_download", "test download data")->getData();
        $this->assertEquals($this->file1["name"], $response["file"]);

        // Force getParam to return test_filename in case it is requested
        $this->request->expects($this->any())->method('getParam')->willReturnCallback(function ($paramName) {
            if ($paramName === "filename") {
                return $this->file1["name"];
            } else {
                return "";
            }
        });

        $this->setUpController();

        // Get uploads folder files indicating 'filename' without existing files in folder
        $response = $this->controller->getUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertStringStartsWith(
            'File not found or not readable:',
            $response["message"]
        );
        // Get downloads folder files indicating 'filename' without existing files in folder
        $response = $this->controller->getDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertStringStartsWith(
            'File not found or not readable:',
            $response["message"]
        );
        // Get signature uploads folder files indicating 'filename' without existing files in folder
        $response = $this->controller->getSignatureUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertStringStartsWith(
            'File not found or not readable:',
            $response["message"]
        );
        // Get signature downloads folder files indicating 'filename' without existing files in folder
        $response = $this->controller->getSignatureDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertStringStartsWith(
            'File not found or not readable:',
            $response["message"]
        );

        // Mock creation of upload and download folder and files
        $relativeUploadFolder = $this->procedureFileService->getLocalPathToFolder($responseInitializeSignature["entity"], $this->procedureType->getUploadsFolder(), $this->userId);
        exec("mkdir -p " . "/" . $relativeUploadFolder . "/");
        exec("touch " . "/" . $relativeUploadFolder . "/" . basename($this->file1["name"]));

        $relativeDownloadFolder = $this->procedureFileService->getLocalPathToFolder($responseInitializeSignature["entity"], $this->procedureType->getDownloadsFolder(), $this->userId);
        exec("mkdir -p " . "/" . $relativeDownloadFolder . "/");
        exec("touch " . "/" . $relativeDownloadFolder . "/" . basename($this->file1["name"]));

        $relativeUploadSignatureFolder = $this->procedureFileService->getLocalPathToFolder($responseInitializeSignature["entity"], $this->procedureType->getSignatureUploadsFolder(), $this->userId);

        exec("mkdir -p " . "/" . $relativeUploadSignatureFolder . "/");
        exec("touch " . "/" . $relativeUploadSignatureFolder . "/" . basename($this->file1["name"]));

        $relativeDownloadSignatureFolder = $this->procedureFileService->getLocalPathToFolder($responseInitializeSignature["entity"], $this->procedureType->getSignatureDownloadsFolder(), $this->userId);

        exec("mkdir -p " . "/" . $relativeDownloadSignatureFolder . "/");
        exec("touch " . "/" . $relativeDownloadSignatureFolder . "/" . basename($this->file1["name"]));

        // Get uploads folder files indicating 'filename' with existing files
        $response = $this->controller->getUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->file1["name"], $response["filename"]);
        // Get downloads folder files indicating 'filename' with existing files
        $response = $this->controller->getDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->file1["name"], $response["filename"]);
        // Get signature uploads folder files indicating 'filename' with existing files
        $response = $this->controller->getSignatureUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->file1["name"], $response["filename"]);
        // Get signature downloads folder files indicating 'filename' with existing files
        $response = $this->controller->getSignatureDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->file1["name"], $response["filename"]);
        
        // Clean created folders
        exec("rm -rf " . "/" . $relativeUploadFolder . "/" . basename($this->file1["name"]));

        // Edit procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeTestId;

        $procedureTypeTestEntity["allowSignature"] = false;
        $procedureTypeTestEntity["allowUpload"] = false;
        $procedureTypeTestEntity["allowDownload"] = false;

        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        // Get uploads folder files without allowing uploads in assigned procedure type
        $response = $this->controller->getUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->expectedResponseMessageUploadsFolder, $response["message"]);
        // Get downloads folder files without allowing downloads in assigned procedure type
        $response = $this->controller->getDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->expectedResponseMessageDownloadsFolder, $response["message"]);
        // Get signature uploads folder without allowing signature in assigned procedure type
        $response = $this->controller->getSignatureUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->expectedResponseMessageSignatureUploadsFolder, $response["message"]);
        // Get signature downloads without allowing signature in assigned procedure type
        $response = $this->controller->getSignatureDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->expectedResponseMessageSignatureDownloadsFolder, $response["message"]);

        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeTestId;

        // Edit procedure type
        $procedureTypeTestEntity["allowSignature"] = true;
        $procedureTypeTestEntity["allowUpload"] = true;
        $procedureTypeTestEntity["allowDownload"] = true;

        $procedureTypeTestEntity["uploadsFolder"] = "";
        $procedureTypeTestEntity["downloadsFolder"] = "";
        $procedureTypeTestEntity["signatureUploadsFolder"] = "";
        $procedureTypeTestEntity["signatureDownloadsFolder"] = "";

        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        // Get uploads folder files without assigning uploads folder
        $response = $this->controller->getUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->expectedResponseMessageUploadsFolder, $response["message"]);
        // Get downloads folder files without assigning downloads folder
        $response = $this->controller->getDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->expectedResponseMessageDownloadsFolder, $response["message"]);
        // Get signature uploads folder without assigning signature uploads folder
        $response = $this->controller->getSignatureUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->expectedResponseMessageSignatureUploadsFolder, $response["message"]);
        // Get signature downloads without assigning signature downloads folder
        $response = $this->controller->getSignatureDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        $this->assertEquals($this->expectedResponseMessageSignatureDownloadsFolder, $response["message"]);

        $this->request = $this->createMock(IRequest::class);
        $this->setUpController();

        // Edit procedure type
        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeTestId;

        $procedureTypeTestEntity["allowSignature"] = false;
        $procedureTypeTestEntity["allowUpload"] = false;
        $procedureTypeTestEntity["allowDownload"] = false;

        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        // Get uploads folder files without allowing uploads in assigned procedure type and without indicating a filename
        $response = $this->controller->getUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        // Get downloads folder files without allowing downloads in assigned procedure type and without indicating a filename
        $response = $this->controller->getDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        // Get signature uploads folder without allowing signature in assigned procedure type and without indicating a filename
        $response = $this->controller->getSignatureUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        // Get signature downloads without allowing signature in assigned procedure type and without indicating a filename
        $response = $this->controller->getSignatureDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();

        $procedureTypeTestEntity = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntity["id"] =  $procedureTypeTestId;

        // Edit procedure type
        $procedureTypeTestEntity["allowSignature"] = true;
        $procedureTypeTestEntity["allowUpload"] = true;
        $procedureTypeTestEntity["allowDownload"] = true;

        $procedureTypeTestEntity["uploadsFolder"] = "";
        $procedureTypeTestEntity["downloadsFolder"] = "";
        $procedureTypeTestEntity["signatureUploadsFolder"] = "";
        $procedureTypeTestEntity["signatureDownloadsFolder"] = "";

        // Save procedure type
        $responseProcedureType = $this->update($this->procedureTypeController, $procedureTypeTestEntity);

        // Get uploads folder files without assigning uploads folder and without indicating a filename and without indicating a filename
        $response = $this->controller->getUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        // Get downloads folder files without assigning downloads folder and without indicating a filename and without indicating a filename
        $response = $this->controller->getDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        // Get signature uploads folder without assigning signature uploads folder and without indicating a filename and without indicating a filename
        $response = $this->controller->getSignatureUploadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();
        // Get signature downloads without assigning signature downloads folder and without indicating a filename and without indicating a filename
        $response = $this->controller->getSignatureDownloadsFolderFiles($responseInitializeSignature["entity"]->id)->getData();

        // Find file signatures By Procedure
        $responseInitializeCancelSignature = $this->signatureService->findFileSignaturesByProcedure($responseInitializeSignature["entity"]->id);
        $this->logResponseData('START SIGNATURE', $responseInitializeCancelSignature);
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
            $expectedData['name'],
            $actualData['name'],
            'Name mismatch'
        );

        $this->assertEquals(
            $expectedData['path'],
            $actualData['path'],
            'Path mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['size']),
            json_encode($actualData['size']),
            'Size mismatch'
        );

        
        $this->assertEquals(
            json_encode($expectedData['mtime']),
            json_encode($actualData['mtime']),
            'Modification time mismatch'
        );

        
        $this->assertEquals(
            json_encode($expectedData['mimetype']),
            json_encode($actualData['mimetype']),
            'Mimetype mismatch'
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
        $this->procedureType = $responseProcedureType["entity"];
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
