<?php
// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later
/**
 * Pre-authenticate the citizen's NC public shares (uploads + downloads)
 * before the SPA's file-upload widget tries to use them.
 *
 * Why this exists:
 *   The compiled publicPortal.js POSTs to
 *     /s/<token>/authenticate/showshare
 *   without a `requesttoken` field. NC ≥32 enforces a strict CSRF check
 *   on that endpoint and returns 412 (Precondition Failed) when the
 *   token is missing — verified empirically against vtramit 2.4.3 on
 *   NC 32.0.8.2:
 *
 *     no requesttoken    → HTTP 412
 *     wrong requesttoken → HTTP 412
 *     right requesttoken → HTTP 303 (auth OK, share session set)
 *
 *   When the auth POST fails, the subsequent PROPFIND on
 *   /public.php/dav/files/<token>/ returns 401 and the file widget
 *   shows an empty/error state.
 *
 *   This snippet runs at page load, fetches a fresh requesttoken from
 *   each share's landing page, and POSTs the password (read from the
 *   `?ak=` query parameter on the public page URL) plus the matching
 *   token. NC accepts the auth and sets the share-session flag on the
 *   shared `oc_sessionPassphrase` cookie. By the time the SPA's widget
 *   tries to PROPFIND the share, the cookie is already authorised.
 *
 *   Once vtramit's compiled publicPortal.js bundle is rebuilt to
 *   include `requesttoken` on its own auth POST, this snippet becomes
 *   redundant and can be deleted.
 *
 * Scope: only runs when the public page has `?ak=<password>` and at
 *        least one share URL in the server-side `data.appointment`.
 */
?>
<script nonce="<?php p($_['cspNonce'] ?? '') ?>">
(function () {
	'use strict';

	function tokenFromShareUrl(url) {
		if (!url || typeof url !== 'string') return null;
		const m = url.match(/\/s\/([^\/?#]+)/);
		return m ? m[1] : null;
	}

	async function preAuthShare(shareToken, password) {
		if (!shareToken || !password) return false;
		try {
			// 1) Hit the share landing to obtain a fresh requesttoken bound
			//    to the current oc_sessionPassphrase cookie.
			const landing = await fetch('/s/' + encodeURIComponent(shareToken), {
				credentials: 'include',
				redirect: 'follow',
			});
			const html = await landing.text();
			const m = html.match(/data-requesttoken="([^"]+)"/);
			if (!m) {
				console.warn('[vtramit:share-preauth] no requesttoken in landing for', shareToken);
				return false;
			}
			const requesttoken = m[1];

			// 2) POST password + requesttoken — NC sets the share's session flag.
			//    redirect:'manual' so we don't follow the 303 (which would just
			//    GET /s/<token>/showshare — cheap but unnecessary).
			const body = new URLSearchParams();
			body.set('password', password);
			body.set('requesttoken', requesttoken);

			const res = await fetch('/s/' + encodeURIComponent(shareToken) + '/authenticate/showshare', {
				method: 'POST',
				body: body,
				credentials: 'include',
				redirect: 'manual',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'requesttoken': requesttoken,
				},
			});

			// 303 (followed=manual → opaqueredirect, status=0) = auth OK.
			// 200 = NC re-rendered the password form with error → wrong password.
			const ok = res.type === 'opaqueredirect' || res.status === 0 || res.status === 303;
			if (!ok) {
				console.warn('[vtramit:share-preauth]', shareToken, 'status=' + res.status + ' (expected 303)');
			}
			return ok;
		} catch (e) {
			console.warn('[vtramit:share-preauth]', shareToken, 'fetch error:', e);
			return false;
		}
	}

	function init() {
		const dataEl = document.querySelector('script[name="server-data"]');
		if (!dataEl) return;
		let data;
		try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }

		const ak = new URLSearchParams(window.location.search).get('ak');
		if (!ak) return;

		const tokens = Array.from(new Set([
			tokenFromShareUrl(data && data.appointment && data.appointment.sharedUrlUploads),
			tokenFromShareUrl(data && data.appointment && data.appointment.sharedUrlDownloads),
		].filter(Boolean)));
		if (!tokens.length) return;

		// Serialise the auth POSTs. NC regenerates the session ID on every
		// successful share auth and stores the share-auth flag in the new
		// session. If two POSTs run in parallel they each fork a fresh
		// session and the browser keeps only the last Set-Cookie — the
		// other share's auth flag lives in an orphaned session and the
		// next GET redirects to the password form. Reproduced on demo
		// (NC 33.0.3) with two shares in flight; only one survived.
		(async () => {
			const results = [];
			for (const t of tokens) {
				results.push(await preAuthShare(t, ak));
			}
			return results;
		})().then(results => {
			const ok = results.filter(Boolean).length;
			// Notify any interested code that share auth is ready. The SPA can
			// listen for this and refresh its file list without needing a reload.
			window.dispatchEvent(new CustomEvent('vtramit:shares-ready', {
				detail: { tokens, ok, total: tokens.length },
			}));
			console.log('[vtramit:share-preauth] authenticated ' + ok + '/' + tokens.length + ' share(s)');
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
</script>
