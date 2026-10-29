<?php
/**
 * @copyright Copyright (c) 2020-2026 VIDEOATENCION
 *
 * @license GNU AGPL version 3 or any later version
 */

declare(strict_types=1);

namespace OCA\VTramit\EventListener;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

/**
 * Adds `worker-src 'self'` to the default CSP for every page on the
 * Nextcloud instance — including pages NOT rendered by vtramit (e.g.
 * the public-share landing at /s/<token>/showshare).
 *
 * Why globally, from a vtramit listener:
 *   The citizen public flow embeds NC's files-app inside the share page,
 *   which tries to register a Service Worker
 *     /index.php/apps/files/preview-service-worker.js
 *   NC ≥32 emits a CSP without an explicit `worker-src` directive for
 *   that page, so browsers fall back to `script-src` (nonce-restricted)
 *   and block the SW. Vtramit's own PageController already adds
 *   `worker-src 'self'` to its own response, but it can't reach NC's
 *   share controller — that's why we do this globally via the
 *   AddContentSecurityPolicyEvent (NC merges all listener-supplied
 *   policies into the default).
 *
 * @template-implements IEventListener<AddContentSecurityPolicyEvent>
 */
class CspListener implements IEventListener {
    public function handle(Event $event): void {
        if (!($event instanceof AddContentSecurityPolicyEvent)) {
            return;
        }

        $policy = new ContentSecurityPolicy();
        $policy->addAllowedWorkerSrcDomain("'self'");

        $event->addPolicy($policy);
    }
}
