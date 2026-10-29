<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Context;

use PHPUnit\Framework\TestCase;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Controller\ContextController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\ContextApiController;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ContextService;
use OCA\VTramit\Helper\UserGroupHelper;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * (PU003)
 */
class ContextServiceTest extends CommonTestOperations
{
    private string $testFunction = "testContextService";

    /** @var ContextController */
    private $contextController;

    /** @var ContextApiController */
    private $contextApiController;

    /** @var ContextService */
    private $contextService;

    /** @var ProcedureTypeGroupMapper */
    private $procedureTypeGroupMapper;

    /** @var UserGroupHelper */
    private $userGroupHelper;

    /**
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'context-service-test';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('context');

        $this->systemConfigValues['vtramit.group.filter'] = $this->allowedGroups;
        $this->systemConfigValues['vtramit.phone.link'] = 'tel:123456789';

        $this->initializeConfigService();


        $this->configService = new ConfigService(
            $this->config,
            $this->translate,
            $this->groupManager
        );
    }

    /**
     * Steps:
     *
     *  1.- Test getting the groups list
     *  2.- Test getting the procedure status list
     *  3.- Test getting the procedure channels list
     *  4.- Test getting the supported language list
     *  5.- Test getting the message language list
     *  6.- Test getting the app context
     * 
     */
    public function testContextService(): void {
        // Test getting the groups list
        $this->testGetGroupsList();

        // Test getting the procedure status list
        $this->testGetProcedureStatusList();

        // Test getting the procedure channels list
        $this->testGetAttendanceChannelList();

        // Test getting the supported language list
        $this->testGetSupportedLanguageList();

        // Test getting the message language list
        $this->testGetMessageChannelsList();

        // Test getting the app context
        $this->testGetAppContext();
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Initialize procedureTypeGroupMapper mock
        $this->procedureTypeGroupMapper = $this->createMock(ProcedureTypeGroupMapper::class);
        $this->procedureTypeGroupMapper->method('findAllGroups')->willReturn($this->allowedGroups);

        // Initialize UserGroupHelper mock
        $this->userGroupHelper = $this->createMock(UserGroupHelper::class);
        $this->userGroupHelper->method('getAllGroups')->willReturn($this->allowedGroups);


        // Create context service
        $this->contextService = new ContextService(
            $this->logger,
            $this->translate,
            $this->procedureTypeGroupMapper,
            $this->userGroupHelper,
            $this->configService
        );

        // Create context controller
        $this->contextController = new ContextController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->contextService,
            $this->userId
        );

        // Create context API controller
        $this->contextApiController = new ContextApiController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->contextService,
            $this->userId
        );
    }

    /**
     * Tests getting the groups list.
     */
    protected function testGetGroupsList(): void {

        // Get response
        $response = $this->contextService->getGroupsList();

        // Save endpoint output into log file
        $this->logResponseData('GET GROUPS LIST', $response->getData());

        // Check response is a ResponseInfo object
        $this->assertInstanceOf(ResponseInfo::class, $response);

        // Check that the response contains the list of groups
        $responseData = $response->getData();
        $this->assertArrayHasKey('list', $responseData);

        // Verify the correct format of the groups
        $expectedGroups = [];
        foreach ($this->allowedGroups as $group) {
            $expectedGroups[] = ['key' => $group, 'value' => $group];
        }
        $this->assertEquals($expectedGroups, $responseData['list']);
    }

    /**
     * Tests getting the procedure status list.
     */
    protected function testGetProcedureStatusList(): void {
        // Get response
        $response = $this->contextService->getProcedureStatusList();

        // Save endpoint output into log file
        $this->logResponseData('GET PROCEDURE STATUS LIST', $response->getData());

        // Check response is a ResponseInfo object
        $this->assertInstanceOf(ResponseInfo::class, $response);

        // Check that the response contains the list of statuses
        $responseData = $response->getData();
        $this->assertArrayHasKey('list', $responseData);

        // Verify the status list is not empty
        $this->assertNotEmpty($responseData['list']);
    }

    /**
     * Tests getting the procedure channels list.
     */
    protected function testGetAttendanceChannelList(): void {
        // Get response
        $response = $this->contextService->getAttendanceChannelList();

        // Save endpoint output into log file
        $this->logResponseData('GET PROCEDURE CHANNELS LIST', $response->getData());

        // Check response is a ResponseInfo object
        $this->assertInstanceOf(ResponseInfo::class, $response);

        // Check that the response contains the list of channels
        $responseData = $response->getData();
        $this->assertArrayHasKey('list', $responseData);

        // Verify the channels list is not empty
        $this->assertNotEmpty($responseData['list']);
    }

        /**
     * Tests getting the messages channel list.
     */
    protected function testGetMessageChannelsList(): void {
        // Get response
        $response = $this->contextService->getMessageChannels();

        // Save endpoint output into log file
        $this->logResponseData('GET MESSAGE CHANNELS LIST', $response->getData());

        // Check response is a ResponseInfo object
        $this->assertInstanceOf(ResponseInfo::class, $response);

        // Check that the response contains the list of languages
        $responseData = $response->getData();
        $this->assertArrayHasKey('list', $responseData);

        // Verify the languages list is not empty
        $this->assertNotEmpty($responseData['list']);
    }

    /**
     * Tests getting the supported language list.
     */
    protected function testGetSupportedLanguageList(): void {
        // Get response
        $response = $this->contextService->getSupportedLanguageList();

        // Save endpoint output into log file
        $this->logResponseData('GET SUPPORTED LANGUAGE LIST', $response->getData());

        // Check response is a ResponseInfo object
        $this->assertInstanceOf(ResponseInfo::class, $response);

        // Check that the response contains the list of languages
        $responseData = $response->getData();
        $this->assertArrayHasKey('list', $responseData);

        // Verify the languages list is not empty
        $this->assertNotEmpty($responseData['list']);
    }

    /**
     * Tests getting the app context.
     */
    protected function testGetAppContext(): void {

        $mockUsers = [
            'user1' => 'User One',
            'user2' => 'User Two'
        ];
        $mockTimezone = new \DateTimeZone('Europe/Berlin');

        // Mock findAllGroups to return all available groups
        $this->procedureTypeGroupMapper->method('findAllGroups')
            ->willReturn($this->allowedGroups);

        // Mock getAllowedGroupsForUser to return groups for the user
        $this->userGroupHelper->method('getAllowedGroupsForUser')
            ->with($this->userId, $this->allowedGroups)
            ->willReturn($this->allowedGroups);

        // Mock getAssignableUsersByGroup to return users for the group
        $this->userGroupHelper->method('getAssignableUsersByGroup')
            ->willReturn($mockUsers);

        // Mock isAdmin to return true
        $this->userGroupHelper->method('isAdmin')
            ->with($this->userId)
            ->willReturn(true);

        // Get response
        $response = $this->contextService->getAppContext($this->userId);

        // Save endpoint output into log file
        $this->logResponseData('GET APP CONTEXT', $response->getData());

        // Check response is a ResponseInfo object
        $this->assertInstanceOf(ResponseInfo::class, $response);

        // Check that the response contains the expected keys
        $responseData = $response->getData();
        $this->assertArrayHasKey('timezone', $responseData);
        $this->assertArrayHasKey('groups', $responseData);
        $this->assertArrayHasKey('users', $responseData);
        $this->assertArrayHasKey('isAdmin', $responseData);
        $this->assertArrayHasKey('phoneLink', $responseData);

        // Verify timezone
        $this->assertEquals('Europe/Berlin', $responseData['timezone']);

        // Verify isAdmin
        $this->assertTrue($responseData['isAdmin']);

        // Verify phoneLink
        $this->assertEquals($this->appConfigValues['vtramit:phoneLink'], $responseData['phoneLink']);
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