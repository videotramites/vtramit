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
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Controller;

use OCA\VTramit\Exception\ControllerErrors;
use OCA\VTramit\Service\ConfigService;

class ConfigurationApiController extends Controller {
    /** @var LoggerInterface */
    protected $logger;

    /** @var ConfigService */
    private $configService;

    /** @var string */
    private $userId;

    use ControllerErrors;

    public function __construct(
        $AppName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        $userId
    ) {
        parent::__construct($AppName, $request);

        $this->userId = $userId;
        $this->configService = $configService;
    }

    /**
     * Obtain all existing configurations.
     *
     * @OA\Get(
     *     path="/api/2.0/configuration",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain all existing configurations"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->configService->findAll();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Update or create configurations using given data.
     *
     * @OA\Put(
     *     path="/api/2.0/configuration",
     *     security={{"basicAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"data"},
     *             @OA\Property(property="data", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data updated"
     *     )
     * )
     *
     * @param string $data  list of configuration keys and values to store
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(array $data): DataResponse {
        return $this->handleExceptions(function () use ($data) {
            $response = $this->configService->update($data);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
}
