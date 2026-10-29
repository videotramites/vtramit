<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\VTramit\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class VideocallTestMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::VIDEOCALL_TEST, VideocallTest::class);
    }

    public function find(int $id): VideocallTest {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTablename())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
        return $this->findEntity($qb);
    }

    public function findBySessionId(string $sessionId): VideocallTest {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTablename())
            ->where($qb->expr()->eq('session_id', $qb->createNamedParameter($sessionId)));
        return $this->findEntity($qb);
    }

    public function findLastByStakeholderId(int $stakeholderId): ?VideocallTest {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTablename())
            ->where($qb->expr()->eq('procedure_stakeholder_id', $qb->createNamedParameter($stakeholderId, IQueryBuilder::PARAM_INT)))
            ->orderBy('created_at', 'DESC')
            ->setMaxResults(1);
        try {
            return $this->findEntity($qb);
        } catch (DoesNotExistException $e) {
            return null;
        }
    }

    public function findLastByProcedureId(int $procedureId): ?VideocallTest {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTablename())
            ->where($qb->expr()->eq('procedure_id', $qb->createNamedParameter($procedureId, IQueryBuilder::PARAM_INT)))
            ->orderBy('created_at', 'DESC')
            ->setMaxResults(1);
        try {
            return $this->findEntity($qb);
        } catch (DoesNotExistException $e) {
            return null;
        }
    }
}
