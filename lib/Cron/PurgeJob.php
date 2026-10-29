<?php
/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia Benítez <letizia@videoatencion.com>
 * @author Daniel Tamajon <litesolutions.es>
 * @author Marta González <litesolutions.es>
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

use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Db\DoesNotExistException;
use Psr\Log\LoggerInterface;
use OCP\AppFramework\Utility\ITimeFactory; // Import ITimeFactory
use OCA\VTramit\Service\ProcedureJobService;

class PurgeJob extends TimedJob {

    public function __construct(
        ITimeFactory $time,
        private ProcedureJobService $procedureJobService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($time);
        // Run once per hour.
        $this->setInterval(60 * 60);
    }

    /**
     * @param array|null $argument
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function run($argument = null) {

        try {
            // Run the service method to purge data
            $this->procedureJobService->purgeData();
        } catch (DoesNotExistException $e) {
            // Log debug information if data does not exist
            $this->logger->debug('Could not purge procedure data', ['app' => 'vtramit']);
        } catch (\Exception $e) {
            // Handle general exceptions
            $this->logger->error('An error occurred during purge data operation', ['exception' => $e, 'app' => 'vtramit']);
        } catch (\Throwable $e) {
            // Handle any other errors or PHP fatal errors
            $this->logger->error('A critical error occurred during purge data operation', ['exception' => $e, 'app' => 'vtramit']);
        }
    }
}
