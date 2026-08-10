/**
 * ESLint configuration (flat config format, ESLint 9+).
 *
 * The rules chosen here are the ones that catch real mistakes in browser
 * JavaScript: undeclared variables, unused variables, accidental globals,
 * and using == where === is meant.
 */
module.exports = [
  {
    files: ['wp-content/themes/agencyflow/assets/js/**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'script',
      globals: {
        // Browser globals this project uses.
        document: 'readonly',
        window: 'readonly',
        console: 'readonly',
        fetch: 'readonly',
        FormData: 'readonly',
        URLSearchParams: 'readonly',
        // Injected from PHP by wp_localize_script().
        agencyflowData: 'readonly'
      }
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': 'warn',
      'no-var': 'error',
      eqeqeq: ['error', 'always'],
      'prefer-const': 'warn',
      curly: 'error',
      semi: ['error', 'always']
    }
  }
];
