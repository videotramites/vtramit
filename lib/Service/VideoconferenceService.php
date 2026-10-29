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

namespace OCA\VTramit\Service;

use OCP\AppFramework\Db\DoesNotExistException;
use Psr\Log\LoggerInterface;
use OCP\IL10N;

use OCA\VTramit\ProcedureStatus;

use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\Videoconference;
use OCA\VTramit\Db\VideoconferenceMapper;

use OCA\VTramit\Service\ConfigService;

class VideoconferenceService {

    /** @var LoggerInterface */
    private $logger;

    /** @var IL10N */
    protected $translate;

    /** @var VideoconferenceMapper */
    private $videoconferenceMapper;

    /** @var ProcedureMapper */
    private $procedureMapper;

    /** @var ConfigService */
    private $configService;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        VideoconferenceMapper $videoconferenceMapper,
        ProcedureMapper $procedureMapper,
        ConfigService $configService
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->videoconferenceMapper = $videoconferenceMapper;
        $this->procedureMapper = $procedureMapper;
        $this->configService = $configService;
    }

    /**
     * Create a new videoconference notification for given procedure, setting
     * first and last connection to current time.
     *
     * @param string $procedureId   identifier of the procedure notified
     * @return Entity|Videoconference  new videoconference notification entity
     */
    protected function create(int $procedureId) {
        $now = new \DateTime();
        $timestamp = $now->getTimestamp();

        $videoconference = new Videoconference();
        $videoconference->setProcedureId($procedureId);
        $videoconference->setFirstConnection($timestamp);
        $videoconference->setLastConnection($timestamp);

        return $this->videoconferenceMapper->insert($videoconference);
    }

    /**
     * Update the given videoconference notification, setting
     * last connection to current time.
     *
     * @param string $videoconference  videoconference notification to update
     * @return Entity|Videoconference  updated videoconference notification entity
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     */
    protected function updateVideoconference(Videoconference $videoconference) {
        $now = new \DateTime();
        $timestamp = $now->getTimestamp();

        $videoconference->setLastConnection($timestamp);

        return $this->videoconferenceMapper->update($videoconference);
    }

    /**
     * Main point entry for a videoconference notification. If there exists an
     * active connection for given room code, it is updated. Otherwise, a new
     * videoconference notification is created.
     *
     * @param string $roomCode  room code of the videoconference that initiated the notification
     * @return ResponseInfo
     */
    public function procedureConnectionNotified(string $roomCode): ResponseInfo {
        $response = new ResponseInfo();

        $filter = [
            'jitsiRoomCode' => $roomCode,
            'status' => [ProcedureStatus::PENDANT, ProcedureStatus::ON_COURSE]
        ];
        $procedures = $this->procedureMapper->findByFilter($filter);

        if (!empty($procedures)) {
            $firstForToday = null;
            foreach($procedures as $procedure) {
                if ($procedure->isAppointmentDateToday($this->configService->getDateTimezone())) {
                    try {
                        $videoconference = $this->videoconferenceMapper->findLastByProcedureId($procedure->getId());

                        // If videoconference is not currently connected we DON'T update it,
                        // instead we must force to create a new videoconference notification
                        if (!empty($videoconference) && !$videoconference->isConnected()) {
                            $videoconference = null;
                        }
                    }
                    catch(DoesNotExistException $e) {}

                    if (!empty($videoconference)) {
                        $videoconference = $this->updateVideoconference($videoconference);
                    }
                    else {
                        $videoconference = $this->create($procedure->getId());
                    }

                    if ($firstForToday === null) {
                        $firstForToday = $procedure;
                    }
                }
            }

            // Surface queue info so the citizen-side Jitsi can show "you are
            // #N in queue" without ever learning who the attendant is.
            //
            // Gated by an admin toggle (default OFF) so existing tenants —
            // whose Jitsi-meet fork doesn't read this field today — keep
            // seeing exactly the response shape they had before. Tenants
            // that opt in can flip `citizenQueueInfo` from the admin UI
            // (Settings → vTramit → General) or via:
            //   occ config:app:set vtramit citizenQueueInfo --value=1
            if ($firstForToday !== null && $this->configService->isCitizenQueueInfoEnabled()) {
                $response->setInfo('queue', $this->buildQueueInfoForCitizen($firstForToday));
            }
        }
        else {
            $response->setError($this->translate->t('No appointments found today and in progress for the notified video conference'));
        }

        return $response;
    }

    /**
     * How many appointments are "ahead" of this citizen's slot today,
     * according to the admin-configured scope (see ConfigService::
     * getCitizenQueueScope). Returns only the count; the attendant
     * identity (assigned_user_id) is never surfaced.
     *
     * "Ahead" always means: appointment_date strictly earlier than the
     * citizen's own and status in {PENDANT, ON_COURSE}. The scope picks
     * which column(s) further narrow the set:
     *   - assigned_user            → same assigned_user_id
     *   - assigned_group           → same assigned_group_id
     *   - procedure_type_and_group → same procedure_type_id AND
     *                                same assigned_group_id
     *   - topic                    → same topic string
     *
     * If the citizen's procedure has no value for the column(s) needed
     * by the chosen scope, the count is 0 (we don't broaden the scope
     * silently — admin keeps full control).
     *
     * @return array{ahead:int, scope:string}
     */
    private function buildQueueInfoForCitizen(Procedure $procedure): array {
        $scope = $this->configService->getCitizenQueueScope();
        $info = ['ahead' => 0, 'scope' => $scope];

        $selfDate = $procedure->getAppointmentDate();
        if (empty($selfDate)) {
            return $info;
        }

        $filter = [
            'minDate' => null, // filled below
            'maxDate' => null, // filled below
            'status' => [ProcedureStatus::PENDANT, ProcedureStatus::ON_COURSE],
        ];

        switch ($scope) {
            case 'assigned_group':
                $group = $procedure->getAssignedGroupId();
                if (empty($group)) {
                    return $info;
                }
                // 'assignedGroupId' (singular) — raw equality only. Don't
                // use 'groups' here; the latter also AND's the procedure_type
                // against oc_vtproc_type_group which excludes valid rows
                // whose type isn't registered for that group.
                $filter['assignedGroupId'] = $group;
                break;

            case 'procedure_type_and_group':
                $group = $procedure->getAssignedGroupId();
                $typeId = $procedure->getProcedureTypeId();
                if (empty($group) || empty($typeId)) {
                    return $info;
                }
                $filter['assignedGroupId'] = $group;
                $filter['procedureTypeId'] = $typeId;
                break;

            case 'topic':
                $topic = $procedure->getTopic();
                if (empty($topic)) {
                    return $info;
                }
                $filter['topic'] = $topic;
                break;

            case 'assigned_user':
            default:
                $userId = $procedure->getAssignedUserId();
                if (empty($userId)) {
                    return $info;
                }
                $filter['users'] = [$userId];
                break;
        }

        $tz = $this->configService->getDateTimezone();
        $today = new \DateTime('today', $tz);
        $selfDateTime = $selfDate instanceof \DateTimeInterface
            ? $selfDate
            : new \DateTime((string) $selfDate, $tz);
        $filter['minDate'] = $today;
        $filter['maxDate'] = $selfDateTime;

        // findByFilter() applies appointment_date <= maxDate inclusively,
        // so it can return procedures with the same slot as self (and
        // self itself). Strip those in PHP to get strict "before" semantics.
        $candidates = $this->procedureMapper->findByFilter($filter);

        $selfTs = $selfDateTime->getTimestamp();
        $count = 0;
        foreach ($candidates as $other) {
            if ($other->getId() === $procedure->getId()) {
                continue;
            }
            $otherDate = $other->getAppointmentDate();
            if (empty($otherDate)) {
                continue;
            }
            $otherTs = ($otherDate instanceof \DateTimeInterface
                ? $otherDate
                : new \DateTime((string) $otherDate, $tz))->getTimestamp();
            if ($otherTs < $selfTs) {
                $count++;
            }
        }
        $info['ahead'] = $count;

        return $info;
    }

    /**
     * Indicates whether exists a connection waiting to be answered by a moderator.
     *
     * @param Procedure $procedure  procedure for which check an existing waiting connection
     * @return bool       true if there is a connection waiting for the procedure; otherwise, false
     */
    public function isProcedureWaitingForModerator(Procedure $procedure): bool {
        if ($procedure->isAppointmentDateToday($this->configService->getDateTimezone())) {
            try {
                $videoconference = $this->videoconferenceMapper->findLastByProcedureId($procedure->getId());
                if (!empty($videoconference)) {
                    return $videoconference->isConnected();
                }
            }
            catch(DoesNotExistException $e) {}
        }

        return false;
    }

    /**
     * Get the list of procedures waiting to be attended.
     */
    public function findConnectedProcedures(): ResponseInfo {
        $list = $this->videoconferenceMapper->findConnectedProcedures($this->configService->getDateTimezone());

        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }
}