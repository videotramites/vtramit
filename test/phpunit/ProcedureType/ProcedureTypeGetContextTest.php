<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\ProcedureType;


use OCA\VTramit\Db\ProcedureTypeGroup;
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
 *  (PU041)
 */
class ProcedureTypeGetContextTest extends CommonTestOperations
{

    private string $testFunction = "testGetContext";

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
        $testName = 'procedure-type-get-context';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('procedure type');

        // Initialize mappers
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = new ProcedureTypeGroupMapper($this->dbConnection);
        $this->procedureTypeProtocolStepMapper = new ProcedureTypeProtocolStepMapper($this->dbConnection);

        // Initialize UserGroupHelper using mock
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        // Mock the return from the methods in UserGroupHelper
        $this->userGroupHelper->method('getAllowedGroupsForUser')->willReturn($this->allowedGroups);
        $this->userGroupHelper->method('getAssignableUsersByGroup')->willReturnCallback(function ($group, $userId, $allowAssignUser) {
            if ($group === "IMH") {
                return ["imh-01" => "IMH 01",
                        "imh-02" => "IMH 02",
                        "oac-imh-01" => "Supervisor OAC/IMH 01"];
            }
            else if ($group === "OAC") {
                return ["oac-01" => "OAC 01",
                        "oac-02" => "OAC 02",
                        "oac-imh-01" => "Supervisor OAC/IMH 01"];
            }
            else {
                return [];
            };
        });

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
     *  1.- Get context of procedure type created
     */
    public function testGetContext(): void {

        $this->createTestRequiredData();

        // Get context of procedure type created
        $this->getContextProcedureType($this->entity['id']);

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
     * @param array $responseData Expected data
     */
    protected function checkEntityData(array $responseData): void {
        // Go throught all groups and compare info depending on $group key field
        foreach ($responseData['groups'] as $group) {

            if ($group["key"] === "IMH") {
                $this->assertEquals('IMH', $group["key"]);
                $this->assertEquals('IMH', $group["value"]);
                $this->assertEquals('[{"key":"imh-01","value":"IMH 01"},{"key":"imh-02","value":"IMH 02"},{"key":"oac-imh-01","value":"Supervisor OAC\/IMH 01"}]'
                                    , json_encode($group["users"]));
            }
            else if ($group["key"] == "OAC") {
                $this->assertEquals('OAC', $group["key"]);
                $this->assertEquals('OAC', $group["value"]);
                $this->assertEquals('[{"key":"oac-01","value":"OAC 01"},{"key":"oac-02","value":"OAC 02"},{"key":"oac-imh-01","value":"Supervisor OAC\/IMH 01"}]'
                                    , json_encode($group["users"]));
            }
            else {
                $this->assertEquals('TEST', $group["key"]);
                $this->assertEquals('TEST', $group["value"]);
                $this->assertEquals('[]', json_encode($group["users"]));
            }
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
        $procedureTypeGroupTestEntity["allowAssignUser"] = true;
        $procedureTypeGroupTestEntity["bypassRequired"] = true;

        $procedureTypeGroupTestEntity["group"] = "IMH";
        $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);

        $procedureTypeGroupTestEntity["group"] = "OAC";
        $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);

        $procedureTypeGroupTestEntity["group"] = "TEST";
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
