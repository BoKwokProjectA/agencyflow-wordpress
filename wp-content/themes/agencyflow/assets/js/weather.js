/**
 * External API integration — Open-Meteo.
 *
 *   GET https://api.open-meteo.com/v1/forecast
 *       ?latitude=53.4808&longitude=-2.2426
 *       &current=temperature_2m,weather_code,wind_speed_10m
 *
 * Why this is in the project at all: an agency site that says "we're in
 * Manchester" can show live Manchester conditions. It is a small, honest
 * reason to call a third-party service, which is the point — it demonstrates
 * consuming an API I do not control.
 *
 * Open-Meteo needs no API key for non-commercial use, so there is no secret
 * in this file. That matters: anything in a JavaScript file is public. A key
 * would have to live in PHP on the server and be proxied.
 *
 * INTERVIEW CHECKLIST FOR THIS FILE
 *   Endpoint      api.open-meteo.com/v1/forecast
 *   Method        GET, parameters in the query string
 *   Response      JSON, with a 'current' object inside it
 *   Error path    network down, or Open-Meteo returns 400 with
 *                 {"error": true, "reason": "..."} — both handled below
 */

'use strict';

/**
 * Turn an Open-Meteo WMO weather code into words.
 *
 * The API returns a number, not a description, so this mapping lives on our
 * side. Ranges are grouped rather than listing all 100 codes.
 *
 * @param {number} code WMO weather code.
 * @returns {string}
 */
function describeWeatherCode(code) {
  if (code === 0) {
    return 'Clear sky';
  }
  if (code <= 3) {
    return 'Partly cloudy';
  }
  if (code <= 49) {
    return 'Fog';
  }
  if (code <= 59) {
    return 'Drizzle';
  }
  if (code <= 69) {
    return 'Rain';
  }
  if (code <= 79) {
    return 'Snow';
  }
  if (code <= 84) {
    return 'Rain showers';
  }
  if (code <= 99) {
    return 'Thunderstorm';
  }
  return 'Unavailable';
}

/**
 * Fetch current conditions and write them into the weather strip.
 */
async function loadWeather() {
  const strip = document.querySelector('#weather-strip');

  if (!strip || typeof agencyflowData === 'undefined') {
    return;
  }

  const tempEl = strip.querySelector('#weather-temp');
  const descEl = strip.querySelector('#weather-desc');
  const windEl = strip.querySelector('#weather-wind');

  // --- Loading state ----------------------------------------------------
  strip.classList.remove('is-error');
  if (tempEl) {
    tempEl.textContent = '…';
  }
  if (descEl) {
    descEl.textContent = 'Checking conditions';
  }
  if (windEl) {
    windEl.textContent = '';
  }

  // URLSearchParams builds the query string safely, encoding each value.
  // Hand-concatenating query strings is where encoding bugs come from.
  const params = new URLSearchParams({
    latitude: agencyflowData.lat,
    longitude: agencyflowData.lon,
    current: 'temperature_2m,weather_code,wind_speed_10m',
    timezone: 'Europe/London'
  });

  const endpoint = 'https://api.open-meteo.com/v1/forecast?' + params.toString();

  try {
    const response = await fetch(endpoint);

    if (!response.ok) {
      throw new Error('Open-Meteo returned status ' + response.status);
    }

    const data = await response.json();

    // Never assume the shape of a response from a service you don't control.
    if (!data || !data.current) {
      throw new Error('Unexpected response shape from Open-Meteo');
    }

    const current = data.current;
    const units = data.current_units || {};

    if (tempEl) {
      tempEl.textContent = Math.round(current.temperature_2m) + (units.temperature_2m || '°C');
    }

    if (descEl) {
      descEl.textContent = describeWeatherCode(current.weather_code);
    }

    if (windEl) {
      windEl.textContent =
        'Wind ' + Math.round(current.wind_speed_10m) + ' ' + (units.wind_speed_10m || 'km/h');
    }
  } catch (error) {
    // --- Error state ----------------------------------------------------
    // The page still works perfectly without the weather. A failing
    // decorative API must never break the rest of the site.
    strip.classList.add('is-error');

    if (tempEl) {
      tempEl.textContent = '—';
    }
    if (descEl) {
      descEl.textContent = 'Weather unavailable';
    }
    if (windEl) {
      windEl.textContent = '';
    }

    console.error('AgencyFlow: weather request failed —', error);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', loadWeather);
} else {
  loadWeather();
}
