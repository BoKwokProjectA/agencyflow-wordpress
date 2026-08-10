# 04 — Testing

Two kinds: automated checks that a machine runs, and a manual matrix you work
through by hand. Both matter, and being able to say why is the point.

---

## Automated

### PHP syntax check

`php -l` parses a file without running it, catching a missing semicolon or
bracket that would otherwise white-screen the whole site.

From the WordPress root, using the PHP that came with Laravel Herd:

```bash
find wp-content/themes/agencyflow wp-content/plugins/agencyflow-project-manager -name "*.php" -print0 | xargs -0 -n1 php -l
```

Every line should read `No syntax errors detected`.

### PHPUnit

```bash
composer install
vendor/bin/phpunit --testdox
```

Expect **20 passing tests**.

They cover the three pure helper functions — comma-splitting, date formatting and
enquiry validation. Nothing here boots WordPress, which is exactly why the suite
runs in under a second and needs no database.

**Be honest about the scope in the interview:** these are unit tests on the pure
logic. The WordPress-dependent code is covered by manual testing, because setting
up the full WordPress test suite wasn't a good use of the time available. That's
a defensible engineering trade-off and saying so is stronger than pretending
otherwise.

The most interesting test to point at is
`test_project_type_outside_the_allowed_list_is_rejected`. The dropdown only offers
four values, but a request sent straight to the endpoint can contain anything —
so the server checks against an allowlist. That test documents a security
decision, not just a behaviour.

Second most interesting: `test_format_completion_date_rejects_impossible_dates`.
`2025-02-30` has the right *shape*, so a regular expression alone lets it
through. That's why the function also calls `checkdate()`.

### ESLint

```bash
npm install
npm run lint:js
```

---

## Manual test matrix

Work through this and tick it off. Screenshot anything that looks good — you need
screenshots for the README.

### Responsive layout

Chrome DevTools → device toolbar (Ctrl+Shift+M).

| Width | Check | Pass |
|---|---|---|
| 375px (mobile) | One card per row; header stacks; nav wraps; form fields full width | [ ] |
| 768px (tablet) | Two cards per row; header goes side by side; name/email side by side | [ ] |
| 1280px (desktop) | Three cards per row; project detail shows content + facts panel | [ ] |
| 375px | No horizontal scrollbar anywhere | [ ] |

### Navigation and templates

| Check | Pass |
|---|---|
| Home page loads and is styled | [ ] |
| `/projects/` shows all nine projects | [ ] |
| Clicking a card title opens the right project | [ ] |
| Project detail shows client, completed date, technologies, live link | [ ] |
| `/contact/` renders the form | [ ] |
| Clicking a type tag on a detail page shows that type's archive | [ ] |
| Skip link appears when you press Tab on page load | [ ] |
| Every interactive element shows a visible focus ring when tabbed to | [ ] |

### Project filtering

| Check | Pass |
|---|---|
| "All" is active on page load | [ ] |
| Clicking "Automation" leaves only automation projects visible | [ ] |
| The status line updates to "Showing 2 of 9 projects." | [ ] |
| Only one button is highlighted at a time | [ ] |
| Clicking "All" restores everything | [ ] |
| Filtering with the keyboard (Tab then Enter) works | [ ] |
| No full page reload happens (the URL never changes) | [ ] |

### API loading and error handling

| Check | How | Pass |
|---|---|---|
| Weather loads | Reload the home page | [ ] |
| Weather loading state | DevTools → Network → throttle to Slow 3G, reload | [ ] |
| Weather error state | DevTools → Network → set to Offline, reload; strip should turn red and say "Weather unavailable", **and the rest of the page must still work** | [ ] |
| Featured projects load | Home page "Recent work" fills with three cards | [ ] |
| Featured projects error | Offline mode; should show "Could not load projects right now" | [ ] |
| Endpoint returns JSON | Open `/wp-json/agencyflow/v1/projects` directly | [ ] |
| Endpoint filter works | Open `/wp-json/agencyflow/v1/projects?type=automation` | [ ] |
| Bad parameter is rejected | Open `?per_page=500` — expect a 400 with an error message, not 500 results | [ ] |

### Form validation

| Case | Expected | Pass |
|---|---|---|
| Submit completely empty | Five red messages, focus jumps to Name, no request sent | [ ] |
| Email `notanemail` | "Enter a valid email address" | [ ] |
| Message of 5 characters | "at least 20 characters" | [ ] |
| Fix a field | Its error clears as you type | [ ] |
| Valid submission | Green success message, form clears | [ ] |
| Enquiry stored | Appears under **Enquiries** in the admin with all fields | [ ] |
| Notification sent | Appears in LocalWP's Mailpit/Mailhog | [ ] |
| Submit twice quickly | Button disables, only one enquiry created | [ ] |

### Backend validation bypass test — do this one, it's your best security story

This proves the server doesn't trust the browser. Open DevTools → Console on the
contact page and run:

```javascript
fetch(agencyflowData.restUrl + 'enquiries', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'X',
    email: 'nope',
    project_type: 'Something I invented',
    budget: 'free',
    message: 'short',
    nonce: agencyflowData.nonce
  })
}).then(r => r.json()).then(console.log);
```

**Expected:** a 422 response with an errors object naming every bad field —
including `project_type`, because it isn't on the server's allowlist.

Then run it again with `nonce: 'rubbish'`. **Expected:** 403.

That's the demo that shows you understand why server-side validation exists. Have
it ready to run.

---

## Code review checklist

Use this on your own pull request, and paste it into the PR description.

- [ ] Does it do what the branch name says, and nothing else?
- [ ] Is all output escaped — `esc_html`, `esc_attr`, `esc_url`?
- [ ] Is all input sanitised before use?
- [ ] Are nonces checked on every state-changing request?
- [ ] Are capabilities checked before saving?
- [ ] Any hardcoded URLs that should use `home_url()` or `rest_url()`?
- [ ] Any secrets, keys or credentials in the diff?
- [ ] Are function names prefixed to avoid collisions with other plugins?
- [ ] Does the JavaScript handle the failure path, not just the happy path?
- [ ] Is there a loading state for anything asynchronous?
- [ ] Does it work at 375px?
- [ ] Is it keyboard operable, with a visible focus style?
- [ ] Do the tests and linters pass?
- [ ] Any leftover `console.log` or commented-out code?
- [ ] Would a developer joining next month understand it without asking?
