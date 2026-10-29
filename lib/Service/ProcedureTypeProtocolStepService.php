<?php
/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia BenÃ­tez <letizia@videoatencion.com>
 * @author Daniel Tamajon <dtamajon@litesolutions.es>
 * @author Marta GonzÃ¡lez <marta.gonzalez@litesolutions.es>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan ReynÃ© <ivanreyne@gmail.com>
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

use OCA\VTramit\Db\ProcedureTypeProtocolStep;
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Service\ConfigService;

class ProcedureTypeProtocolStepService extends EntityService {

    /** @var ProcedureTypeProtocolStepMapper */
    private $procedureTypeProtocolStepMapper;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        ProcedureTypeProtocolStepMapper $procedureTypeProtocolStepMapper
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $procedureTypeProtocolStepMapper);
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new ProcedureTypeProtocolStep();
    }

    /**
     * Obtain the list of steps for given procedure type group.
     *
     * @param int $procedureTypeGroupId  the procedure type group identifier for which retrieve the steps
     * @return ResponseInfo  {result, list[]}
     */
    public function findByProcedureTypeGroupId(int $procedureTypeGroupId): ResponseInfo {
        $list = $this->entityMapper->findByProcedureTypeGroupId($procedureTypeGroupId);
        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }
}
