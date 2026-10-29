<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit;

use OCP\IRequest;
use OCA\VTramit\Service\ConfigService;
use PHPUnit\Framework\TestCase;
use OCA\VTramit\Controller\EntityController;
use OCP\IL10N;
use OCP\IConfig;
use OCP\IGroupManager;
use OCA\VTramit\Service\ActivityManager;
use OCP\IDBConnection;
use OCP\AppFramework\Http\DataResponse;
use OCP\Activity\IManager;
use OCP\IUserSession;

/**
 * Base class for entity service tests providing common CRUD operations
 */
abstract class TestEntityServiceBase extends TestBase
{
    /** @var array Groups allowed for testing */
    public array $allowedGroups = ['IMH', 'OAC', 'TEST'];

    public array $systemConfigValues  = [
        'vtramit.phone.defaultCountryCode' => '34',
        'vtramit.phone.prefix'             => '+',
        'logtimezone'                      => 'Europe/Berlin',
        'vtramit.log.timer'                 => true,
        'vtramit.mode'=> 'test'
    ];

    public array $appConfigValues = [
        'vtramit:resultsPerPage' => 25,
        'vtramit:encryption_key' => '',
        'vtramit:languages' => ['en', 'es'],
        'vtramit:filterOption' => ["status" => true],
        'vtramit:dateFormat' => 'd/m/Y',
        'vtramit:timeFormat' => 'H:i',
        'vtramit:admin' => 'admin',
        'vtramit:mailsAllowed' => true,
        'vtramit:phoneLink' => 'ciscotel',
        'vtramit:phonePrefix' => '+',
        'vtramit:phoneDefaultCountryCode' => '34',
        'vtramit:filterOverdueDays' => '2'
    ];

    /** @var IConfig */
    public IConfig $config;

/** @var IGroupManager */
    public IGroupManager $groupManager;

    /** @var IRequest Mock request object */
    public IRequest $request;

    /** @var IL10N Translation service */
    public IL10N $translate;

    /** @var ActivityManager Activity manager service */
    public ActivityManager $activityManager;

    /** @var ConfigService Configuration service */
    public ConfigService $configService;

    /** @var IDBConnection Database connection */
    public IDBConnection $dbConnection;

    /** @var array Cache of created test entities for cleanup */
    protected array $createdEntities = [];

    /**
     * Constructor for entity service base class
     *
     * @param string $testName The test name for logging purposes
     */
    public function __construct(string $testName = '', string $testFunction = '') {
        parent::__construct($testName, $testFunction);
        $this->initializeMocks();
    }

   public function initializeConfigService(): void {
    // getSystemValue como ya tienes
    $this->config->method('getSystemValue')
        ->willReturnCallback(function ($key, $default = null) {
            return array_key_exists($key, $this->systemConfigValues)
                ? $this->systemConfigValues[$key]
                : $default;
        });


    // En tu test/base:
    $savedEncryptionKey = null;

    $this->config->method('getAppValue')
        ->willReturnCallback(function ($app, $key, $default = '') use (&$savedEncryptionKey) {
            // Caso especial: encryption_key
            if ($key === 'encryption_key') {
                static $first = true;
                if ($first) {
                    $first = false;
                    return ''; // 1ª llamada: fuerza generación
                }
                // siguientes: devuelve lo que se guardó (si hay)
                return $savedEncryptionKey ?? $default;
            }

            // Resto de claves: usa tu tabla appConfigValues "<app>:<key>"
            $compound = $app . ':' . $key;
            return array_key_exists($compound, $this->appConfigValues)
                ? $this->appConfigValues[$compound]
                : $default;
        });

    // Capturar lo que se guarda para encryption_key
    $this->config->method('setAppValue')
        ->willReturnCallback(function ($app, $key, $value) use (&$savedEncryptionKey) {
            if ($key === 'encryption_key') {
                $savedEncryptionKey = $value;
            }
            return null;
        });
        
    }

    /**
     * Initialize mock objects needed for testing
     */
    protected function initializeMocks(): void {

        $this->config = $this->createMock(IConfig::class);

        $this->initializeConfigService();


        $this->groupManager = $this->createMock(IGroupManager::class);

        $this->request = $this->createMock(IRequest::class);

        $this->translate = $this->createMock(IL10N::class);
        // Ensure `t()` returns the same string (avoids null returns)
        $this->translate->method('t')->willReturnCallback(fn ($string) => $string);

                // Initialize mocks
        $manager = $this->createMock(IManager::class);
        $user = $this->createMock(IUserSession::class);

        // $this->activityManager->method('triggerEvent')->willReturn(true);

        //$this->configService = $this->createMock(ConfigService::class);
        // Create config service
        $this->configService = new ConfigService(
            $this->config,
            $this->translate,
            $this->groupManager
        );

        //$this->configService->method('getAllowedGroups')->willReturn($this->allowedGroups);

        // Create configuration controller
        $this->activityManager = new ActivityManager(
            $this->logger,
            $manager,
            $this->configService,
            $user
        );

        // Connect to Nextcloud DB
        try {
            $this->dbConnection = \OC::$server->getDatabaseConnection();
        } catch (\Exception $e) {
            $this->markTestSkipped('Cannot connect to database: ' . $e->getMessage());
        }
    }

    /**
     * Create an entity using the controller
     *
     * @param EntityController $controller The controller to use
     * @param array $data The entity data
     * @param bool $trackForCleanup Whether to track this entity for cleanup
     * @return array Response data
     */
    public function create(EntityController $controller, array $data, bool $trackForCleanup = true): array {
        $response = $this->processControllerResponse($controller->create($data));

        // Track created entity for cleanup if requested
        if ($trackForCleanup && isset($response['result']) && isset($response['entity'])) {
            $controllerClass = get_class($controller);
            if (!isset($this->createdEntities[$controllerClass])) {
                $this->createdEntities[$controllerClass] = [];
            }
            $this->createdEntities[$controllerClass][] = $response['entity']->id;
        }

        return $response;
    }

    /**
     * Find an entity by ID
     *
     * @param EntityController $controller The controller to use
     * @param int $entityId The entity ID to find
     * @return array Response data
     */
    public function find(EntityController $controller, int $entityId): array {
        return $this->processControllerResponse($controller->find($entityId));
    }

    /**
     * Find all entities
     *
     * @param EntityController $controller The controller to use
     * @return array Response data with list of entities
     */
    public function findAll(EntityController $controller): array {
        return $this->processControllerResponse($controller->index());
    }

    /**
     * Update an entity
     *
     * @param EntityController $controller The controller to use
     * @param array $data The updated entity data
     * @return array Response data
     */
    public function update(EntityController $controller, array $data): array {
        return $this->processControllerResponse($controller->update($data));
    }

    /**
     * Delete an entity
     *
     * @param EntityController $controller The controller to use
     * @param int $entityId The entity ID to delete
     * @return array Response data
     */
    public function delete(EntityController $controller, int $entityId): array {
        $response = $this->processControllerResponse($controller->delete($entityId));

        // Remove from tracked entities if it was tracked
        $controllerClass = get_class($controller);
        if (isset($this->createdEntities[$controllerClass])) {
            $this->createdEntities[$controllerClass] = array_filter(
                $this->createdEntities[$controllerClass],
                function ($id) use ($entityId) {
                    return $id !== $entityId;
                }
            );
        }

        return $response;
    }

    /**
     * Process controller response to ensure it's an array
     *
     * @param mixed $response Response from controller
     * @return array Processed response data
     */
    protected function processControllerResponse($response): array {
        if ($response instanceof DataResponse) {
            return $response->getData();
        }

        if (is_object($response) && method_exists($response, 'getData')) {
            return $response->getData();
        }

        if (is_array($response)) {
            return $response;
        }

        $this->logMessage('Warning: Unexpected response type: ' . gettype($response));
        return ['result' => 'ERROR', 'message' => 'Unexpected response type'];
    }

    /**
     * Extract entity from response
     *
     * @param array $response The response data
     * @return array The entity data
     * @throws \Exception If entity cannot be extracted
     */
    public function getEntity(array $response): array {
        if (!isset($response['entity'])) {
            throw new \Exception('Response does not contain an entity');
        }

        return json_decode(json_encode($response['entity'], JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Get entities from a list response
     *
     * @param array $response The response data
     * @return array Array of entities
     * @throws \Exception If list cannot be extracted
     */
    public function getEntityList(array $response): array {
        if (!isset($response['list']) || !is_array($response['list'])) {
            throw new \Exception('Response does not contain a list of entities');
        }

        return json_decode(json_encode($response['list'], JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

        /**
     * Clean up created test entities
     */
    protected function cleanupTestEntities(): void {

        $this->createdEntities = array_reverse($this->createdEntities);
        foreach ($this->createdEntities as $controllerClass => $entityIds) {
            if (empty($entityIds)) {
                continue;
            }

            $this->logMessage("Cleaning up " . count($entityIds) . " entities for controller: " . $controllerClass);

            try {
                $controller = $this->getControllerInstance($controllerClass);
                foreach (array_reverse($entityIds) as $entityId) {
                    try {
                        $response = $controller->delete($entityId)->getData();
                        $this->logMessage("Deleted entity with ID: " . $entityId);
                        $this->assertEquals("OK", $response["result"]);
                    } catch (\Exception $e) {
                        $this->logMessage("Failed to delete entity with ID: " . $entityId . " - " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                $this->logMessage("Could not instantiate controller for cleanup: " . $e->getMessage());
            }
        }

        // Clear the tracking array
        $this->createdEntities = [];
    }

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     * @throws \Exception If controller cannot be instantiated
     */
    protected function getControllerInstance(string $controllerClass = ''): EntityController {
        // This is a placeholder - child classes should override if cleanup is needed
        throw new \Exception("Method getControllerInstance must be implemented by child classes for cleanup");
    }

    /**
     * Creates test required data
     */
    abstract protected function createTestRequiredData(): void;

    /**
     * Set up before each test
     */
    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void {
        $this->cleanupTestEntities();
        parent::tearDown();
    }
}