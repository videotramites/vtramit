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

namespace OCA\VTramit\Db;

use OCP\IDBConnection;

use OCA\VTramit\Db\Tables;
use OCA\VTramit\Exception\EntityNotFoundException;

class MailTemplateMapper extends EntityMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, Tables::MAIL_TEMPLATE, MailTemplate::class, 'mail template');
    }

    /**
     * Obtain the entity given its unique identifier.
     *
     * @param int $id
     * @return MailTemplate
     * @throws OCA\VTramit\Exception\EntityNotFoundException
     */
    public function find(int $id): MailTemplate {
        return parent::find($id);
    }
}