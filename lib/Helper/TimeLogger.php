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

namespace OCA\VTramit\Helper;

use DateTime;

use Psr\Log\LoggerInterface;

use OCA\VTramit\Service\ConfigService;

class TimeLogger {

    /** @var LoggerInterface */
    protected $logger;

    /** @var ConfigService */
    private $configService;

    private $startTime = [];
    private $endTime = [];

    public function __construct(
        LoggerInterface $logger,
        ConfigService $configService
    ) {
        $this->logger = $logger;
        $this->configService = $configService;
    }

    public function startTime($section) {
        if (!$this->configService->getLogTimer()) {
            return;
        }

        $this->startTime[$section] = microtime(true);
    }

    public function logTime($section, $operation) {
        if (!$this->configService->getLogTimer()) {
            return;
        }

        $endTime = microtime(true);
        $time = ($endTime - $this->startTime[$section]);
        $classname = $this->getCallingClass(); //get_class($this);
        $this->logger->info("Entity '$classname' - $operation - Operation took $time ms", ['app' => 'vtramit']);
    }

    private function getCallingClass() {
        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        $class = $trace[1]['class'];

        // +1 to i cos we have to account for calling this function
        for ( $i=1; $i<count( $trace ); $i++ ) {
            if ( isset( $trace[$i] ) ) // is it set?
                 if ( $class != $trace[$i]['class'] ) // is it a different class
                     return $trace[$i]['class'];
        }
    }
}