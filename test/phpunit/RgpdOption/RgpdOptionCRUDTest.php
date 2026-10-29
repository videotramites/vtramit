<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\RgpdOption;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\RgpdOptionMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\RgpdOption;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\RgpdOptionController;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\RgpdOptionService;
use OCA\VTramit\Service\ActivityManager;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * PU047
 */
class RgpdOptionCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";

    /** @var RgpdOptionService */
    private RgpdOptionService $rgpdOptionService;

    /** @var RgpdOptionMapper */
    private RgpdOptionMapper $rgpdOptionMapper;

    /** @var ProcedureMapper */
    private ProcedureMapper $procedureMapper;

    /** @var ProcedureStakeholderMapper */
    private ProcedureStakeholderMapper $procedureStakeholderMapper;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /**
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'rgpd-option-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('rgpd option');
    }
    
    /**
     * 
     * Steps:
     *
     *  1.- Create rgpd option correctly (CREATE)
     *  2.- Find rgpd option correctly using ID (FIND)
     *  3.- Find rgpd option in rgpd activity existing entities list (FIND ALL)
     *  4.- Update rgpd option correctly (UPDATE)
     *  5.- Delete rgpd option correctly (DELTE)
     *  6.- Create entity indicting id previously
     * 
     */
    public function testCRUD(): void {


        // Create rgpd option correctly (CREATE)
        // Find rgpd option correctly using ID (FIND)
        // Find rgpd option in rgpd activity existing entities list (FIND ALL)
        // Update rgpd option correctly (UPDATE)
        // Delete rgpd option correctly (DELTE)
        $this->runCRUDTest(TestData::$rgpdOptionTestEntityData,TestData::$updatedRgpdOptionTestEntityData);

        // Create entity indicting id previously
        $initalData = TestData::$rgpdOptionTestEntityData;
        $initalData["id"] = 111;
        $this->create($this->controller, $initalData);
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Set mappers
        $this->rgpdOptionMapper = new RgpdOptionMapper($this->dbConnection);
        $this->procedureMapper = $this->createMock(ProcedureMapper::class);
        $this->procedureStakeholderMapper = $this->createMock(ProcedureStakeholderMapper::class);
        $this->procedureTypeMapper = $this->createMock(ProcedureTypeMapper::class);

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
        $this->controller = new RgpdOptionController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->rgpdOptionService,
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
            $expectedData['type'],
            $actualData['type'],
            'Type mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['label']),
            json_encode($actualData['label']),
            'Label content mismatch'
        );

        $this->assertEquals(
            $expectedData['required'],
            $actualData['required'],
            'Required value mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {}

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     */
    protected function getControllerInstance($controllerClass = ''): EntityController {

        return $this->controller;
    }
}