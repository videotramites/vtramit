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

use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;

use OCA\VTramit\Constants;
use OCA\VTramit\Exception\DataNotFoundException;
use OCA\VTramit\Exception\EntityNotFoundException;

use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureMapper;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Db\ProcedureStakeholderMapper;
use OCA\VTramit\Db\ProcedureType;

use OCA\VTramit\Helper\NextcloudHelper;

class ProcedureFileService {

    public function __construct(
        protected IConfig $config,
        protected IRootFolder $rootFolder,
        protected IL10N $translate,
        protected IURLGenerator $urlGenerator,
        protected NextcloudHelper $nextcloudHelper,
        protected ProcedureMapper $procedureMapper,
        protected ProcedureStakeholderMapper $procedureStakeholderMapper,
        protected ProcedureTypeService $procedureTypeService,
        protected ConfigService $configService,
        protected ProcedureVideoconferenceService $procedureVideoconferenceService
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
     * Obtain the procedure type related to given procedure.
     *
     * @param int $procedure  the procedure
     * @return ProcedureType
     */
    protected function getProcedureTypeByProcedureId(int $procedureId): ProcedureType {
        $response = $this->procedureTypeService->findByProcedureId($procedureId);
        return $response->getInfo('entity');
    }

    /**
     * Obtain the main stakeholder for the given procedure.
     *
     * @param Procedure $entity  procedure entity to which belongs the stakeholder
     * @return ProcedureStakeholder|null
     */
    protected function getMainStakeholder(Procedure $entity): ?ProcedureStakeholder {
        try {
            return $this->procedureStakeholderMapper->findMainByProcedure($entity);
        }
        catch (EntityNotFoundException $e) {
            return null;
        }
    }

    /**
     * Uploads a base64-encoded data URL (e.g., image snapshot) to the user's uploads folder.
     *
     * @param Procedure $procedure  The procedure entity associated with the upload.
     * @param string $userId        The identifier of the user performing the upload.
     * @param string $filename      The filename.
     * @param string $data          The data URL to be uploaded (for example, an image encoded in base64).
     * @return string|null          The path or identifier of the saved file, or NULL if uploads are not
     *                              allowed or the uploads folder is not configured.
     */
    public function uploadToUploadsFolder(Procedure $procedure, string $userId, string $filename, string $data): ?string {
        $file = null;
        $procedureType = $this->getProcedureTypeByProcedureId($procedure->getId());

         if ($procedureType->getAllowUpload()
            && !empty($procedureType->getUploadsFolder())
        ) {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getUploadsFolder(), $userId);

            // Filename base: snapshot-YYYYMMDD-HHMMSS
            //$ts = (new \DateTime())->format('Ymd-His');
            //$baseName = 'snapshot-' . $ts;

            // Save data URL into the target folder (auto-detects extension from MIME)
            $file = $this->nextcloudHelper->saveDataUrl($path, $filename, $data, false);
        }

        return $file;
    }

    /**
     * Uploads a base64-encoded data URL (e.g., image snapshot) to the user's uploads folder.
     *
     * @param Procedure $procedure  The procedure entity associated with the upload.
     * @param string $userId        The identifier of the user performing the upload.
     * @param string $filename      The filename.
     * @param string $data          The data URL to be uploaded (for example, an image encoded in base64).
     * @return string|null          The path or identifier of the saved file, or NULL if uploads are not
     *                              allowed or the uploads folder is not configured.
     */
    public function uploadToDownloadsFolder(Procedure $procedure, string $userId, string $filename, string $data): ?string {
        $file = null;
        $procedureType = $this->getProcedureTypeByProcedureId($procedure->getId());

         if ($procedureType->getAllowUpload()
            && !empty($procedureType->getDownloadsFolder())
        ) {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getDownloadsFolder(), $userId);

            // Save data URL into the target folder (auto-detects extension from MIME)
            $file = $this->nextcloudHelper->saveDataUrl($path, $filename, $data, false);
        }

        return $file;
    }

    /**
     * Uploads a base64-encoded data URL (e.g., image snapshot) to the user's uploads folder.
     *
     * @param Procedure $procedure  The procedure entity associated with the upload.
     * @param string $userId        The identifier of the user performing the upload.
     * @param string $filename      The filename.
     * @param string $data          The data URL to be uploaded (for example, an image encoded in base64).
     * @return string|null          The path or identifier of the saved file, or NULL if uploads are not
     *                              allowed or the uploads folder is not configured.
     */
    public function uploadToSignatureUploadsFolder(Procedure $procedure, string $userId, string $filename, string $data): ?string {
        $file = null;
        $procedureType = $this->getProcedureTypeByProcedureId($procedure->getId());

         if ($procedureType->getAllowUpload()
            && !empty($procedureType->getSignatureUploadsFolder())
        ) {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureUploadsFolder(), $userId);

            // Save data URL into the target folder (auto-detects extension from MIME)
            $file = $this->nextcloudHelper->saveDataUrl($path, $filename, $data, false);
        }

        return $file;
    }

    /**
     * Uploads a base64-encoded data URL (e.g., image snapshot) to the user's downloads folder.
     *
     * @param Procedure $procedure  The procedure entity associated with the upload.
     * @param string $userId        The identifier of the user performing the upload.
     * @param string $filename      The filename.
     * @param string $data          The data URL to be uploaded (for example, an image encoded in base64).
     * @return string|null          The path or identifier of the saved file, or NULL if downloads are not
     *                              allowed or the downloads folder is not configured.
     */
    public function uploadToSignatureDownloadsFolder(Procedure $procedure, string $userId, string $filename, string $data): ?string {
        $file = null;
        $procedureType = $this->getProcedureTypeByProcedureId($procedure->getId());

         if ($procedureType->getAllowUpload()
            && !empty($procedureType->getSignatureDownloadsFolder())
        ) {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureDownloadsFolder(), $userId);

            // Save data URL into the target folder (auto-detects extension from MIME)
            $file = $this->nextcloudHelper->saveDataUrl($path, $filename, $data, false);
        }

        return $file;
    }

    /**
     * Deletes a specific file from the uploads folder.
     *
     * @throws DataNotFoundException
     */
    public function deleteFromUploadsFolder(Procedure $procedure, ?string $userId, string $filename): bool {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowUpload() || empty($procedureType->getUploadsFolder())) {
            throw new DataNotFoundException($this->translate->t('Uploads folder is not available for this procedure.'));
        }

        $relativeFolder = $this->getLocalPathToFolder($procedure, $procedureType->getUploadsFolder(), $userId);
        return $this->deleteFileInsideFolder($relativeFolder, $filename);
    }

    /**
     * Deletes a specific file from the downloads folder.
     *
     * @throws DataNotFoundException
     */
    public function deleteFromDownloadsFolder(Procedure $procedure, ?string $userId, string $filename): bool {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowDownload() || empty($procedureType->getDownloadsFolder())) {
            throw new DataNotFoundException($this->translate->t('Downloads folder is not available for this procedure.'));
        }

        $relativeFolder = $this->getLocalPathToFolder($procedure, $procedureType->getDownloadsFolder(), $userId);
        return $this->deleteFileInsideFolder($relativeFolder, $filename);
    }

    /**
     * Deletes a specific file from the signature uploads folder.
     *
     * @throws DataNotFoundException
     */
    public function deleteFromSignatureUploadsFolder(Procedure $procedure, ?string $userId, string $filename): bool {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowSignature() || empty($procedureType->getSignatureUploadsFolder())) {
            throw new DataNotFoundException($this->translate->t('Signature uploads folder is not available for this procedure.'));
        }

        $relativeFolder = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureUploadsFolder(), $userId);
        return $this->deleteFileInsideFolder($relativeFolder, $filename);
    }

    /**
     * Deletes a specific file from the signature downloads folder.
     *
     * @throws DataNotFoundException
     */
    public function deleteFromSignatureDownloadsFolder(Procedure $procedure, ?string $userId, string $filename): bool {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowSignature() || empty($procedureType->getSignatureDownloadsFolder())) {
            throw new DataNotFoundException($this->translate->t('Signature downloads folder is not available for this procedure.'));
        }

        $relativeFolder = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureDownloadsFolder(), $userId);
        return $this->deleteFileInsideFolder($relativeFolder, $filename);
    }

    //
    // Files listing
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Return first-level files from the configured uploads folder. Returns an
     * empty array if uploads are not allowed or the folder is not configured.
     *
     * @param Procedure $procedure
     *   The procedure whose uploads folder will be inspected.
     * @param string|null $userId
     *   Optional user ID to build the local path; admin user will be used if null.
     *
     * @return array<int,array{name:string,path:string,size:int,mtime:int,mimetype:string}>
     *   A list of file metadata.
     */
    public function getUploadsFolderFiles(Procedure $procedure, string $userId = null): array {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowUpload() || empty($procedureType->getUploadsFolder())) {
            return [];
        }

        try {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getUploadsFolder(), $userId);
        } catch (DataNotFoundException $e) {
            return [];
        }

        return $this->nextcloudHelper->listFilesInFolder($path, true, false);
    }

    /**
     * Return first-level files from the configured downloads folder. Returns an
     * empty array if downloads are not allowed or the folder is not configured.
     *
     * @param Procedure $procedure
     *   The procedure whose downloads folder will be inspected.
     * @param string|null $userId
     *   Optional user ID to build the local path; admin user will be used if null.
     *
     * @return array<int,array{name:string,path:string,size:int,mtime:int,mimetype:string}>
     *   A list of file metadata.
     */
    public function getDownloadsFolderFiles(Procedure $procedure, string $userId = null): array {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowDownload() || empty($procedureType->getDownloadsFolder())) {
            return [];
        }

        try {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getDownloadsFolder(), $userId);
        } catch (DataNotFoundException $e) {
            return [];
        }

        return $this->nextcloudHelper->listFilesInFolder($path, true, false);
    }

    /**
     * Return first-level files from the configured signature uploads folder. Returns an
     * empty array if signature uploads are not allowed or the folder is not configured.
     *
     * @param Procedure $procedure
     *   The procedure whose signature uploads folder will be inspected.
     * @param string|null $userId
     *   Optional user ID to build the local path; admin user will be used if null.
     *
     * @return array<int,array{name:string,path:string,size:int,mtime:int,mimetype:string}>
     *   A list of file metadata.
     */
    public function getSignatureUploadsFolderFiles(Procedure $procedure, string $userId = null): array {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowSignature() || empty($procedureType->getSignatureUploadsFolder())) {
            return [];
        }

        try {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureUploadsFolder(), $userId);
        } catch (DataNotFoundException $e) {
            return [];
        }

        return $this->nextcloudHelper->listFilesInFolder($path, true, false);
    }

    /**
     * Return first-level files from the configured signature downloads folder. Returns an
     * empty array if signature downloads are not allowed or the folder is not configured.
     *
     * @param Procedure $procedure
     *   The procedure whose signature downloads folder will be inspected.
     * @param string|null $userId
     *   Optional user ID to build the local path; admin user will be used if null.
     *
     * @return array<int,array{name:string,path:string,size:int,mtime:int,mimetype:string}>
     *   A list of file metadata.
     */
    public function getSignatureDownloadsFolderFiles(Procedure $procedure, string $userId = null): array {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowSignature() || empty($procedureType->getSignatureDownloadsFolder())) {
            return [];
        }

        try {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureDownloadsFolder(), $userId);
        } catch (DataNotFoundException $e) {
            return [];
        }

        return $this->nextcloudHelper->listFilesInFolder($path, true, false);
    }


    //
    // File content
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get content of a file from the procedure "uploads" folder as data URL.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     * @param string $filename
     * @param string|null $userId
     *
     * @return array{filename:string,mimetype:string,data:string}
     *
     * @throws \OCA\VTramit\Exception\DataNotFoundException
     */
    public function getUploadsFolderFileContent(Procedure $procedure, string $filename, ?string $userId = null): array {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowUpload() || empty($procedureType->getUploadsFolder())) {
            throw new DataNotFoundException($this->translate->t('Uploads folder is not available for this procedure.'));
        }

        $relativeFolder = $this->getLocalPathToFolder($procedure, $procedureType->getUploadsFolder(), $userId);
        return $this->readFileAsDataUrl($relativeFolder, $filename);
    }

    /**
     * Get content of a file from the procedure "downloads" folder as data URL.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     * @param string $filename
     * @param string|null $userId
     *
     * @return array{filename:string,mimetype:string,data:string}
     *
     * @throws \OCA\VTramit\Exception\DataNotFoundException
     */
    public function getDownloadsFolderFileContent(Procedure $procedure, string $filename, ?string $userId = null): array {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowDownload() || empty($procedureType->getDownloadsFolder())) {
            throw new DataNotFoundException($this->translate->t('Downloads folder is not available for this procedure.'));
        }

        $relativeFolder = $this->getLocalPathToFolder($procedure, $procedureType->getDownloadsFolder(), $userId);
        return $this->readFileAsDataUrl($relativeFolder, $filename);
    }

    /**
     * Get content of a file from the "signature uploads" folder as data URL.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     * @param string $filename
     * @param string|null $userId
     *
     * @return array{filename:string,mimetype:string,data:string}
     *
     * @throws \OCA\VTramit\Exception\DataNotFoundException
     */
    public function getSignatureUploadsFolderFileContent(Procedure $procedure, string $filename, ?string $userId = null): array {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowSignature() || empty($procedureType->getSignatureUploadsFolder())) {
            throw new DataNotFoundException($this->translate->t('Signature uploads folder is not available for this procedure.'));
        }

        $relativeFolder = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureUploadsFolder(), $userId);
        return $this->readFileAsDataUrl($relativeFolder, $filename);
    }

    /**
     * Get content of a file from the "signature downloads" folder as data URL.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     * @param string $filename
     * @param string|null $userId
     *
     * @return array{filename:string,mimetype:string,data:string}
     *
     * @throws \OCA\VTramit\Exception\DataNotFoundException
     */
    public function getSignatureDownloadsFolderFileContent(Procedure $procedure, string $filename, ?string $userId = null): array {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowSignature() || empty($procedureType->getSignatureDownloadsFolder())) {
            throw new DataNotFoundException($this->translate->t('Signature downloads folder is not available for this procedure.'));
        }

        $relativeFolder = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureDownloadsFolder(), $userId);
        return $this->readFileAsDataUrl($relativeFolder, $filename);
    }

    /**
     * Read a file via the Nextcloud Files API and return it as a data URL payload.
     *
     * Goes through IRootFolder so the call honours external storage, primary
     * objectstore, encryption and any other backend Nextcloud has configured —
     * matching how listFilesInFolder() walks the same tree. A previous version
     * read with is_file()/file_get_contents() directly on the datadirectory,
     * which silently failed whenever the configured folder lived outside the
     * local FS (Azure Blob / SMB mount / external storage / etc.).
     *
     * @param string $relativeFolder Path relative to the Nextcloud root, like
     *                               "<owner>/files/<group>/<pid>/<external>/<folder>".
     * @param string $filename       File name within the folder (basename'd to strip traversal).
     *
     * @return array{filename:string,mimetype:string,data:string}
     *
     * @throws \OCA\VTramit\Exception\DataNotFoundException
     */
    protected function readFileAsDataUrl(string $relativeFolder, string $filename): array {
        $safeName    = basename($filename);
        $relativePath = rtrim($relativeFolder, '/') . '/' . $safeName;

        if ($safeName === '' || !$this->rootFolder->nodeExists($relativePath)) {
            throw new DataNotFoundException('File not found: ' . $relativePath);
        }

        $node = $this->rootFolder->get($relativePath);
        if (!($node instanceof \OCP\Files\File)) {
            throw new DataNotFoundException('Not a file: ' . $relativePath);
        }

        $content = $node->getContent();
        if ($content === false) {
            throw new DataNotFoundException('Unable to read file: ' . $relativePath);
        }

        $mime = $node->getMimeType() ?: 'application/octet-stream';

        return [
            'filename' => $node->getName(),
            'mimetype' => $mime,
            'data'     => 'data:' . $mime . ';base64,' . base64_encode($content),
        ];
    }

    /**
     * Deletes a file located inside the provided relative folder.
     *
     * @throws DataNotFoundException
     */
    protected function deleteFileInsideFolder(string $relativeFolder, string $filename): bool {
        $safeName = trim((string) $filename);
        if ($safeName === '') {
            throw new DataNotFoundException($this->translate->t('Filename cannot be empty.'));
        }

        $relativePath = $relativeFolder . Constants::DIRECTORY_SEPARATOR . basename($safeName);

        if (!$this->nextcloudHelper->fileExists($relativePath)) {
            throw new DataNotFoundException($this->translate->t('The requested file does not exist.'));
        }

        $this->nextcloudHelper->deletePath($relativePath);

        return true;
    }


    //
    // File Paths
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Create the procedure folders.
     *
     * @param Procedure $procedure  the procedure for which structure must be created
     * @param string    $userId     identifier of user executing the operation
     * @return Procedure            procedure with folder fields updated
     * @throws DataNotFoundException
     */
    public function createFolders(Procedure $procedure, string $userId, bool $force = false): Procedure {
        // Example: my-admin/{$departament}/{$citizen_id}/{$appointment_id}/Entrada
        //          my-admin/{$departament}/{$citizen_id}/{$appointment_id}/Sortida

        $procedureType = $this->getProcedureType($procedure);
        $updated = false;

        // Create upload folder
        if ($procedureType->getAllowUpload()
            && !empty($procedureType->getUploadsFolder())
        ) {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getUploadsFolder(), $userId);
            if (empty($procedure->getStaffUploadsUrl()) || !$this->nextcloudHelper->folderExists($path) || $force) {
                $this->nextcloudHelper->createPath($path);

                $webPath = $this->getWebPathToFolder($procedure, $procedureType->getUploadsFolder());
                $url = $this->urlGenerator->getAbsoluteURL($webPath);
                $procedure->setStaffUploadsUrl($url);
                $updated = true;
            }
        }


        // Create download folder
        if ($procedureType->getAllowDownload()
            && !empty($procedureType->getDownloadsFolder())
        ) {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getDownloadsFolder(), $userId);
            if (empty($procedure->getStaffDownloadsUrl()) || !$this->nextcloudHelper->folderExists($path) || $force) {
                $this->nextcloudHelper->createPath($path);

                $webPath = $this->getWebPathToFolder($procedure, $procedureType->getDownloadsFolder());
                $url = $this->urlGenerator->getAbsoluteURL($webPath);
                $procedure->setStaffDownloadsUrl($url);
                $updated = true;
            }
        }

        // Create signature folder
        // TODO: review signature dependency
        if ($procedureType->getAllowSignature()
            && !empty($procedureType->getSignatureUploadsFolder())
        ) {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureUploadsFolder(), $userId);
            if (empty($procedure->getStaffSignatureUploadsUrl()) || !$this->nextcloudHelper->folderExists($path) || $force) {
                $this->nextcloudHelper->createPath($path);

                $webPath = $this->getWebPathToFolder($procedure, $procedureType->getSignatureUploadsFolder());
                $url = $this->urlGenerator->getAbsoluteURL($webPath);
                $procedure->setStaffSignatureUploadsUrl($url);
                $updated = true;
            }
        }

        if ($procedureType->getAllowSignature()
            && !empty($procedureType->getSignatureDownloadsFolder())
        ) {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getSignatureDownloadsFolder(), $userId);
            if (empty($procedure->getStaffSignatureDownloadsUrl()) || !$this->nextcloudHelper->folderExists($path) || $force) {
                $this->nextcloudHelper->createPath($path);

                $webPath = $this->getWebPathToFolder($procedure, $procedureType->getSignatureDownloadsFolder());
                $url = $this->urlGenerator->getAbsoluteURL($webPath);
                $procedure->setStaffSignatureDownloadsUrl($url);
                $updated = true;
            }
        }

        if ($updated) {
            $this->procedureMapper->update($procedure);
        }

        return $procedure;
    }

    /**
     * Obtain the local path for the given procedure based on the main stakeholder.
     *
     * @param Procedure $procedure  the entity for which obtain the local path
     * @param string $userId        identifier of user executing the operation
     * @return string               the local path
     * @throws DataNotFoundException
     */
    public function getLocalPathToProcedure(Procedure $procedure, string $userId = null) {
        $procedureType = $this->getProcedureType($procedure);

        $assignedGroupId = $procedure->getAssignedGroupId();
        if (empty($assignedGroupId)) {
            if ($procedureType->getRequireAssignedGroup()) {
                throw new DataNotFoundException($this->translate->t('You have to assign a group to create the procedure folders'));
            }

            $assignedGroupId = 'GLOBAL';
        }

        $user = empty($userId) ? $this->configService->getAdminUser() : $userId;
        if (empty($user)) {
            throw new DataNotFoundException($this->translate->t('The path must be owned by a user and none given'));
        }

        $externalId = $procedure->getExternalId();
        if (empty($externalId)) {
            if ($procedureType->getRequireExternalId()) {
                throw new DataNotFoundException($this->translate->t('The procedure requires an external ID to create the path'));
            }

            $externalId = 'PROC#'.$procedure->getId();
        }

        $procedureStakeholder = $this->getMainStakeholder($procedure);
        if (empty($procedureStakeholder)) {
            if ($procedureType->getRequireStakeholderPid()) {
                throw new DataNotFoundException($this->translate->t('There is no stakeholder'));
            }
        }

        $personalId = empty($procedureStakeholder) ? '' : $procedureStakeholder->getPersonalId();
        if (empty($personalId)) {
            if ($procedureType->getRequireStakeholderPid()) {
                throw new DataNotFoundException($this->translate->t('The procedure requires a Personal ID for the main stakeholder to create the path'));
            }

            // Set 'Virtual Personal ID'
            $personalId = 'VPID#'. (empty($procedureStakeholder) ? $procedure->getId() : $procedureStakeholder->getId());
        }

        $group = empty($assignedGroupId) ? '' : Constants::DIRECTORY_SEPARATOR.$assignedGroupId;
        $personalId = empty($personalId) ? '' : Constants::DIRECTORY_SEPARATOR.$personalId;
        $externalId = empty($externalId) ? '' : Constants::DIRECTORY_SEPARATOR.$externalId;

        return $user
            .Constants::DIRECTORY_SEPARATOR.'files'
            .$group
            .$personalId
            .$externalId;
    }

    /**
     * Obtain the local path to the folder for the given procedure.
     *
     * @param Procedure $procedure  the entity for which obtain the local path
     * @param string    $folder     the name of folder in the procedure local path
     * @param string    $userId     identifier of user executing the operation
     * @return string               the local path folder
     * @throws DataNotFoundException
     */
    public function getLocalPathToFolder(Procedure $procedure, string $folder, string $userId = null) {
        return $this->getLocalPathToProcedure($procedure, $userId)
            .Constants::DIRECTORY_SEPARATOR.$folder;
    }

    /**
     * Obtain the local path to the Readme.md file for the given procedure.
     *
     * @param Procedure $procedure  the entity for which obtain the local path
     * @param string    $userId     identifier of user executing the operation
     * @return string               the local path to the Readme.md file
     * @throws DataNotFoundException
     */
    public function getLocalPathToReadme(Procedure $procedure, string $userId = null) {
        return $this->getLocalPathToProcedure($procedure, $userId)
            .Constants::DIRECTORY_SEPARATOR.'Readme.md';
    }

    /**
     * Obtain the relative path folder for the given procedure.
     *
     * Example: '/var/www/data/my-admin/files/my-deparment/{personal_id}/{external_id}/{folder}'
     *
     * @param Procedure $procedure  the entity for which obtain the local path
     * @param string    $folder     the name of folder in the procedure local path
     * @param string    $userId     identifier of user executing the operation
     * @return string               the local path folder
     * @throws DataNotFoundException
     */
    public function getUserRelativePathToFolder(Procedure $procedure, string $folder) {

        // TODO: Què passa si el tràmit no té stakeholders o externalId??

        if (empty($procedure->getAssignedGroupId())) {
            throw new DataNotFoundException($this->translate->t('You have to assign a group to create the procedure folders'));
        }

        if (empty($procedure->getExternalId())) {
            throw new DataNotFoundException($this->translate->t('The procedure requires an external ID to create the path'));
        }

        $procedureStakeholder = $this->getMainStakeholder($procedure);
        if (empty($procedureStakeholder)) {
            throw new DataNotFoundException($this->translate->t('There is no stakeholder'));
        }

        $group = Constants::DIRECTORY_SEPARATOR.$procedure->getAssignedGroupId();
        $personalId = empty($procedureStakeholder) || empty($procedureStakeholder->getPersonalId()) ? '' : Constants::DIRECTORY_SEPARATOR.$procedureStakeholder->getPersonalId();
        $externalId = empty($procedure) || empty($procedure->getExternalId()) ? '' : Constants::DIRECTORY_SEPARATOR.$procedure->getExternalId();

        // The folder to share must not include the username
        return $group
            .$personalId
            .$externalId
            .Constants::DIRECTORY_SEPARATOR.$folder;
    }

    /**
     * Obtain the webpath to the site root.
     *
     * @return string               the webpath to the site root page
     */
    private function getWebPathToRoot(): string {
        $frontControllerActive = ($this->config->getSystemValueBool('htaccess.IgnoreFrontController', false) || getenv('front_controller_active') === 'true');
        return $frontControllerActive ? '/' : '/index.php/';
    }

    /**
     * Obtain the webpath to the vTramit application root.
     *
     * @return string               the webpath to the application root page
     */
    public function getWebPathToVtramit(): string {
        return $this->getWebPathToRoot() . 'apps/vtramit/';
    }

    /**
     * Obtain the webpath for the given procedure based on the main stakeholder.
     *
     * @param Procedure $procedure  the entity for which obtain the webpath
     * @param string $userId        identifier of user executing the operation
     * @return string               the webpath
     * @throws DataNotFoundException
     */
    private function getWebPathToProcedure(Procedure $procedure) {
        // TODO: Què passa si el tràmit no té stakeholders o externalId??

        if (empty($procedure->getAssignedGroupId())) {
            throw new DataNotFoundException($this->translate->t('You have to assign a group to get access to the procedure folders'));
        }

        if (empty($procedure->getExternalId())) {
            throw new DataNotFoundException($this->translate->t('The procedure requires an external ID to have access to the path'));
        }

        $procedureStakeholder = $this->getMainStakeholder($procedure);
        if (empty($procedureStakeholder)) {
            throw new DataNotFoundException($this->translate->t('There is no stakeholder'));
        }

        $group = Constants::DIRECTORY_SEPARATOR.$procedure->getAssignedGroupId();
        $personalId = empty($procedureStakeholder) || empty($procedureStakeholder->getPersonalId()) ? '' : Constants::DIRECTORY_SEPARATOR.$procedureStakeholder->getPersonalId();
        $externalId = empty($procedure) || empty($procedure->getExternalId()) ? '' : Constants::DIRECTORY_SEPARATOR.$procedure->getExternalId();

        return $this->getWebPathToRoot() . 'apps/files/?dir='
            .$group
            .$personalId
            .$externalId;
    }

    /**
     * Obtain the webpath to the folder for the given procedure.
     *
     * @param Procedure $procedure  the entity for which obtain the webpath
     * @param string    $folder     the name of folder in the procedure webpath
     * @return string               the webpath folder
     * @throws DataNotFoundException
     */
    public function getWebPathToFolder(Procedure $procedure, string $subfolder) {
        return $this->getWebPathToProcedure($procedure)
            .Constants::DIRECTORY_SEPARATOR.$subfolder;
    }

    /**
     * TODO: Determinar si esta función la mantenemos o la eliminamos.
     *
     * Obtain the webpath to the Readme.md file for the given procedure.
     *
     * @param Procedure $procedure  the entity for which obtain the webpath
     * @return string               the webpath to the Readme.md file
     * @throws DataNotFoundException
     */
    public function getWebPathToReadme(Procedure $procedure): string {
        return $this->getWebPathToProcedure($procedure)
            .Constants::DIRECTORY_SEPARATOR.'Readme.md';
    }

    /**
     * Obtain the webpath to the public page file for the given procedure.
     *
     * @param Procedure $procedure  the entity for which obtain the webpath
     * @return string               the webpath to the public page
     * @throws DataNotFoundException
     */
    public function getWebPathToPublicPage(Procedure $procedure): string {
        return $this->getWebPathToVtramit() . 'public/' . $procedure->getJitsiRoomCode();
    }


    /**
     * Obtain the WebDAV path for the given procedure based on the main stakeholder.
     *
     * @param Procedure $procedure  the entity for which obtain the WebDAV path
     * @param string $userId        identifier of user executing the operation
     * @return string               the WebDAV path
     * @throws DataNotFoundException
     */
    private function getWebDAVToProcedure(Procedure $procedure, $userId = null) {
        // TODO: Què passa si el tràmit no té stakeholders o externalId??

        if (empty($procedure->getAssignedGroupId())) {
            throw new DataNotFoundException($this->translate->t('You have to assign a group to get access to the procedure folders'));
        }

        if (empty($procedure->getExternalId())) {
            throw new DataNotFoundException($this->translate->t('The procedure requires an external ID to have access to the path'));
        }

        $procedureStakeholder = $this->getMainStakeholder($procedure);
        if (empty($procedureStakeholder)) {
            throw new DataNotFoundException($this->translate->t('There is no stakeholder'));
        }

        $group = Constants::DIRECTORY_SEPARATOR.$procedure->getAssignedGroupId();
        $personalId = empty($procedureStakeholder) || empty($procedureStakeholder->getPersonalId()) ? '' : Constants::DIRECTORY_SEPARATOR.$procedureStakeholder->getPersonalId();
        $externalId = empty($procedure) || empty($procedure->getExternalId()) ? '' : Constants::DIRECTORY_SEPARATOR.$procedure->getExternalId();

        return $group
            .$personalId
            .$externalId;
    }

    /**
     * Obtain the WebDAV path to the folder for the given procedure.
     *
     * @param Procedure $procedure  the entity for which obtain the WebDAV path
     * @param string    $folder     the name of folder in the procedure WebDAV path
     * @return string               the WebDAV path folder
     * @throws DataNotFoundException
     */
    public function getWebDAVToFolder(Procedure $procedure, string $subfolder, $userId = null) {
        return $this->getWebDAVToProcedure($procedure, $userId)
            .Constants::DIRECTORY_SEPARATOR.$subfolder;
    }

    /**
     * TODO: Determinar si esta función la mantenemos o la eliminamos.
     *
     * Obtain the WebDAV path to the Readme.md file for the given procedure.
     *
     * @param Procedure $procedure  the entity for which obtain the WebDAV path
     * @return string               the WebDAV path to the Readme.md file
     * @throws DataNotFoundException
     */
    /*public function getWebDAVToReadme(Procedure $procedure, $userId = null) {
        return $this->getWebDAVToProcedure($procedure, $userId)
            .Constants::DIRECTORY_SEPARATOR.'Readme.md';
    }*/


    //
    // Files counters
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Count first-level files (excluding folders and hidden files) in the uploads folder.
     *
     * @param \OCA\VTramit\Db\Procedure $procedure
     *   The procedure whose uploads folder will be inspected.
     * @param string|null $userId
     *   Optional user ID to build the local path; admin user will be used if null.
     *
     * @return int
     *   Number of files in the first level of the uploads folder. Returns 0 if the
     *   folder is not defined, uploads are not allowed, path cannot be resolved,
     *   or the folder does not exist.
     */
    public function getUploadsFilesCount(Procedure $procedure, string $userId = null): int {
        $procedureType = $this->getProcedureType($procedure);

        if (!$procedureType->getAllowUpload() || empty($procedureType->getUploadsFolder())) {
            return 0;
        }

        try {
            $path = $this->getLocalPathToFolder($procedure, $procedureType->getUploadsFolder(), $userId);
        } catch (DataNotFoundException $e) {
            return 0;
        }

        return $this->nextcloudHelper->countFilesInFolderFirstLevel($path, true);
    }


    //
    // Readme
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the content of the Readme file for given procedure.
     *
     * @param Procedure $procedure             the procedure for which readme must be created
     * @param Procedure $procedureStakeholder  the procedure stakeholder for which readme must be created
     * @return string                          the Readme file content
     * @throws DataNotFoundException
     */
    public function generateReadmeContent(Procedure $procedure, ProcedureStakeholder $procedureStakeholder): string {
        $content = '##';
        $procedureType = $this->getProcedureType($procedure);

        // Link to Jitsi
        if (!empty($procedure->getJitsiRoomStaffUrl())) {
            $content = ' ['.$this->translate->t('Videconference').'](' . $procedure->getJitsiRoomStaffUrl() . Constants::NEW_LINE;
        }

        // Stakeholder phone
        if (!empty($procedureStakeholder) && !empty($procedureStakeholder->getPhone())) {
            if (!empty($this->configService->getPhoneLink())) {
                $content .= ' ['. $procedureStakeholder->getPhone() . ']'
                    . '(' . $this->configService->getPhoneLink() . ':' . $procedureStakeholder->getPhone() . ')'
                    . Constants::NEW_LINE;
            }
            else {
                $content .= ' ['.$procedureStakeholder->getPhone().']' . Constants::NEW_LINE;
            }
        }

        // Stakeholder email
        if (!empty($procedureStakeholder) && !empty($procedureStakeholder->getEmail())) {
            $content .= ' [' . $procedureStakeholder->getEmail() . ']'
                . '(mailto:'.$procedureStakeholder->getEmail() . ')'
                . Constants::NEW_LINE;
        }

        // Link to form
        $form = $this->procedureVideoconferenceService->getVideoconferenceConfirmationForm($procedure, $procedureStakeholder);
        if (!empty($form)) {
            $content .= ' [' . $this->translate->t('Procedure form') . ']'
                . '(' . $form . ')'
                . Constants::NEW_LINE . Constants::NEW_LINE;
        }

        // Stakeholder name and personal ID
        // {name} - {personalId}
        if (!empty($procedureStakeholder)) {
            $content .= ' ' . $procedureStakeholder->getName();
            if (!empty($procedureStakeholder->getPersonalId())) {
                $content .= ' - ' . $procedureStakeholder->getPersonalId();
            }
            $content .= Constants::NEW_LINE . Constants::NEW_LINE;
        }

        // Appointment date
        if (!empty($procedure->getAppointmentDate())) {
            // TODO: permetre definir el format a nivell de tipus de procediment
            $content .= ' ' . $this->translate->t('Date') . ': '
                . $procedure->getFormattedAppointmentDate($this->configService->getDateTimezone(), $this->configService->getDatetimeFormat())
                . Constants::NEW_LINE . Constants::NEW_LINE;
        }

        $content .= "### " . Constants::NEW_LINE;

        return $content;
    }
}
