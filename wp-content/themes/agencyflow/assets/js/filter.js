/**
 * Project filtering for the projects archive.
 */

'use strict';

/**
 * Show project cards matching the selected type.
 *
 * @param {string}   selectedType Taxonomy slug, or 'all'.
 * @param {NodeList} cards        Project card elements.
 * @returns {number} Number of visible cards.
 */
function applyFilter(selectedType, cards) {
  let visibleCount = 0;

  cards.forEach(function (card) {
    // Read the project's assigned type slugs.
    const cardTypes = (card.dataset.types || '').split(' ');

    const matches = selectedType === 'all' || cardTypes.indexOf(selectedType) !== -1;

    // Hide cards that do not match the selected type.
    card.classList.toggle('is-hidden', !matches);

    if (matches) {
      visibleCount += 1;
    }
  });

  return visibleCount;
}

/**
 * Update the project count and empty state.
 *
 * @param {number} visible Number of visible cards.
 * @param {number} total   Total number of cards.
 */
function updateStatus(visible, total) {
  const status = document.querySelector('#filter-status');
  const empty = document.querySelector('#filter-empty');

  if (status) {
    
    status.textContent = 'Showing ' + visible + ' of ' + total + ' projects.';
  }

  if (empty) {
    empty.hidden = visible !== 0;
  }
}

/**
 * Initialise project filtering.
 */
function initProjectFilter() {
  const filterBar = document.querySelector('#project-filters');
  const cards = document.querySelectorAll('.project-card');

  // Exit if the filter UI is not available.
  if (!filterBar || cards.length === 0) {
    return;
  }

  const buttons = filterBar.querySelectorAll('.filter-button');

  buttons.forEach(function (button) {
    
    button.addEventListener('click', function () {
      const selectedType = button.dataset.type || 'all';

      // Update the active filter button.
      buttons.forEach(function (other) {
        other.classList.remove('is-active');
        other.setAttribute('aria-pressed', 'false');
      });

      button.classList.add('is-active');
      button.setAttribute('aria-pressed', 'true');

      const visible = applyFilter(selectedType, cards);
      updateStatus(visible, cards.length);
    });
  });

  // Set the initial project count.
  updateStatus(cards.length, cards.length);
}

// Initialise once the DOM is ready.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initProjectFilter);
} else {
  initProjectFilter();
}
