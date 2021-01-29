<?php
/**
 * @copyright Copyright (c) 2020-2021 Ajuntament de Barcelona
 * 
 * @author Daniel Tamajon <daniel@floss.cat>
 * @author Kenneth Peiruza <kenneth@floss.cat>
 * @author Letizia Benítez <letizia@floss.cat>
 * @author Marta González <gonzalez.marta@gmail.com>
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
use Doctrine\DBAL\Types\Type;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version100000Date20210125124731 extends SimpleMigrationStep {

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
            $table->addColumn('id', Type::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('external_id', Type::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('citizen_id', Type::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('department', Type::STRING, [
                'notnull' => false,
                'length' => 60
            ]);
            $table->addColumn('comments', Type::TEXT, [
                'notnull' => false,
                'default' => ''
            ]);
            $table->addColumn('date', Type::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('name', Type::STRING, [
                'notnull' => false,
                'length' => 120
            ]);
            $table->addColumn('phone', Type::STRING, [
                'notnull' => false,
                'length' => 20
            ]);
            $table->addColumn('email', Type::STRING, [
                'notnull' => false,
                'length' => 320
            ]);
            $table->addColumn('topic', Type::STRING, [
                'notnull' => false,
                'length' => 300
            ]);
            $table->addColumn('jitsi_room_code', Type::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('jitsi_room_informador', Type::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('jitsi_room_ciutada', Type::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('shared_url_uploads', Type::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('shared_url_downloads', Type::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('assigned_to', Type::STRING, [
                'notnull' => false,
            ]);
            $table->addColumn('state', Type::INTEGER, [
                'notnull' => false,
                'length' => 16
            ]);
            $table->addColumn('state_date', Type::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('user_id', Type::STRING, [
                'notnull' => true
            ]);
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('vtramit_mail_queue')) {
            $table = $schema->createTable('vtramit_mail_queue');
            $table->addColumn('id', Type::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('appointment_id', Type::STRING, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('mail_to', Type::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('mail_cc', Type::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('mail_cco', Type::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('subject', Type::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('body', Type::TEXT, [
                'notnull' => false,
                'default' => ''
            ]);
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('vtramit_videoconference')) {
            $table = $schema->createTable('vtramit_videoconference');
            $table->addColumn('id', Type::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('appointment_id', Type::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('first_connection', Type::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('last_connection', Type::INTEGER, [
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
