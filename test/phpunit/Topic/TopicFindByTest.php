<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\MailTemplate;

use OCA\VTramit\Controller\TopicController;
use OCP\IURLGenerator;
use OCP\Files\IRootFolder;

use OCA\VTramit\Exception\DataNotFoundException;

use OCA\VTramit\ProcedureStatus;

use OCA\VTramit\Db\MailQueueMapper;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\MessageQueueMapper;
use OCA\VTramit\Db\MessageTemplateMapper;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\RgpdActivityMapper;
use OCA\VTramit\Db\SignatureButtonMapper;
use OCA\VTramit\Db\SignatureTypeMapper;
use OCA\VTramit\Db\SignatureFileMapper;
use OCA\VTramit\Db\SignatureMapper;
use OCA\VTramit\Db\TopicMapper;
use OCA\VTramit\Db\WebhookMapper;

use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Controller\ProcedureController;
use OCA\VTramit\Controller\ProcedureStakeholderController;
use OCA\VTramit\Controller\ProcedureTypeController;
use OCA\VTramit\Controller\ProcedureTypeGroupController;

use OCA\VTramit\Service\MailQueueService;
use OCA\VTramit\Service\MailTemplateService;
use OCA\VTramit\Service\MessageQueueService;
use OCA\VTramit\Service\MessageTemplateService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\ProcedureMailService;
use OCA\VTramit\Service\ProcedureMessageService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\ProcedureTokenService;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Service\ProcedureTypeService;
use OCA\VTramit\Service\ProcedureVideoconferenceService;
use OCA\VTramit\Service\RgpdActivityService;
use OCA\VTramit\Service\SignatureButtonService;
use OCA\VTramit\Service\SignatureService;
use OCA\VTramit\Service\SignatureTypeService;
use OCA\VTramit\Service\TopicService;
use OCA\VTramit\Service\WebhookService;

use OCA\VTramit\Helper\CurlHelper;
use OCA\VTramit\Helper\DeckHelper;
use OCA\VTramit\Helper\JwtHelper;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Helper\UserGroupHelper;

use OCA\VTramit\Test\Phpunit\CommonTestOperations;
use OCA\VTramit\Test\Phpunit\TestData;


/**
 * (PU065)
 */
class TopicFindByTest extends CommonTestOperations
{

    private string $testFunction = "testFindBy";

    /** @var TopicMapper */
    private TopicMapper $topicMapper;

    /** @var TopicService */
    private TopicService $topicService;

		/** @var TopicController */
    private TopicController $topicController;

    /**
     * Constructor for the Procedure test
     */
    public function __construct() {
        // Set test's unique name for the .log
        $testName = 'topic_crud';
        $this->setEntityTypeName('mail template');
        parent::__construct($testName, $this->testFunction);


        // Inizializate mappers
				$this->topicMapper = new TopicMapper($this->dbConnection);
    }

    /**
     * 
     * Steps:
     *
     *  1.- Find by id topic
     *  2.- Find by name topic
     *  3.- Find by filter topic
     * 
     */
    public function testFindBy(): void {

        
        $topicTestEntity = TestData::$topicEntityData;
        $responseCreate = $this->create($this->controller, $topicTestEntity);

        // Find by id topic
				$responseFind = $this->topicService->findById($responseCreate["entity"]->id)->getData();
        $this->logResponseData("", $responseFind);
        $this->assertEquals('OK', $responseFind['result']);

        // Find by id topic
				$responseFind = $this->topicService->findByName($topicTestEntity["name"])->getData();
        $this->logResponseData("", $responseFind);
        $this->assertEquals('OK', $responseFind['result']);

				$filter = [
            'name' => $topicTestEntity["name"],
            'minRequiredDocs' => $topicTestEntity["minRequiredDocs"],
            'videoPreservationTime' => $topicTestEntity["videoPreservationTime"],
            'helpUrl' => $topicTestEntity["helpUrl"],
            'helpTarget' => $topicTestEntity["helpTarget"],
        ];

				$responseFind = $this->topicService->findByFilter($filter, $this->userId)->getData();

        $this->logResponseData("", $responseFind);

        $this->assertEquals('OK', $responseFind['result']);

    }

    /**
     * Set up the controller to test
     */
    protected function setUpController(): void {
			$this->topicService = new TopicService(
					$this->logger,
					$this->translate,
					$this->activityManager,
					$this->configService,
					$this->topicMapper
			);

			$this->controller = new TopicController(
					'vtramit',
					$this->request,
					$this->logger,
					$this->configService,
					$this->topicService,
					$this->userId
			);
    }

    /**
     * Checks response data against expected values
     *
     * @param array $expectedData Expected data
     * @param array $actualData Actual data from response
     */
    protected function checkEntityData(array $expectedData, array $actualData): void {
        // Check all received information
        $this->assertEquals(
            $expectedData['name'],
            $actualData['name'],
            'Name mismatch'
        );

        $this->assertEquals(
            $expectedData['subject'],
            $actualData['subject'],
            'Subject mismatch'
        );

        $this->assertEquals(
            json_encode($expectedData['template']),
            json_encode($actualData['template']),
            'Template mismatch'
        );
    }

    /**
     * Creates test required data
     */
    protected function createTestRequiredData(): void {}

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     */
    protected function getControllerInstance($controllerClass = ''): EntityController {

        switch ($controllerClass) {
            default:
                return $this->controller;
        }
    }
}