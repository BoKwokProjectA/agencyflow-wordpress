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
API, and submit a project enquiry that is validated, stored and automatically
notified to the team.

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
for anything the project is meant to demonstrate; the custom post type, the REST
endpoint and the enquiry handling are all my own code.

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
- An automated email notification when a valid enquiry is received
- Escaped output and sanitised input throughout, with nonce protection on
  state-changing requests
- Unit tests on the pure helper functions
- A CI pipeline running PHP linting, PHPUnit and ESLint on every push

---

## Technology Stack

| Layer | Technology |
|---|---|
| CMS | WordPress |
| Server language | PHP 8 |
| Database | MySQL, accessed only through WordPress APIs |
| Markup | Semantic HTML5 |
| Styling | Hand-written CSS — custom properties, Flexbox, Grid, media queries |
| Client scripting | Vanilla JavaScript — DOM API, Fetch, async/await |
| Internal API | WordPress REST API, custom namespace |
| External API | Open-Meteo |
| Version control | Git and GitHub, with a feature branch and pull request |
| CI | GitHub Actions |
| Testing | PHPUnit, ESLint, and a documented manual test matrix |
| Local environment | LocalWP |

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

- `add_theme_support()` for the title tag, featured images and HTML5 markup
- One registered menu location, with a PHP fallback so the header is never empty
- Assets loaded with `wp_enqueue_style()` and `wp_enqueue_script()`, with a
  version string for cache busting and a declared dependency between the two
  stylesheets
- Scripts enqueued **conditionally** — the filter script only loads on the
  projects archive, the enquiry script only on the contact page
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

All four check `response.ok`, because `fetch` resolves rather than rejects on a
404 or 500.

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
| 201 | Created — enquiry stored and notification sent |
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

It needs no API key for non-commercial use, which is why the call can safely live
in client-side JavaScript. If a key were required it could not go in a JS file at
all — anything shipped to the browser is public — and would need to sit in PHP
and be proxied.

Both failure modes are handled: a network failure rejects the promise, and a bad
parameter returns a 400 with a JSON error body, which `response.ok` catches. In
either case the strip shows "Weather unavailable" and the rest of the page is
unaffected.

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
| XSS | Every dynamic value escaped on output with `esc_html()`, `esc_attr()` or `esc_url()`. JavaScript builds DOM nodes with `textContent`, never `innerHTML` with user data. |
| CSRF | Nonces on the meta box save and on the enquiry endpoint. |
| Privilege escalation | `current_user_can( 'edit_post', $post_id )` before writing meta. |
| Untrusted input | Sanitised on input, then validated against allowlists for project type and budget. |
| Bypassed client validation | The server re-runs every rule. Verified by POSTing invalid data straight to the endpoint from the console. |
| Resource exhaustion | `per_page` range-validated. |
| Secrets | None in the repository. `wp-config.php` is gitignored, and the external API needs no key. |
| Direct file access | Every PHP file exits unless `ABSPATH` is defined. |
| Information leakage | Errors give visitors a plain sentence; detail goes to the console or `debug.log`. |

---

## Testing

**Automated:** PHP syntax checking with `php -l` across every file, PHPUnit
covering the pure helper functions, and ESLint on the JavaScript.

The PHPUnit suite deliberately does not boot WordPress, which is why it runs in
under a second. Its scope is the logic worth protecting: comma-splitting, date
parsing including impossible dates like `2025-02-30`, and every branch of the
enquiry validator — including the case where a project type outside the allowlist
is rejected.

**Manual:** a documented matrix in `docs/04-TESTING.md` covering three
breakpoints, navigation, every template, the filter, both API integrations under
throttled and offline conditions, and eight form-submission cases including a
deliberate server-side bypass test.

```bash
composer install && vendor/bin/phpunit --testdox
npm install && npm run lint:js
```

---

## CI/CD

`.github/workflows/ci.yml` runs on every push to `main` and every pull request:

```
push / PR -> clean Ubuntu runner
   -> php -l on every PHP file
   -> composer install -> PHPUnit
   -> npm install -> ESLint
   -> pass or fail on the commit
```

Two parallel jobs, so a PHP failure and a JavaScript failure are reported
separately. The value is that a broken commit is caught by a machine before a
reviewer — or a client — sees it.

---

## Installation

Full step-by-step instructions, including the debug configuration to set up
first, are in **`docs/01-INSTALL.md`**. In short:

1. Create a WordPress site in LocalWP.
2. Copy `wp-content/themes/agencyflow/` and
   `wp-content/plugins/agencyflow-project-manager/` into the site.
3. Activate the **plugin first**, then the theme.
4. **Settings → Permalinks → Post name → Save.**
5. Create a Page with the slug `contact`.
6. Add projects under **Projects**.

---

## Screenshots

*Home page. The weather strip is fetched from Open-Meteo after the page loads.*
![Home page with live weather](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20home%20page.png)

*All nine projects in a CSS Grid layout.*
![Projects archive](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20all%20project.png)

*The same page after clicking a filter — no page reload, no network request.*
![Filtered to Automation](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20project%20automation%20category.png)

*A single project. The facts panel is built from custom meta fields; the completion date is stored as `YYYY-MM-DD` and reformatted for display.*
![Project detail page](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20project%20detail.png)

*The archive at 375px. Mobile-first CSS with breakpoints at 600px and 900px.*
![Mobile layout](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20mobile%20view.png)

*Client-side validation. Errors appear under the field they belong to, linked with `aria-describedby`, and focus moves to the first invalid input.*
![Form validation errors](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20contact%20form%20with%20red%20validation%20errors.png)

*A valid enquiry. The button shows a loading state while the request is in flight.*
![Successful submission](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20contact%20form%20without%20red%20validation%20errors.png)

*The enquiry saved as a custom post type, with the submitted fields shown in a read-only meta box.*
![Enquiry stored in the admin](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20add%20new%20contract.png)

*The automated notification, captured locally by Mailpit. Sent with `wp_mail()` after the enquiry is validated and stored.*
![Automated notification email](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20notification%20email%20in%20mailbox.png)

*The custom endpoint at `/wp-json/agencyflow/v1/projects`, returning only the fields the front end needs rather than the full core response.*
![Custom REST API response](https://github.com/BoKwokProjectA/agencyflow-wordpress/blob/main/AgencyFlow%20raw%20json.jpeg)


---

## Challenges and Solutions

_To be completed with the real problem encountered during setup — see
`docs/06-INTERVIEW-PREP.md` for the structure. Not filled in speculatively._

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
