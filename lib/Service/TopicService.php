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

use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\Topic;
use OCA\VTramit\Db\TopicMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Helper\BeHelper;

class TopicService extends EntityService {

    /** @var TopicMapper */
    private $topicMapper;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        TopicMapper $topicMapper
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $topicMapper);
        $this->topicMapper = $topicMapper;
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new Topic();
    }

    /**
     * Find a topic by its identifier.
     *
     * @param int $topicId
     * @return ResponseInfo
     */
    public function findById(int $topicId): ResponseInfo {
        $topic = $this->entityMapper->find($topicId);
        $response = new ResponseInfo();
        $response->setInfo('entity', $topic);

        return $response;
    }

    /**
     * Get the procedure type signatures that matches given signatures.
     *
     * @param int $name  identifier of the procedure type for which obtain matching groups
     * @return ResponseInfo
     */
    public function findByName(string $name): ResponseInfo {
        $topic = $this->findUniqueByName($name);
        $response = new ResponseInfo();
        $response->setInfo('entity', $topic);

        return $response;
    }

    /**
     * Get filtered topcis.
     *
     * Valid values for $filter:
     * 'name'            topic name
     *
     * @param array  $filter           filter parameters as array or JSON
     * @param string $userId           identifier of user executing the operation
     * @return ResponseInfo  {result, list[]}
     */
    public function findByFilter(array $filter, string $userId = null): ResponseInfo {
        $list = $this->entityMapper->findByFilter($filter);
        $response = new ResponseInfo();
        $response->setInfo('list', $list);
        return $response;
    }

    /**
     * Gets the help URL related to the topic of the given procedure.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     *   The procedure entity containing the topic reference.
     *
     * @return string
     *   The help URL of the topic if available, or NULL if no topic is found.
     */
    public function getTopicHelpUrlByProcedure(Procedure $procedure): string {
        return $this->getTopicHelpByProcedure($procedure)['helpUrl'];
    }

    /**
     * Gets the help URL related to the topic of the given procedure.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     *   The procedure entity containing the topic reference.
     *
     * @return string
     *   The help URL of the topic if available, or NULL if no topic is found.
     */
    public function getTopicHelpTargetByProcedure(Procedure $procedure): string {
        return $this->getTopicHelpByProcedure($procedure)['helpTarget'];
    }

    /**
     * Gets the help URL related to the topic of the given procedure.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     *   The procedure entity containing the topic reference.
     *
     * @return string|null
     *   The help URL of the topic if available, or NULL if no topic is found.
     */
    public function getTopicHelpByProcedure(Procedure $procedure): array {
        $topic = $this->findUniqueByName($procedure->getTopic() ?? '');
        return [
            'helpUrl' => $topic?->getHelpUrl() ?? '',
            'helpTarget' => $topic?->getHelpTarget() ?? ''
        ];
    }

    /**
     * Gets the minimum number of documents the citizen is expected to
     * upload, as declared on the topic linked to the given procedure.
     *
     * @return int  the configured minimum, or 0 when there is no topic
     *              or the topic has no min set
     */
    public function getTopicMinRequiredDocsByProcedure(Procedure $procedure): int {
        $topic = $this->findUniqueByName($procedure->getTopic() ?? '');
        return (int) ($topic?->getMinRequiredDocs() ?? 0);
    }

    /**
     * Returns the best matching Topic for given name.
     *
     * @param string $name
     *   The input name used to search and compare against Topic entities.
     *
     * @return \OCA\VTramit\Db\Topic|null
     *   The Topic entity with the best matching score, or NULL if no match is found.
     */
    private function findUniqueByName(string $name): ?Topic {
        $list = $this->entityMapper->findByName($name);

        $match = null;
        $matchScore = -1.0;
        $needle = mb_strtolower(trim($name));

        foreach ($list as $topic) {
            $candidateName = $topic->getName();
            $score = BeHelper::scoreNameMatch($needle, $candidateName);

            if ($score > $matchScore) {
                $matchScore = $score;
                $match = $topic;
                if ($matchScore === 100.0) {
                    break; // exact match
                }
            }
        }

        return $match;
    }
}
