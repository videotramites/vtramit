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

namespace OCA\VTramit\Controller;

use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;
use OCP\IL10N;
use OCP\Util;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FeaturePolicy;

use OCA\VTramit\AppInfo\Application;

use OCA\VTramit\ProcedureStatus;
use OCA\VTramit\Db\Procedure;
use OCA\VTramit\Db\ProcedureType;
use OCA\VTramit\Db\ProcedureTypeGroup;
use OCA\VTramit\Db\ProcedureTypeGroupMapper;
use OCA\VTramit\Db\ProcedureStakeholder;
use OCA\VTramit\Helper\NextcloudHelper;
use OCA\VTramit\Service\ProcedureService;
use OCA\VTramit\Service\ProcedureStakeholderService;
use OCA\VTramit\Service\RgpdOptionService;
use OCA\VTramit\Service\ConfigService;
use OCA\VTramit\Service\TopicService;

class PageController extends Controller {
    /** @var LoggerInterface */
    protected $logger;

    /** @var IL10N */
    private $translate;

    /** @var IUserSession */
    private $userSession;

    /** @var ConfigService */
    private ConfigService $config;

    /** @var RgpdOptionService */
    private $rgpdOptionService;

    /** @var TopicService */
    private $topicService;

    /** @var ProcedureService */
    private $procedureService;

    /** @var ProcedureStakeholderService */
    private $procedureStakeholderService;

    /** @var ProcedureTypeGroupMapper */
    private $procedureTypeGroupMapper;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        IL10N $translate,
        IUserSession $userSession,
        ConfigService $config,
        RgpdOptionService $rgpdOptionService,
        TopicService $topicService,
        ProcedureService $procedureService,
        ProcedureStakeholderService $procedureStakeholderService,
        ProcedureTypeGroupMapper $procedureTypeGroupMapper
    ) {
        parent::__construct($appName, $request);
        $this->logger = $logger;
        $this->translate = $translate;
        $this->userSession = $userSession;
        $this->config = $config;
        $this->rgpdOptionService = $rgpdOptionService;
        $this->topicService = $topicService;
        $this->procedureService = $procedureService;
        $this->procedureStakeholderService = $procedureStakeholderService;
        $this->procedureTypeGroupMapper = $procedureTypeGroupMapper;
    }

    /**
     * Returns the current authenticated user's UID or null if no session.
     *
     * @return string|null User ID (UID) or null when unauthenticated.
     */
    private function getUserId(): ?string {
        return $this->userSession->getUser()?->getUID();
    }

    /**
     * Default endpoint: redirects to the procedures view.
     *
     * @return \OCP\AppFramework\Http\TemplateResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index() {
        return $this->proceduresView();
    }

    /**
     * Renders the procedures list view.
     *
     * @return \OCP\AppFramework\Http\TemplateResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function proceduresView() {
        $fpolicy = new FeaturePolicy();
        $cspolicy = new ContentSecurityPolicy();

        // Enable configuring more CSP & Feature policies with a code snipplet
        if ( file_exists ( "apps/vtramit/templates/proceduresView-headers.php" ) ) {
            include ( "apps/vtramit/templates/proceduresView-headers.php" );
        }

        $template = new TemplateResponse('vtramit', 'proceduresView');
        $template->setContentSecurityPolicy($cspolicy);
        $template->setFeaturePolicy($fpolicy);

        if (NextcloudHelper::getMajorVersion() >= 25) {
            Util::addStyle(Application::APP_ID, 'overflow_nc25');
        }

        return $template;
    }

    /**
     * Public appointment page for stakeholders joining via Jitsi room code.
     *
     * @param string $jitsiRoomCode Jitsi room code identifying the appointment.
     * @return \OCP\AppFramework\Http\Template\PublicTemplateResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function public(string $jitsiRoomCode) {
        [$procedure, $stakeholder] = $this->findProcedureAndStakeholder($jitsiRoomCode);

        $data = $this->prepareData($procedure, $stakeholder);

        if (empty($procedure)) {
            // TODO: Redirect to an external page. Create a redirection page in configuration
            //return new RedirectResponse($target);
        }

        // Campos específicos del modo PUBLIC
        if (!empty($procedure) && !empty($stakeholder)) {
            $data['appointment'] = [
                'id'                 => $procedure->getId(), // Required to data acknowledge on frontend, not used for any other purporse.
                'date'               => $procedure->getAppointmentDateTimestamp($this->config->getDateTimezone()),
                'sharedUrlDownloads' => $stakeholder->getStakeholderDownloadsUrl(),
                'sharedUrlUploads'   => $stakeholder->getStakeholderUploadsUrl(),
                'jitsiRoomCode'      => $procedure->getJitsiRoomCode(),
                'jitsiRoomUrl'          => $stakeholder->getJitsiRoomStakeholderUrl(),
            ];
        }

        $config       = $data['config'];
        $jitsiRoomUrl = $data['appointment']['jitsiRoomUrl'] ?? null;

        $cspolicy = $this->prepareContentSecurityPolicy($jitsiRoomUrl, $config);
        $fpolicy  = $this->prepareFeaturePolicy($jitsiRoomUrl);

        // Enable configuring more CSP & Feature policies with a code snippet
        if ( file_exists ( "apps/vtramit/templates/publicPortal-headers.php" ) ) {
            include ( "apps/vtramit/templates/publicPortal-headers.php" );
        }

        $template = $this->preparePublicTemplate('publicPortal', $data, $cspolicy, $fpolicy);
        return $template;
    }

    /**
     * Public appointment page for stakeholders joining via Jitsi room code.
     *
     * @param string $jitsiRoomCode Jitsi room code identifying the appointment.
     * @return \OCP\AppFramework\Http\Template\PublicTemplateResponse
     */
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function attendant(string $jitsiRoomCode) {
        [$procedure, $stakeholder] = $this->findProcedureAndStakeholderByExternalId($jitsiRoomCode);
        if (empty($procedure)) {
            [$procedure, $stakeholder] = $this->findProcedureAndStakeholder($jitsiRoomCode);
        }

        $data = $this->prepareData($procedure, $stakeholder);
        $jitsiRoomUrl = null;

        // Campos específicos del modo ATTENDANT
        if (!empty($procedure)) {
            $data['procedure'] = $procedure;
            $data['procedureId'] = $procedure->id;
            $jitsiRoomUrl = $procedure->getFullJitsiRoomStaffUrl();
        }

        $config = $data['config'];

        $cspolicy = $this->prepareContentSecurityPolicy($jitsiRoomUrl, $config);
        $fpolicy  = $this->prepareFeaturePolicy($jitsiRoomUrl);

        // Enable configuring more CSP & Feature policies with a code snippet
        if ( file_exists ( "apps/vtramit/templates/attendantPortal-headers.php" ) ) {
            include ( "apps/vtramit/templates/attendantPortal-headers.php" );
        }

        $template = $this->preparePrivateTemplate('attendantPortal', $data, $cspolicy, $fpolicy);
        return $template;
    }


    //
    // Helpers for public and attendant
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Finds the first matching Procedure and Stakeholder for a given Jitsi room code.
     *
     * @param string $jitsiRoomCode Jitsi room code to filter by.
     * @param string $minDate       Min date filter (default 'today').
     * @return array{0:mixed|null,1:mixed|null} Array with [Procedure|null, Stakeholder|null].
     */
    private function findProcedureAndStakeholder(string $jitsiRoomCode, string $minDate = 'today'): array {
        $procedure = null;
        $stakeholder = null;

        $filter = [
            'jitsiRoomCode' => $jitsiRoomCode,
            'minDate'       => $minDate,
        ];

        // Find Procedure
        try {
            // Explicitily avoid using userId in the filter to avoid excluding anonymous users
            $response = $this->procedureService->findByFilter($filter, null, true);
            $list = $response->getInfo('list');
            /*if (!empty($list)) {
                $procedure = reset($list);
            }*/

            if (!empty($list)) {
                $procedure = $this->selectProcedureByStatusAndTime($list);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error finding procedure: ' . $e->getMessage(), ['code' => $jitsiRoomCode]);
        }

        // Find Stakeholder
        try {
            if (!empty($procedure)) {
                $response = $this->procedureStakeholderService->findMainByProcedureId($procedure->getId());
                $stakeholder = $response->getInfo('entity');
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error finding stakeholder: ' . $e->getMessage(), ['code' => $jitsiRoomCode]);
        }

        return [$procedure, $stakeholder];
    }

    /**
     * Finds the first matching Procedure and Stakeholder for a given Jitsi room code.
     *
     * @param string $externalId    External ID to filter by.
     * @param string $minDate       Min date filter (default 'today').
     * @return array{0:mixed|null,1:mixed|null} Array with [Procedure|null, Stakeholder|null].
     */
    private function findProcedureAndStakeholderByExternalId(string $externalId, string $minDate = 'today'): array {
        $procedure = null;
        $stakeholder = null;

        $filter = [
            'externalId' => $externalId,
            'minDate'    => $minDate,
        ];

        // Find Procedure
        try {
            // Explicitily avoid using userId in the filter to avoid excluding anonymous users
            $response = $this->procedureService->findByFilter($filter, null, true);
            $list = $response->getInfo('list');
            /*if (!empty($list)) {
                $procedure = reset($list);
            }*/

            if (!empty($list)) {
                $procedure = $this->selectProcedureByStatusAndTime($list);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error finding procedure: ' . $e->getMessage(), ['code' => $externalId]);
        }

        // Find Stakeholder
        try {
            if (!empty($procedure)) {
                $response = $this->procedureStakeholderService->findMainByProcedureId($procedure->getId());
                $stakeholder = $response->getInfo('entity');
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error finding stakeholder: ' . $e->getMessage(), ['code' => $externalId]);
        }

        return [$procedure, $stakeholder];
    }

    /**
     * Builds the full data array (config, appointment, stakeholder, rgpdOptions)
     * for the appointment.
     *
     * @param \OCA\VTramit\Db\Procedure|null            $procedure
     *   The procedure entity or null if not available.
     * @param \OCA\VTramit\Db\ProcedureStakeholder|null $stakeholder
     *   The procedure stakeholder entity or null if not available.
     *
     * @return array<string,mixed>
     *   Data array for the frontend, including config, procedureStakeholder, and rgpdOptions.
     */
    private function prepareData(
        ?Procedure $procedure = null,
        ?ProcedureStakeholder $stakeholder = null
    ): array {
        $data = [
            'config'               => [],
            'appointment'          => [],
            'procedureStakeholder' => [],
            'rgpdOptions'          => [],
        ];

        if (empty($procedure) || empty($stakeholder)) {
            return $data;
        }

        // Resolve procedure type
        $procedureType = $this->procedureService->getProcedureType($procedure);

        // Build config (includes topic help, signature, etc.)
        $config = $this->buildConfig($procedure, $procedureType);

        // Stakeholder serialization (common)
        $data['procedureStakeholder'] = [
            'id'          => $stakeholder->getId(),
            'procedureId' => $stakeholder->getProcedureId(),
            'jitsiRoom'   => $stakeholder->getJitsiRoomStakeholderUrl(),
            //'jitsiJwt'    => $jwt,
        ];

        // RGPD options
        $data['rgpdOptions'] = $this->rgpdOptionService->findByJitsiRoomCode($procedure->getJitsiRoomCode());

        // Attach config last
        $data['config'] = $config;

        return $data;
    }

    /**
     * Builds the configuration array for both public and attendant portal.
     *
     * @param object|null $procedure     Procedure entity or null if not available.
     * @param object|null $procedureType ProcedureType entity or null if not available.
     *
     * @return array<string,mixed> Configuration array for the front end.
     */
    private function buildConfig($procedure = null, $procedureType = null): array
    {
        $config = [];

        // General URLs
        $config['documentationUrl'] = $this->config->getPublicDocumentationUrl();
        $config['logoUrl']          = $this->config->getPublicLogoUrl();
        $config['logoLogin']        = $this->config->getPublicLogoLogin();

        // Texts
        $config['textExpired'] = $this->config->getPublicTextExpired();
        $config['textFuture']  = $this->config->getPublicTextFuture();

        // Visibility switches
        $config['passwordHide']  = $this->config->getPublicPasswordHide();
        $config['downloadsHide'] = $this->config->getPublicDownloadsHide();
        $config['uploadsHide']   = $this->config->getPublicUploadsHide();
        $config['signatureHide'] = $this->config->getPublicSignatureHide();
        $config['forceFullscreen'] = false;

        if ($this->hasRemoteCommandsEnabled($procedureType)) {
            $config['passwordHide']  = true;
            $config['downloadsHide'] = true;
            $config['uploadsHide']   = true;
            $config['signatureHide'] = true;
            $config['forceFullscreen'] = true;
        }

        // Features
        $config['isSignatureEnabled'] = !empty($procedureType) ? (bool) $procedureType->getAllowSignature() : false;
        $config['outputAutodownload'] = $this->config->getPublicOutputAutodownload();

        // Topic help
        $topicHelp = !empty($procedure) ? $this->topicService->getTopicHelpByProcedure($procedure) : null;
        $config['topicHelpUrl']    = $topicHelp['helpUrl']    ?? null;
        $config['topicHelpTarget'] = $topicHelp['helpTarget'] ?? null;

        // Languages
        $config['languages'] = $this->config->getDetailedAllowedLanguages();

        // Robot videocall test (URL only — token is never sent to frontend)
        $robotUrl = $this->config->getRobotTestUrl();
        $config['robotTestUrl'] = !empty($robotUrl) ? $robotUrl : null;
        // Pass all language variants — frontend picks the right one
        $languages = $this->config->getAllowedLanguages();
        $helpTexts = [];
        foreach ($languages as $lang) {
            $text = $this->config->getVideocallTestHelpText($lang);
            if (!empty($text)) {
                $helpTexts[$lang] = $text;
            }
        }
        $config['videocallTestHelpTexts'] = $helpTexts;

        // Default views (per procedure-type-group). Resolved from the
        // appointment's assigned group, falling back to the general group.
        // Empty string means "Auto" — the frontend keeps its own default.
        $config['defaultViewDesktopLeft']  = '';
        $config['defaultViewDesktopRight'] = '';
        $config['defaultViewMobile']       = '';
        $group = !empty($procedure) ? $this->resolveProcedureTypeGroup($procedure) : null;
        if (!empty($group)) {
            $config['defaultViewDesktopLeft']  = (string) $group->getDefaultViewDesktopLeft();
            $config['defaultViewDesktopRight'] = (string) $group->getDefaultViewDesktopRight();
            $config['defaultViewMobile']       = (string) $group->getDefaultViewMobile();
        }

        return $config;
    }

    /**
     * Resolve the ProcedureTypeGroup whose per-group configuration applies to
     * the given procedure: the appointment's assigned group if it has its own
     * configuration row, otherwise the "general" group (empty group name).
     *
     * @param Procedure $procedure
     * @return ProcedureTypeGroup|null  the applicable group, or null if none exists
     */
    private function resolveProcedureTypeGroup(Procedure $procedure): ?ProcedureTypeGroup {
        $assignedGroup = $procedure->getAssignedGroupId();
        if (!empty($assignedGroup)) {
            try {
                return $this->procedureTypeGroupMapper->findByProcedureAndGroup($procedure->getId(), $assignedGroup);
            } catch (\Throwable $e) {
                // No specific row for this group — fall back to the general one.
            }
        }

        try {
            return $this->procedureTypeGroupMapper->findByProcedureAndGeneralGroup($procedure->getId());
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Checks whether the given procedure type has remote commands enabled.
     *
     * @param ProcedureType|null $procedureType
     */
    private function hasRemoteCommandsEnabled(?ProcedureType $procedureType): bool {
        if (empty($procedureType)) {
            return false;
        }

        $properties = $procedureType->getAttendantPortalProperties();
        if (empty($properties)) {
            return false;
        }

        if (is_string($properties)) {
            $decoded = json_decode($properties, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $properties = $decoded;
            }
        } elseif ($properties instanceof \stdClass) {
            $properties = (array) $properties;
        }

        if (!is_array($properties)) {
            return false;
        }

        $value = $properties['allowRemoteCommands'] ?? null;
        $parsed = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $parsed === true;
    }

    /**
     * Prepares a FeaturePolicy allowing 'self' and, if provided, the Jitsi room origin
     * for camera, microphone, fullscreen, and autoplay.
     *
     * @param string|null $jitsiRoomUrl Absolute Jitsi room URL used to extract the allowed origin; null to allow only 'self'.
     * @return FeaturePolicy Configured FeaturePolicy instance.
     */
    private function prepareFeaturePolicy(?string $jitsiRoomUrl): FeaturePolicy {
        $policy = new FeaturePolicy();

        // Always allow self for camera/microphone
        $policy->addAllowedCameraDomain('\'self\'');
        $policy->addAllowedMicrophoneDomain('\'self\'');

        // Allow Jitsi room origin for required capabilities
        if (!empty($jitsiRoomUrl)) {
            $domain = $this->domainOnly($jitsiRoomUrl);
            $policy->addAllowedAutoplayDomain($domain);
            $policy->addAllowedCameraDomain($domain);
            $policy->addAllowedFullScreenDomain($domain);
            $policy->addAllowedMicrophoneDomain($domain);
        }

        return $policy;
    }

    /**
     * Creates a content security policy that allow us to open all the
     * required urls and view the contents of the iframes the frontend
     * will create.
     *
     * @param string|null $jitsiRoomUrl Absolute Jitsi room URL used to extract the allowed origin; null to skip.
     * @param array $config Configuration array containing documentationUrl, logoUrl, and logoLogin.
     * @return ContentSecurityPolicy Configured ContentSecurityPolicy instance.
     */
    private function prepareContentSecurityPolicy(?string $jitsiRoomUrl, array $config): ContentSecurityPolicy {
        $policy = new ContentSecurityPolicy();

        // Allow Service Workers from the same origin. Without an explicit
        // worker-src directive the browser falls back to script-src, which
        // is nonce-restricted on Nextcloud pages — service workers can't
        // carry a nonce, so they get blocked. Hits NC's files-app preview
        // Service Worker (preview-service-worker.js) on public pages.
        $policy->addAllowedWorkerSrcDomain("'self'");

        // Allow Jitsi origin for frames and scripts
        if (!empty($jitsiRoomUrl)) {
            $domain = $this->domainOnly($jitsiRoomUrl);
            $policy->addAllowedFrameDomain($domain);
            $policy->addAllowedScriptDomain($domain);
            $policy->addAllowedConnectDomain($domain);
        }

        // Allow common CDN/font providers
        $policy->addAllowedStyleDomain("https://cdn.jsdelivr.net/");
        $policy->addAllowedFontDomain("https://cdn.jsdelivr.net/");
        $policy->addAllowedConnectDomain('https://cdn.jsdelivr.net/');
        $policy->addAllowedStyleDomain("https://fonts.googleapis.com/");
        $policy->addAllowedFontDomain("https://fonts.googleapis.com/");
        $policy->addAllowedFontDomain("https://fonts.gstatic.com/");

        // Allow documentation and logos if configured
        if (!empty($config['documentationUrl'])) {
            $policy->addAllowedFrameDomain($this->domainOnly($config['documentationUrl']));
        }
        if (!empty($config['logoUrl'])) {
            $policy->addAllowedImageDomain($this->domainOnly($config['logoUrl']));
        }
        if (!empty($config['logoLogin'])) {
            $policy->addAllowedImageDomain($this->domainOnly($config['logoLogin']));
        }

        return $policy;
    }

    /**
     * Builds the template response with common options and policies, and disabling
     * Nextcloud headers and footers to have a full custom web page.
     *
     * @param string                   $templateName  Twig template name ('public' | 'attendant' | ...).
     * @param array<string,mixed>      $data          Payload to pass as ['data' => $data].
     * @param ContentSecurityPolicy    $csp           Content-Security-Policy already prepared.
     * @param FeaturePolicy            $fp            Feature-Policy already prepared.
     * @return TemplateResponse
     */
    private function preparePrivateTemplate(
        string $templateName,
        array $data,
        ContentSecurityPolicy $csp,
        FeaturePolicy $fp
    ): TemplateResponse {

        $template = new TemplateResponse('vtramit', $templateName, ['data' => $data]);
        $template->renderAs(TemplateResponse::RENDER_AS_BASE);
        $template->setContentSecurityPolicy($csp);
        $template->setFeaturePolicy($fp);

        return $template;
    }

    /**
     * Builds the template response with common options and policies, and disabling
     * Nextcloud headers and footers to have a full custom web page.
     *
     * @param string                   $templateName  Twig template name ('public' | 'attendant' | ...).
     * @param array<string,mixed>      $data          Payload to pass as ['data' => $data].
     * @param ContentSecurityPolicy    $csp           Content-Security-Policy already prepared.
     * @param FeaturePolicy            $fp            Feature-Policy already prepared.
     * @return PublicTemplateResponse
     */
    private function preparePublicTemplate(
        string $templateName,
        array $data,
        ContentSecurityPolicy $csp,
        FeaturePolicy $fp
    ): PublicTemplateResponse {

        $template = new PublicTemplateResponse('vtramit', $templateName, ['data' => $data]);
        $template->setFooterVisible(false);
        $template->renderAs(PublicTemplateResponse::RENDER_AS_BASE);
        $template->setContentSecurityPolicy($csp);
        $template->setFeaturePolicy($fp);

        return $template;
    }

    /**
     * Strips the path and query parameters from the URL.
     *
     * @param string $url
     * @return string
     */
    private function domainOnly($url) {
        $parsed_url = parse_url($url);
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host   = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port   = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        return "$scheme$host$port";
    }


    //
    // Helpers
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Pick a single Procedure: first by status buckets, then by time priority.
     *
     * @param \OCA\VTramit\Db\Procedure[] $list
     * @param int[] $preferredStatusIds
     * @param int[] $secondaryStatusIds
     */
    private function selectProcedureByStatusAndTime(array $list): ?Procedure {
        $group = $this->selectByStatusPriority($list);

        if (!empty($group)) {
            return $this->selectByTimePriority($group);
        }

        return null;
    }

    /**
     * Split list into three buckets: preferred, secondary, others.
     *
     * @param \OCA\VTramit\Db\Procedure[] $list
     * @return array{0: array, 1: array, 2: array}
     */
    private function selectByStatusPriority(array $list): array {
        $processing = [];
        $closed = [];
        $created = [];
        $others = [];

        foreach ($list as $item) {
            /** @var \OCA\VTramit\Db\Procedure $item */
            $statusId = (int) $item->getStatusId();

            if (in_array($statusId, [ProcedureStatus::PENDANT, ProcedureStatus::ON_COURSE], true)) {
                $processing[] = $item;
            } elseif (in_array($statusId, [ProcedureStatus::FINISHED, ProcedureStatus::COMPLETED], true)) {
                $closed[] = $item;
            } elseif (in_array($statusId, [ProcedureStatus::CREATED], true)) {
                $created[] = $item;
            } else {
                $others[] = $item;
            }
        }

        if (!empty($processing)) {
            return $processing;
        }
        if (!empty($closed)) {
            return $closed;
        }
        if (!empty($created)) {
            return $created;
        }
        return $others;
    }

    /**
     * From a set of candidates, pick by time priority:
     *  1) Any with appointmentDate = today (closest to now if multiple)
     *  2) Nearest future (strictly after today)
     *  3) Nearest past (strictly before today)
     *
     * @param \OCA\VTramit\Db\Procedure[] $candidates
     */
    private function selectByTimePriority(array $candidates): ?Procedure {
        if (empty($candidates)) {
            return null;
        }

        $tz = $this->config->getDateTimezone();

        $now = new \DateTime('now', $tz);
        $todayStart = (clone $now)->setTime(0, 0, 0);
        $todayEnd   = (clone $now)->setTime(23, 59, 59);
        $nowTs = $now->getTimestamp();

        $todayCandidate = null;
        $todayDelta     = PHP_INT_MAX;

        $futureCandidate = null;
        $futureTs        = null;

        $pastCandidate = null;
        $pastTs        = null;

        foreach ($candidates as $item) {
            /** @var \OCA\VTramit\Db\Procedure $item */
            $dateStr = $item->getAppointmentDate();
            if (empty($dateStr)) {
                continue;
            }

            try {
                $d = new \DateTime($dateStr, $tz);
            } catch (\Throwable $e) {
                continue;
            }

            $ts = $d->getTimestamp();

            // Case 1: today
            if ($d >= $todayStart && $d <= $todayEnd) {
                $delta = \abs($ts - $nowTs);
                if ($delta < $todayDelta) {
                    $todayDelta     = $delta;
                    $todayCandidate = $item;
                }
                continue;
            }

            // Case 2: strictly future
            if ($d > $todayEnd) {
                if ($futureTs === null || $ts < $futureTs) {
                    $futureTs        = $ts;
                    $futureCandidate = $item;
                }
                continue;
            }

            // Case 3: strictly past
            if ($d < $todayStart) {
                if ($pastTs === null || $ts > $pastTs) {
                    $pastTs        = $ts;
                    $pastCandidate = $item;
                }
            }
        }

        if ($todayCandidate !== null) {
            return $todayCandidate;
        }
        if ($futureCandidate !== null) {
            return $futureCandidate;
        }
        if ($pastCandidate !== null) {
            return $pastCandidate;
        }

        // Fallback: first candidate if none has a valid appointmentDate
        return \reset($candidates) ?: null;
    }

}
