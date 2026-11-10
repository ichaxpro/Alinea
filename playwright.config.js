// @ts-check
import { defineConfig, devices } from '@playwright/test';

/**
 * Konfigurasi Playwright untuk E2E Testing Alinea
 * Base URL bisa di-override via environment variable PW_BASE_URL
 */
export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,       // sequential — hindari race condition DB
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : 1,
  timeout: 30_000,            // 30 detik per test

  reporter: [
    ['list'],                 // ringkasan di console
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
    ...(process.env.CI ? [['github']] : []),  // annotasi inline di GitHub UI
  ],

  use: {
    baseURL: process.env.PW_BASE_URL ?? 'http://localhost:8000',
    headless: true,
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    trace: 'on-first-retry',
    locale: 'id-ID',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  outputDir: 'test-results',
});
