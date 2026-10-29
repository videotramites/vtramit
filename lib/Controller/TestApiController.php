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

namespace OCA\VTramit\Controller;

use OCP\IRequest;
use Psr\Log\LoggerInterface;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Exception\ControllerErrors;

use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureJobService;

use OCA\VTramit\Test\ProcedureEntityTest;
use OCA\VTramit\Test\ProcedureTest;
use OCA\VTramit\Test\ProcedureTypeTest;

class TestApiController extends ApiController {
    /** @var string */
    private $userId;

    private $logger;

    /** @var ConfigService */
    private $configService;

    /** @var ProcedureEntityTest */
    private $procedureEntityTest;

    /** @var ProcedureTest */
    private $procedureTypeTest;

    /** @var ProcedureTypeTest */
    private $procedureTest;

    /** @var ProcedureJobService */
    private $procedureJobService;


    use ControllerErrors;

    public function __construct(
        $appName,
        IRequest $request,
        $userId,
        LoggerInterface $logger,
        ConfigService $configService,
        ProcedureEntityTest $procedureEntityTest,
        ProcedureTest $procedureTest,
        ProcedureTypeTest $procedureTypeTest,
        ProcedureJobService $procedureJobService
    ) {
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->logger = $logger;
        $this->configService = $configService;
        $this->procedureEntityTest = $procedureEntityTest;
        $this->procedureTest = $procedureTest;
        $this->procedureTypeTest = $procedureTypeTest;
        $this->procedureJobService = $procedureJobService;
    }

    /**
     * Create data for testing in all related procedure type tables.
     *
     * @OA\Post(
     *     path="/api/2.0/test/procedureType/create",
     *     @OA\Response(
     *         response="200",
     *         description="Create data for testing in all related procedure type tables"
     *     )
     * )
     *
     * Command line to test this function:
     * curl -i -H "Accept: application/json" -H "Content-Type: application/json" -X POST https://<my.domain>/index.php/apps/vtramit/api/2.0/test/all'
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function createConfiguration(): DataResponse {
        if ($this->configService->getMode() != 'test') {
            return new DataResponse("Cannot execute test while in production mode", HTTP::STATUS_METHOD_NOT_ALLOWED);
        }

        return $this->handleExceptions(function () {
            $this->procedureTest->createConfiguration();
            return new DataResponse("Test data created!", HTTP::STATUS_OK);
        }, true);
    }

    /**
     * Delete current data in all related procedure type tables.
     *
     * @OA\Post(
     *     path="/api/2.0/test/procedure/configuration/clean",
     *     @OA\Response(
     *         response="200",
     *         description="Delete all data in procedure type tables"
     *     )
     * )
     *
     * Command line to test this function:
     * curl -i -H "Accept: application/json" -H "Content-Type: application/json" -X POST https://<my.domain>/index.php/apps/vtramit/api/2.0/test/all'
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function cleanConfiguration(): DataResponse {
        if ($this->configService->getMode() != 'test') {
            return new DataResponse("Cannot execute test while in production mode", HTTP::STATUS_METHOD_NOT_ALLOWED);
        }

        return $this->handleExceptions(function () {
            $this->procedureTest->cleanConfiguration();
            return new DataResponse("Test data removed", HTTP::STATUS_OK);
        }, true);
    }

    /**
     * Delete current data in all related procedure type tables.
     *
     * @OA\Post(
     *     path="/api/2.0/test/procedure/clean",
     *     @OA\Response(
     *         response="200",
     *         description="Delete all data in procedure type tables"
     *     )
     * )
     *
     * Command line to test this function:
     * curl -i -H "Accept: application/json" -H "Content-Type: application/json" -X POST https://<my.domain>/index.php/apps/vtramit/api/2.0/test/all'
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function cleanProcedures(): DataResponse {
        if ($this->configService->getMode() != 'test') {
            return new DataResponse("Cannot execute test while in production mode", HTTP::STATUS_METHOD_NOT_ALLOWED);
        }

        return $this->handleExceptions(function () {
            $this->procedureTest->cleanProcedures();
            return new DataResponse("Test data removed", HTTP::STATUS_OK);
        }, true);
    }

    /**
     * Execute complete test set for procedure entity.
     *
     * @OA\Post(
     *     path="/api/2.0/test/procedureEntity/all",
     *     @OA\Response(
     *         response="200",
     *         description="Execute complete test set for procedure entity"
     *     )
     * )
     *
     * Command line to test this function:
     * curl -i -H "Accept: application/json" -H "Content-Type: application/json" -X POST https://<my.domain>/index.php/apps/vtramit/api/2.0/test/all'
     *
     * @return DataResponse
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function procedureEntityTest(): DataResponse {
        if ($this->configService->getMode() != 'test') {
            return new DataResponse("Cannot execute test while in production mode", HTTP::STATUS_METHOD_NOT_ALLOWED);
        }

        return $this->handleExceptions(function () {
            $this->procedureEntityTest->doTest();
            return new DataResponse("Test finished", HTTP::STATUS_OK);
        }, true);
    }
}
