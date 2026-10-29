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

use OCA\VTramit\Service\TopicService;
use OCA\VTramit\Service\ConfigService;

class TopicController extends EntityController {

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        TopicService $service,
        $userId
    ) {
        parent::__construct($appName, $request, $logger, $configService, $service, $userId);
    }

    /**
     * Obtain all existing topics.
     *
     * @OA\Get(
     *     path="/web/2.0/topic",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain all existing topics"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): DataResponse {
        return parent::index();
    }

    /**
     * Obtain topic given its identifier.
     *
     * @OA\Get(
     *     path="/web/2.0/topic/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain topic given its identifier"
     *     )
     * )
     *
     * @param int $id  identifier of the topic to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function find(int $id): DataResponse {
        $name = (string) $this->request->getParam('name', '');
        if (!empty($name)) {
            return $this->handleExceptions(function () use ($name) {
                $response = $this->service->findByName($name);
                return new DataResponse($response->getData(), HTTP::STATUS_OK);
            });
        }

        return parent::find($id);
    }

    /**
     * Get filtered topics.
     *
     * @OA\Post(
     *     path="/web/2.0/topic/search/filter",
     *     security={{"basicAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"filter"},
     *             @OA\Property(property="filter", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Topics found with given filter"
     *     )
     * )
     *
     * @param array $filter  filter parameters to apply
     * @return array         list of topics that accomplish given filters
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function findByFilter(array $filter): mixed {
        return $this->handleExceptions(function () use ($filter) {
            $response = $this->service->findByFilter($filter, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Create a new topic from the given JSON data.
     *
     * @OA\Post(
     *     path="/web/2.0/topic",
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
     *         description="Create a new topic from the given JSON data"
     *     )
     * )
     *
     * @param array $data  fields of the entity to create with its values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(array $data): DataResponse {
        return parent::create($data);
    }

    /**
     * Update topic related to the given JSON data.
     *
     * @OA\Put(
     *     path="/web/2.0/topic",
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
     *         description="Update topic related to the given JSON data"
     *     )
     * )
     *
     * @param array $data  fields of the entity to modify wit its new values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(array $data): DataResponse {
        return parent::update($data);
    }

    /**
     * Delete topic by given id.
     *
     * @OA\Delete(
     *     path="/web/2.0/topic/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Delete topic by given id"
     *     )
     * )
     *
     * @param int $id  identifier of the entity to delete
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function delete(int $id): DataResponse {
        return parent::delete($id);
    }
}
