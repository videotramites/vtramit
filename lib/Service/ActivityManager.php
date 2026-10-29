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
use OCP\IUser;
use OCP\IUserSession;
use OCP\Activity\IManager;

use OCA\VTramit\Db\EntityMapper;
use OCA\VTramit\Enum\ActivityType;

class ActivityManager {

    /** @var LoggerInterface */
    protected $logger;

    /** @var \OCP\Activity\IManager */
    protected $activityManager;

    /** @var ConfigService */
    protected $configService;

    /** @var string */
    protected $userId;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        LoggerInterface $logger,
        IManager $activityManager,
        ConfigService $configService,
        IUserSession $userSession
    ) {
        $this->logger = $logger;
        $this->activityManager = $activityManager;
        $this->configService = $configService;
        $this->userId = !empty($userSession) && !empty($userSession->getUser()) ? $userSession->getUser()->getUID() : 'ananymous';
    }

    /**
     * Adds the activity.
     *
     * @param string $objectType
     * @param string $type
     * @param string $affectedUser
     * @param string $action
     * @param array $subjectParams
     * @param int $entityId
     * @param string $path
     * @param string $type
     */
    public function reportActivity(
        string $objectType,
        string $type,
        ?string $affectedUser,
        string $subject,
        array $subjectParams = [],
        int $entityId = 0,
        string $path = '',
        string $link = ''
    ) {
        $app = 'vtramit';

        $event = $this->activityManager->generateEvent();
        try {
            $event->setApp($app)
                ->setType($type)
                ->setAuthor($this->userId)
                ->setAffectedUser(empty($affectedUser) ? $this->userId : $affectedUser)  // the affected user cannot be empty so we use the user doing the operation as the affected users are anonymous for the system
                ->setTimestamp(time())
                ->setSubject($subject, $subjectParams)
                ->setObject($objectType, $entityId, $path)
                ->setLink($link)
            ;
        }
        catch (\InvalidArgumentException $e) {
            // @codeCoverageIgnoreStart
            $this->logger->error($e, ['app' => 'vtramit']);
            // @codeCoverageIgnoreEnd
        }

        // Add activity to stream
        $this->activityManager->publish($event);
    }

    /**
     * Adds the activity.
     *
     * @param EntityMapper $entityMapper
     * @param string $affectedUser
     * @param string $action
     * @param array $subjectParams
     * @param int $entityId
     * @param string $path
     * @param string $type
     */
    public function reportEntityActivity(
        EntityMapper $entityMapper,
        ?string $affectedUser,
        ActivityType $action,
        array $subjectParams = [],
        int $entityId = 0,
        string $path = '',
        string $link = ''
    ) {
        $entityType = $entityMapper->getTableName();

        $entityName = $entityMapper->getEntityName();
        $entityName = strtolower(trim($entityName));
        $entityName = preg_replace('/\s+/', '_', $entityName);

        $type = $entityName;
        $subject = $entityName . '_' . $action->value;

        $this->reportActivity($entityType, $type, $affectedUser, $subject, $subjectParams, $entityId, $path, $link);
    }
}