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

/**
 * Restrictions for naming length (given to Oracle support by Nextcloud):
 *
 *   Table name : 27 (23 if pk name is not explicitly defined)
 *   Column name: 30
 *   Index name : 30
 *   Foreign key: 30
 *   Primary key: 30
 *
 * The real table name length is 30 characters, it is 'oc_' + the name you define.
 */
class Version020100Date20231128104039 extends SimpleMigrationStep {

    /** @var SystemConfig */
    protected $config;

    /** @var IDBConnection */
    private $connection;

    /** @var string */
    private $tablePrefix;

    public function __construct(SystemConfig $config, IDBConnection $connection) {
        $this->config = $config;
        $this->connection = $connection;
        $this->tablePrefix = $config->getValue('dbtableprefix', 'oc_');
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();


        $schema = $this->updateTableProcedureType($schema);
        $schema = $this->createTableTopic($schema);
        $schema = $this->createTableWebhook($schema);
        $schema = $this->createTableSignatureType($schema);
        $schema = $this->createTableSignatureButtons($schema);

        // Try to change existing signature table, then try to create the
        // table again in case there were no previous signature table
        //$schema = $this->updateTableSignature($schema);
        $schema = $this->createTableSignature($schema);

        // Try to change existing signature  file table, then try to create the
        // table again in case there were no previous signature file table
        //$schema = $this->updateTableSignatureFile($schema);
        $schema = $this->createTableSignatureFile($schema);

        // Try to change existing videoconference table, then try to create the
        // table again in case there were no previous videoconference table
        //$schema = $this->updateTableVideoconference($schema);
        $schema = $this->createTableVideoconference($schema);

        // Try to change existing mail queue table, then try to create the
        // table again in case there were no previous mail queue table
        //$schema = $this->updateTableMailQueue($schema);
        $schema = $this->createTableMailQueue($schema);

        // Remove old unused tables
        $schema = $this->removeOldSchema($schema);

        return $schema;
    }

    private function updateTableProcedureType(ISchemaWrapper $schema): ISchemaWrapper {
        if ($schema->hasTable(Tables::PROCEDURE_TYPE)) {
            $table = $schema->getTable(Tables::PROCEDURE_TYPE);

            // Add missing column
            if (!$table->hasColumn('signature_timeframe_sign')) {
                $table->addColumn('signature_timeframe_sign', Types::INTEGER, [
                    'notnull' => false,
                    'default' => 0,
                    'comment' => 'in hours; use 0 for no expiry'
                ]);
            }
            if (!$table->hasColumn('signature_timeframe_download')) {
                $table->addColumn('signature_timeframe_download', Types::INTEGER, [
                    'notnull' => false,
                    'default' => 0,
                    'comment' => 'in hours; use 0 for no expiry'
                ]);
            }
            if (!$table->hasColumn('jitsi_stakeholder_jwt_secret')) {
                $table->addColumn('jitsi_stakeholder_jwt_secret', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
                ]);
            }
            if (!$table->hasColumn('jitsi_stakeholder_jwt_payload')) {
                $table->addColumn('jitsi_stakeholder_jwt_payload', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 4000
                ]);
            }
            if (!$table->hasColumn('jitsi_staff_jwt_secret')) {
                $table->addColumn('jitsi_staff_jwt_secret', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
                ]);
            }
            if (!$table->hasColumn('jitsi_staff_jwt_payload')) {
                $table->addColumn('jitsi_staff_jwt_payload', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 4000
                ]);
            }
            if ($table->hasColumn('signature_buttons')) {
                $table->dropColumn('signature_buttons');
            }
            if ($table->hasColumn('jitsi_stakeholder_jwt')) {
                $table->dropColumn('jitsi_stakeholder_jwt');
            }
            if ($table->hasColumn('jitsi_staff_jwt')) {
                $table->dropColumn('jitsi_staff_jwt');
            }
        }

        return $schema;
    }

    private function createTableSignatureType(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::SIGNATURE_TYPE)) {
            $table = $schema->createTable(Tables::SIGNATURE_TYPE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => false,
                'length' => 120
            ]);
            $table->addColumn('request_limit', Types::INTEGER, [
                'notnull' => false,
                'default' => 0
            ]);
            $table->addColumn('files_per_request', Types::INTEGER, [
                'notnull' => false,
                'default' => 0
            ]);
            $table->addColumn('file_type', Types::STRING, [
                'notnull' => false,
                'length' => 320
            ]);
            $table->addColumn('format', Types::STRING, [
                'notnull' => false,
                'length' => 120,
            ]);
            $table->addColumn('certified_authority', Types::STRING, [
                'notnull' => false,
                'length' => 120,
            ]);
            $table->addColumn('certified_authority_proxy', Types::STRING, [
                'notnull' => false,
                'length' => 320,
            ]);
            $table->addColumn('method', Types::STRING, [
                'notnull' => false,
                'length' => 120,
                'comment' => 'signature method allowed; leave blank to use default method defined in ca-proxy'
            ]);
            $table->addColumn('additional_info', Types::TEXT, [
                'notnull' => false,
            ]);
            $table->addColumn('phone_required', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);

            $table->setPrimaryKey(['id'], Tables::SIGNATURE_TYPE . '_pk');
        }

        return $schema;
    }

    private function createTableSignatureButtons(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::SIGNATURE_BUTTONS)) {
            $table = $schema->createTable(Tables::SIGNATURE_BUTTONS);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('procedure_type_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('group', Types::STRING, [
                'notnull' => true,
                'default' => 0,
                'length' => 255
            ]);
            $table->addColumn('signature_type_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('mail_enabled', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('mail_template_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('message_enabled', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('message_template_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('label', Types::TEXT, [
                'notnull' => false,
                'comment' => 'text, in HTML format, with Signature buttons information for each allowed language'
            ]);

            $table->setPrimaryKey(['id'], Tables::SIGNATURE_BUTTONS . '_pk');
        }

        return $schema;
    }

    /**
     * Update old signature schema.
     */
    private function updateTableSignature(ISchemaWrapper $schema): ISchemaWrapper {
        // Remove old name and set new name
        if ($schema->hasTable('vtramit_signature')) {
            if (!$schema->hasTable(Tables::SIGNATURE)) {
                $this->renameTable('vtramit_signature', Tables::SIGNATURE);
            }
        }

        if ($schema->hasTable(Tables::SIGNATURE)) {
            $table = $schema->getTable(Tables::SIGNATURE);

            // Add missing column
            if (!$table->hasColumn('signature_type_id')) {
                $table->addColumn('signature_type_id', Types::INTEGER, [
                    'notnull' => false,
                    'default' => NULL
                ]);
            }

            // Drop unused column
            if ($table->hasColumn('appointment_id')) {
                $table->dropColumn('appointment_id');
            }

            // Rename existing columns
            if ($table->hasColumn('state')) {
                $this->executeQuery('ALTER TABLE ' . $this->tablePrefix . Tables::SIGNATURE . ' RENAME COLUMN `state` TO `status`;');
            }
            if ($table->hasColumn('state_date')) {
                $this->executeQuery('ALTER TABLE ' . $this->tablePrefix . Tables::SIGNATURE . ' RENAME COLUMN `state_date` TO `status_date`;');
            }
        }

        return $schema;
    }

    /**
     * Create new signature schema.
     */
    private function createTableSignature(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::SIGNATURE)) {
            $table = $schema->createTable(Tables::SIGNATURE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('signature_type_id', Types::INTEGER, [
                'notnull' => true
            ]);
            $table->addColumn('procedure_id', Types::INTEGER, [
                'notnull' => true
            ]);
            $table->addColumn('status', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('status_date', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('url', Types::STRING, [
                'notnull' => false,
                'length' => 800
            ]);
            $table->addColumn('hash', Types::STRING, [
                'notnull' => false,
                'length' => 64
            ]);
            $table->addColumn('davpath_upload', Types::STRING, [
                'notnull' => false,
                'length' => 1500
            ]);
            $table->addColumn('davpath_download', Types::STRING, [
                'notnull' => false,
                'length' => 1500
            ]);
            $table->addColumn('signature_methods', Types::STRING, [
                'notnull' => false,
                'default' => '[]',
                'length' => 1500
            ]);
            $table->addColumn('additional_info', Types::STRING, [
                'notnull' => false,
                'default' => '',
                'length' => 4000
            ]);

            $table->setPrimaryKey(['id'], Tables::SIGNATURE . '_pk');
        }

        return $schema;
    }

    /**
     * Update old signature file schema.
     */
    private function updateTableSignatureFile(ISchemaWrapper $schema): ISchemaWrapper {
        // Remove old name and set new name
        if ($schema->hasTable('vtramit_signature_file')) {
            if (!$schema->hasTable(Tables::SIGNATURE_FILE)) {
                $this->renameTable('vtramit_signature_file', Tables::SIGNATURE_FILE);
            }
        }
        return $schema;
    }

    /**
     * Create new signature file schema.
     */
    private function createTableSignatureFile(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::SIGNATURE_FILE)) {
            $table = $schema->createTable(Tables::SIGNATURE_FILE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('signature_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('file_id', Types::STRING, [
                'notnull' => false,
                'length' => 1500
            ]);
            $table->addColumn('filepath', Types::STRING, [
                'notnull' => false,
                'length' => 1500
            ]);
            $table->addColumn('davpath_upload', Types::STRING, [
                'notnull' => false,
                'length' => 1500
            ]);
            $table->addColumn('davpath_download', Types::STRING, [
                'notnull' => false,
                'length' => 1500
            ]);
            $table->addColumn('shared_url', Types::STRING, [
                'notnull' => false,
                'length' => 500,
            ]);
            $table->addColumn('deleted', Types::SMALLINT, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
            ]);

            $table->setPrimaryKey(['id'], Tables::SIGNATURE_FILE . '_pk');
        }

        return $schema;
    }

    /**
     * Update old mail queue schema.
     */
    private function updateTableMailQueue(ISchemaWrapper $schema): ISchemaWrapper {
        // Remove old name and set new name
        if ($schema->hasTable('vtramit_mail_queue')) {
            if (!$schema->hasTable(Tables::MAIL_QUEUE)) {
                $this->renameTable('vtramit_mail_queue', Tables::MAIL_QUEUE);
            }
        }

        if ($schema->hasTable(Tables::MAIL_QUEUE)) {
            $table = $schema->getTable(Tables::MAIL_QUEUE);

            // Drop unused column
            if ($table->hasColumn('appointment_id')) {
                $table->dropColumn('appointment_id');
            }
            if ($table->hasColumn('origin')) {
                $table->dropColumn('origin');
            }
        }

        return $schema;
    }

    /**
     * Create new mail queue schema.
     */
    private function createTableMailQueue(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::MAIL_QUEUE)) {
            $table = $schema->createTable(Tables::MAIL_QUEUE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('procedure_id', Types::INTEGER, [
                'notnull' => false,
                'default' => NULL
            ]);
            $table->addColumn('mail_to', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('mail_cc', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('mail_cco', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('subject', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('body', Types::TEXT, [
                'notnull' => false,
                'default' => ''
            ]);
            $table->addColumn('citizen_password', Types::STRING, [
                'notnull' => false,
                'default' => '',
                'length' => 500
            ]);

            $table->setPrimaryKey(['id'], Tables::MAIL_QUEUE . '_pk');
        }

        return $schema;
    }

    /**
     * Update old videoconference schema.
     */
    private function updateTableVideoconference(ISchemaWrapper $schema): ISchemaWrapper {
        // Remove old name and set new name
        if ($schema->hasTable('vtramit_videoconference')) {
            if (!$schema->hasTable(Tables::VIDEOCONFERENCE)) {

                // Remove columns before updating table name because that column causes a
                // 'too long index name' error
                $table = $schema->getTable('vtramit_videoconference');

                // Drop unused column
                if ($table->hasColumn('appointment_id')) {
                    $table->dropColumn('appointment_id');
                }

                $this->renameTable('vtramit_videoconference', Tables::VIDEOCONFERENCE);
            }
        }

        return $schema;
    }

    /**
     * Create new videoconference schema.
     */
    private function createTableVideoconference(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::VIDEOCONFERENCE)) {
            $table = $schema->createTable(Tables::VIDEOCONFERENCE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('procedure_id', Types::INTEGER, [
                'notnull' => true
            ]);
            $table->addColumn('first_connection', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('last_connection', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);

            $table->setPrimaryKey(['id'], 'vtproc_vc_pk');      // Cannot use Tables::VIDEOCONFERENCE because table name is too long to create the index
            $table->addIndex(['procedure_id'], 'vtproc_vc_ix'); // Cannot use Tables::VIDEOCONFERENCE because table name is too long to create the index
        }

        return $schema;
    }

    /**
     * Create new webhook schema.
     */
    private function createTableWebhook(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::WEBHOOK)) {
            $table = $schema->createTable(Tables::WEBHOOK);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => false,
                'length' => 120
            ]);
            $table->addColumn('endpoint', Types::STRING, [
                'notnull' => false,
                'length' => 2000
            ]);
            $table->addColumn('auth_type', Types::STRING, [
                'notnull' => false,
                'length' => 120
            ]);
            $table->addColumn('auth_type_data', Types::STRING, [
                'notnull' => false,
                'length' => 800
            ]);
            $table->addColumn('event', Types::STRING, [
                'notnull' => false,
                'length' => 800,
            ]);

            $table->setPrimaryKey(['id'], Tables::WEBHOOK . '_pk');
        }

        return $schema;
    }

    /**
     * Create topic schema.
     */
    private function createTableTopic(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::TOPIC)) {
            $table = $schema->createTable(Tables::TOPIC);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => false,
                'length' => 120
            ]);
            $table->addColumn('min_required_docs', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('video_preservation_time', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
                'comment' => 'in days; use 0 for never'
            ]);
            $table->addColumn('help_url', Types::STRING, [
                'notnull' => false,
                'length' => 800
            ]);
            $table->addColumn('help_target', Types::STRING, [
                'notnull' => false,
                'default' => '',
                'length' => 120
            ]);

            $table->setPrimaryKey(['id'], Tables::TOPIC . '_pk');
        }

        return $schema;
    }

    /**
     * Remove deprecated tables.
     */
    private function removeOldSchema(ISchemaWrapper $schema): ISchemaWrapper {

        if ($schema->hasTable('vtramit_appointment')) {
            $schema->dropTable('vtramit_appointment');
        }
        if ($schema->hasTable('vtramit_videoconference')) {
            $schema->dropTable('vtramit_videoconference');
        }
        if ($schema->hasTable('vtramit_mail_queue')) {
            $schema->dropTable('vtramit_mail_queue');
        }
        if ($schema->hasTable('vtramit_signature')) {
            $schema->dropTable('vtramit_signature');
        }
        if ($schema->hasTable('vtramit_signature_file')) {
            $schema->dropTable('vtramit_signature_file');
        }

        return $schema;
    }

    /**
     *
     */
    private function renameTable(string $currentName, string $newName) {
        $this->executeQuery('RENAME TABLE '. $this->tablePrefix . $currentName . ' TO ' . $this->tablePrefix . $newName);
    }

    /**
     *
     */
    private function executeQuery($sql) {
        $query = $this->connection->prepare($sql);
        try {
            $query->executeStatement();
        } catch (DatabaseException $e) {
            $this->logger->error('Error while trying to create database', [
                'app' => 'vtramit',
                'exception' => $e,
            ]);
        }
    }
}
