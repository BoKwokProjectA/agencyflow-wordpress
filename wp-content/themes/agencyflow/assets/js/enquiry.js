/**
 * Handles validation and submission for the project enquiry form.
 * Server-side validation remains authoritative.
 */
'use strict';

/**
 * Validate enquiry fields before submission.
 *
 * @param {Object} values Field values keyed by field name.
 * @returns {Object} Field validation errors.
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
 * Clear validation errors from the form.
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
 * Display field validation errors.
 *
 * @param {HTMLFormElement} form   The enquiry form.
 * @param {Object}          errors Field validation errors.
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

  // Focus the first field with an error.

  const firstField = Object.keys(errors)[0];
  const firstInput = form.querySelector('[name="' + firstField + '"]');

  if (firstInput) {
    firstInput.focus();
  }
}

/**
 * Update the form status message.
 *
 * @param {HTMLElement} statusEl The status element.
 * @param {string}      message  Message to display.
 * @param {string}      state    Status state.
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
 * Initialise the enquiry form.
 */
function initEnquiryForm() {
  const form = document.querySelector('#enquiry-form');

  if (!form || typeof agencyflowData === 'undefined') {
    return;
  }

  const statusEl = document.querySelector('#form-status');
  const submitButton = form.querySelector('button[type="submit"]');

  // Clear validation errors when a field is updated.
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
    event.preventDefault();

    clearErrors(form);
    setStatus(statusEl, '', '');

    const formData = new FormData(form);

    const values = {
      name: (formData.get('name') || '').trim(),
      email: (formData.get('email') || '').trim(),
      company: (formData.get('company') || '').trim(),
      project_type: formData.get('project_type') || '',
      budget: formData.get('budget') || '',
      message: (formData.get('message') || '').trim()
    };

    const errors = validateEnquiry(values);

    if (Object.keys(errors).length > 0) {
      showErrors(form, errors);
      setStatus(statusEl, 'Please check the highlighted fields.', 'error');
      return;
    }

    
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Sending…';
    }
    setStatus(statusEl, 'Sending your enquiry…', '');

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
          nonce: agencyflowData.nonce
        })
      });

      const payload = await response.json();

      if (response.status === 201) {
        form.reset();
        setStatus(statusEl, payload.message || 'Thanks — your enquiry is with us.', 'success');
        return;
      }

      // Display server-side validation errors.
      if (response.status === 422 && payload.data && payload.data.errors) {
        showErrors(form, payload.data.errors);
        setStatus(statusEl, payload.message || 'Please check the highlighted fields.', 'error');
        return;
      }

      throw new Error(payload.message || 'Request failed with status ' + response.status);
    } catch (error) {
      setStatus(
        statusEl,
        error.message || 'Something went wrong sending your enquiry. Please try again.',
        'error'
      );
      console.error('AgencyFlow: enquiry submission failed —', error);
    } finally {
      // Restore the submit button.
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
