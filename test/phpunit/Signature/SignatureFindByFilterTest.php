<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Signature;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCA\VTramit\Db\SignatureTypeMapper;
use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Db\RgpdActivityMapper;
use OCA\VTramit\Db\SignatureFileMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\SignatureMapper;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureType;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\SignatureType;
use OCA\VTramit\Db\SignatureButton;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureTypeGroupController;
use OCA\VTramit\Controller\RgpdActivityApiController;
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
use OCA\VTramit\Service\ProcedureTools;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Service\MailTemplateService;
use OCA\VTramit\Service\WebhookService;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\SignatureButtonController;
use OCA\VTramit\Controller\SignatureTypeController;
use OCA\VTramit\Helper\CurlHelper;
use OCA\VTramit\Db\SignatureButtonMapper;
use OCP\Files\Folder;
use OCP\Files\File;
use OCP\AppFramework\Db\DoesNotExistException;
use OCA\VTramit\Service\TopicService;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureVideoconferenceService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureTokenService;

/**
 * PU051
 */
class SignatureFindByFilterTest extends CommonTestOperations
{

    private string $testFunction = "testSignature";
    private string $expectedResponseMessage = "Signature system is disabled or you don't have permissions; please contact the system administrator";

    /** @var TopicService */
    private TopicService $topicService;

    /** @var Procedure Mapper for signature button database operations */
    private Procedure $procedureEntity;

    /** @var ProcedureType Mapper for signature button database operations */
    private ProcedureType $procedureTypeEntity;

    /** @var ProcedureStakeholder Mapper for signature button database operations */
    private ProcedureStakeholder $procedureStakeholderEntity;

    /** @var SignatureType Mapper for signature button database operations */
    private SignatureType $signatureTypeEntity;


    /** @var SignatureButton Mapper for signature button database operations */
    private SignatureButton $signatureButtonEntity;


     /** @var SignatureButtonService Service for signature button operations */
    private SignatureButtonService $signatureButtonService;

     /** @var MailTemplateService */
    private MailTemplateService $mailTemplateService;

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

      /** @var ProcedureTypeGroupService */
    private ProcedureTypeGroupService $procedureTypeGroupService;

    /** @var SignatureTypeService */
    private SignatureTypeService $signatureTypeService;

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

    /** @var ProcedureTypeGroupController */
    private ProcedureTypeGroupController $procedureTypeGroupController;

    /** @var SignatureTypeController */
    private SignatureTypeController $signatureTypeController;

    /** @var SignatureButtonController */
    private SignatureButtonController $signatureButtonController;

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

     /** @var SignatureMapper */
    private SignatureMapper $signatureMapper;

     /** @var SignatureFileMapper */
    private SignatureFileMapper $signatureFileMapper;

    /** @var CurlHelper */
    private $curlHelper;

    /** @var NextcloudHelper */
    private $nextcloudHelper;

    /** @var UserGroupHelper */
    private $userGroupHelper;

    /**
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'signature-find-by-filter';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure');

        // $this->procedureTools = $this->createMock(ProcedureTools::class);
        // $this->procedureTools->method('sendEmailToStakeholder')->willReturn(new ResponseInfo());
        // $this->procedureTools->method('sendEmailToStaff')->willReturn(new ResponseInfo());

        $this->topicService = $this->createMock(TopicService::class);

        $this->procedureTokenService = $this->createMock(ProcedureTokenService::class);

        $this->procedureFileService = $this->createMock(ProcedureFileService::class);
        $this->procedureFileService->method('getWebDAVToFolder')->willReturn("test/");

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
        $this->signatureMapper = new SignatureMapper($this->dbConnection);
        $this->signatureFileMapper = new SignatureFileMapper($this->dbConnection);

        $this->mailTemplateMapper = new MailTemplateMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = new ProcedureTypeGroupMapper($this->dbConnection);
        $this->procedureTypeProtocolStepMapper = new ProcedureTypeProtocolStepMapper($this->dbConnection);

        // Mock del rootFolder
       $this->rootFolder = $this->createMock(IRootFolder::class);

        $filepath = '/vtramit/proc/123/uploads/firma';

        // Simular que la ruta existe
       $this->rootFolder->method('nodeExists')->willReturn(true);

        // Simular carpeta obtenida
        $folderMock = $this->createMock(Folder::class);
       $this->rootFolder->method('get')
            ->willReturn($folderMock);

        // Simular archivos en esa carpeta
        $fileMock = $this->createMock(File::class);
        $fileMock->method('getExtension')->willReturn('pdf');

        $folderMock->method('getDirectoryListing')
            ->willReturn([$fileMock]);

        $this->urlGenerator = $this->createMock(IURLGenerator::class);
        $this->curlHelper = $this->createMock(CurlHelper::class);
        $this->curlHelper->method('request')
            ->willReturn((object)[
                'Ok' => true,
                'UserUrl' => 'https://example.com/signature/12345'
            ]);
        $this->nextCloudHelper = $this->createMock(NextcloudHelper::class);
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->deckHelper = $this->createMock(DeckHelper::class);

        $this->mailQueueService = $this->createMock(MailQueueService::class);
        $this->signatureTypeMapper = new SignatureTypeMapper($this->dbConnection);

        $this->webhookService = $this->createMock(WebhookService::class);
        $this->signatureButtonMapper = new SignatureButtonMapper($this->dbConnection);


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

        $this->topicService = $this->createMock(TopicService::class);

        $this->mailTemplateService = new MailTemplateService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->mailTemplateMapper,
            $this->createMock(ProcedureTokenService::class)
        );

       // Create signature button service
        $this->signatureTypeService = new SignatureTypeService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->signatureTypeMapper
        );

        // Create signature button controller
        $this->signatureTypeController = new SignatureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->signatureTypeService,
            $this->userId
        );

            // Create signature button service
        $this->signatureButtonService = new SignatureButtonService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->signatureButtonMapper
        );

        // Create signature button controller
        $this->signatureButtonController = new SignatureButtonController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->signatureButtonService,
            $this->userId
        );

    }

		    /**
     * Tests the findLast() function
     */
    public function setUpSignatureService(): void {

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
     *  1.- Filter with filter: F002
     *  2.- Filter with filter: F001
     *  3.- Force that allowedGroupsForUser returns created allowed groups list
     *  4.- Filter with filter: F001
     *  5.- Find with filter: F003
     *  6.- Change appointment's date to today
     *  7.- Filter with filter: F009
     *  8.- Filter with filter: F010
     *  9.- Filter with filter: F003
     *  10.- Filter with filter: F004
     *  11.- Change appointment's date to now
     *  12.- Filter with filter: F005
     *  13.- Filter with filter: F006
     *  14.- Filter with filter: F011
     *  15.- Filter with filter: F007
     *  16.- Filter with filter: F008
     * 
     */
    public function testSignature(): void {

        $this->createTestRequiredData();

        $procedureId = $this->createdEntities[get_class($this->controller)][0];
        $signatureButtonId = $this->createdEntities[get_class($this->signatureButtonController)][0];


        $this->entity["statusId"] = ProcedureStatus::PENDANT;
        $responseProcedure = $this->update($this->controller, $this->entity);
        $actualStatusId = $this->getEntity($responseProcedure)["statusId"];
        $this->assertEquals(
                            ProcedureStatus::PENDANT,
                            $actualStatusId,
                            'Pendant Status Id mismatch'
                        );
        $this->procedureEntity = $responseProcedure["entity"];

        $signatureEntity = $this->signatureService->createSignatureForProcedure($this->procedureEntity, $this->procedureTypeEntity,$this->procedureStakeholderEntity, $this->userId, "", "", $this->signatureTypeEntity, $this->signatureButtonEntity);

        // Filter with filter: F002
        $filter = '{
            "minDate": "tomorrow",
            "maxDate": "tomorrow",
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "status": '. $this->entity['statusId'].',
            "users": [null],
            "freeSearch": "'. substr($this->entity['externalId'], 0, -3).'",
            "jitsiRoomCode": "'.$this->entity['jitsiRoomCode'].'",
            "externalId": "'. $this->entity['externalId'].'",
            "procedureTypeId": '. $this->entity['procedureTypeId'].'
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Filter with filter: F001
        $filter = '{
            "minDate": "tomorrow",
            "maxDate": "tomorrow",
            "status": '. $this->entity['statusId'].',
            "users": [null],
            "freeSearch": "'. substr($this->entity['externalId'], 0, -3).'",
            "jitsiRoomCode": "'.$this->entity['jitsiRoomCode'].'",
            "externalId": "'. $this->entity['externalId'].'",
            "procedureTypeId": '. $this->entity['procedureTypeId'].'
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Force that allowedGroupsForUser returns created allowed groups list
        $this->userGroupHelper->method('getAllowedGroupsForUser')->willReturn($this->allowedGroups);
        $this->setUpSignatureService();

        // Filter with filter: F001
        $filter = '{
            "minDate": "tomorrow",
            "maxDate": "tomorrow",
            "status": '. $this->entity['statusId'].',
            "users": [null],
            "freeSearch": "'. substr($this->entity['externalId'], 0, -3).'",
            "jitsiRoomCode": "'.$this->entity['jitsiRoomCode'].'",
            "externalId": "'. $this->entity['externalId'].'",
            "procedureTypeId": '. $this->entity['procedureTypeId'].'
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Filter with filter: F003
        $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueWeek"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Change appointment's date to today
        $today = new \DateTime('today',new \DateTimeZone('Europe/Berlin'));
        $this->entity["appointmentDate"] = $today->format('Y-m-d H:i:s');
        $responseProcedure = $this->update($this->controller, $this->entity);

        // Filter with filter: F009
        $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "overdue"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Filter with filter: F010
        $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueToday"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Filter with filter: F003
         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueWeek"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Filter with filter: F004
        $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueMonth"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Change appointment's date to now
        $now = new \DateTime('now',new \DateTimeZone('Europe/Berlin'));
        $this->entity["appointmentDate"] = $now->format('Y-m-d H:i:s');
        $responseProcedure = $this->update($this->controller, $this->entity);

        // Filter with filter: F005
         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueNow"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Filter with filter: F006
        $tomorrow = new \DateTime('tomorrow',new \DateTimeZone('Europe/Berlin'));

         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDateTo": "'. $tomorrow->format('Y-m-d H:i:s') .'"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        $this->systemConfigValues['vtramit.group.filter'] = [
           'default' => [
               'now' => [
                   'interval-before' => '-2 day',
                   'interval-after'  => '+1 day',
               ],
           ],
       ];

        $this->initializeConfigService();
        $this->setUpSignatureService();

        //Filter with filter: F011
         $filter = '{
            "dueDate": "dueNow"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Filter with filter: F007
        $yesterday = new \DateTime('yesterday',new \DateTimeZone('Europe/Berlin'));

         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDateFrom": "'. $yesterday->format('Y-m-d H:i:s') .'",
            "dueDateTo": "'. $tomorrow->format('Y-m-d H:i:s') .'"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);

        // Filter with filter: F008
        $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "stakeholderPhone": "'. TestData::$procedureStakeholderTestEntityData["phone"] .'"
        }';

        $setSignatureError = $this->signatureService->findByFilter(json_decode($filter, true), $this->userId)->getData();
        $this->logResponseData("", $setSignatureError);
    }


    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        $this->procedureStakeholderService = $this->createMock(ProcedureStakeholderService::class);
        $this->setUpSignatureService();
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
        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, $procedureTypeTestEntity);
				$this->procedureTypeEntity =	$responseProcedureType["entity"];
              // Add rgpd options to procedure type
        $signatureTypeTestEntity = TestData::$signatureTypeTestEntityData;
        $signatureTypeTestEntity["filesPerRequest"] = -1;
        // Save signature type response
        $responseSignatureType = $this->create($this->signatureTypeController, $signatureTypeTestEntity);
				$this->signatureTypeEntity =	$responseSignatureType["entity"];


        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
        $procedureTestEntity["assignedGroupId"] = "TEST";
        $responseProcedure = $this->create($this->controller, $procedureTestEntity);

        $this->entity = $this->getEntity($responseProcedure);
				$this->procedureEntity =	$responseProcedure["entity"];


        // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $responseProcedureType["entity"]->id;
        $procedureTypeGroupTestEntity["vcConfirmationForm"] = "https://dev.vtramit.com/index.php/apps/forms/KKS64KkwapoJp8Dk/submit?procedure_id=%procedureId%";

        $responseProcedureTypeGroup = $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);

        $signatureButtonTest = $this->setAssociatedIdsToSignatureButtons(TestData::$signatureButtonTestEntityData, $responseProcedureType["entity"]->id, $responseSignatureType["entity"]->id,  $procedureTypeGroupTestEntity["group"]);

        $responseSignatureButton = $this->create($this->signatureButtonController, $signatureButtonTest);
				$this->signatureButtonEntity =	$responseSignatureButton["entity"];

        // Create procedure stakeholder
        $procedureStakeholderTestEntity = $this->setAssociatedInfoToProcedureStakeholder(TestData::$procedureStakeholderTestEntityData, $responseProcedure["entity"]->id);
        $procedureStakeholderTestEntity["email"] = $this->testEmail;
        $procedureStakeholderTestEntity["isMain"] = true;

        $responseProcedureStakeholder = $this->create($this->procedureStakeholderController, $procedureStakeholderTestEntity);
				$this->procedureStakeholderEntity =	$responseProcedureStakeholder["entity"];

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
