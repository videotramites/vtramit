<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace OCA\VTramit\Service;

use OCP\IConfig;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\Activity\IProvider;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;

use OCP\Activity\Exceptions\UnknownActivityException;
use OCA\VTramit\Enum\ActivityType;
use OCA\VTramit\Enum\MessageChannel;
use OCA\VTramit\ProcedureStatus;

class ActivityProvider implements IProvider {
    public const PROCEDURE_CREATED = 'procedure_created';
    public const PROCEDURE_CHANGED = 'procedure_changed';
    public const PROCEDURE_DELETED = 'procedure_deleted';

    public const PROCEDURE_STAKEHOLDER_CREATED = 'procedure_stakeholder_created';
    public const PROCEDURE_STAKEHOLDER_CHANGED = 'procedure_stakeholder_changed';
    public const PROCEDURE_STAKEHOLDER_DELETED = 'procedure_stakeholder_deleted';

    public const SIGNATURE_CREATED = 'signature_created';
    public const SIGNATURE_CHANGED = 'signature_changed';
    public const SIGNATURE_DELETED = 'signature_deleted';

    public const MESSAGE_QUEUE_CREATED = 'message_queue_created';
    public const MESSAGE_QUEUE_CHANGED = 'message_queue_changed';
    public const MESSAGE_QUEUE_DELETED = 'message_queue_deleted';

    /** @var IL10N */
    protected $l;

    public function __construct(
        protected IConfig $config,
        protected IFactory $languageFactory,
        protected IURLGenerator $url,
        protected IManager $activityManager
    ) {}

    /**
     * @param string $language
     * @param IEvent $event
     * @param IEvent|null $previousEvent
     * @return IEvent
     * @throws \InvalidArgumentException
     * @since 11.0.0
     */
    public function parse($language, IEvent $event, IEvent $previousEvent = null): IEvent {
        if ($event->getApp() !== 'vtramit') {
            throw new UnknownActivityException();
        }

        $this->l = $this->languageFactory->get('vtramit', $language);

        $this->parseIcon($event);
        $this->parseSubject($event);

        return $event;
    }

    /**
     * Selects the icon for an activity based on the subject suffix.
     *
     * @param IEvent $event Activity event to decorate.
     * @return void
     */
    protected function parseIcon(IEvent $event): void {
        $action = $this->resolveActionFromSubjectKey($event->getSubject());
        $icon = 'change';

        if ($event->getSubject() === self::MESSAGE_QUEUE_CREATED) {
            $icon = 'remote-command';
        }
        elseif ($action === ActivityType::CREATED->value) {
            $icon = 'add-color';
        }
        elseif ($action === ActivityType::CHANGED->value) {
            $icon = 'change';
        }
        elseif ($action === ActivityType::DELETED->value) {
            $icon = 'delete-color';
        }

        $this->setIcon($event, $icon);
    }

    /**
     * Sets the activity icon, honoring PNG/SVG requirements.
     *
     * @param IEvent $event Activity event to decorate.
     * @param string $icon Icon name without extension.
     * @param string $app App from which the icon is loaded.
     * @return void
     */
    protected function setIcon(IEvent $event, string $icon, string $app = 'vtramit'): void {
        if ($this->activityManager->getRequirePNG()) {
            $event->setIcon($this->url->getAbsoluteURL($this->url->imagePath($app, $icon . '.png')));
        } else {
            $event->setIcon($this->url->getAbsoluteURL($this->url->imagePath($app, $icon . '.svg')));
        }
    }

    /**
     * Builds and assigns the rich subject and parameters for the event.
     *
     * @param IEvent $event Activity event to decorate.
     * @return void
     */
    protected function parseSubject(IEvent $event): void {
        $parameters = $this->getParameters($event);
        $subject = $this->getSubject($event);

        $event->setRichSubject($subject, $parameters);
    }

    /**
     * Returns the translated subject template for the given event.
     *
     * @param IEvent $event Activity event to parse.
     * @return string
     * @throws UnknownActivityException
     */
    protected function getSubject(IEvent $event): string {
        $subjectKey = $event->getSubject();
        $parameters = $event->getSubjectParameters();

        if ($subjectKey === self::PROCEDURE_CREATED) {
            return $this->l->t('Procedure {procedure} created');
        } elseif ($subjectKey === self::PROCEDURE_CHANGED) {
            if ($this->hasProcedureStatusChange($parameters)) {
                return $this->l->t('Procedure {procedure} status changed from {statusFrom} to {statusTo}');
            }
            return $this->l->t('Procedure {procedure} changed');
        } elseif ($subjectKey === self::PROCEDURE_DELETED) {
            return $this->l->t('Procedure {procedure} deleted');
        } elseif ($subjectKey === self::PROCEDURE_STAKEHOLDER_CREATED) {
            return $this->l->t('Procedure stakeholder created in procedure {procedure}');
        } elseif ($subjectKey === self::PROCEDURE_STAKEHOLDER_CHANGED) {
            return $this->l->t('Procedure stakeholder changed in procedure {procedure}');
        } elseif ($subjectKey === self::PROCEDURE_STAKEHOLDER_DELETED) {
            return $this->l->t('Procedure stakeholder deleted in procedure {procedure}');
        } elseif ($subjectKey === self::SIGNATURE_CREATED) {
            $files = isset($parameters['files']) && is_array($parameters['files']) ? $parameters['files'] : [];
            $fileCount = count($files);
            if ($fileCount > 0) {
                $filePlaceholders = [];
                for ($i = 1; $i <= $fileCount; $i++) {
                    $filePlaceholders[] = '{file' . $i . '}';
                }

                return $this->l->t('Signature created in procedure {procedure} with {fileCount} file(s): ') . implode(', ', $filePlaceholders);
            }

            return $this->l->t('Signature created in procedure {procedure}');
        } elseif ($subjectKey === self::SIGNATURE_CHANGED) {
            return $this->l->t('Signature changed in procedure {procedure}');
        } elseif ($subjectKey === self::SIGNATURE_DELETED) {
            return $this->l->t('Signature deleted in procedure {procedure}');
        } elseif ($subjectKey === self::MESSAGE_QUEUE_CREATED) {
            return $this->l->t("Remote command '{body}' sent for procedure {procedureId} attendance");
        } elseif ($subjectKey === self::MESSAGE_QUEUE_CHANGED) {
            throw new UnknownActivityException();
        } elseif ($subjectKey === self::MESSAGE_QUEUE_DELETED) {
            throw new UnknownActivityException();
        }

        return $this->l->t('Subject undefined in vtramit for: ' . $subjectKey);
    }

    /**
     * Builds the rich subject parameters for the event.
     *
     * @param IEvent $event Activity event to parse.
     * @return array<string, mixed>
     * @throws UnknownActivityException
     */
    protected function getParameters(IEvent $event): array {
        $subject = $event->getSubject();
        $parameters = $event->getSubjectParameters();

        switch ($subject) {
            case self::PROCEDURE_CREATED:
            case self::PROCEDURE_CHANGED:
                $procedureId = (string)$event->getObjectId();

                $richParameters = [
                    'procedure' => [
                        'type' => 'highlight',
                        'id' => $procedureId,
                        'name' => $procedureId,
                        'link' => $this->getLinkToProcedure($procedureId),
                    ],
                ];

                if ($subject === self::PROCEDURE_CHANGED && $this->hasProcedureStatusChange($parameters)) {
                    $statusChange = $this->getProcedureStatusChange($parameters);
                    $richParameters['statusFrom'] = [
                        'type' => 'highlight',
                        'id' => (string)$statusChange['fromId'],
                        'name' => $statusChange['fromLabel'],
                    ];
                    $richParameters['statusTo'] = [
                        'type' => 'highlight',
                        'id' => (string)$statusChange['toId'],
                        'name' => $statusChange['toLabel'],
                    ];
                }

                return $richParameters;

            case self::PROCEDURE_DELETED:
                $procedureId = (string)$event->getObjectId();

                return [
                    'procedure' => [
                        'type' => 'highlight',
                        'id' => $procedureId,
                        'name' => $procedureId,
                    ],
                ];

            case self::PROCEDURE_STAKEHOLDER_CREATED:
            case self::PROCEDURE_STAKEHOLDER_CHANGED:
            case self::SIGNATURE_CHANGED:
                $procedureId = (string)$parameters['procedureId'];

                return [
                    'procedure' => [
                        'type' => 'highlight',
                        'id' => $procedureId,
                        'name' => $procedureId,
                        'link' => $this->getLinkToProcedure($procedureId),
                    ],
                ];

            case self::SIGNATURE_CREATED:
                return $this->getParametersForSignatureCreated($subject, $parameters);

            case self::PROCEDURE_STAKEHOLDER_DELETED:
            case self::SIGNATURE_DELETED:
                $procedureId = (string)$event->getObjectId();

                return [
                    'procedure' => [
                        'type' => 'highlight',
                        'id' => $procedureId,
                        'name' => $procedureId,
                    ],
                ];

                return [];

            case self::MESSAGE_QUEUE_CREATED:
                if (!$this->isCommandMessage($parameters)) {
                    throw new UnknownActivityException();
                }

                $procedureId = (string)($parameters['procedureId'] ?? '');
                $body = (string)($parameters['body'] ?? '');

                return [
                    'body' => [
                        'type' => 'highlight',
                        'id' => $body,
                        'name' => $body,
                    ],
                    'procedureId' => [
                        'type' => 'highlight',
                        'id' => $procedureId,
                        'name' => $procedureId,
                        'link' => $this->getLinkToProcedure($procedureId),
                    ],
                ];
        }

        throw new UnknownActivityException();
    }

    /**
     * Resolves the action type suffix from a subject key.
     *
     * @param string $subjectKey Subject key for the activity.
     * @return string
     */
    protected function resolveActionFromSubjectKey(string $subjectKey): string {
        if (str_ends_with($subjectKey, '_created')) {
            return ActivityType::CREATED->value;
        }
        if (str_ends_with($subjectKey, '_changed')) {
            return ActivityType::CHANGED->value;
        }
        if (str_ends_with($subjectKey, '_deleted')) {
            return ActivityType::DELETED->value;
        }

        return $subjectKey;
    }

    /**
     * Checks if the subject parameters include a procedure status change.
     *
     * @param array<string, mixed> $parameters
     * @return bool
     */
    private function hasProcedureStatusChange(array $parameters): bool {
        $oldStatus = $parameters['oldData']['statusId'] ?? null;
        $newStatus = $parameters['newData']['statusId'] ?? null;

        return $oldStatus !== null && $newStatus !== null && (int)$oldStatus !== (int)$newStatus;
    }

    /**
     * Builds the status change payload for the rich subject.
     *
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    private function getProcedureStatusChange(array $parameters): array {
        $oldStatus = (int)($parameters['oldData']['statusId'] ?? 0);
        $newStatus = (int)($parameters['newData']['statusId'] ?? 0);

        return [
            'fromId' => $oldStatus,
            'toId' => $newStatus,
            'fromLabel' => $this->getProcedureStatusLabel($oldStatus),
            'toLabel' => $this->getProcedureStatusLabel($newStatus),
        ];
    }

    /**
     * Returns the localized label for a procedure status id.
     *
     * @param int $statusId
     * @return string
     */
    private function getProcedureStatusLabel(int $statusId): string {
        $details = ProcedureStatus::getDetails();
        foreach ($details as $status) {
            if (($status['key'] ?? null) === $statusId) {
                $label = (string)($status['value'] ?? '');
                return $label !== '' ? $this->l->t($label) : (string)$statusId;
            }
        }

        return (string)$statusId;
    }

    /**
     * Indicates whether the message queue activity is for a command channel.
     *
     * @param array<string, mixed> $parameters
     * @return bool
     */
    private function isCommandMessage(array $parameters): bool {
        $channel = (string)($parameters['channel'] ?? '');
        return $channel === MessageChannel::COMMAND->value;
    }


    //
    // Signature resolvers
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Builds rich parameters for signature creation, including file links.
     *
     * @param string $subject Subject key.
     * @param array<string, mixed> $parameters Subject parameters.
     * @return array<string, mixed>
     */
    protected function getParametersForSignatureCreated(string $subject, array $parameters): array {
        $procedureId = (string)$parameters['procedureId'];
        $files = isset($parameters['files']) && is_array($parameters['files']) ? $parameters['files'] : [];

        $richParameters = [
            'procedure' => [
                'type' => 'highlight',
                'id' => $procedureId,
                'name' => $procedureId,
                'link' => $this->getLinkToProcedure($procedureId),
            ],
        ];

        $fileIndex = 1;
        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }

            $fileName = $this->getSignatureFileName($file);
            $folderPath = $this->getSignatureFileFolderPath($file);
            if ($fileName === '' || $folderPath === '') {
                continue;
            }

            $richParameters['file' . $fileIndex] = [
                'type' => 'highlight',
                'id' => (string)($file['fileId'] ?? $fileName),
                'name' => $fileName,
                'link' => $this->getLinkToFilesFolder($folderPath),
            ];
            $fileIndex++;
        }

        $fileCount = $fileIndex - 1;
        $richParameters['fileCount'] = [
            'type' => 'highlight',
            'id' => (string)$fileCount,
            'name' => (string)$fileCount,
        ];

        return $richParameters;
    }

    /**
     * Extracts the filename from virtual signature paths.
     *
     * @param array<string, mixed> $file Signature file payload.
     * @return string
     */
    private function getSignatureFileName(array $file): string {
        if (!empty($file['filepath'])) {
            $name = $this->getLastPathSegment((string)$file['filepath']);
            if ($name !== '') {
                return $name;
            }
        }

        if (!empty($file['davpathUpload'])) {
            $name = $this->getLastPathSegment((string)$file['davpathUpload']);
            if ($name !== '') {
                return $name;
            }
        }

        if (!empty($file['davpathDownload'])) {
            $name = $this->getLastPathSegment((string)$file['davpathDownload']);
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }

    /**
     * Extracts the parent folder path from virtual signature paths.
     *
     * @param array<string, mixed> $file Signature file payload.
     * @return string
     */
    private function getSignatureFileFolderPath(array $file): string {
        $path = '';
        if (!empty($file['filepath'])) {
            $filepath = (string)$file['filepath'];
            $marker = '/files/';
            $pos = strpos($filepath, $marker);
            if ($pos !== false) {
                $path = substr($filepath, $pos + strlen($marker));
            }
        }

        if ($path === '' && !empty($file['davpathUpload'])) {
            $path = (string)$file['davpathUpload'];
        }

        if ($path === '' && !empty($file['davpathDownload'])) {
            $path = (string)$file['davpathDownload'];
        }

        if ($path === '') {
            return '';
        }

        $folder = $this->getParentPath($path);
        return $folder;
    }


    //
    // Path helpers
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Builds an absolute link to a procedure edit view.
     *
     * @param string $procedureId Procedure identifier.
     * @return string
     */
    protected function getLinkToProcedure(string $procedureId): string {
        return $this->url->getAbsoluteURL(
            $this->getWebPathToRoot() . 'apps/vtramit/#/' . $procedureId . '/edit'
        );
    }

    /**
     * Builds an absolute link to a Files folder.
     *
     * @param string $folderPath Folder path (virtual).
     * @return string
     */
    protected function getLinkToFilesFolder(string $folderPath): string {
        if ($folderPath === '') {
            $folderPath = '/';
        }

        return $this->url->getAbsoluteURL(
            $this->getWebPathToRoot() . 'apps/files/?dir=' . $folderPath
        );
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
     * Returns the last segment of a virtual path.
     *
     * @param string $path Virtual path.
     * @return string
     */
    private function getLastPathSegment(string $path): string {
        $trimmed = rtrim($path, '/');
        if ($trimmed === '') {
            return '';
        }

        $parts = explode('/', $trimmed);
        return $parts[count($parts) - 1] ?? '';
    }

    /**
     * Returns the parent path for a virtual path.
     *
     * @param string $path Virtual path.
     * @return string
     */
    private function getParentPath(string $path): string {
        $trimmed = rtrim($path, '/');
        if ($trimmed === '') {
            return '';
        }

        $pos = strrpos($trimmed, '/');
        if ($pos === false || $pos === 0) {
            return '';
        }

        return substr($trimmed, 0, $pos);
    }
}
