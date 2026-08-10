/**
 * Load featured projects from our own WordPress REST endpoint.
 *
 *   GET /wp-json/agencyflow/v1/projects?per_page=3
 *
 * This is the piece that proves the site consumes its own API rather than
 * only rendering PHP. The projects on the home page are fetched by the
 * browser after the page loads, then built into the DOM by JavaScript.
 *
 * INTERVIEW CHECKLIST FOR THIS FILE
 *   Endpoint      /wp-json/agencyflow/v1/projects
 *   Method        GET
 *   Response      JSON array of project objects
 *   Loading path  container shows "Loading projects…" first
 *   Error path    network failure OR a non-2xx status both show a message
 *                 and log the detail to the console
 */

'use strict';

/**
 * Build one project card element.
 *
 * Built with createElement and textContent rather than one big innerHTML
 * string. That means any character in the project title is treated as text,
 * never as markup, so this cannot introduce a cross-site scripting hole.
 *
 * @param {Object} project A project object from the API.
 * @returns {HTMLElement}
 */
function buildProjectCard(project) {
  const article = document.createElement('article');
  article.className = 'project-card';

  // --- Image (only if the project has a featured image) ------------------
  if (project.image) {
    const media = document.createElement('div');
    media.className = 'project-card__media';

    const img = document.createElement('img');
    img.src = project.image;
    img.alt = project.title;
    img.loading = 'lazy';

    media.appendChild(img);
    article.appendChild(media);
  }

  // --- Body -------------------------------------------------------------
  const body = document.createElement('div');
  body.className = 'project-card__body';

  if (project.type_names && project.type_names.length > 0) {
    const tags = document.createElement('div');
    tags.className = 'tag-list';

    project.type_names.forEach(function (name) {
      const tag = document.createElement('span');
      tag.className = 'tag';
      tag.textContent = name;
      tags.appendChild(tag);
    });

    body.appendChild(tags);
  }

  const heading = document.createElement('h3');
  heading.className = 'project-card__title';

  const link = document.createElement('a');
  link.href = project.permalink;
  link.textContent = project.title;
  heading.appendChild(link);
  body.appendChild(heading);

  if (project.excerpt) {
    const excerpt = document.createElement('p');
    excerpt.className = 'project-card__excerpt';
    excerpt.textContent = project.excerpt;
    body.appendChild(excerpt);
  }

  // --- Metadata ---------------------------------------------------------
  const meta = document.createElement('div');
  meta.className = 'project-meta';

  if (project.client) {
    meta.appendChild(buildMetaItem('Client', project.client));
  }

  if (project.completed_label) {
    meta.appendChild(buildMetaItem('Completed', project.completed_label));
  }

  body.appendChild(meta);
  article.appendChild(body);

  return article;
}

/**
 * Build a single label/value pair for the metadata row.
 *
 * @param {string} key   Label text.
 * @param {string} value Value text.
 * @returns {HTMLElement}
 */
function buildMetaItem(key, value) {
  const item = document.createElement('span');
  item.className = 'project-meta__item';

  const keyEl = document.createElement('span');
  keyEl.className = 'project-meta__key';
  keyEl.textContent = key;

  const valueEl = document.createElement('span');
  valueEl.textContent = value;

  item.appendChild(keyEl);
  item.appendChild(valueEl);

  return item;
}

/**
 * Fetch the projects and render them.
 *
 * async/await is used instead of .then() chains because it reads top to
 * bottom like ordinary code. 'await' pauses this function until the promise
 * settles; it does not block the browser.
 */
async function loadFeaturedProjects() {
  const container = document.querySelector('#featured-projects');

  if (!container || typeof agencyflowData === 'undefined') {
    return;
  }

  // --- Loading state ----------------------------------------------------
  // Set before the request starts. A visitor on a slow connection sees
  // something honest rather than an empty box.
  container.innerHTML = '';
  const loading = document.createElement('p');
  loading.className = 'is-loading';
  loading.textContent = 'Loading projects…';
  container.appendChild(loading);

  try {
    const url = agencyflowData.restUrl + 'projects?per_page=3';

    const response = await fetch(url, {
      headers: { Accept: 'application/json' }
    });

    // fetch() only rejects on a NETWORK failure. A 404 or 500 still resolves,
    // so the status must be checked by hand. This catches a lot of people out.
    if (!response.ok) {
      throw new Error('Request failed with status ' + response.status);
    }

    const projects = await response.json();

    container.innerHTML = '';

    // An empty list is a valid response, not an error.
    if (!Array.isArray(projects) || projects.length === 0) {
      const empty = document.createElement('p');
      empty.className = 'empty-state';
      empty.textContent = 'No projects published yet.';
      container.appendChild(empty);
      return;
    }

    // A DocumentFragment collects the new nodes off-screen, so the browser
    // only reflows the page once instead of on every appendChild.
    const fragment = document.createDocumentFragment();

    projects.forEach(function (project) {
      fragment.appendChild(buildProjectCard(project));
    });

    container.appendChild(fragment);
  } catch (error) {
    // --- Error state ----------------------------------------------------
    container.innerHTML = '';

    const message = document.createElement('p');
    message.className = 'empty-state';
    message.textContent = 'Could not load projects right now. Please try the Projects page.';
    container.appendChild(message);

    // The visitor gets a plain sentence; the developer gets the detail.
    console.error('AgencyFlow: featured projects failed —', error);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', loadFeaturedProjects);
} else {
  loadFeaturedProjects();
}
