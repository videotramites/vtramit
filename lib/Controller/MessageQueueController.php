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

use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Enum\MessageChannel;
use OCA\VTramit\Exception\ControllerErrors;
use OCA\VTramit\Service\MessageQueueService;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureService;

class MessageQueueController extends EntityController {

    private ProcedureService $procedureService;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        ConfigService $configService,
        ProcedureService $procedureService,
        MessageQueueService $service,
        $userId
    ) {
        parent::__construct($appName, $request, $logger, $configService, $service, $userId);
        $this->procedureService = $procedureService;
    }

    /**
     * Obtain next message to be sent in queue. In case of no more messages, it return a null entity.
     *
     * @OA\Get(
     *     path="/web/2.0/message-queue",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="Obtain next message in queue to be sent; in case of no more messages, it return a null entity"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->service->findAll();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain next message to be sent in queue. In case of no more messages, it return a null entity.
     *
     * @OA\Get(
     *     path="/web/2.0/message-queue/next",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain next message in queue to be sent; in case of no more messages, it return a null entity"
     *     )
     * )
     *
     * @param string $channel  channel queue
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function next(string $channel): DataResponse {
        return $this->handleExceptions(function () use ($channel) {
            $response = $this->service->getNext($channel);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain next message to be sent in queue. In case of no more messages, it return a null entity.
     *
     * @OA\Get(
     *     path="/web/2.0/videoconference/{jitsiRoomCode}/message/{channel}/next",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="channel", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response="200",
     *         description="Next message in the queue or null when empty",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="object", additionalProperties=true),
     *                 @OA\Schema(type="null")
     *             }
     *         )
     *     )
     * )
     *
     * @param string $jitsiRoomCode  the Jitsi room code
     * @param string $channel  channel queue
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function nextByVideoconference(string $jitsiRoomCode, string $channel): DataResponse {
        return $this->handleExceptions(function () use ($jitsiRoomCode, $channel) {
            $procedure = $this->procedureService->findByJitsiRoomCode($jitsiRoomCode)->getInfo('entity');

            if (empty($procedure)) {
                return new DataResponse([], HTTP::STATUS_NOT_FOUND);
            }

            $response = $this->service->getNextByProcedure($channel, $procedure->getId());
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain next command to be sent in queue. In case of no more commands, it return a null entity.
     *
     * @OA\Get(
     *     path="/web/2.0/videoconference/{jitsiRoomCode}/command/next",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response="200",
     *         description="Next command in the queue or null when empty",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="object", additionalProperties=true),
     *                 @OA\Schema(type="null")
     *             }
     *         )
     *     )
     * )
     *
     * @param string $jitsiRoomCode  the Jitsi room code
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function nextCommandByVideoconference(string $jitsiRoomCode): DataResponse {
        return $this->handleExceptions(function () use ($jitsiRoomCode) {
            $procedure = $this->procedureService->findByJitsiRoomCode($jitsiRoomCode)->getInfo('entity');

            if (empty($procedure)) {
                $response = new ResponseInfo();
                $response->setError('Wrong Jitsi room code.');

                return new DataResponse($response->getData(), HTTP::STATUS_NOT_FOUND);
            }

            $response = $this->service->getNextByProcedure(MessageChannel::COMMAND->value, $procedure->getId());
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Obtain message to be sent in queue given its identifier.
     *
     * @OA\Get(
     *     path="/web/2.0/message-queue/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Obtain message to be sent in queue given its identifier"
     *     )
     * )
     *
     * @param int $id  identifier of the message in queue to get
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function find(int $id): DataResponse {
        return parent::find($id);
    }

    /**
     * Add a new message to the queue from the given JSON data.
     *
     * @OA\Post(
     *     path="/web/2.0/message-queue",
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
     *         description="Add a new message to the queue from the given JSON data"
     *     )
     * )
     *
     * @param string $data  fields of the entity to create with its values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(array $data): DataResponse {
        return parent::create($data);
    }

    /**
     * Add a new message to the queue from the given JSON data.
     *
     * @OA\Post(
     *     path="/web/2.0/videoconference/{jitsiRoomCode}/message/{channel}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="channel", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"data"},
     *             @OA\Property(property="data", type="object", additionalProperties=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="New message added to the queue"
     *     )
     * )
     *
     * @param string $jitsiRoomCode  the Jitsi room code
     * @param string $channel        the channel name
     * @param array  $data           fields of the entity to create with its values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function createByVideoconference(string $jitsiRoomCode, string $channel, array $data): DataResponse {
        return $this->handleExceptions(function () use ($jitsiRoomCode, $channel, $data) {
            $procedure = $this->procedureService->findByJitsiRoomCode($jitsiRoomCode)->getInfo('entity');

            if (empty($procedure)) {
                $response = new ResponseInfo();
                $response->setError('Wrong Jitsi room code.');

                return new DataResponse($response->getData(), HTTP::STATUS_NOT_FOUND);
            }

            $data['procedureId'] = $procedure->getId();
            $data['channel'] = $channel;

            $response = $this->service->create($data, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Add a new command to the queue from the given JSON data.
     *
     * @OA\Post(
     *     path="/web/2.0/videoconference/{jitsiRoomCode}/command",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"data"},
     *             @OA\Property(property="data", type="object", additionalProperties=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="New command added to the queue"
     *     )
     * )
     *
     * @param string $jitsiRoomCode  the Jitsi room code
     * @param array  $data           fields of the entity to create with its values
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function createCommandByVideoconference(string $jitsiRoomCode, array $data): DataResponse {
        return $this->handleExceptions(function () use ($jitsiRoomCode, $data) {
            $procedure = $this->procedureService->findByJitsiRoomCode($jitsiRoomCode)->getInfo('entity');

            if (empty($procedure)) {
                $response = new ResponseInfo();
                $response->setError('Wrong Jitsi room code.');

                return new DataResponse($response->getData(), HTTP::STATUS_NOT_FOUND);
            }

            $data['procedureId'] = $procedure->getId();
            $data['channel'] = MessageChannel::COMMAND->value;

            $response = $this->service->create($data, $this->userId);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Update message in queue related to the given JSON data.
     *
     * @OA\Put(
     *     path="/web/2.0/message-queue",
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
     *         description="Update message in queue related to the given JSON data"
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
     * Delete message from queue by given id.
     *
     * @OA\Delete(
     *     path="/web/2.0/message-queue/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Message successfully deleted from the queue"
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

    /**
     * Delete message from queue by given id.
     *
     * @OA\Delete(
     *     path="/web/2.0/videoconference/{jitsiRoomCode}/message/{channel}/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="channel", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Message successfully deleted from the queue"
     *     )
     * )
     *
     * @param string $jitsiRoomCode  the Jitsi room code
     * @param string $channel        the channel name
     * @param int $id                the identifier of the entity to delete
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function deleteByVideoconference(string $jitsiRoomCode, string $channel, int $id): DataResponse {
        return parent::delete($id);
    }

    /**
     * Delete command from queue by given id.
     *
     * @OA\Delete(
     *     path="/web/2.0/videoconference/{jitsiRoomCode}/command/{id}",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Command successfully deleted from the queue"
     *     )
     * )
     *
     * @param string $jitsiRoomCode  the Jitsi room code
     * @param int $id                the identifier of the entity to delete
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function deleteCommandByVideoconference(string $jitsiRoomCode, int $id): DataResponse {
        return parent::delete($id);
    }
}
