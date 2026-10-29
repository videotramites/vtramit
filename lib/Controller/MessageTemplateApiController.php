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
use OCA\VTramit\Service\MessageTemplateService;
use OCA\VTramit\Service\ConfigService;

class MessageTemplateApiController extends EntityController {

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        MessageTemplateService $service,
        $userId
    ) {
        parent::__construct($appName, $request, $logger, $configService, $service, $userId);
    }

    /**
     * Obtain all existing message templates.
     *
     * @OA\Get(
     *     path="/api/2.0/message-template",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain all existing message templates"
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
     * Obtain message template given its identifier.
     *
     * @OA\Get(
     *     path="/api/2.0/message-template/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain message template given its identifier"
     *     )
     * )
     *
     * @param int $id  identifier of the message template to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function find(int $id): DataResponse {
        return parent::find($id);
    }

    /**
     * Create a new message template from the given JSON data.
     *
     * @OA\Post(
     *     path="/api/2.0/message-template",
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
     *         description="Create a new message template from the given JSON data"
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
     * Update message template related to the given JSON data.
     *
     * @OA\Put(
     *     path="/api/2.0/message-template",
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
     *         description="Update message template related to the given JSON data"
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
     * Delete message template by given id.
     *
     * @OA\Delete(
     *     path="/api/2.0/message-template/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Delete message template by given id"
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

    /**
     * Obtain all variables to use in the emessage body.
     *
     * @OA\Get(
     *     path="/api/2.0/message-template/context/variables",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain all variables to use in the emessage body"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function indexVariables(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->service->getListOfMessageVariables();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
}