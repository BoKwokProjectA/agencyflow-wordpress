/**
 * Displays current Manchester weather using the Open-Meteo API.
 */

'use strict';

/**
 * Convert a WMO weather code to a readable description.
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

  // Reset the weather display while loading.
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

  // Build the API query parameters.
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

    // Validate the expected response data.
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
    // Show a fallback state if the weather request fails.
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
