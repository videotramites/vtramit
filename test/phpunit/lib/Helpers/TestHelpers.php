<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\Helpers;

/**
 * Collection of static helper methods for tests
 */
class TestHelpers
{

     /**
     * Get requird appoinment date for procedure.
     *
     * @return string The appointment date value
     */
    public static function getRequiredAppointmentDate(): string {
        // Set one more day for the appoiment date, beacuse it has to be a future date
        $date = new \DateTime();
        $formattedDate = self::addDayToAppointmentDate($date);

        return $formattedDate;
    }

     /**
     * Get requirde appoinment date.
     *
     * @return string The deadline date value
     */
    public static function getDeadlineDate(): string {
        // Set one more day for the appoiment date, beacuse it has to be a future date
        $date = new \DateTime();
        $formattedDate = self::addWeekToAppoinmentDate($date);

        return $formattedDate;
    }

    /**
     * Add a week to \DateTime date
     * 
     * @param \DateTime $appointmentDate Appointment date
     * @return string The appointment date value
     */
    public static function addWeekToAppoinmentDate(\DateTime $appointmentDate): string {
        // Set one more day for the appoiment date, beacuse it has to be a future date
        $appointmentDate->modify('+7 day');
        $formattedDate = $appointmentDate->format('Y-m-d H:i:s');

        return $formattedDate;
    }

    /**
     * Get requirde appoinment date.
     * 
     * @param \DateTime $appointmentDate Appointment date
     * @return string The generated external ID
     */
    public static function addDayToAppointmentDate(\DateTime $appointmentDate): string {
        // Set one more day for the appoiment date, beacuse it has to be a future date
        $appointmentDate->modify('+1 day');
        $formattedDate = $appointmentDate->format('Y-m-d H:i:s');

        return $formattedDate;
    }

     /**
     * Generate a random external ID for testing
     *
     * @param int $length The length of the ID to generate
     * @return string The generated external ID
     */
    public static function generateExternalId(int $length): string {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)))), 0, $length);
    }

     /**
     * Generate jitsi room code alike for testing.
     *
     * @return string The generated jitsi room code
     */
    public static function generateJitsiRoomCode() {
        $numbers = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $randomText = '';
        $length = 16;

        for ($i = 0; $i < $length; $i++) {
            $randomText .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return "pr-$numbers-$randomText";
    }

    /**
     * Normalize a phone number (+34XXXXXXXXX)
     *
     * @param string $phoneNumber The phone number
     * @return string Normalized phone number
     */
    public static function normalizePhoneNumber(string $phoneNumber): string {
        // Delete spaces, hyphens or parenthesis
        $normalizedPhoneNumber = preg_replace('/\D+/', '', $phoneNumber);

        // Secure the number is 9 characters long (Ej.666777888)
        if (strlen($normalizedPhoneNumber) === 9) {
            return '+34' . $normalizedPhoneNumber;
        }

        // If the phone number comes wit +34 prefix is correct too
        if (preg_match('/^34\d{9}$/', $normalizedPhoneNumber)) {
            return '+'.$normalizedPhoneNumber;
        }

        throw new \InvalidArgumentException("Invalid phone number format given: $phoneNumber");
    }

    /**
     * Check if provided citizen id is valid. This means it contains 5 capital letters.
     *
     * @param string $citizenId The phone number
     * @return bool True if citizen id, false if it is not
     */
    public static function stakeholderCitizenIdIsValid(string $citizenId): bool {
        return preg_match('/^[A-Z]{5}$/', $citizenId) === 1;
    }

    /**
     * Compare two JSON strings by content
     *
     * @param string $expected Expected JSON string
     * @param string $actual Actual JSON string
     * @param bool $strict Whether to check for exact equality (true) or subset (false)
     * @return bool True if equal, false otherwise
     */
    public static function jsonEquals(string $expected, string $actual, bool $strict = false): bool {
        $expectedData = json_decode($expected, true);
        $actualData = json_decode($actual, true);

        if ($expectedData === null || $actualData === null) {
            return $expected === $actual;
        }

        if ($strict) {
            return $expectedData == $actualData;
        } else {
            // Check if expectedData is a subset of actualData
            foreach ($expectedData as $key => $value) {
                if (!isset($actualData[$key]) || $actualData[$key] != $value) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * Convert an array to a string representation for error messages
     *
     * @param array $array The array to stringify
     * @return string String representation of the array
     */
    public static function arrayToString(array $array): string {
        return json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Format message for array comparison
     *
     * @param string $message Base message
     * @param array $expected Expected array
     * @param array $actual Actual array
     * @return string Formatted message
     */
    public static function formatArrayComparisonMessage(string $message, array $expected, array $actual): string {
        return sprintf(
            "%s\nExpected: %s\nActual: %s",
            $message,
            self::arrayToString($expected),
            self::arrayToString($actual)
        );
    }

    /**
     * Returns the class name without namespace
     *
     * @param object|string $classOrObject Class name or object
     * @return string Class name without namespace
     */
    public static function getShortClassName($classOrObject): string {
        $class = is_object($classOrObject) ? get_class($classOrObject) : $classOrObject;
        $parts = explode('\\', $class);
        return end($parts);
    }

}