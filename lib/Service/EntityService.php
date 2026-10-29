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

namespace OCA\VTramit\Service;

use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\AppFramework\Db\Entity;

use OCA\VTramit\Enum\ActivityType;
use OCA\VTramit\Db\EntityMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Helper\BeHelper;
use OCA\VTramit\Helper\TimeLogger;

use OCA\VTramit\Exception\InvalidIdException;

use OCA\VTramit\Service\ConfigService;

abstract class EntityService {

    /** @var LoggerInterface */
    protected $logger;

    /** @var IL10N */
    protected $translate;

    /** @var ActivityManager */
    protected $activityManager;

    /** @var ConfigService */
    protected $configService;

    /** @var EntityMapper */
    protected $entityMapper;

    /** @var TimeLogger */
    protected $timeLogger;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        EntityMapper $entityMapper
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->activityManager = $activityManager;
        $this->configService = $configService;
        $this->entityMapper = $entityMapper;

        $this->timeLogger = new TimeLogger($this->logger, $this->configService);
    }

    /**
     * Creates and obtain a new entity.
     */
    abstract protected function newEntity();

    /**
     * Indicates wether the activity is allowed to be reported or not.
     *
     * @return bool  true if activity is allowed to be reported; othersise, false
     */
    protected function allowReportActivity(): bool {
        return false;
    }

    /**
     * Get the list of fields that should be ignored when receiving new data.
     *
     * @return array  list of fields to ignore on update
     */
    protected function fieldsToIgnoreOnUpdate(): array {
        return [];
    }

    /**
     * Obtain all existing entities.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function findAll(): ResponseInfo {
        $list = $this->entityMapper->findAll();
        $response = new ResponseInfo();
        $response->setInfo('list', $list);

        return $response;
    }

    /**
     * Obtain entity given its identifier.
     *
     * @return ResponseInfo  {result, entity}
     */
    public function find(int $id): ResponseInfo {
        $entity = $this->entityMapper->find($id);
        $response = new ResponseInfo();
        $response->setInfo('entity', $entity);

        return $response;
    }

    /**
     * Create a new entity using given data.
     *
     * @param array  $data    entity data
     * @param string $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, message, ?entity}
     */
    public function create(array $data, string $userId): ResponseInfo {
        $entity = $this->newEntity();
        $entity = $entity->fromParams($data);

        // Ensure the given data does NOT try to insert an identifier
        if (!empty($entity->getId())) {
            throw new InvalidIdException($this->translate->t('The data contains an identifier while creating an entity with auto-increment identifier'));
        }

        $this->entityMapper->beginTransaction();

        $this->timeLogger->startTime('validateEntityToCreate');
        $response = $this->validateEntityToCreate($entity, $userId);
        $this->timeLogger->logTime('validateEntityToCreate', 'Validate before create');

        if ($response->isOk()) {
            $this->timeLogger->startTime('beforeCreate');
            $entity = $this->beforeCreate($entity, $userId);
            $this->timeLogger->logTime('beforeCreate', 'Actions before create');

            $this->timeLogger->startTime('insertEntity');
            $entity = $this->entityMapper->insert($entity);
            $this->timeLogger->logTime('insertEntity', 'Insert in database');

            $response->addMessage($this->translate->t('The entity has been created'));
            if ($this->allowReportActivity()) {
                $this->activityManager->reportEntityActivity($this->entityMapper, null, ActivityType::CREATED, $data, $entity->getId());
            }

            $this->timeLogger->startTime('afterCreate');
            $newResponse = $this->afterCreate($entity, $userId);
            $this->timeLogger->logTime('afterCreate', 'Actions after create');

            $response->mergeResponseInfo($newResponse);
            $response->setInfo('entity', $entity);
        }

        $this->entityMapper->commitTransaction();

        return $response;
    }

    /**
     * Update existing entity using JSON data.
     *
     * @param array  $newData  entity data in JSON format
     * @param string $userId   identifier of user executing the operation
     * @return ResponseInfo  {result, message, ?entity}
     */
    public function update(array $newData, string $userId): ResponseInfo {
        // Ensure the given data does HAVE an identifier
        if (empty($newData['id'])) {
            throw new InvalidIdException($this->translate->t('The data does not contain the identifier of the entity to update'));
        }
        $id = $newData['id'];

        foreach($this->fieldsToIgnoreOnUpdate() as $field) {
            unset($newData[$field]);
        }

        $this->entityMapper->beginTransaction();

        $oldEntity = $this->entityMapper->find($id);
        $oldData = $oldEntity->jsonSerialize();

        $diff = BeHelper::arrayDiff($newData, $oldData);
        $data = array_merge($oldData, $newData);

        $entity = $this->newEntity();
        $entity = $entity->fromParams($data);

        $this->timeLogger->startTime('validateEntityToUpdate');
        $response = $this->validateEntityToUpdate($entity, $oldEntity, $diff, $userId);
        $this->timeLogger->logTime('validateEntityToUpdate', 'Validate before update');

        if ($response->isOk()) {
            $this->timeLogger->startTime('beforeUpdate');
            $entity = $this->beforeUpdate($entity, $oldEntity, $diff, $userId);
            $this->timeLogger->logTime('beforeUpdate', 'Actions before update');

            $this->timeLogger->startTime('updateEntity');
            $entity = $this->entityMapper->update($entity);
            $this->timeLogger->logTime('updateEntity', 'Update in database');

            $response->addMessage($this->translate->t('The entity has been updated'));
            if ($this->allowReportActivity()) {
                $subjectParams = array_merge($newData, [
                    'oldData' => $oldData,
                    'newData' => $data,
                    'diff' => $diff,
                ]);
                $this->activityManager->reportEntityActivity($this->entityMapper, null, ActivityType::CHANGED, $subjectParams, $id);
            }

            $this->timeLogger->startTime('afterUpdate');
            $newResponse = $this->afterUpdate($entity, $diff, $userId);
            $this->timeLogger->logTime('afterUpdate', 'Actions after update');

            $response->mergeResponseInfo($newResponse);
            $response->setInfo('entity', $entity);
        }

        $this->entityMapper->commitTransaction();

        return $response;
    }

    /**
     * Updates given entity or create a new one if not existing.
     *
     * @param array  $data    entity data in JSON format
     * @param string $userId  identifier of user executing the operation
     * @return ResponseInfo  {result, message, ?entity}
     */
    public function createOrUpdate(array $data, string $userId): ResponseInfo {
        try {
            return $this->update($data, $userId);
        }
        catch (InvalidIdException $e) {
            return $this->create($data, $userId);
        }
    }

    /**
     * Validates entity information being created.
     *
     * @param Entity $entity  the entity object to validate
     * @param string $userId  identifier of user executing the operation
     * @return ResponseInfo  {result}
     */
    protected function validateEntityToCreate(Entity $entity, string $userId): ResponseInfo {
        return new ResponseInfo();
    }

    /**
     * Operations to apply before create entity.
     *
     * @param Entity $entity  the entity object being created
     * @param string $userId  identifier of user executing the operation
     * @return Entity         the entity object being created with any applied modification
     */
    protected function beforeCreate(Entity $entity, string $userId): Entity {
        return $entity;
    }

    /**
     * Operations to apply after create entity.
     *
     * @param Entity $entity  the entity object that has been created
     * @param string $userId  identifier of user executing the operation
     * @return ResponseInfo  {result}
     */
    protected function afterCreate(Entity $entity, string $userId): ResponseInfo {
        return new ResponseInfo();
    }

    /**
     * Validates entity information being updated.
     *
     * @param Entity $newEntity  the entity object to validate
     * @param Entity $oldEntity  the previous existing entity object
     * @param array  $diff       the different elements between the new and old entities
     * @param string $userId     identifier of user executing the operation
     * @return ResponseInfo  {result}
     */
    protected function validateEntityToUpdate(Entity $newEntity, Entity $oldEntity, array $diff, string $userId): ResponseInfo {
        return new ResponseInfo();
    }

    /**
     * Operations to apply before update entity.
     *
     * @param Entity $newEntity  the entity with the new data; that is the object that should be modified
     * @param Entity $oldEntity  the existing entity object
     * @param array  $diff       the different elements between the new and old entities
     * @param string $userId     identifier of user executing the operation
     * @return Entity            the entity object being updated with any applied modification
     */
    protected function beforeUpdate(Entity $newEntity, Entity $oldEntity, array $diff, string $userId): Entity {
        return $newEntity;
    }

    /**
     * Operations to apply after update entity.
     *
     * @param Entity $entity  the entity object that has been updated
     * @param array  $diff    the different elements between the new and old entities
     * @param string $userId  identifier of user executing the operation
     * @return ResponseInfo  {result}
     */
    protected function afterUpdate(Entity $entity, array $diff, string $userId): ResponseInfo {
        return new ResponseInfo();
    }

    /**
     * Delete entity by given id.
     *
     * @param int $id         identifier of the entity to delete
     * @return ResponseInfo  {result}
     */
    public function delete(int $id): ResponseInfo {
        $entity = $this->entityMapper->find($id);

        $response = $this->beforeDelete($entity);
        if ($response->isOk()) {
            $this->entityMapper->delete($entity);
            $response->addMessage($this->translate->t('The entity has been deleted'));
            if ($this->allowReportActivity()) {
                $this->activityManager->reportEntityActivity($this->entityMapper, null, ActivityType::DELETED, [], $id);
            }
            $newResponse = $this->afterDelete($entity);

            $response->mergeResponseInfo($newResponse);
            $response->setInfo('entity', $entity);
        }

        return $response;
    }

    /**
     * Operations to apply before delete entity.
     *
     * @param Entity $entity  the entity to delete
     * @return bool           indicates wether the entity can be deleted or not
     */
    protected function beforeDelete(Entity $entity): ResponseInfo {
        return new ResponseInfo();
    }

    /**
     * Operations to apply after update entity.
     *
     * @param Entity $entity  the entity object that has been deleted
     * @return ResponseInfo  {result}
     */
    protected function afterDelete(Entity $entity): ResponseInfo {
        return new ResponseInfo();
    }
}
