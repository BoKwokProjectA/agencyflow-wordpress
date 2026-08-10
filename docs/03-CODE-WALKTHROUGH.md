# 03 — Code walkthrough

Organised by how well you need to know each file, not by folder. Tier 1 is your
actual homework: roughly 250 lines.

---

## TIER 1 — know these line by line

### `assets/js/filter.js` — the project filter

The most likely thing you'll be asked to talk through, because it's the clearest
demonstration of events + DOM.

**Say it in one breath:** *"PHP renders every project card with a
`data-types` attribute. When a filter button is clicked, I read the type off the
button's dataset, loop over all the cards, and toggle an `is-hidden` class on the
ones that don't match. CSS does the hiding. No page reload and no network
request, because the data is already in the page."*

| Question | Answer |
|---|---|
| What event fires? | `click` on a `<button>` |
| Why `<button>` not `<div>`? | Buttons are focusable and fire click on Enter/Space for free. A div needs `tabindex`, `role` and key handlers to match. |
| How is the element selected? | `document.querySelector('#project-filters')` for the bar, `querySelectorAll('.project-card')` for the cards |
| What is `dataset`? | The JS API for `data-*` attributes. `data-types="website automation"` becomes `card.dataset.types` |
| What changes in the DOM? | The `is-active` class on buttons, `is-hidden` on cards, `aria-pressed`, and the status text |
| Why `textContent` not `innerHTML`? | `textContent` treats the value as plain text, so it can never execute injected markup |
| Why `classList.toggle(class, boolean)`? | Adds when the second argument is true, removes when false — one line instead of an if/else |
| Where does the hiding actually happen? | CSS: `.project-card.is-hidden { display: none; }`. JS decides *what*, CSS decides *how*. |

**Trade-off to volunteer:** this filters cards already in the page, so it doesn't
scale past a page of results. With hundreds of projects I'd request a filtered
page from the REST endpoint instead — which is the same pattern
`featured-projects.js` already uses.

### `assets/js/enquiry.js` — the form

| Question | Answer |
|---|---|
| What event? | `submit` on the `<form>` |
| Why `preventDefault()`? | Stops the browser's default full-page POST so we can send it with `fetch` and stay on the page |
| Where do values come from? | `new FormData(form)`, then `.get('name')` etc. |
| Why validate here at all if PHP validates too? | Speed and UX — instant feedback with no round trip |
| Why is that not enough? | Anyone can delete this file in devtools and POST directly. **Client-side validation is convenience; server-side is the gate.** |
| What's the loading state? | Button disabled and relabelled "Sending…", status text set |
| Why disable the button? | Prevents a double submission from an impatient second click |
| What does `finally` do? | Runs whether the request succeeded or threw, so the button can never be left stuck disabled |
| What statuses are handled? | 201 created, 422 validation errors, 403 stale nonce, anything else → generic error |
| How do errors reach the right field? | The server returns `{errors: {email: "..."}}`; the JS looks up `[name="email"]` and writes into `#error-email` |
| How is that accessible? | `aria-describedby` links the input to its error element, `aria-invalid` marks it, and focus moves to the first broken field |

### `includes/rest-api.php` → `agencyflow_rest_create_enquiry()`

The six-step journey, in order. Learn the order:

```
nonce check -> sanitise -> validate -> save -> notify -> respond
```

| Question | Answer |
|---|---|
| What's a nonce? | A one-time token proving the request came from a form WordPress rendered for this user. It stops cross-site request forgery. |
| Why sanitise before validate? | Sanitising makes the data safe to handle; validating decides whether it's acceptable. Safe first, then judged. |
| Sanitise vs escape? | **Sanitise on input** (clean what comes in), **escape on output** (make it safe for where it's going). Different jobs, different moments. |
| Which functions? | `sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`, `esc_url_raw` |
| Why 422 not 400? | 400 means malformed request; 422 means well-formed but the contents failed our rules |
| Why 201 not 200? | 201 Created is correct when the request created a new resource |
| How is SQL injection avoided? | No SQL is written at all — `wp_insert_post()` and `update_post_meta()` handle escaping. Using the framework's data API *is* the defence. |
| Why `permission_callback => '__return_true'`? | The public must be able to submit. The nonce check inside the callback is what actually gates it. |

### `archive-project.php` — The Loop

```php
while ( have_posts() ) : the_post();
```

`have_posts()` asks "is there another post in the query results?".
`the_post()` advances to it and sets up the global post data that `the_title()`,
`the_excerpt()` and `the_permalink()` read from. That's why those functions take
no arguments — they read the current post in the loop.

**Why `esc_html()` on output:** escaping happens at the point of output because
that's the only place you know the context. The same string needs `esc_html()`
inside HTML, `esc_attr()` inside an attribute, and `esc_url()` in an `href`.

**The one exception:** `the_content()` is not escaped, because WordPress has
already filtered it and the author is a trusted logged-in editor. Escaping it
would print visible HTML tags on the page.

### `assets/css/main.css` — the layout decisions

You will be asked "when do you use Flexbox and when do you use Grid?". Answer
with your own code:

**Flexbox — one-dimensional, items size to their content:**
- `.site-header__inner` — logo one side, nav the other. `flex-direction: column`
  on mobile, `row` from 600px.
- `.site-nav__list`, `.filter-bar`, `.project-meta` — rows that wrap
- `.project-card` — `flex-direction: column` so every card's metadata sits at
  the same height regardless of title length

**Grid — two-dimensional, columns I control:**
- `.project-grid` — `repeat(auto-fit, minmax(min(100%, 280px), 1fr))`.
  The browser picks the column count from available width: one on a phone, two
  on a tablet, three on a desktop, **with no media query for the columns at
  all.** That's the single best thing to point at.
- `.project-layout` — one column on mobile, `2fr 1fr` content-plus-aside from
  900px
- `.field-row` — form fields side by side from 600px

**Mobile first:** base styles are the mobile layout; each `min-width` query adds
capability. The alternative (desktop first with `max-width`) means writing rules
then undoing them.

**Breakpoints:** 600px tablet, 900px desktop. Chosen from where the layout
starts to look wrong, not from device names.

**Two details worth mentioning unprompted:**
- `:focus-visible` outlines — keyboard users need to see where they are
- `prefers-reduced-motion` in `style.css` — respects the OS accessibility setting

---

## TIER 2 — know the concept, not the syntax

### `functions.php`

- **What's a hook?** `add_action('after_setup_theme', 'my_function')` says
  "WordPress, when you reach this point, run my function." You never edit
  WordPress core — you register to be called. That's what makes WordPress
  extensible.
- **Action vs filter?** An **action** does something and returns nothing. A
  **filter** receives a value, changes it, and **must return it**. This project
  has both: `agencyflow_enqueue_assets` is an action;
  `agencyflow_excerpt_length` is a filter.
- **Why `wp_enqueue_style()` instead of a `<link>` tag?** WordPress can then
  deduplicate files, resolve dependency order, and append a version for cache
  busting. Hardcoded tags get none of that.
- **Why conditional enqueueing?** A visitor on the contact page has no use for
  the filter script. `is_front_page()`, `is_post_type_archive('project')` and
  `is_page('contact')` load only what's needed.
- **What is `wp_localize_script()`?** It passes data from PHP into JavaScript.
  Never hardcode a REST URL in a JS file — the site could move domain or live in
  a subdirectory. The nonce has to come from PHP too, because only the server can
  generate one.

### `includes/post-types.php`

- **What's a custom post type?** A kind of content that isn't a post or a page.
  Projects need their own admin screen, URLs and fields, so they get their own
  type.
- **Why is this in a plugin, not the theme?** *The single most important
  architectural point in the project.* The plugin owns the data; the theme owns
  the presentation. Switch theme and every project survives. Registering content
  types inside a theme destroys the client's data the day they redesign.
- **What's a taxonomy?** A way of grouping content — the same mechanism as blog
  categories, attached to projects. It's what the filter buttons filter by.
- **Why `'public' => false` on enquiries?** No front-end URL, so nobody can
  browse other people's enquiries.

### Template hierarchy

WordPress picks a template by filename. There's no router:

| URL | Looks for, in order |
|---|---|
| `/` | `front-page.php` → `home.php` → `index.php` |
| `/projects/` | `archive-project.php` → `archive.php` → `index.php` |
| `/projects/some-project/` | `single-project.php` → `single.php` → `singular.php` → `index.php` |
| `/contact/` | `page-contact.php` → `page.php` → `singular.php` → `index.php` |

`index.php` is the last resort and every theme must have one.

### `assets/js/weather.js` and the REST fetch

- **Why an API at all?** So one system can use another system's data without a
  human copying it, and without either side knowing how the other is built.
- **Request → response:** the browser sends `GET` with parameters in the query
  string; the service returns JSON; `response.json()` parses it into a JS object;
  the DOM is updated.
- **The gotcha worth stating:** `fetch()` only rejects on a *network* failure. A
  404 or 500 still resolves successfully, so you must check `response.ok`
  yourself. This catches a lot of people out.
- **No API key here** — Open-Meteo needs none for non-commercial use. If it did,
  the key could not live in a JS file, because anything in JavaScript is public.
  It would have to sit in PHP and be proxied server-side.
- **Why `URLSearchParams`?** It encodes each value properly. Hand-concatenated
  query strings are where encoding bugs come from.

### Semantic HTML

Why it matters, four reasons — have all four ready:

1. **Accessibility** — `<nav>`, `<main>`, `<header>` become landmarks a screen
   reader user can jump between. A page of `<div>`s has no landmarks.
2. **SEO** — search engines use structure to work out what a page is about, and
   one clear `<h1>` tells them the subject.
3. **Maintainability** — `<article class="project-card">` tells the next
   developer what it is. `<div class="pc">` doesn't.
4. **Document structure** — headings are an outline, not font sizes. On the
   archive: `h1` is the page, `h2` the section, `h3` each card title. Never skip
   a level to get a smaller font — that's what CSS is for.

`<aside>` in `single-project.php` is genuinely tangential content, not just "the
thing on the right" — that's the distinction that matters.

---

## TIER 3 — recognise it, state its purpose, move on

Nobody quizzes a junior on YAML.

| File | One-sentence answer |
|---|---|
| `.gitignore` | Keeps WordPress core, `wp-config.php` and uploads out of the repo — core is third-party, and `wp-config.php` holds database credentials and secret salts |
| `.github/workflows/ci.yml` | On every push and PR, GitHub runs PHP syntax checks, PHPUnit and ESLint, so a broken commit is caught by a machine |
| `phpunit.xml` / `tests/bootstrap.php` | Points PHPUnit at the tests and loads the helpers without booting WordPress |
| `eslint.config.js` | Catches undeclared variables, `==` instead of `===`, and accidental globals |
| `composer.json` / `package.json` | Declare the dev dependencies (PHPUnit, ESLint) |
| `template-parts/project-card.php` | The card markup in one file, so a design change happens once instead of in every template |

---

## The three sentences to have absolutely ready

1. **"The plugin owns the data, the theme owns the presentation — so if the
   client changes theme, every project and enquiry survives."**
2. **"Client-side validation is for speed; server-side validation is the actual
   gate, because anyone can bypass the browser."**
3. **"Sanitise on the way in, escape on the way out."**
