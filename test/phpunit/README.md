# PHPUnit Tests

## Introduction
This directory contains comprehensive unit tests for the VTramit application's service layer. The tests focus on validating all functions in `lib/Service` simulating the API endpoints flow used by the frontend through the Controllers found in `lib/Controller`.

## Prerequisites
- PHP 8.1 or later
- Nextcloud environment
- Docker
- Database access for integration tests

## Test Framework

The test framework is built with a hierarchical approach:

1. `TestBase.php`: Base class with common testing utilities
   - Logging functionality
   - Custom assertions for API responses
   - Common test infrastructure

2. `TestEntityServiceBase.php`: Extends TestBase for entity service testing
   - Mock initialization
   - CRUD operations with controllers
   - Entity tracking for automatic cleanup
   - Error handling and response processing

3. `CommonTestOperations.php`: Standardized test framework
   - Generic Test operations
   - Consistent testing methodology
   - Easy extension for entity-specific validation

4. Entity-specific test classes: Implement specific test logic for each entity
   - Data providers for comprehensive test coverage
   - Entity-specific validation logic
   - Error case testing

5. `TestHelpers.php`: Utility functions for common testing needs
   - JSON comparison
   - Test data generation
   - Error message formatting

56. `TestData.php`: Test entities data

## Tests

All tests should extend from CommonTestOperations, where the operations done during tests are stored. The tests that extend from CommonTestOperations, have to implement these methods:

   - `setUpController()`: Initialize the controller for testing
   - `checkEntityData()`: Validate entity-specific fields
   - `createTestRequiredData()`: Create required data for test
   - `getControllerInstance()`: Get controller instances of test for cleanup


These methods secure the correct functionality of the test. There are 2 types of tests in the project:

 - CRUD tests
 - Additional actions tests

### CRUD tests

   CRUD tests are the tests focused on the CRUD actions (CREATE, FIND, FIND ALL, UPDATE, DELETE). In these tests we define the create and update info and pass it to the function runCRUDTest():


```php
<?php
/**
 * Tests for Rgpd Option CRUD operations
 */
class RgpdOptionCRUDTest extends CommonTestOperations
{

    private string $testFunction = "testCRUD";

    /** class variables.. */

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
     * Tests the CRUD functions (CREATE, FIND, FIND ALL, UPDATE, DELETE)
     */
    public function testCRUD(): void {

        $this->runCRUDTest(TestData::$rgpdOptionTestEntityData,TestData::$updatedRgpdOptionTestEntityData);
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Set controller
    }

    /**
     * Checks response data against expected values
     *
     * @param array $expectedData Expected rgpd option data
     * @param array $actualData Actual rgpd option data from response
     */
    protected function checkEntityData(array $expectedData, array $actualData): void {
        // Check all received information
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
      // List controller instances for clean up
    }
}
```

### Additional tests

The additional tests are tests not related to CRUD actions like search by filter, find by X, etc:

```php
<?php
class RgpdOptionFindByJitsiRoomCodeTest extends CommonTestOperations
{

    private string $testFunction = "testFindByJitsiRoomCode";

    /* class variables */

    /**
     * Constructor for the test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'rgpd-options-find-by-jitsi-room-code';
        parent::__construct($testName, $this->testFunction);
        $this->setEntityTypeName('rgpd option');

        // Set up necessary controllers, mappers & services
    }

    /**
     * Tests the findByJitsiRoomCode action
     */
    public function testFindByJitsiRoomCode(): void {

        $this->createTestRequiredData();

        $this->findByJitsiRoomCode($this->entity, $this->procedureJitsiRoomCode);
    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
        // Set up controller
    }

    /**
     * Checks rgpd option data against expected values
     *
     * @param array $expectedData Expected rgpd option data
     * @param array $actualData Actual rgpd option data from response
     */
    protected function checkEntityData(array $expectedData, array $actualData): void {
        // Check all received information
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {
        // Create required data
    }

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     */
    protected function getControllerInstance($controllerClass = ''): EntityController {
         // List controller instances for clean up
    }
}
```

## Running Tests

To run all tests using dockers, type:

```bash
make run-phpunit
```

To run all tests directly from your local installation, type:
```bash
vendor/bin/phpunit --bootstrap test/phpunit/bootstrap.php --configuration test/phpunit/phpunit.xml
```

You can view the results in the file `test/phpunit-results.xml`:

You can view the coverage results in the file `test/phpunit-coverage.xml`:
```bash
cat test/phpunit-coverage.txt
```

Coverage reports are be generated in the `test/phpunit-coverage/` directory.

## Test Logs

Tests generate detailed log files with information about each operation, located in `test/phpunit/logs/`. Each log entry includes:

- Timestamp
- Action performed
- Source file and line number
- Response data

## Best Practices

1. Use data providers for comprehensive test coverage
2. Test both valid and invalid scenarios
3. Keep tests independent - don't rely on state from other tests
4. Clean up created test data in tearDown methods
5. Use appropriate dependency injection and mocking
6. Add thorough assertions with meaningful error messages
7. Document test methods with PHPDoc comments

## Additional Resources
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Nextcloud Testing Documentation](https://docs.nextcloud.com/server/latest/developer_manual/app_development/testing.html)