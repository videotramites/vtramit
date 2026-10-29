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

use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\AppFramework\Db\Entity;

use OCA\VTramit\Db\Webhook;
use OCA\VTramit\Db\WebhookMapper;
use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Helper\BeHelper;
use OCA\VTramit\Service\ConfigService;


class WebhookService extends EntityService  {

    /** @var WebhookMapper */
    private $webhookMapper;


    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ActivityManager $activityManager,
        ConfigService $configService,
        WebhookMapper $webhookMapper
    ) {
        parent::__construct($logger, $translate, $activityManager, $configService, $webhookMapper);
    }

    /**
     * Creates and obtain a new entity.
     */
    protected function newEntity() {
        return new Webhook();
    }

    //
    // Webhook management
    ////////////////////////////////////////////////////////////////////////////////
    public function event(string $operation, string $dataType, Entity $data) {
        $webhooks = $this->entityMapper->findByOperation($operation);
        foreach ($webhooks as $webhook) {
            $this->emit($operation, $dataType, $data, $webhook);
        }
    }

    private function emit(string $operation, string $dataType, Entity $data, $row) {
        $submission_url = $row->getEndpoint()?? '';
        $authTypeData = $row->getAuthTypeDataList();
        $username = $authTypeData['user'] ?? '';
        $password = $authTypeData['password'] ?? '';
        $token = $authTypeData['token'] ?? '';
        $crudOperation = '';

        if (str_contains(strtolower($operation), Webhook::CREATE)) {
            $crudOperation = Webhook::CREATE;
        }
        if (str_contains(strtolower($operation), Webhook::UPDATE)) {
            $crudOperation = Webhook::UPDATE;
        }
        if (str_contains(strtolower($operation), Webhook::DELETE)) {
            $crudOperation = Webhook::DELETE;
        }

        if (empty($submission_url) ||
            empty($data) ||
            empty($dataType) ||
            empty($row->getAuthType()) ||
            ($row->getAuthType() == Webhook::BASIC_AUTH && (empty($username) || empty($password))) ||
            ($row->getAuthType() == Webhook::TOKEN && empty($token)) ||
            empty($crudOperation)
            ) {
                $this->logger->error('One of the necessary information to call a webhook is missing. The call was not made. Check the data that was present at the moment in the details',
                [
                    'app' => 'vtramit',
                    'details' => [
                        'submission URL' => $submission_url,
                        'auth type' => $row->getAuthType(),
                        'type of operation done on entity' => $operation,
                        'calculated crud operation for webhook' => $crudOperation,
                        'entity type' => $dataType,
                        'auth user' => $username,
                        'auth pass' => empty($password) ? '' : '****',
                        'token' => $token,
                        'entity' => $data
                    ]
                ]);

                return;
        }

        $info = [
            'object' => $dataType,
            'operation' => $crudOperation,
            'data' => $data
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $submission_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($info));
        $header[] = 'Content-Type: application/json';

        if ($row->getAuthType() == Webhook::BASIC_AUTH && (!empty($username) && !empty($password))) {
            $header[] = 'Authorization: Basic ' . base64_encode($username.':'.$password);
        }
        if ($row->getAuthType() == Webhook::TOKEN && !empty($token)) {
            $header[] = 'Authorization: Bearer ' . $token;
        }
        if (!empty($header)) {
            if ($this->configService->getMode() == "test") {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        /* // No hacemos tratamiento de la respuesta
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
        */
    }
}
