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
use OCA\VTramit\Service\WebhookService;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Db\MailTemplate;

class WebhookApiController extends EntityController {

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        WebhookService $service,
        $userId
    ) {
        parent::__construct($appName, $request, $logger, $configService, $service, $userId);
    }

    /**
     * Obtain all existing webhooks.
     *
     * @OA\Get(
     *     path="/api/2.0/webhook",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain all existing webhooks"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): DataResponse {
        return parent::index();
    }

    /**
     * Obtain webhook given its identifier.
     *
     * @OA\Get(
     *     path="/api/2.0/webhook/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain webhook given its identifier"
     *     )
     * )
     *
     * @param int $id  identifier of the mail template to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function find(int $id): DataResponse {
        return parent::find($id);
    }

    /**
     * Create a new webhook from the given JSON data.
     *
     * @OA\Post(
     *     path="/api/2.0/webhook",
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
     *         description="Create a new webhook from the given JSON data"
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
     * Update webhook related to the given JSON data.
     *
     * @OA\Put(
     *     path="/api/2.0/webhook",
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
     *         description="Update webhook related to the given JSON data"
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
     * Delete webhook by given id.
     *
     * @OA\Delete(
     *     path="/api/2.0/webhook/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Delete webhook by given id"
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