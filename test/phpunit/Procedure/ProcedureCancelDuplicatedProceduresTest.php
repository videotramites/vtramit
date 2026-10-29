<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Procedure;

use OCP\IURLGenerator;
use OCP\Files\IRootFolder;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\VTramit\ProcedureStatus;

use OCA\VTramit\Db\MailQueueMapper;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\MessageQueueMapper;
use OCA\VTramit\Db\MessageTemplateMapper;
use OCA\VTramit\Db\ProcedureMapper;
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
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureStakeholderController;

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
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * (PU019)
 */
class ProcedureCancelDuplicatedProceduresTest extends CommonTestOperations
{

    private string $testFunction = "testCancelDuplicatedProcedures";

    private string $expectedMessageError = "The procdure cannot be created because a finished procedure with same external ID is awaiting to be completed";

    private string $isProcedureWaitingForSignatureExpectedMessageError = "The procedure cannot be cancelled because is waiting for signatures";

    private string $procedureHasSignatureWaitingForDownloadExpectedMessageError = "The procedure cannot be cancelled because it has signatures waiting for download";

    private array $initalData = [];
    private array $updateData = [];

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

    /** @var SignatureButtonMapper Mapper for signature button database operations */
    private SignatureButtonMapper $signatureButtonMapper;

    /** @var SignatureButtonService */
    private SignatureButtonService $signatureButtonService;

    /** @var SignatureFileMapper */
    private SignatureFileMapper $signatureFileMapper;

    /** @var SignatureMapper */
    private SignatureMapper $signatureMapper;

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
        $testName = 'procedure-cancel-duplicated';
        $this->setEntityTypeName('procedure');
        parent::__construct($testName, $this->testFunction);

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

    }


    /**
     * 
     * Steps:
     *
     *  1.- Create Procedure with another exisiting Procedure with the same external ID
     *  2.- Create Procedure with another exisiting Procedure with the same external ID and status FINISHED
     *  3.- Assign EXPIRED status to procedure
     *  4.- Cancel procedure with a signature pending
     *  5.- Cancel procedure with a signature pending of download
     *  6.- Cancel procedure when deletePath in NextcloudHelper throws DoesNotExistException
     * 
     */
    public function testCancelDuplicatedProcedures(): void {

        $this->createTestRequiredData();

        unset($this->entity["id"]);

        // Create Procedure with another exisiting Procedure with the same 
        $duplicatedProceduresResponseOnCreate = $this->create($this->controller, $this->initalData);
        // Save endpoint output into log file
        $this->logResponseData("CANCEL DUPLICATED {$this->entityTypeName} ON CREATE", $duplicatedProceduresResponseOnCreate);

        // Verify data
        $this->assertEquals('OK', $duplicatedProceduresResponseOnCreate['result']);
        $responseEntity = $this->getEntity($duplicatedProceduresResponseOnCreate);
        $entityList = $this->getEntityList($duplicatedProceduresResponseOnCreate);
        $this->checkEntityData($responseEntity, $entityList[0]);
        
        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];
        $newInitalData = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
    
        // Create procedure and set it FINISHED status
        $responseProcedure = $this->create($this->controller, $newInitalData);
        $this->entity = $this->getEntity($responseProcedure);
        $this->entity["statusId"] = ProcedureStatus::PENDANT;
        $responseProcedure = $this->update($this->controller, $this->entity);
        $this->entity["statusId"] = ProcedureStatus::ON_COURSE;
        $responseProcedure = $this->update($this->controller, $this->entity);
        // Create procedure stakeholder
        $procedureStakeholderTestEntity = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $responseProcedure["entity"]->id);
        $procedureStakeholderTestEntity["email"] = $this->testEmail;
        $this->create($this->procedureStakeholderController, $procedureStakeholderTestEntity);
        $this->entity["statusId"] = ProcedureStatus::FINISHED;
        $responseProcedure = $this->update($this->controller, $this->entity);


        // Create Procedure with another exisiting Procedure with the same external ID and status FINISHED
        $duplicatedProceduresResponseOnCreate = $this->create($this->controller, $newInitalData);
        // Save endpoint output into log file
        $this->logResponseData("CANCEL ERROR", $duplicatedProceduresResponseOnCreate);

        // Verify data
        $this->assertEquals('ERROR', $duplicatedProceduresResponseOnCreate['result']);
        $this->assertEquals($this->expectedMessageError, $duplicatedProceduresResponseOnCreate['message'][0]);

        // Assign EXPIRED status to procedure
        $newInitalData = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
        $responseProcedure = $this->create($this->controller, $newInitalData);
        $this->entity = $this->getEntity($responseProcedure);
        $this->entity["statusId"] = ProcedureStatus::EXPIRED;
        $duplicatedProceduresResponseOnUpdate = $this->update($this->controller, $this->entity);
        $this->logResponseData("EXPIRED PROCEDURE", $duplicatedProceduresResponseOnUpdate);

        // Set mocks to force next case
        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureService->method('procedureHasSignatureWaitingForDownload')->willReturn(false);
        $this->signatureService->method('isProcedureWaitingForSignature')->willReturn(true);

        $this->setUpController();
        $newInitalData = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $procedureTypeId);
        $responseProcedure = $this->create($this->controller, $newInitalData);

        // Cancel procedure with a signature pending
        $responseCancelProcedure = $this->procedureService->cancelProcedure($responseProcedure["entity"], $this->userId, ProcedureStatus::DUPLICATED)->getData();
        $this->logResponseData("PROCEDURE WAITING FOR SIGNATURE", $responseCancelProcedure);
        $this->assertEquals('ERROR', $duplicatedProceduresResponseOnCreate['result']);
        $this->assertEquals($this->isProcedureWaitingForSignatureExpectedMessageError, $responseCancelProcedure['message']);

        // Set mocks to force next case
        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureService->method('procedureHasSignatureWaitingForDownload')->willReturn(true);
        $this->signatureService->method('isProcedureWaitingForSignature')->willReturn(false);

        $this->setUpController();

        // Cancel procedure with a signature pending of download
        $responseCancelProcedure = $this->procedureService->cancelProcedure($responseProcedure["entity"], $this->userId, ProcedureStatus::DUPLICATED)->getData();
        $this->logResponseData("PROCEDURE WAITING FOR DOWNLOADING", $responseCancelProcedure);
         $this->assertEquals('ERROR', $duplicatedProceduresResponseOnCreate['result']);
        $this->assertEquals($this->procedureHasSignatureWaitingForDownloadExpectedMessageError, $responseCancelProcedure['message']);

        $this->nextCloudHelper->method('deletePath')->willThrowException(new DoesNotExistException('Signature not found'));
        $this->signatureService = $this->createMock(SignatureService::class);
        $this->signatureService->method('procedureHasSignatureWaitingForDownload')->willReturn(false);
        $this->signatureService->method('isProcedureWaitingForSignature')->willReturn(false);
        $this->setUpController();

        // Cancel procedure when deletePath in NextcloudHelper throws DoesNotExistException
        try {
            $responseCancelProcedure = $this->procedureService->cancelProcedure($responseProcedure["entity"], $this->userId, ProcedureStatus::DUPLICATED)->getData();
            $this->fail('cancelProcedure method expected to throw DoesNotExistException instance');
        } catch (\Throwable $th) {
            $this->assertInstanceOf(
                    DoesNotExistException::class,
                    $th,
                    'Exception thrown, but not instance of DoesNotExistException'
            );
        }
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
     * @param array $testEntity Expected data
     * @param array $duplicatedEntity Actual data from response
     */
    protected function checkEntityData(array $testEntity, array $duplicatedEntity): void {
        // Check all received information
        $this->assertEquals(
            $testEntity['externalId'],
            $duplicatedEntity['externalId'],
            'External Id mismatch'
        );

        $this->assertEquals(
            $testEntity['procedureTypeId'],
            $duplicatedEntity['procedureTypeId'],
            'Procedure Type Id mismatch'
        );

        $this->assertEquals(
            json_encode($testEntity['assignedGroupId']),
            json_encode($duplicatedEntity['assignedGroupId']),
            'Assigned Group Id mismatch'
        );

        $this->assertEquals(
            json_encode($testEntity['comments']),
            json_encode($duplicatedEntity['comments']),
            'Comments mismatch'
        );

        $this->assertEquals(
            json_encode($testEntity['topic']),
            json_encode($duplicatedEntity['topic']),
            'Topic mismatch'
        );

        $this->assertEquals(
            json_encode($testEntity['attendanceChannel']),
            json_encode($duplicatedEntity['attendanceChannel']),
            'Attendance Channel mismatch'
        );

        $this->assertEquals(
            json_encode($testEntity['appointmentDate']),
            json_encode($duplicatedEntity['appointmentDate']),
            'Appointment Date mismatch'
        );

        $this->assertEquals(
            json_encode($duplicatedEntity['statusId']),
            ProcedureStatus::DUPLICATED,
            'Appointment Date mismatch'
        );

        $this->assertEquals(
            json_encode($testEntity['statusId']),
            ProcedureStatus::INITIALIZING,
            'Appointment Date mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Save procedure type
        $procedureTypeTestEntityData = TestData::$procedureTypeTestEntityData;
        $procedureTypeTestEntityData["allowSignature"] = true;
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntityData);

        // Create procedure to test cancel duplication
        $this->initalData = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $responseProcedure = $this->create($this->controller, $this->initalData);
        $this->entity = $this->getEntity($responseProcedure);

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
                return $this->controller;
            case 'OCA\VTramit\Controller\ProcedureStakeholderController':
                return $this->procedureStakeholderController;
            default:
                return $this->controller;
        }
    }
}
