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

namespace OCA\VTramit\Helper;

use DateTime;

use Psr\Log\LoggerInterface;

use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCA\VTramit\Service\ConfigService;

class BeHelper {

    /**
     * Indicates wether the given string is an enconded JSON string or not.
     *
     * @param string $str  string to test
     * @return bool        true if given string is an encoded JSON string; otherwise, false
     */
    public static function isJson($str) {
        $json = json_decode($str);
        return $json && $str != $json;
    }

    /**
     * Transforms a plain array into a key-value array for creating JSON entries.
     *
     * @param array $plainArray  plain array to transform like [key => value, key => value, ...]
     * @return array             the new array, like [0 => ["key" => key, "value" => value], 1 => ["key" => key, "value" => value], ...]
     */
    public static function arrayToKeyValue(array $plainArray): array {
        $newArray = [];

        foreach($plainArray as $key => $value) {
            $newArray[] = ["key" => $key, "value" => $value];
        }

        return $newArray;
    }

    /**
     * Computes a recursive diff between two arrays without converting values to strings.
     *
     * @param array $newData
     * @param array $oldData
     * @return array
     */
    public static function arrayDiff(array $newData, array $oldData): array {
        $diff = [];

        foreach ($newData as $key => $value) {
            if (!array_key_exists($key, $oldData)) {
                $diff[$key] = $value;
            }
            elseif (is_array($value) && is_array($oldData[$key])) {
                $subDiff = self::arrayDiff($value, $oldData[$key]);
                if (!empty($subDiff)) {
                    $diff[$key] = $subDiff;
                }
            }
            elseif ($value !== $oldData[$key]) {
                $diff[$key] = $value;
            }
        }

        return $diff;
    }


    //
    // Encrypt/Decrypt Functions
    ////////////////////////////////////////////////////////////////////////////////

    public static function encrypt($pure_string, $encryption_key) {
        $cipher     = 'AES-256-CBC';
        $options    = OPENSSL_RAW_DATA;
        $hash_algo  = 'sha256';
        $sha2len    = 32;
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($pure_string, $cipher, $encryption_key, $options, $iv);
        $hmac = hash_hmac($hash_algo, $ciphertext_raw, $encryption_key, true);
        return bin2hex($iv.$hmac.$ciphertext_raw);
    }

    public static function decrypt($encrypted_string, $encryption_key) {
        $encrypted_string = hex2bin($encrypted_string);
        $cipher     = 'AES-256-CBC';
        $options    = OPENSSL_RAW_DATA;
        $hash_algo  = 'sha256';
        $sha2len    = 32;
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($encrypted_string, 0, $ivlen);
        $hmac = substr($encrypted_string, $ivlen, $sha2len);
        $ciphertext_raw = substr($encrypted_string, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $encryption_key, $options, $iv);
        $calcmac = hash_hmac($hash_algo, $ciphertext_raw, $encryption_key, true);
        if (function_exists('hash_equals')) {
            if (hash_equals($hmac, $calcmac)) return $original_plaintext;
        } else {
            if (BeHelper::decrypt_hash_equals($hmac, $calcmac)) return $original_plaintext;
        }

        return null;
    }

    /**
     * hash_equals() function polyfilling.
     * PHP 5.6+ timing attack safe comparison
     */
    private static function decrypt_hash_equals($knownString, $userString) {
        if (function_exists('mb_strlen')) {
            $kLen = mb_strlen($knownString, '8bit');
            $uLen = mb_strlen($userString, '8bit');
        } else {
            $kLen = strlen($knownString);
            $uLen = strlen($userString);
        }
        if ($kLen !== $uLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $kLen; $i++) {
            $result |= (ord($knownString[$i]) ^ ord($userString[$i]));
        }
        return 0 === $result;
    }


    //
    // Functions for random
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * This function uses type hints now (PHP 7+ only), but it was originally
     * written for PHP 5 as well.
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * See:
     *   - https://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425
     *
     * @param int $length      How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    public static function random_string(
        int $length = 64,
        string $additionalCharacters = ''
        //string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#_,.()[]'
    ): string {
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'.$additionalCharacters;

        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public static function random_string_with_special_chars(int $length = 64) {
        return BeHelper::random_string($length, '@#_,.()[]');
    }

    public static function getRandomPIN() {
        $pin = rand(10000000, 99999999);
        return $pin;
    }

    public static function getRandomPhone(string $countryCode = "", string $prefix = "") {
        $phone = rand(100000000, 999999999);
        return self::normalizePhone($phone, $countryCode, $prefix);
    }

    public static function getRandomNIF() {
        $nif = rand(10000000, 99999999) . chr(rand(65,90));
        return $nif;
    }


    //
    // Date/Time Functions
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Obtain the date from a relative interval calculated using a given moment.
     *
     * You can use 'auto' for the 'relativeTo' parameter. In that case it will be used 'now' if interval
     * is defined in seconds, minutes or hours; otherwise, it will be used 'today'.
     *
     * For the 'interval' parameter check the "Used Symbols" in:
     * https://www.php.net/manual/es/datetime.formats.relative.php
     *
     * For the 'relativeTo' parameter check the "Day-base Notations" in:
     * https://www.php.net/manual/es/datetime.formats.relative.php
     *
     * @param string    $interval    the interval to modify the date
     * @param \TimeZone $timezone    the date timezone
     * @param string    $relativeTo  the moment from which calculate the new date using the interval
     */
    public static function getFromRelativeDate($interval, $timezone, $relativeTo = 'now'): \DateTime {
        if (strpos($interval, ' ') !== false) {
            if ($relativeTo == 'auto') {
                list(, $timepart) = explode(' ', $interval); // year, month, day, hour, minute, second
                switch($timepart) {
                    case 'hour':
                    case 'minute':
                    case 'second':
                        $date = new \DateTime('now', $timezone);
                        break;

                    default:
                        $date = new \DateTime('today', $timezone);
                }
            }
            else {
                $date = new \DateTime($relativeTo, $timezone);
            }
            $date->modify($interval);
        }
        else {
            $date = new \DateTime($interval, $timezone);
        }

        return $date;
    }


    //
    // Normalization
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Check if a phone number string contains enough digits to be considered valid.
     *
     * A phone is considered valid when it contains at least 7 digits (after stripping
     * non-numeric characters). This covers the shortest valid national numbers.
     *
     * @param string $phone  the raw phone input to validate
     * @return bool          true if the phone has enough digits, false otherwise
     */
    public static function isValidPhone(string $phone): bool {
        if (empty($phone)) {
            return false;
        }
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) >= 7;
    }

    /**
     * Normalize Phone for international prefix.
     *
     * @param string $phone
     * @return string $phone
     */
    public static function normalizePhone(string $phone, string $countryCode = "", string $prefix = "") {
        if (empty($phone)) {
            return $phone;
        }

        if (!preg_match("/^\+/", $phone)) {
            $phone = preg_replace("/[^\d]/", '', $phone);

            // Normalize to Standard ITU-T E.164 ( i.e. 34123456789 )
            if (strlen($phone) <= 10) {
                $phone = $countryCode.$phone;
            }
        }
        else {
            $phone = preg_replace("/[^\d]/", '', $phone);
        }

        return $prefix.$phone;
    }


    //
    // Matching
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Rank a candidate name vs search needle.
     * Exact (case-insensitive) > startsWith > word-boundary contains > contains > textual similarity.
     *
     * @param string $needle Lowercased search
     * @param string $candidate Original candidate
     * @return float 0..100
     */
    public static function scoreNameMatch(string $needle, string $candidate): float {
        $cand = mb_strtolower($candidate);

        if ($cand === $needle) return 100.0;
        if (str_starts_with($cand, $needle)) return 95.0;

        if (preg_match('/\b' . preg_quote($needle, '/') . '\b/u', $cand)) return 90.0;

        if (str_contains($cand, $needle)) return 80.0;

        $pct = 0.0;
        similar_text($needle, $cand, $pct);
        return max(0.0, min(75.0, $pct));
    }
}