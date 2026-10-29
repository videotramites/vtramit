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

namespace OCA\VTramit\Service;

use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Exception\EntityNotFoundException;

class ProcedureVideoconferenceService {

    public function __construct(
        protected ProcedureTypeGroupService $procedureTypeGroupService,
        protected ProcedureTokenService $procedureTokenService,
    // @codeCoverageIgnoreStart
    ) {}
    // @codeCoverageIgnoreEnd

    /**
     * Get the confirmation form for the given entity.
     *
     * @param Procedure $procedure             the procedure object for which get the confirmation form
     * @param Procedure $procedureStakeholder  the procedure stakeholder for which get the confirmation form
     * @return string|null                     the confirmation form URL
     * @throws EntityNotFoundException|DataNotFoundException
     */
    public function getVideoconferenceConfirmationForm(Procedure $procedure, ProcedureStakeholder $procedureStakeholder): ?string {
        if (empty($procedure->getAssignedGroupId())) {
            return null;
        }

        try {
            $response = $this->procedureTypeGroupService->findByProcedureAndGroup($procedure, $procedure->getAssignedGroupId());
            $procedureTypeGroup = $response->getInfo('entity');
        }
        catch (EntityNotFoundException $e) {
            // If no entity found for the procedure group, then try to find the entity
            // for the general group (group = '')
            $response = $this->procedureTypeGroupService->findByProcedureAndGeneralGroup($procedure);
            $procedureTypeGroup = $response->getInfo('entity');
        }

        $template = $procedureTypeGroup->getVcConfirmationForm();
        return $this->procedureTokenService->applyReplacements($procedure, $procedureStakeholder, $template);
    }
}
