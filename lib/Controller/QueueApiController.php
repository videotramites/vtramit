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
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\VTramit\Service\QueueService;
use \OCP\ILogger;

class QueueApiController extends Controller {
    /** @var QueueService */
    private $service;

    /** @var string */
    private $userId;

    private $logger;

    use Errors;

    public function __construct(
        $appName,
        IRequest $request,
        QueueService $service,
        $userId,
        ILogger $logger
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->userId = $userId;
        $this->logger = $logger;
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function index(): DataResponse {
        return new DataResponse($this->service->getNext(), HTTP::STATUS_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/0.1/queue",
     *     @OA\Response(response="200", description="Get next queue email")
     * )
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function show(int $id): DataResponse {
        return $this->handleNotFound(function () use ($id) {
            return new DataResponse($this->service->find($id), HTTP::STATUS_OK);
        });
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function create(int $appointmentId,
                            string $mailTo,
                            string $mailCc,
                            string $mailCco,
                            string $subject,
                            string $body
    ): DataResponse {
        return new DataResponse($this->service->create($appointmentId, $mailTo, $mailCc, $mailCco, $subject, $body, $this->userId), HTTP::STATUS_OK);
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function update(int $appointmentId,
                            string $mailTo,
                            string $mailCc,
                            string $mailCco,
                            string $subject,
                            string $body
    ): DataResponse {
        return $this->handleNotFound(function () use ($id, $appointmentId, $mailTo, $mailCc, $mailCco, $subject, $body) {
            return new DataResponse($this->service->update($id, $appointmentId, $mailTo, $mailCc, $mailCco, $subject, $body, $this->userId), HTTP::STATUS_OK);
        });
    }

    /**
     * @OA\Delete(
     *     path="/api/0.1/queue/{id}",
     *       @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Destroy element in queue")
     * )
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function destroy(int $id): DataResponse {
        return $this->handleNotFound(function () use ($id) {
            return new DataResponse($this->service->delete($id, $this->userId), HTTP::STATUS_OK);
        });
    }
}
