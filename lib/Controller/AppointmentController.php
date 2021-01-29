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

namespace OCA\VTramit\Controller;

use OCP\IRequest;
use OCP\ILogger;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\VTramit\Service\AppointmentService;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Db\Appointment;

class AppointmentController extends Controller {
    /** @var AppointmentService */
    private $service;

    /** @var ConfigService */
    private $configService;

    /** @var string */
    private $userId;

    private $logger;

    use Errors;

    public function __construct(
        $appName,
        IRequest $request,
        AppointmentService $service,
        ConfigService $configService,
        $userId,
        ILogger $logger
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->configService = $configService;
        $this->userId = $userId;
        $this->logger = $logger;
    }

    /**
     * @NoAdminRequired
     */
    public function getAppointments(string $id = '', string $due = '', array $states = [], array $departments = [], array $users = [], bool $unassigned = false): DataResponse {
        switch ($due) {
            case "overdue":
                $minDate = strtotime('-'.$this->configService->getHistoryDays().' days');
                $maxDate = strtotime('today');
                break;
            case "dueToday":
                $minDate = strtotime('today');
                $maxDate = strtotime('tomorrow');
                break;
            case "dueWeek":
                $minDate = strtotime('today');
                $maxDate = strtotime('+1 week');
                break;
            case "dueMonth":
                $minDate = strtotime('today');
                $maxDate = strtotime('+1 month');
                break;
        }
        return new DataResponse($this->service->findByDepartmentAndDate($this->userId, $id, $minDate, $maxDate, $states, $departments, $users, $unassigned));
    }

    /**
     * @NoAdminRequired
     */
    public function index(): DataResponse {
        return new DataResponse($this->service->findByDepartmentAndDate($this->userId));
    }

    /**
     * @NoAdminRequired
     */
    public function filterAppointments(int $state = 0): DataResponse {
        // TODO: Create new function on AppointmentService to not use findAll()
        return new DataResponse($this->service->findAll($state));
    }

    /**
     * @NoAdminRequired
     */
    public function show(int $id): DataResponse {
        return $this->handleNotFound(function () use ($id) {
            $data = $this->service->find($id);
            return new DataResponse($data, HTTP::STATUS_OK);
        });
    }

    /**
     * @NoAdminRequired
     */
    public function create(
        string $externalId,
        string $citizenId,
        string $department,
        string $comments,
        int $date,
        string $name,
        string $phone,
        string $email,
        string $topic,
        string $assignedTo,
        string $userId
    ): DataResponse {

        $data = $this->service->create(
            $externalId,
            $citizenId,
            $department,
            $comments,
            $date,
            $name,
            $phone,
            $email,
            $topic,
            $assignedTo,
            $this->userId
        );

        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
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
        string $topic,
        string $assignedTo
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
                $topic,
                $assignedTo
            ) {

                $data = $this->service->update(
                    $id,
                    $externalId,
                    $citizenId,
                    $department,
                    $comments,
                    $date,
                    $name,
                    $phone,
                    $email,
                    $topic,
                    $assignedTo,
                    $this->userId
                );

                return new DataResponse($data, HTTP::STATUS_OK);
            }
        );
    }

    /**
     * @NoAdminRequired
     */
    public function destroy(int $id): DataResponse {
        return $this->handleNotFound(function () use ($id) {
            $data = $this->service->deleteById($id);
            return new DataResponse($data, HTTP::STATUS_OK);
        });
    }

    /**
     * @NoAdminRequired
     */
    public function getUserDepartments(): DataResponse {
        $data = $this->service->getUserDepartments($this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }


    /**
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
     * @param string $appointmentId identifier for the appoinment to notify
     * @return array         information about result status and message status
     */
    public function sendMailByAppointmentId(int $appointmentId): DataResponse {
        $data = $this->service->sendMailByAppointmentId($appointmentId ,true);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to pendant.
     * 
     * @NoAdminRequired
     *
     * @param string $appointmentId identifier for the appoinment to notify
     * @return array         information about result status and message status
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
     * @param string $appointmentId identifier for the appoinment to notify
     * @return array         information about result status and message status
     */
    public function updateStateToOnCourse(int $appointmentId): DataResponse {
        $data = $this->service->changeStateById($appointmentId, Appointment::STATE_ON_COURSE, $this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to cancelled.
     * 
     * @NoAdminRequired
     *
     * @param string $appointmentId identifier for the appoinment to notify
     * @return array         information about result status and message status
     */
    public function updateStateToFinished(int $appointmentId): DataResponse {
        $data = $this->service->changeStateById($appointmentId, Appointment::STATE_FINISHED, $this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Change the state of an appointment to cancelled.
     * 
     * @NoAdminRequired
     *
     * @param string $appointmentId identifier for the appoinment to notify
     * @return array         information about result status and message status
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
     * @param string $appointmentId identifier for the appoinment to notify
     * @return array         information about result status and message status
     */
    public function updateStateToCancelled(int $appointmentId): DataResponse {
        $data = $this->service->cancelAppointmentById($appointmentId, $this->userId);
        return new DataResponse($data, HTTP::STATUS_OK);
    }

    /**
     * Receive notification of finished videoconference.
     * 
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
}
