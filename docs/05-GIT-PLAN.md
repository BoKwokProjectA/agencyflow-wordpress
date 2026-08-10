# 05 — Git plan

Don't commit the bundle in one go. Stage it in slices that tell the story of how
the project was built, because your commit history is part of what an interviewer
looks at.

---

## Setup

From the WordPress root (`app/public`):

```bash
git --version
git init
git branch -M main
```

Check the ignore rules are working **before your first commit**:

```bash
git add .gitignore
git status
```

You should see a handful of files, not hundreds. If you see WordPress core
files, stop and fix `.gitignore` first.

---

## The commit sequence

Ten commits. Run `git status` before each `git commit` so you can see what you're
about to record.

```bash
# 1
git add .gitignore
git commit -m "chore: initialise AgencyFlow project and ignore WordPress core"

# 2
git add wp-content/themes/agencyflow/style.css \
        wp-content/themes/agencyflow/functions.php \
        wp-content/themes/agencyflow/index.php
git commit -m "feat: create custom WordPress theme with asset enqueueing"

# 3
git add wp-content/themes/agencyflow/header.php \
        wp-content/themes/agencyflow/footer.php \
        wp-content/themes/agencyflow/assets/css/main.css
git commit -m "feat: add semantic site layout with responsive Flexbox and Grid CSS"

# 4
git add wp-content/plugins/agencyflow-project-manager/agencyflow-project-manager.php \
        wp-content/plugins/agencyflow-project-manager/includes/post-types.php \
        wp-content/plugins/agencyflow-project-manager/includes/helpers.php
git commit -m "feat: add project custom post type and taxonomy in a custom plugin"

# 5
git add wp-content/plugins/agencyflow-project-manager/includes/meta-fields.php
git commit -m "feat: add project metadata with nonce-protected sanitised saving"

# 6
git add wp-content/themes/agencyflow/front-page.php \
        wp-content/themes/agencyflow/archive-project.php \
        wp-content/themes/agencyflow/single-project.php \
        wp-content/themes/agencyflow/template-parts/project-card.php
git commit -m "feat: add project archive and detail templates with escaped output"

# 7  -- this one goes on a branch, see below
# 8
git add wp-content/plugins/agencyflow-project-manager/includes/rest-api.php \
        wp-content/themes/agencyflow/assets/js/featured-projects.js
git commit -m "feat: add custom REST endpoint and load featured projects with fetch"

# 9
git add wp-content/themes/agencyflow/assets/js/weather.js
git commit -m "feat: integrate Open-Meteo API with loading and error states"

# 10
git add wp-content/themes/agencyflow/page-contact.php \
        wp-content/themes/agencyflow/assets/js/enquiry.js \
        wp-content/plugins/agencyflow-project-manager/includes/enquiries.php
git commit -m "feat: add enquiry workflow with client and server validation"

# 11
git add tests/ phpunit.xml composer.json package.json eslint.config.js
git commit -m "test: add PHPUnit helper tests and linting configuration"

# 12
git add .github/
git commit -m "ci: add GitHub Actions workflow for PHP and JavaScript checks"

# 13
git add README.md docs/
git commit -m "docs: add README and technical documentation"
```

---

## The branch and pull request (commit 7)

Do this properly — it's an explicit interview topic. Skip commit 7 above, get
through the rest, then come back and do the filtering feature on a branch.

```bash
# Create a branch and switch to it in one command.
# A branch is a movable pointer to a commit — it is not a copy of the files.
git checkout -b feature/project-filtering

# Add the filtering feature.
git add wp-content/themes/agencyflow/assets/js/filter.js
git commit -m "feat: add vanilla JavaScript project filtering"

# Push the branch to GitHub and set it as the upstream for future pushes.
git push -u origin feature/project-filtering
```

On GitHub: **Compare & pull request**. In the description, write what changed,
how you tested it, and paste the review checklist from `04-TESTING.md`.

Watch GitHub Actions run on the PR. Wait for the green tick — that's the pipeline
doing its job.

Then:

```bash
git checkout main
git pull origin main        # bring the merged commit down
git branch -d feature/project-filtering
```

**What each step means, in interview language:**

| Command | What it actually does |
|---|---|
| `git init` | Creates the `.git` directory that stores the entire history |
| `git add` | Stages changes — the staging area lets you commit *some* of your work, not all of it |
| `git commit` | Records a permanent snapshot with a message |
| `git checkout -b` | Creates a branch and moves onto it, so work happens off `main` |
| `git push -u origin <branch>` | Uploads the branch and remembers the link |
| Pull request | A request to merge, plus a place for review and automated checks *before* anything reaches `main` |
| `git merge` | Combines the branch's commits back into `main` |
| `git pull` | Fetches from the remote and merges into your local branch |

**Why branch at all on a solo project?** Because `main` should always be
deployable. A branch means unfinished work never sits on the branch a client's
site would be built from — and it gives CI something to check before the merge,
not after.

---

## Pushing to GitHub

On github.com: **New repository**, name `agencyflow`, **Public**, add nothing
else (no README, no .gitignore — you already have them).

```bash
git remote add origin https://github.com/YOURUSERNAME/agencyflow.git
git push -u origin main
```

Then update the placeholder `YOURUSERNAME` in `style.css`, the plugin header and
`composer.json`.

---

## If you make a mistake

| Situation | Fix |
|---|---|
| Wrong commit message, not pushed | `git commit --amend -m "better message"` |
| Staged something by accident | `git restore --staged <file>` |
| Want to see what you're about to commit | `git diff --staged` |
| Committed core files by accident | Fix `.gitignore`, then `git rm -r --cached wp-admin wp-includes` and commit |
