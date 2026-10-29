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

use Exception;

use OCP\IDBConnection;
use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;

use OCA\VTramit\Enum\MessageChannel;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\MessageQueue;
use OCA\VTramit\Db\MessageQueueMapper;

use OCA\VTramit\Exception\QueueNotFound;

class MessageQueueService extends EntityService {

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        MessageQueueMapper $mapper,
        ConfigService $configService
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $mapper);
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new MessageQueue();
    }

    /**
     * Indicates wether the activity is allowed to be reported or not.
     *
     * @return bool  true if activity is allowed to be reported; othersise, false
     */
    protected function allowReportActivity(): bool {
        return true;
    }

    /**
     * Obtains the next message to be sent.
     *
     * @param MessageChannel|string $channel  the channel which belongs the message to obtain
     *
     * @return ResponseInfo
     */
    public function getNext(MessageChannel|string $channel): ResponseInfo {
        $message = $this->entityMapper->getNext($channel);
        $response = new ResponseInfo();
        $response->setInfo('entity', $message);

        return $response;
    }

    /**
     * Obtains the next message to be sent.
     *
     * @param MessageChannel|string $channel                 the channel which belongs the message to obtain
     * @param int                   $procedureId             procedure indentifier to which the message belongs
     * @param ?int                  $procedureStakeholderId  procedure stakeholder indentifier associated to the message to obtain
     *
     * @return ResponseInfo
     */
    public function getNextByProcedure(MessageChannel|string $channel, int $procedureId, ?int $procedureStakeholderId = null): ResponseInfo {
        $message = $this->entityMapper->getNextByProcedure($channel, $procedureId, $procedureStakeholderId);
        $response = new ResponseInfo();
        $response->setInfo('entity', $message);

        return $response;
    }

    /**
     * Creates and add a new message to the message queue.
     *
     * @param int            $procedureId             procedure indentifier for which the message is related to
     * @param MessageChannel $channel                 communication channel
     * @param string         $recipient               message recipient
     * @param string         $subject                 message subject
     * @param string         $body                    body content
     * @param string         $citizenPassword         citizen password
     * @param string         $procedureStakeholderId  related stakeholder, if any
     * @return ResponseInfo  {result, message, ?entity}
     */
    public function createMessage(
        int $procedureId,
        MessageChannel $channel,
        string $recipient,
        string $subject,
        string $body,
        string $citizenPassword,
        ?int $procedureStakeholderId = null
    ): ResponseInfo {

        $data = [
            'procedureId' => $procedureId,
            'channel' => $channel->value,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'citizenPassword' => $citizenPassword,
        ];

        if (!empty($procedureStakeholderId)) {
            $data['procedureStakeholderId'] = $procedureStakeholderId;
        }

        return parent::create($data, 'messenger');
    }

    /**
     * Find all the messages in message queue for given procedure identifier.
     *
     * @param int $procedureId  procedure indentifier to filter for
     * @return array            list of found entities
     */
    public function findByProcedureId(int $procedureId): array {
        return $this->entityMapper->findByProcedureId($procedureId);
    }

    /**
     * Delete all the messages in queue related to given procedure.
     *
     * @param int $id  identifier of the procedure which belongs the messages to delete
     * @return ResponseInfo  {result}
     */
    public function deleteByProcedureId(int $procedureId): ResponseInfo {
        $response = new ResponseInfo();

        $messageList = $this->entityMapper->findByProcedureId($procedureId);
        foreach($messageList as $message) {
            $deleteResponse = $this->delete($message->getId());
            $response->mergeResponseInfo($deleteResponse);
        }

        return $response;
    }
}
