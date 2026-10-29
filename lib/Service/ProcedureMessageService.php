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

use OCP\IL10N;

use OCA\VTramit\Enum\MessageChannel;

use OCA\VTramit\Db\MessageTemplate;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureType;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\SignatureButton;

use OCA\VTramit\Exception\EntityNotFoundException;

use OCA\VTramit\Helper\UserGroupHelper;

class ProcedureMessageService {

    public function __construct(
        protected IL10N $translate,
        protected MessageTemplateService $messageTemplateService,
        protected ProcedureTypeService $procedureTypeService,
        protected ProcedureTypeGroupService $procedureTypeGroupService,
        protected ProcedureStakeholderMapper $procedureStakeholderMapper,
        protected UserGroupHelper $userGroupHelper,
        protected ConfigService $configService,
        protected MessageQueueService $messageQueueService,
        protected ProcedureTokenService $procedureTokenService,
    // @codeCoverageIgnoreStart
    ) {}
    // @codeCoverageIgnoreEnd

    /**
     * Obtain the procedure type related to given procedure.
     *
     * @param Procedure $entity  the procedure
     * @return ProcedureType
     */
    protected function getProcedureType(Procedure $entity): ProcedureType {
        $response = $this->procedureTypeService->find($entity->getProcedureTypeId());
        return $response->getInfo('entity');
    }

    /**
     * Obtain all the stakeholders for the given procedure.
     *
     * @param Procedure $entity  procedure entity to which belongs the stakeholders
     * @return array
     */
    protected function getStakeholders(Procedure $entity): array {
        return $this->procedureStakeholderMapper->findByProcedure($entity);
    }

    /**
     * Obtain ALL stakeholder message templates for given procedure, honoring SignatureButton precedence.
     *
     * Rules:
     * - If a SignatureButton with messageTemplateId is provided and enabled, return ONLY that template.
     * - Otherwise, return ALL templates defined at ProcedureTypeGroup level.
     *
     * @param Procedure              $entity             procedure entity for which obtain message templates
     * @param SignatureButton|null   $signatureButton    optional signature button with preferred template
     * @return MessageTemplate[]                          list of message templates (may be empty)
     */
    protected function getNotifyStakeholderTemplateList(Procedure $entity, ?SignatureButton $signatureButton = null): array {
        if (empty($entity->getAssignedGroupId())) {
            return [];
        }

        if (!empty($signatureButton)) {

            if ($signatureButton->getMessageEnabled()
                && $signatureButton->getMessageTemplateId() > 0
            ) {
                try {
                    $template = $this->messageTemplateService
                        ->findById($signatureButton->getMessageTemplateId())
                        ->getInfo('entity');

                    if ($template instanceof MessageTemplate) {
                        return [$template];
                    }
                }
                catch (EntityNotFoundException $e) {
                }
            }

            // No messages to send if no templates are related to the given signature button
            return [];
        }

        $templates = [];
        $idsResponse = $this->procedureTypeGroupService->findNotifyStakeholderMessageTemplateIds(
            $entity->getProcedureTypeId(),
            $entity->getAssignedGroupId()
        );
        $ids = (array) $idsResponse->getInfo('list');

        foreach ($ids as $templateId) {
            try {
                $template = $this->messageTemplateService->findById((int) $templateId)->getInfo('entity');
                if ($template instanceof MessageTemplate) {
                    $templates[] = $template;
                }
            }
            catch (EntityNotFoundException $e) {
                // Skip missing template ids
            }
        }

        return $templates;
    }

    /**
     * Obtain ALL staff message templates for given procedure.
     *
     * @param Procedure $entity  procedure entity for which obtain message templates
     * @return MessageTemplate[] list of message templates (may be empty)
     */
    protected function getNotifyStaffTemplateList(Procedure $entity): array {
        if (empty($entity->getAssignedGroupId())) {
            return [];
        }

        $templates = [];
        $idsResponse = $this->procedureTypeGroupService->findNotifyStaffMessageTemplateIds(
            $entity->getProcedureTypeId(),
            $entity->getAssignedGroupId()
        );
        $ids = (array) $idsResponse->getInfo('list');

        foreach ($ids as $templateId) {
            try {
                $template = $this->messageTemplateService->findById((int) $templateId)->getInfo('entity');
                if ($template instanceof MessageTemplate) {
                    $templates[] = $template;
                }
            }
            catch (EntityNotFoundException $e) {
                // Skip missing template ids
            }
        }

        return $templates;
    }

    /**
     * Build a message body applying token replacements.
     *
     * @param Procedure            $procedure
     * @param ProcedureStakeholder $procedureStakeholder
     * @param MessageTemplate      $messageTemplate
     * @return string
     */
    protected function getMessageBody(Procedure $procedure, ProcedureStakeholder $procedureStakeholder, MessageTemplate $messageTemplate): string {
        $template = $messageTemplate->getTemplate();
        return $this->procedureTokenService->applyReplacements($procedure, $procedureStakeholder, $template);
    }


    //
    // Message management - staff
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Prepare and queue ALL staff messages defined for the given procedure.
     *
     * @param ProcedureStakeholder $procedureMainStakeholder  the main stakeholder of the procedure (for token replacements)
     * @param Procedure            $procedure                 the procedure assigned to the staff
     * @return ResponseInfo                                   {result, message}
     */
    public function sendMessagesToStaff(ProcedureStakeholder $procedureMainStakeholder, Procedure $procedure): ResponseInfo {
        $response = new ResponseInfo();

        if (!$procedure->getAssignedUserId()) {
            return $response;
        }

        $procedureType = $this->getProcedureType($procedure);
        if (!$procedureType->getNotifyStaff()) {
            return $response;
        }

        $templates = $this->getNotifyStaffTemplateList($procedure);
        if (empty($templates)) {
            $response->setError($this->translate->t('There is no template defined for the staff to send the message'));
            return $response;
        }

        foreach ($templates as $messageTemplate) {
            $channel = MessageChannel::tryFromString((string) $messageTemplate->getChannel());
            $recipient = $this->getStaffRecipient($channel, $procedure);
            if (!$channel->isRecipientValid($recipient)) {
                $response->setError($this->translate->t('There is no recipient to address the message'));
                continue;
            }

            $subject = $this->getStaffSubject($channel, $messageTemplate);
            $body = $this->getMessageBody($procedure, $procedureMainStakeholder, $messageTemplate);
            $password = '';

            $queueResponse = $this->messageQueueService->createMessage(
                $procedure->getId(),
                $channel,
                $recipient,
                $subject,
                $body,
                $password,
                null
            );
            $response->mergeResponseInfo($queueResponse);
        }

        return $response;
    }


    //
    // Message management - stakeholders
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Prepare and queue ALL stakeholder messages for the given procedure.
     *
     * - If a SignatureButton is provided and has a messageTemplateId enabled, only that template is used.
     * - Otherwise, ALL group-defined templates are used.
     *
     * @param Procedure            $procedure         the procedure with all the recipient stakeholders
     * @param SignatureButton|null $signatureButton   optional signature button to prioritize its template
     * @return ResponseInfo                           {result, message}
     */
    public function sendMessagesToAllStakeholders(Procedure $procedure, ?SignatureButton $signatureButton = null): ResponseInfo {
        $response = new ResponseInfo();

        $procedureType = $this->getProcedureType($procedure);
        if (!$procedureType->getNotifyStakeholder()) {
            return $response;
        }

        $templates = $this->getNotifyStakeholderTemplateList($procedure, $signatureButton);
        if (empty($templates)) {
            $response->setError($this->translate->t('There is no template defined to send the message'));
            return $response;
        }

        $stakeholders = $this->getStakeholders($procedure);
        foreach ($stakeholders as $procedureStakeholder) {
            $queueResponse = $this->sendMessagesToStakeholder($procedureStakeholder, $procedure, $signatureButton);
            $response->mergeResponseInfo($queueResponse);
        }

        return $response;
    }

    /**
     * Prepare and queue ALL messages for a single stakeholder.
     *
     * - If a SignatureButton is provided and has a messageTemplateId enabled, only that template is used.
     * - Otherwise, ALL group-defined templates are used.
     *
     * @param ProcedureStakeholder $procedureStakeholder  the recipient stakeholder
     * @param Procedure            $procedure             procedure to notify
     * @param SignatureButton|null $signatureButton       optional signature button to prioritize its template
     * @return ResponseInfo                                 {result, message}
     */
    public function sendMessagesToStakeholder(ProcedureStakeholder $procedureStakeholder, Procedure $procedure, ?SignatureButton $signatureButton = null): ResponseInfo {
        $response = new ResponseInfo();

        $procedureType = $this->getProcedureType($procedure);
        if (!$procedureType->getNotifyStakeholder()) {
            return $response;
        }

        $templates = $this->getNotifyStakeholderTemplateList($procedure, $signatureButton);
        if (empty($templates)) {
            $response->setError($this->translate->t('There is no template defined to send the message'));
            return $response;
        }

        foreach ($templates as $messageTemplate) {
            $channel = MessageChannel::tryFromString((string) $messageTemplate->getChannel());
            $recipient = $this->getStakeholderRecipient($channel, $procedureStakeholder);
            if (!$channel->isRecipientValid($recipient)) {
                $response->setError($this->translate->t('There is no recipient to address the message'));
                continue;
            }

            $subject = $this->getStakeholderSubject($channel, $messageTemplate);
            $body = $this->getMessageBody($procedure, $procedureStakeholder, $messageTemplate);
            $password = (string) $procedureStakeholder->getEntryPassword($this->configService->getEncryptionKey());

            $queueResponse = $this->messageQueueService->createMessage(
                $procedure->getId(),
                $channel,
                $recipient,
                $subject,
                $body,
                $password,
                $procedureStakeholder->getId()
            );
            $response->mergeResponseInfo($queueResponse);
        }

        return $response;
    }


    // Helpers
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Resolve recipient for a stakeholder given a channel.
     *
     * @param ?MessageChannel $channel
     * @param ProcedureStakeholder $stakeholder
     * @return string  the recipient; or empty string when no suitable recipient is available
     */
    protected function getStakeholderRecipient(?MessageChannel $channel, ProcedureStakeholder $stakeholder): string {
        return match ($channel) {
            MessageChannel::EMAIL   => (string) $stakeholder->getEmail(),
            MessageChannel::SMS     => (string) $stakeholder->getPhone(),
            MessageChannel::COMMAND => '',
            default                 => '',
        };
    }

    /**
     * Resolve recipient for staff given a channel.
     *
     * @param ?MessageChannel $channel
     * @param Procedure $procedure
     * @return string  the recipient; or empty string when no suitable recipient is available
     */
    protected function getStaffRecipient(?MessageChannel $channel, Procedure $procedure): string {
        return match ($channel) {
            MessageChannel::EMAIL   => (string) $this->userGroupHelper->getUserEmail($procedure->getAssignedUserId()),
            MessageChannel::SMS     => '', // TODO: Obtain info (staff phone)
            MessageChannel::COMMAND => '',
            default                 => '',
        };
    }

    /**
     * Resolve subject for a stakeholder message given the channel.
     *
     * @param ?MessageChannel $channel
     * @param MessageTemplate $template
     * @return string  the subject; or empty string when no suitable subject is available
     */
    protected function getStakeholderSubject(?MessageChannel $channel, MessageTemplate $template): string {
        return match ($channel) {
            MessageChannel::EMAIL    => (string) $template->getSubject(),
            MessageChannel::SMS      => (string) $template->getSubject(),
            MessageChannel::COMMAND  => (string) $template->getSubject(),
            default                  => '',
        };
    }

    /**
     * Resolve subject for a staff message given the channel.
     *
     * @param ?MessageChannel $channel
     * @param MessageTemplate $template
     * @return string  the subject; or empty string when no suitable subject is available
     */
    protected function getStaffSubject(?MessageChannel $channel, MessageTemplate $template): string {
        return match ($channel) {
            MessageChannel::EMAIL    => (string) $template->getSubject(),
            MessageChannel::SMS      => (string) $template->getSubject(),
            MessageChannel::COMMAND  => (string) $template->getSubject(),
            default                  => '',
        };
    }
}
