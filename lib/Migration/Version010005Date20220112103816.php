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

class Version010005Date20220112103816 extends SimpleMigrationStep {

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
            if (!$table->hasColumn('uploads_qty')) {
                $table->addColumn('uploads_qty', Types::INTEGER, [
                    'notnull' => false,
                    'default' => 0,
                    'length' => 16
                ]);
            }
            if (!$table->hasColumn('downloads_qty')) {
                $table->addColumn('downloads_qty', Types::INTEGER, [
                    'notnull' => false,
                    'default' => 0,
                    'length' => 16
                ]);
            }
        }

        if ($schema->hasTable('vtramit_signature')) {
            $table = $schema->getTable('vtramit_signature');

            if (!$table->hasColumn('signature_methods')) {
                $table->addColumn('signature_methods', Types::STRING, [
                    'notnull' => false,
                    'default' => '[]',
                    'length' => 1500
                ]);
            }
        }

        return $schema;
    }
}
