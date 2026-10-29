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

use OCA\VTramit\Db\MailTemplate;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureType;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Service\ProcedureTypeGroupService;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Db\SignatureButton;
use OCA\VTramit\Exception\EntityNotFoundException;

use OCA\VTramit\Helper\UserGroupHelper;

class ProcedureMailService {

    /**
     * PHPUnit Test: Ignore _construct coverage as the class is initialized with mocks
     * @codeCoverageIgnore
     */
    public function __construct(
        protected IL10N $translate,
        protected MailTemplateService $mailTemplateService,
        protected ProcedureTypeService $procedureTypeService,
        protected ProcedureTypeGroupService $procedureTypeGroupService,
        protected ProcedureStakeholderMapper $procedureStakeholderMapper,
        protected UserGroupHelper $userGroupHelper,
        protected ConfigService $configService,
        protected MailQueueService $mailQueueService,
        protected ProcedureTokenService $procedureTokenService,
    ) {}

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
     * Obtain the stakeholder mail template for given procedure.
     *
     * @param Procedure $entity  procedure entity for which obtain the mail template
     * @param SignatureButton $signatureButton the signature button entity knows which template is assigned to the
     *                                          stakeholder email to request a signature
     * @return MailTemplate|null
     */
    protected function getStakeholderTemplate(Procedure $entity, ?SignatureButton $signatureButton): ?MailTemplate {
        if (empty($entity->getAssignedGroupId())) {
            return null;
        }
        if (!empty($signatureButton)) {
            $templateId = $signatureButton->getMailTemplateId();
        } else {
            try {
                $templateId = $this->procedureTypeGroupService->findNotifyStakeholderTemplateId($entity->getProcedureTypeId(), $entity->getAssignedGroupId());
            }
            catch (EntityNotFoundException $e) {
                return null;
            }
        }

        try {
            return $this->mailTemplateService->find($templateId)->getInfo('entity');
        }
        catch (EntityNotFoundException $e) {
            return null;
        }
    }

    /**
     * Obtain the staff mail template for given procedure.
     *
     * @param Procedure $entity  procedure entity for which obtain the mail template
     * @return MailTemplate|null
     */
    protected function getStaffTemplate(Procedure $entity): ?MailTemplate {
        try {
            $templateId = $this->procedureTypeGroupService->findNotifyStaffTemplateId($entity->getProcedureTypeId(), $entity->getAssignedGroupId());
        }
        catch (EntityNotFoundException $e) {
            return null;
        }

        try {
            return $this->mailTemplateService->find($templateId)->getInfo('entity');
        }
        catch (EntityNotFoundException $e) {
            return null;
        }
    }


    //
    // Mail management - staff
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Prepare and send an e-mail to all staff in given procedure.
     *
     * @param ProcedureStakeholder $procedureMainStakeholder  the main stakeholder of the procedure
     * @param Procedure $procedure  the procedure assigned to the staff
     * @return ResponseInfo  {result, message}
     */
    public function sendEmailToStaff(ProcedureStakeholder $procedureMainStakeholder, Procedure $procedure): ResponseInfo {
        $response = new ResponseInfo();

        if (!$procedure->getAssignedUserId()) {
            return $response;
        }

        $recipient = $this->userGroupHelper->getUserEmail($procedure->getAssignedUserId());
        if (empty($recipient)) {
            return $response;
        }

        $procedureType = $this->getProcedureType($procedure);
        if (!$procedureType->getNotifyStaff()) {
            return $response;
        }

        $mailTemplate = $this->getStaffTemplate($procedure);
        if (empty($mailTemplate)) {
            $response->setError($this->translate->t('There is no template defined for the staff to send the email'));
            return $response;
        }

        $mailResponse = $this->addEmailToQueue($recipient, $procedureMainStakeholder, $procedure, $mailTemplate);
        $response->mergeResponseInfo($mailResponse);

        return $response;
    }


    //
    // Mail management - stakeholders
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Prepare and send an e-mail to all stakeholders in given procedure.
     *
     * @param Procedure $procedure  the procedure with all the recipient stakeholders
     * @param SignatureButton $signatureButton the signature button used to request a signature for this procedure.
     *                                          It contains the information for which mail template to use
     * @return ResponseInfo  {result, message}
     */
    public function sendEmailToAllStakeholders(Procedure $procedure, SignatureButton $signatureButton = null): ResponseInfo {
        $response = new ResponseInfo();

        $procedureType = $this->getProcedureType($procedure);
        if (!$procedureType->getNotifyStakeholder()) {
            return $response;
        }

        $mailTemplate = $this->getStakeholderTemplate($procedure, $signatureButton);
        if (empty($mailTemplate)) {
            $response->setError($this->translate->t('There is no template defined to send the email'));
            return $response;
        }

        $stakeholders = $this->getStakeholders($procedure);
        foreach ($stakeholders as $procedureStakeholder) {
            $mailResponse = $this->addEmailToStakeholderToQueue($procedureStakeholder, $procedure, $mailTemplate);
            $response->mergeResponseInfo($mailResponse);
        }

        return $response;
    }

    /**
     * Prepare and send an e-mail to given stakeholder recipient.
     *
     * @param ProcedureStakeholder $procedureStakeholder  the recipient
     * @param SignatureButton $signatureButton the signature button used to request a signature for this procedure.
     *                                          It contains the information for which mail template to use
     * @return ResponseInfo  {result, message}
     */
    public function sendEmailToStakeholder(ProcedureStakeholder $procedureStakeholder, Procedure $procedure, ?SignatureButton $signatureButton = null): ResponseInfo {
        $response = new ResponseInfo();

        $procedureType = $this->getProcedureType($procedure);
        if (!$procedureType->getNotifyStakeholder()) {
            return $response;
        }

        $mailTemplate = $this->getStakeholderTemplate($procedure, $signatureButton);
        if (empty($mailTemplate)) {
            $response->setError($this->translate->t('There is no template defined to send the email'));
            return $response;
        }

        return $this->addEmailToStakeholderToQueue($procedureStakeholder, $procedure, $mailTemplate);
    }

    /**
     * Send a new e-mail for the given procedure. During the process shared
     * links are created with a stakeholder password. New shared links are
     * set in the stakeholder object.
     *
     * @param ProcedureStakeholder $procedureStakeholder  stakeholder who should receive notification
     * @param Procedure            $procedure             procedure to notify
     * @param MailTemplate         $mailTemplate          the email template to use for the notification
     * @return ResponseInfo  {result, message}
     */
    protected function addEmailToStakeholderToQueue(ProcedureStakeholder $procedureStakeholder, Procedure $procedure, MailTemplate $mailTemplate): ResponseInfo {
        return $this->addEmailToQueue(
            $procedureStakeholder->getEmail(),
            $procedureStakeholder,
            $procedure,
            $mailTemplate
        );
    }


    //
    // Mail management
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Send a new e-mail for the given procedure. During the process shared
     * links are created with a stakeholder password. New shared links are
     * set in the stakeholder object.
     *
     * @param ProcedureStakeholder $procedureStakeholder  stakeholder who should receive notification
     * @param Procedure            $procedure             procedure to notify
     * @param MailTemplate         $mailTemplate          the email template to use for the notification
     * @return ResponseInfo  {result, message}
     */
    protected function addEmailToQueue(string $recipientEmail, ProcedureStakeholder $procedureStakeholder, Procedure $procedure, MailTemplate $mailTemplate): ResponseInfo {
        $response = new ResponseInfo();

        if (!empty($recipientEmail)) {
            $subject = $mailTemplate->getSubject();
            $body = $this->getEmailBody($procedure, $procedureStakeholder, $mailTemplate);
            $password = $procedureStakeholder->getEntryPassword($this->configService->getEncryptionKey());
            $this->mailQueueService->createMail($procedure->getId(), $subject, $body, $password, $recipientEmail);

            $response->setMessage($this->translate->t('Recorded message for sending'));
        }
        else {
            $response->setError($this->translate->t('There is no email to address the message'));
        }

        return $response;
    }

    /**
     * Obtain the email body to send to given stakeholder using given template.
     *
     * @param Procedure            $procedure             procedure to notify
     * @param ProcedureStakeholder $procedureStakeholder  stakeholder who should receive notification
     * @param MailTemplate         $mailTemplate          the email template to use for the notification
     * @return string                                     the body to send
     */
    protected function getEmailBody(Procedure $procedure, ProcedureStakeholder $procedureStakeholder, MailTemplate $mailTemplate): string {
        $template = $mailTemplate->getTemplate();
        return $this->procedureTokenService->applyReplacements($procedure, $procedureStakeholder, $template);
    }
}
