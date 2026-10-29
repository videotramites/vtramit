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
use OCP\IURLGenerator;
use OCP\Files\IRootFolder;

use OCA\VTramit\Constants;
use OCA\VTramit\ProcedureStatus;

use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureTypeMapper;
use OCA\VTramit\Db\ResponseInfo;

use OCA\VTramit\Exception\DataNotFoundException;

use OCA\VTramit\Helper\NextcloudHelper;

use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\ProcedureFileService;
use OCA\VTramit\Service\SignatureService;

/**
 * Operaciones pendientes:
 * - Job: anonimización
 * - Job: eliminación de archivos
 * - Job: eliminación de procedimiento
 *
 */

class ProcedureJobService {

    /** @var LoggerInterface  */
    protected $logger;

    /** @var IL10N */
    protected $translate;

    /** @var IRootFolder */
    private $rootFolder;

    /** @var IURLGenerator */
    private $urlGenerator;

    /** @var ProcedureMapper */
    protected $procedureMapper;

    /** @var ProcedureStakeholderMapper */
    private $procedureStakeholderMapper;

    /** @var ProcedureStakeholderService */
    private $procedureStakeholderService;

    /** @var ProcedureTypeMapper */
    private $procedureTypeMapper;

    /** @var ProcedureService */
    private $procedureService;

    /** @var ProcedureFileService */
    private $procedureFileService;

    /** @var NextcloudHelper */
    private $nextcloudHelper;

    /** @var ConfigService */
    private $configService;

    /** @var SignatureService */
    private $signatureService;


    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        IRootFolder $rootFolder,
        IURLGenerator $urlGenerator,
        ProcedureMapper $procedureMapper,
        ProcedureService $procedureService,
        ProcedureFileService $procedureFileService,
        ProcedureStakeholderMapper $procedureStakeholderMapper,
        ProcedureStakeholderService $procedureStakeholderService,
        ProcedureTypeMapper $procedureTypeMapper,
        NextcloudHelper $nextcloudHelper,
        ConfigService $configService,
        SignatureService $signatureService
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->rootFolder = $rootFolder;
        $this->urlGenerator = $urlGenerator;
        $this->procedureMapper = $procedureMapper; // en uso
        $this->procedureStakeholderMapper = $procedureStakeholderMapper; // en uso
        $this->procedureStakeholderService = $procedureStakeholderService; // en uso
        $this->procedureTypeMapper = $procedureTypeMapper; // en uso
        $this->procedureService = $procedureService; // en uso
        $this->procedureFileService = $procedureFileService; // en uso
        $this->nextcloudHelper = $nextcloudHelper; // en uso
        $this->configService = $configService; // en uso
        $this->signatureService = $signatureService; // en uso
    }


    //
    // Jobs: Purge data
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Purge old data which means 2 operations: remove appointment structure and
     * anonymize data in database.
     *
     * The operation is done for cancelled and completed appointments which state
     * has not been changed in the last 72 hours.
     */
    public function purgeData(): ResponseInfo {
        $this->expireOldData();
        $this->cleanFilesAndAnonymizeForCancelledProcedures();
        $this->cleanFilesForCompletedProcedures();

        $response = new ResponseInfo();
        return $response;
    }

    /**
     * Set status to expired for procedures older than the anonymization period
     * defined in the procedure type and yet not solved.
     */
    private function expireOldData() {
        $statusToExpire = [ProcedureStatus::INITIALIZING, ProcedureStatus::CREATED, ProcedureStatus::PENDANT];

        $procedureTypeList = $this->procedureTypeMapper->findAll();
        foreach($procedureTypeList as $procedureType) {

            $anonymizationPeriod = $procedureType->getProcedureAnonymizationPeriod(); // hours

            // Ignore procedure type with no anonymization period
            if ($anonymizationPeriod <= 0) {
                continue;
            }

            $procedures = $this->procedureMapper->findNotAnonymizedByStatusAndAge(
                $procedureType->getId(),
                $statusToExpire,
                $anonymizationPeriod,
                $this->configService->getDateTimezone()
            );

            foreach($procedures as $procedure) {
                $this->procedureService->cancelProcedure($procedure, Constants::USERNAME_JOB, ProcedureStatus::EXPIRED, true);
            }
        }
    }

    /**
     * Anonymizes any cancelled procedure.
     */
    private function cleanFilesAndAnonymizeForCancelledProcedures() {
        $statusToAnonymize = [ProcedureStatus::EXPIRED, ProcedureStatus::DUPLICATED, ProcedureStatus::CANCELLED];
        $procedureList = $this->procedureMapper->findNotAnonymizedByStatus($statusToAnonymize);

        foreach($procedureList as $procedure) {
            // Remove files related to the procedure
            $path = $this->procedureFileService->getLocalPathToProcedure($procedure);
            $this->nextcloudHelper->deletePath($path);

            // Anonymize procedure
            $procedure = $this->procedureService->anonymizeProcedure($procedure);
        }
    }

    /**
     * Delete files associated to procedures which last change is older than
     * the deletion period.
     */
    private function cleanFilesForCompletedProcedures() {
        $procedureTypeList = $this->procedureTypeMapper->findAll();
        foreach($procedureTypeList as $procedureType) {

            $filesDeletionPeriod = $procedureType->getFilesDeletionPeriod(); // hours

            // Ignore procedure type with no files deletion period
            if ($filesDeletionPeriod <= 0) {
                continue;
            }

            $procedures = $this->procedureMapper->findNotAnonymizedByStatusAndAge(
                $procedureType->getId(),
                [ProcedureStatus::COMPLETED],
                $filesDeletionPeriod,
                $this->configService->getDateTimezone()
            );

            foreach($procedures as $procedure) {

                // Do not delete files that are waiting to be signed
                if ($this->signatureService->isProcedureWaitingForSignature($procedure, $procedureType)) {
                    continue;
                }

                // Do not delete files that are waiting to be downloaded
                if ($this->signatureService->procedureHasSignatureWaitingForDownload($procedure, $procedureType)) {
                    continue;
                }

                // Remove files related to the procedure
                try {
                    $path = $this->procedureFileService->getLocalPathToProcedure($procedure);
                    $this->nextcloudHelper->deletePath($path);
                }
                catch (DataNotFoundException) {
                    // NOOP
                }
            }
        }
    }

    /**
     * TODO: Is this method useful or we have to remove it?
     *
     * @codeCoverageIgnore
     */
    private function cleanProcedures() {
        $statusToDelete = [ProcedureStatus::EXPIRED, ProcedureStatus::DUPLICATED, ProcedureStatus::CANCELLED, ProcedureStatus::COMPLETED];

        $procedureTypeList = $this->procedureTypeMapper->findAll();
        foreach($procedureTypeList as $procedureType) {

            $deletionPeriod = $procedureType->getProcedureDeletionPeriod(); // hours

            // Ignore procedure type with no anonymization period
            if ($deletionPeriod <= 0) {
                continue;
            }

            $procedures = $this->procedureMapper->findByStatusAndAge(
                $procedureType->getId(),
                $deletionPeriod,
                $statusToDelete,
                $this->configService->getDateTimezone()
            );

            foreach($procedures as $procedure) {

                // Do not delete procedures that are waiting to be signed
                if ($this->signatureService->isProcedureWaitingForSignature($procedure, $procedureType)) {
                    continue;
                }

                // Do not delete procedures that are waiting to be downloaded
                if ($this->signatureService->procedureHasSignatureWaitingForDownload($procedure, $procedureType)) {
                    continue;
                }

                // Do not delete procedures wich have files yet in path and needs
                // to expire access before deletion
                $path = $this->procedureFileService->getLocalPathToProcedure($procedure);
                $folderExists = $this->nextcloudHelper->folderExists($path);
                if ($folderExists && $procedureType->getFilesDeletionPeriod() > 0) {
                    continue;
                }

                // TODO: Delete signature???

                $response = $this->procedureStakeholderService->deleteByProcedureId($procedure->getId());

                if ($response->isOk()) {
                    $this->procedureService->delete($procedure->getId());
                }
            }
        }

    }


    //
    // Jobs: Prepare work
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Obtains the list of procedures with a given status in a date range.
     *
     * This method has been created to test the procedures that are being updated by the
     * prepare today's work job.
     *
     */
    public function findWorkForTodayToPrepare(): ResponseInfo {
        $minDate = new \DateTime("today", $this->configService->getDateTimezone());
        $maxDate = new \DateTime("tomorrow", $this->configService->getDateTimezone());

        $result = $this->procedureMapper->findByStatusDateRange(ProcedureStatus::CREATED, $minDate, $maxDate);

        $response = new ResponseInfo();
        $response->setInfo('list', $result);

        return $response;
    }

    /**
     * Change state from created to pendant for all procedures to be done during today.
     *
     * @return ResponseInfo  {result}
     */
    public function prepareWorkForToday(): ResponseInfo {
        $minDate = new \DateTime("today", $this->configService->getDateTimezone());
        $maxDate = new \DateTime("tomorrow", $this->configService->getDateTimezone());

        $result = $this->procedureMapper->changeStatusByDateRange(ProcedureStatus::CREATED, ProcedureStatus::PENDANT, $minDate, $maxDate);

        $response = new ResponseInfo();
        $response->setMessage($this->translate->t('%s procedures has been updated to pendant status', [$result]));

        return $response;
    }

}
