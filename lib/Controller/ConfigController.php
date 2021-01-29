<?php
/**
 * @copyright Copyright (c) 2020-2021 Ajuntament de Barcelona
 * 
 * @author Daniel Tamajon <daniel@floss.cat>
 * @author Kenneth Peiruza <kenneth@floss.cat>
 * @author Letizia Benítez <letizia@floss.cat>
 * @author Marta González <gonzalez.marta@gmail.com>
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

namespace OCA\VTramit\Controller;

use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\IRequest;
use OCP\AppFramework\Controller;

use OCA\VTramit\Service\ConfigService;

class ConfigController extends Controller {
    /** @var ConfigService */
    private $ConfigService;

    /** @var string */
    private $userId;

    public function __construct(
        $AppName,
        IRequest $request,
        ConfigService $configService,
        $userId
    ) {
        parent::__construct($AppName, $request);

        $this->userId = $userId;
        $this->configService = $configService;
    }

    /**
     * @NoCSRFRequired
     */
    public function get() {
        return new DataResponse($this->configService->get());
    }

    /**
     * @NoCSRFRequired
     */
    public function setValue($key, $value) {
        $result = $this->configService->setValue($key, $value);

        if ($result === null) {
            return new NotFoundResponse();
        }
        return new DataResponse($result);
    }
}
