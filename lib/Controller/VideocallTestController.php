<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\VTramit\Exception\ControllerErrors;
use OCA\VTramit\Service\VideocallTestService;

class VideocallTestController extends Controller {
    private VideocallTestService $service;

    use ControllerErrors;

    public function __construct(
        $appName,
        IRequest $request,
        VideocallTestService $service
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
    }

    /**
     * Start a videocall test (called from public portal).
     */
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function startTest(): DataResponse {
        return $this->handleExceptions(function () {
            $stakeholderId = (int) $this->request->getParam('procedureStakeholderId');
            $procedureId = (int) $this->request->getParam('procedureId');
            $language = $this->request->getParam('language', 'es');

            $response = $this->service->startTest($stakeholderId, $procedureId, $language);
            return new DataResponse($response->getData(), Http::STATUS_OK);
        });
    }

    /**
     * Get last test result for a stakeholder.
     */
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function getResult(int $stakeholderId): DataResponse {
        return $this->handleExceptions(function () use ($stakeholderId) {
            $response = $this->service->getLastResult($stakeholderId);
            return new DataResponse($response->getData(), Http::STATUS_OK);
        });
    }
}
