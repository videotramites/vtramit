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
class Version020200Date20250919191855 extends SimpleMigrationStep {

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
        $schema = $this->updateTableProcedureTypeGroup($schema);
        $schema = $this->updateTableSignatureFile($schema);
        $schema = $this->updateTableSignatureButtons($schema);
        $schema = $this->createTableMessageTemplate($schema);
        $schema = $this->createTableMessageQueue($schema);

        return $schema;
    }

    private function updateTableProcedureType(ISchemaWrapper $schema): ISchemaWrapper {
        if ($schema->hasTable(Tables::PROCEDURE_TYPE)) {
            $table = $schema->getTable(Tables::PROCEDURE_TYPE);

            // Add missing columns
            if (!$table->hasColumn('attendant_portal_properties')) {
                $table->addColumn('attendant_portal_properties', Types::JSON, [
                    'notnull' => false,
                    'default' => NULL
                ]);
            }
        }

        return $schema;
    }

    private function updateTableProcedureTypeGroup(ISchemaWrapper $schema): ISchemaWrapper {
        if ($schema->hasTable(Tables::PROCEDURE_TYPE_GROUP)) {
            $table = $schema->getTable(Tables::PROCEDURE_TYPE_GROUP);

            // Add missing columns
            if (!$table->hasColumn('notify_stakeholder_template')) {
                $table->addColumn('notify_stakeholder_template', Types::STRING, [
                    'notnull' => false,
                    'length' => 200
                ]);
            }
            if (!$table->hasColumn('notify_staff_template')) {
                $table->addColumn('notify_staff_template', Types::STRING, [
                    'notnull' => false,
                    'length' => 200
                ]);
            }

            // Drop on next version!!
            // Drop unused column
            /*if ($table->hasColumn('notify_stakeholder_template_id')) {
                $table->dropColumn('notify_stakeholder_template_id');
            }
            if ($table->hasColumn('notify_staff_template_id')) {
                $table->dropColumn('notify_staff_template_id');
            }*/
        }

        return $schema;
    }

    private function updateTableSignatureFile(ISchemaWrapper $schema): ISchemaWrapper {
        if ($schema->hasTable(Tables::SIGNATURE_FILE)) {
            $table = $schema->getTable(Tables::SIGNATURE_FILE);

            // Add missing column
            if (!$table->hasColumn('deleted')) {
                $table->addColumn('deleted', Types::SMALLINT, [
                    'notnull' => true,
                    'default' => 0,
                    'unsigned' => true,
                ]);
            }
        }

        return $schema;
    }

    private function updateTableSignatureButtons(ISchemaWrapper $schema): ISchemaWrapper {
        if ($schema->hasTable(Tables::SIGNATURE_BUTTONS)) {
            $table = $schema->getTable(Tables::SIGNATURE_BUTTONS);

            // Add missing column
            if (!$table->hasColumn('message_enabled')) {
                $table->addColumn('message_enabled', Types::BOOLEAN, [
                    'notnull' => false,
                    'default' => false
                ]);
            }
            if (!$table->hasColumn('message_template_id')) {
                $table->addColumn('message_template_id', Types::INTEGER, [
                    'notnull' => true,
                    'default' => 0,
                    'unsigned' => true
                ]);
            }

            // DEPRECATED: Add following lines in next version
            // Drop on next version!!
            // Drop unused column
            /*if ($table->hasColumn('notify_stakeholder_template_id')) {
                $table->dropColumn('notify_stakeholder_template_id');
            }
            if ($table->hasColumn('notify_staff_template_id')) {
                $table->dropColumn('notify_staff_template_id');
            }
            if ($table->hasColumn('mail_enabled')) {
                $table->dropColumn('mail_enabled');
            }
            if ($table->hasColumn('mail_template_id')) {
                $table->dropColumn('mail_template_id');
            }*/
        }

        return $schema;
    }

    private function createTableMessageTemplate(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::MESSAGE_TEMPLATE)) {
            $table = $schema->createTable(Tables::MESSAGE_TEMPLATE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('channel', Types::STRING, [
                'notnull' => false,
                'length' => 120,
                'comment' => 'message channel, e.g. email, sms, command'
            ]);
            $table->addColumn('subject', Types::STRING, [
                'notnull' => true,
                'length' => 255
            ]);
            $table->addColumn('template', Types::TEXT, [
                'notnull' => false
            ]);

            $table->setPrimaryKey(['id'], Tables::MESSAGE_TEMPLATE . '_pk');
        }

        return $schema;
    }

    private function createTableMessageQueue(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::MESSAGE_QUEUE)) {
            $table = $schema->createTable(Tables::MESSAGE_QUEUE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('procedure_id', Types::INTEGER, [
                'notnull' => false,
                'default' => NULL
            ]);
            $table->addColumn('procedure_stakeholder_id', Types::INTEGER, [
                'notnull' => false,
                'default' => NULL
            ]);
            $table->addColumn('channel', Types::STRING, [
                'notnull' => false,
                'length' => 120,
                'comment' => 'message channel, e.g. email, sms, command'
            ]);
            $table->addColumn('recipient', Types::STRING, [
                'notnull' => false,
                'length' => 120,
                'comment' => 'generic destination (email, phone number, jitsi room, ...)'
            ]);
            $table->addColumn('subject', Types::STRING, [
                'notnull' => false,
                'length' => 320
            ]);
            $table->addColumn('body', Types::TEXT, [
                'notnull' => false,
                'default' => ''
            ]);
            $table->addColumn('citizen_password', Types::STRING, [
                'notnull' => false,
                'length' => 120,
            ]);

            $table->setPrimaryKey(['id'], Tables::MESSAGE_QUEUE . '_pk');
        }

        return $schema;
    }
}