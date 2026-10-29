<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use OCA\VTramit\Controller\EntityController;
use OCP\AppFramework\Http\DataResponse;

/**
 * Base test class providing common testing functionality
 */
abstract class TestBase extends TestCase
{
    protected static string $runId;

    protected static string $appName = 'vtramit';

    /** @var string Default user ID for testing */
    public string $userId;

    public string $testEmail;


    /** @var string Name of the test, used for logs */
    public string $testName;


    /** @var LoggerInterface Logger interface */
    public LoggerInterface $logger;

    /** @var bool Whether to enable logging */
    protected bool $loggingEnabled = true;

    private string $logFilePath;

    /**
     * Constructor for test base class
     *
     * @param string $testName The test name for logging purposes
     */
    public function __construct(string $testName, string $testFunction = '') {
        parent::__construct($testFunction);

        if (!isset(self::$runId)) {
            self::$runId = date('Ymd_His');
        }

        $this->testName = ltrim($testName, '/');
        $this->testEmail = getenv('TEST_EMAIL');
        //$this->assertMatchesRegularExpression('/@example\.com$/',$this->testEmail);
        $this->userId = getenv('ADMIN_USER');
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    protected function getRunId(): string {
        return self::$runId;
    }

    /**
     * Generate a unique test name if none provided
     *
     * @return string Generated test name
     */
    protected function generateTestName(): string {
        $className = (new \ReflectionClass($this))->getShortName();
        return sprintf('/%s_%s', strtolower($className), time());
    }

    /**
     * Enable or disable logging
     *
     * @param bool $enabled Whether logging should be enabled
     * @return void
     */
    protected function setLoggingEnabled(bool $enabled): void {
        $this->loggingEnabled = $enabled;
    }

    /**
     * Returns the absolute path of the log directory.
     *
     * This method ensures that the directory `phpunit-logs` exists relative
     * to the current directory. If the directory does not exist, it will be
     * created with full permissions (0770). If the real path cannot be resolved,
     * the method will return null.
     *
     * @return string|null
     *   The resolved absolute path of the log directory, or null if it cannot be resolved.
     */
    protected function getLogDirectoryPath(): ?string {
        $logDir = __DIR__ . '/../phpunit-logs/' . self::$runId;

        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0770, true) && !is_dir($logDir)) {
                return null;
            }
        }

        return realpath($logDir) ?: null;
    }

    /**
     * Returns the absolute path of the log file for the current test.
     *
     * This method constructs the log file path based on the test name and the
     * directory returned by getLogDirectoryPath(). If the log file does not
     * exist, it will be created with read/write permissions (0666).
     * If the log directory cannot be resolved or file creation fails, the method
     * will return null.
     *
     * @return string|null
     *   The absolute path of the log file, or null if the directory cannot be resolved
     *   or the file cannot be created.
     */
    protected function getLogFilePath(): ?string {
        if (isset($this->logFilePath)) {
            if ($this->logFilePath === '') {
                return null;
            }

            return $this->logFilePath;
        }

        // Set non-valid value, but required to avoid executing the file creation again
        $this->logFilePath = '';

        $logDir = $this->getLogDirectoryPath();
        if ($logDir === null) {
            return null;
        }

        $logFilePath = $logDir . '/' . $this->testName . '.log';

        // Ensure the file exists and has proper permissions
        if (!file_exists($logFilePath)) {
            if (!touch($logFilePath)) {
                return null; // Failed to create the file
            }
            if (!chmod($logFilePath, 0660)) {
                return null; // Failed to set permissions
            }
        }

        $this->logFilePath = $logFilePath;
        return $this->logFilePath;
    }

    /**
     * Writes a log entry to the test log file.
     *
     * This method handles the log file retrieval, content writing, and optional verbose output.
     * If the log file cannot be created or accessed, the method will silently fail.
     *
     * @param string $content The log content to write.
     * @param bool $verbose Whether to print confirmation message (default: false).
     * @return void
     */
    protected function writeLogEntry(string $content, bool $verbose = false): void {
        if (!$this->loggingEnabled) {
            return;
        }

        $logFilePath = $this->getLogFilePath();
        if ($logFilePath === null) {
            return;
        }

        try {
            file_put_contents($logFilePath, $content, FILE_APPEND);

            if ($verbose) {
                echo PHP_EOL . 'Log entry saved to: ' . realpath($logFilePath);
            }
        } catch (\Exception $e) {
            echo PHP_EOL . 'Error writing log entry: ' . $e->getMessage();
        }
    }

    /**
     * Save response data to log file for debugging.
     *
     * This method formats the response data as JSON and writes it to the log file,
     * including a timestamp, action, and backtrace information.
     *
     * @param string $action The action being tested.
     * @param array $response The response data to log.
     * @param bool $verbose Whether to print confirmation message (default: true).
     * @return void
     */
    protected function logResponseData(string $action, array $response, bool $verbose = true): void {
        $jsonString = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        $timestamp = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backtrace[1]) ? basename($backtrace[1]['file']) . ':' . $backtrace[1]['line'] : 'unknown';

        $logEntry = PHP_EOL
            . '[' . $timestamp . '] [' . $action . '] [' . $caller . ']' . PHP_EOL
            . $jsonString . PHP_EOL
            . '--------------------------------' . PHP_EOL;

        $this->writeLogEntry($logEntry, $verbose);
    }

    /**
     * Log a simple message to the test log.
     *
     * This method writes a timestamped message to the log file for informational purposes.
     *
     * @param string $message The message to log.
     * @param bool $verbose Whether to print confirmation message (default: false).
     * @return void
     */
    protected function logMessage(string $message, bool $verbose = false): void {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = PHP_EOL . '[' . $timestamp . '] [INFO] ' . $message . PHP_EOL;

        $this->writeLogEntry($logEntry, $verbose);
    }

    /**
     * Assert that a response has status OK
     *
     * @param array $response The response to check
     * @param string $message Optional failure message
     */
    protected function assertResponseOk(array $response, string $message = ''): void {
        $defaultMessage = 'Response does not have OK status';
        $message = $message ?: $defaultMessage;

        if (isset($response['message'])) {
            $message .= ': ' . (is_array($response['message'])
                ? implode(', ', $response['message'])
                : $response['message']);
        }

        $this->assertEquals('OK', $response['result'], $message);
    }

    /**
     * Assert that a response has error status
     *
     * @param array $response The response to check
     * @param string $expectedError Optional expected error substring
     * @param string $message Optional failure message
     */
    protected function assertResponseError(array $response, string $expectedError = '', string $message = ''): void {
        $defaultMessage = 'Response does not have ERROR status';
        $message = $message ?: $defaultMessage;

        $this->assertNotEquals('OK', $response['result'], $message);

        if ($expectedError && isset($response['message'])) {
            $responseMessage = is_array($response['message'])
                ? implode(', ', $response['message'])
                : $response['message'];

            $this->assertStringContainsString(
                $expectedError,
                $responseMessage,
                'Error message does not contain expected text'
            );
        }
    }

    /**
     * Assert that a response contains an entity
     *
     * @param array $response The response to check
     * @param string $message Optional failure message
     */
    protected function assertResponseHasEntity(array $response, string $message = 'Response does not contain an entity'): void {
        $this->assertArrayHasKey('entity', $response, $message);
        $this->assertNotNull($response['entity'], $message);
    }

    /**
     * Assert that a response contains a list of entities
     *
     * @param array $response The response to check
     * @param int $minCount Minimum expected count (default: 1)
     * @param string $message Optional failure message
     */
    protected function assertResponseHasList(array $response, int $minCount = 1, string $message = ''): void {
        $defaultMessage = 'Response does not contain a list of entities';
        $message = $message ?: $defaultMessage;

        $this->assertArrayHasKey('list', $response, $message);
        $this->assertIsArray($response['list'], $message);

        if ($minCount > 0) {
            $this->assertGreaterThanOrEqual(
                $minCount,
                count($response['list']),
                $message . ': Expected at least ' . $minCount . ' items'
            );
        }
    }
}