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
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\AppFramework\Db\DoesNotExistException;
use OCA\VTramit\Service\ProcedureJobService;
use Psr\Log\LoggerInterface;

class PrepareWorkdayJob extends TimedJob {

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
     */
    public function run($argument = null) {
        try {
            $this->procedureJobService->prepareWorkForToday();
        } catch (DoesNotExistException $e) {
            $this->logger->error('Could not prepare the procedures work for today: Does not exist.', ['app' => 'vtramit']);
        } catch (\Exception $e) {
            $this->logger->error('Could not prepare the procedures work for today: Exception.', ['app' => 'vtramit']);
        } catch (\Throwable $e) {
            $this->logger->error('Could not prepare the procedures work for today: Throwable.', ['app' => 'vtramit']);
        }
    }
}
