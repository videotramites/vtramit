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

use OCA\VTramit\Exception\ControllerErrors;
use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\ConfigService;

class MailQueueApiController extends EntityController {

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        MailQueueService $service,
        $userId
    ) {
        parent::__construct($appName, $request, $logger, $configService, $service, $userId);
    }


    /**
     * Obtain next mail to be sent in queue. In case of no more mails, it return a null entity.
     *
     * @OA\Get(
     *     path="/api/2.0/mail-queue",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain next mail in queue to be sent; in case of no more mails, it return a null entity"
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
            $response = $this->service->findAll();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain mail to be sent in queue given its identifier.
     *
     * @OA\Get(
     *     path="/api/2.0/mail-queue/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain mail to be sent in queue given its identifier"
     *     )
     * )
     *
     * @param int $id  identifier of the mail in queue to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function find(int $id): DataResponse {
        return parent::find($id);
    }

    /**
     * Obtain next mail to be sent in queue. In case of no more mails, it return a null entity.
     *
     * @OA\Get(
     *     path="/api/2.0/mail-queue/next",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain next mail in queue to be sent; in case of no more mails, it return a null entity"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function next(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->service->getNext();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Add a new mail to the queue from the given JSON data.
     *
     * @OA\Post(
     *     path="/api/2.0/mail-queue",
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
     *         description="Add a new mail to the queue from the given JSON data"
     *     )
     * )
     *
     * @param string $data  fields of the entity to create with its values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(array $data): DataResponse {
        return parent::create($data);
    }

    /**
     * Update mail in queue related to the given JSON data.
     *
     * @OA\Put(
     *     path="/api/2.0/mail-queue",
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
     *         description="Update mail in queue related to the given JSON data"
     *     )
     * )
     *
     * @param array $data  fields of the entity to modify wit its new values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(array $data): DataResponse {
        return parent::update($data);
    }

    /**
     * Delete mail from queue by given id.
     *
     * @OA\Delete(
     *     path="/api/2.0/mail-queue/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Delete mail from queue by given id"
     *     )
     * )
     *
     * @param int $id  identifier of the entity to delete
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function delete(int $id): DataResponse {
        return parent::delete($id);
    }
}
