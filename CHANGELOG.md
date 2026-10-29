# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.4.19] - 2026-07-21

### Fixed
- **Untranslated status labels in the procedures list.** The "Estat" column
  rendered the raw backend labels ("Pendant", "On course", …) instead of the
  translated ones. `ContextService::getProcedureStatusList()` returns the
  English labels verbatim (unlike the signature statuses, which are
  translated server-side), and `proceduresView` printed
  `procedure.statusValue` directly without running it through `t()`.

  Both consumers now translate: `ProceduresList.vue` (the column) and
  `ProceduresListFilter.vue` (the status filter dropdown) wrap the label in
  `t('vtramit', …)`. The `ca`/`es` translations for all nine status labels
  already existed, so no l10n additions were needed. Mirrors the approach
  already used in the attendant portal (`Procedure.vue`).

## [2.4.20] - 2026-08-06

### Fixed
- **Mobile "upload"/"download" panels stuck on an endless spinner.** The
  mobile accordion panels for uploads and downloads only loaded their share
  iframe when the active panel index was the hardcoded legacy position
  (uploads=1, downloads=2, assuming only [videocall, uploads, downloads]).
  Any appointment whose config adds documentation, topic-help, RGPD or
  videocall-test panels shifts the indices, so tapping "upload"/"download"
  opened an empty panel that never loaded — reported by OAC citizens on
  mobile (2026-08-06). `DashboardMobile` now passes each panel its computed
  accordion offset (`acc-index`) and the components compare against it; the
  `panelOptions` watcher that auto-clicks the download link now uses the
  computed downloads offset too (it recounted panels ignoring the optional
  ones, same class of bug).

## [2.4.18] - 2026-07-06

### Fixed
- **Default views per procedure-type-group were never applied.** The admin
  settings "Default view Desktop (Left/Right)" and "Default view Mobile" were
  stored and serialized correctly but never consumed: `PageController::buildConfig()`
  never sent them to the frontend, and the public dashboards never read them.
  Mobile always opened the first available panel (so the configured mobile
  view was ignored); desktop only *appeared* to work because its default-tab
  logic was hardcoded (videocall → documentation → rgpd) and happened to match
  common configurations.

  Now `buildConfig()` resolves the applicable `ProcedureTypeGroup` (the
  appointment's assigned group, falling back to the general group) and emits
  `defaultViewDesktopLeft`, `defaultViewDesktopRight` and `defaultViewMobile`.
  - `DashboardMobile.vue` selects the initial panel from `defaultViewMobile`.
  - `DashboardDesktop.vue` honours `defaultViewDesktopLeft/Right`, falling back
    to the previous hardcoded priority when the setting is empty ("Auto").

  Empty/"Auto" or a view that isn't available for the appointment keeps the
  prior default, so unconfigured procedures behave exactly as before.

## [2.4.17] - 2026-07-06

### Fixed
- **Manager ("gestor") groups regression.** The procedures list "Assigned
  to" column and the user search filter only ever knew the current user, so
  appointments assigned to anyone else rendered blank. Root cause: the
  "removed deprecated code" commit deleted `UserGroupHelper::canUserDoAssignments()`
  and the whole `groupLimit` config, so `getAssignableUsersByGroup()` no
  longer widened the user list for managers — and `ContextService::getAppContext()`
  (which builds the global `users` store) never passed the widening flag,
  falling back to self-only.

  Restored as `UserGroupHelper::isUserManager()`: a global admin, or a member
  of any configured manager group, again sees every user of the groups they
  belong to. The check lives back inside `getAssignableUsersByGroup()`, so it
  fixes both the "Assigned to" column and the search filter at once. The
  legacy `groupLimit` app-config key is reused, so existing configured values
  keep working with no migration.

### Added
- **Settings → vTramit → General → "Manager groups"** picker (two-column group
  selector) to define which Nextcloud groups act as managers, from the admin
  UI. `ConfigService::getManagerGroups()` reads the setting robustly (accepts
  both the serialized-array format written by the UI and the historical
  comma-separated string). The available-groups list is surfaced alongside the
  configuration payload and is never persisted back on save.

## [2.4.16] - 2026-05-31

### Added
- Two new message-template placeholders, both backed by `oc_vttopic`:
    - `%topicMinFiles%` — `min_required_docs` for the procedure's
      topic, or empty when none is set.
    - `%topicHelpUrl%`  — alias of the existing `%topicHelp%`; named
      consistently with the rest of the URL-bearing tokens so newer
      templates read more naturally.

  Both surface in the admin TokensPanel help. They unblock the
  out-of-band `vtramit-missing-files-notifier` cron, which uses
  them to tell citizens how many uploads are still missing and
  where to read the topic-level instructions.

## [2.4.15] - 2026-05-31

### Added
- Citizen-queue scope is now admin-configurable. The "ahead in
  queue" count surfaced by the videoconference-waiting ping (added
  in 2.4.14, OFF by default) can now be scoped four ways:
    - `assigned_user`             — same `assigned_user_id` (previous
                                    behaviour; default)
    - `assigned_group`            — same `assigned_group_id`
    - `procedure_type_and_group`  — same `procedure_type_id` AND
                                    `assigned_group_id`
    - `topic`                     — same `topic` string

  Drives a single scope per tenant; pick whichever fits how that
  tenant organises its OAC / desk groups. Toggle via Settings →
  vTramit → General (a select appears when "Tell waiting citizen
  how many appointments are ahead" is on), or:
      occ config:app:set vtramit citizenQueueScope --value=assigned_group

  The response shape changed from `queue.aheadForAttendant` to
  `queue.ahead` plus a `queue.scope` echo of the scope used. The
  old key was only present when the toggle was on and no consumer
  is reading it yet (the companion script is still to be written),
  so this is a safe rename.

### Changed
- `ProcedureMapper::findByFilter()` gained two new keys for the
  citizen-queue use case:
    - `topic`           — exact string match on the topic column
    - `assignedGroupId` — raw equality on `assigned_group_id` (the
                          existing `groups` array filter also AND's
                          the procedure type against `oc_vtproc_type_group`
                          via JOIN, which excludes valid rows whose
                          type isn't registered for that group)
  Both are documented in the method's docblock.

  Verified end-to-end on demo with seven synthetic appointments
  varying user / group / type / topic. Counts: assigned_user=1,
  assigned_group=3, procedure_type_and_group=2, topic=2 — all match
  the hand-computed expected values.

## [2.4.14] - 2026-05-31

### Added
- Optional "you are #N in queue" feedback for the waiting citizen.
  The existing ping into the videoconference-waiting endpoint
  (`POST /api/2.0/procedure/videoconference/{jitsiRoomCode}/waiting`,
  also reached via the legacy `/api/0.1/waitingforvc/`) can now
  return a `queue.aheadForAttendant` count — the number of other
  appointments scheduled for the SAME assigned attendant, today,
  with `appointmentDate` strictly earlier than the citizen's own and
  status in {PENDANT, ON_COURSE}. The attendant's identity is never
  surfaced, only the count.

  Gated behind the new `citizenQueueInfo` admin setting (default
  OFF) so existing tenants whose Jitsi-meet fork ignores the
  response body keep seeing the exact same payload as before.
  Toggle via Settings → vTramit → General ("Tell waiting citizen
  how many appointments are ahead") or:
      occ config:app:set vtramit citizenQueueInfo --value=1

  Verified end-to-end on demo (NC 33.0.3) with four synthetic
  appointments at -60min / -30min / now / +30min: count returns
  2 when self is the third in line, 0 when the attendant differs
  or self is the earliest, and the `queue` key disappears entirely
  when the toggle is off.

## [2.4.13] - 2026-05-09

### Added
- "HTML" toggle button above the message-template CKEditor. Click
  swaps the WYSIWYG editor for the raw `<textarea>` underneath so
  users can paste arbitrary HTML (e.g. `<img src="https://…">`) when
  the toolbar can't express what they need. Click again ("WYSIWYG")
  re-mounts CKEditor on the same textarea. Implemented in the
  `componentCKEditor` mixin because the Classic Build doesn't bundle
  the official `SourceEditing` plugin.

## [2.4.12] - 2026-05-09

### Fixed
- The `insertImage` toolbar button in the message-template CKEditor
  was falling back to "Insert image with file manager" (CKFinder)
  because the bundled CKFinder / CKBox / CloudServices plugins
  remained enabled and outranked the URL inserter. The button then
  opened a file-picker that talked to a non-existent backend.
  Remove `CKFinder`, `CKBox` and `CloudServices` from the editor
  plugin list so the dropdown collapses to the URL form, which is
  what we wired in 2.4.11.

## [2.4.11] - 2026-05-09

### Added
- Image-via-URL support in the message-template CKEditor. The
  `insertImage` toolbar button now appears between Link and Insert
  Table; with `ImageUpload` still removed, the dropdown collapses to
  a single "Image via URL" form, which is what tenants need to embed
  the ajuntament logo (or any externally hosted image) inside an
  email/SMS template body. Standard image alt-text + style toolbar
  (inline / block / side) is wired in too.

## [2.4.10] - 2026-05-09

### Fixed
- `NextcloudPanel` no longer waits ~10 s on the failback timer for
  upload/download iframes that are pre-authenticated. The previous
  load handler always required two onload events (login page →
  submit → file list), but with the sequential pre-auth from 2.4.9
  the very first GET `/s/<token>` returns the file list directly,
  so the second onload never fires and the panel sat behind a
  spinner until the 10 s fallback. Detect the login page on the
  first onload (`#body-login` / `#password-submit`) and jump
  straight to the "loaded" stage when it's absent.

### Changed
- `NextcloudPanel` now injects a small stylesheet into the iframe
  document on each load to hide Nextcloud's outer chrome (top
  `<header>`, public-page `<footer>`, skip-navigation links). A
  `MutationObserver` re-applies the stylesheet if NC's public-share
  SPA replaces the head between routes. Result: the embedded share
  shows only the file widget, blending with the surrounding vtramit
  panel.

## [2.4.9] - 2026-05-09

### Fixed
- Public-page upload/download iframes no longer end up on the share's
  password form when the appointment has more than one share. The
  pre-auth helper at `templates/publicPortal-snippet.php` was POSTing
  the password to both shares with `Promise.all`, but Nextcloud
  regenerates the session ID on every successful share auth and stores
  the share-auth flag in the freshly minted session — when two POSTs
  raced, the browser kept only the last `Set-Cookie` and the other
  share's auth flag lived in an orphaned session, so the next iframe
  navigation redirected to the password form. Verified on
  demo.vtramit.com (NC 33.0.3): two PROPFIND auth POSTs returned 303
  in parallel but the subsequent `GET /s/<token>` for one of them
  came back 303 (still unauth'd) while the other returned 200.
  Sequentialise the auth loop so each session regen is observed by
  the next request.

## [2.4.8] - 2026-05-09

### Changed
- **Minimum Nextcloud version bumped from 22 to 30.** The release fleet
  (demo, OIT, DIDU) is already on NC32+ so this is a no-op in practice,
  but it unlocks the typed `IConfig` getters introduced in NC30.

### Fixed / Modernised
- Cron jobs (`PrepareWorkdayJob`, `PurgeJob`) no longer pull services
  out of the static `\OC::$server` container. They now declare their
  dependencies in the constructor (`ITimeFactory`, `ProcedureJobService`,
  `LoggerInterface`) so Nextcloud's DI container wires them — the same
  pattern the rest of the codebase already uses, and the only one that
  will keep working when `\OC::$server->query()` is removed.
- `ConfigService` swapped the four remaining `getSystemValue()` calls
  (deprecated since NC30) for the typed variants:
  `getSystemValueString` for `vtramit.mode` and `logtimezone`,
  `getSystemValueBool` for `vtramit.log.timer`, and
  `getSystemValueArray` for `vtramit.group.filter`.
- Dropped a stray `@NoAdminRequired` PHPDoc tag on
  `UserGroupHelper::getUsersFromGroup()` — the file isn't a controller,
  so the annotation never had any effect; removed for clarity.

## [2.4.7] - 2026-05-02

### Fixed
- Compatibility with Nextcloud 33: replace the four QueryBuilder
  `execute()` calls in NextcloudHelper, ProcedureMapper and
  ProcedureTypeGroupMapper with `executeQuery()` (SELECT) or
  `executeStatement()` (UPDATE), and the one Statement `execute()` in
  the 2.1.0 migration with `executeStatement()`. NC33 dropped the
  long-deprecated `execute()` shim, so anything calling it now blows
  up with `Call to undefined method`. The replacements have existed
  since NC21, so the build remains compatible with NC22+ (the
  declared minimum) — verified against NC32 and NC33.

## [2.4.6] - 2026-05-01

### Fixed
- File download from procedure folders now works on deployments where the
  uploads/downloads folders live behind external storage, primary objectstore
  or any non-local Nextcloud backend. Previous code read the file directly
  from the datadirectory with `is_file()`/`file_get_contents()`, which
  silently returned 404 whenever the configured folder wasn't on the local
  filesystem (e.g. an Azure Blob mount under `OIT/`). The download path now
  goes through `IRootFolder` exactly like the listing already did, so all
  storage backends Nextcloud knows about are honoured.

## [1.0.0] - 2021-01-25
### Added
- First release

## [1.0.1] - 2021-03-15

### Added
- Added 'vtramit.group.forms' to config to optionally define a group URL for redirection after the appointment is completed.
- Integration with Deck module. Two new configutarion options added: vtramit.deck.enabled, vtramit.group.deck
- Automatically edit appointment when videoconference is finished.
- Assign the appointment automatically to the user that started the videoconference, but only if appointment is in pendant state.
- Add a new button "Phonecall finished" and store if attention was done by phone or videoconference.

### Fixed
- Wrong display of edit appointment on a long list of appointments.
- Wrong display of appointments list when the filter is longer than the screen (when there are a lot of users to show).

## [1.0.2] - 2021-??-??

### Added
- Compatibility with Nextcloud 21. Now minimal version supported is Nextcloud 20.
- Integration with AOC electronic signature. (WIP)
- Allow using multiple e-mail addresses on an appointment.
- Hide videoconference button if citizen is not present. This feature can be enabled by using the 'vtramit.vc.hideWaiting' configuration option.
- Add a link with the link to the citizen videoconference on the '...' menu inside the appointment edit card.
- Convert search by external appointmet ID in a more open search including also citizend ID and citizen name in the search.
- Make link to citizen entry page available for e-mails.


## [1.0.6] - 2022-02-21

### Added
- Compatibility with Nextcloud 22.


## [2.0.0]

### Added
- Procedures management
- Administration management for most of the configurations


## [2.1.0]

### Added
- Compatibility with NextCloud 31
- Tabbed panels in the administration zone for better usability
- Moved all configuration settings from the config.php file to database
- General configuration settings management from the administration panel
- JWT payload and secret on procedures for better JWT management
- CKEditor in admin panel for rich text fields
- Signature type to allow define as many signatures as needed
- Signature configuration management from administration panel

### Changed
- Column 'vtramit_signature.state' renamed as 'vtramit_signature.status'
- Column 'vtramit_signature.state_date' renamed as 'vtramit_signature.status_date'
- Column 'vtproc_type.jitsi_stakeholder_jwt' removed
- Column 'vtproc_type.jitsi_staff_jwt' removed
- Rename table 'vtramit_signature' as 'vtsign'
- Rename table 'vtramit_videoconference' as 'vtvideoconference'
- Rename table 'vtramit_mail_queue' as 'vtmail_queue'
- Rename table 'vtramit_signature_file' as 'vtsign_file'

### Removed
- Compatibility with versions previous to NextCloud 31
- API v0.1 endpoints
- Appointments management; now you must manage appointments through procedures


## [2.2.0]

### Added

### Changed