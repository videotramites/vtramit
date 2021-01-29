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

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\VTramit\Service\VideoconferenceService;
use OCA\VTramit\Db\Videoconference;

class VideoconferenceController extends Controller {
    /** @var VideoconferenceService */
    private $service;

    /** @var string */
    private $userId;


    use Errors;

    public function __construct(
        $appName,
        IRequest $request,
        VideoconferenceService $service,
        $userId
    ) {

        parent::__construct($appName, $request);
        $this->service = $service;
        $this->userId = $userId;
    }

    /**
     * By default, response is empty. We need something to filter.
     *
     * @NoAdminRequired
     */
    public function index(): DataResponse {
        return new DataResponse([]);
    }

}