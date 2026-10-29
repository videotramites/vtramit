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

class JwtHelper {
    /**
     * Generates a JWT token for Jitsi.
     *
     * References:
     *   https://www.jwt.io/
     *   https://phpbuilder.com/using-a-json-web-token-in-php/
     *
     * @param array $payload The payload of the JWT, containing the claims and data.
     *                       This should include data like room name, user info, etc.
     * @param string $secretKey The secret key used to sign the JWT.
     *
     * @return string The generated JWT token.
     */
    public function generateJitsiJWT(array $payload, string $secretKey): string {
        // Encode the header
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $encodedHeader  = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));

        // Encode the payload
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        // Create the signature
        $signature = hash_hmac('SHA256', "$encodedHeader.$encodedPayload", $secretKey, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        // Combine all parts to form the JWT
        return "$encodedHeader.$encodedPayload.$encodedSignature";
    }

    /**
     * URL-safe base64 without padding ('='). Required by JWT.
     */
    private function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}