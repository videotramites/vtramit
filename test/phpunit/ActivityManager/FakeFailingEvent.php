<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Test\Phpunit\ActivityManager;

use OCP\Activity\IEvent;

class FakeFailingEvent implements IEvent
{
    public function setApp(string $app): IEvent {
        throw new \InvalidArgumentException("Simulated failure");
    }

    public function setType(string $type): IEvent { return $this; }
    public function setAuthor(string $author): IEvent { return $this; }
    public function setAffectedUser(string $user): IEvent { return $this; }
    public function setTimestamp(int $timestamp): IEvent { return $this; }
    public function setSubject(string $subject, array $parameters = []): IEvent { return $this; }
    public function setObject(string $objectType, int $objectId, string $objectName = ''): IEvent { return $this; }
    public function setLink(string $link): IEvent { return $this; }
    public function setParsedSubject(string $subject): IEvent { return $this; }
    public function getParsedSubject(): string { return ''; }
    public function setRichSubject(string $subject, array $parameters = []): IEvent { return $this; }
    public function getRichSubject(): string { return ''; }
    public function setParsedSubjectParameters(array $parsedParams): IEvent { return $this; }
    public function getParsedSubjectParameters(): array { return []; }
    public function getRichSubjectParameters(): array { return []; }
    public function setMessage(string $message, array $parameters = []): IEvent { return $this; }
    public function setParsedMessage(string $message): IEvent { return $this; }
    public function getParsedMessage(): string { return ''; }
    public function setRichMessage(string $message, array $parameters = []): IEvent { return $this; }
    public function getRichMessage(): string { return ''; }
    public function setParsedMessageParameters(array $parsedParams): IEvent { return $this; }
    public function getParsedMessageParameters(): array { return []; }
    public function getRichMessageParameters(): array { return []; }
    public function getApp(): string { return 'vtramit'; }
    public function getType(): string { return 'type'; }
    public function getAuthor(): string { return 'author'; }
    public function getAffectedUser(): string { return 'affectedUser'; }
    public function getTimestamp(): int { return time(); }
    public function getSubject(): string { return 'subject'; }
    public function getSubjectParameters(): array { return []; }
    public function getObjectType(): string { return 'objectType'; }
    public function getObjectId(): int { return 123; }
    public function getObjectName(): string { return 'objectName'; }
    public function getLink(): string { return '/link'; }
    public function getMessage(): string { return ''; }
    public function getMessageParameters(): array { return []; }
    public function setIcon(string $icon): IEvent { return $this; }
    public function getIcon(): string { return 'icon'; }
    public function setBackground(string $background): IEvent { return $this; }
    public function getBackground(): string { return 'background'; }
    public function setForeground(string $foreground): IEvent { return $this; }
    public function getForeground(): string { return 'foreground'; }
    public function setLinkPreview(string $linkPreview): IEvent { return $this; }
    public function getLinkPreview(): string { return 'linkPreview'; }
    public function setPriority(int $priority): IEvent { return $this; }
    public function getPriority(): int { return 0; }
    public function setChildEvent(IEvent $child): IEvent { return $this; }
    public function getChildEvent(): ?IEvent { return null; }
    public function isValid(): bool { return true; }
    public function setAppVisibility(?string $visibility): IEvent { return $this; }
    public function getAppVisibility(): ?string { return null; }
    public function isRichObjectName(): bool { return false; }
    public function setRichObjectName(bool $richObjectName): IEvent { return $this; }
    public function isValidParsed(): bool { return true; }
    public function setGenerateNotification(bool $generate): IEvent { return $this; }
    public function getGenerateNotification(): bool { return true; }
}