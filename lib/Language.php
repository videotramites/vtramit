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

class Language {
    public const ENGLISH    = 'en';
    public const CATALAN    = 'ca';
    public const SPANISH    = 'es';
    public const EUSKERA    = 'eu';
    public const GALICIAN   = 'gl';
    public const ITALIAN    = 'it';
    public const PORTUGUESE = 'pt';

    /**
     * Get the list of status with their meaning translated.
     *
     * @return array  list of statuses and translated descriptions
     */
    public static function getDetails(IL10N $translate): array {
        return [
            [
                "key" => self::ENGLISH,
                "value" => $translate->t('English')
            ],
            [
                "key" => self::CATALAN,
                "value" => $translate->t('Catalan')
            ],
            [
                "key" => self::SPANISH,
                "value" => $translate->t('Spanish')
            ],
            [
                "key" => self::EUSKERA,
                "value" => $translate->t('Euskera')
            ],
            [
                "key" => self::GALICIAN,
                "value" => $translate->t('Galician')
            ],
            [
                "key" => self::ITALIAN,
                "value" => $translate->t('Italian')
            ],
            [
                "key" => self::PORTUGUESE,
                "value" => $translate->t('Portuguese')
            ],
        ];
    }
}