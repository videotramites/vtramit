<?php
/**
 * @copyright Copyright (c) 2020-2021 Ajuntament de Barcelona
 * 
 * @author Daniel Tamajon <daniel@floss.cat>
 * @author Kenneth Peiruza <kenneth@floss.cat>
 * @author Letizia Benítez <letizia@floss.cat>
 * @author Marta González <gonzalez.marta@gmail.com>
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


/**
 * @OA\Info(title="VTramit", version="0.1")
 */

namespace OCA\VTramit\Controller;

use OCP\IRequest;
use OCP\ILogger;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

use OCA\VTramit\Db\Appointment;
use OCA\VTramit\Service\AppointmentService;

class AppointmentApiController extends ApiController {
    /** @var AppointmentService */
    private $service;

    /** @var string */
    private $userId;

    private $logger;

    use Errors;

    public function __construct(
        $appName,
        IRequest $request,
        AppointmentService $service,
        $userId,
        ILogger $logger
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->userId = $userId;
        $this->logger = $logger;
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function index(string $due = ''): DataResponse {
        //$this->logger->critical("API".print_r($due, true),['app' => 'vtramit']);
        return new DataResponse($this->service->findAll());
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function show(int $id): DataResponse {
        return $this->handleNotFound(function () use ($id) {
            $data = $this->service->find($id);
            return new DataResponse($data, HTTP::STATUS_OK);
        });
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function create(string $externalId,
                            string $citizenId,
                            string $department,
                            string $comments,
                            int $date,
                            string $name,
                            string $phone,
                            string $email,
                            string $topic,
                            string $userId): DataResponse {

        $data = $this->service->create($externalId,
                                        $citizenId,
                                        $department,
                                        $comments,
                                        $date,
                                        $name,
                                        $phone,
                                        $email,
                                        $topic,
                                        $this->userId);

        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function update(
        int $id,
        string $externalId,
        string $citizenId,
        string $department,
        string $comments,
        int $date,
        string $name,
        string $phone,
        string $email,
        string $topic
    ): DataResponse {

        return $this->handleNotFound(
            function () use (
                $id,
                $externalId,
                $citizenId,
                $department,
                $comments,
                $date,
                $name,
                $phone,
                $email,
                $topic
            ) {
                $data = $this->service->update($id,
                                                $externalId,
                                                $citizenId,
                                                $department,
                                                $comments,
                                                $date,
                                                $name,
                                                $phone,
                                                $email,
                                                $topic,
                                                $this->userId);

                return new DataResponse($data, HTTP::STATUS_OK);
            }
        );
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function destroy(int $id): DataResponse {
        return $this->handleNotFound(function () use ($id) {
            $data = $this->service->deleteById($id);
            return new DataResponse($data, HTTP::STATUS_OK);
        });
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function getUserDepartments(): DataResponse {
        $data = $this->service->getUserDepartments($this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function getUserContext(): DataResponse {
        $data = $this->service->getUserContext($this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Send a new e-mail for the given appointment identifier.
     *
     * @NoAdminRequired
     *
     * @param int $appointmentId identifier for the appoinment to notify
     * @return array      information about result status and message status
     */
    public function sendMailByAppointmentId(int $appointmentId): DataResponse {
        $data = $this->service->sendMailByAppointmentId($appointmentId, true);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to pendant.
     *
     * @NoAdminRequired
     *
     * @param int $appointmentId identifier for the appoinment to notify
     * @return array      information about result status and message status
     */
    public function updateStateToPendant(int $appointmentId): DataResponse {
        $data = $this->service->changeStateById($appointmentId, Appointment::STATE_PENDANT, $this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to on course.
     *
     * @NoAdminRequired
     *
     * @param int $appointmentId identifier for the appoinment to notify
     * @return array      information about result status and message status
     */
    public function updateStateToOnCourse(int $appointmentId): DataResponse {
        $data = $this->service->changeStateById($appointmentId, Appointment::STATE_ON_COURSE, $this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to finished.
     *
     * @NoAdminRequired
     *
     * @param int $appointmentId identifier for the appoinment to notify
     * @return array      information about result status and message status
     */
    public function updateStateToFinished(int $appointmentId): DataResponse {
        $data = $this->service->changeStateById($appointmentId, Appointment::STATE_FINISHED, $this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to completed.
     *
     * @NoAdminRequired
     *
     * @param int $appointmentId identifier for the appoinment to notify
     * @return array      information about result status and message status
     */
    public function updateStateToCompleted(int $appointmentId): DataResponse {
        $data = $this->service->changeStateById($appointmentId, Appointment::STATE_COMPLETED, $this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to cancelled.
     *
     * @NoAdminRequired
     *
     * @param int $appointmentId identifier for the appoinment to notify
     * @return array      information about result status and message status
     */
    public function updateStateToCancelled(int $appointmentId): DataResponse {
        $data = $this->service->cancelAppointmentById($appointmentId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to cancelled.
     *
     * @OA\Get(
     *     path="/api/0.1/cancelappointment",
     *     @OA\Parameter(name="externalId", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="Cancel appointment by given external ID")
     * )
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param string $externalId identifier for the appoinment to notify
     * @return array             information about result status and message status
     */
    public function updateStateToCancelledByExternalId(string $externalId): DataResponse {
        $data = $this->service->cancelAppointmentByExternalId($externalId, $this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Receive notification of finished videoconference.
     *
     * @OA\Get(
     *     path="/api/0.1/videoconferencefinished",
     *     @OA\Parameter(name="jitsiRoomCode", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="Notify finished videoconference")
     * )
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param string $jitsiRoomCode videoconference room
     * @return array                information about result status and message status
     *
     */
    public function videoconferenceFinished(string $jitsiRoomCode): DataResponse {
        $data = $this->service->videoconferenceFinished($jitsiRoomCode);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/0.1/createorupdateappointment",
     *     @OA\Parameter(name="externalId", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="citizenId", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="department", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="comments", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="name", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="phone", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="email", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="topic", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="Create or update appointment")
     * )
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function createOrUpdateAppointment(
        string $externalId,
        string $citizenId,
        string $department,
        string $comments = "",
        int $date,
        string $name,
        string $phone,
        string $email,
        string $topic = ""
    ): DataResponse {

        $data = $this->service->createOrUpdateAppointment(
            $externalId,
            $citizenId,
            $department,
            $comments,
            $date,
            $name,
            $phone,
            $email,
            $topic,
            $this->userId
        );

        return new DataResponse($data, HTTP::STATUS_OK);
    }
}
