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

namespace OCA\VTramit\Enum;

use OCP\IL10N;

enum MessageChannel: string {
    use EnumComparableTrait;

    case EMAIL    = 'email';
    case SMS      = 'sms';
    case COMMAND  = 'command';

    /**
     * Tell whether the recipient is required for the current channel.
     *
     * @return bool
     */
    public function isRecipientRequired(): bool {
        return match ($this) {
            self::EMAIL   => true,
            self::SMS     => true,
            self::COMMAND => false,
        };
    }

    /**
     * Validate the recipient according to the current channel rules.
     *
     * @param mixed $recipient
     *   The recipient to validate (string or any value that could be empty()).
     *
     * @return bool
     */
    public function isRecipientValid(mixed $recipient): bool {
        if (!$this->isRecipientRequired()) {
            return true;
        }
        return !empty($recipient);
    }

    /**
     * Get the list of status with their meaning translated.
     *
     * @return array  list of statuses and translated descriptions
     */
    public static function getDetails(IL10N $translate): array {
        return [
            [
                "key"                     => self::EMAIL->value,
                "value"                   => $translate->t('E-mail'),
                "filterable"              => false,
                "availableForStaff"       => true,
                "availableForStakeholder" => true,
                "isRecipientRequired"     => self::EMAIL->isRecipientRequired(),
                "editor"                  => EditorType::EDITOR_RICH,
            ],
            [
                "key"                     => self::SMS->value,
                "value"                   => $translate->t('SMS'),
                "filterable"              => false,
                "availableForStaff"       => false,
                "availableForStakeholder" => true,
                "isRecipientRequired"     => self::SMS->isRecipientRequired(),
                "editor"                  => EditorType::EDITOR_PLAIN,
            ],
            [
                "key"                     => self::COMMAND->value,
                "value"                   => $translate->t('Command'),
                "filterable"              => false,
                "availableForStaff"       => false,
                "availableForStakeholder" => false,
                "isRecipientRequired"     => self::COMMAND->isRecipientRequired(),
                "editorType"              => EditorType::EDITOR_PLAIN,
            ],
        ];
    }
}
