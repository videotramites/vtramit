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

namespace OCA\VTramit\Controller;

use OCP\IRequest;
use Psr\Log\LoggerInterface;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\VTramit\Db\Procedure;

use OCA\VTramit\Exception\ControllerErrors;
use OCA\VTramit\Service\ProcedureJobService;
use OCA\VTramit\Service\ConfigService;

class ProcedureJobApiController extends Controller {

    /** @var LoggerInterface */
    protected $logger;

    /** @var ConfigService */
    protected $configService;

    /** @var ProcedureJobService */
    protected $procedureJobService;

    /** @var string */
    protected $userId;

    use ControllerErrors;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        ProcedureJobService $procedureJobService,
        $userId
    ) {
        parent::__construct($appName, $request);
        $this->configService = $configService;
        $this->procedureJobService = $procedureJobService;
        $this->userId = $userId;
        $this->logger = $logger;
    }

    /**
     * Execute the prepare for work today job.
     *
     * @OA\Post(
     *     path="/api/2.0/job/procedure/prepareWork/find",
     *     @OA\Response(
     *         response="200",
     *         description="Obtained work for today to prepare"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function findWorkForTodayToPrepare(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->procedureJobService->findWorkForTodayToPrepare();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Execute the prepare for work today job.
     *
     * @OA\Post(
     *     path="/api/2.0/job/procedure/prepareWork",
     *     @OA\Response(
     *         response="200",
     *         description="Work for today prepared"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function prepareWorkToday(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->procedureJobService->prepareWorkForToday();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Purge old data, remove procedure information and anonymize data in database.
     *
     * @OA\Post(
     *     path="/api/2.0/job/procedure/purge",
     *     @OA\Response(
     *         response="200",
     *         description="Old data purged"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function purgeData(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->procedureJobService->purgeData();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
}