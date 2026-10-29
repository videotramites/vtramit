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
class Version020000Date20220915152417 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        $schema = $this->createTableMailTemplate($schema);
        $schema = $this->createTableRgpdOption($schema);
        $schema = $this->createTableRgpdActivity($schema);
        $schema = $this->createTableProcedureType($schema);
        $schema = $this->createTableProcedureTypeGroup($schema);
        //$schema = $this->createTableProcedureTypeStatus($schema);
        //$schema = $this->createTableProcedureTypeStatusGroup($schema);
        $schema = $this->createTableProcedure($schema);
        $schema = $this->createTableProcedureStakeholder($schema);
        $schema = $this->alterTableQueue($schema);
        $schema = $this->alterTableSignature($schema);
        $schema = $this->alterTableVideoconference($schema);

        return $schema;
    }

    private function alterTableQueue($schema) {
        if ($schema->hasTable('vtramit_mail_queue')) {
            $table = $schema->getTable('vtramit_mail_queue');
            if (!$table->hasColumn('procedure_id')) {
                $table->addColumn('procedure_id', Types::INTEGER, [
                    'notnull' => false,
                    'default' => NULL
                ]);
            }
            if (!$table->hasColumn('origin')) {
                $table->addColumn('origin', Types::STRING, [
                    'notnull' => false,
                    'default' => 'A',
                    'length' => 6,
                    'comment' => 'A = appointments / P = procedures'
                ]);
            }
        }

        return $schema;
    }

    private function alterTableSignature($schema) {
        if ($schema->hasTable('vtramit_signature')) {
            $table = $schema->getTable('vtramit_signature');
            if (!$table->hasColumn('procedure_id')) {
                $table->addColumn('procedure_id', Types::INTEGER, [
                    'notnull' => false,
                    'default' => 0
                ]);
            }
        }

        return $schema;
    }

    private function alterTableVideoconference($schema) {
        if ($schema->hasTable('vtramit_videoconference')) {
            $table = $schema->getTable('vtramit_videoconference');
            if (!$table->hasColumn('procedure_id')) {
                $table->addColumn('procedure_id', Types::INTEGER, [
                    'notnull' => false,
                    'default' => 0
                ]);
            }

            /*if ($table->hasColumn('appointment_id')) {
                $table->changeColumn('appointment_id', Types::INTEGER, [
                    'notnull' => false,
                    'default' => 0
                ]);
            }*/
        }

        return $schema;
    }

    private function createTableMailTemplate($schema) {
        if (!$schema->hasTable(Tables::MAIL_TEMPLATE)) {
            $table = $schema->createTable(Tables::MAIL_TEMPLATE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('subject', Types::STRING, [
                'notnull' => true,
                'length' => 255
            ]);
            $table->addColumn('template', Types::TEXT, [
                'notnull' => false
            ]);

            $table->setPrimaryKey(['id'], Tables::MAIL_TEMPLATE . '_pk');
        }

        return $schema;
    }

    private function createTableRgpdOption($schema) {
        if (!$schema->hasTable(Tables::RGPD_OPTION)) {
            $table = $schema->createTable(Tables::RGPD_OPTION);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('type', Types::STRING, [
                'notnull' => true,
                'length' => 200,
                'comment' => 'allowed values: checkbox, markup'
            ]);
            $table->addColumn('label', Types::TEXT, [
                'notnull' => false,
                'comment' => 'text, in HTML format, with RGPD information for each allowed language'
            ]);
            $table->addColumn('required', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);

            $table->setPrimaryKey(['id'], Tables::RGPD_OPTION . '_pk');
        }

        return $schema;
    }

    private function createTableRgpdActivity($schema) {
        if (!$schema->hasTable(Tables::RGPD_ACTIVITY)) {
            $table = $schema->createTable(Tables::RGPD_ACTIVITY);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('procedure_id', Types::INTEGER, [
                'notnull' => true
            ]);
            $table->addColumn('procedure_stakeholder_id', Types::INTEGER, [
                'notnull' => true
            ]);
            $table->addColumn('date', Types::DATETIME, [
                'notnull' => false,
            ]);
            $table->addColumn('label_list', Types::TEXT, [
                'notnull' => false,
                'comment' => 'list of accepted RGPD text in the user language'
            ]);
            $table->addColumn('option_list', Types::TEXT, [
                'notnull' => false,
                'comment' => 'list of accepted RGPD options'
            ]);
            $table->addColumn('user_agent', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('ip', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 40
            ]);

            $table->setPrimaryKey(['id'], Tables::RGPD_ACTIVITY . '_pk');
        }

        return $schema;
    }

    private function createTableProcedureType($schema) {
        if (!$schema->hasTable(Tables::PROCEDURE_TYPE)) {
            $table = $schema->createTable(Tables::PROCEDURE_TYPE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            $table->addColumn('rgpd_options', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('allow_upload', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('allow_download', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('uploads_folder', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 200
            ]);
            $table->addColumn('downloads_folder', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 200
            ]);
            $table->addColumn('allow_signature', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('signature_uploads_folder', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 200
            ]);
            $table->addColumn('signature_downloads_folder', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 200
            ]);
            $table->addColumn('signature_timeframe_sign', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'comment' => 'in hours; use 0 for no expiry'
            ]);
            $table->addColumn('signature_timeframe_download', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'comment' => 'in hours; use 0 for no expiry'
            ]);
            $table->addColumn('signature_limit', Types::INTEGER, [
                'notnull' => true,
                'default' => 0
            ]);
            $table->addColumn('allow_video', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('jitsi_stakeholder_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('jitsi_stakeholder_jwt_secret', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('jitsi_stakeholder_jwt_payload', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 4000
            ]);
            $table->addColumn('jitsi_staff_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('jitsi_staff_jwt_secret', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('jitsi_staff_jwt_payload', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 4000
            ]);
            $table->addColumn('allowed_attendance_channels', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
           $table->addColumn('notify_stakeholder', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('notify_staff', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('require_assigned_group', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('require_external_id', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('require_appointment_date', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('require_deadline_date', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('require_stakeholder_pid', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('require_stakeholder_phone', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('require_stakeholder_email', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('require_attendance_channel', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('attendant_portal_properties', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 4000
            ]);
            $table->addColumn('deck_integration', Types::BOOLEAN, [
                'notnull' => false,
                'default' => 0
            ]);
            $table->addColumn('deck_board', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('deck_stack', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('procedure_anonymization_period', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => false,
                'comment' => 'in hours; use 0 for never'
            ]);
            $table->addColumn('files_deletion_period', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => false,
                'comment' => 'in hours; use 0 for never'
            ]);
            $table->addColumn('procedure_deletion_period', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => false,
                'comment' => 'in hours; use 0 for never'
            ]);

            $table->setPrimaryKey(['id'], Tables::PROCEDURE_TYPE . '_pk');
        }

        return $schema;
    }

    private function createTableProcedureTypeGroup($schema) {
        if (!$schema->hasTable(Tables::PROCEDURE_TYPE_GROUP)) {
            $table = $schema->createTable(Tables::PROCEDURE_TYPE_GROUP);
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
                'length' => 64
            ]);
            $table->addColumn('allow_create', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('allow_assign_group', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('allow_assign_user', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('bypass_required', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            ///////////
            // TODO: Eliminar les 4 columnes següents quan es faci servir la taula VTRAMIT_PROCEDURE_TYPE_STATUS_GROUP
            $table->addColumn('notify_stakeholder_template_id', Types::INTEGER, [ // DEPRECATED on 2.2.0
                'notnull' => false,
                'default' => NULL,
                'unsigned' => true
            ]);
            $table->addColumn('notify_staff_template_id', Types::INTEGER, [       // DEPRECATED on 2.2.0
                'notnull' => false,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('notify_stakeholder_template', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('notify_staff_template', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('vc_confirmation_form', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            //
            ///////////

            $table->setPrimaryKey(['id'], Tables::PROCEDURE_TYPE_GROUP . '_pk');
            $table->addIndex(['group'], Tables::PROCEDURE_TYPE_GROUP . '_ix');
        }

        return $schema;
    }

    private function createTableProcedureTypeStatus($schema) {
        if (!$schema->hasTable(Tables::PROCEDURE_TYPE_STATUS)) {
            $table = $schema->createTable(Tables::PROCEDURE_TYPE_STATUS);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('procedure_type_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('status_name', Types::STRING, [
                'notnull' => true,
                'length' => 200
            ]);
            // TODO: Pensar si sustituimos next_status por una tabla vtproc_type_status_map
            $table->addColumn('next_status', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('is_initial_status', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('is_final_status', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('allow_upload', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('allow_download', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('allow_signature', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('allow_video', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('notify_stakeholder', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('notify_staff', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('procedure_anonymization_period', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => false,
                'comment' => 'in hours; use -1 to inherit procedure type value; use 0 for never'
            ]);
            $table->addColumn('files_deletion_period', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => false,
                'comment' => 'in hours; use -1 to inherit procedure type value; use 0 for never'
            ]);
            $table->addColumn('procedure_deletion_period', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => false,
                'comment' => 'in hours; use -1 to inherit procedure type value; use 0 for never'
            ]);

            $table->setPrimaryKey(['id'], Tables::PROCEDURE_TYPE_STATUS . '_pk');
            $table->addIndex(['procedure_type_id'], Tables::PROCEDURE_TYPE_STATUS . '_ix');
        }

        return $schema;
    }

    private function createTableProcedureTypeStatusGroup($schema) {
        if (!$schema->hasTable(Tables::PROCEDURE_TYPE_STATUS_GROUP)) {
            $table = $schema->createTable(Tables::PROCEDURE_TYPE_STATUS_GROUP);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('procedure_type_status_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('procedure_type_group_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
                'comment' => 'use 0 to apply to all non-defined groups'
            ]);
            $table->addColumn('notify_stakeholder_template_id', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('notify_staff_template_id', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('vc_confirmation_form', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);

            $table->setPrimaryKey(['id'], Tables::PROCEDURE_TYPE_STATUS_GROUP . '_pk');
            $table->addIndex(['procedure_type_status_id', 'procedure_type_group_id'], Tables::PROCEDURE_TYPE_STATUS_GROUP . '_ix');
        }

        return $schema;
    }

    private function createTableProcedure($schema) {
        if (!$schema->hasTable(Tables::PROCEDURE)) {
            $table = $schema->createTable(Tables::PROCEDURE);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('procedure_type_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('external_id', Types::STRING, [
                'notnull' => false,
                'length' => 200
            ]);
            $table->addColumn('assigned_group_id', Types::STRING, [
                'notnull' => false,
                'length' => 64
            ]);
            $table->addColumn('assigned_user_id', Types::STRING, [
                'notnull' => false,
                'length' => 64
            ]);
            $table->addColumn('topic', Types::STRING, [
                'notnull' => false,
                'length' => 255
            ]);
            $table->addColumn('comments', Types::TEXT, [
                'notnull' => false
            ]);
            $table->addColumn('staff_uploads_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('staff_downloads_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('uploads_qty', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'length' => 16
            ]);
            $table->addColumn('downloads_qty', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'length' => 16
            ]);
            $table->addColumn('staff_signature_uploads_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('staff_signature_downloads_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('jitsi_room_code', Types::STRING, [
                'notnull' => false,
                'length' => 1024
            ]);
            $table->addColumn('jitsi_room_staff_url', Types::STRING, [
                'notnull' => false,
                'length' => 1024
            ]);
            $table->addColumn('status_id', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true
            ]);
			$table->addColumn('status_date', Types::DATETIME, [
				'notnull' => false,
			]);
            $table->addColumn('attendance_channel', Types::INTEGER, [
                'notnull' => false,
                'default' => 0,
                'unsigned' => true,
                'comment' => '1=video; 2=phone; 3=face-to-face; 4=chat; 5=offline'
            ]);
			$table->addColumn('appointment_date', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('deadline_date', Types::DATETIME, [
				'notnull' => false,
			]);
            $table->addColumn('folders_owner', Types::STRING, [
                'notnull' => false,
                'length' => 64
            ]);
			$table->addColumn('modification_date', Types::DATETIME, [
				'notnull' => false,
			]);
            $table->addColumn('modification_user', Types::STRING, [
                'notnull' => false,
                'length' => 64
            ]);

            $table->setPrimaryKey(['id'], Tables::PROCEDURE . '_pk');

        }

        return $schema;
    }

    private function createTableProcedureStakeholder($schema) {
        if (!$schema->hasTable(Tables::PROCEDURE_STAKEHOLDER)) {
            $table = $schema->createTable(Tables::PROCEDURE_STAKEHOLDER);
            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('procedure_id', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true
            ]);
            $table->addColumn('personal_id', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 64
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 120
            ]);
            $table->addColumn('phone', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 20
            ]);
            $table->addColumn('email', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 320
            ]);
            $table->addColumn('stakeholder_uploads_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('stakeholder_downloads_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('jitsi_room_stakeholder_url', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('attendance_channel', Types::INTEGER, [
                'notnull' => false,
                'default' => 0
            ]);
            $table->addColumn('entry_page', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 1024
            ]);
            $table->addColumn('entry_password', Types::STRING, [
                'notnull' => false,
                'default' => NULL,
                'length' => 512
            ]);
			$table->addColumn('deadline_date', Types::DATETIME, [
				'notnull' => false,
                'default' => NULL,
			]);
            $table->addColumn('is_main', Types::BOOLEAN, [
                'notnull' => false,
                'default' => false
            ]);
            $table->addColumn('deck_card_id', Types::INTEGER, [
                'notnull' => false,
                'default' => 0
            ]);

            $table->setPrimaryKey(['id'], Tables::PROCEDURE_STAKEHOLDER . '_pk');
            $table->addIndex(['procedure_id'], Tables::PROCEDURE_STAKEHOLDER . '_proc_ix');
            $table->addIndex(['personal_id'], Tables::PROCEDURE_STAKEHOLDER . '_person_ix');
        }

        return $schema;
    }
}
