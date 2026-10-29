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

/**
 * Trait for comparing enum cases and converting from strings.
 *
 * Intended for string-backed enums used within the connector module.
 */
trait EnumComparableTrait {

  /**
   * Compares the current enum instance with another of the same type
   * based on their internal string value.
   *
   * This method does not compare identity (`===`), but rather checks
   * if both enum cases represent the same underlying value.
   *
   * @param self $other
   *   The other enum case to compare against.
   *
   * @return bool
   *   TRUE if both enums have the same value, FALSE otherwise.
   */
  public function equals(self $other): bool {
    return $this->value === $other->value;
  }

  /**
   * Compares two enum cases based on their internal values.
   *
   * @param self $a
   *   The first enum case to compare.
   * @param self $b
   *   The second enum case to compare.
   *
   * @return bool
   *   TRUE if both enums have the same value, FALSE otherwise.
   */
  public static function equalsTo(self $a, self $b): bool {
    return $a->value === $b->value;
  }

  /**
   * Converts a string to an enum case, case-insensitively.
   *
   * @param string $value
   *   The string value to convert.
   * @param self|null $default
   *   The default value to return if no match is found.
   *
   * @return self|null
   *   The matching enum case or the provided default.
   */
  public static function fromString(string $value, self $default = null): ?self {
    foreach (self::cases() as $case) {
      if (strcasecmp($case->value, $value) === 0) {
        return $case;
      }
    }

    return $default;
  }

  /**
   * Attempts to convert a string to an enum case, case-insensitively.
   *
   * This method performs a case-insensitive comparison between the input string
   * and the enum case values. If a matching case is found, it is returned;
   * otherwise, null is returned.
   *
   * @param string $value
   *   The string value to match against the enum values.
   *
   * @return self|null
   *   The matching enum case if found, or null if no match is found.
   *
   * @example
   *   MessageStatus::tryFromString('error');    // returns MessageStatus::ERROR
   *   MessageStatus::tryFromString('ERROR');    // returns MessageStatus::ERROR
   *   MessageStatus::tryFromString('invalid');  // returns null
   */
  public static function tryFromString(string $value): ?self {
    foreach (self::cases() as $case) {
      if (strcasecmp($case->value, $value) === 0) {
        return $case;
      }
    }
    return null;
  }

}