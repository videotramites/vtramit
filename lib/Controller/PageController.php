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
 use OCP\AppFramework\Http\Template\PublicTemplateResponse;
 use OCP\AppFramework\Http\TemplateResponse;
 use OCP\AppFramework\Controller;
 use OCP\IL10N;
 
 use OCA\VTramit\Service\AppointmentService;
 
 class PageController extends Controller {

    /** @var AppointmentService */
    private $service;

    /** @var IL10N */
    private $translate;

    public function __construct(
        $appName, 
        IRequest $request, 
        AppointmentService $service,
        IL10N $translate
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->translate = $translate;
     }

     /**
      * @NoAdminRequired
      * @NoCSRFRequired
      */
     public function index() {
         return new TemplateResponse('vtramit', 'main');
     }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
      */
    public function public(string $jitsiRoomCode) {
        $appointment = $this->service->getVideoconferenceLinksByRoomcode($jitsiRoomCode);
        $template = new PublicTemplateResponse('vtramit', 'public', $appointment);
        $template->setHeaderTitle('V-Tramit');
        $template->setHeaderDetails($this->translate->t('Access links to the platform'));

        return $template;
    }
}
