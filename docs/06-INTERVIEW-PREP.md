# 06 — Interview preparation

Tuesday 11 August, 4:30pm, Bonded Warehouse, 18 Lower Byrom Street, M3 4AP.

---

## First, the thing nobody else will tell you

You did not type most of this code. If you're asked directly — *"did you write
this yourself?"* — say yes to the design decisions and be straight about the
assistance. Something like:

> "I specified it and I used an AI assistant to help write it, then went through
> it line by line to make sure I understood every part. I can talk through any
> file in it."

That is a **good** answer at a modern agency, and it is the truth. What would
sink you is claiming you hand-wrote it and then stalling on your own filter
function. Agencies increasingly use these tools daily; what they're testing is
whether you understand what you shipped.

Then make the second half true. Spend two hours on Tier 1 of
`03-CODE-WALKTHROUGH.md` and the bypass test in `04-TESTING.md`. Read
`filter.js` and `enquiry.js` out loud, explaining each block to an empty room.
It feels stupid and it works.

---

## Q1. "Tell us about a website or application you've built."

Two minutes maximum. Structure: what and why → what's in it → what it taught you.

> "I built AgencyFlow, a WordPress site for a fictional Manchester digital
> agency. I chose it deliberately — my degree and previous projects were heavier
> on Python, React and Laravel, and I wanted something that proved I could work
> the way this role actually works: WordPress, PHP, and plain JavaScript with no
> framework underneath it.
>
> It's a custom theme and a custom plugin, both written from scratch — no page
> builder, no CSS framework. The plugin registers a Project content type with its
> own fields and taxonomy, so the client manages everything from the WordPress
> admin. The theme renders it with semantic HTML, Flexbox for the one-dimensional
> pieces like the header and metadata rows, and CSS Grid for the project
> listings.
>
> The JavaScript is all vanilla. There's a filter on the projects page that
> shows and hides cards by category with no page reload, a fetch call to a REST
> endpoint I built inside the plugin, and one to Open-Meteo for live Manchester
> conditions — both with proper loading and error states.
>
> There's also an enquiry form that validates in the browser for speed and then
> again in PHP, because the browser can't be trusted. Valid enquiries are stored
> and trigger an automated notification email.
>
> It's version controlled on GitHub with a feature branch and a pull request, and
> GitHub Actions runs PHP linting, PHPUnit and ESLint on every push."

**Then stop talking.** Let them pick what to dig into.

---

## Q2. "What was the most challenging part?"

**You must fill this in with a real problem from your own install.** Do not use an
invented one — it falls apart under two follow-up questions, and it would be a
lie.

You will hit at least one of these during setup. When you do, write it up here
immediately while it's fresh:

| Likely problem | Why it happens |
|---|---|
| `/projects/` returns 404 | WordPress caches URL rewrite rules; a new post type isn't in the cache until they're flushed |
| Contact form doesn't appear | The page slug isn't `contact`, so `page-contact.php` never matches |
| Project fields save as empty | Nonce, capability or post-type guard rejecting the save silently |
| CSS or JS 404s | Asset path wrong relative to the theme folder |
| Form returns 403 | Nonce not reaching the endpoint |
| Weather strip empty | Response shape not what the code expected |

Write it up in this shape:

```
SITUATION      What I was doing when it happened.
PROBLEM        The exact symptom. Quote the error if there was one.
INVESTIGATION  What I checked, in order, and what I ruled out.
CAUSE          The actual reason.
SOLUTION       What I changed.
TESTING        How I confirmed it was fixed.
LEARNED        The general principle, not just the fix.
```

**Worked example, if the 404 is the one you hit:**

> **Situation:** I'd registered the Project post type in the plugin and added the
> archive template, and I wanted to see the listing page.
>
> **Problem:** `/projects/` returned a WordPress 404, but individual projects
> were visible in the admin and the post type appeared in the menu, so the
> registration itself was clearly working.
>
> **Investigation:** I confirmed `has_archive` was set to true. I checked the post
> type was registered on `init` rather than too late. I confirmed permalinks were
> set to Post name. Then I noticed the 404 was coming from WordPress's routing
> rather than from a missing template, which pointed at URL handling rather than
> at my template file.
>
> **Cause:** WordPress caches its rewrite rules in the database and only rebuilds
> them when asked. A brand new post type adds new URL patterns that aren't in the
> cached rules, so `/projects/` didn't match any known route.
>
> **Solution:** Calling `flush_rewrite_rules()` from the plugin's activation hook,
> after the post types are registered. Activation-time rather than on every
> request, because flushing is expensive and doing it per page load would slow
> the whole site.
>
> **Testing:** Deactivated and reactivated the plugin, confirmed `/projects/`
> loaded, then confirmed individual project URLs and the taxonomy archives worked
> too.
>
> **Learned:** The obvious cause isn't always the real one — my instinct was that
> my template was wrong, when the template was never reached. It also taught me
> that WordPress caches more than it looks like it does, and that "where in the
> request lifecycle does this happen?" is usually the more useful question than
> "what's wrong with my code?"

That last paragraph is the part they actually care about.

---

## Q3. "How did you solve a technical problem?"

Use a **different** problem from Q2 so you're not repeating yourself. Good option
if you want a design decision rather than a bug — the validation duplication:

> "I had to decide how much to trust the browser. Validating only in JavaScript
> gives instant feedback but is trivially bypassed — you can delete the script in
> devtools and POST straight to the endpoint. Validating only in PHP is secure but
> means a full round trip before someone learns they've mistyped their email.
>
> I did both, and I tested the bypass deliberately: I sent a request from the
> console with a project type that isn't in the dropdown at all. The server
> rejected it with a 422, because it checks the value against an allowlist rather
> than trusting what arrived.
>
> The honest downside is that the rules now exist in two places and could drift
> apart. If I were doing it again I'd have PHP expose the rules through the REST
> API so the JavaScript reads them from one source. I've noted that in the
> README's future improvements rather than pretending it isn't a trade-off."

Naming the weakness in your own design is a strong move, not a weak one.

---

## Q4. "What would you improve?"

Pick three, be specific, no waffle:

1. **One source of truth for validation rules** — expose them via REST so the
   browser and server can't drift apart.
2. **Real WordPress integration tests** — the current PHPUnit suite covers the
   pure helper functions only. Proper coverage of the meta save and the REST
   routes needs the WordPress test suite, which I ran out of time for.
3. **Server-side filtering with progressive enhancement** — the filter currently
   hides cards already in the page, which won't scale past one page of results.
   I'd request filtered results from the endpoint and update the URL so a
   filtered view is shareable and works without JavaScript.

Bonus if it comes up: **caching and nonces**. On a site with full-page caching,
a cached page would serve a stale nonce and the form would start returning 403s.
The fix is fetching the nonce separately rather than baking it into the HTML.
Knowing that is well above junior level.

---

## Quiz yourself

Answer out loud. If you hesitate, go back to `03-CODE-WALKTHROUGH.md`.

**HTML**
- Name five semantic elements and what each is for
- Why is heading order important? What breaks if you skip from h1 to h3?
- What does `<label for="">` actually do, and why isn't a placeholder enough?
- When is `<aside>` correct and when is it wrong?

**CSS**
- Flexbox vs Grid — when do you reach for each?
- What does `repeat(auto-fit, minmax(280px, 1fr))` do?
- What is mobile-first and why is it better than desktop-first?
- What's a media query? Where did you put your breakpoints and why?
- What are CSS custom properties for?

**JavaScript**
- What's the difference between `const` and `let`?
- What is an event listener? Name three events you used.
- `querySelector` vs `querySelectorAll` — what does each return?
- What is the DOM?
- What does `preventDefault()` do and why did you need it?
- What is `async`/`await` doing? What is a promise?
- What is JSON? How do you turn it into a JS object?
- **What does `fetch` do when the server returns a 404?** (Resolves. Doesn't
  reject. You must check `response.ok`.)
- `textContent` vs `innerHTML` — why does it matter for security?

**WordPress**
- What is WordPress, in one sentence, to a non-technical person?
- Theme vs plugin — and why did your content types go in the plugin?
- What is a hook? Action vs filter?
- What is the template hierarchy? Which file renders `/projects/`?
- What is The Loop?
- What is a custom post type? A taxonomy?
- What is `functions.php` for?
- Why `wp_enqueue_style()` instead of a `<link>` tag?

**PHP**
- What does PHP do that JavaScript in the browser can't?
- Server-side vs client-side — explain to a non-developer
- Sanitise vs validate vs escape — three different things, three different moments
- How does this project avoid SQL injection?
- What is a nonce and what attack does it prevent?

**APIs**
- What is an API, to a non-technical client?
- What does REST mean?
- GET vs POST?
- What is JSON and why is it used?
- What do 200, 201, 400, 403, 404, 422 and 500 mean?
- Which APIs did you build, and which did you consume?

**Git and CI**
- What problem does version control solve?
- Repository, commit, branch, merge, pull request — define each
- Why work on a branch instead of committing to `main`?
- What is a pull request for beyond merging?
- What does your GitHub Actions workflow run, and why does that help?

---

## Their soft criteria — have an example for each

They said they value curiosity, independent learning, problem solving,
communication, collaboration and attention to detail.

- **Independent learning** — you had no WordPress experience before this project.
  You now have a custom theme, a custom plugin and a REST API. That *is* the
  answer.
- **Curiosity** — you can explain why WordPress caches rewrite rules, not just
  that flushing them fixes the 404.
- **Attention to detail** — escaping every output, a visible focus ring on every
  interactive element, `prefers-reduced-motion` respected.
- **Communication** — the README and the code comments. Bring the repo up on your
  phone or a laptop if they'll let you.
- **Collaboration** — the PR with a review checklist, and the point that CI exists
  so a reviewer isn't the first line of defence.

---

## Two questions to ask them

Have a couple ready — it signals genuine interest:

- "How much of your WordPress work is custom themes versus maintaining existing
  client sites?"
- "How does code review work here day to day — is it a formal PR process or more
  conversational?"

---

## The night before

- [ ] Site running locally, all nine projects entered
- [ ] Repo pushed, CI green
- [ ] Your real challenge story written up in this file
- [ ] Read `filter.js` and `enquiry.js` out loud once more
- [ ] Bypass test rehearsed
- [ ] Route to Bonded Warehouse checked — it's off Deansgate near the Science and
      Industry Museum
- [ ] Laptop charged, in case they want to see it
