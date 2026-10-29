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
use OCP\AppFramework\ApiController;

use OCA\VTramit\Exception\ControllerErrors;
use OCA\VTramit\Service\SignatureService;

/**
 * DEPRECATED -- To be removed on next versions
 */
class SignatureApiController extends ApiController {
    /** @var SignatureService */
    private $service;

    /** @var string */
    private $userId;

    private $logger;

    use ControllerErrors;

    public function __construct(
        $appName,
        IRequest $request,
        SignatureService $service,
        $userId,
        LoggerInterface $logger
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->userId = $userId;
        $this->logger = $logger;
    }

    /**
     * Get information related to given hash
     *
     * @OA\Get(
     *     path="/api/2.0/signature/info/{hash}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="hash", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="Get information related to given hash")
     * )
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getInfoByHash($hash): DataResponse {
        $data = $this->service->getInfoByHash($hash);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Get filtered signatures.
     *
     * @OA\Post(
     *     path="/api/2.0/signature/filter",
     *     security={{"basicAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"filter"},
     *             @OA\Property(property="filter", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Signatures found with given filter"
     *     )
     * )
     *
     * @param array $filter  filter parameters to apply
     * @return array         list of signatures that accomplish given filters
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function findByFilter(array $filter): DataResponse {
        return $this->handleExceptions(function () use ($filter) {
            $response = $this->service->findByFilter($filter, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Notification about upload is completed for files related to given hash
     *
     * @OA\Post(
     *     path="/api/2.0/signature/uploadcompleted/{hash}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="hash", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="Notification about upload is completed for files related to given hash")
     * )
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function uploadCompletedByHash($hash, $files): DataResponse {
        $data = $this->service->uploadCompletedByHash($hash, $files);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Get the list of pending files to be signed
     *
     * @OA\Post(
     *     path="/api/2.0/signature/pendingfiles/{jitsiRoomCode}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="List of pending files to be signed")
     * )
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getPendingSignatureFilesByJitsiRoomCode($jitsiRoomCode): DataResponse {
        $data = $this->service->getPendingSignatureFilesByJitsiRoomCode($jitsiRoomCode);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Get the list of procedures waiting to be signed.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure/signature/op/waiting",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="List of procedures awaiting to be signed"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function waitingProcedures(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->service->findProceduresWaitingForSignature();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Cancel a specific file within a signature (public API endpoint).
     *
     * @OA\Post(
     *     path="/api/2.0/signature/{id}/file/{fileId}/cancel",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="fileId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Signature file cancelled"
     *     )
     * )
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function cancelFileSignature(int $id, int $fileId): DataResponse {
        return $this->handleExceptions(function () use ($id, $fileId) {
            $response = $this->service->cancelFileSignature($id, $fileId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
}
