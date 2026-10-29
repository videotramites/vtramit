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
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\ProcedureTypeGroupController;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * (PU040)
 */
class ProcedureTypeFindByFilterTest extends CommonTestOperations
{

    private string $testFunction = "testFindByFilter";

    private string $filter = '{
        "groups": ["TEST"],
        "allowCreate": false,
        "allowAssignGroup": true,
        "allowAssignUser": false
    }';

    /** @var ProcedureTypeGroupController */
    private $procedureTypeGroupController;

    /** @var ProcedureTypeService */
    private $procedureTypeService;

    /** @var ProcedureTypeGroupService */
    private $procedureTypeGroupService;

    /** @var ProcedureTypeMapper */
    private $procedureTypeMapper;

    /** @var ProcedureTypeGroupMapper */
    private $procedureTypeGroupMapper;

    /** @var ProcedureTypeProtocolStepMapper */
    private $procedureTypeProtocolStepMapper;

    /**
     * Constructor for the Procedure Type test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'procedure-type-find-by-filter';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure type');


        // Initialize mappers
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = new ProcedureTypeGroupMapper($this->dbConnection);
        $this->procedureTypeProtocolStepMapper = new ProcedureTypeProtocolStepMapper($this->dbConnection);

        // Initialize UserGroupHelper using mock
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Mock the return from the method getAllowedGroupsForUser in UserGroupHelper
        $this->userGroupHelper->method('getAllowedGroupsForUser')->willReturn($this->allowedGroups);

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

    }

    /**
     * 
     *  Steps:
     * 
     *  1.- Search with filter
     *  2.- Search with the same filter when no groups are alowed asigned for the user
     */
    public function testFindByFilter(): void {

        $this->createTestRequiredData();

        // Search with filter:
        $this->findByFilter($this->entity, $this->filter);

        $filter = '
            {
                "allowCreate": false,
                "allowAssignGroup": true,
                "allowAssignUser": false
            }';
        $this->findByFilter($this->entity, $filter);

        // Search with the same filter when no groups are alowed asigned for the user
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        $this->userGroupHelper->method('getAllowedGroupsForUser')->willReturn([]);
        $this->userGroupHelper->method('getAssignableUsersByGroup')->willReturn([]);
        // Set mock value to isValidEmailMethod
        $this->userGroupHelper->method('isValidEmail')->willReturn(true);
        $this->setUpController();

        $this->findByFilter($this->entity, $filter,1,false,true);

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
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Save procedure type response
        $this->entity = $this->getEntity($this->create($this->controller, TestData::$procedureTypeTestEntityData));
        // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $this->entity["id"];

        $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);
    }

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     */
    protected function getControllerInstance(string $controllerClass = ''): EntityController {

        switch ($controllerClass) {
            case 'OCA\VTramit\Controller\ProcedureTypeGroupController':
                return $this->procedureTypeGroupController;
            default:
                return $this->controller;
        }
    }
}
