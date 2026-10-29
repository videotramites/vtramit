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

use OCA\VTramit\ProcedureStatus;

use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureType;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\RgpdOption;
use OCA\VTramit\Db\RgpdOptionMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Service\ConfigService;

class RgpdOptionService extends EntityService {

    /** @var ProcedureMapper */
    private $procedureMapper;

    /** @var ProcedureStakeholderMapper */
    private $procedureStakeholderMapper;

    /** @var ProcedureTypeMapper */
    private $procedureTypeMapper;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        RgpdOptionMapper $rgpdOptionMapper,
        ProcedureMapper $procedureMapper,
        ProcedureStakeholderMapper $procedureStakeholderMapper,
        ProcedureTypeMapper $procedureTypeMapper
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $rgpdOptionMapper);
        $this->procedureMapper = $procedureMapper;
        $this->procedureStakeholderMapper = $procedureStakeholderMapper;
        $this->procedureTypeMapper = $procedureTypeMapper;
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new RgpdOption();
    }

    public function findByJitsiRoomCode(string $jitsiRoomCode): ResponseInfo {
        $filter['jitsiRoomCode'] = $jitsiRoomCode;
        $procedureList = $this->procedureMapper->findByFilter($filter);

        $selectedRgpdOptions = [];
        if (!empty($procedureList)) {
            $procedureTypeId = 0;

            foreach($procedureList as $procedure) {
                if (!ProcedureStatus::isStatusCancelled($procedure->getStatusId())) {
                    // If procedure is not cancelled, then trust the information
                    // and avoid looping over other procedures (if any)
                    $procedureTypeId = $procedure->getProcedureTypeId();
                    break;
                }

                // If procedure is cancelled, we hold the procedure type ID in case
                // there are no more procedures to loop; otherwise, loop keep searching
                // for a non-cancelled procedure
                $procedureTypeId = $procedure->getProcedureTypeId();
            }

            // Obtain the RGPD options selected for the procedure
            $procedureType = $this->procedureTypeMapper->find($procedureTypeId);
            $procedureRgpdOptions = $procedureType->getRgpdOptionsList();

            // Get all defined RGPD options
            $response = $this->findAll();
            $rgpdOptions = $response->getInfo('list');

            foreach($rgpdOptions as $option) {
                if (in_array($option->getId(), $procedureRgpdOptions)) {
                    // Index the options in the same order that are stored in the procedure type
                    $index = array_search($option->getId(), $procedureRgpdOptions);
                    $selectedRgpdOptions[$index] = $option;
                }
            }

            // Ensure array is ordered using the indexed keys
            ksort($selectedRgpdOptions);
        }

        $response = new ResponseInfo();
        $response->setInfo('list', $selectedRgpdOptions);

        return $response;
    }
}