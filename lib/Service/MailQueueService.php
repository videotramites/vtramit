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

use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\MailQueue;
use OCA\VTramit\Db\MailQueueMapper;

use OCA\VTramit\Exception\QueueNotFound;

class MailQueueService extends EntityService {

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        MailQueueMapper $mapper,
        ConfigService $configService
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $mapper);
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new MailQueue();
    }

    /**
     * Obtains the next mail to be sent.
     *
     * @return ResponseInfo
     */
    public function getNext(): ResponseInfo {
        $mail = $this->entityMapper->getNext();
        $response = new ResponseInfo();
        $response->setInfo('entity', $mail);

        return $response;
    }

    /**
     * Creates and add a new mail to the mail queue.
     *
     * @param int    $procedureId      procedure indentifier for which the mail is related to
     * @param string $subject          mail subject
     * @param string $body             body content
     * @param string $citizenPassword  citizen password
     * @param string $mailTo           mail recipient
     * @param string $mailCc           list of e-mails, separated by commas, that will receive a copy of the mail
     * @param string $mailCco          list of e-mails, separated by commas, that will receive a hidden copy of the mail
     * @return ResponseInfo  {result, message, ?entity}
     */
    public function createMail(
        int $procedureId,
        string $subject,
        string $body,
        string $citizenPassword,
        string $mailTo,
        ?string $mailCc = null,
        ?string  $mailCco = null
    ): ResponseInfo {

        $data = [
            'procedureId' => $procedureId,
            'subject' => $subject,
            'body' => $body,
            'citizenPassword' => $citizenPassword,
            'mailTo' => $mailTo
        ];

        if (!empty($mailCc)) {
            $data['mailCc'] = $mailCc;
        }

        if (!empty($mailCco)) {
            $data['mailCco'] = $mailCco;
        }

        return parent::create($data, 'mailer');
    }

    /**
     * Find all the mails in mail queue for given procedure identifier.
     *
     * @param int $procedureId  procedure indentifier to filter for
     * @return array            list of found entities
     */
    public function findByProcedureId(int $procedureId): array {
        return $this->entityMapper->findByProcedureId($procedureId);
    }

    /**
     * Delete all the mails in queue related to given procedure.
     *
     * @param int $id  identifier of the procedure which belongs the mails to delete
     * @return ResponseInfo  {result}
     */
    public function deleteByProcedureId(int $procedureId): ResponseInfo {
        $response = new ResponseInfo();

        $mailList = $this->entityMapper->findByProcedureId($procedureId);
        foreach($mailList as $mail) {
            $deleteResponse = $this->delete($mail->getId());
            $response->mergeResponseInfo($deleteResponse);
        }

        return $response;
    }
}
