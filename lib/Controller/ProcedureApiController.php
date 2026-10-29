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
use OCA\VTramit\Exception\FormatNotValidException;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureService;

class ProcedureApiController extends EntityController {

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        ProcedureService $service,
        $userId
    ) {
        parent::__construct($appName, $request, $logger, $configService, $service, $userId);
    }

    /**
     * Obtain all existing procedures.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="List of all existing procedures"
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
     * Obtain procedure given its identifier.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure found"
     *     )
     * )
     *
     * @param int $id  identifier of the procedure to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function find(int $id): DataResponse {
        return parent::find($id);
    }

    /**
     * Get filtered procedures.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/search/filter",
     *     security={{"basicAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filter"},
     *             @OA\Property(property="filter", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Procedures found with given filter"
     *     )
     * )
     *
     * @param array $filter  filter parameters to apply
     * @return DataResponse
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
     * Get all non-cancelled procedures using given external ID.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure/externalId/{externalId}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="externalId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response="200",
     *         description="List of all non-cancelled procedures with given external ID"
     *     )
     * )
     *
     * Command line to test this function:
     * curl -i -u <user>:<password> https://<my.domain>/index.php/apps/vtramit/api/2.0/procedure/externalId/<externalId>
     *
     * @param string $externalId  external identifier of the procedure to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function findByExternalId(string $externalId): DataResponse {
        return $this->handleExceptions(function () use ($externalId) {
            $response = $this->service->findByExternalId($externalId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Create a new procedure from the given data.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure",
     *     security={{"basicAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"data"},
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure created"
     *     )
     * )
     *
     * @param array $data  fields of the entity to create with its values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(array $data): DataResponse {
        return parent::create($data);
    }

    /**
     * Create or updates a procedure using the given data.
     *
     * It checks if there is an existing procedure with the same external ID in
     * which case it updates the existing procedure; otherwise, a new procedure
     * is created.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/externalId",
     *     summary="It checks if there is an existing procedure with the same external ID in which case it updates the existing procedure; otherwise, a new procedure is created",
     *     security={{"basicAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"data"},
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure updated"
     *     )
     * )
     *
     * @param array $data  fields of the entity to create with its values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function updateByExternalId(array $data): DataResponse {
        return $this->handleExceptions(function () use ($data) {
            $response = $this->service->createOrUpdateByExternalId($data, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Update procedure related to the given data.
     *
     * @OA\Put(
     *     path="/api/2.0/procedure",
     *     security={{"basicAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"data"},
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure updated"
     *     )
     * )
     *
     * @param array $data  fields of the entity to modify with its new values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(array $data): DataResponse {
        return parent::update($data);
    }

    /**
     * Delete procedure by given id.
     *
     * @OA\Delete(
     *     path="/api/2.0/procedure/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure deleted"
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
     * Get the procedure context for the user.
     *
     * @OA\Get(
     *     path="/api/2.0/context/procedure/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Procedure context"
     *     )
     * )
     *
     * @param int $id  identifier of the procedure for which retrieve the allowed groups
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getContext(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->service->getContext($id, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Send the e-mail to all stakeholders that belongs to given procedure.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure/{id}/stakeholder/op/email",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="E-mail sent"
     *     )
     * )
     *
     * @param int $id  identifier of the procedure which belongs the stakeholder to delete
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function sendEmail(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->service->sendEmailToAllStakeholders($id);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Receive notification of started videoconference.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/{jitsiRoomCode}/videoconference/started",
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"jitsiRoomCode"},
     *             @OA\Property(property="jitsiRoomCode", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Videoconference started"
     *     )
     * )
     *
     * @param string $jitsiRoomCode videoconference room
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function videoconferenceStarted(string $jitsiRoomCode): DataResponse {
        return $this->handleExceptions(function () use ($jitsiRoomCode) {
            $response = $this->service->videoconferenceStarted($jitsiRoomCode, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Receive notification of finished videoconference.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/{jitsiRoomCode}/videoconference/finished",
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"jitsiRoomCode"},
     *             @OA\Property(property="jitsiRoomCode", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Videoconference finished"
     *     )
     * )
     *
     * Command line to test this function:
     * curl -i -H "Accept: application/json" -H "Content-Type: application/json" -X POST https://<my.domain>/index.php/apps/vtramit/api/2.0/procedure/{jitsiRoomCode}/videoconference/finished
     *
     * @param string $jitsiRoomCode videoconference room
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function videoconferenceFinished(string $jitsiRoomCode): DataResponse {
        return $this->handleExceptions(function () use ($jitsiRoomCode) {
            $response = $this->service->videoconferenceFinished($jitsiRoomCode, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Initialize the signature process for given procedure.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/{id}/signature/initialize/{signatureButtonId}",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="signatureButtonId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response="200",
     *         description="Signature initialized"
     *     )
     * )
     *
     * @param int    $id                 procedure identifier
     * @param string $signatureButtonId  the signature button id selected
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function initializeSignature(int $id, string $signatureButtonId): DataResponse {
        return $this->handleExceptions(function () use ($id, $signatureButtonId) {
            $response = $this->service->initializeSignature($id, $this->userId, $signatureButtonId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain list of files in the uploads folder, or the content of a specific file if provided.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure/{id}/file/upload-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filename", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional JSON body to request a specific file content",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="filename", type="string", example="document.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="List of files in the uploads folder or the file content when filename is provided"
     *     )
     * )
     *
     * @param int $id  Procedure identifier to which belongs the uploads folder.
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getUploadsFolderFiles(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            // Read optional 'filename' from request (accepts JSON body or query param).
            $filename = $this->request->getParam('filename');

            if (is_string($filename) && $filename !== '') {
                $response = $this->service->getUploadsFolderFileContent($id, $filename, $this->userId);
                return new DataResponse($response->getData(), HTTP::STATUS_OK);
            }

            $response = $this->service->getUploadsFolderFiles($id, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain list of files in the downloads folder, or the content of a specific file if provided.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure/{id}/file/download-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filename", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional JSON body to request a specific file content",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="filename", type="string", example="document.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="List of files in the downloads folder or the file content when filename is provided"
     *     )
     * )
     *
     * @param int $id  procedure identifier to which belongs the downloads folder
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getDownloadsFolderFiles(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            // Read optional 'filename' from request (accepts JSON body or query param).
            $filename = $this->request->getParam('filename');

            if (is_string($filename) && $filename !== '') {
                $response = $this->service->getDownloadsFolderFileContent($id, $filename, $this->userId);
                return new DataResponse($response->getData(), HTTP::STATUS_OK);
            }

            $response = $this->service->getDownloadsFolderFiles($id, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain list of files in the signature uploads folder, or the content of a specific file if provided.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure/{id}/signature/upload-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filename", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional JSON body to request a specific file content",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="filename", type="string", example="document.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="List of files in the signature uploads folder or the file content when filename is provided"
     *     )
     * )
     *
     * @param int $id  procedure identifier to which belongs the signature uploads folder
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getSignatureUploadsFolderFiles(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            // Read optional 'filename' from request (accepts JSON body or query param).
            $filename = $this->request->getParam('filename');

            if (is_string($filename) && $filename !== '') {
                $response = $this->service->getSignatureUploadsFolderFileContent($id, $filename, $this->userId);
                return new DataResponse($response->getData(), HTTP::STATUS_OK);
            }

            $response = $this->service->getSignatureUploadsFolderFiles($id, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain list of files in the signature downloads folder, or the content of a specific file if provided.
     *
     * @OA\Get(
     *     path="/api/2.0/procedure/{id}/signature/download-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filename", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional JSON body to request a specific file content",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="filename", type="string", example="document.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="List of files in the signature downloads folder or the file content when filename is provided"
     *     )
     * )
     *
     * @param int $id  procedure identifier to which belongs the signature downloads folder
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getSignatureDownloadsFolderFiles(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            // Read optional 'filename' from request (accepts JSON body or query param).
            $filename = $this->request->getParam('filename');

            if (is_string($filename) && $filename !== '') {
                $response = $this->service->getSignatureDownloadsFolderFileContent($id, $filename, $this->userId);
                return new DataResponse($response->getData(), HTTP::STATUS_OK);
            }

            $response = $this->service->getSignatureDownloadsFolderFiles($id, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Stores given file to the uploads folder.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/{id}/file/upload-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filename", "data"},
     *             @OA\Property(property="filename", type="string"),
     *             @OA\Property(property="data", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="File uploaded"
     *     )
     * )
     *
     * @param int    $id        procedure identifier which belongs the file
     * @param string $filename  filename
     * @param string $data      the file content
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function uploadToUploadsFolder(int $id, string $filename, string $data): DataResponse {
        return $this->handleExceptions(function () use ($id, $filename, $data) {
            $response = $this->service->uploadToUploadsFolder($id, $this->userId, $filename, $data);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Stores given file to the downloads folder.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/{id}/file/download-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filename", "data"},
     *             @OA\Property(property="filename", type="string"),
     *             @OA\Property(property="data", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="File uploaded"
     *     )
     * )
     *
     * @param int    $id        procedure identifier which belongs the file
     * @param string $filename  filename
     * @param string $data      the file content
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function uploadToDownloadsFolder(int $id, string $filename, string $data): DataResponse {
        return $this->handleExceptions(function () use ($id, $filename, $data) {
            $response = $this->service->uploadToDownloadsFolder($id, $this->userId, $filename, $data);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Stores given file to the signature uploads folder.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/{id}/signature/upload-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filename", "data"},
     *             @OA\Property(property="filename", type="string"),
     *             @OA\Property(property="data", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="File uploaded"
     *     )
     * )
     *
     * @param int    $id        procedure identifier which belongs the file
     * @param string $filename  filename
     * @param string $data      the file content
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function uploadToSignatureUploadsFolder(int $id, string $filename, string $data): DataResponse {
        return $this->handleExceptions(function () use ($id, $filename, $data) {
            $response = $this->service->uploadToSignatureUploadsFolder($id, $this->userId, $filename, $data);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Stores given file to the signature downloads folder.
     *
     * @OA\Post(
     *     path="/api/2.0/procedure/{id}/signature/download-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filename", "data"},
     *             @OA\Property(property="filename", type="string"),
     *             @OA\Property(property="data", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="File uploaded"
     *     )
     * )
     *
     * @param int    $id        procedure identifier which belongs the file
     * @param string $filename  filename
     * @param string $data      the file content
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function uploadToSignatureDownloadsFolder(int $id, string $filename, string $data): DataResponse {
        return $this->handleExceptions(function () use ($id, $filename, $data) {
            $response = $this->service->uploadToSignatureDownloadsFolder($id, $this->userId, $filename, $data);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Deletes a file from the uploads folder.
     *
     * @OA\Delete(
     *     path="/api/2.0/procedure/{id}/file/upload-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filename", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional JSON body with the filename to delete",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filename"},
     *             @OA\Property(property="filename", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="File deleted from uploads folder"
     *     )
     * )
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function deleteFromUploadsFolder(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $filename = $this->request->getParam('filename');
            if (empty($filename)) {
                throw new FormatNotValidException('The filename parameter is required');
            }

            $response = $this->service->deleteFromUploadsFolder($id, $this->userId, $filename);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Deletes a file from the downloads folder.
     *
     * @OA\Delete(
     *     path="/api/2.0/procedure/{id}/file/download-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filename", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional JSON body with the filename to delete",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filename"},
     *             @OA\Property(property="filename", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="File deleted from downloads folder"
     *     )
     * )
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function deleteFromDownloadsFolder(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $filename = $this->request->getParam('filename');
            if (empty($filename)) {
                throw new FormatNotValidException('The filename parameter is required');
            }

            $response = $this->service->deleteFromDownloadsFolder($id, $this->userId, $filename);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Deletes a file from the signature uploads folder.
     *
     * @OA\Delete(
     *     path="/api/2.0/procedure/{id}/signature/upload-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filename", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional JSON body with the filename to delete",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filename"},
     *             @OA\Property(property="filename", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="File deleted from signature uploads folder"
     *     )
     * )
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function deleteFromSignatureUploadsFolder(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $filename = $this->request->getParam('filename');
            if (empty($filename)) {
                throw new FormatNotValidException('The filename parameter is required');
            }

            $response = $this->service->deleteFromSignatureUploadsFolder($id, $this->userId, $filename);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Deletes a file from the signature downloads folder.
     *
     * @OA\Delete(
     *     path="/api/2.0/procedure/{id}/signature/download-folder",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filename", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional JSON body with the filename to delete",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"filename"},
     *             @OA\Property(property="filename", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="File deleted from signature downloads folder"
     *     )
     * )
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function deleteFromSignatureDownloadsFolder(int $id): DataResponse {
        return $this->handleExceptions(function () use ($id) {
            $filename = $this->request->getParam('filename');
            if (empty($filename)) {
                throw new FormatNotValidException('The filename parameter is required');
            }

            $response = $this->service->deleteFromSignatureDownloadsFolder($id, $this->userId, $filename);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
}
