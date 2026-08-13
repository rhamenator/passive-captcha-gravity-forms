import { defineConfig } from 'eslint/config';

export default defineConfig([
  {
    files: ['js/**/*.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        btoa: 'readonly',
        console: 'readonly',
        document: 'readonly',
        fetch: 'readonly',
        navigator: 'readonly',
        screen: 'readonly',
        sessionStorage: 'readonly',
        setInterval: 'readonly',
        window: 'readonly',
      },
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': [
        'error',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^(findCaptchaTokenField|logMissingFieldWarning)$',
        },
      ],
    },
  },
]);
