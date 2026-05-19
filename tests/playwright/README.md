# block_notices Playwright tests

A small Playwright suite that mirrors the Behat scenarios for the `block_notices`
plugin so the same end-to-end journeys can be driven against a normal running
Moodle dev site (not just a Behat-initialised one).

The first spec — `tests/add_notice.spec.ts` — is a 1:1 port of
[`../behat/add_notice.feature`](../behat/add_notice.feature).

## Prerequisites

- Node.js 18 or later
- A running Moodle 4.x site with `block_notices` installed
- Admin credentials for that site

## Install

```bash
cd blocks/notices/tests/playwright
npm install
npm run install:browsers   # downloads the Chromium build Playwright uses
```

## Configure

Copy the example env file and fill in the real values for your local Moodle:

```bash
cp .env.example .env
# then edit .env
```

| Variable | Purpose |
| --- | --- |
| `MOODLE_BASE_URL` | Root URL of the Moodle site (e.g. `http://localhost` or `http://moodle.local`) |
| `MOODLE_ADMIN_USER` | Username of an account that can manage blocks on the site homepage |
| `MOODLE_ADMIN_PASSWORD` | Password for that account |

`.env` is read at config-load time if (and only if) the optional `dotenv`
package is installed. To opt in:

```bash
npm install --save-dev dotenv
```

Otherwise just `export` the variables in the shell that runs `npm test`.

## Run

```bash
npm test                 # headless run
npm run test:headed      # watch the browser
npm run test:debug       # step through with the Playwright inspector
npm run report           # open the HTML report from the last run
```

## A note on state

These tests drive a real Moodle site, so they mutate state:

- They add a "Notices" block to the site homepage if one isn't there.
- They create a notice titled `Notice1title`.

Running the spec twice in a row will pile up duplicates. If you need a clean
slate, remove the extra "Notices" block from the site homepage (editing mode →
block actions → delete) and delete leftover notices via **Manage notices**.
