<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Config;

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

/**
 * (PU002)
 */
class ConfigServiceTest extends CommonTestOperations
{

    private string $testFunction = "testConfigService";

    private array $fakeDetailedAllowedLanguages = [
        'en'=> 'English',
        'es'=> 'Spanish',
    ];

    /** @var ConfigurationController */
    private $configurationController;

    /** @var ConfigurationApiController */
    private $configurationApiController;

    /**
     * Set up the controller to test
     */
    public function __construct() {
        $testName = 'config-service-test';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('config');

        // Setting fake config data
        $fakeSettings = [
            'files.output.autodownload'=> ['false', 0],
            'signature.hide'=> false,
            'uploads.hide'=> false,
            'downloads.hide'=> false,
            'password.hide'=> false,
            'text.future'=> '',
            'text.expired'=> '',
            'logo.login'=> '',
            'logo.url'=> '',
            'documentation.url'=> '',
        ];

        $this->systemConfigValues['vtramit.public.settings'] = $fakeSettings;

        $this->initializeConfigService();
    }

    /**
     * 
     * Steps:
     *
     *  1.- Call index() method in ConfigService
     *  2.- Call update() method in ConfigService
     *  3.- Call getPublicSignatureHide() method in ConfigService
     *  4.- Call getPublicUploadsHide() method in ConfigService
     *  5.- Call getPublicDownloadsHide() method in ConfigService
     *  6.- Call getPublicTextFuture() method in ConfigService
     *  7.- Call getPublicTextExpired() method in ConfigService
     *  8.- Call getPublicLogoLogin() method in ConfigService
     *  9.- Call getPublicLogoUrl() method in ConfigService
     *  10.- Call getPublicDocumentationUrl() method in ConfigService
     *  11.- Call getPublicOutputAutodownload() method in ConfigService
     *  12.- Call getMode() method in ConfigService
     *  13.- Call getResultsPerPage() method in ConfigService
     *  14.- Call getDateFormat() method in ConfigService
     *  15.- Call getTimeFormat() method in ConfigService
     *  16.- Call getAdminRoot() method in ConfigService
     *  17.- Call getTrashRoot() method in ConfigService
     *  18.- Call isAllowSendEmails() method in ConfigService
     *  19.- Call getDetailedAllowedLanguages() method in ConfigService
     * 
     */
    public function testConfigService(): void
    {
        $configTestData = [
            "languages" => ["en", "es"],
            "resultsPerPage" => "25",
            "filterOption" => [
                "status" => true
            ]
        ];

        // Call index() method in ConfigService
        $this->findAllConfigurations();

        // Call update() method in ConfigService
        $this->updateConfigurations($configTestData);
        $this->checkUpdatedConfigurations($configTestData);

        // Mock settigs value
        $fakeSettings = [
            'files.output.autodownload'=> false,
            'signature.hide'=> false,
            'uploads.hide'=> false,
            'downloads.hide'=> false,
            'password.hide'=> false,
            'text.future'=> '',
            'text.expired'=> '',
            'logo.login'=> '',
            'logo.url'=> '',
            'documentation.url'=> '',
        ];

        $this->systemConfigValues['vtramit.public.settings'] = $fakeSettings;
        $this->systemConfigValues['vtramit.mode'] = "test";
        $this->systemConfigValues['vtramit.timeFormat'] = "H:i";
        $this->systemConfigValues['vtramit.mails.allowed'] = true;
        $this->systemConfigValues['vtramit.admin'] = "admin";

        $this->initializeConfigService();

        $values = [];

        // Call getPublicSignatureHide() method in ConfigService
        $values['publicSignatureHide']  = $this->configService->getPublicSignatureHide();
        $this->assertSame($fakeSettings['signature.hide'], $values['publicSignatureHide']);

        // Call getPublicUploadsHide() method in ConfigService
        $values['publicUploadsHide']    = $this->configService->getPublicUploadsHide();
        $this->assertSame($fakeSettings['uploads.hide'], $values['publicUploadsHide']);

        // Call getPublicDownloadsHide() method in ConfigService
        $values['publicDownloadsHide']  = $this->configService->getPublicDownloadsHide();
        $this->assertSame($fakeSettings['downloads.hide'], $values['publicDownloadsHide']);

        // Call getPublicPasswordHide() method in ConfigService
        $values['publicPasswordHide']   = $this->configService->getPublicPasswordHide();
        $this->assertSame($fakeSettings['password.hide'], $values['publicPasswordHide']);

        // Call getPublicTextFuture() method in ConfigService
        $values['publicTextFuture']     = $this->configService->getPublicTextFuture();
        $this->assertSame($fakeSettings['text.future'], $values['publicTextFuture']);

        // Call getPublicTextExpired() method in ConfigService
        $values['publicTextExpired']    = $this->configService->getPublicTextExpired();
        $this->assertSame($fakeSettings['text.expired'], $values['publicTextExpired']);

        // Call getPublicLogoLogin() method in ConfigService
        $values['publicLogoLogin']      = $this->configService->getPublicLogoLogin();
        $this->assertSame($fakeSettings['logo.login'], $values['publicLogoLogin']);

        // Call getPublicLogoUrl() method in ConfigService
        $values['publicLogoUrl']        = $this->configService->getPublicLogoUrl();
        $this->assertSame($fakeSettings['logo.url'], $values['publicLogoUrl']);

        // Call getPublicDocumentationUrl() method in ConfigService
        $values['publicDocumentationUrl'] = $this->configService->getPublicDocumentationUrl();
        $this->assertSame($fakeSettings['documentation.url'], $values['publicDocumentationUrl']);

        // Call getPublicOutputAutodownload() method in ConfigService
        $values['publicOutputAutodownload'] = $this->configService->getPublicOutputAutodownload();
        $this->assertSame($fakeSettings['files.output.autodownload'], $values['publicOutputAutodownload']);

        // Call getMode() method in ConfigService
        $mode = $this->configService->getMode();
        $this->assertSame( $this->systemConfigValues['vtramit.mode'], $mode);

        // Call getResultsPerPage() method in ConfigService
        $resultsPerPage = $this->configService->getResultsPerPage();
        $this->assertSame($this->appConfigValues['vtramit:resultsPerPage'], $resultsPerPage);

        // Call getDateFormat() method in ConfigService
        $dateFormat = $this->configService->getDateFormat();
        $this->assertSame($this->appConfigValues['vtramit:dateFormat'], $dateFormat);

        // Call getTimeFormat() method in ConfigService
        $timeFormat = $this->configService->getTimeFormat();
        $this->assertSame($this->appConfigValues['vtramit:timeFormat'], $timeFormat);

        // Call getAdminRoot() method in ConfigService
        $adminRoot  = $this->configService->getAdminRoot();
        $this->assertSame("admin/files/", $adminRoot);

        // Call getTrashRoot() method in ConfigService
        $trashRoot = $this->configService->getTrashRoot();
        $this->assertSame("admin/files/", $adminRoot);

        // Call isAllowSendEmails() method in ConfigService
        $allowSendEmails = $this->configService->isAllowSendEmails();
        $this->assertTrue($allowSendEmails);

        $this->appConfigValues['vtramit:mailsAllowed'] = '1';
        $this->initializeConfigService();

        // Call getDetailedAllowedLanguages() method in ConfigService
        $detailedAllowedLanguages=$this->configService->getDetailedAllowedLanguages();
        $this->assertSame($this->fakeDetailedAllowedLanguages, $detailedAllowedLanguages);

    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Create configuration controller
        $this->configurationController = new ConfigurationController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->userId
        );

        // Create configuration API controller
        $this->configurationApiController = new ConfigurationApiController(
            'vtramit',
            $this->request,
            $this->logger,
            $this->configService,
            $this->userId
        );
    }


    /**
     * Finds all configurations and verifies the response.
     */
    public function findAllConfigurations(): void {

        // Get response from the controller
        $responseData = $this->processControllerResponse($this->configurationController->index());

        // Save endpoint output into log file
        $this->logResponseData('FIND ALL CONFIGURATIONS', $responseData);

        // Check if result is OK
        $this->assertEquals('OK', $responseData['result']);

        // Verify the configuration data is available
        $this->assertArrayHasKey('configuration', $responseData);
        $this->assertArrayHasKey('languages', $responseData['configuration']);
        $this->assertArrayHasKey('resultsPerPage', $responseData['configuration']);
        $this->assertArrayHasKey('filterOption', $responseData['configuration']);
    }

    /**
     * Updates configurations and verifies the response.
     *
     * @param array $configTestData  test configuration data
     */
    public function updateConfigurations(array $configTestData): void {
        // Setup mock expectations for the update
        $this->config->expects($this->atLeastOnce())
            ->method('setAppValue')
            ->willReturn(null);

        // Get response from controller
        $responseData = $this->processControllerResponse($this->configurationController->update($configTestData));

        // Save endpoint output into log file
        $this->logResponseData('UPDATE CONFIGURATIONS', $responseData);

        // Check if result is OK
        $this->assertEquals('OK', $responseData['result']);
    }

    /**
     * Verify that configurations were properly updated.
     *
     * @param array $configTestData  test configuration data
     */
    protected function checkUpdatedConfigurations(array $configTestData): void {

        // Initialize mocks
        $this->initializeConfigService();

        // Create configuration controller
        $this->setUpController();

        // Get configuration data after update
        $responseData = $this->processControllerResponse($this->configurationController->index());

        // Save endpoint output into log file
        $this->logResponseData('CHECK UPDATED CONFIGURATIONS', $responseData);
        
        // Verify data
        $this->assertEquals('OK', $responseData['result']);
        $this->assertEquals($configTestData['languages'], $responseData['configuration']['languages']);
        $this->assertEquals($configTestData['resultsPerPage'], $responseData['configuration']['resultsPerPage']);
        $this->assertEquals($configTestData['filterOption'], $responseData['configuration']['filterOption']);
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