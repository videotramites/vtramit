<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit;

/**
 * Test suite definitions for PHPUnit
 * 
 * This allows organizing tests into logical groups.
 * To run a specific suite: 
 * vendor/bin/phpunit --bootstrap test/phpunit/bootstrap.php --testsuite=core
 */
class TestSuites
{
    /**
     * Core entities test suite
     * Tests for fundamental data entities
     */
    public static $coreSuite = [
        'test/phpunit/config/*.php',
        'test/phpunit/context/*.php',
        'test/phpunit/entity/*.php',
    ];
    
    /**
     * Procedure entities test suite
     * Tests related to procedures and their components
     */
    public static $procedureSuite = [
        'test/phpunit/procedure-type/*.php',
        'test/phpunit/procedure-type-group/*.php',
    ];
    
    /**
     * Authentication and security test suite
     * Tests related to signatures, RGPD, etc.
     */
    public static $securitySuite = [
        'test/phpunit/signature-button/*.php',
        'test/phpunit/signature-type/*.php',
        'test/phpunit/rgpd-activity/*.php',
        'test/phpunit/rgpd-options/*.php',
    ];
    
    /**
     * Communication test suite
     * Tests related to email, webhooks, etc.
     */
    public static $communicationSuite = [
        'test/phpunit/mail-queue/*.php',
    ];
    
    /**
     * Get all test suites as a configuration array
     * 
     * @return array PHPUnit suite configuration
     */
    public static function getAllSuites(): array {
        return [
            'core' => self::$coreSuite,
            'procedure' => self::$procedureSuite,
            'security' => self::$securitySuite,
            'communication' => self::$communicationSuite,
            'all' => array_merge(
                self::$coreSuite,
                self::$procedureSuite,
                self::$securitySuite,
                self::$communicationSuite
            ),
        ];
    }
}