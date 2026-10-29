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
use OCP\DB\QueryBuilder\IQueryBuilder;
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
class Version020300Date20263101193356 extends SimpleMigrationStep {

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

        $schema = $this->createTableProcedureTypeProtocolStep($schema);
        $schema = $this->createTableProcedureProtocolLog($schema);

        return $schema;
    }

    private function createTableProcedureTypeProtocolStep(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::PROCEDURE_TYPE_PROTOCOL_STEP)) {
            $table = $schema->createTable(Tables::PROCEDURE_TYPE_PROTOCOL_STEP);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('procedure_type_id', Types::INTEGER, [
                'notnull' => false,
                'default' => NULL
            ]);
            $table->addColumn('procedure_type_group_id', Types::INTEGER, [
                'notnull' => false,
                'default' => NULL
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('description', Types::TEXT, [
                'notnull' => false
            ]);
            $table->addColumn('order', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true
            ]);

            $table->setPrimaryKey(['id'], Tables::PROCEDURE_TYPE_PROTOCOL_STEP . '_pk');
            //$table->addIndex(['procedure_type_id'], Tables::PROCEDURE_TYPE_PROTOCOL_STEP . '_ptps_ptid_idx');
            //$table->addIndex(['procedure_type_group_id'], Tables::PROCEDURE_TYPE_PROTOCOL_STEP . '_ptps_ptgid_idx');
        }

        return $schema;
    }

    private function createTableProcedureProtocolLog(ISchemaWrapper $schema): ISchemaWrapper {
        if (!$schema->hasTable(Tables::PROCEDURE_PROTOCOL_LOG)) {
            $table = $schema->createTable(Tables::PROCEDURE_PROTOCOL_LOG);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('procedure_id', Types::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('proc_type_protocol_step_id', Types::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('procedure_type_group_id', Types::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);

            $table->setPrimaryKey(['id'], Tables::PROCEDURE_PROTOCOL_LOG . '_pk');
            $table->addIndex(['procedure_id'], Tables::PROCEDURE_PROTOCOL_LOG . '_pid_idx');
        }

        return $schema;
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
        /** @var IQueryBuilder $qb */
        $qb = $this->connection->getQueryBuilder();

        // Update only vtramit activities with action suffix in type.
        $expr = $qb->expr();

        $qb->update('activity')
            // Keep the original activity type as subject.
            ->set('subject', $qb->createFunction('`type`'))
            ->set(
                'type',
                $qb->createFunction(
                    "CASE
                        WHEN `type` LIKE '%_created' THEN SUBSTRING(`type`, 1, CHAR_LENGTH(`type`) - 8)
                        WHEN `type` LIKE '%_changed' THEN SUBSTRING(`type`, 1, CHAR_LENGTH(`type`) - 8)
                        WHEN `type` LIKE '%_deleted' THEN SUBSTRING(`type`, 1, CHAR_LENGTH(`type`) - 8)
                        ELSE `type`
                    END"
                )
            )
            ->where($expr->eq('app', $qb->createNamedParameter('vtramit')))
            ->andWhere(
                $expr->orX(
                    $expr->like('type', $qb->createNamedParameter('%_created')),
                    $expr->like('type', $qb->createNamedParameter('%_changed')),
                    $expr->like('type', $qb->createNamedParameter('%_deleted'))
                )
            );

        $qb->executeStatement();
    }
}
