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
use OCP\IL10N;

use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCA\VTramit\Service\ConfigService;

class CurlHelper {

    /** @var LoggerInterface */
    private $logger;

    /** @var IL10N */
    private $translate;

    /** @var ConfigService */
    private $configService;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ConfigService $configService
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->configService = $configService;
    }

    function request(
        $submission_url,
        $username = null,
        $password = "",
        $data = null,
        $header = array(),
        $customRequest = null
    ) {
        if (!is_array($header)) {
            $header = array($header);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $submission_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, true);

            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $header[] = 'Content-Type: application/json';
            }
            elseif (BeHelper::isJson($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $header[] = 'Content-Type: application/json';
            }
            else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        if (!empty($customRequest)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
        }

        if (!empty($username)) {
            $header[] = 'Authorization: Basic ' . base64_encode($username.':'.$password);
        }
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        if (BeHelper::isJson($response)) {
            return json_decode($response);
        }

        $values = array();
        $indexes = array();
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $response, $values, $indexes);
        xml_parser_free($parser);

        $result = array();
        foreach($indexes as $key => $data) {
            foreach($data as $value) {
                $result[$key][] = $values[$value];
            }
        }

        return $result;
    }
}
