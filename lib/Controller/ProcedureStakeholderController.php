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

use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Exception\ControllerErrors;

use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\ConfigService;

class ProcedureStakeholderController extends EntityController {

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        ProcedureStakeholderService $service,
        $userId
    ) {
        parent::__construct($appName, $request, $logger, $configService, $service, $userId);
    }

    //
    // CRUD
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Obtain all existing procedure stakeholders.
     *
     * @OA\Get(
     *     path="/web/2.0/procedure-stakeholder",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain all existing procedure stakeholders"
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
     * Obtain procedure stakeholder given its identifier.
     *
     * @OA\Get(
     *     path="/web/2.0/procedure-stakeholder/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain procedure stakeholder given its identifier"
     *     )
     * )
     *
     * @param int $id  identifier of the procedure stakeholder to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function find(int $id): DataResponse {
        return parent::find($id);
    }

    /**
     * Get filtered procedures stakeholders.
     *
     * @OA\Post(
     *     path="/web/2.0/procedure-stakeholder/filter",
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
     *         description="Procedures stakeholders found with given filter"
     *     )
     * )
     *
     * @param array $filter  filter parameters to apply
     * @return array         list of procedures stakeholders that accomplish given filters
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function findByFilter(array $filter): DataResponse {
        return $this->handleExceptions(function () use ($filter) {
            $response = $this->service->findByFilter($filter, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Get the entry page for given stakeholder.
     *
     * @OA\Get(
     *     path="/web/2.0/procedure-stakeholder/{id}/entry-page",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="the procedure stakeholder public entry page"
     *     )
     * )
     *
     * @param array $id  identifier of the procedure stakeholder
     * @return array     list of procedures stakeholders that accomplish given filters
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getEntryPage(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->service->getFullEntryPage($id);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Create a new procedure stakeholder from the given JSON data.
     *
     * @OA\Post(
     *     path="/web/2.0/procedure-stakeholder",
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
     *         description="Create a new procedure stakeholder from the given JSON data"
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
     * Update procedure stakeholder.
     *
     * @OA\Put(
     *     path="/web/2.0/procedure-stakeholder",
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
     *         description="Update procedure stakeholder"
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
     * Delete procedure stakeholder by given id.
     *
     * @OA\Delete(
     *     path="/web/2.0/procedure-stakeholder/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Delete procedure stakeholder by given id"
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

    //
    // CRUD by prodcedure
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Obtain all existing procedure stakeholders that belongs to the given procedure.
     *
     * @OA\Get(
     *     path="/web/2.0/procedure/{procedureId}/stakeholder",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="procedureId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain all existing procedure stakeholders that belongs to the given procedure"
     *     )
     * )
     *
     * @param int $procedureId  identifier of the procedure which belongs the stakeholders to retrieve
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function indexByProcedureId(int $procedureId): DataResponse {
        return $this->handleExceptions(function () use ($procedureId) {
            $response = $this->service->findByProcedureId($procedureId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain procedure stakeholder given its identifier.
     *
     * @OA\Get(
     *     path="/web/2.0/procedure/{procedureId}/stakeholder/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="procedureId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain procedure stakeholder given its identifier"
     *     )
     * )
     *
     * @param int $procedureId  identifier of the procedure which belongs the stakeholders to retrieve
     * @param int $id           identifier of the procedure stakeholder to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function findByProcedureId(int $procedureId, int $id): DataResponse {
        return $this->find($id);
    }

    /**
     * Create a new procedure stakeholder associated to a procedure from the
     * given data.
     *
     * @OA\Post(
     *     path="/web/2.0/procedure/{procedureId}/stakeholder",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="procedureId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"data"},
     *             @OA\Property(property="data", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="New procedure stakeholder created"
     *     )
     * )
     *
     * @param int   $procedureId  identifier of the procedure which belongs the stakeholders to retrieve
     * @param array $data         fields of the entity to create with its values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function createByProcedureId(int $procedureId, array $data): DataResponse {
        if (!empty($data[0]) && is_array($data[0])) {
            $response = new ResponseInfo();

            foreach($data as $entity) {
                $entity['procedureId'] = $procedureId;
                $dataResponse = $this->create($entity); // returns DataResponse

                // Merge response
                $responseInfo = $dataResponse->getData();
                if (!empty($responseInfo['entity'])) {
                    $response->addInfo('list', $responseInfo['entity']);
                }
                $response->addMessages($responseInfo['message']);

                // On error stop processing
                if ($responseInfo['result'] !== 'OK') {
                    $response->setInfo(ResponseInfo::RESPONSE_INFO_RESULT, $responseInfo[ResponseInfo::RESPONSE_INFO_RESULT]);
                    break;
                }
            }

            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        }

        $data['procedureId'] = $procedureId;
        return $this->create($data);
    }

    /**
     * Update procedure stakeholder.
     *
     * @OA\Put(
     *     path="/web/2.0/procedure/{procedureId}/stakeholder",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="procedureId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"data"},
     *             @OA\Property(property="data", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure stakeholder updated"
     *     )
     * )
     *
     * @param int   $procedureId  identifier of the procedure which belongs the stakeholder to update
     * @param array $data         fields of the entity to modify with its new values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function updateByProcedureId(int $procedureId, array $data): DataResponse {
        if (!empty($data[0]) && is_array($data[0])) {
            $response = new ResponseInfo();

            foreach($data as $entity) {
                $entity['procedureId'] = $procedureId;
                $dataResponse = $this->update($entity);
                $responseInfo = $dataResponse->getData();
                $response->addInfo('list', $responseInfo['entity']);
            }

            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        }

        $data['procedureId'] = $procedureId;
        return $this->update($data);
    }

    /**
     * Delete procedure stakeholder by given id.
     *
     * @OA\Delete(
     *     path="/web/2.0/procedure/{procedureId}/stakeholder/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="procedureId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure stakeholder deleted"
     *     )
     * )
     *
     * @param int $procedureId  identifier of the procedure which belongs the stakeholder to delete
     * @param int $id           identifier of the entity to delete
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function deleteByProcedureId(int $procedureId, int $id): DataResponse {
        return $this->delete($id);
    }

    /**
     * Delete all procedure stakeholder by given procedure id.
     *
     * @OA\Delete(
     *     path="/web/2.0/procedure/{procedureId}/stakeholder",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="procedureId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure stakeholders deleted for given procedure id"
     *     )
     * )
     *
     * @param int $procedureId  identifier of the procedure which belongs the stakeholder to delete
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function deleteAllByProcedureId(int $procedureId, array $data = null): DataResponse {
        if (!empty($data)) {
            $response = new ResponseInfo();

            foreach($data as $id) {
                $entity['procedureId'] = $procedureId;
                $dataResponse = $this->deleteByProcedureId($procedureId, $id);
                $responseInfo = $dataResponse->getData();
                //$response->addInfo('list', $responseInfo['entity']);
            }

            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        }

        return $this->handleExceptions(function () use ($procedureId) {
            $response = $this->service->deleteByProcedureId($procedureId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Send the e-mail to given stakeholder that belongs to given procedure.
     *
     * @OA\Get(
     *     path="/web/2.0/procedure/{procedureId}/stakeholder/{id}/op/email",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="procedureId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Send the e-mail to given stakeholder that belongs to given procedure"
     *     )
     * )
     *
     * @param int $procedureId  identifier of the procedure which belongs the stakeholders
     * @param int $id           identifier of the stakeholder to send the e-mail
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function sendEmail(int $procedureId, int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->service->sendEmailToStakeholder($id);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }


    //
    // Other operations
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Force a password change.
     *
     * @OA\Put(
     *     path="/web/2.0/procedure-stakeholder/{id}/password",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Force a password change"
     *     )
     * )
     *
     * @param int $id  identifier of the entity to change the password
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function changePassword(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->service->changePassword($id, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
}