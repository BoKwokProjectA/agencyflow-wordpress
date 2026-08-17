/**
 * Load featured projects from the WordPress REST API.
 */

'use strict';

/**
 * Build a project card.
 *
 * @param {Object} project Project data from the API.
 * @returns {HTMLElement}
 */
function buildProjectCard(project) {
  const article = document.createElement('article');
  article.className = 'project-card';

  // Featured image.
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

  // Card content.
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

  // Project metadata.
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
 * Build a project metadata item.
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
 * Fetch and render featured projects.
 */
async function loadFeaturedProjects() {
  const container = document.querySelector('#featured-projects');

  if (!container || typeof agencyflowData === 'undefined') {
    return;
  }

  // Show the loading state.
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

    // Treat unsuccessful HTTP responses as errors.
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

    // Build the cards before adding them to the page.
    const fragment = document.createDocumentFragment();

    projects.forEach(function (project) {
      fragment.appendChild(buildProjectCard(project));
    });

    container.appendChild(fragment);
  } catch (error) {
    // Show a fallback message.
    container.innerHTML = '';

    const message = document.createElement('p');
    message.className = 'empty-state';
    message.textContent = 'Could not load projects right now. Please try the Projects page.';
    container.appendChild(message);

    console.error('AgencyFlow: featured projects failed —', error);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', loadFeaturedProjects);
} else {
  loadFeaturedProjects();
}
