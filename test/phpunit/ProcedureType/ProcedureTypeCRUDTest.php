<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\ProcedureType;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\RgpdOptionMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\RgpdOption;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\RgpdOptionController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\RgpdOptionService;
use OCA\VTramit\Service\ActivityManager;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * (PU039)
 */
class ProcedureTypeCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";


    /** @var RgpdOptionController */
    private RgpdOptionController $rgpdController;

    /** @var ProcedureTypeService */
    private ProcedureTypeService $procedureTypeService;

    /** @var RgpdOptionService */
    private RgpdOptionService $rgpdOptionService;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /** @var ProcedureTypeGroupMapper */
    private ProcedureTypeGroupMapper  $procedureTypeGroupMapper;

    /** @var RgpdOptionMapper */
    private RgpdOptionMapper $rgpdOptionMapper;

    /** @var ProcedureMapper */
    private ProcedureMapper $procedureMapper;

    /** @var ProcedureStakeholderMapper */
    private ProcedureStakeholderMapper $procedureStakeholderMapper;

    /**
     * Constructor for the Procedure Type test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'procedure-type-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure type');


        // Initialize mappers
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = $this->createMock(ProcedureTypeGroupMapper::class);
        $this->rgpdOptionMapper = new RgpdOptionMapper($this->dbConnection);
        $this->procedureMapper = $this->createMock(ProcedureMapper::class);
        $this->procedureStakeholderMapper = $this->createMock(ProcedureStakeholderMapper::class);
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);

        // Instantiate the service with mocks
        $this->rgpdOptionService = new RgpdOptionService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->rgpdOptionMapper,
            $this->procedureMapper,
            $this->procedureStakeholderMapper,
            $this->procedureTypeMapper
        );

        // Ensure controller is instantiated properly
        $this->rgpdController = new RgpdOptionController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->rgpdOptionService,
            $this->userId
        );

    }

    /**
     * 
     * Steps:
     *
     *  1.- Create procedure type correctly (CREATE)
     *  2.- Find procedure type correctly using ID (FIND)
     *  3.- Find procedure type in procedure type  existing entities list (FIND ALL)
     *  4.- Update procedure type correctly (UPDATE)
     *  5.- Delete procedure type correctly (DELTE)
     *  6.- Delete procedure type with procedure type groups assigned
     * 
     */
    public function testCRUD(): void {

        $this->createTestRequiredData();

        $initalData = TestData::$procedureTypeTestEntityData;
        $updateData = TestData::$updatedProcedureTypeTestEntityData;

        $updateData["rgpdOptions"] = [$this->createdEntities[get_class($this->rgpdController)][0]];

        // Create procedure type correctly (CREATE)
        // Find procedure type correctly using ID (FIND)
        // Find procedure type in procedure type existing entities list (FIND ALL)
        // Update procedure type correctly (UPDATE)
        // Delete procedure type correctly (DELTE)
        $this->runCRUDTest($initalData, $updateData);

        // Delete procedure type with procedure type groups assigned
        $this->procedureTypeGroupMapper = $this->createMock(ProcedureTypeGroupMapper::class);
        $this->procedureTypeGroupMapper->method('findGroupsByProcedureTypeId')->willReturn(["TEST"]);

        $this->setUpController();

        $responseProcedureType = $this->create($this->controller,  $initalData);
        $responseProcedureType = $this->controller->delete($responseProcedureType["entity"]->id)->getData();
        $this->logResponseData("", $responseProcedureType);

    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {

        // Create procedure type service
        $this->procedureTypeService = new ProcedureTypeService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->procedureTypeMapper,
            $this->procedureTypeGroupMapper,
            $this->userGroupHelper,
            $this->configService
        );

        // Create procedure type controller
        $this->controller = new ProcedureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeService,
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
            $expectedData['name'],
            $actualData['name'],
            'Name mismatch'
        );

        $this->assertEquals(
            $expectedData['uploadsFolder'],
            $actualData['uploadsFolder'],
            'Uploads Folder mismatch'
        );

        $this->assertEquals(
            $expectedData['downloadsFolder'],
            $actualData['downloadsFolder'],
            'Downloads Folder mismatch'
        );

        $this->assertEquals(
            $expectedData['signatureUploadsFolder'],
            $actualData['signatureUploadsFolder'],
            'Signature Uploads Folder mismatch'
        );

        $this->assertEquals(
            $expectedData['signatureDownloadsFolder'],
            $actualData['signatureDownloadsFolder'],
            'Signature Downloads Folder mismatch'
        );

        $this->assertEquals(
            $expectedData['jitsiStakeholderUrl'],
            $actualData['jitsiStakeholderUrl'],
            'Jitsi Stakeholder Url mismatch'
        );

        $this->assertEquals(
            $expectedData['jitsiStakeholderJwt'],
            $actualData['jitsiStakeholderJwt'],
            'Jitsi Stakeholder Jwt mismatch'
        );

        $this->assertEquals(
            $expectedData['jitsiStaffUrl'],
            $actualData['jitsiStaffUrl'],
            'Jitsi Staff Url mismatch'
        );

        $this->assertEquals(
            $expectedData['jitsiStaffJwt'],
            $actualData['jitsiStaffJwt'],
            'Jitsi Staff Jwt mismatch'
        );

        $this->assertEquals(
            $expectedData['allowUpload'],
            $actualData['allowUpload'],
            'Allow Upload mismatch'
        );

        $this->assertEquals(
            $expectedData['allowDownload'],
            $actualData['allowDownload'],
            'Allow Download mismatch'
        );

        $this->assertEquals(
            $expectedData['allowSignature'],
            $actualData['allowSignature'],
            'Allow Signature mismatch'
        );

        $this->assertEquals(
            $expectedData['signatureLimit'],
            $actualData['signatureLimit'],
            'Signature Limit mismatch'
        );

        $this->assertEquals(
            $expectedData['allowVideo'],
            $actualData['allowVideo'],
            'Allow Video mismatch'
        );

        $this->assertEquals(
            $expectedData['notifyStakeholder'],
            $actualData['notifyStakeholder'],
            'Notify Stakeholder mismatch'
        );

        $this->assertEquals(
            $expectedData['notifyStaff'],
            $actualData['notifyStaff'],
            'Notify Staff mismatch'
        );

        $this->assertEquals(
            $expectedData['requireAssignedGroup'],
            $actualData['requireAssignedGroup'],
            'Require Assigned Group mismatch'
        );

        $this->assertEquals(
            $expectedData['requireExternalId'],
            $actualData['requireExternalId'],
            'Require External ID mismatch'
        );

        $this->assertEquals(
            $expectedData['requireAppointmentDate'],
            $actualData['requireAppointmentDate'],
            'Require Appointment Date mismatch'
        );

        $this->assertEquals(
            $expectedData['requireDeadlineDate'],
            $actualData['requireDeadlineDate'],
            'Require Deadline Date mismatch'
        );

        $this->assertEquals(
            $expectedData['requireStakeholderPid'],
            $actualData['requireStakeholderPid'],
            'Require Stakeholder PID mismatch'
        );

        $this->assertEquals(
            $expectedData['requireStakeholderPhone'],
            $actualData['requireStakeholderPhone'],
            'Require Stakeholder Phone mismatch'
        );

        $this->assertEquals(
            $expectedData['requireStakeholderEmail'],
            $actualData['requireStakeholderEmail'],
            'Require Stakeholder Email mismatch'
        );

        $this->assertEquals(
            $expectedData['requireAttendanceChannel'],
            $actualData['requireAttendanceChannel'],
            'Require Attendance Channel mismatch'
        );

        $this->assertEquals(
            $expectedData['procedureAnonymizationPeriod'],
            $actualData['procedureAnonymizationPeriod'],
            'Procedure Anonymization Period mismatch'
        );

        $this->assertEquals(
            $expectedData['filesDeletionPeriod'],
            $actualData['filesDeletionPeriod'],
            'Files Deletion Period mismatch'
        );

        $this->assertEquals(
            $expectedData['procedureDeletionPeriod'],
            $actualData['procedureDeletionPeriod'],
            'Procedure Deletion Period mismatch'
        );

        if (isset($expectedData['rgpdOptions'])) {
            $this->assertEquals(
                $expectedData['rgpdOptions'],
                $actualData['rgpdOptions'],
                'Rgpd options mismatch'
            );
        }

        if (isset($expectedData['allowedAttendanceChannels'])) {
            $this->assertEquals(
                $expectedData['allowedAttendanceChannels'],
                $actualData['allowedAttendanceChannels'],
                'Allowed Attendance Channels mismatch'
            );
        }
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Save rgpd option
        $this->create($this->rgpdController, TestData::$rgpdOptionTestEntityData);
    }

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     */
    protected function getControllerInstance(string $controllerClass = ''): EntityController {

        switch ($controllerClass) {
            case 'OCA\VTramit\Controller\RgpdOptionController':
                return $this->rgpdController;
            case 'OCA\VTramit\Controller\ProcedureTypeGroupController':
                return $this->rgpdController;
            default:
                return $this->controller;
        }
    }
}