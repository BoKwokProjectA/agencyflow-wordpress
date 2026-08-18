/**
 * ESLint configuration for the theme JavaScript.
 */
module.exports = [
  {
    files: ['wp-content/themes/agencyflow/assets/js/**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'script',
      globals: {
        // Browser globals.
        document: 'readonly',
        window: 'readonly',
        console: 'readonly',
        fetch: 'readonly',
        FormData: 'readonly',
        URLSearchParams: 'readonly',
        // Localized by WordPress.
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
