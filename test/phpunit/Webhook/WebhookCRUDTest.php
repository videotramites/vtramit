<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Webhook;

use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCA\VTramit\Db\WebhookMapper;
use OCA\VTramit\Controller\WebhookController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\WebhookService;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * PU068
 */
class WebhookCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";


    /** @var WebhookService Service for webhook operations */
    private WebhookService $webhookService;

    /** @var WebhookMapper Mapper for webhook database operations */
    private WebhookMapper $webhookMapper;

    /**
     * Constructor for the Webhook test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'webhook-crud';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('webhook');
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Initialize mapper
        $this->webhookMapper = new WebhookMapper($this->dbConnection);

        // Create webhook service
        $this->webhookService = new WebhookService(
            $this->logger,
            $this->translate,
            $this->activityManager,
            $this->configService,
            $this->webhookMapper
        );

        // Create webhook controller
        $this->controller = new WebhookController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->webhookService,
            $this->userId
        );
    }

    /**
     * 
     * Steps:
     *
     *  1.- Create topic correctly (CREATE)
     *  2.- Find topic correctly using ID (FIND)
     *  3.- Find topic in topic existing entities list (FIND ALL)
     *  4.- Update topic correctly (UPDATE)
     *  5.- Delete topic correctly (DELTE)
     * 
     */
    public function testCRUD(): void {

        // Create topic correctly (CREATE)
        // Find topic correctly using ID (FIND)
        // Find topic in topic existing entities list (FIND ALL)
        // Update topic correctly (UPDATE)
        // Delete topic correctly (DELTE)
        $this->runCRUDTest(TestData::$webhookTestEntityData,TestData::$updatedWebhookTestEntityData);
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
            $expectedData['endpoint'],
            $actualData['endpoint'],
            'Endpoint mismatch'
        );

        $this->assertEquals(
            $expectedData['authType'],
            $actualData['authType'],
            'Auth Type mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['authTypeData']),
            json_encode($actualData['authTypeData']),
            'Auth Type Data mismatch'
        );

        $this->assertEquals(
            $expectedData['event'],
            $actualData['event'],
            'Event mismatch'
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