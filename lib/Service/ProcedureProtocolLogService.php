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

use Psr\Log\LoggerInterface;
use OCP\IL10N;

use OCA\VTramit\Db\ProcedureProtocolLog;
use OCA\VTramit\Db\ProcedureProtocolLogMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureTypeProtocolStepMapper;
use OCA\VTramit\Db\ResponseInfo;

class ProcedureProtocolLogService extends EntityService {

    /** @var ProcedureProtocolLogMapper */
    private $logMapper;

    /** @var ProcedureMapper */
    private $procedureMapper;

    /** @var ProcedureTypeGroupService */
    private $procedureTypeGroupService;

    /** @var ProcedureTypeProtocolStepMapper */
    private $stepMapper;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        ProcedureProtocolLogMapper $logMapper,
        ProcedureMapper $procedureMapper,
        ProcedureTypeGroupService $procedureTypeGroupService,
        ProcedureTypeProtocolStepMapper $stepMapper,
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $logMapper);
        $this->logMapper = $logMapper;
        $this->procedureMapper = $procedureMapper;
        $this->procedureTypeGroupService = $procedureTypeGroupService;
        $this->stepMapper = $stepMapper;
    }

    /**
     * Creates and obtains a new entity.
     */
    protected function newEntity() {
        return new ProcedureProtocolLog();
    }

    /**
     * Return the protocol steps for the group assigned to the given procedure,
     * each step enriched with its completion status from the log.
     *
     * @param int $procedureId  identifier of the procedure
     * @return ResponseInfo  {result, list[]}
     */
    public function getProtocolStepsForProcedure(int $procedureId): ResponseInfo {
        $response = new ResponseInfo();

        $procedure = $this->procedureMapper->find($procedureId);

        if (empty($procedure->getAssignedGroupId()) || empty($procedure->getProcedureTypeId())) {
            $response->setInfo('list', []);
            return $response;
        }

        $groupResponse = $this->procedureTypeGroupService->findByTypeIdAndGroup(
            (int) $procedure->getProcedureTypeId(),
            (string) $procedure->getAssignedGroupId()
        );
        $group = $groupResponse->getInfo('entity');

        if (!$group) {
            $response->setInfo('list', []);
            return $response;
        }

        $steps = $this->stepMapper->findByProcedureTypeGroupId($group->getId());

        // Keep latest log entry per step and track the most recent global entry.
        $logEntries = $this->logMapper->findByProcedureId($procedureId);
        $latestLogByStepId = [];
        $lastLogEntry = null;
        foreach ($logEntries as $entry) {
            $latestLogByStepId[$entry->getProcTypeProtocolStepId()] = $entry;
            $lastLogEntry = $entry;
        }

        $stepOrderById = [];
        foreach ($steps as $step) {
            $stepOrderById[$step->getId()] = (int) $step->getOrder();
        }

        $lastCompletedOrder = null;
        if ($lastLogEntry) {
            $lastStepId = $lastLogEntry->getProcTypeProtocolStepId();
            if (array_key_exists($lastStepId, $stepOrderById)) {
                $lastCompletedOrder = $stepOrderById[$lastStepId];
            }
        }

        $list = [];
        foreach ($steps as $step) {
            $order = (int) $step->getOrder();
            $isCompleted = $lastCompletedOrder !== null && $order <= $lastCompletedOrder;
            $logEntry = $latestLogByStepId[$step->getId()] ?? null;
            $list[] = array_merge($step->jsonSerialize(), [
                'completed'   => $isCompleted,
                'completedBy' => ($isCompleted && $logEntry) ? $logEntry->getUserId() : null,
                'completedAt' => ($isCompleted && $logEntry) ? $logEntry->getCreatedAt() : null,
            ]);
        }

        $response->setInfo('list', $list);
        return $response;
    }

    /**
     * Mark a protocol step as completed by the given user.
     * This appends a log entry even if the step had been completed before.
     *
     * @param int    $procedureId  identifier of the procedure
     * @param int    $stepId       identifier of the protocol step
     * @param string $userId       identifier of the user completing the step
     * @return ResponseInfo  {result, list[]}  updated list of steps with completion status
     */
    public function completeStep(int $procedureId, int $stepId, string $userId): ResponseInfo {
        $step = $this->stepMapper->find($stepId);

        $log = new ProcedureProtocolLog();
        $log->setProcedureId($procedureId);
        $log->setProcTypeProtocolStepId($stepId);
        $log->setProcedureTypeGroupId($step->getProcedureTypeGroupId());
        $log->setUserId($userId);
        $log->setCreatedAt(date('Y-m-d H:i:s'));

        $this->logMapper->insert($log);

        return $this->getProtocolStepsForProcedure($procedureId);
    }
}
