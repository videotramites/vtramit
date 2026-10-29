<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace OCA\VTramit\Migration;

use Closure;
use OC\SystemConfig;
use OCP\IDBConnection;
use OCP\DB\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;
use OCA\VTramit\Db\Tables;

class Version020400Date20260321120000 extends SimpleMigrationStep {

    protected $config;
    private $connection;

    public function __construct(SystemConfig $config, IDBConnection $connection) {
        $this->config = $config;
        $this->connection = $connection;
    }

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        $schema = $this->createTableVideocallTest($schema);
        $schema = $this->addDefaultViewColumns($schema);

        return $schema;
    }

    private function createTableVideocallTest(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::VIDEOCALL_TEST)) {
            $table = $schema->createTable(Tables::VIDEOCALL_TEST);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('procedure_stakeholder_id', Types::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('procedure_id', Types::INTEGER, [
                'notnull' => false,
            ]);
            $table->addColumn('session_id', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('state', Types::STRING, [
                'notnull' => true,
                'length' => 30,
                'default' => 'pending',
            ]);
            $table->addColumn('result_json', Types::TEXT, [
                'notnull' => false,
            ]);
            $table->addColumn('created_at', Types::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('completed_at', Types::INTEGER, [
                'notnull' => false,
            ]);
            $table->addColumn('join_url', Types::STRING, [
                'notnull' => false,
                'length' => 500,
            ]);

            $table->setPrimaryKey(['id'], Tables::VIDEOCALL_TEST . '_pk');
            $table->addIndex(['procedure_stakeholder_id'], Tables::VIDEOCALL_TEST . '_sh_idx');
            $table->addIndex(['session_id'], Tables::VIDEOCALL_TEST . '_sid_idx');
        }

        return $schema;
    }

    private function addDefaultViewColumns(ISchemaWrapper $schema): ISchemaWrapper {
        if ($schema->hasTable(Tables::PROCEDURE_TYPE_GROUP)) {
            $table = $schema->getTable(Tables::PROCEDURE_TYPE_GROUP);
            if (!$table->hasColumn('default_view_desktop_left')) {
                $table->addColumn('default_view_desktop_left', Types::STRING, [
                    'notnull' => false, 'length' => 30,
                ]);
            }
            if (!$table->hasColumn('default_view_desktop_right')) {
                $table->addColumn('default_view_desktop_right', Types::STRING, [
                    'notnull' => false, 'length' => 30,
                ]);
            }
            if (!$table->hasColumn('default_view_mobile')) {
                $table->addColumn('default_view_mobile', Types::STRING, [
                    'notnull' => false, 'length' => 30,
                ]);
            }
        }

        return $schema;
    }

    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
        // Set default videocall test help text if not already set
        $appName = 'vtramit';
        $key = 'videocallTestHelpText';

        $qb = $this->connection->getQueryBuilder();
        $qb->select('configvalue')
            ->from('appconfig')
            ->where($qb->expr()->eq('appid', $qb->createNamedParameter($appName)))
            ->andWhere($qb->expr()->eq('configkey', $qb->createNamedParameter($key)));
        $result = $qb->executeQuery();
        $existing = $result->fetchOne();
        $result->closeCursor();

        if ($existing === false || $existing === '') {
            $defaultText = '<p><strong>Abans de la videotrucada:</strong></p>'
                . '<ul>'
                . '<li>Haureu de donar permís al navegador per accedir al micròfon i la càmera.</li>'
                . '<li>A la pantalla de la videotrucada, assegureu-vos que el micròfon i la càmera estan oberts.</li>'
                . '<li>Si el sistema no els detecta, probablement no heu donat permís al navegador, o una altra aplicació té la càmera en ús. En aquest cas, tanqueu l\'altra aplicació o reinicieu l\'equip.</li>'
                . '</ul>'
                . '<p><strong>Antes de la videollamada:</strong></p>'
                . '<ul>'
                . '<li>Deberá dar permiso al navegador para acceder al micrófono y la cámara.</li>'
                . '<li>En la pantalla de la videollamada, asegúrese de que el micrófono y la cámara están abiertos.</li>'
                . '<li>Si el sistema no los detecta, probablemente no ha dado permiso al navegador, o otra aplicación tiene la cámara en uso. En ese caso, cierre la otra aplicación o reinicie su equipo.</li>'
                . '</ul>'
                . '<p><strong>Before the videocall:</strong></p>'
                . '<ul>'
                . '<li>You will need to give your browser permission to access the microphone and camera.</li>'
                . '<li>On the videocall screen, make sure the microphone and camera are open.</li>'
                . '<li>If the system does not detect them, you probably have not given permission to the browser, or another application is using the camera. In that case, close the other application or restart your computer.</li>'
                . '</ul>';

            $qb2 = $this->connection->getQueryBuilder();
            $qb2->insert('appconfig')
                ->values([
                    'appid' => $qb2->createNamedParameter($appName),
                    'configkey' => $qb2->createNamedParameter($key),
                    'configvalue' => $qb2->createNamedParameter($defaultText),
                ]);
            $qb2->executeStatement();
        }
    }
}
