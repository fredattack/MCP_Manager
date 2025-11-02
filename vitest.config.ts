import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  test: {
    // Environnement de test (DOM simulé)
    environment: 'jsdom',

    // Variables globales (describe, it, expect, vi)
    globals: true,

    // Fichier de setup
    setupFiles: './resources/js/setupTests.ts',

    // Patterns de fichiers de test
    include: ['resources/js/**/*.{test,spec}.{ts,tsx}'],

    // Exclure
    exclude: [
      'node_modules',
      'dist',
      'resources/js/types/**',
    ],

    // Couverture de code
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      reportsDirectory: './coverage/frontend',
      include: ['resources/js/**/*.{ts,tsx}'],
      exclude: [
        'resources/js/**/*.d.ts',
        'resources/js/**/__tests__/**',
        'resources/js/types/**',
        'resources/js/setupTests.ts',
      ],
      thresholds: {
        lines: 70,
        functions: 70,
        branches: 70,
        statements: 70,
      },
    },

    // Reporters
    reporters: ['verbose'],

    // Timeout
    testTimeout: 10000,
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
});
