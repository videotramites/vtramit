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

namespace OCA\VTramit\Service;

use Exception;
use DateTime;
use DateTimeZone;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\Constants;
use OCP\Share\IShare;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCP\IL10N;

use OCA\Files_Sharing\Controller\ShareAPIController;
use OCA\Provisioning_API\Controller\UsersController;
use OCA\Provisioning_API\Controller\GroupsController;

use OCA\VTramit\Db\Appointment;
use OCA\VTramit\Db\AppointmentMapper;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\QueueService;
use OCA\VTramit\Service\VideoconferenceService;
use OCA\VTramit\Helper\UserGroupHelper;

class AppointmentService {

    /** @var ILogger */
    private $logger;

    /** @var IL10N */
    private $translate;

    /** @var AppointmentMapper */
    private $mapper;

    /** @var IDBConnection */
    private $db;

    /** @var ConfigService */
    private $configService;

    /** @var QueueService */
    private $queueService;

    /** @var VideoconferenceService */
    private $videoconferenceService;

    /** @var IRootFolder */
    private $rootFolder;

    /** @var ShareAPIController */
    private $shareManager;

    /** @var IURLGenerator */
    private $urlGenerator;

    /** @var UserGroupHelper */
    private $userGroupHelper;

    public function __construct(
        ILogger $logger,
        IL10N $translate,
        AppointmentMapper $mapper,
        IDBConnection $db,
        ConfigService $configService,
        QueueService $queueService,
        VideoconferenceService $videoconferenceService,
        IRootFolder $rootFolder,
        ShareAPIController $shareManager,
        IURLGenerator $urlGenerator,
        UserGroupHelper $userGroupHelper
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->mapper = $mapper;
        $this->db = $db;
        $this->configService = $configService;
        $this->queueService = $queueService;
        $this->videoconferenceService = $videoconferenceService;
        $this->rootFolder = $rootFolder;
        $this->shareManager = $shareManager;
        $this->urlGenerator = $urlGenerator;
        $this->userGroupHelper = $userGroupHelper;
    }

    private function handleException(Exception $e): void {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new AppointmentNotFound($e->getMessage());
        } else {
            throw $e;
        }
    }

    /**
     * Get the list of departments allowed for the user.
     *
     * @param int $userId  user identifier
     * return array        list of allowed departments
     */
    public function getUserContext(string $userId) {
        $groups = $this->userGroupHelper->getAllowedGroupsForUser($userId);
        $users = [];
        $userGroups = [];
        $filterDepartments = [];
        $filterUsers = [];
        foreach($groups as $group) {
            $usersInGroup = $this->userGroupHelper->getAssignableUsersByGroup($group, $userId);
            $userGroups[$group] = $usersInGroup;
            $filterUsers = $filterUsers + $usersInGroup; // Avoid array_merge because we don't want to loss key index
            $filterDepartments[$group] = $group;
        }

        foreach($this->configService->getGroups() as $group) {
            $usersInGroup = $this->userGroupHelper->getUsersByGroup($group, true);
            $users = $users + $usersInGroup; // Avoid array_merge because we don't want to loss key index
        }

        asort($users, SORT_NATURAL | SORT_FLAG_CASE);
        asort($filterUsers, SORT_NATURAL | SORT_FLAG_CASE);
        $data['departments'] = $userGroups;
        $data['users'] = $users;
        $data['filterDepartments'] = $filterDepartments;
        $data['filterUsers'] = $filterUsers;
        $data['isAdmin'] = $this->userGroupHelper->isAdmin($userId);

        return $data;
    }

    public function find($id) {
        try {
            return $this->mapper->find($id);

        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function findAll(): array {
        try {
            return $this->mapper->findAll();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function findByDepartmentAndDate(
        string $userId,
        string $id = '',
        int $minDate = null,
        int $maxDate = null,
        array $states = [],
        array $departments = [],
        array $users = [],
        bool $unassigned = false
    ): array {

        try {
            if (empty($departments)) {
                $departments = $this->userGroupHelper->getAllowedGroupsForUser($userId);
            }
            if (empty($minDate) && empty($maxDate)) {
                $minDate = strtotime('today');
                $maxDate = strtotime('tomorrow');
            }
            $appointments = $this->mapper->findByDepartmentAndDate($departments, $id, $minDate, $maxDate, $states, $users, $unassigned);
            return $this->prepareListForPanel($appointments, $userId);

        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function create($externalId, $citizenId, $department, $comments, $date, $name, $phone, $email, $topic, $assignedTo, $userId) {
        $result = $this->validateInputData(
            'create',
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
            $userId);

        if ($result['result'] == 'KO') {
            return $result;
        }
        // $utcTz = new DateTimeZone('UTC');

        $appointment = new Appointment();
        $appointment->setExternalId($externalId);
        $appointment->setCitizenId($citizenId);
        $appointment->setDepartment($department);
        $appointment->setComments($comments);
        $appointment->setDate($date);
        $utcDate = gmdate("Y-m-d H:i:00", (int) $date);
        $appointment->setName($name);
        $appointment->setPhone($phone);
        $appointment->setEmail($email);
        $appointment->setTopic($topic);
        $appointment->setAssignedTo($assignedTo);

        $appointment->setJitsiRoomCode(urlencode(strtolower($appointment->getExternalId(). '-' . $this->userGroupHelper->random_string(16))));
        $appointment->setJitsiRoomInformador($this->configService->getJitsiStaffUrl() . '/' . $appointment->getJitsiRoomCode());
        $appointment->setJitsiRoomCiutada($this->configService->getJitsiCitizenUrl() . '/' . $appointment->getJitsiRoomCode());

        $appointment->setSharedUrlUploads('');
        $appointment->setSharedUrlDownloads('');

        $appointment->setState(Appointment::STATE_INITIALIZING);
        $appointment->setUserId($userId);

        $result = $this->preProcess($appointment, false);
        if (!$result['save']) {
            if ($appointment->getState() == Appointment::STATE_INITIALIZING) {
                $this->mapper->delete($appointment);
            }
            return $result;
        }

        $this->mapper->insert($appointment);

        $result = $this->createAppointmentStructure($appointment, false, true, $userId);
        if(isset($result['message'])) {
            $this->logger->debug($result['message'],['app' => 'vtramit']);
        }

        if ($result['result'] == 'KO') {
            return $result;
        }

        if($result['save']) {
            $appointment->setState($appointment->isDateToday() ? Appointment::STATE_PENDANT : Appointment::STATE_CREATED);
            $this->mapper->update($appointment);
        }

        return $result;
    }

    public function update($id, $externalId, $citizenId, $department, $comments, $date, $name, $phone, $email, $topic, $assignedTo, $userId) {
        $result = $this->validateInputData(
            'update',
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
            $userId);

        if ($result['result'] == 'KO') {
            return $result;
        }

        $appointment = $this->mapper->find($id);

        if ($externalId == $appointment->getExternalId()
            && $citizenId == $appointment->getCitizenId()
            && $department == $appointment->getDepartment()
            && $comments == $appointment->getComments()
            && $date == $appointment->getDate()
            && $name == $appointment->getName()
            && $phone == $appointment->getPhone()
            && $email == $appointment->getEmail()
            && $topic == $appointment->getTopic()
            && (empty($assignedTo) || $assignedTo == $appointment->getAssignedTo())
        ) {
            return [
                'result' => 'OK',
                'message' => $this->translate->t('Appointment %s is already registered for citizen with identification %s', [$appointment->getExternalId(), $appointment->getCitizenId()]),
                'appointment' => $appointment
            ];
        }

        $updateStatus = false;
        if ($appointment->getExternalId() != $externalId || $appointment->getCitizenId() != $citizenId) {
            $appointment->setState(Appointment::STATE_INITIALIZING);
            $appointment->setUserId($userId);
            $updateStatus = true;
        }

        $sendMail = false;
        if ($appointment->getEmail() != $email) {
            $appointment->setEmail($email);
            $sendMail = true;
        }
        if ($appointment->getDate() != $date) {
            $sendMail = true;
        }

        $appointment->setExternalId($externalId);
        $appointment->setCitizenId($citizenId);
        $appointment->setDepartment($department);
        $appointment->setComments($comments);
        $appointment->setDate($date);
        $utcDate = gmdate("Y-m-d H:i:00", (int) $date);
        $appointment->setName($name);
        $appointment->setPhone($phone);
        $appointment->setTopic($topic);

        if (!empty($assignedTo)) {
            $appointment->setAssignedTo($assignedTo);
        }

        $result = $this->preProcess($appointment, true);
        if (!$result['save']) {
            return $result;
        }

        $result = $this->createAppointmentStructure($appointment, true, $sendMail, $userId);
        if(isset($result['message'])) {
            $this->logger->debug($result['message'],['app' => 'vtramit']);
        }

        if ($result['result'] == 'KO') {
            return $result;
        }

        if($result['save']) {
            if ($updateStatus) {
                $appointment->setState(Appointment::STATE_CREATED);
            }
            if ($appointment->isDateToday() && $appointment->getState() == Appointment::STATE_CREATED) {
                $appointment->setState(Appointment::STATE_PENDANT);
            }

            return $this->mapper->update($appointment);
        }

        return $result;
    }

    public function createOrUpdateAppointment(
        string $externalId,
        string $citizenId,
        string $department,
        string $comments,
        int $date,
        string $name,
        string $phone,
        string $email,
        string $topic,
        string $userId
    ) {

        $found = $this->mapper->findByExternalId($externalId);
        foreach($found as $potential_duplicated) {
            if ($potential_duplicated->getExternalId() == $externalId && $potential_duplicated->getCitizenId() == $citizenId) {
                if ($potential_duplicated->getState() == Appointment::STATE_COMPLETED || $potential_duplicated->getState() == Appointment::STATE_CANCELLED) {
                    return $potential_duplicated;
                }

                return $this->update(
                    $potential_duplicated->getId(),
                    $externalId,
                    $citizenId,
                    $department,
                    $comments,
                    $date,
                    $name,
                    $phone,
                    $email,
                    $topic,
                    '',
                    $userId);
            }
        }

        return $this->create(
            $externalId,
            $citizenId,
            $department,
            $comments,
            $date,
            $name,
            $phone,
            $email,
            $topic,
            '',
            $userId);
    }

    private function validateInputData(
        string $operation,
        string $externalId,
        string $citizenId,
        string $department,
        string $comments,
        int $date,
        string $name,
        string $phone,
        string $email,
        string $topic,
        $assignedTo,
        $userId
    ) {

        $result = ['result' => 'OK', 'message' => ''];
        $messages = [];

        if ($operation != 'update' && !preg_match("/^[A-Za-z0-9-_]+$/", $externalId)) {
            $messages[] = $this->translate->t('The appointment identifier can only contain letters, numbers, hyphens, and underscores');
        }

        if (!$this->userGroupHelper->isValidEmail($email)) {
            $messages[] = $this->translate->t('Email is not valid');
        }

        if (empty($department)) {
            $messages[] = $this->translate->t('Please select a department');
        }

        if (empty($date)) {
            $messages[] = $this->translate->t('Please select a date');
        }

        if (empty($name)) {
            $messages[] = $this->translate->t('Please indicate the citizen name');
        }

        /*if (empty($phone)) {
            $messages[] = $this->translate->t('Please indicate the citizen phone');
        }*/

        if (!empty($messages)) {
            $result = ['result' => 'KO', 'message' => $messages];
        }

        return $result;
    }

    /**
     * Cancel and anonymize an appointment. Cancellation is done if current
     * state if one of initializing, created, pendant or on course.
     *
     * If any mail is in queue to be sent, it is removed from the queue.
     *
     * It is compatible with appointments not registered in database, but that
     * have folders either created. Folders removal are always done, independently
     * of the current appointment state.
     *
     * @param Entity|Appointment  $appointment    appointment to cancel
     * @param int          $userId  identifier of user executing the operation
     * @return array                information about result status and message status
     */
    public function cancelAppointment($appointment, $userId) {
        // We need to keep cancellable state from the beginning as after first
        // step the state will be changed and anoymization would never be produced
        $cancellable = $appointment->allowStateCancelled();

        if ($cancellable) {
            $appointment->setState(Appointment::STATE_CANCELLED);
            $this->mapper->update($appointment);
        }

        $this->nextcloudDeletePath($this->configService->getLocalPathToAppointment($appointment, $userId));

        if ($cancellable) {
            $this->anonymizeAppointment($appointment);
        }
        if (!empty($appointment->getId())) {
            $this->queueService->deleteByAppointmentId($appointment->getId());
        }

        $appointment = $this->prepareAppointmentForPanel($appointment, $userId);
        return ['result' => 'OK', 'message' => $this->translate->t('Appointment cancelled'), 'appointment' => $appointment];
    }

    /**
     * Cancel and anonymize an appointment. Cancellation is done if current
     * state if one of initializing, created, pendant or on course.
     *
     * If any mail is in queue to be sent, it is removed from the queue.
     *
     * It is compatible with appointments not registered in database, but that
     * have folders either created. Folders removal are always done, independently
     * of the current appointment state.
     *
     * @param int $id      identifier of the appointment to cancel
     * @param int $userId  identifier of user executing the operation
     * @return array       information about result status and message status
     */
    public function cancelAppointmentById($id, $userId) {
        $appointment = $this->mapper->find($id);
        if (!empty($appointment)) {
            return $this->cancelAppointment($appointment, $userId);
        }

        return [
            'result' => 'KO',
            'message' => $this->translate->t('There is no appointment with the specified ID')
        ];
    }

    /**
     * Cancel and anonymize an appointment. Cancellation is done if current
     * state if one of initializing, created, pendant or on course.
     *
     * If any mail is in queue to be sent, it is removed from the queue.
     *
     * It is compatible with appointments not registered in database, but that
     * have folders either created. Folders removal are always done, independently
     * of the current appointment state.
     *
     * @param string $externalId  external identifier of the appointment to cancel
     * @param int    $userId      identifier of user executing the operation
     * @return array              information about result status and message status
     */
    public function cancelAppointmentByExternalId($externalId, $userId) {
        $appointments = $this->mapper->findByExternalId($externalId);
        if (!empty($appointments)) {
            foreach($appointments as $appointment) {
                return $this->cancelAppointment($appointment, $userId);
            }
        }

        return [
            'result' => 'KO',
            'message' => $this->translate->t('There is no appointment with the specified external ID')
        ];
    }

    /**
     * Delete given appointment, including removing the related folders.
     *
     * It is compatible with appointments not registered in database, but that
     * have folders either created.
     *
     * @param Entity|Appointment  $appointment  appointment to delete
     * @return Entity|Appointment        deleted entity
     */
    public function delete($appointment) {
        try {
            if (!empty($appointment->getId())) {
                $this->queueService->deleteByAppointmentId($appointment->getId());
                $this->mapper->delete($appointment);
            }
            $this->nextcloudDeletePath($this->configService->getLocalPathToAppointment($appointment));
            return ['result' => 'OK', 'message' => $this->translate->t('Appointment deleted'), 'appointment' => $appointment];

        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete the appointment with given identifier, including removing the
     * related folders.
     *
     * @param int $id       identifier of the appointment to delete
     * @return Entity|Appointment  deleted entity
     */
    public function deleteById($id) {
        $appointment = $this->mapper->find($id);
        if (!empty($appointment)) {
            return $this->delete($appointment);
        }

        return ['result' => 'KO', 'message' => $this->translate->t('There is no appointment with the specified ID')];
    }

    /**
     * Change the state of given appointment.
     *
     * @param Entity|Appointment  $appointment      appointment to change state
     * @param int          $newState  the new appointment state
     * @param int          $userId    identifier of user executing the operation
     * @return array                  information about result status and message status
     */
    public function changeState($appointment, $newState, $userId) {
        if ($appointment->getState() == $newState) {
            return ['result' => 'OK', 'message' => $this->translate->t('Appointment is already with requested status'), 'appointment' => $appointment];
        }

        $allowUpdate = false;
        switch($newState) {
            // Cancellation has a more complex treatment and requires
            // a different treatment
            case Appointment::STATE_CANCELLED:
                return $this->cancelAppointment($appointment, $userId);

            case Appointment::STATE_INITIALIZING:
                $allowUpdate = $appointment->allowStateInitializing();
                break;
            case Appointment::STATE_CREATED:
                $allowUpdate = $appointment->allowStateCreated();
                break;
            case Appointment::STATE_PENDANT:
                $allowUpdate = $appointment->allowStatePendant();
                break;
            case Appointment::STATE_ON_COURSE:
                $allowUpdate = $appointment->allowStateOnCourse();
                break;
            case Appointment::STATE_FINISHED:
                $allowUpdate = $appointment->allowStateFinished();
                break;
            case Appointment::STATE_COMPLETED:
                $allowUpdate = $appointment->allowStateCompleted();
                break;
        }

        if ($allowUpdate) {
            $appointment->setState($newState);
            $this->mapper->update($appointment);
            $appointment = $this->prepareAppointmentForPanel($appointment, $userId);
            return ['result' => 'OK', 'message' => $this->translate->t('Appointment updated'), 'appointment' => $appointment];
        }

        $appointment = $this->prepareAppointmentForPanel($appointment, $userId);
        return ['result' => 'KO', 'message' => $this->translate->t('New state not recognized'), 'appointment' => $appointment];
    }

    /**
     * Change the state of given appointment.
     *
     * @param int $id        identifier of the appointment to change state
     * @param int $newState  the new appointment state
     * @param int $userId    identifier of user executing the operation
     * @return array         information about result status and message status
     */
    public function changeStateById($id, $newState, $userId) {
        $appointment = $this->mapper->find($id);
        if (!empty($appointment)) {
            return $this->changeState($appointment, $newState, $userId);
        }

        $appointment = $this->prepareAppointmentForPanel($appointment, $userId);
        return ['result' => 'KO', 'message' => $this->translate->t('There is no appointment with the specified ID'), 'appointment' => $appointment];
    }

    /**
     * Purge old data which means 2 operations: remove appointment structure and
     * anonymize data in database.
     *
     * The operation is done for cancelled and completed appointments which state
     * has not been changed in the last 72 hours.
     */
    public function purgeData() {
        $age = 72; // hours

        // TODO: Què fem amb els casos de Appointment::STATE_ON_COURSE

        $toCancel = [Appointment::STATE_INITIALIZING, Appointment::STATE_CREATED, Appointment::STATE_PENDANT];

        $appointments = $this->mapper->findNotAnonymizedByAge($age);
        foreach($appointments as $appointment) {

            // Cancel old appointments not done
            if (in_array($appointment->getState(), $toCancel)) {
                $appointment->setState(Appointment::STATE_CANCELLED);
                $this->mapper->update($appointment);
            }

            // Remove files and anonymize database
            $this->nextcloudDeletePath($this->configService->getLocalPathToAppointment($appointment));
            $this->anonymizeAppointment($appointment);
        }

        $age = 24; // hours
        $completed = $this->mapper->findNotAnonymizedByStateAndAge(Appointment::STATE_COMPLETED, $age);
        foreach($completed as $appointment) {
            $this->nextcloudDeletePath($this->configService->getLocalPathToDocuments($appointment, $this->configService->getFolderUploadsName()));
        }
    }

    /**
     * Change state from created to pendant for all appointments to be done during today.
     *
     * @return array        information about result status and message status
     */
    public function prepareWorkForToday() {
        $minDate = strtotime('today');
        $maxDate = strtotime('tomorrow');

        $result = $this->mapper->changeStateByDateRange(Appointment::STATE_CREATED, Appointment::STATE_PENDANT, $minDate, $maxDate);

        if (is_int($result)) {
            return ['result' => 'OK', 'message' => $this->translate->t('%d appointments have been prepared to be attended today', [$result])];
        }
        else {
            return ['result' => 'KO', 'message' => $this->translate->t('An error occurred: %s', [$result])];
        }
    }

    /**
     * Send a new e-mail for the given appointment identifier. During the process
     * shared links are created with a citizen password. New shared links are set
     * in the appointment object.
     *
     * @param string $id    identifier for the appoinment to notify
     * @param bool   $save  whether the appointment must be saved with
     *                      updated shared folders
     * @return array        information about result status and message status
     */
    public function sendMailByAppointmentId($id, $save = false) {
        $appointment = $this->mapper->find($id);
        if (!empty($appointment)) {
            return $this->sendMailByAppointment($appointment, $save);
        }

        return ['result' => 'KO', 'message' => $this->translate->t('There is no appointment with the specified ID')];
    }

    /**
     * Send a new e-mail for the given appointment. During the process shared
     * links are created with a citizen password. New shared links are set in
     * the appointment object.
     *
     * @param string $appointment  appoinment to notify
     * @param bool   $save  whether the appointment must be saved with
     *                      updated shared folders
     * @return array        information about result status and message status
     */
    public function sendMailByAppointment($appointment, $save = false) {
        if (!empty($appointment->getEmail())) {
            $citizePassword = $this->userGroupHelper->getRandomPIN(8);
            $this->shareCitizenFolder($appointment, $citizePassword);
            if ($save) {
                $this->mapper->update($appointment);
            }

            $subject = $this->configService->getGroupMailSubject($appointment->getDepartment());
            $body = $this->getMailBody($appointment, $citizePassword);
            $this->queueService->create($appointment->getId(), $appointment->getEmail(), '', '', $subject, $body);

            return ['result' => 'OK', 'message' => $this->translate->t('Recorded message for sending'), 'save' => true, 'appointment' => $appointment];
        }
        //
        return ['result' => 'KO', 'message' => $this->translate->t('There is no email to address the message')];
    }

    /**
     * Receive notification of finished videoconference.
     *
     * @param string $jitsiRoomCode videoconference room
     * @param int    $userId        identifier of user executing the operation
     * @return array                information about result status and message status
     *
     */
    public function videoconferenceFinished(string $jitsiRoomCode, $userId = null) {
        $appointments = $this->mapper->findByJitsiRoomCodeAndState($jitsiRoomCode, array(Appointment::STATE_PENDANT, Appointment::STATE_ON_COURSE));
        $current_appointment = null;
        foreach($appointments as $appointment) {
            if ($appointment->isDateToday()) {
                $appointment->setState(Appointment::STATE_FINISHED);
                $this->mapper->update($appointment);
                $current_appointment = $appointment;
            }
        }

        if (!empty($current_appointment)) {
            $current_appointment = $this->prepareAppointmentForPanel($current_appointment, $userId);
            if ($this->configService->existsGroupVideoconferenceConfirmationForm($current_appointment->getDepartment())) {
                return [
                    'result' => 'OK',
                    'message' => $this->translate->t('Appointment completed'),
                    'save' => false,
                    'appointment' => $current_appointment,
                    'redirect' => $this->configService->getGroupVideoconferenceConfirmationForm($current_appointment->getDepartment())
                ];
            }
            return [
                'result' => 'OK',
                'message' => $this->translate->t('Appointment completed'),
                'save' => false,
                'appointment' => $current_appointment,
                'redirect' => false
            ];
        }
        else {
            return [
                'result' => 'ERROR',
                'message' => $this->translate->t('No appointments found today and in progress for the notified video conference'),
                'save' => false,
                'appointment' => '',
                'redirect' => false
            ];
        }
    }

    /**
     * Pre process appointment checking if is valid.
     *
     * @param Entity|Appointment $appointment       the appointment to pre-process
     * @param bool        $isUpdate   true if appointment already exists and is being
     *                                modificated; false if processing a brand new appointment
     * @return array                  information about result status and message status
     */
    protected function preProcess($appointment, $isUpdate) {
        // This situation can happen ONLY when data is sent from via API
        if ($appointment->getDate() <= strtotime('-1 days') && !$isUpdate) {
            $this->delete($appointment);
            return [
                'result' => 'OK',
                'message' => $this->translate->t('Appointment deleted due to age'),
                'save' => false,
                'appointment' => $appointment
            ];
        }

        // This situation can happen ONLY when data is sent from via API
        if (empty($appointment->getExternalId()) || empty($appointment->getCitizenId())) {
            return [
                'result' => 'ERROR',
                'message' => $this->translate->t('The appointment and personal ID are mandatory'),
                'save' => false,
                'appointment' => $appointment
            ];
        }

        return ['result' => 'OK', 'message' => '', 'save' => true, 'appointment' => $appointment];
    }

    /**
     * Create the folders structure for the appointment and send an e-mail
     * notification to the citizen.
     *
     * Is also removes duplicated appointments.
     *
     * @param Entity|Appointment $appointment       the appointment for which structure must be created
     * @param bool        $isUpdate   true if appointment already exists and is being
     *                                modificated; false if processing a brand new appointment
     * @param bool        $sendEmail  true to send information by e-mail
     * @param int         $userId     identifier of user executing the operation
     * @return array                  information about result status and message status
     */
    protected function createAppointmentStructure($appointment, $is_update, $sendEmail, $userId) {
        $this->removeDuplicatedAppointment($appointment, $userId);
        $this->createCitizenFolders($appointment, $userId);
        $this->createReadme($appointment, $userId);

        if (!$sendEmail) {
            return [
                'result' => 'OK',
                'message' => $this->translate->t('Recorded appointment'),
                'save' => true,
                'appointment' => $appointment
            ];
        }

        if ($this->configService->isAllowSendEmails()) {
            $result = $this->sendMailByAppointment($appointment, false);
            if ($result['result'] == 'OK') {
                return [
                    'result' => 'OK',
                    'message' => $this->translate->t('Recorded appointment'),
                    'save' => true,
                    'appointment' => $appointment
                ];
            }
            else {
                return [
                    'result' => 'OK',
                    'message' => $this->translate->t('Recorded appointment without sending mail (recipient email not reported)'),
                    'save' => true,
                    'appointment' => $appointment
                ];
            }
        } else {
            return [
                'result' => 'OK',
                'message' => $this->translate->t('Recorded appointment without sending mail (service disabled)'),
                'save' => true,
                'appointment' => $appointment
            ];
        }
    }

    /**
     * Check if appointment is resolved accordingly to existant comments.
     *
     * @param Entity|Appointment $appointment  appointment to check
     * return bool               true if appointment is solved; otherwise, false
     */
    protected function isAppointmentSolved($appointment) {
        if (!empty($appointment->getComments())) {
            return $this->isAppointmentSolvedAccordinglyToComment($appointment->getComments());
        }

        return false;
    }

    /**
     * Check if appointment is resolved accordingly to given comments.
     *
     * @param string $comment  comment to check
     * return bool             true if appointment is solved; otherwise, false
     */
    protected function isAppointmentSolvedAccordinglyToComment($comment) {
        $estats_finals = array('JATRAMITAT', 'RESOLT', 'NC3');

        $parts = explode('_', $comment);
        if (in_array($parts[0], $estats_finals)) {
            return true;
        }

        return false;
    }

    /**
     * Anonymize given appointment.
     *
     * @param Entity|Appointment $appointment  appointment to anonymize
     */
    public function anonymizeAppointment($appointment) {
        $name = $this->userGroupHelper->random_string(100, ' ');
        $citizenId = $this->userGroupHelper->random_string(12);
        $phone = $this->userGroupHelper->getRandomPhone();
        $email = $this->userGroupHelper->random_string(15).'@anonymous.com';
        $comments = $this->userGroupHelper->random_string(200, ' ');

        $appointment->setName($name);
        $appointment->setCitizenId($citizenId);
        $appointment->setPhone($phone);
        $appointment->setEmail($email);
        $appointment->setComments($comments);

        $this->mapper->update($appointment);
    }

    /**
     * Search for duplicated appointments and remove them.
     *
     * Search query
     * SELECT * FROM `oc_filecache` WHERE name = '{cita_id}' AND path NOT like 'files/{document_ciutada}/{cita_id}'
     *
     * @param Entity|Appointment $appointment    the appointment for which structure must be created
     * @param int         $userId  identifier of user executing the operation
     */
    protected function removeDuplicatedAppointment($appointment, $userId) {
        $appointments_to_cancel = $this->mapper->cancelByExternalId($appointment->getExternalId(), array($appointment->getId()));
        foreach($appointments_to_cancel as $cancel) {
            $this->cancelAppointment($cancel, $userId);
        }
    }

    /**
     * Create appointment folders for the citizen.
     *
     * @param Entity|Appointment $appointment    the appointment for which structure must be created
     * @param int         $userId  identifier of user executing the operation
     */
    protected function createCitizenFolders($appointment, $userId) {
        // Example: my-admin/{$departament}/{$citizen_id}/{$appointment_id}/Entrada
        //          my-admin/{$departament}/{$citizen_id}/{$appointment_id}/Sortida

        // Create upload folder
        $path = $this->configService->getLocalPathToDocuments($appointment, $this->configService->getFolderUploadsName(), $userId);
        $this->nextcloudCreatePath($path, $userId);

        // Create download folder
        $path = $this->configService->getLocalPathToDocuments($appointment, $this->configService->getFolderDownloadsName(), $userId);
        $this->nextcloudCreatePath($path);
    }

    /**
     * Create a share link for the citizen appointment folders.
     *
     * @param Entity|Appointment $appointment    the appointment for which sharing must be created
     * @param int         $userId  identifier of user executing the operation
     * @return Entity|Appointment         updated appointment with the links to shared folders
     */
    protected function shareCitizenFolder($appointment, $citizePassword) {
        // Shared link for upload folder
        $url = $this->configService->getUserRelativePathToDocuments($appointment, $this->configService->getFolderUploadsName());
        $url_uploads = $this->nextcloudShareFolder($url, $citizePassword, true);
        $appointment->setSharedUrlUploads($url_uploads);

        // Shared link for download folder
        $url = $this->configService->getUserRelativePathToDocuments($appointment, $this->configService->getFolderDownloadsName());
        $url_downloads = $this->nextcloudShareFolder($url, $citizePassword, false);
        $appointment->setSharedUrlDownloads($url_downloads);

        return $appointment;
    }

    /**
     * Create file Readme.md and its content
     *
     * @param Entity|Appointment $appointment    the appointment for which readme must be created
     * @param int         $userId  identifier of user executing the operation
     */
    protected function createReadme($appointment, $userId) {
        $content = $this->generateReadmeContent($appointment);

        $fullpath = $this->configService->getLocalPathToReadme($appointment);
        $folder = $this->nextcloudCreatePath(dirname($fullpath));

        $readme = $folder->newFile(basename($fullpath));
        $readme->putContent($content);
    }

    /**
     * Generated the message to insert into the Readme file
     */
    private function generateReadmeContent($appointment) {
        $content = '## ['.$this->translate->t('Videconference').'](' . $appointment->getJitsiRoomInformador() . '#config.followMe=true&config.autoStartRecording=true)'."\n";
        if (strlen($appointment->getPhone()) > 3) {
            if (!empty($this->configService->getPhoneLink())) {
                $content .= ' ['.$appointment->getPhone().']('.$this->configService->getPhoneLink().':'.$this->configService->getPhonePrefix().$appointment->getPhone().')';
            }
            else {
                $content .= ' ['.$appointment->getPhone().']';
            }
        }

        $content .= ' ['.$appointment->getEmail().'](mailto:'.$appointment->getEmail().')'."\n\n";

        if ($this->configService->existsGroupVideoconferenceConfirmationForm($appointment->getDepartment())) {
          $content .= ' ['.$this->translate->t('Appointment form').']('.$this->configService->getGroupVideoconferenceConfirmationForm($appointment->getDepartment()).')'."\n\n";
        }

        $content .= ' '.$appointment->getName().' - '.$appointment->getCitizenId()."\n\n";
        $content .= ' '.$this->translate->t('Date').': '.$appointment->getDateAsString()."\n\n### ";
        $content .= "\n";

        return $content;
    }


    //
    // NextCloud helpers
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Indica si la carpeta per a la cita indicada ja existeix o no
     */
    private function nextcloudFolderExists($appointment) {
        return $this->rootFolder->nodeExists($this->configService->getLocalPathToAppointment($appointment));
    }

    private function nextcloudCreatePath($path) {
        $parts = explode(ConfigService::DIRECTORY_SEPARATOR, $path);
        $folder = $this->rootFolder;

        $this->logger->critical($path,['app' => 'vtramit']);

        if ($folder->nodeExists($path)) {
            $folder = $folder->get($path);
            return $folder;
        }

        foreach($parts as $part) {
            if (!empty($part)) {

                if (!$folder->nodeExists($part)) {
                    $folder = $folder->newFolder($part);
                }
                else {
                    $folder = $folder->get($part);
                    if (empty($folder)) {
                        break;
                    }
                }
            }
        }

        return $folder;
    }

    private function nextcloudShareFolder($path, $password, $allowUploads) {
        // Documentation on
        // https://docs.nextcloud.com/server/15/developer_manual/core/ocs-share-api.html

        // POST Arguments: path - (string) path to the file/folder which should be shared
        // POST Arguments: shareType - (int) 0 = user; 1 = group; 3 = public link; 6 = federated cloud share
        // POST Arguments: shareWith - (string) user / group id with which the file should be shared
        // POST Arguments: publicUpload - (string) allow public upload to a public shared folder (true/false)
        // POST Arguments: password - (string) password to protect public link Share with
        // POST Arguments: permissions - (int) 1 = read; 2 = update; 4 = create; 8 = delete; 16 = share; 31 = all (default: 31, for public shares: 1)

        $response = $this->shareManager->createShare(
            $path,
            $allowUploads ? Constants::PERMISSION_CREATE : Constants::PERMISSION_READ,
            IShare::TYPE_LINK,
            null,
            false,
            $password
        );

        if ($allowUploads) {
            $qb = $this->db->getQueryBuilder();
            $qb->update('share')
                ->set('permissions', $qb->createNamedParameter(4, IQueryBuilder::PARAM_INT))
                ->where($qb->expr()->eq('file_target', $qb->createNamedParameter('/'.$this->configService->getFolderUploadsName(), IQueryBuilder::PARAM_STR)))
                ->andWhere($qb->expr()->gte('share_type', $qb->createNamedParameter(3, IQueryBuilder::PARAM_INT)));

            $qb->execute();

        }

        if (!empty($response) && !empty($response->getData()['url'])) {
            return $response->getData()['url'];
        }

        return '';
    }

    private function nextcloudDeletePath($path) {
        if (empty($path)) {
            return;
        }

        if (is_array($path)) {
            $root = $this->configService->getAdminRoot();
            $root_length = strlen($root);

            $trashbin = $this->configService->getTrashRoot();
            $trashbin_length = strlen($trashbin);
            foreach($path as $item) {
                $real_path = $item['path'];
                if (substr($real_path, 0, $trashbin_length) != $trashbin) {
                    $this->nextcloudDeletePath($real_path);
                }
            }
        }
        else {
            //$path = 'my-admin/files/my-department/11223344';
            if ($this->rootFolder->nodeExists($path)) {
                $folder = $this->rootFolder->get($path);
                $parent = $folder->getParent();
                $folder->delete();

                // If parent is empty, remove it also
                if (!empty($parent)) {
                    $children = $parent->getDirectoryListing();
                    if (empty($children)) {
                        $parent->delete();
                    }
                }
            }

            // Check if adding 'admin' user it exists
            else if ($this->rootFolder->nodeExists($this->configService->getAdminUser().ConfigService::DIRECTORY_SEPARATOR.$path)) {
                $this->nextcloudDeletePath($this->configService->getAdminUser().ConfigService::DIRECTORY_SEPARATOR.$path);
            }
        }
    }


    //
    // Mail helpers
    ////////////////////////////////////////////////////////////////////////////////

    private function getMailBody($appointment, $citizePassword) {
        $body = '';

        $group = $appointment->getDepartment();
        $departmentLongName = $this->configService->getGroupFullname($group);
        $address = $this->configService->getGroupAddress($group);
        $cp = $this->configService->getGroupZip($group);
        $phone = $this->configService->getGroupPhone($group);

        $data = $appointment->getDateAsString($this->configService->getDateFormat());
        $hora = $appointment->getDateAsString($this->configService->getTimeFormat());
        $cita_id = $appointment->getExternalId();
        $url_videotrucada = $appointment->getJitsiRoomCiutada();
        $url_nextcloud_uploads = $appointment->getSharedUrlUploads();
        $url_nextcloud_downloads = $appointment->getSharedUrlDownloads();
        $contrasenya = $citizePassword;
        $tramit = $appointment->getTopic();

        ob_start();
        if (file_exists(getcwd()."/apps/vtramit/lib/Service/templates/$group.tpl")) {
            include "templates/$group.tpl";
        }
        else {
            include "templates/default.tpl";
        }
        return ob_get_clean();
    }


    //
    // Other helpers
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Prepare given appointment for be shown in frontend
     *
     * @param array $appointments   list of appointments to prepare
     * @param int   $userId  identifier of user executing the operation
     */
    public function prepareListForPanel($appointments, $userId): array {
        foreach($appointments as &$appointment) {
            $appointment = $this->prepareAppointmentForPanel($appointment, $userId);
        }
        return $appointments;
    }

    /**
     * Prepare given appointment for be shown in frontend
     *
     * @param Entity|Appointment $appointment    the appointment to prepare for frontend
     * @param int         $userId  identifier of user executing the operation
     */
    public function prepareAppointmentForPanel($appointment, $userId) {
        $uploadsIsShareable = false;
        $downloadsIsShareable = false;

        // https://{domain}/index.php/apps/files/?dir=/{department}/{citizen_id}/{appointment_id}/Entrada
        $uploads_filepath = $this->configService->getLocalPathToDocuments($appointment, $this->configService->getFolderUploadsName(), $userId);

        if ($this->rootFolder->nodeExists($uploads_filepath)) {
            $uploads_webpath = $this->configService->getWebPathToDocuments($appointment, $this->configService->getFolderUploadsName(), $userId);
            $uploads_url = $this->urlGenerator->getAbsoluteURL($uploads_webpath);
            $appointment->setUrlUploads($uploads_url);
        }

        // https://{domain}/index.php/apps/files/?dir=/{department}/{citizen_id}/{appointment_id}/Sortida
        $downloads_filepath = $this->configService->getLocalPathToDocuments($appointment, $this->configService->getFolderDownloadsName(), $userId);

        if ($this->rootFolder->nodeExists($downloads_filepath)) {
            $downloads_webpath = $this->configService->getWebPathToDocuments($appointment, $this->configService->getFolderDownloadsName(), $userId);
            $downloads_url = $this->urlGenerator->getAbsoluteURL($downloads_webpath);
            $appointment->setUrlDownloads($downloads_url);
        }

        $appointment->setAllowSendEmail(true);
        $appointment->setIsWaitingForModerator($this->videoconferenceService->isWaitingForModerator($appointment));

        switch ($appointment->getState()) {
            case Appointment::STATE_INITIALIZING:
                $appointment->setStateDesc($this->translate->t('Initializing'));
                break;
            case Appointment::STATE_CREATED:
                $appointment->setStateDesc($this->translate->t('Created'));
                break;
            case Appointment::STATE_PENDANT:
                $appointment->setStateDesc($this->translate->t('Pendant'));
                break;
            case Appointment::STATE_ON_COURSE:
                $appointment->setStateDesc($this->translate->t('On course'));
                break;
            case Appointment::STATE_FINISHED:
                $appointment->setStateDesc($this->translate->t('Finished'));
                break;
            case Appointment::STATE_COMPLETED:
                $appointment->setStateDesc($this->translate->t('Completed'));
                break;
            case Appointment::STATE_CANCELLED:
                $appointment->setStateDesc($this->translate->t('Cancelled'));
                break;
        }

        return  $appointment;
    }

    /**
     * Get videoconference links by roomcode.
     *
     * @param string $jitsiRoomCode videoconference room
     * @return array jitsi links
     *
     */
    public function getVideoconferenceLinksByRoomcode(string $jitsiRoomCode) {
        $appointments = $this->mapper->findByJitsiRoomCode($jitsiRoomCode);
        $current_appointment = null;
        foreach($appointments as $appointment) {
            $current_appointment = $appointment;
        }

        if (!empty($current_appointment)) {
            return [
                'result' => 'OK',
                'message' => $this->translate->t('Appointment completed'),
                'appointment' => $current_appointment
            ];
        }
        else {
            return [
                'result' => 'ERROR',
                'message' => $this->translate->t('No appointment was found for the notified video conference'),
                'appointment' => ''
            ];
        }
    }
}
