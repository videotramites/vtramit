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
use OCA\VTramit\Helper\BeHelper;

class ProcedureTokenService {

    /**
     * Constructs the ProcedureTokenService.
     *
     * @param ConfigService $configService
     * @param TopicService $topicService
     */
    public function __construct(
        protected ConfigService $configService,
        protected TopicService $topicService,
    // @codeCoverageIgnoreStart
    ) {}
    // @codeCoverageIgnoreEnd

    /**
     * Get the list of replacements for the email.
     *
     * @param Procedure            $procedure             procedure to notify
     * @param ProcedureStakeholder $procedureStakeholder  stakeholder who should receive notification
     * @return array                                      list of replacements
     */
    public function getListOfReplacements(Procedure $procedure, ?ProcedureStakeholder $procedureStakeholder): array {
        $replacements = [];

        $replacements['%procedureId%'] = $procedure->getId();
        $replacements['%group%'] = $procedure->getAssignedGroupId();
        $replacements['%date%'] = $procedure->getFormattedAppointmentDate($this->configService->getDateTimezone(), $this->configService->getDateFormat());
        $replacements['%time%'] = $procedure->getFormattedAppointmentDate($this->configService->getDateTimezone(), $this->configService->getTimeFormat());
        $replacements['%externalId%'] = $procedure->getExternalId();
        $replacements['%urlStaffVideocall%'] = $procedure->getJitsiRoomStaffUrl();
        $replacements['%jwtExpiryTimestamp%'] = $procedure->getAppointmentExpiryTimestamp($this->configService->getDateTimezone());
        $replacements['%topic%'] = $procedure->getTopic();
        $replacements['%topicHelp%'] = $this->topicService->getTopicHelpUrlByProcedure($procedure);
        // Aliased to %topicHelp% (older) for clarity in newer templates.
        $replacements['%topicHelpUrl%'] = $replacements['%topicHelp%'];
        // Surface oc_vttopic.min_required_docs so templates that warn the
        // citizen about missing uploads can say "you must upload N files".
        // Empty string when no topic is bound or the topic has no min set,
        // matching the str_replace null-coalescing behaviour of the engine.
        $replacements['%topicMinFiles%'] = $this->topicService->getTopicMinRequiredDocsByProcedure($procedure) ?: '';

        if (!is_null($procedureStakeholder)) {
            $replacements['%urlStakeholderVideocall%'] = $procedureStakeholder->getJitsiRoomStakeholderUrl();
            $replacements['%userPersonalId%'] = $procedureStakeholder->getPersonalId();
            $replacements['%userName%'] = $procedureStakeholder->getName();
            $replacements['%userPhone%'] = $procedureStakeholder->getPhone();
            $replacements['%userEmail%'] = $procedureStakeholder->getEmail();
            $replacements['%uploadsUrl%'] = $procedureStakeholder->getStakeholderUploadsUrl();
            $replacements['%downloadsUrl%'] = $procedureStakeholder->getStakeholderDownloadsUrl();
            $replacements['%userEntryPageUrl%'] = $procedureStakeholder->getEntryPage();
            $replacements['%password%'] = $procedureStakeholder->getEntryPassword($this->configService->getEncryptionKey());
        }

        //$replacements['%random:?%'] = 'random number of given long';

        return $replacements;
    }

    /**
     * Applies the replacement map to a template string.
     *
     * @param Procedure                 $procedure             The procedure entity used to extract common values.
     * @param ProcedureStakeholder|null $procedureStakeholder  Optional stakeholder entity used to extract per-user values.
     * @param string                    $template              The template string containing tokens like %token%.
     *
     * @return string  The template with all known tokens replaced. Unknown tokens are left
     *                 untouched. Null values are replaced with empty strings.
     */
    public function applyReplacements(Procedure $procedure, ?ProcedureStakeholder $procedureStakeholder, string $template): string {
        $replacements = $this->getListOfReplacements($procedure, $procedureStakeholder);
        $search = array_keys($replacements);
        $replace = [];
        foreach($search as $key) {
            $replace[] = is_null($replacements[$key]) ? '' : $replacements[$key];
        }

        $result = str_replace($search, $replace, $template);

        // Detect and replace dynamic %random:?% tokens
        if (preg_match_all('/%random:(\d+)%/', $result, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $length = (int) $match[1];
                $randomValue = BeHelper::random_string($length);
                $result = str_replace($match[0], $randomValue, $result);
            }
        }

        return $result;
    }
}
