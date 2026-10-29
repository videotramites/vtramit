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
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ContextService;

class ContextApiController extends Controller {
    /** @var ConfigService */
    protected $configService;

    /** @var ProcedureService */
    protected $contextService;

    /** @var string */
    protected $userId;

    /** @var LoggerInterface */
    protected $logger;

    use ControllerErrors;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        ContextService $contextService,
        $userId
    ) {
        parent::__construct($appName, $request);
        $this->configService = $configService;
        $this->contextService = $contextService;
        $this->userId = $userId;
        $this->logger = $logger;
    }

    /**
     * Get the app context for the user.
     *
     * @OA\Get(
     *     path="/api/2.0/context/app",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="App context for the user"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getAppContext(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->contextService->getAppContext($this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Get the available procedure statuses.
     *
     * @OA\Get(
     *     path="/api/2.0/context/procedure/status",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Available procedure statuses"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getProcedureStatuses(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->contextService->getProcedureStatusList();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Get the available signature statuses.
     *
     * @OA\Get(
     *     path="/api/2.0/context/signature/status",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Available signaturestatuses"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getSignatureStatuses(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->contextService->getSignatureStatusList();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Get the available channels.
     *
     * @OA\Get(
     *     path="/api/2.0/context/attendance-channel",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Available channels"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getChannels(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->contextService->getAttendanceChannelList();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Get the supported languages.
     *
     * @OA\Get(
     *     path="/api/2.0/context/language",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Supported languages"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getLanguages(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->contextService->getSupportedLanguageList();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Get the system groups.
     *
     * @OA\Get(
     *     path="/api/2.0/context/groups",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="System groups"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getGroups(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->contextService->getGroupsList();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
        /**
     * Get the system message channels.
     *
     * @OA\Get(
     *     path="/api/2.0/context/message-channel",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="System channels"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getMessageChannels(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->contextService->getMessageChannels();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
}