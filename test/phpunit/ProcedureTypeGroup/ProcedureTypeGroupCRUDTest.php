<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\ProcedureTypeGroup;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\SignatureButtonMapper;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureTypeGroupController;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * (PU043)
 */
class ProcedureTypeGroupCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";

    private string $procedureTypeGroupName = "";

    /** @var ProcedureTypeController */
    private ProcedureTypeController $procedureTypeController;


    /** @var ProcedureTypeService */
    private ProcedureTypeService $procedureTypeService;

    /** @var ProcedureTypeGroupService */
    private ProcedureTypeGroupService $procedureTypeGroupService;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /** @var ProcedureTypeGroupMapper */
    private ProcedureTypeGroupMapper $procedureTypeGroupMapper;

    /** @var ProcedureTypeProtocolStepMapper */
    private ProcedureTypeProtocolStepMapper $procedureTypeProtocolStepMapper;

    /**
     * Constructor for the SignatureButton test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'procedure-type-group-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure type group');
        // Initialize mappers
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = new ProcedureTypeGroupMapper($this->dbConnection);
        $this->procedureTypeProtocolStepMapper = new ProcedureTypeProtocolStepMapper($this->dbConnection);
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);


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
        $this->procedureTypeController = new ProcedureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeService,
            $this->userId
        );

    }

    /**
     * 
     * Steps:
     *
     *  1.- Create procedure type group correctly (CREATE)
     *  2.- Find procedure type group correctly using ID (FIND)
     *  3.- Find procedure type group in procedure type group existing entities list (FIND ALL)
     *  4.- Update procedure type group correctly (UPDATE)
     *  5.- Delete procedure type group correctly (DELTE)
     * 
     */
    public function testCRUD(): void {


        $this->createTestRequiredData();

        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];

        $initalData = TestData::$procedureTypeGroupTestEntityData;
        $updateData = TestData::$updatedProcedureTypeGroupTestEntityData;

        $initalData["procedureTypeId"] = $procedureTypeId;
        $updateData["procedureTypeId"] = $procedureTypeId;


        // Create procedure type group correctly (CREATE)
        // Find procedure type group correctly using ID (FIND)
        // Find procedure type group in procedure type group existing entities list (FIND ALL)
        // Update procedure type group correctly (UPDATE)
        // Delete procedure type group correctly (DELTE)
        $this->runCRUDTest($initalData, $updateData);
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {

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
        $this->controller = new ProcedureTypeGroupController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->procedureTypeGroupService,
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
            $expectedData['procedureTypeId'],
            $actualData['procedureTypeId'],
            'Procedure type ID mismatch'
        );

        $this->assertEquals(
            $expectedData['group'],
            $actualData['group'],
            'Group mismatch'
        );

        $this->assertEquals(
            $expectedData['notifyStaffTemplateId'],
            $actualData['notifyStaffTemplateId'],
            'Notify staff template ID mismatch'
        );

        $this->assertEquals(
            $expectedData['vcConfirmationForm'],
            $actualData['vcConfirmationForm'],
            'Vc confirmation form mismatch'
        );

        $this->assertEquals(
            $expectedData['mailTemplateId'],
            $actualData['mailTemplateId'],
            'Mail template ID mismatch'
        );

        $this->assertEquals(
            $expectedData['allowCreate'],
            $actualData['allowCreate'],
            'Allow create mismatch'
        );

        $this->assertEquals(
            $expectedData['allowAssignGroup'],
            $actualData['allowAssignGroup'],
            'Allow assign group mismatch'
        );

        $this->assertEquals(
            $expectedData['allowAssignUser'],
            $actualData['allowAssignUser'],
            'Allow assign user mismatch'
        );

        $this->assertEquals(
            $expectedData['bypassRequired'],
            $actualData['bypassRequired'],
            'Bypass required mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Save procedure type response
       $this->create($this->procedureTypeController, TestData::$procedureTypeTestEntityData);

    }

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     */
    protected function getControllerInstance(string $controllerClass = ''): EntityController {

        switch ($controllerClass) {
            case 'OCA\VTramit\Controller\ProcedureTypeController':
                return $this->procedureTypeController;
            default:
                return $this->controller;
        }
    }
}
