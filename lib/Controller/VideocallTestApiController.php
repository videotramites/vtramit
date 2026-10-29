<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Controller;

use OCP\IRequest;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\VTramit\Exception\ControllerErrors;
use OCA\VTramit\Service\VideocallTestService;

class VideocallTestApiController extends ApiController {
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
     * Callback from robot with test results.
     * Authenticated by Bearer token matching robotTestToken.
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function callback(): DataResponse {
        return $this->handleExceptions(function () {
            // Validate Bearer token
            $authHeader = $this->request->getHeader('Authorization');
            $token = '';
            if (str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
            }
            if (!$this->service->validateCallbackToken($token)) {
                return new DataResponse(['error' => 'Unauthorized'], Http::STATUS_UNAUTHORIZED);
            }

            $sessionId = $this->request->getParam('session_id');
            $state = $this->request->getParam('state', 'completed');
            $data = $this->request->getParam('data', []);

            $response = $this->service->handleCallback($sessionId, $state, $data);
            return new DataResponse($response->getData(), Http::STATUS_OK);
        });
    }

    /**
     * Start test (API mirror of web endpoint).
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
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
     * Get last test result.
     */
    #[\OCP\AppFramework\Http\Attribute\CORS]
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
