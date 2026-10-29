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
use OCA\VTramit\Exception\DataNotFoundException;
use OCA\VTramit\Exception\FormatNotValidException;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\EntityService;

class EntityController extends Controller {
    /** @var LoggerInterface */
    protected $logger;

    /** @var ConfigService */
    protected $configService;

    /** @var EntityService */
    protected $service;

    /** @var string */
    protected $userId;

    use ControllerErrors;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        EntityService $service,
        $userId
    ) {
        parent::__construct($appName, $request);
        $this->configService = $configService;
        $this->service = $service;
        $this->userId = $userId ?? '';
        $this->logger = $logger;
    }

    /**
     * Obtain all existing entities.
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
     * Obtain entity given its identifier.
     *
     * @param int $id  identifier of the procedure type to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function find(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->service->find($id);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Create a new entity from the given JSON data.
     *
     * @param array $data     array with all data required to create the entity
     * @return DataResponse
     */
    public function create(array $data): DataResponse {
        return $this->handleExceptions(function () use ($data) {
            $response = $this->service->create($data, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Update entity related to the given JSON data.
     *
     * @param array  $data    array with all data required to update the entity
     * @return DataResponse
     */
    public function update(array $data): DataResponse {
        return $this->handleExceptions(function () use ($data) {
            $response = $this->service->update($data, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Delete entity by given id.
     *
     * @param int $id  identifier of the entity to delete
     * @return DataResponse
     */
    public function delete(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->service->delete($id);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Convert a JSON string to an array.
     *
     * @param string $data   JSON string with the data to transform
     * @param string $index  index to return; if no index given, full array is returned
     * @return DataResponse
     */
    protected function jsonStringToArray(string $json, string $index = null) {
        if (is_string($json)) {
            $json = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new FormatNotValidException("JSON format is not valid");
            }
        }

        if (is_null($index)) {
            return $json;
        }

        if (!empty($json[$index])) {
            return $json[$index];
        }

        throw new DataNotFoundException("Requested data not found in JSON string. Please check content and format!");
    }
}