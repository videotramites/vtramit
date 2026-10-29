<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\ActivityManager;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDBConnection;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Controller\ConfigurationController;
use OCA\VTramit\Controller\ConfigurationApiController;
use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Service\ActivityManager;
use OCA\VTramit\Test\Phpunit\TestEntityServiceBase;
use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;
use OCP\Activity\IManager;
use OCP\IUserSession;

/**
 * (PU001)
 */
class ActivityManagerReportActivityErrorTest extends CommonTestOperations
{

    private string $testFunction = "testActivityManagerError";


    /** @var ActivityManager */
    private ActivityManager $activiyManagerTest;

    /** @var IManager */
    private IManager $managerTest;

    /**
     * Constructor for the test
     */
    public function __construct() {
        $testName = 'activity-manager-report-activity-error-test';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('activity manager');
    }

    /**
     *
     * Steps:
     *
     *  1.-Calls reportActivity forcing the method setApp in to throw InvalidArgumentException in $event->setApp.
     * 
     **/
    public function testActivityManagerError(): void {

        $this->activiyManagerTest->reportActivity('TEST', 1, 'TEST', 'TEST');

        $this->logMessage("ACTIVITY MANAGER EXCEPCION SUCCESSFULLY THROWN");
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        $user = $this->createMock(IUserSession::class);

        // Force InvalidArgumentException throw when $event->setApp si called
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->isInstanceOf(\InvalidArgumentException::class),
                $this->arrayHasKey('app')
            );

        $this->managerTest = $this->createMock(IManager::class);
        $this->managerTest->method('generateEvent')->willReturn(new FakeFailingEvent());

        // Initializate ActivityManager
        $this->activiyManagerTest = new ActivityManager(
            $this->logger,
            $this->managerTest,
            $this->configService,
            $user
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