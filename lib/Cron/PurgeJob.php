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

namespace OCA\VTramit\Cron;

use \OC\BackgroundJob\TimedJob;
use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\ILogger;

use OCA\VTramit\Db\Appointment;
use OCA\VTramit\Service\AppointmentService;

class PurgeJob extends TimedJob {

    /** @var AppointmentService */
    private $appointmentService;

    /** @var ILogger */
    private $logger;

    public function __construct(AppointmentService $appointmentService, ILogger $logger) {
        // Run once an hour
        $this->setInterval(60 * 60);

        if (is_null($appointmentService) || is_null($logger)) {
            $this->fixDIForJobs();
        } else {
            $this->appointmentService = $appointmentService;
            $this->logger = $logger;
        }
    }

    protected function fixDIForJobs() {
        /** @var Application $application */
        $application = \OC::$server->query(Application::class);
        $this->userManager = \OC::$server->getUserManager();
        $this->appointmentService = $application->getContainer()->query('AppointmentService');
        $this->logger = $application->getContainer()->query('ILogger');
    }

    /**
     * @param $argument
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function run($argument) {
        try {
            $this->appointmentService->purgeData();

        } catch (DoesNotExistException $e) {
            // Skip if any error occurs
            $this->logger->debug('Could not purge data');
        }
    }
}
