import { defineConfig, devices } from '@playwright/test';

const isCI = !!process.env.CI;
const baseURL = process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8000';

export default defineConfig({
  testDir: './e2e',
  // Test berbagi satu database (alur pinjam buku), jadi jalan berurutan agar deterministik.
  fullyParallel: false,
  workers: 1,
  retries: isCI ? 1 : 0,
  forbidOnly: isCI,
  timeout: 30_000,
  expect: { timeout: 7_000 },

  reporter: isCI
    ? [
        ['github'],
        ['list'],
        ['html', { open: 'never' }],
        ['json', { outputFile: 'test-results/results.json' }],
      ]
    : [['list'], ['html', { open: 'never' }]],

  use: {
    baseURL,
    locale: 'id-ID',
    timezoneId: 'Asia/Jakarta',
    trace: 'retain-on-failure', // trace hanya disimpan kalau test gagal
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },

  projects: [
    // 1) login sekali per role, simpan sesi ke e2e/.auth/*.json
    { name: 'setup', testMatch: /global\.setup\.ts/ },
    // 2) semua spec memakai sesi tersebut (kecuali yang override storageState)
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['setup'],
      testIgnore: /global\.setup\.ts/,
    },
  ],

  // Jalankan Laravel otomatis kalau E2E_BASE_URL tidak diisi (mode lokal).
  // Di CI server sudah dinyalakan oleh workflow, jadi blok ini dilewati.
  webServer: process.env.E2E_BASE_URL
    ? undefined
    : {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: baseURL,
        reuseExistingServer: !isCI,
        timeout: 60_000,
        env: { PHP_CLI_SERVER_WORKERS: '4' },
      },
});
