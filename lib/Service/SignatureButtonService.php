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

use OCA\VTramit\Db\SignatureButton;
use OCA\VTramit\Db\SignatureButtonMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Service\ConfigService;

class SignatureButtonService extends EntityService  {

    /** @var SignatureButtonMapper */
    private $signatureButtonMapper;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        SignatureButtonMapper $signatureButtonMapper
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $signatureButtonMapper);
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new SignatureButton();
    }

    //
    // Signature Button management
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the procedure type signatures that matches given signatures.
     *
     * @param int   $procedureTypeId  identifier of the procedure type for which obtain matching groups
     * @return array                  list of procedure type groups matching given groups in the procedure type
     */
    public function findByProcedureTypeId(int $procedureTypeId): ResponseInfo {
        $list = $this->entityMapper->findSignatureButtonByProcedureTypeId($procedureTypeId);
        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }
}