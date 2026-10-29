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

class Version010000Date20210125124731 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('vtramit_appointment')) {
            $table = $schema->createTable('vtramit_appointment');
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('external_id', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('citizen_id', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('department', Types::STRING, [
                'notnull' => false,
                'length' => 60
            ]);
            $table->addColumn('comments', Types::TEXT, [
                'notnull' => false,
                'default' => ''
            ]);
            $table->addColumn('date', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => false,
                'length' => 120
            ]);
            $table->addColumn('phone', Types::STRING, [
                'notnull' => false,
                'length' => 20
            ]);
            $table->addColumn('email', Types::STRING, [
                'notnull' => false,
                'length' => 320
            ]);
            $table->addColumn('topic', Types::STRING, [
                'notnull' => false,
                'length' => 300
            ]);
            $table->addColumn('jitsi_room_code', Types::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('jitsi_room_informador', Types::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('jitsi_room_ciutada', Types::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('shared_url_uploads', Types::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('shared_url_downloads', Types::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('assigned_to', Types::STRING, [
                'notnull' => false,
            ]);
            $table->addColumn('state', Types::INTEGER, [
                'notnull' => false,
                'length' => 16
            ]);
            $table->addColumn('state_date', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true
            ]);
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('vtramit_mail_queue')) {
            $table = $schema->createTable('vtramit_mail_queue');
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('appointment_id', Types::STRING, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
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
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('vtramit_videoconference')) {
            $table = $schema->createTable('vtramit_videoconference');
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('appointment_id', Types::INTEGER, [
                'notnull' => true,
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
            $table->setPrimaryKey(['id'], 'oc_vtramit_vc');
            $table->addIndex(['appointment_id'], 'vtramit_vc_appointment_index');
        }

        return $schema;
    }

    public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options) {
    }
}
