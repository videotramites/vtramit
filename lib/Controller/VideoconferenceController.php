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
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\VTramit\Db\Videoconference;
use OCA\VTramit\Exception\ControllerErrors;
use OCA\VTramit\Service\VideoconferenceService;

class VideoconferenceController extends Controller {
    /** @var VideoconferenceService */
    private $service;

    /** @var string */
    private $userId;


    use ControllerErrors;

    public function __construct(
        $appName,
        IRequest $request,
        VideoconferenceService $service,
        $userId
    ) {

        parent::__construct($appName, $request);
        $this->service = $service;
        $this->userId = $userId;
    }

    /**
     * By default, response is empty. We need something to filter.
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): DataResponse {
        return new DataResponse([]);
    }

    /**
     * Manage the notification of waiting for videoconference. This notification
     * must be sent by the videoconference system, like Jitsi.
     *
     * @OA\Post(
     *     path="/web/2.0/procedure/videoconference/{jitsiRoomCode}/waiting",
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(name="jitsiRoomCode", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="List of procedures awaiting to be attended by videoconference"
     *     )
     * )
     *
     * @param string $jitsiRoomCode  the room identifier
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    public function procedureWaiting($jitsiRoomCode): DataResponse {
        return $this->handleExceptions(function () use ($jitsiRoomCode) {
            $response = $this->service->procedureConnectionNotified($jitsiRoomCode);
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }

    /**
     * Get the list of procedures waiting to be attended.
     *
     * @OA\Get(
     *     path="/web/2.0/procedure/videoconference/op/waiting",
     *     security={{"basicAuth": {}}},
     *     @OA\Response(
     *         response="200",
     *         description="List of procedures awaiting to be attended by videoconference"
     *     )
     * )
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function waitingProcedures(): DataResponse {
        return $this->handleExceptions(function () {
            $response = $this->service->findConnectedProcedures();
            return new DataResponse($response->getData(), HTTP::STATUS_OK);
        });
    }
}