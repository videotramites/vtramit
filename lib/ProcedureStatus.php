<?php
/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia Benítez <letizia@videoatencion.com>
 * @author Daniel Tamajon <dtamajon@litesolutions.es>
 * @author Marta González <marta.gonzalez@litesolutions.es>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan Reyné <ivanreyne@gmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\VTramit;

use OCP\IL10N;

class ProcedureStatus {
    public const INITIALIZING = 1;
    public const CREATED      = 2;
    public const PENDANT      = 3;
    public const ON_COURSE    = 4;
    public const FINISHED     = 5;
    public const COMPLETED    = 6;
    public const CANCELLED    = 7;
    public const DUPLICATED   = 8;
    public const EXPIRED      = 9;

    private static $finalStatus = [self::COMPLETED, self::CANCELLED, self::DUPLICATED, self::EXPIRED];
    private static $nonFinalStatus = [self::INITIALIZING, self::CREATED, self::PENDANT, self::ON_COURSE];
    private static $cancelledStatus = [self::CANCELLED, self::DUPLICATED, self::EXPIRED];
    private static $allowedChanges = [
        self::INITIALIZING => [self::CREATED, self::PENDANT, self::DUPLICATED, self::EXPIRED, self::CANCELLED],
        self::CREATED      => [self::PENDANT, self::DUPLICATED, self::EXPIRED, self::CANCELLED],
        self::PENDANT      => [self::ON_COURSE, self::FINISHED, self::COMPLETED,  self::DUPLICATED, self::EXPIRED, self::CANCELLED],
        self::ON_COURSE    => [self::FINISHED, self::COMPLETED],
        self::FINISHED     => [self::ON_COURSE, self::COMPLETED],
        self::COMPLETED    => [self::ON_COURSE, self::FINISHED],
        self::CANCELLED    => [],
        self::DUPLICATED   => [],
        self::EXPIRED      => []
    ];
    private static $allowedActions = [
        self::INITIALIZING => [],
        self::CREATED      => [],
        self::PENDANT      => [self::ON_COURSE, self::FINISHED, self::COMPLETED],
        self::ON_COURSE    => [self::FINISHED, self::COMPLETED],
        self::FINISHED     => [self::ON_COURSE, self::COMPLETED],
        self::COMPLETED    => [self::ON_COURSE, self::FINISHED],
        self::CANCELLED    => [],
        self::DUPLICATED   => [],
        self::EXPIRED      => []
    ];

    /**
     * Get the list of final statuses.
     *
     * @return array  list of statuses that are final status
     */
    public static function getFinalStatus(): array {
        return self::$finalStatus;
    }

    /**
     * Get the list of non-final statuses.
     *
     * @return array  list of statuses that are non-final status
     */
    public static function getNonFinalStatus(): array {
        return self::$nonFinalStatus;
    }

    /**
     * Get the list of cancelled statuses.
     *
     * @return array  list of statuses that are cancelled status
     */
    public static function getCancelledStatus(): array {
        return self::$cancelledStatus;
    }

    /**
     * Indicates if given status is one of the final statuses.
     *
     * @param bool $status  the status to check
     * @return bool         true is given status is a final status; otherwise, false
     */
    public static function isStatusFinal($status): bool {
        return in_array($status, self::$finalStatus);
    }

    /**
     * Indicates if given status is one of the non-final statuses.
     *
     * @param bool $status  the status to check
     * @return bool         true is given status is a non-final status; otherwise, false
     */
    public static function isStatusNonFinal($status): bool {
        return in_array($status, self::$nonFinalStatus);
    }

    /**
     * Indicates if given status is one of the cancelled statuses.
     *
     * @param bool $status  the status to check
     * @return bool         true is given status is a cancelled status; otherwise, false
     */
    public static function isStatusCancelled($status): bool {
        return in_array($status, self::$cancelledStatus);
    }

    /**
     * Get the allowed status to change from given one.
     *
     * @param int $from  current status
     * @return array     possibles statutes that are allowed to change
     */
    public static function getAllowedTransition($from): array {
        if (isset(self::$allowedChanges[$from])) {
            return self::$allowedChanges[$from];
        }

        if (empty($from)) {
            // When no status yet defined allow initializing and created status,
            // but also the opportunity to cancel the procedure in case of
            // intermediate errors
            return [self::INITIALIZING, self::CREATED, self::CANCELLED, self::DUPLICATED, self::EXPIRED];
        }

        return [];
    }

    /**
     * Indicates wether the transition from one status to another is valid or not.
     *
     * @param int $from  the initial status
     * @param int $to    the target status
     * @return bool      true is transition is valid; otherwise, false
     */
    public static function isValidTransition($from, $to): bool {
        $allowed = self::getAllowedTransition($from);
        return in_array($to, $allowed);
    }

    /**
     * Get the allowed status to change from given one by a user from the app interface.
     *
     * @param int $from  current status
     * @return array     possibles statutes that are allowed to change
     */
    public static function getAllowedActions($from): array {
        if (isset(self::$allowedActions[$from])) {
            return self::$allowedActions[$from];
        }

        return [];
    }

    /**
     * Get the list of status with their meaning translated.
     *
     * @return array  list of statuses and translated descriptions
     */
    public static function getDetails(): array {
        return [
            [
                "key" => self::INITIALIZING,
                "value" => 'Initializing',
                "action" => 'Initialize',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::CREATED,
                "value" => 'Created',
                "action" => 'Create',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::PENDANT,
                "value" => 'Pendant',
                "action" => 'Pendant',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::ON_COURSE,
                "value" => 'On course',
                "action" => 'To On Course',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::FINISHED,
                "value" => 'Finished',
                "action" => 'Finish',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::COMPLETED,
                "value" => 'Completed',
                "action" => 'Complete',
                "filterable" => true,
                "editable" => false
            ],
            [
                "key" => self::CANCELLED,
                "value" => 'Cancelled',
                "action" => 'Cancel',
                "filterable" => false,
                "editable" => false
            ],
            [
                "key" => self::DUPLICATED,
                "value" => 'Duplicated',
                "action" => 'Duplicate',
                "filterable" => false,
                "editable" => false
            ],
            [
                "key" => self::EXPIRED,
                "value" => 'Expired',
                "action" => 'Expire',
                "filterable" => false,
                "editable" => false
            ]
        ];
    }
}