# 01 — Installation

Work through this in order. It should take 45–90 minutes including the debugging
that always happens.

---

## Step 1 — Create the local site (15 min)

1. Download **LocalWP** from `https://localwp.com` and install it.
2. **Quit Laravel Herd first** if it is running — Herd and LocalWP compete for
   the same ports and it causes confusing failures.
3. In Local: **+ Create a new site** → **Create a new site**.
4. Site name: `agencyflow`
5. Environment: **Preferred** (accept the PHP 8.x / MySQL / nginx defaults).
6. WordPress username `bo`, a password you'll remember, your own email.
7. **Add site**, wait for the build, then **Start site**.

Your WordPress root will be at roughly:

```
C:\Users\cronk\Local Sites\agencyflow\app\public
```

Open **that folder** in VS Code.

---

## Step 2 — Turn on debug logging BEFORE anything else (5 min)

Do this first. It is the difference between "white screen, no idea why" and
"line 47 of meta-fields.php".

Open `wp-config.php` in the WordPress root. Find the `WP_DEBUG` line and
replace it with:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Errors now land in `wp-content/debug.log`. When something breaks, read that file
first.

---

## Step 3 — Copy the bundle in (5 min)

From the unzipped bundle, copy into your WordPress root:

| Copy this | To here |
|---|---|
| `wp-content/themes/agencyflow/` | `app/public/wp-content/themes/agencyflow/` |
| `wp-content/plugins/agencyflow-project-manager/` | `app/public/wp-content/plugins/agencyflow-project-manager/` |
| `.gitignore` | `app/public/.gitignore` |
| `.github/` | `app/public/.github/` |
| `docs/` | `app/public/docs/` |
| `tests/` | `app/public/tests/` |
| `README.md`, `composer.json`, `package.json`, `phpunit.xml`, `eslint.config.js` | `app/public/` |

Merge into the existing `wp-content` folder — don't replace it, or you'll delete
WordPress's own themes and the uploads directory.

---

## Step 4 — Activate, in this order (5 min)

Order matters. The plugin registers the Project post type, and the theme's
templates assume it exists.

1. **Plugins → Installed Plugins → AgencyFlow Project Manager → Activate**
   This also creates the four project types and flushes the URL rules.
2. **Appearance → Themes → AgencyFlow → Activate**

You should now see **Projects** and **Enquiries** in the admin sidebar, and
**Projects → Project Types** should already contain Website, E-commerce,
Automation and Web Application.

---

## Step 5 — WordPress settings (10 min)

1. **Settings → General** — Site Title `AgencyFlow`, Tagline
   `Digital projects, delivered.`, Timezone `London`.
2. **Settings → Permalinks** → **Post name** → **Save Changes**.
   Save it even if it's already selected — saving is what rebuilds the URL
   rules. **If `/projects/` gives you a 404 at any point, this is the fix.**
3. **Pages → Add New** → title it exactly **Contact** → Publish.
   Check the slug is `contact` in the sidebar. The template file is
   `page-contact.php`, and WordPress matches it by slug — if the slug ends up
   `contact-2` because a page called Contact already exists, the form will not
   appear.
4. **Appearance → Menus** → create a menu called `Primary`, add Home, Projects
   and Contact, tick **Primary Menu** as the display location, Save.
   *(If you skip this, the theme falls back to a built-in nav, so it won't
   break — but do it, it's 60 seconds.)*
5. **Posts** → delete "Hello world!". **Plugins** → delete Akismet and Hello
   Dolly.

---

## Step 6 — Add the content (30 min)

Open `docs/02-CONTENT.md` and enter the nine projects. Each one needs:

- Title, and the description into the main editor
- **Excerpt** — the card layout uses it, so don't skip it
  *(if you can't see the Excerpt box: three dots menu top-right → Preferences →
  Panels → Excerpt)*
- **Featured image** — grab free photos from `unsplash.com`; anything roughly
  landscape is fine
- **Project Type** — tick one or two
- The four **Project Details** fields

Nine projects across four types is enough for the filter to look real.

---

## Step 7 — Check it works (10 min)

Visit `http://agencyflow.local` and confirm:

- [ ] Home page loads with styling
- [ ] The weather strip shows a real Manchester temperature within a second or two
- [ ] "Recent work" fills with three project cards
- [ ] `/projects/` shows all nine in a grid
- [ ] Clicking a filter button hides the non-matching cards instantly
- [ ] Clicking a project title opens its detail page with the facts panel
- [ ] `/contact/` shows the form
- [ ] Submitting it empty shows red messages under each field
- [ ] Submitting it valid shows a green success message
- [ ] **Enquiries** in the admin contains your submission
- [ ] LocalWP → your site → **Tools → Mailpit** (or **Mailhog**) contains the
      notification email

Also open the API directly in a browser tab:

```
http://agencyflow.local/wp-json/agencyflow/v1/projects
```

You should see raw JSON. That is a good thing to have open in a tab during the
interview.

---

## Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| White screen | PHP fatal error | Read `wp-content/debug.log` |
| Theme not in Appearance → Themes | Folder nested wrongly, or `style.css` header missing | `style.css` must be directly inside `themes/agencyflow/` |
| `/projects/` returns 404 | Rewrite rules stale | Settings → Permalinks → Save |
| No styling | CSS not loading | DevTools → Network, look for a 404 on `main.css`; check the folder is `assets/css/` |
| Filter buttons do nothing | JS not loading | DevTools → Console for errors; the script only loads on the projects archive |
| Contact form missing | Page slug isn't `contact` | Edit the page, fix the slug |
| Form says "session expired" | Nonce is over 24h old | Reload the page |
| Weather says unavailable | No internet, or Open-Meteo down | Check DevTools → Network; the page must still work otherwise |
| Enquiry saves but no email | Local mail not captured | Check Mailpit in LocalWP; `_agf_mail_failed` meta is set if `wp_mail` returned false |
