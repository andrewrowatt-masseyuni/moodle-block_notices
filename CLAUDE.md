# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this checkout is

A Moodle 4.5 (`MOODLE_405_STABLE`) core checkout that exists to develop one plugin: **`block_notices`** in [blocks/notices/](blocks/notices/). Work happens in the plugin; core is host scaffolding.

- `blocks/notices/` is its **own nested git repo** (origin `andrewrowatt-masseyuni/moodle-block_notices`, branch `main`). Commit plugin changes from inside that directory, never in the outer core repo. Feature branches are named after GitHub issues (e.g. `26-consider-ordering-...`), and commit messages end with `Fixes #NN`.
- `local/codechecker`, `local/moodlecheck` and `theme/snap` are third-party clones that provide tooling or a theme. Don't modify them.

## Environment (moodle-docker)

The site runs in moodle-docker. Shell state does not persist between Bash calls, so put the exports in the same command as each `moodle-docker-compose` call:

```bash
export COMPOSE_PROJECT_NAME=moodle405_block_notices MOODLE_DOCKER_WWWROOT=/home/arowatt/moodle405_block_notices \
  MOODLE_DOCKER_PHP_VERSION=8.1 MOODLE_DOCKER_DB=pgsql MOODLE_DOCKER_BROWSER=chrome \
  MOODLE_DOCKER_WEB_PORT=8050 MOODLE_DOCKER_DB_PORT=5450 MOODLE_DOCKER_SELENIUM_VNC_PORT=5950
MDC=/home/arowatt/moodle-docker/bin/moodle-docker-compose
$MDC up -d                                   # containers are not always running
```

Inside the container, Moodle lives at `/var/www/html` and the DB table prefix is `m_` (e.g. `$MDC exec db psql -U moodle -d moodle -c "SELECT * FROM m_block_notices;"`).

## Commands

```bash
# After bumping version.php or changing db/*
$MDC exec webserver php admin/cli/upgrade.php --non-interactive
$MDC exec webserver php admin/cli/purge_caches.php

# PHPUnit (re-run init.php after schema/version changes)
$MDC exec webserver php admin/tool/phpunit/cli/init.php
$MDC exec webserver vendor/bin/phpunit blocks/notices/tests/notices_test.php
$MDC exec webserver vendor/bin/phpunit --filter test_get_notices_orders_unread_first blocks/notices/tests/notices_test.php
$MDC exec webserver vendor/bin/phpunit privacy/tests/privacy/provider_test.php   # CI also runs core privacy compliance

# Behat (Chrome; re-run init.php after schema/version changes)
$MDC exec webserver php admin/tool/behat/cli/init.php
$MDC exec -u www-data webserver php admin/tool/behat/cli/run.php --tags=@block_notices
$MDC exec -u www-data webserver php admin/tool/behat/cli/run.php \
  --feature="/var/www/html/blocks/notices/tests/behat/view_notice.feature:60" --format progress --format moodle_screenshot

# Code style: run phpcs on the host from the Moodle root (root phpcs.xml already excludes thirdparty/)
local/codechecker/vendor/bin/phpcs blocks/notices
$MDC exec webserver php local/moodlecheck/cli/moodlecheck.php -p=blocks/notices -f=text   # PHPDoc

# AMD JS: amd/build/ is committed and must be rebuilt after any amd/src change (host, Node per .nvmrc)
cd blocks/notices && npx grunt amd
```

CI ([blocks/notices/.github/workflows/moodle-ci.yml](blocks/notices/.github/workflows/moodle-ci.yml)) runs moodle-plugin-ci with **zero warnings allowed** for phpcs, phpdoc and grunt (eslint), plus savepoints, mustache lint, PHPUnit `--fail-on-warning`, and Behat. A stale `amd/build` or any lint warning fails the build.

## Plugin architecture

**Notices belong to a course, not to a block instance.** Every Notices block in a course shows the same set. The Dashboard and the site front page are both `SITEID`, so they share notices. Files (the optional image) are stored in the course context, or in the system context for `SITEID`. Always resolve that context with `notices::get_notice_context()`.

**Layers**
- [classes/notices.php](blocks/notices/classes/notices.php): a static service class and the only place that writes to the DB. It also fires every `classes/event/*` event and maintains `sortorder`. Put new data logic here.
- [block_notices.php](blocks/notices/block_notices.php): renders `templates/notices.mustache` (a Swiper carousel). `html_attributes()` adds `block_notices_exclusive_*` classes when an exclusive notice is active.
- [manage.php](blocks/notices/manage.php): a GET-action controller (`action`, `id`, `sesskey`; delete goes through `$OUTPUT->confirm`). It renders `templates/notices_admin.mustache` with notices grouped by visibility.
- Add/edit is a `core_form\dynamic_form` ([classes/form/notice.php](blocks/notices/classes/form/notice.php)) opened in a ModalForm by `amd/src/manage.js`.

**State model**
- `visible`: 0 = hidden, 1 = visible, 2 = preview (shown only to manage-all users). `sortorder` only applies to visible notices.
- `exclusive`: 0 = none, 1 = important (red), 2 = information (blue). At most one per course. While one is visible, `get_notices()` short-circuits and returns only that notice, with no carousel.
- Per-user ordering is computed in the SQL in `get_notices()`. A notice is "unread" when it has no `block_notices_read` row or `timeread < timepromoted`. Unread notices come first in `sortorder`; read ones follow, least recently read first. Editing a notice or using **Promote** bumps `timepromoted`, which makes it unread for everyone.
- Read tracking: `amd/src/block_notices.js` watches carousel slides with an IntersectionObserver and calls the `block_notices_mark_read` web service. That service is `loginrequired => false` and returns an empty ack for guests. `mark_read_batch()` re-checks visibility on the server.
- `task/auto_close_notices` runs every 15 minutes. It hides notices whose `closedate` has passed and emails the owner.

**Permissions (two tiers)**
- `block/notices:manageallnotices` is always checked against the **system** context, even on course pages. It grants create, delete, reorder and set-exclusive.
- The per-notice `additionaleditorid` user can edit, show/hide and promote **that notice only**, with no role or capability needed. They cannot create, delete, reorder or set exclusive.
- These rules live in `notices::user_can_*` and in the per-action guard in `manage.php`. Keep both in sync.
- Install/upgrade calls `util::provision_default_roles()`, which creates the `block_notices_manager` system role.

**Staff detection is username-based (Massey-specific).** `util::is_staff()` returns false for 8-digit usernames (students) and `st` + 6 digits ("ST" accounts), and true for everyone else. Staff-only notices, and image access in `lib.php` `block_notices_pluginfile()`, depend on this. Behat fixtures rely on it: `98186700` is a student and `arowatt` is staff.

**Content and XSS.** Notice content is HTML from Quill. Additional editors don't hold RISK_XSS, so always output `title` through `format_string()` and `content` through `format_text()` with the course context, and keep the URL-scheme validation in the form.

**Third-party JS** is vendored in `thirdparty/` and declared in `thirdpartylibs.xml`:
- **Swiper** is loaded as a browser global through `$PAGE->requires->js()`, not as an AMD module.
- **Quill** is lazy-loaded by `amd/src/notice_editor.js`, which temporarily clears `window.define` so the UMD bundle sets `window.Quill`. The editor re-initialises through a MutationObserver because ModalForm swaps the form body without firing events.
- The real textarea is hidden, so Behat must use the custom step `I set the notice Quill editor to "..."` from [tests/behat/behat_block_notices.php](blocks/notices/tests/behat/behat_block_notices.php).

**Testing fixtures.** The Behat generator ([tests/generator/](blocks/notices/tests/generator/)) supplies the entities used in `the following "block_notices > notice" exist:` and `the following "block_notices > notice read" exist:`. Rows reference courses, users and notices by shortname, username and title.

**Schema changes:**
1. Edit `db/install.xml`.
2. Add a step in `db/upgrade.php` that ends with `upgrade_block_savepoint(true, <version>, 'notices')`.
3. Bump `$plugin->version` (`YYYYMMDDXX`) and `$plugin->release` in `version.php`.

CI verifies the savepoints.

**CSS** in `styles.css` must stay scoped under `section.block_notices` or `#page-blocks-notices-manage`, because unscoped rules previously broke the gradebook. The UI is tuned for the Snap theme (`theme/snap` is installed here), but it must still work under Boost.
