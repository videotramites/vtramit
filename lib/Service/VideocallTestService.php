<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Service;

use OCP\AppFramework\Db\DoesNotExistException;
use Psr\Log\LoggerInterface;

use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\VideocallTest;
use OCA\VTramit\Db\VideocallTestMapper;
use OCA\VTramit\Service\ConfigService;

class VideocallTestService {

    private LoggerInterface $logger;
    private VideocallTestMapper $mapper;
    private ConfigService $configService;

    public function __construct(
        LoggerInterface $logger,
        VideocallTestMapper $mapper,
        ConfigService $configService
    ) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->configService = $configService;
    }

    /**
     * Start a videocall test: call the robot API and store the pending test.
     */
    public function startTest(int $stakeholderId, int $procedureId, string $language): ResponseInfo {
        $response = new ResponseInfo();

        $robotUrl = $this->configService->getRobotTestUrl();
        $robotToken = $this->configService->getRobotTestToken();

        if (empty($robotUrl)) {
            $response->setError('Robot test URL not configured');
            return $response;
        }

        // Determine this VTramit's hostname for the origin field
        $origin = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'unknown';

        // Build callback URL
        $callbackUrl = 'https://' . $origin . '/apps/vtramit/api/2.0/videocall-test/callback';

        // Call robot API
        $ch = curl_init($robotUrl . '/api/v1/session');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'display_name' => 'Usuario',
                'language' => $language,
                'origin' => $origin,
                'callback_url' => $callbackUrl,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $robotToken,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || empty($body)) {
            $this->logger->error('Robot API call failed: HTTP ' . $httpCode . ' — ' . $curlError . ' — ' . $body);
            $response->setError('Robot test service unavailable');
            return $response;
        }

        $robotResponse = json_decode($body, true);
        if (empty($robotResponse['session_id']) || empty($robotResponse['join_url'])) {
            $response->setError('Invalid response from robot');
            return $response;
        }

        // Store pending test
        $now = time();
        $test = new VideocallTest();
        $test->setProcedureStakeholderId($stakeholderId);
        $test->setProcedureId($procedureId);
        $test->setSessionId($robotResponse['session_id']);
        $test->setState('pending');
        $test->setCreatedAt($now);
        $test->setJoinUrl($robotResponse['join_url']);
        $test = $this->mapper->insert($test);

        $this->logger->info('Videocall test started: session=' . $robotResponse['session_id'] . ' stakeholder=' . $stakeholderId);

        $response->setInfo('test', $test);
        return $response;
    }

    /**
     * Handle callback from robot with test results.
     */
    public function handleCallback(string $sessionId, string $state, array $data): ResponseInfo {
        $response = new ResponseInfo();

        try {
            $test = $this->mapper->findBySessionId($sessionId);
        } catch (DoesNotExistException $e) {
            $response->setError('Test not found for session: ' . $sessionId);
            return $response;
        }

        $test->setState($state);
        $test->setResultJson(json_encode($data));
        $test->setCompletedAt(time());
        $this->mapper->update($test);

        $this->logger->info('Videocall test callback: session=' . $sessionId . ' state=' . $state);

        $response->setInfo('test', $test);
        return $response;
    }

    /**
     * Get last test result for a stakeholder.
     */
    public function getLastResult(int $stakeholderId): ResponseInfo {
        $response = new ResponseInfo();
        $test = $this->mapper->findLastByStakeholderId($stakeholderId);
        $response->setInfo('test', $test);
        return $response;
    }

    /**
     * Validate the robot callback token.
     */
    public function validateCallbackToken(string $token): bool {
        $expected = $this->configService->getRobotTestToken();
        return !empty($expected) && $expected === $token;
    }
}
