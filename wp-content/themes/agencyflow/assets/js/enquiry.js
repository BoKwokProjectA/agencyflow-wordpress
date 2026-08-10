/**
 * Project enquiry form.
 *
 * Flow:
 *   submit event -> preventDefault -> validate in the browser
 *   -> POST JSON to /wp-json/agencyflow/v1/enquiries
 *   -> PHP validates again -> render success or per-field errors
 *
 * The browser checks are for SPEED — instant feedback, no round trip. They
 * are not security. Anyone can open devtools, delete this file, and POST
 * straight to the endpoint, which is exactly why the PHP validates the same
 * rules again on the server. Client-side validation is a convenience;
 * server-side validation is the actual gate.
 *
 * INTERVIEW CHECKLIST FOR THIS FILE
 *   Event         'submit' on the form
 *   Why preventDefault?  Stops the browser doing a full page reload so we
 *                        can send the data with fetch instead.
 *   Method        POST, JSON body, nonce included
 *   Statuses      201 created, 422 validation failed, 403 bad nonce, 500 saved failed
 *   Error path    field errors from the server are shown under each field
 */

'use strict';

/**
 * Client-side validation rules.
 *
 * These deliberately mirror agencyflow_validate_enquiry() in the plugin.
 * Keeping them in step by hand is a real trade-off of this approach and it
 * is noted in the README as a future improvement.
 *
 * @param {Object} values Field values keyed by field name.
 * @returns {Object} Map of field name to error message. Empty means valid.
 */
function validateEnquiry(values) {
  const errors = {};

  if (!values.name) {
    errors.name = 'Enter your name.';
  } else if (values.name.length < 2) {
    errors.name = 'Your name needs at least 2 characters.';
  }

  if (!values.email) {
    errors.email = 'Enter your email address.';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(values.email)) {
    errors.email = 'Enter a valid email address, for example name@company.com.';
  }

  if (!values.project_type) {
    errors.project_type = 'Choose a project type.';
  }

  if (!values.budget) {
    errors.budget = 'Choose a budget range.';
  }

  if (!values.message) {
    errors.message = 'Tell us about your project.';
  } else if (values.message.length < 20) {
    errors.message = 'Please give us a little more detail — at least 20 characters.';
  }

  return errors;
}

/**
 * Clear every error message and error class in the form.
 *
 * @param {HTMLFormElement} form The enquiry form.
 */
function clearErrors(form) {
  form.querySelectorAll('.field').forEach(function (field) {
    field.classList.remove('has-error');
  });

  form.querySelectorAll('.field__error').forEach(function (slot) {
    slot.textContent = '';
  });

  form.querySelectorAll('input, select, textarea').forEach(function (input) {
    input.removeAttribute('aria-invalid');
  });
}

/**
 * Display errors underneath the fields they belong to.
 *
 * aria-invalid and the error element's id being referenced by
 * aria-describedby in the HTML is what makes this accessible: a screen
 * reader announces the message when focus reaches the broken field.
 *
 * @param {HTMLFormElement} form   The enquiry form.
 * @param {Object}          errors Map of field name to message.
 */
function showErrors(form, errors) {
  Object.keys(errors).forEach(function (fieldName) {
    const input = form.querySelector('[name="' + fieldName + '"]');

    if (!input) {
      return;
    }

    const wrapper = input.closest('.field');
    const slot = document.querySelector('#error-' + fieldName);

    if (wrapper) {
      wrapper.classList.add('has-error');
    }

    if (slot) {
      slot.textContent = errors[fieldName];
    }

    input.setAttribute('aria-invalid', 'true');
  });

  // Move focus to the first broken field so a keyboard user is taken
  // straight to the problem.
  const firstField = Object.keys(errors)[0];
  const firstInput = form.querySelector('[name="' + firstField + '"]');

  if (firstInput) {
    firstInput.focus();
  }
}

/**
 * Write a message into the form-level status area.
 *
 * @param {HTMLElement} statusEl The status element.
 * @param {string}      message  Text to show.
 * @param {string}      state    'success', 'error' or '' for neutral.
 */
function setStatus(statusEl, message, state) {
  if (!statusEl) {
    return;
  }

  statusEl.textContent = message;
  statusEl.classList.remove('is-success', 'is-error');

  if (state === 'success') {
    statusEl.classList.add('is-success');
  } else if (state === 'error') {
    statusEl.classList.add('is-error');
  }
}

/**
 * Wire up the form.
 */
function initEnquiryForm() {
  const form = document.querySelector('#enquiry-form');

  if (!form || typeof agencyflowData === 'undefined') {
    return;
  }

  const statusEl = document.querySelector('#form-status');
  const submitButton = form.querySelector('button[type="submit"]');

  // Clear a field's error as soon as the visitor starts fixing it. 'input'
  // fires on every keystroke; 'change' fires when a select loses focus with
  // a new value, which is why both are used.
  form.querySelectorAll('input, textarea').forEach(function (input) {
    input.addEventListener('input', function () {
      const wrapper = input.closest('.field');
      const slot = document.querySelector('#error-' + input.name);

      if (wrapper) {
        wrapper.classList.remove('has-error');
      }
      if (slot) {
        slot.textContent = '';
      }
      input.removeAttribute('aria-invalid');
    });
  });

  form.querySelectorAll('select').forEach(function (select) {
    select.addEventListener('change', function () {
      const wrapper = select.closest('.field');
      const slot = document.querySelector('#error-' + select.name);

      if (wrapper) {
        wrapper.classList.remove('has-error');
      }
      if (slot) {
        slot.textContent = '';
      }
      select.removeAttribute('aria-invalid');
    });
  });

  form.addEventListener('submit', async function (event) {
    // Stop the browser's default behaviour, which is to reload the page and
    // send the form as a normal POST. We want to send it with fetch instead.
    event.preventDefault();

    clearErrors(form);
    setStatus(statusEl, '', '');

    // FormData reads the current values straight out of the form elements.
    const formData = new FormData(form);

    const values = {
      name: (formData.get('name') || '').trim(),
      email: (formData.get('email') || '').trim(),
      company: (formData.get('company') || '').trim(),
      project_type: formData.get('project_type') || '',
      budget: formData.get('budget') || '',
      message: (formData.get('message') || '').trim()
    };

    // --- Step 1: browser-side check ---------------------------------------
    const errors = validateEnquiry(values);

    if (Object.keys(errors).length > 0) {
      showErrors(form, errors);
      setStatus(statusEl, 'Please check the highlighted fields.', 'error');
      return;
    }

    // --- Step 2: loading state -------------------------------------------
    // Disabling the button prevents a double submission from an impatient
    // second click.
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Sending…';
    }
    setStatus(statusEl, 'Sending your enquiry…', '');

    // --- Step 3: send to the server --------------------------------------
    try {
      const response = await fetch(agencyflowData.restUrl + 'enquiries', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-WP-Nonce': agencyflowData.restNonce
        },
        body: JSON.stringify({
          name: values.name,
          email: values.email,
          company: values.company,
          project_type: values.project_type,
          budget: values.budget,
          message: values.message,
          // The nonce PHP put on the page. Without it the endpoint replies 403.
          nonce: agencyflowData.nonce
        })
      });

      const payload = await response.json();

      // 201 Created — success.
      if (response.status === 201) {
        form.reset();
        setStatus(statusEl, payload.message || 'Thanks — your enquiry is with us.', 'success');
        return;
      }

      // 422 — the server rejected specific fields. WordPress puts our error
      // map inside payload.data.errors when a WP_Error carries extra data.
      if (response.status === 422 && payload.data && payload.data.errors) {
        showErrors(form, payload.data.errors);
        setStatus(statusEl, payload.message || 'Please check the highlighted fields.', 'error');
        return;
      }

      // 403 (stale nonce), 500 (save failed) or anything else.
      throw new Error(payload.message || 'Request failed with status ' + response.status);
    } catch (error) {
      setStatus(
        statusEl,
        error.message || 'Something went wrong sending your enquiry. Please try again.',
        'error'
      );
      console.error('AgencyFlow: enquiry submission failed —', error);
    } finally {
      // 'finally' runs whether the request succeeded or threw, so the button
      // can never be left stuck in its disabled state.
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = 'Send enquiry';
      }
    }
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initEnquiryForm);
} else {
  initEnquiryForm();
}
