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

use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureProtocolLogService;

class ProcedureProtocolLogController extends EntityController {

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        ProcedureProtocolLogService $service,
        $userId
    ) {
        parent::__construct($appName, $request, $logger, $configService, $service, $userId);
    }

    /**
     * By default, response is empty. We need something to filter.
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): DataResponse {
        return new DataResponse([]);
    }

    /**
     * Obtain the protocol steps for the group assigned to the given procedure,
     * enriched with each step's completion status.
     *
     * @OA\Get(
     *     path="/web/2.0/procedure/{id}/protocol-step",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="List of protocol steps with completion status"
     *     )
     * )
     *
     * @param int $id  identifier of the procedure
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getProtocolSteps(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->service->getProtocolStepsForProcedure($id);
            return new DataResponse(array_merge(
                ['result' => 'OK'],
                $response->getData()
            ), Http::STATUS_OK);
        });
    }

    /**
     * Mark a protocol step as completed for the given procedure.
     *
     * @OA\Post(
     *     path="/web/2.0/procedure/{id}/protocol-step/{stepId}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id",     in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="stepId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Updated list of protocol steps with completion status"
     *     )
     * )
     *
     * @param int $id      identifier of the procedure
     * @param int $stepId  identifier of the protocol step to complete
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function completeStep(int $id, int $stepId): DataResponse {
        return $this->handleExceptions(function () use ($id, $stepId) {
            $response = $this->service->completeStep($id, $stepId, $this->userId);
            return new DataResponse(array_merge(
                ['result' => 'OK'],
                $response->getData()
            ), Http::STATUS_OK);
        });
    }
}
