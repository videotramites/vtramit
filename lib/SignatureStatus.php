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

class SignatureStatus {
    public const INITIALIZING      = 1;
    public const REQUEST_TO_START  = 2;
    public const WAITING_MAIL      = 3;
    public const WAITING_SIGNATURE = 4;
    public const SIGNED            = 5;
    public const CANCELLED         = 6;
    public const EXPIRED           = 7;
    public const ERROR             = 8;

    private static $finalStatus = [self::SIGNED, self::CANCELLED, self::EXPIRED, self::ERROR];
    private static $nonFinalStatus = [self::INITIALIZING, self::REQUEST_TO_START, self::WAITING_MAIL, self::WAITING_SIGNATURE];
    private static $cancelledStatus = [self::CANCELLED, self::EXPIRED];

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
     * Indicates if given status is one of the pending statuses.
     *
     * @param bool $status  the status to check
     * @return bool         true is given status is a pending status; otherwise, false
     */
    public function isPendingStatus($status): bool {
        return self::isStatusNonFinal($status);
    }

    /**
     * Get the list of status with their meaning translated.
     *
     * @return array  list of statuses and translated descriptions
     */
    public static function getDetails(IL10N $translate): array {
        return [
            [
                "key" => self::INITIALIZING,
                "value" => 'Initializing',
                "action" => 'Initialize',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::REQUEST_TO_START,
                "value" => 'Signature process start requested',
                "action" => 'Request to start',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::WAITING_MAIL,
                "value" => 'Mail in queue to be sent',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::WAITING_SIGNATURE,
                "value" => 'Waiting for signature',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::SIGNED,
                "value" => 'Signed',
                "filterable" => true,
                "editable" => true
            ],
            [
                "key" => self::CANCELLED,
                "value" => 'Cancelled',
                "action" => 'Cancel',
                "filterable" => true,
                "editable" => false
            ],
            [
                "key" => self::EXPIRED,
                "value" => 'Expired',
                "action" => 'Expire',
                "filterable" => false,
                "editable" => false
            ],
            [
                "key" => self::ERROR,
                "value" => 'Error',
                "filterable" => false,
                "editable" => false
            ]
        ];
    }
}