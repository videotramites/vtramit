<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\SignatureButton;

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
use OCA\VTramit\Db\SignatureTypeMapper;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureTypeGroupController;
use OCA\VTramit\Controller\SignatureTypeController;
use OCA\VTramit\Controller\SignatureButtonController;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * PU061
 */
class SignatureButtonCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";

    private string $procedureTypeGroupName = "";

    /** @var SignatureButtonService Service for signature button operations */
    private SignatureButtonService $signatureButtonService;

    /** @var SignatureButtonMapper Mapper for signature button database operations */
    private SignatureButtonMapper $signatureButtonMapper;

    /** @var ProcedureTypeController */
    private ProcedureTypeController $procedureTypeController;

    /** @var ProcedureTypeGroupController */
    private ProcedureTypeGroupController $procedureTypeGroupController;

    /** @var SignatureTypeController */
    private SignatureTypeController $signatureTypeController;

    /** @var ProcedureTypeService */
    private ProcedureTypeService $procedureTypeService;

    /** @var ProcedureTypeGroupService */
    private ProcedureTypeGroupService $procedureTypeGroupService;

    /** @var SignatureTypeService */
    private SignatureTypeService $signatureTypeService;

    /** @var ProcedureTypeMapper */
    private ProcedureTypeMapper $procedureTypeMapper;

    /** @var ProcedureTypeGroupMapper */
    private ProcedureTypeGroupMapper $procedureTypeGroupMapper;

    /** @var ProcedureTypeProtocolStepMapper */
    private ProcedureTypeProtocolStepMapper $procedureTypeProtocolStepMapper;

    /** @var SignatureTypeMapper */
    private SignatureTypeMapper $signatureTypeMapper;

    /**
     * Constructor for the SignatureButton test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'signature-buttons-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('signature button');
        // Initialize mappers
        $this->procedureTypeMapper = new ProcedureTypeMapper($this->dbConnection);
        $this->procedureTypeGroupMapper = new ProcedureTypeGroupMapper($this->dbConnection);
        $this->procedureTypeProtocolStepMapper = new ProcedureTypeProtocolStepMapper($this->dbConnection);
        $this->signatureTypeMapper = new SignatureTypeMapper($this->dbConnection);

        // Initialize UserGroupHelper using mock
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);

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
    }

    /**
     * 
     * Steps:
     *
     *  1.- Create signature button correctly (CREATE)
     *  2.- Find signature button correctly using ID (FIND)
     *  3.- Find signature button in signature button existing entities list (FIND ALL)
     *  4.- Update signature button correctly (UPDATE)
     *  5.- Delete signature button correctly (DELTE)
     * 
     */
    public function testCRUD(): void {

        $this->createTestRequiredData();

        $procedureTypeId = $this->createdEntities[get_class($this->procedureTypeController)][0];
        $signatureTypeId = $this->createdEntities[get_class($this->signatureTypeController)][0];

        $initalData = $this->setAssociatedIdsToSignatureButtons(TestData::$signatureButtonTestEntityData, $procedureTypeId, $signatureTypeId, $this->procedureTypeGroupName);
        $updateData = $this->setAssociatedIdsToSignatureButtons(TestData::$updatedSignatureButtonTestEntityData, $procedureTypeId, $signatureTypeId, $this->procedureTypeGroupName);

        // Crear signature button correctamente (CREATE)
        // Encontrar signature button indicando el ID (FIND)
        // Encontrar signature button al listar todos los procedures (FIND ALL)
        // Actualizar signature button correctamente (UPDATE)
        // Eliminar signature button correctamente (DELETE)
        $this->runCRUDTest($initalData, $updateData);
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
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

        // Create signature button controller
        $this->controller = new SignatureButtonController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->signatureButtonService,
            $this->userId
        );
    }

    /**
     * Checks response data against expected values
     *
     * @param array $expectedData Expected signature button data
     * @param array $actualData Actual signature button data from response
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
            $expectedData['signatureTypeId'],
            $actualData['signatureTypeId'],
            'Signature type ID mismatch'
        );

        $this->assertEquals(
            $expectedData['mailEnabled'],
            $actualData['mailEnabled'],
            'Mail enabled flag mismatch'
        );

        $this->assertEquals(
            $expectedData['mailTemplateId'],
            $actualData['mailTemplateId'],
            'Mail template ID mismatch'
        );

        // Handle label comparison (account for JSON encoding/decoding)
        $expectedLabel = json_decode($expectedData['label'], true);

        if ($expectedLabel !== null) {
            // Compare as decoded arrays
            $this->assertEquals(
                $expectedLabel,
                $actualData['label'],
                'Label content mismatch'
            );
        } else {
            // Fallback to string comparison if JSON is invalid
            $this->assertEquals(
                $expectedData['label'],
                json_encode($actualData['label']),
                'Label string mismatch'
            );
        }
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {

        // Save procedure type response
        $responseProcedureType = $this->create($this->procedureTypeController, TestData::$procedureTypeTestEntityData);

        // Set the procedure type ID to the procedure type group entity
        $procedureTypeGroupTestEntity = TestData::$procedureTypeGroupTestEntityData;
        $procedureTypeGroupTestEntity["procedureTypeId"] = $responseProcedureType["entity"]->id;
        // Save procedure type group response
        $responseProcedureTypeGroup = $this->create($this->procedureTypeGroupController, $procedureTypeGroupTestEntity);
        // Save procedure type group name
        $this->procedureTypeGroupName = $responseProcedureTypeGroup["entity"]->getGroup();
        // Save signature type response
        $responseSignatureType = $this->create($this->signatureTypeController, TestData::$signatureTypeTestEntityData);

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
            case 'OCA\VTramit\Controller\SignatureTypeController':
                return $this->signatureTypeController;
            default:
                return $this->controller;
        }
    }
}
