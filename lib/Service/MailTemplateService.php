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

use OCA\VTramit\Db\MailTemplate;
use OCA\VTramit\Db\MailTemplateMapper;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureTokenService;

class MailTemplateService extends EntityService {

    /** @var MailTemplateMapper */
    private $mailTemplateMapper;

    /** @var ProcedureTokenService */
    private $procedureTokenService;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        MailTemplateMapper $mailTemplateMapper,
        ProcedureTokenService $procedureTokenService
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $mailTemplateMapper);

        $this->procedureTokenService = $procedureTokenService;
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new MailTemplate();
    }


    //
    // Mail management
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the list of available variables to use in the email body.
     *
     * @return ResponseInfo  {result, list[]}
     */
    public function getListOfEmailVariables(): ResponseInfo {
        $procedure = new Procedure();
        $procedureStakeholder = new ProcedureStakeholder();
        $replacements = $this->procedureTokenService->getListOfReplacements($procedure, $procedureStakeholder);

        $response = new ResponseInfo();
        $response->setInfo('list', array_keys($replacements));

        return $response;
    }

    /**
     * Get the procedure type signatures that matches given signatures.
     *
     * @param int   $mailTemplateId  identifier of the procedure type for which obtain matching groups
     * @return ResponseInfo                  {result, entity[]}
     */
    public function findById(int $mailTemplateId): ResponseInfo {
        $entity = $this->entityMapper->find($mailTemplateId);
        $response = new ResponseInfo();
        $response->setInfo('entity', $entity);

        return $response;
    }
}
