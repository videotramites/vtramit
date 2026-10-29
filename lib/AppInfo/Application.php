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


/**
 * @OA\Info(
 *     title="VTramit API",
 *     version="2.0.0",
 *     description="VTramit API for procedures management"
 * )
 * @OA\Server(
 *     url="https://vtramit.example.com/index.php/apps/vtramit",
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="basicAuth",
 *     scheme="basic"
 * )
 */

// declare(strict_types=1);

namespace OCA\VTramit\AppInfo;

use OCA\VTramit\EventListener\CspListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

class Application extends App implements IBootstrap {
    public const APP_ID = 'vtramit';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        // Globally inject `worker-src 'self'` into the default CSP so that
        // NC's files-app preview Service Worker can register on share
        // pages (which vtramit cannot directly modify). See CspListener
        // for the full background.
        $context->registerEventListener(AddContentSecurityPolicyEvent::class, CspListener::class);
    }

    public function boot(IBootContext $context): void {
        // No-op.
    }
}
