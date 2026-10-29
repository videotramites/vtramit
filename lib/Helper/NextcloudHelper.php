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

namespace OCA\VTramit\Helper;

use DateTime;

use OCP\Constants;
use Psr\Log\LoggerInterface;
use OCP\IL10N;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

use OCP\Files\IRootFolder;
use OCA\Files_Sharing\Controller\ShareAPIController;
use OCP\IURLGenerator;
use OCP\Share\IShare;

use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCA\VTramit\Service\ConfigService;

class NextcloudHelper {

    /** @var LoggerInterface */
    private $logger;

    /** @var IL10N */
    private $translate;

    /** @var IDBConnection */
    private $db;

    /** @var IRootFolder */
    private $rootFolder;

    /** @var ShareAPIController */
    private $shareManager;

    /** @var ConfigService */
    private $configService;


    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        IDBConnection $db,
        IRootFolder $rootFolder,
        ShareAPIController $shareManager,
        ConfigService $configService
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->db = $db;
        $this->rootFolder = $rootFolder;
        $this->shareManager = $shareManager;
        $this->configService = $configService;
    }

    public function folderExists($path) {
        return $this->rootFolder->nodeExists($path);
    }

    public function fileExists($path) {
        return $this->rootFolder->nodeExists($path);
    }

    public function createPath(string $path) {
        $path = trim($path, ConfigService::DIRECTORY_SEPARATOR);
        $folder = $this->rootFolder;

        //$this->logger->critical($path, ['app' => 'vtramit']);

        if ($folder->nodeExists($path)) {
            $folder = $folder->get($path);
            if ($folder instanceof \OCP\Files\Folder) {
                return $folder;
            }

            throw new \RuntimeException("Path '$path' exists but is not a folder");
        }

        $parts = explode(ConfigService::DIRECTORY_SEPARATOR, $path);
        foreach($parts as $part) {
            if (!empty($part)) {

                if (!$folder->nodeExists($part)) {
                    try {
                        $folder = $folder->newFolder($part);
                    } catch (\OCP\Files\NotFoundException $e) {
                        // Can be raised by listeners, so we check it again
                        if (!$folder->nodeExists($part)) {
                            throw $e;
                        }
                    }
                }
                else {
                    $folder = $folder->get($part);
                    if (empty($folder)) {
                        break;
                    }
                }
            }
        }

        return $folder;
    }

    public function shareFolder($path, $password, $allowUploads, $allowDownloads) {
        return $this->shareItem($path, $password, $allowUploads, $allowDownloads);
    }

    public function shareFile($path, $password) {
        return $this->shareItem($path, $password, false, true);
    }

    private function shareItem($path, $password, $allowUploads, $allowDownloads) {
        // Documentation on
        // https://docs.nextcloud.com/server/15/developer_manual/core/ocs-share-api.html

        // POST Arguments: path - (string) path to the file/folder which should be shared
        // POST Arguments: shareType - (int) 0 = user; 1 = group; 3 = public link; 6 = federated cloud share
        // POST Arguments: shareWith - (string) user / group id with which the file should be shared
        // POST Arguments: publicUpload - (string) allow public upload to a public shared folder (true/false)
        // POST Arguments: password - (string) password to protect public link Share with
        // POST Arguments: permissions - (int) 1 = read; 2 = update; 4 = create; 8 = delete; 16 = share; 31 = all (default: 31, for public shares: 1)

        if (!$allowUploads && !$allowDownloads) {
            return '';
        }

        // For TYPE_LINK, the permissions are ignored and are set using the $publicUpload
        // parameter, in the following way:
        //
        //   - true : Constants::PERMISSION_READ | Constants::PERMISSION_CREATE | Constants::PERMISSION_UPDATE | Constants::PERMISSION_DELETE
        //   - false: Constants::PERMISSION_READ
        //
        // $allowUploads | $allowDownload | $publicPage | method
        // ------------- | -------------- | ----------- | --------------------
        // true          | true           | true        | $publicPage variable
        // false         | true           | false       | $publicPage variable
        // true          | false          | false       | update database

        $response = $this->shareManager->createShare(
            $path,
            $allowUploads ? Constants::PERMISSION_CREATE : Constants::PERMISSION_READ,
            IShare::TYPE_LINK,
            null,
            $allowUploads && $allowDownloads ? 'true' : 'false',
            $password
        );

        // Update database
        if ($allowUploads && !$allowDownloads) {
            $qb = $this->db->getQueryBuilder();
            $qb->update('share')
                ->set('permissions', $qb->createNamedParameter(4, IQueryBuilder::PARAM_INT))
                ->where($qb->expr()->eq('id', $qb->createNamedParameter($response->getData()['id'], IQueryBuilder::PARAM_INT)));

            $qb->executeStatement();

        }

        if (!empty($response) && !empty($response->getData()['url'])) {
            return $response->getData()['url'];
        }

        return '';
    }

    public function deletePath($path) {
        if (empty($path)) {
            return;
        }

        try {
            if (is_array($path)) {
                $root = $this->configService->getAdminRoot();
                $root_length = strlen($root);

                $trashbin = $this->configService->getTrashRoot();
                $trashbin_length = strlen($trashbin);
                foreach($path as $item) {
                    $real_path = $item['path'];
                    if (substr($real_path, 0, $trashbin_length) != $trashbin) {
                        $this->deletePath($real_path);
                    }
                }
            }
            else {
                //$path = 'my-admin/files/my-department/11223344';
                if ($this->rootFolder->nodeExists($path)) {
                    $folder = $this->rootFolder->get($path);
                    $parent = $folder->getParent();
                    $folder->delete();

                    // If parent is empty, remove it also
                    if (!empty($parent)) {
                        $children = $parent->getDirectoryListing();
                        if (empty($children)) {
                            $parent->delete();
                        }
                    }
                }

                // Check if adding 'admin' user it exists
                else if ($this->rootFolder->nodeExists($this->configService->getAdminUser().ConfigService::DIRECTORY_SEPARATOR.$path)) {
                    $this->deletePath($this->configService->getAdminUser().ConfigService::DIRECTORY_SEPARATOR.$path);
                }
            }
        }
        catch (\Exception $e) {
            $this->logger->error('Error trying to delete the following path: ' . $path);
            throw $e;
        }
        catch (\Throwable $e) {
            $this->logger->error('Error trying to delete the following path: ' . $path);
            throw $e;
        }
    }

    /**
     * Count first-level files in a given folder path.
     *
     * Hidden files (starting with ".") are excluded when $excludeHidden is true.
     * Only files are counted; folders are ignored. If the path does not exist
     * or is not a folder, 0 is returned.
     *
     * @param string $path
     *   Absolute path from the Nextcloud root folder.
     * @param bool $excludeHidden
     *   Whether to exclude hidden files (default: true).
     *
     * @return int
     *   Number of files in the first level of the folder.
     */
    public function countFilesInFolderFirstLevel(string $path, bool $excludeHidden = true): int {
        if (!$this->rootFolder->nodeExists($path)) {
            return 0;
        }

        $node = $this->rootFolder->get($path);
        if (!($node instanceof \OCP\Files\Folder)) {
            return 0;
        }

        $count = 0;
        foreach ($node->getDirectoryListing() as $child) {
            if ($child instanceof \OCP\Files\File) {
                $name = $child->getName();
                if ($excludeHidden && isset($name[0]) && $name[0] === '.') {
                    continue;
                }
                $count++;
            }
        }

        return $count;
    }

    /**
     * Obtains the major version of the current Nextcloud installation.
     */
    public static function getMajorVersion() {
        $version = \OCP\Util::getVersion();
        return reset($version);
    }

    /**
     * Indicates if current version is equals or higher than given one.
     *
     * @param int   $version  reference version to check
     * @return bool           true if current version equals or higher than $version; otherwise, false
     */
    public static function isVersionOrHigher($version) {
        $version = \OCP\Util::getVersion();
        return reset($version) >= $version;
    }

    /**
     * List files for a given folder path.
     *
     * Hidden files (starting with ".") are excluded when $excludeHidden is true.
     * Only files are returned; folders are ignored. If $recursive is true, files
     * from subfolders are included as well. If the path does not exist or is not
     * a folder, an empty array is returned.
     *
     * @param string $path
     *   Absolute path from the Nextcloud root folder.
     * @param bool $excludeHidden
     *   Whether to exclude hidden files (default: true).
     * @param bool $recursive
     *   Whether to include files from subfolders (default: false).
     *
     * @return array<int,array{
     *   name:string,
     *   path:string,
     *   size:int,
     *   mtime:int,
     *   mimetype:string
     * }>
     *   A list of file metadata.
     */
    public function listFilesInFolder(string $path, bool $excludeHidden = true, bool $recursive = false): array {
        if (!$this->rootFolder->nodeExists($path)) {
            return [];
        }

        $node = $this->rootFolder->get($path);
        if (!($node instanceof \OCP\Files\Folder)) {
            return [];
        }

        $result = [];

        // ------------------------------------------------------------------------
        // Walk a folder and collect file metadata (optionally recursive)
        $walk = function (\OCP\Files\Folder $folder, string $currentPath) use (&$walk, &$result, $excludeHidden, $recursive) {
            foreach ($folder->getDirectoryListing() as $child) {
                $name = $child->getName();
                if ($excludeHidden && isset($name[0]) && $name[0] === '.') {
                    continue;
                }

                $childPath = rtrim($currentPath, '/') . '/' . $name;

                if ($child instanceof \OCP\Files\File) {
                    $result[] = [
                        'id'       => (int) $child->getId(),
                        'name'     => $name,
                        'path'     => $childPath,
                        'size'     => (int) $child->getSize(),
                        'mtime'    => (int) $child->getMTime(),
                        'mimetype' => (string) $child->getMimeType(),
                    ];
                } elseif ($recursive && $child instanceof \OCP\Files\Folder) {
                    $walk($child, $childPath);
                }
            }
        };

        $walk($node, rtrim($path, '/'));

        return $result;
    }

    /**
     * Save a data URL (data:<mime>;base64,...) into a folder path.
     *
     * The extension is inferred from the MIME type (fallback: "bin").
     * If $baseName already includes an extension, it is preserved and not duplicated.
     * If $overwrite is false and the file exists, a numeric suffix is appended.
     *
     * @param string $folderPath Absolute folder path from Nextcloud root (e.g. "user/files/Group/123/Entrada")
     * @param string $baseName   File base name (with or without extension, e.g. "snapshot-20250101-120000" or "image.png")
     * @param string $dataUrl    Data URL content (e.g. "data:image/png;base64,AAAA...")
     * @param bool   $overwrite  Overwrite if the file already exists
     *
     * @return string Full path of the written file (folder + "/" + filename)
     *
     * @throws \InvalidArgumentException When the data URL is invalid
     */
    public function saveDataUrl(string $folderPath, string $baseName, string $dataUrl, bool $overwrite = false): string {
        // Parse data URL
        if (!preg_match('#^data:([^;]+);base64,(.*)$#s', $dataUrl, $m)) {
            throw new \InvalidArgumentException('Invalid data URL format');
        }

        $mime = $m[1] ?? 'application/octet-stream';
        $b64  = $m[2] ?? '';

        // Binary content
        $binary = base64_decode($b64, true);
        if ($binary === false) {
            throw new \InvalidArgumentException('Invalid base64 payload in data URL');
        }

        // Determine target filename without duplicating extension
        $calculatedExt = strtolower($this->extensionFromMime($mime));
        $baseExt       = strtolower((string) pathinfo($baseName, PATHINFO_EXTENSION));

        // If $baseName already has an extension, keep it as-is (avoid ".ext.ext").
        // Otherwise, append the calculated one.
        $filename = $baseExt !== '' ? $baseName : ($baseName . '.' . $calculatedExt);

        return $this->writeFile($folderPath, $filename, $binary, $overwrite);
    }

    /**
     * Write a binary file into a folder path, creating the folder if needed.
     *
     * @param string $folderPath Absolute folder path from Nextcloud root
     * @param string $filename   Target file name
     * @param string $binary     Binary contents
     * @param bool   $overwrite  Overwrite if the file exists
     *
     * @return string Full path of the written file
     */
    public function writeFile(string $folderPath, string $filename, string $binary, bool $overwrite = false): string {
        // Ensure folder exists
        $folder = $this->createPath($folderPath);
        if (!($folder instanceof \OCP\Files\Folder)) {
            throw new \RuntimeException("Target path is not a folder: $folderPath");
        }

        // Resolve collision
        $targetName = $filename;
        if ($folder->nodeExists($targetName)) {
            if ($overwrite) {
                $file = $folder->get($targetName);
                if (!($file instanceof \OCP\Files\File)) {
                    throw new \RuntimeException("A non-file node already exists with the same name: $targetName");
                }
                $file->putContent($binary);
                return rtrim($folderPath, '/') . '/' . $targetName;
            }

            // Append numeric suffix
            $i = 1;
            $dot = strrpos($filename, '.');
            $name = $dot === false ? $filename : substr($filename, 0, $dot);
            $ext  = $dot === false ? '' : substr($filename, $dot); // keep ".ext"

            do {
                $targetName = $name . '-' . $i . $ext;
                $i++;
            } while ($folder->nodeExists($targetName));
        }

        // Create and write
        $newFile = $folder->newFile($targetName);
        $newFile->putContent($binary);

        return rtrim($folderPath, '/') . '/' . $targetName;
    }

    /**
     * Infer a reasonable file extension from a MIME type.
     *
     * @param string $mime
     * @return string
     */
    private function extensionFromMime(string $mime): string {
        $map = [
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
        ];

        if (isset($map[$mime])) {
            return $map[$mime];
        }

        // Fallback: take subtype if looks sane, else "bin"
        if (strpos($mime, '/') !== false) {
            $sub = substr(strrchr($mime, '/'), 1);
            if ($sub !== false && $sub !== '') {
                // Avoid "+xml" like endings
                $sub = str_replace('+xml', '', $sub);
                return preg_replace('/[^a-z0-9_-]/i', '', $sub) ?: 'bin';
            }
        }

        return 'bin';
    }
}
