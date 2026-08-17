# AgencyFlow

A custom WordPress client-project and lead-management website, built as a
portfolio project. Custom theme, custom plugin, vanilla JavaScript, no page
builder and no CSS framework.

**Stack:** WordPress · PHP 8 · MySQL · HTML5 · CSS (Flexbox + Grid) · vanilla
JavaScript · WordPress REST API · Open-Meteo · Git · GitHub Actions · PHPUnit ·
ESLint

---

## Overview

AgencyFlow is the website for a fictional Manchester digital agency. Visitors can
browse the agency's client projects, filter them by type without a page reload,
open a detail page for each one, see live local weather pulled from a third-party
API, and submit a project enquiry that is validated, stored and followed by an
automated email-notification attempt to the site administrator.

Everything a client would need to manage — projects, their categories, their
custom fields, and the enquiries that come in — is managed from the standard
WordPress admin.

---

## Why I Built It

My degree and previous projects lean towards Python, React and data work. This
project exists to cover the ground those don't: WordPress, custom theme and
plugin development, PHP, and plain JavaScript with nothing underneath it.

I set myself two constraints. First, no page builder and no CSS framework —
because the point was to demonstrate that I can write the HTML, CSS and
JavaScript myself, not configure someone else's. Second, no third-party plugin
for anything the project is meant to demonstrate; the custom post type, REST
endpoint and enquiry handling are implemented in this repository rather than
outsourced to third-party plugins.

---

## Features

- Custom WordPress theme built from scratch, using the template hierarchy
- Custom WordPress plugin registering a Project content type with its own
  taxonomy and custom fields
- Responsive layouts at mobile, tablet and desktop, using Flexbox and CSS Grid
  for different jobs
- Project filtering by category in vanilla JavaScript, with no page reload
- A custom REST endpoint at `/wp-json/agencyflow/v1/projects`, consumed by the
  front end with `fetch`
- External API integration with Open-Meteo, including loading and error states
- A project enquiry form validated in the browser and again on the server
- A server-side email notification attempt after each valid enquiry is stored,
  with mail failure recorded without losing the enquiry
- Escaped output and sanitised input in the custom application code, with nonce
  protection on state-changing requests
- Unit tests on the pure helper functions
- A GitHub Actions CI pipeline running PHP linting, PHPUnit and ESLint on pushes
  to `main` and pull requests targeting `main`

---

## Technology Stack

| Layer | Technology |
|---|---|
| CMS | WordPress (plugin declares WordPress 6.0+) |
| Server language | PHP 8 (Composer requires PHP >= 8.0; CI uses PHP 8.2) |
| Database | MySQL, accessed only through WordPress APIs |
| Markup | Semantic HTML5 |
| Styling | Hand-written CSS — custom properties, Flexbox, Grid, media queries |
| Client scripting | Vanilla JavaScript — DOM API, Fetch, async/await |
| Internal API | WordPress REST API, custom namespace |
| External API | Open-Meteo |
| Version control | Git/GitHub workflow documented in `docs/05-GIT-PLAN.md` |
| CI | GitHub Actions (PHP 8.2 and Node 20 runners) |
| Testing | PHPUnit, ESLint, and a documented manual test matrix |
| Local environment | LocalWP |

### Source bundle contents

This ZIP is a **project source bundle, not a complete WordPress installation**.
It contains the custom theme and plugin, tests, CI configuration and project
documentation. WordPress core, `wp-config.php`, uploads, Composer's `vendor/`
directory and `node_modules/` are not bundled.

```text
agencyflow/
├── .github/workflows/ci.yml
├── docs/
│   ├── 01-INSTALL.md
│   ├── 02-CONTENT.md
│   ├── 03-CODE-WALKTHROUGH.md
│   ├── 04-TESTING.md
│   ├── 05-GIT-PLAN.md
│   └── 06-INTERVIEW-PREP.md
├── tests/
│   ├── bootstrap.php
│   └── HelpersTest.php
├── wp-content/
│   ├── plugins/agencyflow-project-manager/
│   └── themes/agencyflow/
├── composer.json
├── package.json
├── phpunit.xml
├── eslint.config.js
├── .gitignore
├── gitignore.txt
└── README.md
```

`.gitignore` and `gitignore.txt` contain the same ignore rules in the supplied
archive.

---

## WordPress Architecture

The central decision: **the plugin owns the data, the theme owns the
presentation.**

```
wp-content/
├── plugins/agencyflow-project-manager/    <- DATA
│   ├── agencyflow-project-manager.php     Bootstrap, activation hook
│   └── includes/
│       ├── helpers.php                    Pure functions (unit tested)
│       ├── post-types.php                 Project CPT, taxonomy, Enquiry CPT
│       ├── meta-fields.php                Project fields + sanitised save
│       ├── rest-api.php                   Custom REST routes
│       └── enquiries.php                  Storage, admin display, notification
│
└── themes/agencyflow/                     <- PRESENTATION
    ├── style.css                          Theme header + reset
    ├── functions.php                      Theme supports, conditional enqueueing
    ├── header.php / footer.php
    ├── index.php                          Hierarchy fallback
    ├── front-page.php                     Home: weather + REST-loaded projects
    ├── archive-project.php                /projects/ with filter bar
    ├── single-project.php                 Project detail
    ├── page-contact.php                   Enquiry form
    ├── template-parts/project-card.php    Reusable card markup
    └── assets/
        ├── css/main.css
        └── js/{filter,featured-projects,weather,enquiry}.js
```

If the site were re-themed tomorrow, every project and every enquiry would
survive, because none of that structure lives in the theme. Registering content
types inside a theme is a common WordPress mistake and it destroys a client's
data the day they redesign.

### Template hierarchy

| URL | Template used |
|---|---|
| `/` | `front-page.php` |
| `/projects/` | `archive-project.php` |
| `/projects/{slug}/` | `single-project.php` |
| `/contact/` | `page-contact.php` |
| anything else | `index.php` |

WordPress selects these by filename. There is no routing configuration.

---

## Custom Theme

- `add_theme_support()` for the title tag, featured images, HTML5 markup and
  automatic feed links, plus a cropped `agencyflow-card` image size
- One registered menu location, with a PHP fallback so the header is never empty
- Assets loaded with `wp_enqueue_style()` and `wp_enqueue_script()`, with a
  version string for cache busting and a declared dependency between the two
  stylesheets
- Scripts enqueued **conditionally** — weather and featured-project loading on
  the front page, filtering on project archives/taxonomy pages, and enquiry
  handling on the contact page
- `wp_localize_script()` passes the REST base URL and a nonce from PHP into
  JavaScript, so neither is hardcoded
- Two filters, `excerpt_length` and `excerpt_more`, alongside the actions — the
  theme demonstrates both hook types
- Semantic markup throughout: `<header>`, `<nav>`, `<main>`, `<section>`,
  `<article>`, `<aside>`, `<footer>`, with one `<h1>` per page and no skipped
  heading levels

### CSS approach

**Flexbox** for one-dimensional layouts where items size to their content: the
header bar, the nav list, the filter row, project metadata, the footer, and the
internal layout of each card.

**Grid** for two-dimensional layouts with columns under my control: the project
listing, and the content-plus-sidebar layout on a project page.

The project grid uses `repeat(auto-fit, minmax(min(100%, 280px), 1fr))`, so the
browser decides the column count from the available width — one column on a
phone, three on a desktop, with no media query for the columns at all.

Mobile-first, with breakpoints at 600px and 900px chosen from where the layout
starts to break rather than from device names.

---

## Custom Plugin

`agencyflow-project-manager` handles:

- **Project post type** — `public`, `has_archive`, rewritten to `/projects/`,
  supporting title, editor, excerpt, featured image and revisions
- **`project_type` taxonomy** — hierarchical, shown as an admin column, with four
  terms seeded on activation
- **Project fields** — client, technologies, completion date and project URL, in
  one meta box, each sanitised on save behind four guard clauses
- **Enquiry post type** — `public => false`, so enquiries have no front-end URL
  and cannot be browsed, with `create_posts` denied because they only arrive via
  the form
- **REST routes** — a read endpoint for projects and a write endpoint for
  enquiries
- **Enquiry handling** — nonce, sanitise, validate, store, notify
- **Activation hook** — seeds the taxonomy terms and flushes the rewrite rules
  once, rather than on every request

---

## JavaScript Features

Four small scripts, each with a single job.

**`filter.js`** — reads `data-types` from each card, listens for `click` on the
filter buttons, and toggles an `is-hidden` class on non-matching cards.
Demonstrates events, `querySelectorAll`, `dataset`, `classList` and DOM
manipulation. The status line and the empty state update with it, and
`aria-pressed` is kept in sync.

**`featured-projects.js`** — `fetch` against the site's own REST endpoint,
`async`/`await`, and cards built with `createElement` and `textContent` rather
than an `innerHTML` string, so no project title can ever be interpreted as
markup. Shows a loading state first, handles an empty result as a valid response,
and falls back to a readable message on failure.

**`weather.js`** — `fetch` against Open-Meteo, with `URLSearchParams` for safe
query building, a mapping from WMO weather codes to words, and an error state
that leaves the rest of the page working.

**`enquiry.js`** — `submit` event with `preventDefault()`, per-field validation,
a disabled-button loading state, a JSON `POST`, per-field error rendering from the
server's response, and a `finally` block so the button can never be left stuck.

The networked scripts handle non-success responses explicitly because `fetch`
resolves rather than rejects on an HTTP error: `featured-projects.js` and
`weather.js` check `response.ok`, while `enquiry.js` branches on the returned HTTP
status (`201`, `422`, or another failure). `filter.js` performs no network request.

---

## REST API

### `GET /wp-json/agencyflow/v1/projects`

| Parameter | Type | Default | Notes |
|---|---|---|---|
| `type` | string | — | Project type slug; omit or pass `all` for everything |
| `per_page` | integer | 6 | Validated to 1–20 |

Returns a JSON array with `id`, `title`, `excerpt`, `permalink`, `image`,
`client`, `technologies`, `completion_date`, `completed_label`, `project_url`,
`type_names` and `type_slugs`.

`per_page` is range-validated so a stranger cannot request 100,000 records and
set the server's workload.

### `POST /wp-json/agencyflow/v1/enquiries`

Body: `name`, `email`, `company`, `project_type`, `budget`, `message`, `nonce`.

| Status | Meaning |
|---|---|
| 201 | Created — enquiry stored and email notification attempted |
| 403 | Nonce missing or expired |
| 422 | Well-formed request, but fields failed validation; an `errors` object names each one |
| 500 | Could not save |

WordPress core already exposes projects at `/wp-json/wp/v2/projects`. A custom
route exists because that response is large and doesn't include the custom meta
in a useful shape — designing the response around what the front end needs is the
difference between an API and a database dump.

---

## External API Integration

Open-Meteo, called from the browser on the home page, showing current Manchester
conditions.

```
click / page load -> fetch(GET api.open-meteo.com/v1/forecast?…)
   -> JSON response -> parse -> update the DOM
```

The implementation calls Open-Meteo directly from client-side JavaScript and no
API key or secret is stored in this repository. If a future provider required a
secret key, it could not safely be embedded in browser JavaScript and would need
to be kept server-side, for example behind a PHP proxy endpoint.

The weather script handles both network failures and HTTP error responses: the
request is wrapped in `try`/`catch`, and a non-success status is rejected via
`response.ok`. On failure the strip shows "Weather unavailable" while the rest
of the page remains available.

---

## Automation

```
visitor submits form -> JS validation -> POST to REST endpoint
   -> nonce check -> sanitise -> validate
   -> stored as an Enquiry -> wp_mail() notification -> 201 response
```

The notification is sent with `wp_mail()`. If it fails, the enquiry is still
saved and a `_agf_mail_failed` flag is set, because losing a lead to a mail
server problem would be worse than a missing email.

---

## Security Considerations

| Concern | Approach |
|---|---|
| SQL injection | No SQL is written. `WP_Query`, `wp_insert_post()` and `update_post_meta()` handle escaping. |
| XSS | Custom PHP output escapes untrusted values with context-appropriate WordPress escaping functions; REST-loaded project cards are built with `createElement`/`textContent` rather than injecting user data through `innerHTML`. |
| CSRF | Nonces on the meta box save and on the enquiry endpoint. |
| Privilege escalation | `current_user_can( 'edit_post', $post_id )` before writing meta. |
| Untrusted input | Sanitised on input, then validated against allowlists for project type and budget. |
| Bypassed client validation | The server re-runs the validation rules. `docs/04-TESTING.md` includes a direct REST POST test designed to verify this manually. |
| Resource exhaustion | `per_page` range-validated. |
| Secrets | No application secret is present in the source bundle; `wp-config.php` and `.env*` are gitignored, and the Open-Meteo request contains no key. |
| Direct file access | WordPress-dependent theme/plugin PHP files guard against direct access with `ABSPATH`; the pure helper file intentionally has no WordPress dependency so PHPUnit can load it directly. |
| Information leakage | Public failures use user-facing messages; front-end request details are logged to the browser console and mail failure can be logged to `debug.log` when `WP_DEBUG` is enabled. |

---

## Testing

**Automated checks configured in the repository:** `php -l` across PHP files under
`wp-content`, PHPUnit for the pure helper functions, and ESLint for the theme's
JavaScript.

The PHPUnit suite deliberately does not boot WordPress or a database. Its scope
is comma-splitting, completion-date parsing (including impossible dates such as
`2025-02-30`) and the enquiry validator, including allowlist rejection cases.

**Manual test plan:** `docs/04-TESTING.md` contains an unchecked matrix covering
375px, 768px and 1280px layouts, navigation/templates, filtering, API loading and
failure states, form validation, stored enquiries, local mail capture and a
direct REST POST intended to bypass browser validation. The ZIP contains the
test plan; it does not record those manual checks as completed.

```bash
composer install && vendor/bin/phpunit --testdox
npm install && npm run lint:js
```

---

## Continuous Integration

`.github/workflows/ci.yml` runs on pushes to `main` and pull requests targeting
`main`:

```
push to main / PR targeting main -> clean Ubuntu runners
   -> PHP job: php -l on PHP files under wp-content
              -> composer install -> PHPUnit
   -> JavaScript job: npm install -> ESLint
   -> pass or fail on the commit
```

Two parallel jobs, so a PHP failure and a JavaScript failure are reported
separately. The value is that a broken commit is caught by a machine before a
reviewer — or a client — sees it.

---

## Installation

Full step-by-step instructions, including the debug configuration to set up
first, are in **`docs/01-INSTALL.md`**. The plugin header declares WordPress 6.0+
and PHP 8.0+, while `composer.json` also requires PHP 8.0 or newer. In short:

1. Create a WordPress site in LocalWP.
2. Copy `wp-content/themes/agencyflow/` and
   `wp-content/plugins/agencyflow-project-manager/` into the site.
3. Activate the **plugin first**, then the theme.
4. **Settings → Permalinks → Post name → Save.**
5. Create a Page with the slug `contact`.
6. Add projects under **Projects**.

---

## Documentation Included

The source bundle includes six supporting documents:

- `docs/01-INSTALL.md` — LocalWP setup, debugging, activation order, content setup
  and troubleshooting
- `docs/02-CONTENT.md` — nine fictional project entries to populate the site
- `docs/03-CODE-WALKTHROUGH.md` — guided walkthrough of the implementation
- `docs/04-TESTING.md` — automated commands plus an unchecked manual test matrix
- `docs/05-GIT-PLAN.md` — a proposed commit, feature-branch and pull-request
  workflow; the ZIP itself contains no `.git` history
- `docs/06-INTERVIEW-PREP.md` — project/interview preparation notes

No screenshot image files are included in the supplied ZIP, so this README does
not claim screenshots or completed manual-test evidence that are not present in
the archive.

---

## What I Learned

- WordPress's template hierarchy replaces a router entirely: name the file
  correctly and WordPress finds it.
- Hooks are the whole extensibility model. You never modify core; you register to
  be called at a specific point in the request lifecycle.
- The theme/plugin split is an architectural decision with real consequences for a
  client, not a filing convention.
- Sanitising and escaping are different jobs at different moments — clean on the
  way in, make safe for context on the way out.
- Client-side validation is a convenience; the server is the only place
  validation actually happens.
- `fetch` resolving on a 404 rather than rejecting means the happy path can look
  finished while the error path is completely unhandled.
- Building layouts with Flexbox and Grid directly, rather than reaching for a
  framework, made it much clearer *which* problem each one is for.

---

## Future Improvements

1. **One source of truth for validation rules** — the browser and the server
   currently duplicate them and could drift. Exposing the rules through the REST
   API would fix this.
2. **WordPress integration tests** — the current suite covers pure functions only.
   The meta save and the REST routes need the WordPress test suite.
3. **Server-side filtering with progressive enhancement** — filtering by hiding
   cards already in the page won't scale past one page of results, and doesn't
   work without JavaScript.
4. **Nonce handling under full-page caching** — a cached page would serve a stale
   nonce and the form would begin returning 403s. The nonce should be fetched
   separately.
5. **Enquiry rate limiting** — nothing currently stops repeated automated
   submissions.
6. **Accessibility audit with a real screen reader** — the markup and ARIA were
   written carefully, but written carefully is not the same as tested.
7. **Public deployment** — the project runs locally; hosting was out of scope for
   the time available.

---

## Licence

GPL-2.0-or-later, matching WordPress.

Built by Bo Kwok, Manchester.
