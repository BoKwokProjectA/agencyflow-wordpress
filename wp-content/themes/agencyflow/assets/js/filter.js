/**
 * Project filtering.
 *
 * The whole feature in one sentence: when a filter button is clicked, read
 * the type off that button, then loop over every project card and add or
 * remove a CSS class depending on whether the card's type matches.
 *
 * No page reload, no network request. The cards are already in the HTML,
 * rendered by PHP, so filtering is purely a DOM operation.
 *
 * INTERVIEW CHECKLIST FOR THIS FILE
 *   What event fires?         'click' on a filter button.
 *   What function runs?       handleFilterClick, then applyFilter.
 *   What is selected?         The buttons and the cards, via querySelectorAll.
 *   What changes in the DOM?  The 'is-active' class on buttons and the
 *                             'is-hidden' class on cards, plus the status text.
 *   What if it fails?         If there is no filter bar on the page the script
 *                             returns early and does nothing.
 */

'use strict';

/**
 * Show only the cards matching a type.
 *
 * @param {string} selectedType Taxonomy slug, or 'all' for everything.
 * @param {NodeList} cards      All project card elements.
 * @returns {number}            How many cards ended up visible.
 */
function applyFilter(selectedType, cards) {
  let visibleCount = 0;

  // NodeList has forEach. Each 'card' is a real DOM element.
  cards.forEach(function (card) {
    // dataset reads data-* attributes. PHP wrote data-types="website ecommerce"
    // into each card, so this is a space-separated string of slugs.
    const cardTypes = (card.dataset.types || '').split(' ');

    const matches = selectedType === 'all' || cardTypes.indexOf(selectedType) !== -1;

    // classList.toggle with a second argument: add the class when the second
    // argument is true, remove it when false. The CSS rule
    // `.project-card.is-hidden { display: none; }` does the actual hiding —
    // JavaScript decides *what* to hide, CSS decides *how*.
    card.classList.toggle('is-hidden', !matches);

    if (matches) {
      visibleCount += 1;
    }
  });

  return visibleCount;
}

/**
 * Update the "showing X of Y" line and the empty state.
 *
 * @param {number} visible Number of visible cards.
 * @param {number} total   Total number of cards.
 */
function updateStatus(visible, total) {
  const status = document.querySelector('#filter-status');
  const empty = document.querySelector('#filter-empty');

  if (status) {
    // textContent, not innerHTML. textContent treats the value as plain text,
    // so even if a string contained '<script>' it could never execute.
    status.textContent = 'Showing ' + visible + ' of ' + total + ' projects.';
  }

  if (empty) {
    empty.hidden = visible !== 0;
  }
}

/**
 * Set up the filter bar.
 */
function initProjectFilter() {
  const filterBar = document.querySelector('#project-filters');
  const cards = document.querySelectorAll('.project-card');

  // Guard clause: this script is only enqueued on the projects archive, but
  // being defensive costs nothing and prevents console errors.
  if (!filterBar || cards.length === 0) {
    return;
  }

  const buttons = filterBar.querySelectorAll('.filter-button');

  buttons.forEach(function (button) {
    // addEventListener attaches a function to run when the event happens.
    // 'click' fires on mouse click AND on Enter/Space for keyboard users,
    // because these are real <button> elements. That is a reason to use
    // <button> rather than a styled <div>.
    button.addEventListener('click', function () {
      const selectedType = button.dataset.type || 'all';

      // Move the active state: clear it from every button, then set it on
      // the one that was clicked.
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

  // Set the initial status line on page load.
  updateStatus(cards.length, cards.length);
}

// The script is enqueued in the footer, so the DOM already exists by the time
// this runs. DOMContentLoaded is still checked in case that ever changes.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initProjectFilter);
} else {
  initProjectFilter();
}
