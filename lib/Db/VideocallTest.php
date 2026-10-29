<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class VideocallTest extends Entity implements JsonSerializable {
    protected $procedureStakeholderId;
    protected $procedureId;
    protected $sessionId;
    protected $state;
    protected $resultJson;
    protected $createdAt;
    protected $completedAt;
    protected $joinUrl;

    public function __construct() {
        $this->addType('procedureStakeholderId', 'integer');
        $this->addType('procedureId', 'integer');
        $this->addType('createdAt', 'integer');
        $this->addType('completedAt', 'integer');
    }

    public function jsonSerialize(): array {
        $result = null;
        if (!empty($this->resultJson)) {
            $result = json_decode($this->resultJson, true);
        }
        return [
            'id'                     => $this->id,
            'procedureStakeholderId' => $this->procedureStakeholderId,
            'procedureId'            => $this->procedureId,
            'sessionId'              => $this->sessionId,
            'state'                  => $this->state,
            'result'                 => $result,
            'createdAt'              => $this->createdAt,
            'completedAt'            => $this->completedAt,
            'joinUrl'                => $this->joinUrl,
        ];
    }
}
