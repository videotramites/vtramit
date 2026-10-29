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
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version010002Date20210602224412 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if ($schema->hasTable('vtramit_appointment')) {
            $table = $schema->getTable('vtramit_appointment');

            if (!$table->hasColumn('citizen_entry_page')) {
                $table->addColumn('citizen_entry_page', Types::STRING, [
                    'notnull' => false,
                    'length' => 500,
                ]);
            }
        }

        if (!$schema->hasTable('vtramit_signature')) {
            $table = $schema->createTable('vtramit_signature');
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('appointment_id', Types::INTEGER, [
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
            $table->addColumn('state', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('state_date', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('vtramit_signature_file')) {
            $table = $schema->createTable('vtramit_signature_file');
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
            $table->setPrimaryKey(['id']);
        }

        return $schema;
    }
}
