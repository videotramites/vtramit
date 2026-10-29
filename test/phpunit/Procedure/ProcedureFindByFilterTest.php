<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Procedure;


use OCP\IURLGenerator;
use OCP\Files\IRootFolder;

use OCA\VTramit\Exception\DataNotFoundException;

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
use OCA\VTramit\Controller\ProcedureStakeholderController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureTypeGroupController;
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

use OCA\VTramit\Test\Phpunit\Helpers\TestHelpers;
/**
 * Tests for Procedure FindByFilter (PU030)
 */
class ProcedureFindByFilterTest extends CommonTestOperations
{

    private string $testFunction = "testFindByFilter";

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

    /** @var SignatureButtonMapper */
    private SignatureButtonMapper $signatureButtonMapper;

    /** @var SignatureButtonService */
    private SignatureButtonService $signatureButtonService;

    /** @var SignatureFileMapper */
    private SignatureFileMapper $signatureFileMapper;

    /** @var SignatureMapper */
    private SignatureMapper $signatureMapper;

    /** @var SignatureService */
    private SignatureService $signatureService;

    /** @var SignatureTypeController */
    private SignatureTypeController $signatureTypeController;

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
     * Constructor for the Procedure test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'procedure-find-by-filter';
        $this->setEntityTypeName('procedure');
        parent::__construct($testName, $this->testFunction);

        // Inizializate mappers
        $this->signatureButtonMapper = new SignatureButtonMapper($this->dbConnection);
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
        $this->userGroupHelper->method('getAllowedGroupsForUser')->willReturn($this->allowedGroups);
        $this->userGroupHelper->method('getAssignableUsersByGroup')->willReturn([]);
        
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

        $this->procedureTypeGroupController = new ProcedureTypeGroupController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeGroupService,
            $this->userId
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

        $this->signatureTypeController = new SignatureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->signatureTypeService,
            $this->userId
        );
    }

    /**
     * 
     * Steps:
     *
     *  1.- Find with filter: F001
     *  2.- Find with filter: F002
     *  3.- Find with filter in csv format: F002
     *  4.- Change appointment date to today
     *  4.- Find with filter: F010
     *  5.- Find with filter: F003
     *  6.- Find with filter: F004
     *  7.- Change appointment date to now
     *  8.- Find with filter: F005
     *  9.- Find with filter: F006
     *  10.- Force vtramit.group.filter to return interval of days
     *  11.- Find with filter: F005
     *  12.- Find with filter: F007
     *  13.- Find with filter: F008
     *  14.- Force vtramit.history.days to return 0
     *  15.- Force that allowedGroupsForUser returns []
     *  16.- Find with filter: F001
     *  17.- Change appointment date to today
     *  18.- Find with filter: F009
     *  19.- Force resultsPerPage to return 0
     *  20.- Find with filter: F002
     * 
     */
    public function testFindByFilter(): void {

        $this->createTestRequiredData();

        // Find with filter: F001
        $filter = '{
            "minDate": "tomorrow",
            "maxDate": "tomorrow",
            "status": '. $this->entity['statusId'].',
            "freeSearch": "'. substr($this->entity['externalId'], 0, -3).'",
            "jitsiRoomCode": "'.$this->entity['jitsiRoomCode'].'",
            "externalId": "'. $this->entity['externalId'].'",
            "procedureTypeId": '. $this->entity['procedureTypeId'].'
        }';


        $this->findByFilter($this->entity, $filter);
        $this->procedureService->findByFilter(json_decode($filter, true), $this->userId, true);

        // Find with filter: F002
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

        $this->findByFilter($this->entity, $filter);

        // Find with filter in csv format: F002
         $this->findByFilter($this->entity, $filter, 1, true);

        // Change appointment date to today
        $today = new \DateTime('today',new \DateTimeZone('Europe/Berlin'));
        $this->entity["appointmentDate"] = $today->format('Y-m-d H:i:s');
        unset($this->entity["statusId"]);
        $responseProcedure = $this->update($this->controller, $this->entity);
        $this->logResponseData("UPDATE", $responseProcedure);
        $this->entity = $this->getEntity($responseProcedure);

        // Find with filter: F010
         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueToday"
        }';

        $this->findByFilter($this->entity, $filter);

        // Find with filter: F003
         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueWeek"
        }';

        $this->findByFilter($this->entity, $filter);

        // Find with filter: F004
        $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueMonth"
        }';

        $this->findByFilter($this->entity, $filter);

        // Change appointment date to now
        $now = new \DateTime('now',new \DateTimeZone('Europe/Berlin'));
        $this->entity["appointmentDate"] = $now->format('Y-m-d H:i:s');
        $responseProcedure = $this->update($this->controller, $this->entity);

        //Find with filter: F005
         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "dueNow"
        }';

        $this->findByFilter($this->entity, $filter);

        // Find with filter: F006
        $tomorrow = new \DateTime('tomorrow',new \DateTimeZone('Europe/Berlin'));
         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDateTo": "'. $tomorrow->format('Y-m-d H:i:s') .'"
        }';

        $this->findByFilter($this->entity, $filter);

        // Force vtramit.group.filter to return interval of days
        $this->systemConfigValues['vtramit.group.filter'] = [
           'default' => [
               'now' => [
                   'interval-before' => '-2 day',
                   'interval-after'  => '+1 day',
               ],
           ],
       ];

        $this->initializeConfigService();
        $this->setUpController();

        // Find with filter: F005
         $filter = '{
            "dueDate": "dueNow"
        }';

        $this->findByFilter($this->entity, $filter, 1, false, true);

        // Find with filter: F007
        $yesterday = new \DateTime('yesterday',new \DateTimeZone('Europe/Berlin'));

         $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDateFrom": "'. $yesterday->format('Y-m-d H:i:s') .'",
            "dueDateTo": "'. $tomorrow->format('Y-m-d H:i:s') .'"
        }';

        $this->findByFilter($this->entity, $filter, 1, false, true);

        // Find with filter: F008
        $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "stakeholderPhone": "'. TestData::$procedureStakeholderTestEntityData["phone"] .'"
        }';

        $this->findByFilter($this->entity, $filter);

        // Force vtramit.history.days to return 0
        $this->systemConfigValues['vtramit.history.days'] = 0;

        $this->initializeConfigService();

        // Force that allowedGroupsForUser returns []
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        $this->userGroupHelper->method('getAllowedGroupsForUser')->willReturn([]);
        $this->userGroupHelper->method('getAssignableUsersByGroup')->willReturn([]);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);

        $this->setUpController();

        // Find with filter: F001
        $filter = '{
            "minDate": "tomorrow",
            "maxDate": "tomorrow",
            "status": '. $this->entity['statusId'].',
            "freeSearch": "'. substr($this->entity['externalId'], 0, -3).'",
            "jitsiRoomCode": "'.$this->entity['jitsiRoomCode'].'",
            "externalId": "'. $this->entity['externalId'].'",
            "procedureTypeId": '. $this->entity['procedureTypeId'].'
        }';

        $this->findByFilter($this->entity, $filter, 1, false, true);

        // Change appointment date to today
        $today = new \DateTime('today',new \DateTimeZone('Europe/Berlin'));
        $this->entity["appointmentDate"] = $today->format('Y-m-d H:i:s');
        $responseProcedure = $this->update($this->controller, $this->entity);

        // Find with filter: F009
        $filter = '{
            "groups": ["'.$this->entity['assignedGroupId'].'"],
            "dueDate": "overdue"
        }';

        $this->findByFilter($this->entity, $filter, 1, false, true);

        // Force resultsPerPage to return 0
        $this->appConfigValues['vtramit:resultsPerPage'] = "0";

        $this->initializeConfigService();
        $this->setUpController();

        //Find with filter: F002
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

         $this->findByFilter($this->entity, $filter, 1, false);

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
        // Save procedure type
        $responseProcedureType = $this->create($this->procedureTypeController, TestData::$procedureTypeTestEntityData);

                // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $responseProcedureType["entity"]->id;

        $responseProcedureTypeGroup = $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);

        // Create procedure
        $procedureTestEntity = $this->setAssociatedInfoToProcedure(TestData::$procedureTestEntityData, $responseProcedureType["entity"]->id);
         $procedureTestEntity["jitsiRoomCode"] = TestHelpers::generateJitsiRoomCode();
        $responseProcedure = $this->create($this->controller, $procedureTestEntity);
        $this->entity = $this->getEntity($responseProcedure);

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
            case 'OCA\VTramit\Controller\ProcedureTypeGroupController':
                return $this->procedureTypeGroupController;
            case 'OCA\VTramit\Controller\ProcedureStakeholderController':
                return $this->procedureStakeholderController;
            default:
                return $this->controller;
        }
    }
}
