<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit;

use OCA\VTramit\Controller\EntityController;
use OCA\VTramit\Test\Phpunit\Helpers\TestHelpers;
use OCA\VTramit\Test\Phpunit\TestData;

/**
 * Abstract class providing common test functionality
 * This reduces duplication across entity test classes
 */
abstract class CommonTestOperations extends TestEntityServiceBase
{
    /** @var int The ID of the test entity */
    protected int $entityId = 0;

    /** @var EntityController The controller for the entity being tested */
    protected EntityController $controller;

    /** @var string The entity type name (for logging) */
    protected string $entityTypeName = 'entity';

    /** @var array The entity */
    protected array $entity = [];

    /**
     * Set up the controller to test
     * Child classes must override this method to set $this->controller
     */
    abstract protected function setUpController(): void;

    /**
     * Define entity type name for logging
     *
     * @param string $name The entity type name
     */
    protected function setEntityTypeName(string $name): void {
        $this->entityTypeName = $name;
    }

    /**
     * Set up the test environment
     */
    protected function setUp(): void {
        $this->setUpController();
    }

    /**
     * Generic CRUD test that works with any entity
     *
     * @param array $initialData Initial entity data
     * @param array $updateData Updated entity data
     */
    protected function runCRUDTest(array $initialData, array $updateData): void {
        $this->logMessage("Starting CRUD test for {$this->entityTypeName}");

        // Create test entity
        $this->entityId = $this->createTestEntity($initialData);
        $this->assertNotEquals(0, $this->entityId, "Failed to get valid entity ID after create");

        // Test finding by ID
        $this->findByIdTestEntity($initialData);

        // Test finding all entities
        $this->findAllTestEntities($initialData);

        // Prepare update data with correct ID
        $updateData['id'] = $this->entityId;

        // Test updating entity
        $this->updateTestEntity($updateData);

        // Test deleting entity
        $this->deleteTestEntity();

        $this->logMessage("Completed CRUD test for {$this->entityTypeName}");
    }

    /**
     * Creates a test entity
     *
     * @param array $testEntity Test entity data
     * @return int Created entity id
     */
    protected function createTestEntity(array $testEntity): int {
        // Log action
        $this->logMessage("Creating {$this->entityTypeName} with data: " . json_encode($testEntity));

        // Get response
        $responseData = $this->create($this->controller, $testEntity);

        // Save endpoint output into log file
        $this->logResponseData("CREATE TEST {$this->entityTypeName}", $responseData);

        // Use the helper assertion
        $this->assertResponseOk($responseData, "Failed to create {$this->entityTypeName}");
        $this->assertResponseHasEntity($responseData, "Response missing entity data after creation");

        // Save entity as an array to access all fields
        $responseEntity = $this->getEntity($responseData);

        // Verify the created entity matches the requested data
        $this->checkEntityData($testEntity, $responseEntity);

        // Return created entity ID
        return $responseEntity['id'];
    }

    /**
     * Finds the created entity by id and checks if its data is correct
     *
     * @param array $testEntity Test entity data
     */
    protected function findByIdTestEntity(array $testEntity): void {
        // Log action
        $this->logMessage("Finding {$this->entityTypeName} with ID: {$this->entityId}");

        // Get response
        $responseData = $this->find($this->controller, $this->entityId);

        // Save endpoint output into log file
        $this->logResponseData("FIND {$this->entityTypeName} BY ID", $responseData);

        // Use the helper assertion
        $this->assertResponseOk($responseData, "Failed to find {$this->entityTypeName} by ID");
        $this->assertResponseHasEntity($responseData, "Response missing entity data when finding by ID");

        // Save entity to access as array
        $responseEntity = $this->getEntity($responseData);

        // Check data
        $this->checkEntityData($testEntity, $responseEntity);
    }

    /**
     * Finds all entities and checks if the created entity is included
     *
     * @param array $testEntity Test entity data
     */
    protected function findAllTestEntities(array $testEntity): void {
        // Log action
        $this->logMessage("Finding all {$this->entityTypeName} entries");

        // Get response
        $responseData = $this->findAll($this->controller);

        // Save endpoint output into log file
        $this->logResponseData("FIND ALL {$this->entityTypeName}", $responseData);

        // Use the helper assertion
        $this->assertResponseOk($responseData, "Failed to find all {$this->entityTypeName} entries");
        $this->assertResponseHasList($responseData, 1, "No {$this->entityTypeName} entries found");

        // Find the created entity in the list
        $found = false;
        $entityList = $this->getEntityList($responseData);

        foreach ($entityList as $entity) {
            if (isset($entity['id']) && $entity['id'] == $this->entityId) {
                $this->checkEntityData($testEntity, $entity);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Created {$this->entityTypeName} not found in list");
    }

    /**
     * Finds all the signature buttons and check if the last one is the created signature button.
     *
     * @param array $testEntity Test entity data
     * @param int   $procedureTypeId
     *
     */
    protected function findByProcedureTypeId(array $testEntity, int $procedureTypeId): void {

        // Get response
        $responseData = $this->controller->findByProcedureTypeId($procedureTypeId)->getData();
        // Save endpoint output into log file
        $this->logResponseData("FIND {$this->entityTypeName} BY PROCEDURE TYPE ID", $responseData);
        // Check if result is OK
        $this->assertEquals('OK', $responseData['result']);
        // Use the helper assertion
        $this->assertResponseOk($responseData, "Failed to find all {$this->entityTypeName} entries");
        $this->assertResponseHasList($responseData, 1, "No {$this->entityTypeName} entries found");

        // Find the created entity in the list
        $found = false;
        $entityList = $this->getEntityList($responseData);

        foreach ($entityList as $entity) {
            if (isset($entity['id']) && $entity['id'] == $testEntity['id']) {
                $this->checkEntityData($testEntity, $entity);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Created {$this->entityTypeName} not found in list");

    }

    /**
     * Finds rgpd option by jitsiRoomCode
     *
     * @param array $testEntity Ttst entity data
     * @param int   $jitsiCode jitsi room code
     *
     */
    public function findByJitsiRoomCode(array $testEntity, string $jitsiCode): void {

        // Test find by jistiRoomCode
        $responseData = $this->controller->findByJitsiRoomCode($jitsiCode)->getData();
        // Save endpoint output into log file
        $this->logResponseData('FIND BY JITSI ROOM CODE', $responseData);
        // Check if result is OK
        $this->assertEquals('OK', $responseData['result']);
        // Find the created entity in the list
        $found = false;
        $entityList = $this->getEntityList($responseData);

        // Find the created entity in the list
        foreach ($entityList as $entity) {
            if (isset($entity['id']) && $entity['id'] == $testEntity['id']) {
                $this->checkEntityData($testEntity, $entity);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Created {$this->entityTypeName} not found in list");
    }

    /**
     * Finds the last created rgpd activity and checks if its data is correct.
     *
     * @param array $testEntity              test entity data
     * @param int   $procedureId             procedure id
     * @param int   $procedureStakeholderId  procedure stakeholder id
     *
     */
    public function findLast(array $testEntity, int $procedureId, int $procedureStakeholderId): void {
        // Find last rgpd activity by ProcedureStakeholder
        $responseData = $this->controller->findLastByProcedureStakeholder($procedureId, $procedureStakeholderId)->getData();;
        // Save endpoint output into log file
        $this->logResponseData('FIND LAST TEST RGPD ACTIVITY', $responseData);
        $this->assertEquals('OK', $responseData['result']);
        $responseEntity = $this->getEntity($responseData);
        // Validate found entity info
        $this->checkEntityData($testEntity, $responseEntity);
    }

    /**
     * Finds by filter a procedure type and checks the recived informartion.
     *
     * @param  array $testEntity  test procedure type entity data (to compare it with the response entity)
     *
     */
    public function findByFilter(array $testEntity, string $filter, ?int $requestedPage = 1, ?bool $isDownloadRequest = false, ?bool $expectError = false) : void {

        // Get response

        if ($this->entityTypeName === "procedure") {

            $responseData = $this->controller->findByFilter(json_decode($filter, true), $requestedPage, $isDownloadRequest);

            if (!$isDownloadRequest) {
                $responseData = $responseData->getData();
                $this->logResponseData('FIND BY FILTER PROCEDURE', $responseData);

            } else {

                $expectedContent = 'externalId,assignedGroupId,assignedUserId,status,topic,appointmentDate,stakeholderName,stakeholderCitizenId,stakeholderPhone AQIMT,TEST,,Initializing,"Testing API 1674581866","2025-07-19 10:31:59",,,';
                $expectedMime = 'text/csv';

                $reflection = new \ReflectionClass($responseData);

                $dataProperty = $reflection->getProperty('data');
                $dataProperty->setAccessible(true);


                // Parsear contenido CSV a array
                $lines = explode("\n", trim($dataProperty->getValue($responseData)));
                $header = str_getcsv(array_shift($lines)); // Primera línea
                // Limpiar posibles BOM
                $header = array_map(function ($key) {
                    return preg_replace('/^\xEF\xBB\xBF/', '', $key);
                }, $header);
                $dataRow = str_getcsv(array_shift($lines)); // Segunda línea


                $csvAssoc = array_combine($header, $dataRow);

                $this->assertEquals($testEntity["externalId"], $csvAssoc["externalId"]);
                $this->assertEquals($testEntity["assignedGroupId"], $csvAssoc["assignedGroupId"]);
                $this->assertEquals("", $csvAssoc["assignedUserId"]);
                $this->assertContains($csvAssoc["status"], ["Pendant", "Initializing"]);
                $this->assertEquals($testEntity["topic"], $csvAssoc["topic"]);
                $this->assertEquals(TestData::$procedureStakeholderTestEntityData["name"], $csvAssoc["stakeholderName"]);

                $formattedPhoneNumber = TestHelpers::normalizePhoneNumber(TestData::$procedureStakeholderTestEntityData["phone"]);
                $this->assertEquals($formattedPhoneNumber, $csvAssoc["stakeholderPhone"]);

                $this->assertTrue(TestHelpers::stakeholderCitizenIdIsValid($csvAssoc["stakeholderCitizenId"]));

                $headers = $responseData->getHeaders();

                $contentType = $headers['Content-Type'];
                $this->assertEquals('text/csv', $contentType);

                $contentDisposition = $headers['Content-Disposition'];

                // Extraer el filename con una expresión regular
                preg_match('/filename="([^"]+)"/', $contentDisposition, $matches);
                $filename = $matches[1] ?? null;

                $this->assertNotNull($filename, 'Filename no encontrado en Content-Disposition');
                $this->assertStringStartsWith('export_', $filename);
                $this->assertStringEndsWith('.csv', $filename);


                return;
            }

        } else {
            $responseData = $this->controller->findByFilter(json_decode($filter, true))->getData();
            $this->logResponseData('FIND BY FILTER PROCEDURE TYPE', $responseData);

        }

        // Check if result is OK
        $this->assertEquals('OK', $responseData['result']);
       $entityList = $this->getEntityList($responseData);
        if (!$expectError) {
            // Find the created entity in the list
            $found = false;


            // Find the created entity in the list
            foreach ($entityList as $entity) {
                if (isset($entity['id']) && $entity['id'] == $testEntity['id']) {
                    $this->checkEntityData($testEntity, $entity);
                    $found = true;
                    break;
                }
            }

            $this->assertTrue($found, "Created {$this->entityTypeName} not found in list");

        } else {


        }

    }



    /**
     * Updates the created entity
     *
     * @param array $testEntity Updated test entity data
     */
    protected function updateTestEntity(array $testEntity): void {
        // Log action
        $this->logMessage("Updating {$this->entityTypeName} with ID: {$this->entityId}");

        // Get response
        $responseData = $this->update($this->controller, $testEntity);

        // Save endpoint output into log file
        $this->logResponseData("UPDATE {$this->entityTypeName}", $responseData);

        // Use the helper assertion
        if ($this->entityTypeName == 'rgpd activity') {
            $this->assertEquals('ERROR', $responseData['result']);
            $this->assertEquals('RGPD activity is not allowed to be updated', $responseData['message']);
        }
        else {
            $this->assertResponseOk($responseData, "Failed to update {$this->entityTypeName}");
            $this->assertResponseHasEntity($responseData, "Response missing entity data after update");

            // Save entity to access as array
            $responseEntity = $this->getEntity($responseData);

            // Check all UPDATED information
            $this->checkEntityData($testEntity, $responseEntity);
        }
    }

    /**
     * Deletes test entity
     */
    protected function deleteTestEntity(): void {
        // Log action
        $this->logMessage("Deleting {$this->entityTypeName} with ID: {$this->entityId}");

        // Delete entity
        $responseData = $this->delete($this->controller, $this->entityId);

        // Save endpoint output into log file
        $this->logResponseData("DELETE {$this->entityTypeName}", $responseData);

        // Use the helper assertion
        $this->assertResponseOk($responseData, "Failed to delete {$this->entityTypeName}");

        // Set id to 0 because it's deleted
        $this->entityId = 0;
    }

    /**
     * Finds by external ID.
     *
     * @param array $testEntity Test entity data
     */
    public function findByExternalId(array $testEntity): void {

        // Get response
        $responseData = $this->processControllerResponse($this->controller->findByExternalId($testEntity['externalId']));
        // Save endpoint output into log file
        $this->logResponseData( "FIND {$this->entityTypeName} BY EXTERNAL ID", $responseData);

        // Check all recived information
        $this->assertEquals('OK', $responseData['result']);

        // Find the created entity in the list
        $found = false;
        $entityList = $this->getEntityList($responseData);

        // Find the created entity in the list
        foreach ($entityList as $entity) {
            if (isset($entity['id']) && $entity['id'] == $testEntity['id']) {
                $this->checkEntityData($testEntity, $entity);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Created {$this->entityTypeName} not found in list");
    }

    /**
     * Gets procedure type context.
     *
     */
    public function getContextProcedureType(int $procedureTypeId): void {

        // Get response
        $responseData = $this->processControllerResponse($this->controller->getContext($procedureTypeId));
        // Save endpoint output into log file
        $this->logResponseData('GET CONTEXT PROCEDURE TYPE', $responseData);

        // Check all recived information
        $this->assertEquals('OK', $responseData['result']);

        $this->checkEntityData($responseData);

    }

    /**
     * Calls the next function and checks if the resultant value
     *
     */
    public function nextQueue(): void {

        // Get response
        $responseDataGetNext = $this->processControllerResponse($this->controller->next());
        // Save endpoint output into log file
        $this->logResponseData('NEXT MAIL QUEUE', $responseDataGetNext);
        // Check if result is OK
        $this->assertEquals('OK', $responseDataGetNext['result']);

        // Get response
        $responseDataList = $this->processControllerResponse($this->findAll($this->controller));

        // Use the helper assertion
        $this->assertResponseOk($responseDataList, "Failed to find all {$this->entityTypeName} entries");

        // Find the created entity in the list
        $found = false;
        $entityList = $this->getEntityList($responseDataList);

        // Check if first element in findAll is equal to getNext response entity
        if (!empty($entityList[0]) && !empty($responseDataGetNext['entity'])) {
            $this->logResponseData('FIRST MAIL QUEUE IN LIST', $entityList[0]);
            // If both entities exists, indicate that are found and check the data
            $found = true;
            $this->checkEntityData($entityList[0], $this->getEntity($responseDataGetNext));
        }
        else if (empty($entityList[0]) && empty($responseDataGetNext['entity'])) {
            // If both are empty, only indicate that is found to not trigger null unexpected errors in checkEntityData()
            $found = true;
        }

        // Check the entity has been found
        $this->assertTrue($found, "{$this->entityTypeName} not found in list");
    }
    

    /**
     * Test that an invalid entity cannot be created
     *
     * @param array $invalidData Invalid entity data
     * @param string $expectedErrorPart Expected part of the error message
     */
    protected function testInvalidEntityCreation(array $invalidData, string $expectedErrorPart): void {
        // Log action
        $this->logMessage("Testing invalid {$this->entityTypeName} creation");

        // Create with invalid data
        $responseData = $this->create($this->controller, $invalidData, false);

        // Save endpoint output into log file
        $this->logResponseData("INVALID {$this->entityTypeName} CREATION", $responseData);

        // Assert it failed with the expected error
        $this->assertResponseError($responseData, $expectedErrorPart);
    }

    /**
     * Test that a non-existent entity cannot be found
     *
     * @param int $nonExistentId ID that doesn't exist
     */
    protected function testFindNonExistentEntity(int $nonExistentId): void {
        // Log action
        $this->logMessage("Testing find non-existent {$this->entityTypeName}");

        // Try to find non-existent entity
        $responseData = $this->find($this->controller, $nonExistentId);

        // Save endpoint output into log file
        $this->logResponseData("FIND NON-EXISTENT {$this->entityTypeName}", $responseData);

        // Assert it failed
        $this->assertResponseError($responseData);
    }

    /**
     * Sets associated ids to signature buttons
     *
     * @param array    $signatureButtonData        signature button data
     * @param int      $procedureTypeId            procedure type id
     * @param int      $signatureTypeId            signature type id
     * @param string   $procedureTypeGroupName     procedure type group name
     * @return array Signature button data with the ids associated
     */
    protected function setAssociatedIdsToSignatureButtons(array $signatureButtonData, int $procedureTypeId, int $signatureTypeId, string $procedureTypeGroupName): array {

        $signatureButtonData["procedureTypeId"] = $procedureTypeId;
        $signatureButtonData["signatureTypeId"] = $signatureTypeId;
        $signatureButtonData["group"] = $procedureTypeGroupName;

        return $signatureButtonData;
    }

    /**
     * Sets associated info to procedure
     *
     * @param array    $procedureData          procedure data
     * @param int      $procedureTypeId        procedure type id
     * @return array Procedure data with the ids associated
     */
    protected function setAssociatedInfoToProcedure(array $procedureData, int $procedureTypeId): array {

        $procedureData["externalId"] = TestHelpers::generateExternalId(5);
        $procedureData["procedureTypeId"] = $procedureTypeId;
        $procedureData["appointmentDate"] = TestHelpers::getRequiredAppointmentDate();
        $procedureData["deadlineDate"] = TestHelpers::getDeadlineDate();

        return $procedureData;
    }

    /**
     * Sets associated info to procedure
     *
     * @param array    $procedureTypeGroupData   procedure type group data
     * @param int      $procedureTypeId          procedure type id
     * @return array Procedure type group data with the ids associated
     */
    protected function setAssociatedInfoToProcedureTypeGroup(array $procedureTypeGroupData, int $procedureTypeId): array {

        $procedureTypeGroupData["procedureTypeId"] = $procedureTypeId;

        return $procedureTypeGroupData;
    }

    /**
     * Sets associated info to message queue
     *
     * @param array       $messageQueueData                message queue data
     * @param int         $procedureId                     procedure id
     * @param int         $procedureStakeholderId          procedure stakeholder id
     * @param string      $channel                         channel of the message
     * @param string      $recipient                       recipient of the message
     * @param string      $citizenPassword                 citizen password of the message
     * @return array message queue data with the ids associated
     */
    protected function setAssociatedInfoToMessageQueue(array $messageQueueData, int $procedureId, int $procedureStakeholderId, string $channel, string $recipient, string $citizenPassword): array {

        $messageQueueData["procedureId"] = $procedureId;
        $messageQueueData["procedureStakeholderId"] = $procedureStakeholderId;
        $messageQueueData["channel"] = $channel;
        $messageQueueData["recipient"] = $recipient;
        $messageQueueData["citizenPassword"] = $citizenPassword;

        return $messageQueueData;
    }

    /**
     * Sets associated info to procedure stakeholder
     *
     * @param array    $procedureStakeholderData        procedure Stakeholder data
     * @param int      $procedureId                     procedure id
     * @return array Procedure stakeholder data with the ids associated
     */
    protected function setAssociatedInfoToProcedureStakeholder(array $procedureStakeholderData, int $procedureId): array {

        $procedureStakeholderData["procedureId"] = $procedureId;
        $procedureStakeholderData["personalId"] = TestHelpers::generateExternalId(5);

        return $procedureStakeholderData;
    }

    /**
     * Sets associated info to rgpd activity
     *
     * @param array    $rgpdActivityData                rgpd activity data
     * @param int      $procedureId                     procedure id
     * @param int      $procedureStakeholderId          procedure stakeholder id

     * @return array Rgpd activity data with the ids associated
     */
    protected function setAssociatedInfoToRgpdActivity(array $rgpdActivityData, int $procedureId, int $procedureStakeholderId): array {

        $rgpdActivityData["procedureId"] = $procedureId;
        $rgpdActivityData["procedureStakeholderId"] = $procedureStakeholderId;

        return $rgpdActivityData;
    }

    /**
     * Get a controller instance for cleanup
     *
     * @param string $controllerClass Controller class name
     * @return EntityController Controller instance
     */
    protected function getControllerInstance(string $controllerClass = ''): EntityController {
        return $this->controller;
    }
}