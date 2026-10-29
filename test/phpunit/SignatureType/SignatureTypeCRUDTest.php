<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\SignatureType;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCA\VTramit\Db\SignatureTypeMapper;
use OCA\VTramit\Controller\SignatureTypeController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * Tests for Signature Type CRUD operations
 */
class SignatureTypeCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";


    /** @var SignatureTypeController */
    private SignatureTypeController $signatureTypeController;

    /** @var SignatureTypeService */
    private SignatureTypeService $signatureTypeService;

    /** @var SignatureTypeMapper */
    private SignatureTypeMapper $signatureTypeMapper;

    /**
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'signature-type-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('signature type');
    }

    /**
     * 
     * Steps:
     *
     *  1.- Create signature type correctly (CREATE)
     *  2.- Find signature type correctly using ID (FIND)
     *  3.- Find signature type in signature type existing entities list (FIND ALL)
     *  4.- Update signature type correctly (UPDATE)
     *  5.- Delete signature type correctly (DELTE)
     * 
     */
    public function testCRUD(): void {


        // Create signature type correctly (CREATE)
        // Find signature type correctly using ID (FIND)
        // Find signature type in signature type existing entities list (FIND ALL)
        // Update signature type correctly (UPDATE)
        // Delete signature type correctly (DELTE)
        $this->runCRUDTest(TestData::$signatureTypeTestEntityData,TestData::$updatedSignatureTypeTestEntityData);
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Initialize mappers
        $this->signatureTypeMapper = new SignatureTypeMapper($this->dbConnection);

        // Create signature type service
        $this->signatureTypeService = new SignatureTypeService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->signatureTypeMapper
        );

        // Create signature type controller
        $this->controller = new SignatureTypeController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->signatureTypeService,
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
            $expectedData['requestLimit'],
            $actualData['requestLimit'],
            'Request Limit mismatch'
        );

        $this->assertEquals(
            $expectedData['filesPerRequest'],
            $actualData['filesPerRequest'],
            'Files Per Request mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['fileType']),
            json_encode($actualData['fileType']),
            'File Type mismatch'
        );

        $this->assertEquals(
            $expectedData['format'],
            $actualData['format'],
            'Format mismatch'
        );

        $this->assertEquals(
            $expectedData['method'],
            $actualData['method'],
            'Method mismatch'
        );

        $this->assertEquals(
            $expectedData['certifiedAuthority'],
            $actualData['certifiedAuthority'],
            'Certified Authority mismatch'
        );

        $this->assertEquals(
            $expectedData['certifiedAuthorityProxy'],
            $actualData['certifiedAuthorityProxy'],
            'Certified Authority Proxy mismatch'
        );

        $this->assertEquals(
            $expectedData['additionalInfo'],
            $actualData['additionalInfo'],
            'Additional Info mismatch'
        );

        $this->assertEquals(
            $expectedData['phoneRequired'],
            $actualData['phoneRequired'],
            'Phone required mismatch'
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
    protected function getControllerInstance(string $controllerClass = ''): EntityController {
        return $this->controller;
    }
}