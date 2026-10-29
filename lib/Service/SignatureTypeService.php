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

namespace OCA\VTramit\Service;

use Psr\Log\LoggerInterface;
use OCP\IL10N;

use OCA\VTramit\Db\SignatureType;
use OCA\VTramit\Db\SignatureTypeMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Service\ConfigService;


class SignatureTypeService extends EntityService  {

    /** @var SignatureTypeMapper */
    private $signatureTypeMapper;


    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        SignatureTypeMapper $signatureTypeMapper
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $signatureTypeMapper);
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new SignatureType();
    }

    //
    // Signature Type management
    ////////////////////////////////////////////////////////////////////////////////
}